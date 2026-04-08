<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movimiento;
use App\Exports\MovimientosExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MovimientoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-movimiento',       ['only' => ['index']]);
        $this->middleware('permission:exportar-movimientos', ['only' => ['exportExcel', 'exportPdf']]);
    }

    public function index(Request $request)
    {
        $busqueda   = $request->get('busqueda');
        $perPage    = $request->get('per_page', 10);
        $sort       = $request->get('sort', 'fecha_hora');
        $direction  = $request->get('direction', 'desc');
        $tipo       = $request->get('tipo');
        $almacen    = $request->get('almacen');
        $usuario    = $request->get('usuario');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin    = $request->get('fecha_fin');
        $producto   = $request->get('producto');

        if (!in_array($perPage,   [5, 10, 15, 20, 25])) $perPage   = 10;
        if (!in_array($direction, ['asc', 'desc']))      $direction = 'desc';

        $query = $this->buildQuery($request);

        $allowedSorts = ['fecha_hora', 'tipo', 'producto_nombre', 'cantidad', 'usuario', 'referencia_texto'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->latest('fecha_hora');
        }

        $movimientos = $query->paginate($perPage);

        // Resumen global
        $totalMovimientos = Movimiento::count();
        $totalVentas      = Movimiento::ventas()->distinct('referencia_id')->count('referencia_id');
        $totalTraslados   = Movimiento::traslados()->where('motivo', 'Traslado - Entrada')->distinct('referencia_id')->count('referencia_id');
        $totalAjustes     = Movimiento::ajustes()->count();

        // Opciones para selects de filtros
        $almacenesDisponibles = collect(
            Movimiento::distinct()->orderBy('almacen_origen')->pluck('almacen_origen')
        )->merge(
            Movimiento::distinct()->orderBy('almacen_destino')->whereNotNull('almacen_destino')->pluck('almacen_destino')
        )->unique()->sort()->filter()->values();

        $usuariosDisponibles = Movimiento::distinct()->orderBy('usuario')->pluck('usuario')->filter()->values();

        return view('admin.movimiento.index', compact(
            'movimientos',
            'busqueda',
            'perPage',
            'sort',
            'direction',
            'tipo',
            'almacen',
            'usuario',
            'fechaInicio',
            'fechaFin',
            'producto',
            'totalMovimientos',
            'totalVentas',
            'totalTraslados',
            'totalAjustes',
            'almacenesDisponibles',
            'usuariosDisponibles'
        ));
    }

    public function exportExcel(Request $request)
    {
        try {
            $movimientos = $this->buildQuery($request)->orderBy('fecha_hora', 'desc')->get();
            $filename    = 'movimientos_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new MovimientosExport($movimientos), $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar Excel: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $movimientos = $this->buildQuery($request)->orderBy('fecha_hora', 'desc')->get();

            $pdf = Pdf::loadView('admin.movimiento.pdf', [
                'movimientos' => $movimientos,
                'title'       => 'Historial de Movimientos',
                'date'        => now()->format('d/m/Y H:i'),
                'filtroTipo'  => $request->get('tipo'),
                'busqueda'    => $request->get('busqueda'),
            ])->setPaper('a4', 'landscape');

            return $pdf->download('movimientos_' . now()->format('Y-m-d_H-i-s') . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar PDF: ' . $e->getMessage());
        }
    }

    private function buildQuery(Request $request)
    {
        $busqueda    = $request->get('busqueda');
        $tipo        = $request->get('tipo');
        $almacen     = $request->get('almacen');
        $usuario     = $request->get('usuario');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin    = $request->get('fecha_fin');
        $producto    = $request->get('producto');

        $query = Movimiento::query();

        // Búsqueda general
        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('producto_nombre',   'like', "%{$busqueda}%")
                  ->orWhere('referencia_texto', 'like', "%{$busqueda}%")
                  ->orWhere('usuario',          'like', "%{$busqueda}%")
                  ->orWhere('almacen_origen',   'like', "%{$busqueda}%")
                  ->orWhere('almacen_destino',  'like', "%{$busqueda}%");
            });
        }

        // Filtro por producto
        if ($producto) {
            $query->where('producto_nombre', 'like', "%{$producto}%");
        }

        // Filtro por tipo
        if ($tipo && in_array($tipo, ['ajuste_stock', 'venta', 'traslado'])) {
            $query->where('tipo', $tipo);
        }

        // Filtro por almacén (busca en origen Y destino)
        if ($almacen) {
            $query->where(function ($q) use ($almacen) {
                $q->where('almacen_origen',  $almacen)
                  ->orWhere('almacen_destino', $almacen);
            });
        }

        // Filtro por usuario
        if ($usuario) {
            $query->where('usuario', $usuario);
        }

        // Filtro por fecha
        if ($fechaInicio) {
            $query->whereDate('fecha_hora', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->whereDate('fecha_hora', '<=', $fechaFin);
        }

        return $query;
    }
}