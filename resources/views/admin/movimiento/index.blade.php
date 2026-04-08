@extends('admin.layouts.app')

@section('title', 'Movimientos')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="{{ asset('css/style_general.css') }}">
@endpush

@section('content')
@include('admin.layouts.partials.alert')

<div class="container-fluid px-4 py-4">

    {{-- ── Cabecera ─────────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Movimientos</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Movimientos</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- ── Tarjetas de resumen ─────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="background:rgba(79,193,255,.12);padding:.75rem;border-radius:10px;min-width:44px;text-align:center;">
                        <i class="fas fa-arrows-rotate text-info fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4 lh-1">{{ number_format($totalMovimientos) }}</div>
                        <div class="info-subtext mt-1">Total registros</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="background:rgba(26,188,156,.12);padding:.75rem;border-radius:10px;min-width:44px;text-align:center;">
                        <i class="fas fa-cart-shopping text-success fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4 lh-1">{{ number_format($totalVentas) }}</div>
                        <div class="info-subtext mt-1">Ventas completadas</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="background:rgba(52,152,219,.12);padding:.75rem;border-radius:10px;min-width:44px;text-align:center;">
                        <i class="fas fa-truck text-primary fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4 lh-1">{{ number_format($totalTraslados) }}</div>
                        <div class="info-subtext mt-1">Traslados completados</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="background:rgba(241,196,15,.12);padding:.75rem;border-radius:10px;min-width:44px;text-align:center;">
                        <i class="fas fa-sliders text-warning fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4 lh-1">{{ number_format($totalAjustes) }}</div>
                        <div class="info-subtext mt-1">Ajustes de stock</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tarjeta principal ───────────────────────────────────────────── --}}
    <div class="card-clean">

        <div class="card-header-clean d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="card-header-title">
                <i class="fas fa-list-alt"></i> Historial de Movimientos
            </div>
            @can('exportar-movimientos')
            <div class="d-flex gap-2">
                <form action="{{ route('movimientos.export.excel') }}" method="POST" class="d-inline" id="formExcelExport">
                    @csrf
                    <input type="hidden" name="busqueda"    id="exportBusqueda"    value="{{ $busqueda }}">
                    <input type="hidden" name="tipo"        id="exportTipo"        value="{{ $tipo }}">
                    <input type="hidden" name="almacen"     id="exportAlmacen"     value="{{ $almacen }}">
                    <input type="hidden" name="usuario"     id="exportUsuario"     value="{{ $usuario }}">
                    <input type="hidden" name="fecha_inicio" id="exportFechaInicio" value="{{ $fechaInicio }}">
                    <input type="hidden" name="fecha_fin"   id="exportFechaFin"    value="{{ $fechaFin }}">
                    <input type="hidden" name="producto"    id="exportProducto"    value="{{ $producto }}">
                    <button type="submit" class="btn btn-sm btn-outline-success" style="border-radius:6px;" onclick="syncExports()">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button>
                </form>
                <form action="{{ route('movimientos.export.pdf') }}" method="POST" class="d-inline" id="formPdfExport">
                    @csrf
                    <input type="hidden" name="busqueda"    id="exportBusquedaPdf"    value="{{ $busqueda }}">
                    <input type="hidden" name="tipo"        id="exportTipoPdf"        value="{{ $tipo }}">
                    <input type="hidden" name="almacen"     id="exportAlmacenPdf"     value="{{ $almacen }}">
                    <input type="hidden" name="usuario"     id="exportUsuarioPdf"     value="{{ $usuario }}">
                    <input type="hidden" name="fecha_inicio" id="exportFechaInicioPdf" value="{{ $fechaInicio }}">
                    <input type="hidden" name="fecha_fin"   id="exportFechaFinPdf"    value="{{ $fechaFin }}">
                    <input type="hidden" name="producto"    id="exportProductoPdf"    value="{{ $producto }}">
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:6px;" onclick="syncExports()">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </button>
                </form>
            </div>
            @endcan
        </div>

        {{-- ── Filtros ──────────────────────────────────────────────────── --}}
        <div class="search-container">
            <form action="{{ route('movimientos.index') }}" method="GET" id="searchForm">
                <div class="row g-2 align-items-center">

                    {{-- Búsqueda general --}}
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="padding:.4rem .75rem;">
                                <i class="fas fa-search text-muted small"></i>
                            </span>
                            <input type="text" name="busqueda" id="searchInput"
                                   class="form-control form-control-clean border-start-0 ps-0"
                                   placeholder="Referencia, usuario..."
                                   value="{{ $busqueda ?? '' }}">
                        </div>
                    </div>

                    {{-- Producto --}}
                    <div class="col-md-2">
                        <input type="text" name="producto" id="productoInput"
                               class="form-control form-control-sm"
                               placeholder="Producto..."
                               style="border-radius:6px;"
                               value="{{ $producto ?? '' }}">
                    </div>

                    {{-- Tipo --}}
                    <div class="col-md-2">
                        <select name="tipo" id="tipoFilter" class="form-select form-select-sm" style="border-radius:6px;">
                            <option value="">Todos los tipos</option>
                            <option value="venta"        {{ ($tipo ?? '') === 'venta'        ? 'selected' : '' }}>Ventas</option>
                            <option value="traslado"     {{ ($tipo ?? '') === 'traslado'     ? 'selected' : '' }}>Traslados</option>
                            <option value="ajuste_stock" {{ ($tipo ?? '') === 'ajuste_stock' ? 'selected' : '' }}>Ajustes de Stock</option>
                        </select>
                    </div>

                    {{-- Almacén --}}
                    <div class="col-md-2">
                        <select name="almacen" id="almacenFilter" class="form-select form-select-sm" style="border-radius:6px;">
                            <option value="">Todos los almacenes</option>
                            @foreach($almacenesDisponibles as $a)
                                <option value="{{ $a }}" {{ ($almacen ?? '') === $a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Usuario --}}
                    <div class="col-md-2">
                        <select name="usuario" id="usuarioFilter" class="form-select form-select-sm" style="border-radius:6px;">
                            <option value="">Todos los usuarios</option>
                            @foreach($usuariosDisponibles as $u)
                                <option value="{{ $u }}" {{ ($usuario ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Per page --}}
                    <div class="col-md-1">
                        <select name="per_page" id="per_page" class="form-select form-select-sm" style="border-radius:6px;">
                            @foreach([5, 10, 15, 20, 25] as $opt)
                                <option value="{{ $opt }}" {{ ($perPage ?? 10) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Segunda fila: fechas + limpiar --}}
                <div class="row g-2 align-items-center mt-1">
                    <div class="col-auto">
                        <label class="text-muted small me-1">Desde:</label>
                        <input type="date" name="fecha_inicio" id="fechaInicio"
                               class="form-control form-control-sm d-inline-block"
                               style="border-radius:6px;width:auto;"
                               value="{{ $fechaInicio ?? '' }}">
                    </div>
                    <div class="col-auto">
                        <label class="text-muted small me-1">Hasta:</label>
                        <input type="date" name="fecha_fin" id="fechaFin"
                               class="form-control form-control-sm d-inline-block"
                               style="border-radius:6px;width:auto;"
                               value="{{ $fechaFin ?? '' }}">
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius:6px;">
                            <i class="fas fa-undo me-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- ── Tabla ────────────────────────────────────────────────────── --}}
        <div class="card-body p-0" id="table-container">
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>
                                <button class="sort-btn {{ $sort == 'fecha_hora' ? 'active '.$direction : '' }}" data-column="fecha_hora">
                                    Fecha <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>
                            <th>
                                <button class="sort-btn {{ $sort == 'tipo' ? 'active '.$direction : '' }}" data-column="tipo">
                                    Tipo <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>
                            <th>
                                <button class="sort-btn {{ $sort == 'referencia_texto' ? 'active '.$direction : '' }}" data-column="referencia_texto">
                                    Referencia <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>
                            <th>
                                <button class="sort-btn {{ $sort == 'producto_nombre' ? 'active '.$direction : '' }}" data-column="producto_nombre">
                                    Producto <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>
                            <th>Almacén</th>
                            <th class="text-center">
                                <button class="sort-btn {{ $sort == 'cantidad' ? 'active '.$direction : '' }}" data-column="cantidad">
                                    Diferencia <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>
                            <th class="text-center">Stock Inicial → Final</th>
                            <th>
                                <button class="sort-btn {{ $sort == 'usuario' ? 'active '.$direction : '' }}" data-column="usuario">
                                    Usuario <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($movimientos as $m)
                        @php
                            // ── Determinar dirección del traslado ──────────────────
                            $esSalida  = $m->tipo === 'traslado' && str_contains($m->motivo ?? '', 'Salida');
                            $esEntrada = $m->tipo === 'traslado' && str_contains($m->motivo ?? '', 'Entrada');

                            // ── Stock a mostrar (una sola columna) ─────────────────
                            if ($esEntrada) {
                                $stockIni = $m->stock_inicial_destino;
                                $stockFin = $m->stock_final_destino;
                            } else {
                                $stockIni = $m->stock_inicial_origen;
                                $stockFin = $m->stock_final_origen;
                            }

                            // ── Diferencia ─────────────────────────────────────────
                            if ($m->tipo === 'ajuste_stock') {
                                $diff = ($m->cantidad_nueva ?? 0) - ($m->cantidad_anterior ?? 0);
                            } elseif ($m->tipo === 'venta') {
                                $diff = -$m->cantidad;
                            } elseif ($esSalida) {
                                $diff = -$m->cantidad;
                            } else {
                                $diff = $m->cantidad;
                            }
                            $cls = $diff > 0 ? 'badge-success' : ($diff < 0 ? 'badge-danger' : 'badge-warning');
                            $txt = ($diff > 0 ? '+' : '') . number_format($diff, 0);
                        @endphp
                        <tr>
                            {{-- Fecha --}}
                            <td>
                                <div class="fw-semibold small">{{ $m->fecha_hora->format('d/m/Y') }}</div>
                                <span class="info-subtext">{{ $m->fecha_hora->format('H:i') }}</span>
                            </td>

                            {{-- Tipo --}}
                            <td>
                                @if($m->tipo === 'venta')
                                    <span class="badge-pill badge-success" style="white-space:nowrap;">
                                        <i class="fas fa-cart-shopping me-1" style="font-size:.7rem;"></i>Venta
                                    </span>
                                @elseif($esSalida)
                                    <span class="badge-pill badge-danger" style="white-space:nowrap;">
                                        <i class="fas fa-arrow-up me-1" style="font-size:.7rem;"></i>Traslado Salida
                                    </span>
                                @elseif($esEntrada)
                                    <span class="badge-pill" style="background:#e8f4fd;color:#1a6fa8;border:1px solid #bee3f8;white-space:nowrap;">
                                        <i class="fas fa-arrow-down me-1" style="font-size:.7rem;"></i>Traslado Entrada
                                    </span>
                                @elseif($m->tipo === 'traslado')
                                    <span class="badge-pill" style="background:#e8f4fd;color:#1a6fa8;border:1px solid #bee3f8;white-space:nowrap;">
                                        <i class="fas fa-truck me-1" style="font-size:.7rem;"></i>Traslado
                                    </span>
                                @else
                                    <span class="badge-pill badge-warning" style="white-space:nowrap;">
                                        <i class="fas fa-sliders me-1" style="font-size:.7rem;"></i>Ajuste
                                    </span>
                                @endif
                            </td>

                            {{-- Referencia --}}
                            <td>
                                <span class="fw-semibold small font-monospace">{{ $m->referencia_texto ?? '—' }}</span>
                            </td>

                            {{-- Producto --}}
                            <td>
                                <div class="product-info">
                                    <div class="product-avatar" style="width:32px;height:32px;font-size:.72rem;">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="fw-semibold small">{{ $m->producto_nombre }}</div>
                                </div>
                            </td>

                            {{-- Almacén --}}
                            <td>
                                @if($m->tipo === 'traslado')
                                    <div class="small">
                                        <i class="fas fa-warehouse text-muted me-1" style="font-size:.7rem;"></i>
                                        {{ $m->almacen_origen ?? '—' }}
                                    </div>
                                    <div class="info-subtext">
                                        <i class="fas fa-arrow-right text-muted me-1" style="font-size:.65rem;"></i>
                                        {{ $m->almacen_destino ?? '—' }}
                                    </div>
                                @else
                                    <span class="small">{{ $m->almacen_origen ?? '—' }}</span>
                                @endif
                            </td>

                            {{-- Diferencia --}}
                            <td class="text-center">
                                <span class="badge-pill {{ $cls }}">{{ $txt }}</span>
                            </td>

                            {{-- Stock Inicial → Final (una sola columna) --}}
                            <td class="text-center">
                                @if($stockIni !== null)
                                    <span class="small text-muted">{{ number_format($stockIni, 0) }}</span>
                                    <i class="fas fa-arrow-right text-muted mx-1" style="font-size:.6rem;"></i>
                                    <span class="small fw-semibold">{{ number_format($stockFin, 0) }}</span>
                                @else
                                    <span class="info-subtext">—</span>
                                @endif
                            </td>

                            {{-- Usuario --}}
                            <td>
                                <div class="small">
                                    <i class="fas fa-user-circle text-muted me-1" style="font-size:.8rem;"></i>
                                    {{ $m->usuario }}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fs-2 d-block mb-2 opacity-25"></i>
                                <span class="small">Sin movimientos registrados</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr class="table-totals">
                            <td colspan="4" class="text-end">
                                <span class="totals-label">RESUMEN GLOBAL</span>
                            </td>
                            <td class="text-center">
                                <span class="totals-value">{{ number_format($totalMovimientos) }}</span>
                                <span class="totals-subtext">Registros</span>
                            </td>
                            <td class="text-center">
                                <span class="totals-value success">{{ $totalVentas }}</span>
                                <span class="totals-subtext">Ventas</span>
                            </td>
                            <td class="text-center">
                                <span class="totals-value" style="color:#1a6fa8;">{{ $totalTraslados }}</span>
                                <span class="totals-subtext">Traslados</span>
                            </td>
                            <td class="text-center">
                                <span class="totals-value text-warning">{{ $totalAjustes }}</span>
                                <span class="totals-subtext">Ajustes</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="p-3 d-flex justify-content-between align-items-center border-top">
                <div class="text-muted extra-small">
                    @if($movimientos->total() > 0)
                        Mostrando {{ $movimientos->firstItem() }} – {{ $movimientos->lastItem() }}
                        de {{ $movimientos->total() }} registros
                    @else
                        Sin registros
                    @endif
                </div>
                <div>
                    {{ $movimientos->appends([
                        'busqueda'    => $busqueda,
                        'per_page'    => $perPage,
                        'sort'        => $sort,
                        'direction'   => $direction,
                        'tipo'        => $tipo,
                        'almacen'     => $almacen,
                        'usuario'     => $usuario,
                        'fecha_inicio'=> $fechaInicio,
                        'fecha_fin'   => $fechaFin,
                        'producto'    => $producto,
                    ])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    let debounceTimer;
    const tableContainer = document.getElementById('table-container');

    function syncExports() {
        const fields = {
            busqueda:    ['exportBusqueda',    'exportBusquedaPdf'],
            tipo:        ['exportTipo',        'exportTipoPdf'],
            almacen:     ['exportAlmacen',     'exportAlmacenPdf'],
            usuario:     ['exportUsuario',     'exportUsuarioPdf'],
            fecha_inicio:['exportFechaInicio', 'exportFechaInicioPdf'],
            fecha_fin:   ['exportFechaFin',    'exportFechaFinPdf'],
            producto:    ['exportProducto',    'exportProductoPdf'],
        };
        const sources = {
            busqueda:    () => document.getElementById('searchInput')?.value  ?? '',
            tipo:        () => document.getElementById('tipoFilter')?.value   ?? '',
            almacen:     () => document.getElementById('almacenFilter')?.value ?? '',
            usuario:     () => document.getElementById('usuarioFilter')?.value ?? '',
            fecha_inicio:() => document.getElementById('fechaInicio')?.value  ?? '',
            fecha_fin:   () => document.getElementById('fechaFin')?.value     ?? '',
            producto:    () => document.getElementById('productoInput')?.value ?? '',
        };
        Object.keys(fields).forEach(key => {
            const val = sources[key]();
            fields[key].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = val;
            });
        });
    }

    function fetchMovimientos(url = null) {
        if (!url) {
            const params = new URLSearchParams(window.location.search);
            params.set('busqueda',     document.getElementById('searchInput')?.value  ?? '');
            params.set('tipo',         document.getElementById('tipoFilter')?.value   ?? '');
            params.set('almacen',      document.getElementById('almacenFilter')?.value ?? '');
            params.set('usuario',      document.getElementById('usuarioFilter')?.value ?? '');
            params.set('fecha_inicio', document.getElementById('fechaInicio')?.value  ?? '');
            params.set('fecha_fin',    document.getElementById('fechaFin')?.value     ?? '');
            params.set('producto',     document.getElementById('productoInput')?.value ?? '');
            params.set('per_page',     document.getElementById('per_page')?.value     ?? '10');
            url = `{{ route('movimientos.index') }}?${params.toString()}`;
        }

        tableContainer.style.opacity = '0.5';
        syncExports();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newContainer = doc.getElementById('table-container');
                if (newContainer) tableContainer.innerHTML = newContainer.innerHTML;
                tableContainer.style.opacity = '1';
                window.history.pushState({}, '', url);
                initEvents();
            })
            .catch(() => { tableContainer.style.opacity = '1'; });
    }

    function initEvents() {
        const searchInput  = document.getElementById('searchInput');
        const productoInput= document.getElementById('productoInput');
        const tipoFilter   = document.getElementById('tipoFilter');
        const almacenFilter= document.getElementById('almacenFilter');
        const usuarioFilter= document.getElementById('usuarioFilter');
        const fechaInicio  = document.getElementById('fechaInicio');
        const fechaFin     = document.getElementById('fechaFin');
        const perPage      = document.getElementById('per_page');
        const sortBtns     = document.querySelectorAll('.sort-btn');

        // Debounce para texto
        [searchInput, productoInput].forEach(el => {
            el?.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchMovimientos(), 350);
            });
        });

        // Inmediato para selects y fechas
        [tipoFilter, almacenFilter, usuarioFilter, fechaInicio, fechaFin, perPage].forEach(el => {
            el?.addEventListener('change', () => fetchMovimientos());
        });

        // Ordenamiento
        sortBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const col     = this.dataset.column;
                const params  = new URLSearchParams(window.location.search);
                const curSort = params.get('sort');
                const curDir  = params.get('direction') || 'asc';
                params.set('sort',      col);
                params.set('direction', (curSort === col && curDir === 'asc') ? 'desc' : 'asc');
                fetchMovimientos(`{{ route('movimientos.index') }}?${params.toString()}`);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initEvents();
        syncExports();
    });
</script>
@endpush