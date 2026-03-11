@extends('admin.layouts.app')

@section('title', 'Clientes')

@push('css-datatable')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/style_general.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style_cliente_filtros.css') }}">
@endpush

@section('content')
    @include('admin.layouts.partials.alert')

    <div class="container-fluid px-4 py-4">

        <div class="page-header">
            <div>
                <h1 class="page-title">Clientes</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Clientes</li>
                    </ol>
                </nav>
            </div>
            @can('crear-cliente')
            <a href="{{ route('clientes.create') }}" class="btn-create">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </a>
            @endcan
        </div>

        <div class="card-clean">
            <div class="card-header-clean">
                <div class="card-header-title">
                    <i class="fas fa-user-tie"></i> Lista de Clientes
                </div>
            </div>

            {{-- ── FILTROS RÁPIDOS ── --}}
            <div class="filter-bar">
                <div class="filter-bar-inner">

                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-layer-group"></i> Grupo
                        </label>
                        <select id="filtroGrupo" class="filter-select">
                            <option value="">Todos</option>
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->nombre }}">{{ $grupo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-divider"></div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-id-card"></i> Documento
                        </label>
                        <select id="filtroDocumento" class="filter-select">
                            <option value="">Todos</option>
                            @foreach($documentos as $doc)
                                <option value="{{ $doc->tipo_documento }}">{{ $doc->tipo_documento }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-divider"></div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-user"></i> Tipo Persona
                        </label>
                        <select id="filtroTipoPersona" class="filter-select">
                            <option value="">Todos</option>
                            <option value="Natural">Natural</option>
                            <option value="Jurídica">Jurídica</option>
                        </select>
                    </div>

                    <div class="filter-divider"></div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-circle"></i> Estado
                        </label>
                        <select id="filtroEstado" class="filter-select">
                            <option value="">Todos</option>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>

                    <button id="limpiarFiltros" class="filter-clear-btn" title="Limpiar filtros">
                        <i class="fas fa-times"></i> Limpiar
                    </button>

                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="datatablesSimple" class="custom-table">
                        <thead>
                            <tr>
                                <th>Cliente / Razón Social</th>
                                <th>Grupo</th>
                                <th>Teléfono</th>
                                <th>Documento</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clientes as $item)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $item->persona->nombre_completo }}</div>
                                    @if(!empty($item->persona->direccion))
                                        <span class="info-subtext">{{ Str::limit($item->persona->direccion, 30) }}</span>
                                    @else
                                        <span class="info-subtext">Sin dirección</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $item->grupoCliente->nombre ?? 'Sin grupo' }}</div>
                                    @if(!empty($item->grupoCliente?->descuento_global))
                                        <span class="info-subtext">Desc: {{ number_format($item->grupoCliente->descuento_global, 2) }}%</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($item->persona->telefono))
                                        <div class="fw-semibold">{{ $item->persona->telefono }}</div>
                                    @else
                                        <span class="info-subtext">Sin número</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $item->persona->documento->tipo_documento ?? 'N/A' }}</div>
                                    <span class="info-subtext">{{ $item->persona->numero_documento }}</span>
                                </td>
                                <td>
                                    @if($item->persona->tipo_persona == 'natural')
                                        <span class="badge bg-light text-dark border">Natural</span>
                                    @else
                                        <span class="badge bg-light text-dark border">Jurídica</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->estado == 1)
                                        <span class="badge-pill badge-success">Activo</span>
                                    @else
                                        <span class="badge-pill badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-action-group">
                                        @can('editar-cliente')
                                        <a href="{{ route('clientes.edit', ['cliente' => $item]) }}" class="btn-icon-soft" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        @endcan

                                        @can('update-estado-cliente')
                                            @if ($item->estado == 1)
                                                <button class="btn-icon-soft delete" data-bs-toggle="modal"
                                                    data-bs-target="#confirmModal-{{ $item->id }}" title="Desactivar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @else
                                                <button class="btn-icon-soft" data-bs-toggle="modal"
                                                    data-bs-target="#confirmModal-{{ $item->id }}" title="Restaurar">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal de confirmación -->
                            <div class="modal fade" id="confirmModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content modal-content-clean">
                                        <div class="modal-header modal-header-clean">
                                            <h5 class="modal-title fs-6">Confirmar acción</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4 text-center">
                                            <h6 class="mb-3">{{ $item->estado == 1 ? '¿Desactivar cliente?' : '¿Activar cliente?' }}</h6>
                                            <p class="text-muted small mb-4">
                                                {{ $item->estado == 1
                                                    ? 'El cliente pasará a estado inactivo (no se eliminará).'
                                                    : 'El cliente volverá a estar activo.' }}
                                            </p>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                                                <form action="{{ route('clientes.updateEstado', ['cliente' => $item->id]) }}" method="post" class="d-inline">
                                                    @method('PATCH')
                                                    @csrf
                                                    <button type="submit" class="btn {{ $item->estado == 1 ? 'btn-outline-danger' : 'btn-outline-success' }} btn-sm px-3">
                                                        {{ $item->estado == 1 ? 'Desactivar' : 'Activar' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="{{ asset('js/clientes.js') }}"></script>
@endpush