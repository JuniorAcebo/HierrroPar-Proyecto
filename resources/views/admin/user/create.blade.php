@extends('admin.layouts.app')

@section('title', 'Crear usuario')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/style_create_user.css') }}">
@endpush

@section('content')
    @include('admin.layouts.partials.alert')

    <div class="container">
        <h1>Crear Usuario</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('panel') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('users.index') }}">Usuarios</a>
                </li>
                <li class="breadcrumb-item active">Crear Usuario</li>
            </ol>

            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>

        </div>

        <div class="card">
            <form action="{{ route('users.store') }}" method="post" novalidate>
                @csrf

                <div class="card-body">

                    <!-- Nombre -->
                    <div class="form-row">
                        <label for="name" class="form-label">Nombres:</label>
                        <div class="form-input-group">
                            <input autocomplete="off" type="text" name="name" id="name"
                                value="{{ old('name') }}" aria-labelledby="nameHelpBlock" class="@error('name') is-invalid @enderror">
                            @error('name')
                                <small class="text-danger">{{ '*' . $message }}</small>
                            @enderror
                        </div>
                        <div class="form-help-text" id="nameHelpBlock">
                            Escriba un solo nombre
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-row">
                        <label for="email" class="form-label">Email:</label>
                        <div class="form-input-group">
                            <input autocomplete="off" type="email" name="email" id="email"
                                value="{{ old('email') }}" aria-labelledby="emailHelpBlock" class="@error('email') is-invalid @enderror">
                            @error('email')
                                <small class="text-danger">{{ '*' . $message }}</small>
                            @enderror
                        </div>
                        <div class="form-help-text" id="emailHelpBlock">
                            Dirección de correo electrónico
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-row">
                        <label for="password" class="form-label">Contraseña:</label>
                        <div class="form-input-group">
                            <input type="password" name="password" id="password" aria-labelledby="passwordHelpBlock" class="@error('password') is-invalid @enderror">
                            @error('password')
                                <small class="text-danger">{{ '*' . $message }}</small>
                            @enderror
                        </div>
                        <div class="form-help-text" id="passwordHelpBlock">
                            Escriba una contraseña segura. Debe incluir números.
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-row">
                        <label for="password_confirmation" class="form-label">Confirmar:</label>
                        <div class="form-input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                aria-labelledby="passwordConfirmationHelpBlock" class="@error('password_confirmation') is-invalid @enderror">
                            @error('password_confirmation')
                                <small class="text-danger">{{ '*' . $message }}</small>
                            @enderror
                        </div>
                        <div class="form-help-text" id="passwordConfirmationHelpBlock">
                            Vuelva a escribir su contraseña.
                        </div>
                    </div>

                    <!-- Roles -->
                    <div class="form-row">
                        <label for="role" class="form-label">Rol:</label>
                        <div class="form-input-group">
                            <select name="role" id="role">
                                <option value="" disabled selected>Seleccione:</option>
                                @foreach ($roles as $item)
                                    <option value="{{ $item->name }}"
                                        @selected(old('role') == $item->name)>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <small class="text-danger">{{ '*' . $message }}</small>
                            @enderror
                        </div>
                        <div class="form-help-text" id="rolHelpBlock">
                            Escoja un rol para el usuario.
                        </div>
                    </div>

                    <!-- Almacen -->
                    <div class="form-row" id="almacen_row" style="display: none;">
                        <label for="almacen_id" class="form-label">Almacen:</label>
                        <div class="form-input-group">
                            <select name="almacen_id" id="almacen_id" aria-labelledby="almacenHelpBlock" class="@error('almacen_id') is-invalid @enderror">
                                <option value="" selected>Sin Almacen asignado</option>
                                @foreach ($almacenes as $almacen)
                                    <option value="{{ $almacen->id }}" @selected(old('almacen_id') == $almacen->id)>
                                        {{ $almacen->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('almacen_id')
                                <small class="text-danger">{{ '*' . $message }}</small>
                            @enderror
                        </div>
                        <div class="form-help-text" id="almacenHelpBlock">
                            Seleccione el Almacen donde trabaja (opcional para administrador).
                        </div>
                    </div>

                </div>
                {{-- Botones --}}
                <div class="card-footer-clean p-4 text-center" >

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Guardar Rol
                    </button>

                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const roleSelect = document.getElementById('role');
    const almacenRow = document.getElementById('almacen_row');
    const almacenSelect = document.getElementById('almacen_id');

    function updateAlmacenField() {
        const selectedText = roleSelect.options[roleSelect.selectedIndex].text.trim().toUpperCase();
        const firstOption = almacenSelect.options[0];

        if (selectedText === 'ADMINISTRADOR') {
            almacenRow.style.display = 'none';
            almacenSelect.value = '';
            almacenSelect.required = false;
        } else {
            almacenRow.style.display = 'flex';
            
            if (selectedText === 'GERENTE' || selectedText === 'VENDEDOR') {
                firstOption.text = '-- Seleccione un Almacen --';
                almacenSelect.required = true;
            } else {
                firstOption.text = 'Sin Almacen asignado';
                almacenSelect.required = false;
            }
        }
    }

    roleSelect.addEventListener('change', updateAlmacenField);

    // Ejecutar al cargar la página
    updateAlmacenField();
});
</script>
@endpush