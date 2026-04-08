<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\InventarioAlmacen;
use App\Traits\FilterByAlmacen;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VentasExport;
use App\Models\Movimiento;

class VentaController extends Controller
{
    use FilterByAlmacen;

    function __construct()
    {
        $this->middleware('permission:ver-venta')->only(['index','show' ]);
        $this->middleware('permission:crear-venta')->only([ 'create','store']);
        $this->middleware('permission:editar-venta')->only(['edit','update']);
        $this->middleware('permission:update-estado-venta')->only(['updateEstado','destroy']);
        $this->middleware('permission:exportar-ventas')->only(['exportExcel','exportPdf']);
        $this->middleware('permission:ver-pdf-venta')->only(['pdf']);
    }

    public function index(Request $request)
    {

        $busqueda = $request->get('busqueda');
        $perPage  = $request->get('per_page', 10);
        $sort     = $request->get('sort', 'fecha_hora');
        $direction = $request->get('direction', 'desc');

        if (!in_array($perPage, [5, 10, 15, 20, 25])) $perPage = 10;
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'desc';

        $query = Venta::filter($request);
        $ventas = $query->paginate($perPage)->withQueryString();

        // Required for filters
        $almacenes = \App\Models\Almacen::where('estado', 1)->get();
        $productos = \App\Models\Producto::where('estado', 1)->get();

        return view('admin.venta.index', compact('ventas', 'busqueda', 'perPage', 'sort', 'direction', 'almacenes', 'productos'));
    }

    public function create()
    {

        $productos = Producto::where('estado', 1)
            ->get(['id', 'codigo', 'nombre', 'precio_compra', 'precio_venta']);

        $clientes = Cliente::where('estado', 1)->with(['persona', 'grupoCliente'])->get();
        $almacenes = \App\Models\Almacen::where('estado', 1)->get();

        $nextComprobanteNumber = $this->getNextComprobanteNumber();

        return view('admin.venta.create', compact('productos', 'clientes', 'nextComprobanteNumber', 'almacenes'));
    }

