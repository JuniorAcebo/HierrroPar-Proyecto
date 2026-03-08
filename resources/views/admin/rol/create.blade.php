@extends('admin.layouts.app')

@section('title', 'Crear Rol')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/style_create_user.css') }}">
@endpush

@section('content')

    @include('admin.layouts.partials.alert')

    <div class="container-fluid px-4 py-4">

        <h1>Crear Rol</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('panel') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('roles.index') }}">Roles</a>
                </li>
                <li class="breadcrumb-item active">Crear Rol</li>
            </ol>

            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>

        </div>

        <div class="card-clean">

            <div class="card-header-clean" style="margin-bottom: 15px;">
                <div class="card-header-title">
                    <i class="fas fa-user-shield"></i> Nuevo Rol
                </div>
            </div>

            <div class="card-body">

                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf

                    {{-- Nombre del Rol --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Nombre del Rol
                        </label>

                        <input autocomplete="off" type="text" name="name" id="name" value="{{ old('name') }} "
                            aria-labelledby="nameHelpBlock">

                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Permisos --}}
                    <div class="mb-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-semibold mb-0">
                                Permisos a seleccionar:
                            </label>
                            <div>
                                <button type="button" id="selectAll" class="btn btn-sm btn-outline-primary me-2">Todos</button>
                                <button type="button" id="deselectAll" class="btn btn-sm btn-outline-secondary">Ninguno</button>
                            </div>
                        </div>

                        <div style="margin-bottom: 10px; margin-top: -10px;"> 
                            @error('permisos')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                        </div>

                        @php
                            $permisosAgrupados = $permisos->groupBy('modulo');
                        @endphp

                        <div class="row">

                            @foreach ($permisosAgrupados as $modulo => $listaPermisos)
                                <div class="col-md-3 mb-3">

                                    <div class="border rounded p-3 h-100">

                                        <div class="fw-bold text-primary mb-2">
                                            <i class="fas fa-folder-open me-1"></i>
                                            {{ $modulo }}
                                        </div>

                                        @foreach ($listaPermisos as $permiso)
                                            <div class="form-check">

                                                <input class="form-check-input perm-checkbox" type="checkbox" name="permisos[]"
                                                    value="{{ $permiso->id }}" id="permiso{{ $permiso->id }}"
                                                    {{ in_array($permiso->id, old('permisos', [])) ? 'checked' : '' }}>

                                                <label class="form-check-label" for="permiso{{ $permiso->id }}">

                                                    {{ $permiso->name }}

                                                </label>

                                            </div>
                                        @endforeach

                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="card-footer-clean p-4 text-center">

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Guardar Rol
                        </button>

                        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function setAllCheckboxes(checked) {
            document.querySelectorAll('.perm-checkbox').forEach(function(cb) {
                cb.checked = checked;
            });
        }

        var selectAllBtn = document.getElementById('selectAll');
        var deselectAllBtn = document.getElementById('deselectAll');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                setAllCheckboxes(true);
            });
        }
        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', function() {
                setAllCheckboxes(false);
            });
        }
    });
</script>
@endpush
