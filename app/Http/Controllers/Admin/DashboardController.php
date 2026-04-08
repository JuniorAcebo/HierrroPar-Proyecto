<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Traslado;
use App\Models\AjusteStock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
 
class DashboardController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-panel');
    }

    public function index()
    {
        $currentYear  = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $ventasHoy = $this->getVentasHoy();
 
        return view('admin.panel.index', [
            'ventasHoy' => $ventasHoy,
 
            // MÓDULO: VENTAS: KP1, KP2, KPI3
            'kpi_ventas' => $this->getKpisVentas($currentYear, $currentMonth),
 

            // MÓDULO: PRODUCTOS / INVENTARIO: KPI4, KPI5, KPI9
            'kpi_inventario' => $this->getKpisInventario($currentYear, $currentMonth),
 
            
            // MÓDULO: TRASLADOS: KPI8
            'kpi_traslados' => $this->getKpisTraslados($currentYear, $currentMonth),
 

            // MÓDULO: CLIENTES: KPI6
            'kpi_clientes' => $this->getKpisClientes($currentYear, $currentMonth),
 

            // MÓDULO: USUARIOS: KPI7
            'kpi_usuarios' => $this->getKpisUsuarios($currentYear, $currentMonth),
 
            // Meta info
            'currentYear'  => $currentYear,
            'currentMonth' => $currentMonth,
        ]);
    }

    private function getVentasHoy()
    {
         return Venta::where('estado', '!=', 'cancelada')
        ->whereDate('fecha_hora', now())
        ->sum('total');
    }
 
    // MÓDULO VENTAS
 
    private function getKpisVentas(int $year, int $month): array
    {
        return [
            'kpi1_flujo_caja'        => $this->kpi1FlujoCaja($year, $month),
            'kpi2_crecimiento_ventas' => $this->kpi2CrecimientoVentas($year, $month),
            'kpi3_tasa_cancelacion'  => $this->kpi3TasaCancelacion($year, $month),
 
            // Datos para gráfico de línea (Ventas vs Compras mensual)
            'grafico_flujo_anual'    => $this->graficoFlujoCajaAnual($year),
        ];
    }
 
    private function kpi1FlujoCaja(int $year, int $month): array
    {
        $ventas = Venta::whereYear('fecha_hora', $year)
            ->whereMonth('fecha_hora', $month)
            ->where('estado', '!=', 'cancelada')
            ->sum('total');
 
        $compras = Compra::whereYear('fecha_hora', $year)
            ->whereMonth('fecha_hora', $month)
            ->where('estado', '!=', 'cancelada')
            ->sum('total');
 
        $flujo_neto = $ventas - $compras;
        $meta       = 15000;
 
        return [
            'flujo_neto'   => round($flujo_neto, 2),
            'ventas'       => round($ventas, 2),
            'compras'      => round($compras, 2),
            'meta'         => $meta,
            'meta_cumplida' => $flujo_neto >= $meta,
            'progreso'     => $meta > 0 ? min(round(($flujo_neto / $meta) * 100, 1), 100) : 0,
        ];
    }
 
    private function kpi2CrecimientoVentas(int $year, int $month): array
    {
        $mesAnterior     = Carbon::create($year, $month, 1)->subMonth();
        $ventasActual    = Venta::whereYear('fecha_hora', $year)
            ->whereMonth('fecha_hora', $month)
            ->where('estado', '!=', 'cancelada')
            ->sum('total');
 
        $ventasAnterior  = Venta::whereYear('fecha_hora', $mesAnterior->year)
            ->whereMonth('fecha_hora', $mesAnterior->month)
            ->where('estado', '!=', 'cancelada')
            ->sum('total');
 
        $crecimiento = 0;
        if ($ventasAnterior > 0) {
            $crecimiento = (($ventasActual - $ventasAnterior) / $ventasAnterior) * 100;
        } elseif ($ventasActual > 0) {
            $crecimiento = 100;
        }
 
        $meta = 10;
 
        return [
            'porcentaje'    => round($crecimiento, 2),
            'ventas_actual'  => round($ventasActual, 2),
            'ventas_anterior' => round($ventasAnterior, 2),
            'meta'          => $meta,
            'meta_cumplida' => $crecimiento >= $meta,
            'progreso'      => $meta > 0 ? min(round(($crecimiento / $meta) * 100, 1), 100) : 0,
        ];
    }
 
    private function kpi3TasaCancelacion(int $year, int $month): array
    {
        $total      = Venta::whereYear('fecha_hora', $year)
            ->whereMonth('fecha_hora', $month)
            ->count();
 
        $canceladas = Venta::whereYear('fecha_hora', $year)
            ->whereMonth('fecha_hora', $month)
            ->where('estado', 'cancelada')
            ->count();
 
        $tasa = $total > 0 ? ($canceladas / $total) * 100 : 0;
        $meta = 5;
 
        return [
            'tasa'          => round($tasa, 2),
            'canceladas'    => $canceladas,
            'total_ventas'  => $total,
            'meta'          => $meta,
            'meta_cumplida' => $tasa <= $meta,
            'progreso'      => min(round(($tasa / $meta) * 100, 1), 100),
        ];
    }
 
    private function graficoFlujoCajaAnual(int $year): array
    {
        $ventasPorMes  = Venta::whereYear('fecha_hora', $year)
            ->where('estado', '!=', 'cancelada')
            ->select(DB::raw('MONTH(fecha_hora) as mes'), DB::raw('SUM(total) as total'))
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();
 
        $comprasPorMes = Compra::whereYear('fecha_hora', $year)
            ->where('estado', '!=', 'cancelada')
            ->select(DB::raw('MONTH(fecha_hora) as mes'), DB::raw('SUM(total) as total'))
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();
 
        $labels = [];
        $ventas = [];
        $compras = [];
 
        for ($i = 1; $i <= 12; $i++) {
            $labels[]  = ucfirst(Carbon::create()->month($i)->locale('es')->translatedFormat('F'));
            $ventas[]  = (float)($ventasPorMes[$i] ?? 0);
            $compras[] = (float)($comprasPorMes[$i] ?? 0);
        }
 
        return [
            'labels'  => $labels,
            'ventas'  => $ventas,
            'compras' => $compras,
        ];
    }
 
    // MÓDULO PRODUCTOS / INVENTARIO
 
    private function getKpisInventario(int $year, int $month): array
    {
        return [
            'kpi4_top5_productos'   => $this->kpi4Top5Productos($year),
            'kpi5_stock_minimo'     => $this->kpi5StockMinimo(),
            'kpi9_ajustes_inventario' => $this->kpi9AjustesInventario($year, $month),
        ];
    }
 
    private function kpi4Top5Productos(int $year): array
    {
        //nombre, stock mínimo, cantidad vendida, stock actual
        $productos = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', '!=', 'cancelada')
            ->whereYear('ventas.fecha_hora', $year)
            ->select(
                'productos.nombre',
                'productos.stock_minimo',
                DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'),
                DB::raw('COALESCE((SELECT SUM(stock) FROM inventario_almacenes WHERE producto_id = productos.id), 0) as stock_actual')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.stock_minimo')
            ->havingRaw('total_vendido > 0')
            ->orderByDesc('total_vendido')
            ->take(5)
            ->get();

        // Marcar cada producto si cumple o no:
        // stock_minimo = 0 → sin restricción → siempre cumple
        // stock_minimo > 0 → debe tener stock_actual >= stock_minimo
        $productos->each(function ($p) {
            $p->cumple = (int)$p->stock_minimo === 0
                || (int)$p->stock_actual >= (int)$p->stock_minimo;
        });

        $totalEvaluados = count($productos);
        $productosOk    = $productos->filter(fn($p) => $p->cumple)->count();
        $meta_cumplida  = $productosOk === $totalEvaluados;

        return [
            'productos'           => $productos,
            'labels'              => $productos->pluck('nombre')->toArray(),
            'cantidades'          => $productos->pluck('total_vendido')->toArray(),
            'meta_cumplida'       => $meta_cumplida,
            'productos_ok'        => $productosOk,
            'total_con_minimo'    => $totalEvaluados,
            'productos_en_riesgo' => $productos->filter(fn($p) => !$p->cumple)->count(),
        ];
    }

    private function kpi5StockMinimo(): array
    {
        // Total de productos activos (con o sin mínimo definido)
        $totalActivos = Producto::where('estado', 1)->count();

        // Solo productos con stock_minimo > 0 que estén por debajo de su mínimo
        $enRiesgo = Producto::select(
                'productos.id',
                'productos.nombre',
                'productos.stock_minimo',
                DB::raw('COALESCE(SUM(inventario_almacenes.stock), 0) as stock_actual')
            )
            ->where('productos.estado', 1)
            ->where('productos.stock_minimo', '>', 0)
            ->leftJoin('inventario_almacenes', 'productos.id', '=', 'inventario_almacenes.producto_id')
            ->groupBy('productos.id', 'productos.nombre', 'productos.stock_minimo')
            ->havingRaw('stock_actual < productos.stock_minimo')
            ->orderBy('stock_actual', 'asc')
            ->get();

        $cantidad = $enRiesgo->count();

        // Porcentaje sobre el TOTAL de productos activos (no solo los que tienen mínimo)
        // Ej: 1 crítico de 3 activos = 33.33%, no 100%
        $porcentaje = $totalActivos > 0 ? ($cantidad / $totalActivos) * 100 : 0;
        $meta = 5;

        return [
            'porcentaje'    => round($porcentaje, 2),
            'cantidad'      => $cantidad,
            'total_activos' => $totalActivos,
            'productos'     => $enRiesgo->take(10),
            'meta'          => $meta,
            'meta_cumplida' => $porcentaje <= $meta,
        ];
    }
 
    private function kpi9AjustesInventario(int $year, int $month): array
    {
        $cantidad = AjusteStock::whereYear('fecha_hora', $year)
            ->whereMonth('fecha_hora', $month)
            ->count();

        $meta = 10;

        // Últimos 8 ajustes del mes para la tabla
        $ultimos = AjusteStock::whereYear('fecha_hora', $year)
            ->whereMonth('fecha_hora', $month)
            ->join('productos', 'ajustes_stock.producto_id', '=', 'productos.id')
            ->join('users', 'ajustes_stock.user_id', '=', 'users.id')
            ->select(
                'ajustes_stock.fecha_hora',
                'productos.nombre as producto',
                'ajustes_stock.cantidad_anterior',
                'ajustes_stock.cantidad_nueva',
                'users.name as usuario'
            )
            ->orderByDesc('ajustes_stock.fecha_hora')
            ->take(8)
            ->get()
            ->map(function ($a) {
                return [
                    'fecha'    => Carbon::parse($a->fecha_hora)->format('d/m H:i'),
                    'producto' => $a->producto,
                    'usuario'  => $a->usuario,
                    'tipo'     => $a->cantidad_nueva > $a->cantidad_anterior ? 'aumento' : 'disminucion',
                    'anterior' => $a->cantidad_anterior,
                    'nueva'    => $a->cantidad_nueva,
                    'diferencia' => $a->cantidad_nueva - $a->cantidad_anterior,
                ];
            });

        return [
            'cantidad'      => $cantidad,
            'meta'          => $meta,
            'meta_cumplida' => $cantidad <= $meta,
            'progreso'      => min(round(($cantidad / $meta) * 100, 1), 100),
            'ultimos'       => $ultimos,
        ];
    }
 
    // MÓDULO TRASLADOS
 
    private function getKpisTraslados(int $year, int $month): array
    {
        return [
            'kpi8_traslados_cancelados' => $this->kpi8TrasladosCancelados($year, $month),
        ];
    }
 
    private function kpi8TrasladosCancelados(int $year, int $month): array
    {
        $total = Traslado::whereYear('fecha_hora', $year)
            ->whereMonth('fecha_hora', $month)
            ->count();
 
        $cancelados = Traslado::whereYear('fecha_hora', $year)
            ->whereMonth('fecha_hora', $month)
            ->where('estado', 'cancelado')
            ->count();
 
        $tasa = $total > 0 ? ($cancelados / $total) * 100 : 0;
        $meta = 5;
 
        return [
            'tasa'          => round($tasa, 2),
            'cancelados'    => $cancelados,
            'total'         => $total,
            'meta'          => $meta,
            'meta_cumplida' => $tasa <= $meta,
            'progreso'      => min(round(($tasa / $meta) * 100, 1), 100),
        ];
    }
 
    // MÓDULO CLIENTES
 
    private function getKpisClientes(int $year, int $month): array
    {
        return [
            'kpi6_clientes_frecuentes' => $this->kpi6ClientesFrecuentes(),
        ];
    }
 
    private function kpi6ClientesFrecuentes(): array
    {
        $totalActivos = Cliente::where('estado', 1)->count();
 
        $frecuentes = Cliente::where('clientes.estado', 1)
            ->join('ventas', 'clientes.id', '=', 'ventas.cliente_id')
            ->join('personas', 'clientes.persona_id', '=', 'personas.id')
            ->join('grupos_clientes', 'clientes.grupo_cliente_id', '=', 'grupos_clientes.id')
            ->whereNotIn('estado', ['cancelada', 'pendiente'])
            ->select(
                'clientes.id',
                'personas.nombre_completo',
                'grupos_clientes.nombre as grupo',
                DB::raw('COUNT(ventas.id) as total_compras'),
                DB::raw('SUM(ventas.total) as monto_total')
            )
            ->groupBy('clientes.id', 'personas.nombre_completo', 'grupos_clientes.nombre')
            ->havingRaw('total_compras > 5')
            ->orderByDesc('total_compras')
            ->get();
 
        $cantidad   = $frecuentes->count();
        $porcentaje = $totalActivos > 0 ? ($cantidad / $totalActivos) * 100 : 0;
        $meta       = 30;
 
        return [
            'porcentaje'    => round($porcentaje, 2),
            'cantidad'      => $cantidad,
            'total_activos' => $totalActivos,
            'meta'          => $meta,
            'meta_cumplida' => $porcentaje >= $meta,
            'progreso'      => min(round(($porcentaje / $meta) * 100, 1), 100),
            'lista'         => $frecuentes->take(8),
        ];
    }
 
    // MÓDULO USUARIOS
 
    private function getKpisUsuarios(int $year, int $month): array
    {
        return [
            'kpi7_productividad' => $this->kpi7ProductividadUsuarios($year, $month),
        ];
    }
 
    private function kpi7ProductividadUsuarios(int $year, int $month): array
    {
        $usuarios = User::where('estado', 'activo')->get();
 
        $detalle = $usuarios->map(function ($user) use ($year, $month) {
            $stats = Venta::where('user_id', $user->id)
                ->whereNotIn('estado', ['cancelada', 'pendiente'])
                ->whereYear('fecha_hora', $year)
                ->whereMonth('fecha_hora', $month)
                ->select(
                    DB::raw('COUNT(id) as cantidad_ventas'),
                    DB::raw('SUM(total) as monto_total')
                )
                ->first();
 
            $cantidad = $stats->cantidad_ventas ?? 0;
            $monto    = $stats->monto_total ?? 0;
            $cumple   = $cantidad >= 100 && $monto >= 6000;
 
            return [
                'nombre'          => $user->name,
                'cantidad_ventas' => $cantidad,
                'monto_total'     => round($monto, 2),
                'meta_cumplida'   => $cumple,
                'progreso_ventas' => min(round(($cantidad / 100) * 100, 1), 100),
                'progreso_monto'  => min(round(($monto / 6000) * 100, 1), 100),
            ];
        });
 
        $meta_cantidad = 100;
        $meta_monto    = 6000;
        $cumplen       = $detalle->where('meta_cumplida', true)->count();
        $total         = $usuarios->count();
        $porcentaje    = $total > 0 ? ($cumplen / $total) * 100 : 0;
 
        return [
            'detalle'        => $detalle,
            'cumplen_meta'   => $cumplen,
            'total_usuarios' => $total,
            'porcentaje'     => round($porcentaje, 1),
            'meta_cantidad'  => $meta_cantidad,
            'meta_monto'     => $meta_monto,
        ];
    }
}