    public function store(StoreVentaRequest $request)
    {
        try {
            DB::beginTransaction();

            $almacenId = auth()->user()->almacen_id ?? $request->almacen_id;
            
            // Validar stock preliminarmente (sin descontar)
            $items = [];
            foreach ($request->arrayidproducto as $index => $productoId) {
                $items[] = [
                    'producto_id' => $productoId,
                    'cantidad' => $request->arraycantidad[$index] ?? 0
                ];
            }
            $this->validarStockDisponible($items, $almacenId);

            $venta = Venta::create([
                'fecha_hora' => $request->fecha_hora ?? now(),
                'numero_comprobante' => $request->numero_comprobante ?? $this->getNextComprobanteNumber(),
                'total' => 0,
                'estado_comprobante' => $request->estado_comprobante ?? 'boleta',
                'estado' => 'pendiente', // Siempre inicia como pendiente
                'cliente_id' => $request->cliente_id,
                'user_id' => auth()->id(),
                'almacen_id' => $almacenId,
                'nota_personal' => $request->nota_personal ?? null,
                'nota_cliente' => $request->nota_cliente ?? null,
            ]);

            $total = 0;
            foreach ($request->arrayidproducto as $index => $productoId) {
                $cantidad = floatval($request->arraycantidad[$index]);
                $precioVenta = floatval($request->arrayprecioventa[$index]);
                $descuento = floatval($request->arraydescuento[$index] ?? 0);

                $venta->detalles()->create([
                    'producto_id' => $productoId,
                    'cantidad' => $cantidad,
                    'precio_venta' => $precioVenta,
                    'descuento' => $descuento
                ]);

                $total += ($cantidad * $precioVenta) - $descuento;
            }

            $venta->update(['total' => $total]);

            // Se descuenta el stock inmediatamente (Reserva en Pendiente)
            $this->procesarSalidaStock($venta, $almacenId);

            DB::commit();

            return redirect()->route('ventas.index')->with('success', 'Venta registrada como PENDIENTE. Para descontar stock, cámbiela a COMPLETADA.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit(Venta $venta)
    {

        $productos = Producto::where('estado', 1)
            ->get(['id', 'codigo', 'nombre', 'precio_compra', 'precio_venta']);

        $clientes = Cliente::where('estado', 1)->with(['persona', 'grupoCliente'])->get();
        $almacenes = \App\Models\Almacen::where('estado', 1)->get();

        $venta->load('detalles.producto');

        return view('admin.venta.edit', compact('venta', 'productos', 'clientes', 'almacenes'));
    }

    public function update(UpdateVentaRequest $request, Venta $venta)
    {
        if (in_array($venta->estado, ['completada', 'cancelada'])) {
            return redirect()->back()->with('error', 'No se puede editar una venta que ya está finalizada o cancelada.');
        }

        try {
            DB::beginTransaction();

            $almacenId = $request->almacen_id;

            // 1. Validate New Stock preliminarily
            $items = [];
            foreach ($request->arrayidproducto as $index => $productoId) {
                $items[] = [
                    'producto_id' => $productoId,
                    'cantidad' => $request->arraycantidad[$index] ?? 0
                ];
            }
            $this->validarStockDisponible($items, $almacenId);

            // 2. Revertir stock anterior (ya que estaba descontado como Pendiente)
            $this->revertirStock($venta);

            // 3. Clear Old Detalles
            $venta->detalles()->delete();

            // 3. Create New Detalles
            $total = 0;
            foreach ($request->arrayidproducto as $index => $productoId) {
                $cantidad = floatval($request->arraycantidad[$index]);
                $precioVenta = floatval($request->arrayprecioventa[$index]);
                $descuento = floatval($request->arraydescuento[$index] ?? 0);

                $venta->detalles()->create([
                    'producto_id' => $productoId,
                    'cantidad' => $cantidad,
                    'precio_venta' => $precioVenta,
                    'descuento' => $descuento,
                ]);

                $total += ($cantidad * $precioVenta) - $descuento;
            }

            // 4. Update Venta properties
            $venta->update([
                'estado_comprobante' => $request->estado_comprobante ?? 'boleta',
                'cliente_id' => $request->cliente_id,
                'almacen_id' => $almacenId,
                'total' => $total,
                'fecha_hora' => $request->fecha_hora ?? $venta->fecha_hora,
                'nota_personal' => $request->nota_personal ?? null,
                'nota_cliente' => $request->nota_cliente ?? null,
            ]);

            // 5. Descontar nuevo stock
            $this->procesarSalidaStock($venta, $almacenId);

            DB::commit();

            return redirect()->route('ventas.index')->with('success', 'Venta actualizada correctamente y stock reservado.');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show(Venta $venta)
    {

        $venta->load(['cliente.persona', 'user.almacen', 'detalles.producto.tipoUnidad']);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_locked' => $venta->is_locked,
                'html' => view('admin.venta.show-modal', compact('venta'))->render()
            ]);
        }

        return view('admin.venta.show', compact('venta'));
    }

    public function destroy(Venta $venta)
    {
        try {
            if ($venta->estado === 'cancelada') {
                return redirect()->route('ventas.index')->with('warning', 'Esta venta ya se encuentra anulada.');
            }

            DB::beginTransaction();
            
            // Si la venta estaba completada o pendiente, debemos reponer el stock al anularla
            if (in_array($venta->estado, ['completada', 'pendiente'])) {
                $this->revertirStock($venta);
            }
            
            $venta->update(['estado' => 'cancelada']);
            
            DB::commit();
            return redirect()->route('ventas.index')->with('success', 'La venta ha sido anulada correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('ventas.index')->with('error', 'Error al anular: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $query = Venta::filter($request);
            $ventas = $query->get();
            
            $filename = 'ventas_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new VentasExport($ventas), $filename);
        } catch (Exception $e) {
            return back()->with('error', 'Error al exportar Excel: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = Venta::filter($request);
            $ventas = $query->get();
            
            $pdf = Pdf::loadView('admin.venta.export_pdf', compact('ventas'))
                ->setPaper('a4', 'landscape');
                
            return $pdf->download('reporte-ventas_' . now()->format('Y-m-d_H-i-s') . '.pdf');
        } catch (Exception $e) {
            return back()->with('error', 'Error al exportar PDF: ' . $e->getMessage());
        }
    }

    public function pdf(Request $request, Venta $venta)
    {
        $venta->load(['cliente.persona', 'almacen', 'user', 'detalles.producto.tipoUnidad']);
        $pdf = Pdf::loadView('admin.venta.pdf', compact('venta'));
        
        return $request->has('print') 
            ? $pdf->stream('venta-' . $venta->numero_comprobante . '.pdf')
            : $pdf->download('venta-' . $venta->numero_comprobante . '.pdf');
    }

    protected function procesarSalidaStock(Venta $venta, $almacenId = null)
    {
        // Solo descontar si no estaba ya completada (evitar doble descuento)
        // Aunque esto se controla en la máquina de estados de updateEstado
        
        $almacenId = $almacenId ?? $venta->almacen_id;
        foreach ($venta->detalles as $detalle) {
            // Usamos consulta directa con lockForUpdate para evitar error de falta de PK 'id' en el modelo
            $query = InventarioAlmacen::where('producto_id', $detalle->producto_id)
                ->where('almacen_id', $almacenId)
                ->lockForUpdate();

            $inventario = $query->first();

            if (!$inventario) {
                throw new Exception("El producto {$detalle->producto->nombre} no tiene registro de inventario en este almacén.");
            }

            if ($inventario->stock < $detalle->cantidad) {
                throw new Exception("Stock insuficiente para: " . $detalle->producto->nombre . " (Disponible: {$inventario->stock})");
            }

            $query->decrement('stock', $detalle->cantidad);
        }
    }

    protected function revertirStock(Venta $venta)
    {
        $almacenId = $venta->almacen_id;
        if (!$almacenId) {
            throw new Exception("No se pudo determinar el almacén de origen para revertir el stock.");
        }

        foreach ($venta->detalles as $detalle) {
            $query = InventarioAlmacen::where('producto_id', $detalle->producto_id)
                ->where('almacen_id', $almacenId)
                ->lockForUpdate();

            $inventario = $query->first();

            if ($inventario) {
                $query->increment('stock', $detalle->cantidad);
            } else {
                // Si por alguna razón no existe el registro, lo creamos para mantener coherencia
                InventarioAlmacen::create([
                    'producto_id' => $detalle->producto_id,
                    'almacen_id' => $almacenId,
                    'stock' => $detalle->cantidad
                ]);
            }
        }
    }

    protected function validarStockDisponible($items, $almacenId)
    {
        foreach ($items as $item) {
            if ($item['cantidad'] <= 0) {
                throw new Exception("La cantidad debe ser mayor a cero.");
            }

            $producto = Producto::find($item['producto_id']);
            $inventario = InventarioAlmacen::where('producto_id', $item['producto_id'])
                ->where('almacen_id', $almacenId)
                ->first();

            if (!$inventario || $inventario->stock < $item['cantidad']) {
                $disponible = $inventario ? $inventario->stock : 0;
                throw new Exception("Stock insuficiente para {$producto->nombre}. Requerido: {$item['cantidad']}, Disponible: {$disponible}");
            }
        }
    }

    public function checkStock(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
        ]);

        // Verificamos permiso de almacén si no es admin (opcional, según política)
        if (auth()->user()->almacen_id && auth()->user()->almacen_id != $request->almacen_id) {
            return response()->json(['success' => false, 'message' => 'No tiene permiso para ver el stock de este almacén'], 403);
        }

        $inventario = InventarioAlmacen::where('producto_id', $request->producto_id)
            ->where('almacen_id', $request->almacen_id)
            ->first();

        return response()->json([
            'success' => true,
            'stock' => $inventario ? $inventario->stock : 0,
            'ilimitado' => false
        ]);
    }

    public function updateEstado(Request $request, Venta $venta)
    {
        $request->validate([
            'estado' => 'required|in:cancelada,completada,pendiente'
        ]);

        $nuevoEstado = $request->estado;
        $estadoActual = $venta->estado;

        if ($nuevoEstado === $estadoActual) {
            return response()->json(['success' => true, 'message' => 'El estado es el mismo']);
        }

        // Bloqueo de estados finales
        if ($estadoActual === 'cancelada') {
            return response()->json(['success' => false, 'message' => 'No se puede reactivar una venta cancelada'], 422);
        }

        DB::beginTransaction();
        try {
            // MÁQUINA DE ESTADOS
            if ($estadoActual === 'pendiente' && $nuevoEstado === 'completada') {
                // Ya se descontó el stock al crear/editar. Solo registramos movimiento si se desea.
                // Pero por simplicidad de la máquina de estados, el stock ya está fuera.

                // 1. Cargar relaciones
                $venta->load(['detalles.producto', 'almacen', 'user.almacen']);

                $almacenId = $venta->almacen_id ?? optional($venta->user)->almacen_id;
                $almacenNombre = optional($venta->almacen)->nombre
                    ?? optional(optional($venta->user)->almacen)->nombre
                    ?? '—';

                // 3. Registrar movimiento
                foreach ($venta->detalles as $detalle) {

                    $stockFinal = InventarioAlmacen::where('producto_id', $detalle->producto_id)
                        ->where('almacen_id', $almacenId)
                        ->value('stock') ?? 0;

                    $stockInicial = $stockFinal + $detalle->cantidad;

                    Movimiento::create([
                        'tipo'                  => 'venta',
                        'referencia_id'         => $venta->id,
                        'referencia_texto'      => $venta->numero_comprobante ?? 'V-' . $venta->id,
                        'producto_nombre'       => optional($detalle->producto)->nombre ?? '—',
                        'almacen_origen'        => $almacenNombre,
                        'almacen_destino'       => null,
                        'cantidad'              => $detalle->cantidad,
                        'cantidad_anterior'     => null,
                        'cantidad_nueva'        => null,
                        'stock_inicial_origen'  => $stockInicial,
                        'stock_final_origen'    => $stockFinal,
                        'stock_inicial_destino' => null,
                        'stock_final_destino'   => null,
                        'usuario'               => auth()->user()->name ?? '—',
                        'motivo'                => 'Venta completada',
                        'fecha_hora'            => $venta->fecha_hora ?? now(),
                    ]);
                }

            } 
            elseif ($estadoActual === 'completada' && $nuevoEstado === 'cancelada') {
                // Reponer stock
                $this->revertirStock($venta);
            }
            elseif ($estadoActual === 'pendiente' && $nuevoEstado === 'cancelada') {
                // Ahora como Pendiente descuenta stock, debemos reponerlo
                $this->revertirStock($venta);
            }
            elseif ($estadoActual === 'completada' && $nuevoEstado === 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede regresar a Pendiente una venta ya Completada'
                ], 422);
            }

            $venta->update(['estado' => $nuevoEstado]);
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Venta actualizada a " . strtoupper($nuevoEstado) . " correctamente."
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function getNextComprobanteNumber()
    {
        // Bloqueo de tabla para obtener el siguiente número de forma segura en alta concurrencia
        return DB::transaction(function() {
            $last = Venta::lockForUpdate()->latest('id')->first();
            $next = $last ? (int)$last->numero_comprobante + 1 : 1;
            return str_pad($next, 8, '0', STR_PAD_LEFT);
        });
    }
}