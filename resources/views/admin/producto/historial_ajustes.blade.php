@extends('admin.layouts.app')

@section('title', 'Historial de Ajustes')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/style_general.css') }}">
@endpush

@section('content')
@include('admin.layouts.partials.alert')

<div class="container-fluid px-4 py-4">

    <div class="page-header">
        <div>
            <h1 class="page-title">Historial de Ajustes</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('productos.index') }}" class="text-decoration-none text-muted">Productos</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Historial de Ajustes</li>
                </ol>
            </nav>
        </div>
        @can('ajustar-stock')
        <a href="{{ route('productos.createAjuste') }}" class="btn-create">
            <i class="fas fa-plus"></i> Nuevo Ajuste
        </a>
        @endcan
    </div>

    <div class="card-clean">
        <div class="card-header-clean">
            <div class="card-header-title">
                <i class="fas fa-history"></i> Historial de Ajustes de Stock
            </div>
        </div>

        <div class="search-container">
            <form action="{{ route('productos.historialAjustes') }}" method="GET" id="searchForm">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="padding: 0.4rem 0.75rem;">
                                <i class="fas fa-search text-muted small"></i>
                            </span>
                            <input type="text" name="busqueda" class="form-control form-control-clean border-start-0 ps-0" placeholder="Buscar producto..." value="{{ $busqueda ?? '' }}" id="searchInput">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <label for="per_page" class="me-2 text-muted small">Mostrar:</label>
                            <select name="per_page" id="per_page" class="form-select form-select-sm w-auto" style="border-radius: 6px;">
                                @foreach([5, 10, 15, 20, 25] as $option)
                                <option value="{{ $option }}" {{ ($perPage ?? 10) == $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5 text-end">
                        @if(request()->hasAny(['busqueda', 'per_page']))
                        <a href="{{ route('productos.historialAjustes') }}" class="btn btn-outline-secondary btn-sm me-2" style="border-radius: 6px;">
                            <i class="fas fa-undo me-1"></i> Limpiar Filtros
                        </a>
                        @endif

                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0" id="table-container">
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Almacén</th>
                            <th>Usuario</th>
                            <th class="text-center">Cantidad Anterior</th>
                            <th class="text-center">Cantidad Nueva</th>
                            <th class="text-center">Diferencia</th>
                            <th>Motivo</th>
                            <th>Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ajustes as $ajuste)
                        @php
                        $diferencia = $ajuste->cantidad_nueva - $ajuste->cantidad_anterior;
                        @endphp
                        <tr>
                            <td>
                                <div class="product-info">
                                    <div class="product-avatar">
                                        <i class="fas fa-box small"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $ajuste->producto->nombre ?? '—' }}</div>
                                        <span class="info-subtext">Cód: {{ $ajuste->producto->codigo ?? '—' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-warehouse me-1 small"></i>
                                    {{ $ajuste->almacen->nombre ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <div class="small">
                                    <div class="fw-semibold">{{ $ajuste->user->name ?? '—' }}</div>
                                    <span class="info-subtext">{{ $ajuste->user->email ?? '' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge-pill badge-secondary" style="background:#e2e8f0; color:#475569;">
                                    {{ $ajuste->cantidad_anterior }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge-pill {{ $ajuste->cantidad_nueva <= 10 ? 'badge-danger' : 'badge-success' }}">
                                    {{ $ajuste->cantidad_nueva }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($diferencia > 0)
                                <span class="badge-pill badge-success">
                                    <i class="fas fa-arrow-up me-1 small"></i>+{{ $diferencia }}
                                </span>
                                @elseif($diferencia < 0) <span class="badge-pill badge-danger">
                                    <i class="fas fa-arrow-down me-1 small"></i>{{ $diferencia }}
                                    </span>
                                    @else
                                    <span class="badge-pill" style="background:#e2e8f0; color:#475569;">
                                        <i class="fas fa-minus me-1 small"></i>0
                                    </span>
                                    @endif
                            </td>
                            <td>
                                <span class="small text-muted" style="max-width: 200px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $ajuste->motivo }}">
                                    {{ $ajuste->motivo ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <div class="small">
                                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($ajuste->fecha_hora)->format('d/m/Y') }}</div>
                                    <span class="info-subtext">{{ \Carbon\Carbon::parse($ajuste->fecha_hora)->format('H:i:s') }}</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No hay ajustes registrados
                                    @if(request('busqueda'))
                                    para "<strong>{{ request('busqueda') }}</strong>"
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr class="table-totals">
                            <td colspan="5" class="text-end">
                                <span class="totals-label">RESUMEN</span>
                            </td>

                            <td class="text-center">
                                <span class="totals-value">{{ $ajustes->total() }}</span>
                                <span class="totals-subtext">Total Ajustes</span>
                            </td>

                            <td class="text-center">
                                <span class="totals-value success">
                                    {{ $ajustesPositivos }}
                                </span>
                                <span class="totals-subtext">Incrementos</span>
                            </td>

                            <td class="text-center">
                                <span class="totals-value warning">
                                    {{ $ajustesNegativos }}
                                </span>
                                <span class="totals-subtext">Reducciones</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="p-3 d-flex justify-content-between align-items-center border-top">
                <div class="text-muted extra-small">
                    @if($ajustes->total() > 0)
                    Mostrando {{ $ajustes->firstItem() }} - {{ $ajustes->lastItem() }} de {{ $ajustes->total() }} registros
                    @else
                    Sin registros que mostrar
                    @endif
                </div>
                <div>
                    {{ $ajustes->appends(['busqueda' => request('busqueda'), 'per_page' => request('per_page', 10)])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const perPageSelect = document.getElementById('per_page');
        const searchInput = document.getElementById('searchInput');
        let debounceTimer;

        // Auto-submit al cambiar per_page
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                document.getElementById('searchForm').submit();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    document.getElementById('searchForm').submit();
                }, 400);
            });
        }
    });
</script>
@endpush