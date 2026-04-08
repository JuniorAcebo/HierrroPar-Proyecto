@extends('admin.layouts.app')

@section('title', 'Traslados')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="{{ asset('css/style_general.css') }}">
@endpush

@section('content')
@include('admin.layouts.partials.alert')

<div class="container-fluid px-4 py-4">

    <div class="page-header">
        <div>
            <h1 class="page-title">Traslados</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Traslados</li>
                </ol>
            </nav>
        </div>
        @can('crear-traslado')
        <a href="{{ route('traslados.create') }}" class="btn-create">
            <i class="fas fa-plus"></i> Nuevo Traslado
        </a>
        @endcan
    </div>

    <div class="card-clean">
        <div class="card-header-clean">
            <div class="card-header-title">
                <i class="fas fa-truck-moving"></i> Lista de Traslados
            </div>
        </div>

        <div class="search-container">
            <form action="{{ route('traslados.index') }}" method="GET" id="searchForm">
                <div class="row g-3 align-items-center">

                    <!-- BUSCADOR -->
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="padding: 0.4rem 0.75rem;">
                                <i class="fas fa-search text-muted small"></i>
                            </span>
                            <input type="text" name="busqueda" class="form-control form-control-clean border-start-0 ps-0" placeholder="Buscar traslado..." value="{{ $busqueda ?? '' }}">
                        </div>
                    </div>

                    <!-- FECHA INICIO -->
                    <div class="col-md-2 d-flex align-items-center">
                        <span class="me-2 extra-small text-muted" style="width: 40px;">Desde</span>
                        <input type="date" name="fecha_inicio" class="form-control form-control-clean" value="{{ request('fecha_inicio') }}">
                    </div>

                    <!-- FECHA FIN -->
                    <div class="col-md-2 d-flex align-items-center">
                        <span class="me-2 extra-small text-muted" style="width: 40px;">Hasta</span>
                        <input type="date" name="fecha_fin" class="form-control form-control-clean" value="{{ request('fecha_fin') }}">
                    </div>

                    <!-- PAGINADO -->
                    <div class="col-md-2">
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

                    <!-- RESET -->
                    <div class="col-md-3 text-end">
                        <a href="{{ route('traslados.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                            <i class="fas fa-undo me-1"></i> Mostrar Todo
                        </a>
                    </div>

                </div>
            </form>
        </div>

        <!-- SECCION: ACCIONES DE SELECCION -->
        <div class="selection-actions-container" id="selectionActions" style="display: none;">
            <div class="selected-counter">
                <span>Traslados seleccionados:</span>
                <span class="selected-counter-badge" id="selectedCount">0</span>
            </div>
            <div class="action-buttons-group">
                <button type="button" class="btn-action-outline" id="deselectAll">
                    <i class="fas fa-times"></i> Deseleccionar Todos
                </button>
                @can('exportar-traslados')
                <button type="button" class="btn-action-secondary" id="exportExcel">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </button>

                <button type="button" class="btn-action-secondary" id="exportPdf" style="background: #e74c3c;">
                    <i class="fas fa-file-pdf"></i> Exportar a PDF
                </button>
                @endcan
            </div>
        </div>

        <div class="card-body p-0" id="table-container">
            <div class="table-responsive">
                <table id="datatablesSimple" class="custom-table">
                    <thead>
                        <tr>
                            <th class="checkbox-header">
                                <div class="custom-checkbox select-all" id="selectAll"></div>
                            </th>

                            <th>
                                <button class="sort-btn {{ $sort == 'id' ? 'active ' . $direction : '' }}" data-column="id">
                                    Traslado <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>
                            <th>Ruta</th>

                            <th>
                                <button class="sort-btn {{ $sort == 'total_items' ? 'active ' . $direction : '' }}" data-column="total_items">
                                    Movimiento <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>

                            <th>
                                <button class="sort-btn {{ $sort == 'responsable' ? 'active ' . $direction : '' }}" data-column="responsable">
                                    Responsable <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>

                            <th>
                                <button class="sort-btn {{ $sort == 'estado' ? 'active ' . $direction : '' }}" data-column="estado">
                                    Estado <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>

                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($traslados as $traslado)
                        <tr data-traslado-id="{{ $traslado->id }}">
                            <td class="checkbox-cell">
                                <div class="custom-checkbox traslado-checkbox" data-traslado-id="{{ $traslado->id }}"></div>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    TR-{{ $traslado->id }}
                                </div>
                                <div class="text-muted extra-small">
                                    {{ \Carbon\Carbon::parse($traslado->fecha_hora)->format('d/m/Y H:i') }}
                                </div>
                            </td>

                            <!-- RUTA -->
                            <td>
                                <div class="fw-semibold">
                                    {{ $traslado->origenAlmacen?->nombre ?? 'N/A' }}
                                    →
                                    {{ $traslado->destinoAlmacen?->nombre ?? 'N/A' }}
                                </div>

                                <div class="text-muted extra-small">
                                    {{ $traslado->detalleTraslados->count() }} productos
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $traslado->total_items ?? 0 }} unidades
                                </div>

                                <div class="text-muted extra-small">
                                    @if(in_array($traslado->estado, ['completado','cancelado']))
                                    Duración:
                                    {{ \Carbon\Carbon::parse($traslado->fecha_hora)->diffForHumans($traslado->updated_at, true) }}
                                    @else
                                    En proceso
                                    @endif
                                </div>
                            </td>

                            <!-- USUARIO -->
                            <td>
                                <div class="fw-semibold">
                                    {{ $traslado->user?->name ?? 'N/A' }}
                                </div>
                            </td>

                            <!-- ESTADO -->
                            <td>
                                @php
                                $opcionesEstado = [];
                                switch($traslado->estado) {
                                case 'pendiente':
                                $opcionesEstado = ['pendiente' => 'Pendiente', 'en_curso' => 'En Curso', 'cancelado' => 'Cancelado'];
                                break;
                                case 'en_curso':
                                $opcionesEstado = ['en_curso' => 'En Curso', 'completado' => 'Completado', 'cancelado' => 'Cancelado'];
                                break;
                                case 'completado':
                                $opcionesEstado = ['completado' => 'Completado'];
                                break;
                                case 'cancelado':
                                $opcionesEstado = ['cancelado' => 'Cancelado'];
                                break;
                                default:
                                $opcionesEstado = [$traslado->estado => ucfirst($traslado->estado)];
                                break;
                                }
                                @endphp

                                @if(in_array($traslado->estado, ['pendiente', 'en_curso']))
                                    @can('update-estado-traslado')
                                        <form action="{{ route('traslados.updateEstado', $traslado) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <select name="estado" class="form-select form-select-sm estado-select" data-traslado-id="{{ $traslado->id }}">
                                                @foreach($opcionesEstado as $valor => $label)
                                                    <option value="{{ $valor }}" {{ $traslado->estado === $valor ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @else
                                        <span class="badge-pill badge-secondary">
                                            {{ ucfirst($traslado->estado) }}
                                        </span>
                                    @endcan
                                @endif
                            </td>

                            <!-- ACCIONES -->
                            <td>
                                <div class="btn-action-group">
                                    @can('ver-traslado')
                                    <button class="btn-icon-soft" data-bs-toggle="modal" data-bs-target="#verModal-{{ $traslado->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @endcan

                                    @can('editar-traslado')
                                    @if($traslado->estado === 'pendiente')
                                    <a href="{{ route('traslados.edit', $traslado) }}" class="btn-icon-soft">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @endif
                                    @endcan
                                </div>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 d-flex justify-content-between align-items-center border-top">
                <div class="text-muted extra-small">
                    Mostrando {{ $traslados->firstItem() }} - {{ $traslados->lastItem() }} de {{ $traslados->total() }} registros
                </div>
                <div>
                    {{ $traslados->appends([
                       'busqueda' => $busqueda,
                       'per_page' => $perPage,
                       'sort' => $sort,
                       'direction' => $direction
                    ])->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Exportacion -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-clean">
                <div class="modal-header modal-header-clean">
                    <h5 class="modal-title fs-6" id="exportModalTitle">
                        <i class="fas fa-file-export me-2"></i> Exportar Traslados
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="exportFormat" value="excel">
                    <div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-4" id="exportAlert" style="border-radius: 12px;">
                        <i class="fas fa-info-circle me-3 fs-5 text-success" id="exportAlertIcon"></i>
                        <div class="small fw-medium text-success">
                            Se exportaran <strong id="exportCountDisplay">0</strong> traslados seleccionados.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="info-subtext mb-2 text-uppercase letter-spacing-05 small fw-bold">Opciones de Datos</label>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="modalIncludeDetalles" checked>
                            <label class="form-check-label d-block" for="modalIncludeDetalles">
                                <span class="d-block fw-semibold small">Incluir detalles de productos</span>
                                <span class="extra-small text-muted">Lista de productos trasladados</span>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="modalIncludeCosto" checked>
                            <label class="form-check-label d-block" for="modalIncludeCosto">
                                <span class="d-block fw-semibold small">Incluir costo de envio</span>
                                <span class="extra-small text-muted">Informacion de costos</span>
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="modalIncludeUsuario" checked>
                            <label class="form-check-label d-block" for="modalIncludeUsuario">
                                <span class="d-block fw-semibold small">Incluir informacion de usuario</span>
                                <span class="extra-small text-muted">Usuario que realizo el traslado</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-outline-danger btn-sm px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary btn-sm px-4" id="confirmExportBtn">
                        <i class="fas fa-download me-1"></i> Generar Archivo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODALES FUERA DE LA TABLA -->
<div id="modales-section">
    @foreach ($traslados as $traslado)
    <!-- Modal de detalles -->
    <div class="modal fade" id="verModal-{{ $traslado->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-clean">
                <div class="modal-header modal-header-clean">
                    <h5 class="modal-title fs-6">Detalles del Traslado #{{ $traslado->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="info-subtext mb-1">Fecha Registro</label>
                            <div class="p-2 border rounded bg-light small">{{ \Carbon\Carbon::parse($traslado->fecha_hora)->format('d/m/Y H:i:s') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="info-subtext mb-1">Usuario</label>
                            <div class="p-2 border rounded bg-light small">{{ $traslado->user?->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="info-subtext mb-1">Almacen Origen</label>
                            <div class="p-2 border rounded bg-light small">{{ $traslado->origenAlmacen?->nombre ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="info-subtext mb-1">Almacen Destino</label>
                            <div class="p-2 border rounded bg-light small">{{ $traslado->destinoAlmacen?->nombre ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="info-subtext mb-1">Costo de Envio</label>
                            <div class="fw-bold">Bs {{ number_format($traslado->costo_envio, 2) }}</div>
                        </div>
                        <div class="col-md-6">
                            @php
                            $estadoColor = match($traslado->estado) {
                            'pendiente' => 'warning',
                            'en_curso' => 'info',
                            'completado' => 'success',
                            'cancelado' => 'danger',
                            default => 'secondary',
                            };
                            $estadoText = match($traslado->estado) {
                            'pendiente' => 'Pendiente',
                            'en_curso' => 'En Curso',
                            'completado' => 'Completado',
                            'cancelado' => 'Cancelado',
                            default => ucfirst($traslado->estado),
                            };
                            @endphp
                            <label class="info-subtext mb-1">Estado</label>
                            <div class="p-2 border rounded bg-{{ $estadoColor }} text-white text-center fw-bold">
                                {{ $estadoText }}
                            </div>
                        </div>
                    </div>

                    <!-- PRODUCTOS TRASLADADOS -->
                    <div class="mt-4">
                        <h6 class="fw-semibold border-bottom pb-2 small uppercase letter-spacing-05">
                            <i class="fas fa-box me-2 text-muted"></i>Productos Trasladados
                        </h6>
                        @if($traslado->detalleTraslados->count())
                        <div class="table-responsive mt-2">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($traslado->detalleTraslados as $detalle)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="product-avatar me-2" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                                    <i class="fas fa-box small"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold small">
                                                        {{ $detalle->producto?->nombre ?? 'Producto eliminado' }}
                                                    </div>
                                                    @if($detalle->producto)
                                                    <span class="info-subtext">
                                                        Código: {{ $detalle->producto->codigo }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge-pill badge-primary">
                                                {{ $detalle->cantidad }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <th class="text-end">Total:</th>
                                        <th class="text-center">
                                            <span class="badge-pill badge-success">
                                                {{ $traslado->detalleTraslados->sum('cantidad') }}
                                            </span>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-light border py-2 small mt-2 text-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No hay productos asociados a este traslado.
                        </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('js')
<script>
    let debounceTimer;
    const tableContainer = document.getElementById('table-container');
    let selectedTraslados = new Set();

    // Sistema de seleccion
    function initializeSelectionSystem() {
        const selectionActions = document.getElementById('selectionActions');
        const selectAllCheckbox = document.getElementById('selectAll');
        const trasladoCheckboxes = document.querySelectorAll('.traslado-checkbox');
        const selectedCountElement = document.getElementById('selectedCount');
        const deselectAllBtn = document.getElementById('deselectAll');

        let exportExcelBtn = document.getElementById('exportExcel');
        let exportPdfBtn = document.getElementById('exportPdf');

        if (exportExcelBtn) {
            exportExcelBtn.replaceWith(exportExcelBtn.cloneNode(true));
            exportExcelBtn = document.getElementById('exportExcel');
        }

        if (exportPdfBtn) {
            exportPdfBtn.replaceWith(exportPdfBtn.cloneNode(true));
            exportPdfBtn = document.getElementById('exportPdf');
        }

        // Seleccionar/Deseleccionar traslado individual
        trasladoCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('click', function() {
                const trasladoId = this.dataset.trasladoId;
                const row = this.closest('tr');

                if (this.classList.contains('checked')) {
                    this.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedTraslados.delete(trasladoId);
                } else {
                    this.classList.add('checked');
                    row.classList.add('selected');
                    selectedTraslados.add(trasladoId);
                }

                updateSelectionUI();
            });
        });

        // Seleccionar todos
        selectAllCheckbox.addEventListener('click', function() {
            const isSelectAll = !this.classList.contains('checked');

            trasladoCheckboxes.forEach(checkbox => {
                const trasladoId = checkbox.dataset.trasladoId;
                const row = checkbox.closest('tr');

                if (isSelectAll) {
                    checkbox.classList.add('checked');
                    row.classList.add('selected');
                    selectedTraslados.add(trasladoId);
                } else {
                    checkbox.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedTraslados.delete(trasladoId);
                }
            });

            selectAllCheckbox.classList.toggle('checked');
            updateSelectionUI();
        });

        // Deseleccionar todos
        deselectAllBtn.addEventListener('click', function() {
            selectedTraslados.clear();
            trasladoCheckboxes.forEach(checkbox => {
                checkbox.classList.remove('checked');
                const row = checkbox.closest('tr');
                row.classList.remove('selected');
            });
            selectAllCheckbox.classList.remove('checked');
            updateSelectionUI();
        });

        // Eventos de Exportacion
        if (exportExcelBtn) {
            exportExcelBtn.addEventListener('click', () => openExportModal('excel'));
        }
        if (exportPdfBtn) {
            exportPdfBtn.addEventListener('click', () => openExportModal('pdf'));
        }

        function openExportModal(format) {
            limpiarModalesBootstrap();
            const modalElement = document.getElementById('exportModal');

            let modal = bootstrap.Modal.getInstance(modalElement);
            if (!modal) {
                modal = new bootstrap.Modal(modalElement);
            }

            const title = document.getElementById('exportModalTitle');
            const formatInput = document.getElementById('exportFormat');
            const confirmBtn = document.getElementById('confirmExportBtn');
            const alertBox = document.getElementById('exportAlert');
            const alertIcon = document.getElementById('exportAlertIcon');

            formatInput.value = format;
            document.getElementById('exportCountDisplay').textContent = selectedTraslados.size;

            if (format === 'excel') {
                title.innerHTML = '<i class="fas fa-file-excel me-2 text-success"></i> Exportar a Excel';
                confirmBtn.className = 'btn btn-outline-success btn-sm px-4';
                alertBox.className = 'alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-4';
                alertIcon.className = 'fas fa-info-circle me-3 fs-5 text-success';
            } else {
                title.innerHTML = '<i class="fas fa-file-pdf me-2 text-danger"></i> Exportar a PDF';
                confirmBtn.className = 'btn btn-outline-danger btn-sm px-4';
                alertBox.className = 'alert alert-danger border-0 bg-danger bg-opacity-10 d-flex align-items-center mb-4';
                alertIcon.className = 'fas fa-info-circle me-3 fs-5 text-danger';
            }

            modal.show();
        }

        function updateSelectionUI() {
            const count = selectedTraslados.size;
            selectedCountElement.textContent = count;

            if (count > 0) {
                selectionActions.style.display = 'flex';
                const totalCheckboxes = document.querySelectorAll('.traslado-checkbox').length;
                if (count === totalCheckboxes) {
                    selectAllCheckbox.classList.add('checked');
                } else {
                    selectAllCheckbox.classList.remove('checked');
                }
            } else {
                selectionActions.style.display = 'none';
                selectAllCheckbox.classList.remove('checked');
            }
        }
    }

    // Manejar confirmacion de exportacion
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'confirmExportBtn') {
            const format = document.getElementById('exportFormat').value;
            const trasladoIds = Array.from(selectedTraslados);
            const includeDetalles = document.getElementById('modalIncludeDetalles').checked;
            const includeCosto = document.getElementById('modalIncludeCosto').checked;
            const includeUsuario = document.getElementById('modalIncludeUsuario').checked;

            const modalElement = document.getElementById('exportModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) modalInstance.hide();

            Swal.fire({
                title: 'Generando archivo...',
                text: 'Por favor espere un momento.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = format === 'excel' ?
                '{{ route("traslados.export-excel") }}' :
                '{{ route("traslados.export-pdf") }}';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            trasladoIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'traslado_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            const options = {
                includeDetalles,
                includeCosto,
                includeUsuario
            };
            Object.keys(options).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = options[key];
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();

            setTimeout(() => {
                document.getElementById('deselectAll').click();
                Swal.fire({
                    icon: 'success',
                    title: 'Exportacion iniciada',
                    text: 'El archivo se descargara automaticamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 1000);
        }
    });

    function initializeEvents() {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');
        const sortButtons = document.querySelectorAll('.sort-btn'); // 🔥 agregado

        // fechas
        const fechaInicio = document.querySelector('input[name="fecha_inicio"]');
        const fechaFin = document.querySelector('input[name="fecha_fin"]');

        if (searchInput) {
            searchInput.focus();
            const len = searchInput.value.length;
            searchInput.setSelectionRange(len, len);

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchTraslados(), 300);
            });
        }

        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => fetchTraslados());
        }

        // eventos fecha
        if (fechaInicio) fechaInicio.addEventListener('change', () => fetchTraslados());
        if (fechaFin) fechaFin.addEventListener('change', () => fetchTraslados());

        //ordenamiento
        sortButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const column = this.dataset.column;
                const currentUrl = new URL(window.location.href);
                let direction = 'asc';

                if (currentUrl.searchParams.get('sort') === column) {
                    direction = currentUrl.searchParams.get('direction') === 'asc' ? 'desc' : 'asc';
                }

                const params = new URLSearchParams(window.location.search);
                params.set('sort', column);
                params.set('direction', direction);

                fetchTraslados(`{{ route('traslados.index') }}?${params.toString()}`);
            });
        });
    }

    function fetchTraslados(url = null) {
        limpiarModalesBootstrap();

        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');

        // fechas
        const fechaInicio = document.querySelector('input[name="fecha_inicio"]');
        const fechaFin = document.querySelector('input[name="fecha_fin"]');

        let fetchUrl = url;
        if (!fetchUrl) {
            const params = new URLSearchParams(window.location.search);
            if (searchInput) params.set('busqueda', searchInput.value);
            if (perPageSelect) params.set('per_page', perPageSelect.value);

            if (fechaInicio && fechaInicio.value) params.set('fecha_inicio', fechaInicio.value);
            if (fechaFin && fechaFin.value) params.set('fecha_fin', fechaFin.value);

            fetchUrl = `{{ route('traslados.index') }}?${params.toString()}`;
        }

        // ------------------- Actualizacion parcial para evitar mezcla -------------------
        fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');

                // actualizar solo tbody de la tabla
                const currentTable = tableContainer.querySelector('table');
                const newTable = newDoc.getElementById('table-container').querySelector('table');

                if (currentTable && newTable) {
                    const currentTbody = currentTable.querySelector('tbody');
                    const newTbody = newTable.querySelector('tbody');
                    if (currentTbody && newTbody) {
                        currentTbody.innerHTML = newTbody.innerHTML;
                    }
                }

                // actualizar modales sin perder detalles
                const modalesSection = document.querySelector('#modales-section');
                const newModalesSection = newDoc.querySelector('#modales-section');
                if (modalesSection && newModalesSection) {
                    modalesSection.innerHTML = newModalesSection.innerHTML;
                }

                selectedTraslados.clear();
                document.getElementById('selectionActions').style.display = 'none';

                window.history.pushState({}, '', fetchUrl);

                initializeEvents();
                initializeSelectionSystem();
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initializeEvents();
        initializeSelectionSystem();
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('tr[data-traslado-id]') && !e.target.closest('.btn-action-group') && !e.target.closest('.custom-checkbox') && !e.target.closest('select') && !e.target.closest('form')) {
            const row = e.target.closest('tr[data-traslado-id]');
            const checkbox = row.querySelector('.traslado-checkbox');
            if (checkbox) checkbox.click();
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('estado-select')) {
            const select = e.target;
            const newEstado = select.value;

            let estadoText = '';
            switch (newEstado) {
                case 'pendiente':
                    estadoText = 'Pendiente';
                    break;
                case 'en_curso':
                    estadoText = 'En Curso';
                    break;
                case 'completado':
                    estadoText = 'Completado';
                    break;
                case 'cancelado':
                    estadoText = 'Cancelado';
                    break;
                default:
                    estadoText = newEstado;
                    break;
            }

            Swal.fire({
                title: 'Confirmar cambio de estado',
                text: `Deseas cambiar el estado a "${estadoText}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = select.closest('form');
                    form.submit();
                } else {
                    const form = select.closest('form');
                    const previousEstado = form.querySelector('option[selected]').value;
                    select.value = previousEstado;
                }
            });
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-real-delete')) {
            const btn = e.target.closest('.btn-real-delete');
            const form = btn.closest('form');
            const nombre = btn.dataset.nombre;

            Swal.fire({
                title: '¿Estas seguro?',
                text: `Deseas eliminar "${nombre}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });

    function limpiarModalesBootstrap() {
        document.querySelectorAll('.modal.show').forEach(modal => {
            const instance = bootstrap.Modal.getInstance(modal);
            if (instance) instance.hide();
        });

        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
    }
</script>
@endpush