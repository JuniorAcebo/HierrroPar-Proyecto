@extends('admin.layouts.app')

@section('title','Usuarios')

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
            <h1 class="page-title">Usuarios</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
                </ol>
            </nav>
        </div>
        @can('crear-user')
        <a href="{{ route('users.create') }}" class="btn-create">
            <i class="fas fa-plus"></i> Añadir Nuevo Usuario
        </a>
        @endcan
    </div>

    <div class="card-clean">
        <div class="card-header-clean">
            <div class="card-header-title">
                <i class="fas fa-users"></i> Tabla de Usuarios
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatablesSimple" class="custom-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Almacen</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $item)
                        <tr>
                            <td class="fw-semibold">
                                {{ $item->name }}
                            </td>
                            <td>
                                {{ $item->email }}
                            </td>
                            <td>
                                @if($item->almacen)
                                    <span class="badge bg-light text-dark border">{{ $item->almacen->nombre }}</span>
                                @else
                                    <span class="text-muted small">TODOS</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $roleName = strtolower($item->role?->name ?? 'usuario');

                                    $styles = match(true) {
                                        str_contains($roleName, 'admin') => 'background-color:#01677D; color:white;',
                                        str_contains($roleName, 'gerente') => 'background-color:#003642; color:white;',
                                        str_contains($roleName, 'vendedor') => 'background-color:#6AB5CD; color:white;',
                                        default => 'background-color:#6c757d; color:white;'
                                    };
                                @endphp

                                <span class="badge rounded-pill" style="{{ $styles }}">
                                    {{ $item->role?->name ?? 'Usuario' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $item->estado == 'activo' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($item->estado) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-action-group">
                                    @can('editar-user')
                                    <a href="{{ route('users.edit', ['user'=>$item]) }}" class="btn-icon-soft" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @endcan

                                    @can('update-estado-user')
                                    <button type="button" class="btn btn-sm {{ $item->estado == 'activo' ? 'btn-success' : 'btn-danger' }} ms-2" 
                                            style="width: 32px; height: 32px; border-radius: 50%; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                            data-bs-toggle="modal" data-bs-target="#confirmModal-{{$item->id}}" 
                                            title="{{ $item->estado == 'activo' ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas {{ $item->estado == 'activo' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Modal de confirmacion -->
                        <div class="modal fade" id="confirmModal-{{$item->id}}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content modal-content-clean">
                                    <div class="modal-header modal-header-clean">
                                        <h5 class="modal-title fs-6">Confirmar acción</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <h6 class="mb-3">¿{{ $item->estado == 'activo' ? 'Desactivar' : 'Activar' }} Usuario?</h6>
                                        <p class="text-muted small mb-4">
                                            ¿Seguro que quieres {{ $item->estado == 'activo' ? 'desactivar' : 'activar' }} este usuario del sistema?
                                        </p>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('users.updateEstado', ['user'=>$item->id]) }}" method="post">
                                                @method('PATCH')
                                                @csrf
                                                <button type="submit" class="btn {{ $item->estado == 'activo' ? 'btn-outline-danger' : 'btn-outline-success' }} btn-sm">Confirmar</button>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabla = document.querySelector("#datatablesSimple");
        if (tabla) {
            new simpleDatatables.DataTable(tabla, {
                perPage: 10,
                perPageSelect: [10, 15, 20],
                searchable: true,
                labels: {
                    placeholder: "Buscar por nombre, email, almacen, rol, estado",
                    perPage: "",
                    noRows: "No se encontraron resultados",
                    info: ""
                }
            });
        }
    });
</script>
<style>
    .datatable-top {
        padding: 1rem 1.5rem !important;
        display: flex !important;
        justify-content: flex-start !important;
    }
    .datatable-search {
        float: none !important;
        margin-right: auto !important;
        margin-left: 0 !important;
    }
    .datatable-selector {
        padding: 0.5rem 1.75rem 0.5rem 0.75rem !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 6px !important;
        margin-right: 8px !important;
        cursor: pointer;
    }
    .datatable-input {
        min-width: 250px;
        border: 1px solid #dee2e6 !important;
        border-radius: 6px !important;
        padding: 0.5rem 0.75rem !important;
    }
    .datatable-input:focus {
        border-color: #4fc1ff !important;
        box-shadow: 0 0 0 0.2rem rgba(79, 193, 255, 0.25) !important;
        outline: none !important;
    }
</style>
@endpush
