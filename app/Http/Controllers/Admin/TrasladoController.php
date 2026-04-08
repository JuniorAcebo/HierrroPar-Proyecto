<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrasladoRequest;
use App\Http\Requests\UpdateTrasladoRequest;
use App\Models\User;
use App\Models\Almacen;
use App\Models\InventarioAlmacen;
use App\Models\Producto;
use App\Models\Traslado;
use App\Models\DetalleTraslado;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TrasladosExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Movimiento;

class TrasladoController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('permission:ver-traslado', ['only' => ['index']]);
        $this->middleware('permission:crear-traslado', ['only' => ['create', 'store','checkStock']]);
        $this->middleware('permission:editar-traslado', ['only' => ['edit', 'update']]);
        $this->middleware('permission:update-estado-traslado', ['only' => ['updateEstado','procesarInventarioTraslado']]);
        $this->middleware('permission:exportar-traslados', ['only' => ['exportExcel', 'exportPdf']]);
    }

    public function index(Request $request)
    {
        $busqueda   = $request->get('busqueda');
        $perPage    = $request->get('per_page', 10);
        $sort       = $request->get('sort', 'id'); // ordenar por 'id' por defecto
        $direction  = $request->get('direction');

        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin    = $request->get('fecha_fin');

        // validaciones básicas
        if (!in_array($perPage, [5, 10, 15, 20, 25])) $perPage = 10;

        // desc por defecto
        if (!$direction && $sort === 'id') {
            $direction = 'desc';
        }

        if (!in_array($direction, ['asc', 'desc'])) $direction = 'desc';

        $user = auth()->user();

        $query = Traslado::with([
            'origenAlmacen',
            'destinoAlmacen',
            'user',
            'detalleTraslados.producto'
        ])->withCount([
            'detalleTraslados as total_items' => function ($q) {
                $q->select(DB::raw("COALESCE(SUM(cantidad),0)"));
            }
        ]);

        // Filtro por rango de fechas
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_hora', [$fechaInicio, $fechaFin]);
        } elseif ($fechaInicio) {
            $query->whereDate('fecha_hora', '>=', $fechaInicio);
        } elseif ($fechaFin) {
            $query->whereDate('fecha_hora', '<=', $fechaFin);
        }

        // Filtro según almacén del usuario
        if ($user->role_id != 1) {
            $almacenId = $user->almacen_id;
            $query->where(function ($q) use ($almacenId) {
                $q->where('origen_almacen_id', $almacenId)
                ->orWhere('destino_almacen_id', $almacenId);
            });
        }

        // Buscador
        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('id', 'like', "%{$busqueda}%")
                ->orWhere('estado', 'like', "%{$busqueda}%")
                ->orWhereHas('origenAlmacen', fn ($qa) => $qa->where('nombre', 'like', "%{$busqueda}%"))
                ->orWhereHas('destinoAlmacen', fn ($qa) => $qa->where('nombre', 'like', "%{$busqueda}%"))
                ->orWhereHas('user', fn ($qa) => $qa->where('name', 'like', "%{$busqueda}%"))
                ->orWhereHas('detalleTraslados.producto', fn ($q) => $q->where('nombre', 'like', "%{$busqueda}%")
                                                                    ->orWhere('codigo', 'like', "%{$busqueda}%"));
            });
        }

        // Ordenamiento
        switch ($sort) {
            case 'estado':
                $query->orderByRaw("
                    CASE estado
                        WHEN 'pendiente' THEN 1
                        WHEN 'en_curso' THEN 2
                        WHEN 'completado' THEN 3
                        WHEN 'cancelado' THEN 4
                        ELSE 5
                    END {$direction}
                ");
                break;

            case 'total_items':
                $query->orderBy('total_items', $direction);
                break;

            case 'responsable':
                $query->orderBy(
                    User::select('name')->whereColumn('users.id', 'traslados.user_id'),
                    $direction
                );
                break;

            case 'id': //ordenar por ID
            default:
                $query->orderBy('id', $direction);
                break;
        }

        $traslados = $query->paginate($perPage)->appends($request->all());
        $almacenes = Almacen::where('estado', true)->get();

        return view('admin.traslado.index', compact(
            'traslados',
            'busqueda',
            'perPage',
            'almacenes',
            'sort',
            'direction'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        $isAdmin = $user->role_id == 1; // administrador

        if ($isAdmin) {
            $almacenes = Almacen::all();
            $almacenesDestino = Almacen::where('estado', true)->get();
            $origenFijo = false;
        } else {
            $almacenes = Almacen::where('id', $user->almacen_id)->get();
            $almacenesDestino = Almacen::where('estado', true)
                ->where('id', '!=', $user->almacen_id)
                ->get();
            $origenFijo = true;
        }

        $productos = Producto::with(['inventarioAlmacenes', 'categoria'])
            ->where('estado', 1)
            ->get();

        return view('admin.traslado.create', compact('almacenes', 'almacenesDestino', 'productos', 'origenFijo', 'user'));
    }

    public function edit(Traslado $traslado)
    {
        if ($traslado->estado !== 'pendiente') {
            abort(403, 'No puedes editar este traslado');
        }

        $user = auth()->user();
        $isAdmin = $user->role_id == 1; // administrador

        if ($isAdmin) {
            $almacenes = Almacen::all();
            $almacenesDestino = Almacen::where('estado', true)->get();
            $origenFijo = false;
        } else {
            $almacenes = Almacen::where('id', $user->almacen_id)->get();
            $almacenesDestino = Almacen::where('estado', true)
                ->where('id', '!=', $user->almacen_id)
                ->get();
            $origenFijo = true;
        }

        $productos = Producto::with(['inventarioAlmacenes', 'categoria'])
            ->where('estado', 1)
            ->get();

        $traslado->load('detalleTraslados.producto.categoria');

        return view('admin.traslado.edit', compact(
            'traslado',
            'almacenes',
            'almacenesDestino',
            'productos',
            'origenFijo',
            'user'
        ));
    }

    public function store(StoreTrasladoRequest $request)
    {
        DB::beginTransaction();

        try {
            // Validar que se hayan agregado productos
            if (empty($request->arrayidproducto) || empty($request->arraycantidad)) {
                return back()->withErrors([
                    'error' => 'No se han agregado productos al traslado.'
                ])->withInput();
            }

            // Validación de cantidad por cada producto (solo > 0)
            foreach ($request->arrayidproducto as $index => $productoId) {
                $cantidad = $request->arraycantidad[$index];

                if ($cantidad <= 0) {
                    $inventario = InventarioAlmacen::where('producto_id', $productoId)
                        ->first();
                    $nombreProducto = $inventario ? $inventario->producto->nombre : "ID {$productoId}";

                    return back()->withErrors([
                        'error' => "La cantidad del producto '{$nombreProducto}' debe ser mayor a cero."
                    ])->withInput();
                }
            }

            // Crear traslado
            $traslado = Traslado::create([
                'origen_almacen_id' => $request->origen_almacen_id,
                'destino_almacen_id' => $request->destino_almacen_id,
                'fecha_hora' => $request->fecha_hora,
                'costo_envio' => $request->costo_envio,
                'user_id' => auth()->id(),
                'estado' => 'pendiente',
            ]);

            // Crear detalles del traslado
            foreach ($request->arrayidproducto as $index => $productoId) {
                $traslado->detalleTraslados()->create([
                    'producto_id' => $productoId,
                    'cantidad' => $request->arraycantidad[$index],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('traslados.index')
                ->with('success', 'Traslado creado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Ocurrió un error al crear el traslado.'
            ]);
        }
    }

    public function update(UpdateTrasladoRequest $request, Traslado $traslado)
    {
        DB::beginTransaction();

        try {
            // Validar que se hayan agregado productos
            if (empty($request->arrayidproducto) || empty($request->arraycantidad)) {
                return back()->withInput()->withErrors([
                    'error' => 'No se han agregado productos al traslado.'
                ]);
            }

            // Validación de cantidad por cada producto (solo > 0)
            foreach ($request->arrayidproducto as $index => $productoId) {
                $cantidad = $request->arraycantidad[$index] ?? 0;

                if ($cantidad <= 0) {
                    $inventario = InventarioAlmacen::where('producto_id', $productoId)->first();
                    $nombreProducto = $inventario ? $inventario->producto->nombre : "ID {$productoId}";

                    return back()->withInput()->withErrors([
                        'error' => "La cantidad del producto '{$nombreProducto}' debe ser mayor a cero."
                    ]);
                }
            }

            // Actualizar o crear segun sea el caso
            $traslado->update([
                'origen_almacen_id' => $request->origen_almacen_id ?? $traslado->origen_almacen_id,
                'destino_almacen_id' => $request->destino_almacen_id ?? $traslado->destino_almacen_id,
                'costo_envio' => $request->costo_envio ?? $traslado->costo_envio,
                'user_id' => auth()->id(),
            ]);

            //obtener arrays y control de null
            $detalleIdsFormulario = $request->arrayidproducto ?? [];
            $detalleCantidades = $request->arraycantidad ?? [];

            // detalle actual: clave = producto_id
            $detalleActual = $traslado->detalleTraslados->keyBy('producto_id');

            // recorrer productos enviados desde el formulario
            foreach ($detalleIdsFormulario as $index => $productoId) {
                //0 control de null o error
                $cantidad = $detalleCantidades[$index] ?? 0;

                if ($detalleActual->has($productoId)) {
                    // actualizar cantidad si cambió
                    $detalle = $detalleActual[$productoId];
                    if ($detalle->cantidad != $cantidad) {
                        DetalleTraslado::where('traslado_id', $traslado->id)
                            ->where('producto_id', $productoId)
                            ->update(['cantidad' => $cantidad, 'updated_at' => now()]);
                    }
                    $detalleActual->forget($productoId);
                } else {
                    // producto nuevo: crear detalle
                    $traslado->detalleTraslados()->create([
                        'producto_id' => $productoId,
                        'cantidad' => $cantidad,
                    ]);
                }
            }

            // eliminar productos que ya no están en el formulario
            foreach ($detalleActual as $detalleEliminado) {
                DetalleTraslado::where('traslado_id', $traslado->id)
                    ->where('producto_id', $detalleEliminado->producto_id)
                    ->delete();
            }

            DB::commit();

            return redirect()->route('traslados.index')
                ->with('success', 'Traslado actualizado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Ocurrió un error al actualizar el traslado: ' . $e->getMessage()]);
        }
    }

    public function checkStock(Request $request)
    {
        $productoIds = $request->producto_id;
        $almacenId = $request->almacen_id;
        $origenId = $request->origen_id;
        $destinoId = $request->destino_id;

        static $cacheProductos = [];
        static $cacheInventario = [];

        // array de ids que se consultaran
        if (!is_array($productoIds)) {
            $productoIds = [$productoIds];
        }

        // Cargar productos en cache
        $productos = Producto::select('id', 'stock_minimo', 'stock_maximo')
            ->whereIn('id', $productoIds)
            ->get()
            ->keyBy('id');

        //array de productos completos
        foreach ($productos as $p) {
            $cacheProductos[$p->id] = $p;
        }

        //almacenes a consultar
        $almacenes = array_filter([$almacenId, $origenId, $destinoId]);

        $inventarios = InventarioAlmacen::whereIn('producto_id', $productoIds)
            ->whereIn('almacen_id', $almacenes)
            ->get();

        //array de stocks de almacenes completo
        //index compuesto
        foreach ($inventarios as $inv) {
            $cacheInventario[$inv->producto_id . '_' . $inv->almacen_id] = $inv->stock;
        }

        $resultado = [];
        foreach ($productoIds as $productoId) {

            $producto = $cacheProductos[$productoId] ?? null;

            // metodo old
            if ($almacenId) {
                $stock = $cacheInventario[$productoId . '_' . $almacenId] ?? 0;

                $resultado[$productoId] = [
                    'stock' => $stock,
                    'min' => $producto?->stock_minimo ?? 0,
                    'max' => $producto?->stock_maximo ?? 0,
                ];
            }
            // modo origen/destino
            /*
            else {
                $stockOrigen = $cacheInventario[$productoId . '_' . $origenId] ?? 0;
                $stockDestino = $cacheInventario[$productoId . '_' . $destinoId] ?? 0;

                $resultado[$productoId] = [
                    'origen' => $stockOrigen,
                    'destino' => $stockDestino,
                    'min' => $producto?->stock_minimo ?? 0,
                    'max' => $producto?->stock_maximo ?? 0,
                ];
            }*/
        }
        // Si es un solo producto, devolver formato viejo
        if (count($resultado) === 1 && $almacenId) {
            return response()->json(reset($resultado));
        }

        return response()->json($resultado);
    }


    public function updateEstado(Request $request, Traslado $traslado)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_curso,completado,cancelado',
        ]);

        DB::beginTransaction();

        try {
            $nuevoEstado = $request->estado;
            $estadoActual = $traslado->estado;

            // solo procesar inventario si no está cancelado
            if ($estadoActual !== 'cancelado') {
                $this->procesarInventarioTraslado($traslado, $estadoActual, $nuevoEstado);
            }

            $traslado->estado = $nuevoEstado;
            $traslado->save();

            DB::commit();

            return back()->with('success', 'Estado actualizado correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    protected function procesarInventarioTraslado(Traslado $traslado, string $estadoActual, string $nuevoEstado)
    {
        $traslado->load('detalleTraslados.producto', 'origenAlmacen', 'destinoAlmacen', 'user');

        foreach ($traslado->detalleTraslados as $detalle) {

            // ── pendiente → en_curso: descontar stock origen ─────────────────
            if ($estadoActual === 'pendiente' && $nuevoEstado === 'en_curso') {

                $inventarioOrigen = InventarioAlmacen::where('producto_id', $detalle->producto_id)
                    ->where('almacen_id', $traslado->origen_almacen_id)
                    ->first();

                if (!$inventarioOrigen) {
                    throw new Exception(
                        "No existe inventario en el almacén origen para '{$detalle->producto?->nombre}'"
                    );
                }

                if ($inventarioOrigen->stock < $detalle->cantidad) {
                    throw new Exception(
                        "No hay stock suficiente para '{$detalle->producto?->nombre}'"
                    );
                }

                InventarioAlmacen::where('producto_id', $detalle->producto_id)
                    ->where('almacen_id', $traslado->origen_almacen_id)
                    ->update(['stock' => DB::raw("stock - {$detalle->cantidad}")]);
            }

            // ── en_curso → completado: sumar stock destino + registrar movimiento ──
            if ($estadoActual === 'en_curso' && $nuevoEstado === 'completado') {

                // Stock origen: ya fue decrementado en el paso anterior.
                // stock_final_origen  = stock actual de origen
                // stock_inicial_origen = stock_final_origen + cantidad
                $stockFinalOrigen   = InventarioAlmacen::where('producto_id', $detalle->producto_id)
                    ->where('almacen_id', $traslado->origen_almacen_id)
                    ->value('stock') ?? 0;
                $stockInicialOrigen = $stockFinalOrigen + $detalle->cantidad;

                // Stock destino: capturar ANTES de incrementar
                $existeDestino = InventarioAlmacen::where('producto_id', $detalle->producto_id)
                    ->where('almacen_id', $traslado->destino_almacen_id)
                    ->exists();

                if (!$existeDestino) {
                    InventarioAlmacen::create([
                        'producto_id' => $detalle->producto_id,
                        'almacen_id'  => $traslado->destino_almacen_id,
                        'stock'       => 0,
                    ]);
                }

                $stockInicialDestino = InventarioAlmacen::where('producto_id', $detalle->producto_id)
                    ->where('almacen_id', $traslado->destino_almacen_id)
                    ->value('stock') ?? 0;

                InventarioAlmacen::where('producto_id', $detalle->producto_id)
                    ->where('almacen_id', $traslado->destino_almacen_id)
                    ->update(['stock' => DB::raw("stock + {$detalle->cantidad}")]);

                $stockFinalDestino = $stockInicialDestino + $detalle->cantidad;

                Movimiento::create([
                    'tipo'                  => 'traslado',
                    'referencia_id'         => $traslado->id,
                    'referencia_texto'      => 'T-' . $traslado->id,
                    'producto_nombre'       => optional($detalle->producto)->nombre ?? '—',
                    'almacen_origen'        => optional($traslado->origenAlmacen)->nombre  ?? '—',
                    'almacen_destino'       => optional($traslado->destinoAlmacen)->nombre ?? '—',
                    'cantidad'              => $detalle->cantidad,
                    'cantidad_anterior'     => null,
                    'cantidad_nueva'        => null,
                    'stock_inicial_origen'  => $stockInicialOrigen,
                    'stock_final_origen'    => $stockFinalOrigen,
                    'stock_inicial_destino' => $stockInicialDestino,
                    'stock_final_destino'   => $stockFinalDestino,
                    'usuario'               => optional($traslado->user)->name ?? auth()->user()->name ?? '—',
                    'motivo'                => null,
                    'fecha_hora'            => $traslado->fecha_hora ?? now(),
                ]);
            }

            // ── en_curso → cancelado: restaurar stock origen ─────────────────
            if ($estadoActual === 'en_curso' && $nuevoEstado === 'cancelado') {

                $inventarioOrigen = InventarioAlmacen::where('producto_id', $detalle->producto_id)
                    ->where('almacen_id', $traslado->origen_almacen_id)
                    ->first();

                if (!$inventarioOrigen) {
                    InventarioAlmacen::create([
                        'producto_id' => $detalle->producto_id,
                        'almacen_id'  => $traslado->origen_almacen_id,
                        'stock'       => $detalle->cantidad,
                    ]);
                } else {
                    InventarioAlmacen::where('producto_id', $detalle->producto_id)
                        ->where('almacen_id', $traslado->origen_almacen_id)
                        ->update(['stock' => DB::raw("stock + {$detalle->cantidad}")]);
                }
            }
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $trasladoIds = $request->input('traslado_ids', []);
            $includeDetalles = filter_var($request->input('includeDetalles', true), FILTER_VALIDATE_BOOLEAN);

            $traslados = empty($trasladoIds)
                ? Traslado::with(['origenAlmacen', 'destinoAlmacen', 'user', 'detalleTraslados.producto'])
                ->orderBy('fecha_hora', 'desc')->get()
                : Traslado::with(['origenAlmacen', 'destinoAlmacen', 'user', 'detalleTraslados.producto'])
                ->whereIn('id', $trasladoIds)
                ->orderBy('fecha_hora', 'desc')->get();

            $filename = 'traslados_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(
                new TrasladosExport($traslados, $includeDetalles),
                $filename
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar traslados: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $trasladoIds = $request->input('traslado_ids', []);

            $includeDetalles = filter_var($request->input('includeDetalles', true), FILTER_VALIDATE_BOOLEAN);
            $includeCosto = filter_var($request->input('includeCosto', true), FILTER_VALIDATE_BOOLEAN);
            $includeUsuario = filter_var($request->input('includeUsuario', true), FILTER_VALIDATE_BOOLEAN);

            $traslados = empty($trasladoIds)
                ? Traslado::with(['origenAlmacen', 'destinoAlmacen', 'user', 'detalleTraslados.producto'])
                ->orderByRaw("
                    CASE estado
                        WHEN 'pendiente' THEN 1
                        WHEN 'en_curso' THEN 2
                        WHEN 'completado' THEN 3
                        WHEN 'cancelado' THEN 4
                        ELSE 5
                    END
                ")
                ->orderBy('fecha_hora', 'desc')
                ->get()
                : Traslado::with(['origenAlmacen', 'destinoAlmacen', 'user', 'detalleTraslados.producto'])
                ->whereIn('id', $trasladoIds)
                ->orderByRaw("
                    CASE estado
                        WHEN 'pendiente' THEN 1
                        WHEN 'en_curso' THEN 2
                        WHEN 'completado' THEN 3
                        WHEN 'cancelado' THEN 4
                        ELSE 5
                    END
                ")
                ->orderBy('fecha_hora', 'desc')
                ->get();

            $fechas = $traslados->pluck('fecha_hora');

            $totalProductos = $traslados
                ->flatMap->detalleTraslados
                ->sum('cantidad');

            $costoTotal = $includeCosto
                ? $traslados->sum('costo_envio')
                : null;

            $estados = $traslados->pluck('estado')->unique()->values();

            $resumen = [
                'total_traslados' => $traslados->count(),
                'fecha_inicio' => $fechas->min() ? \Carbon\Carbon::parse($fechas->min())->format('d/m/Y') : null,
                'fecha_fin' => $fechas->max() ? \Carbon\Carbon::parse($fechas->max())->format('d/m/Y') : null,
                'total_productos' => $totalProductos,
                'costo_total' => $costoTotal,
                'estados' => $estados,
            ];

            $pdf = Pdf::loadView('admin.traslado.pdf', [
                'title' => 'Reporte de Traslados',
                'date' => now()->format('d/m/Y H:i'),
                'traslados' => $traslados,
                'resumen' => $resumen,
                'includeDetalles' => $includeDetalles,
                'includeCosto' => $includeCosto,
                'includeUsuario' => $includeUsuario,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('traslados_' . now()->format('Y-m-d_H-i-s') . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar PDF: ' . $e->getMessage());
        }
    }
}
