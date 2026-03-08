<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Http\Requests\StoreAjusteStockRequest;
use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\InventarioAlmacen;
use App\Models\Marca;
use App\Models\TipoUnidad;
use App\Models\Producto;
use App\Models\AjusteStock;
use App\Models\DetalleCompra;
use App\Models\DetalleVenta;
use App\Models\DetalleTraslado;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosExport;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductoController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:ver-producto', ['only' => ['index']]);
        $this->middleware('permission:crear-producto', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-producto', ['only' => ['edit', 'update']]);
        $this->middleware('permission:update-estado-producto', ['only' => ['updateEstado']]);
        $this->middleware('permission:ajustar-stock-producto', ['only' => ['createAjuste', 'storeAjuste','checkStock']]);
        $this->middleware('permission:ver-historial-stock-producto', ['only' => ['historialAjustes']]);
        $this->middleware('permission:exportar-productos', ['only' => ['exportExcel', 'exportPdf']]);
    }
    
    // muestra el listado de productos con filtros, ordenamiento y resumen de stock
    public function index(Request $request)
    {
        $busqueda = $request->get('busqueda');
        $perPage  = $request->get('per_page', 10);
        $sort     = $request->get('sort', 'nombre');
        $direction = $request->get('direction', 'asc');

        if (!in_array($perPage, [5, 10, 15, 20, 25])) $perPage = 10;
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'asc';

        $query = Producto::with([
            'marca',
            'categoria',
            'tipoUnidad',
            'inventarioAlmacenes.almacen'
        ])
            ->withSum('inventarioAlmacenes as stock_total', 'stock');

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('codigo', 'like', "%{$busqueda}%")
                    ->orWhere('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('descripcion', 'like', "%{$busqueda}%")
                    ->orWhereHas('categoria', function ($sub) use ($busqueda) {
                        $sub->where('nombre', 'like', "%{$busqueda}%");
                    });
            });
        }

        switch ($sort) {
            case 'categoria':
                $query->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                    ->select('productos.*')
                    ->orderBy('categorias.nombre', $direction);
                break;
            case 'stock_total':
                $query->orderBy('stock_total', $direction);
                break;
            case 'precio_venta':
            case 'nombre':
            case 'estado':
                $query->orderBy($sort, $direction);
                break;
            default:
                $query->latest();
                break;
        }

        $productos = $query->paginate($perPage);

        $totalStockGlobal = DB::table('inventario_almacenes')->sum('stock');

        $productosActivos = Producto::where('estado', 1)->count();


        $bajoStockCount = Producto::join('inventario_almacenes', 'productos.id', '=', 'inventario_almacenes.producto_id')
            ->select('productos.id', 'productos.stock_minimo')
            ->groupBy('productos.id', 'productos.stock_minimo')
            ->havingRaw('SUM(inventario_almacenes.stock) < productos.stock_minimo')
            ->get()
            ->count();


        $excesoStockCount = Producto::join('inventario_almacenes', 'productos.id', '=', 'inventario_almacenes.producto_id')
            ->select('productos.id', 'productos.stock_maximo')
            ->groupBy('productos.id', 'productos.stock_maximo')
            ->havingRaw('productos.stock_maximo > 0 AND SUM(inventario_almacenes.stock) > productos.stock_maximo')
            ->get()
            ->count();

        $almacenes = Almacen::where('estado', true)->get();

        return view('admin.producto.index', compact(
            'productos',
            'busqueda',
            'perPage',
            'almacenes',
            'sort',
            'direction',
            'totalStockGlobal',
            'productosActivos',
            'bajoStockCount',
            'excesoStockCount'
        ));
    }

    // muestra el formulario para crear un nuevo producto
    public function create()
    {
        $marcas = Marca::all();
        $tipounidades = TipoUnidad::all();
        $categorias = Categoria::all();

        return view('admin.producto.create', compact('marcas', 'tipounidades', 'categorias'));
    }

    // guarda un nuevo producto y lo inicializa en todos los almacenes
    public function store(StoreProductoRequest $request)
    {
        DB::beginTransaction();
        try {
            $producto = Producto::create([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio_compra' => $request->precio_compra,
                'precio_venta' => $request->precio_venta,
                'stock_minimo' => $request->stock_minimo,
                'stock_maximo' => $request->stock_maximo,
                'marca_id' => $request->marca_id,
                'tipo_unidad_id' => $request->tipo_unidad_id,
                'categoria_id' => $request->categoria_id,
                'estado' => true,
            ]);

            $almacenes = Almacen::where('estado', true)->get();
            foreach ($almacenes as $almacen) {
                $producto->inventarioAlmacenes()->create([
                    'almacen_id' => $almacen->id,
                    'stock' => 0
                ]);
            }

            DB::commit();
            return redirect()->route('productos.index')->with('success', 'Producto creado y agregado a todos los almacenes.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al crear producto: ' . $e->getMessage());
        }
    }

    // muestra el formulario de edición de un producto existente
    public function edit(Producto $producto)
    {
        $marcas = Marca::all();
        $tipounidades = TipoUnidad::all();
        $categorias = Categoria::all();

        return view('admin.producto.edit', compact('producto', 'marcas', 'tipounidades', 'categorias'));
    }

    // actualiza los datos de un producto
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        try {

            $producto->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio_compra' => $request->precio_compra,
                'precio_venta' => $request->precio_venta,
                'stock_minimo' => $request->stock_minimo,
                'stock_maximo' => $request->stock_maximo,
                'marca_id' => $request->marca_id,
                'tipo_unidad_id' => $request->tipo_unidad_id,
                'categoria_id' => $request->categoria_id,
            ]);

            return redirect()->route('productos.index')
                ->with('success', 'Producto actualizado correctamente.');
        } catch (\Exception $e) {

            return back()->withInput()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    // cambia el estado activo/inactivo de un producto
    public function updateEstado(Producto $producto)
    {
        $producto->estado = !$producto->estado;
        $producto->save();
        return redirect()->route('productos.index')
            ->with('success', $producto->estado ? 'Producto activado correctamente.' : 'Producto desactivado correctamente.');
    }

    // muestra el formulario para realizar un ajuste manual de stock
    public function createAjuste(Request $request)
    {
        $productos = Producto::where('estado', true)->get();
        $almacenes = Almacen::where('estado', true)->get();

        $productoSeleccionado = old('producto_id', $request->producto_id);

        return view('admin.producto.create_ajuste', compact(
            'productos',
            'almacenes',
            'productoSeleccionado'
        ));
    }

    // procesa y registra un ajuste manual de stock
    public function storeAjuste(StoreAjusteStockRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {

                // validar cantidad mínima según tipo de ajuste
                if (in_array($request->tipo_ajuste, ['sumar', 'restar']) && $request->cantidad <= 0) {
                    throw ValidationException::withMessages([
                        'cantidad' => "La cantidad debe ser mayor que 0 para esta operación."
                    ]);
                }

                if (intval($request->cantidad) != $request->cantidad) {
                    throw ValidationException::withMessages([
                        'cantidad' => "La cantidad debe ser un número entero."
                    ]);
                }

                if ($request->tipo_ajuste === 'fijar' && $request->cantidad < 0) {
                    throw ValidationException::withMessages([
                        'cantidad' => "El stock a fijar no puede ser negativo."
                    ]);
                }

                // Obtener inventario bloqueando la fila
                $inventario = InventarioAlmacen::where('producto_id', $request->producto_id)
                    ->where('almacen_id', $request->almacen_id)
                    ->lockForUpdate()
                    ->first();

                $cantidadAnterior = $inventario ? $inventario->stock : 0;
                $nuevaCantidad = $cantidadAnterior;

                switch ($request->tipo_ajuste) {
                    case 'sumar':
                        $nuevaCantidad += $request->cantidad;
                        break;

                    case 'restar':
                        if ($request->cantidad > $cantidadAnterior) {
                            throw ValidationException::withMessages([
                                'cantidad' => "No se puede restar {$request->cantidad}. Stock disponible: {$cantidadAnterior}."
                            ]);
                        }
                        $nuevaCantidad -= $request->cantidad;
                        break;

                    case 'fijar':
                        $nuevaCantidad = $request->cantidad;
                        break;
                }

                if ($nuevaCantidad < 0) {
                    throw ValidationException::withMessages([
                        'cantidad' => "El stock resultante no puede ser negativo."
                    ]);
                }

                if ($inventario) {
                    InventarioAlmacen::where('producto_id', $request->producto_id)
                        ->where('almacen_id', $request->almacen_id)
                        ->update(['stock' => $nuevaCantidad, 'updated_at' => now()]);
                } else {
                    InventarioAlmacen::create([
                        'producto_id' => $request->producto_id,
                        'almacen_id'  => $request->almacen_id,
                        'stock'       => $nuevaCantidad
                    ]);
                }

                // Guardar ajuste histórico
                AjusteStock::create([
                    'producto_id'       => $request->producto_id,
                    'almacen_id'        => $request->almacen_id,
                    'user_id'           => auth()->id(),
                    'cantidad_anterior' => $cantidadAnterior,
                    'cantidad_nueva'    => $nuevaCantidad,
                    'motivo'            => $request->motivo ?? 'Ajuste manual desde menú'
                ]);
            });

            return redirect()->route('productos.historialAjustes')
                ->with('success', 'Stock ajustado correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()->with('error', 'Error al ajustar stock: ' . $e->getMessage());
        }
    }

    // devuelve el stock actual de un producto en un almacén específico
    public function checkStock(Request $request)
    {
        $producto = Producto::find($request->producto_id);

        if (!$producto) {
            return response()->json([
                'stock' => 0,
                'min'   => 0,
                'max'   => 0
            ]);
        }

        $inventario = InventarioAlmacen::where('producto_id', $request->producto_id)
            ->where('almacen_id', $request->almacen_id)
            ->first();

        $stock = $inventario ? $inventario->stock : 0;

        return response()->json([
            'stock' => $stock,
            'min'   => $producto->stock_minimo,
            'max'   => $producto->stock_maximo
        ]);
    }

    // muestra el historial de ajustes de stock con filtros y resumen
    public function historialAjustes(Request $request)
    {
        $busqueda = $request->get('busqueda');
        $perPage  = $request->get('per_page', 10);
        $sort     = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if (!in_array($perPage, [5, 10, 15, 20, 25])) $perPage = 10;
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'desc';

        $query = AjusteStock::with(['producto', 'almacen', 'user']);

        if ($busqueda) {
            $query->whereHas('producto', function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('codigo', 'like', "%{$busqueda}%");
            });
        }

        switch ($sort) {
            case 'producto':
                $query->join('productos', 'ajustes_stock.producto_id', '=', 'productos.id')
                    ->select('ajustes_stock.*')
                    ->orderBy('productos.nombre', $direction);
                break;

            case 'usuario':
                $query->join('users', 'ajustes_stock.user_id', '=', 'users.id')
                    ->select('ajustes_stock.*')
                    ->orderBy('users.name', $direction);
                break;

            case 'almacen':
                $query->join('almacenes', 'ajustes_stock.almacen_id', '=', 'almacenes.id')
                    ->select('ajustes_stock.*')
                    ->orderBy('almacenes.nombre', $direction);
                break;

            default:
                $query->orderBy($sort, $direction);
                break;
        }

        $ajustes = $query->paginate($perPage);

        $resumen = AjusteStock::query();

        if ($busqueda) {
            $resumen->whereHas('producto', function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('codigo', 'like', "%{$busqueda}%");
            });
        }

        $ajustesPositivos = (clone $resumen)
            ->whereColumn('cantidad_nueva', '>', 'cantidad_anterior')
            ->count();

        $ajustesNegativos = (clone $resumen)
            ->whereColumn('cantidad_nueva', '<', 'cantidad_anterior')
            ->count();

        return view('admin.producto.historial_ajustes', compact(
            'ajustes',
            'busqueda',
            'perPage',
            'sort',
            'direction',
            'ajustesPositivos',
            'ajustesNegativos'
        ));
    }

    //Exporta el listado de productos a Excel
    public function exportExcel(Request $request)
    {
        try {
            $productIds = $request->input('product_ids', []);
            $includePrices = filter_var($request->input('includePrices', true), FILTER_VALIDATE_BOOLEAN);
            $includeStock = filter_var($request->input('includeStock', true), FILTER_VALIDATE_BOOLEAN);
            $includeAllDetails = filter_var($request->input('includeAllDetails', true), FILTER_VALIDATE_BOOLEAN);

            // Obtener almacenes si se incluye stock
            $almacenes = $includeStock
                ? Almacen::where('estado', true)
                    ->orderBy('nombre')
                    ->get(['id', 'nombre'])
                : collect();

            // Consulta base correcta
            $query = Producto::with([
                    'marca',
                    'categoria',
                    'tipoUnidad',
                    'inventarioAlmacenes' // 👈 ESTA ES LA RELACIÓN CORRECTA
                ])
                ->withSum('inventarioAlmacenes as stock_total', 'stock');

            // Si se seleccionaron productos específicos
            if (!empty($productIds)) {
                $query->whereIn('id', $productIds);
            }

            $productos = $query->get();

            $filename = 'productos_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(
                new ProductosExport(
                    $productos,
                    $includePrices,
                    $includeStock,
                    $includeAllDetails,
                    $almacenes
                ),
                $filename
            );

        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar productos: ' . $e->getMessage());
        }
    }

    //Exporta el historial de movimientos de productos a PDF
    public function exportPdf(Request $request)
    {
        try {
            $productIds = $request->input('product_ids', []);

            // Si no hay selección, tomar todos
            $ids = empty($productIds)
                ? Producto::pluck('id')->toArray()
                : $productIds;

            $movements = [];

            // ── 1. AJUSTES DE STOCK ──────────────────────────────────────
            AjusteStock::with(['producto', 'almacen', 'user'])
                ->whereIn('producto_id', $ids)
                ->orderBy('created_at', 'desc')
                ->get()
                ->each(function ($a) use (&$movements) {
                    $diff = $a->cantidad_nueva - $a->cantidad_anterior;
                    $movements[] = [
                        'fecha'     => \Carbon\Carbon::parse($a->created_at)->format('d/m/Y H:i'),
                        'tipo'      => 'Ajuste Stock',
                        'producto'  => $a->producto->nombre ?? '-',
                        'almacen'   => $a->almacen->nombre ?? '-',
                        'cant_ant'  => $a->cantidad_anterior,
                        'cant_nva'  => $a->cantidad_nueva,
                        'diferencia' => ($diff >= 0 ? '+' : '') . $diff,
                        'referencia' => $a->motivo ?? '-',
                        'usuario'   => $a->user->name ?? '-',
                    ];
                });

            // ── 2. VENTAS ────────────────────────────────────────────────
            DetalleVenta::with(['producto', 'venta.user.almacen'])
                ->whereIn('producto_id', $ids)
                ->get()
                ->each(function ($d) use (&$movements) {
                    $movements[] = [
                        'fecha'     => \Carbon\Carbon::parse($d->venta->fecha_hora ?? $d->venta->created_at)->format('d/m/Y H:i'),
                        'tipo'      => 'Venta',
                        'producto'  => $d->producto->nombre ?? '-',
                        'almacen'   => optional(optional($d->venta->user)->almacen)->nombre ?? '-',
                        'cant_ant'  => '-',
                        'cant_nva'  => '-',
                        'diferencia' => '-' . $d->cantidad,
                        'referencia' => $d->venta->numero_comprobante ?? ('V-' . $d->venta_id),
                        'usuario'   => optional($d->venta->user)->name ?? '-',
                    ];
                });

            // ── 3. COMPRAS ───────────────────────────────────────────────
            // Ajusta las relaciones según tu modelo Compra
            DetalleCompra::with(['producto', 'compra.user'])
                ->whereIn('producto_id', $ids)
                ->get()
                ->each(function ($d) use (&$movements) {
                    $movements[] = [
                        'fecha'     => \Carbon\Carbon::parse($d->compra->fecha_hora ?? $d->compra->created_at)->format('d/m/Y H:i'),
                        'tipo'      => 'Compra',
                        'producto'  => $d->producto->nombre ?? '-',
                        'almacen'   => optional(optional($d->compra->user)->almacen)->nombre ?? '-',
                        'cant_ant'  => '-',
                        'cant_nva'  => '-',
                        'diferencia' => '+' . $d->cantidad,
                        'referencia' => $d->compra->numero_comprobante ?? ('C-' . $d->compra_id),
                        'usuario'   => optional($d->compra->user)->name ?? '-',
                    ];
                });

            // ── 4. TRASLADOS ─────────────────────────────────────────────
            DetalleTraslado::with(['producto', 'traslado.origenAlmacen', 'traslado.destinoAlmacen', 'traslado.user'])
                ->whereIn('producto_id', $ids)
                ->get()
                ->each(function ($d) use (&$movements) {
                    $origen  = optional($d->traslado->origenAlmacen)->nombre ?? '-';
                    $destino = optional($d->traslado->destinoAlmacen)->nombre ?? '-';
                    $movements[] = [
                        'fecha'     => \Carbon\Carbon::parse($d->traslado->fecha_hora ?? $d->traslado->created_at)->format('d/m/Y H:i'),
                        'tipo'      => 'Traslado',
                        'producto'  => $d->producto->nombre ?? '-',
                        'almacen'   => "{$origen} → {$destino}",
                        'cant_ant'  => '-',
                        'cant_nva'  => '-',
                        'diferencia' => '±' . $d->cantidad,
                        'referencia' => 'T-' . $d->traslado_id,
                        'usuario'   => optional($d->traslado->user)->name ?? '-',
                    ];
                });

            // Ordenar por fecha descendente
            usort($movements, fn ($a, $b) => strcmp($b['fecha'], $a['fecha']));

            $headings = ['Fecha', 'Tipo', 'Producto', 'Almacén / Ruta', 'Cant. Anterior', 'Cant. Nueva', 'Diferencia', 'Referencia', 'Usuario'];
            $rows = array_map(fn ($m) => array_values($m), $movements);

            $pdf = Pdf::loadView('admin.producto.pdf', [
                'title'    => 'Historial de Movimientos',
                'date'     => now()->format('d/m/Y H:i'),
                'count'    => count($movements),
                'headings' => $headings,
                'rows'     => $rows,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('historial-productos_' . now()->format('Y-m-d_H-i-s') . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar historial PDF: ' . $e->getMessage());
        }
    }
}