@extends('admin.layouts.app')

@section('title', 'Crear Almacén')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/style_create_edit_cliente.css') }}">
@endpush

@section('content')
    @include('admin.layouts.partials.alert')

    <div class="container-fluid px-4 py-4">
        <h1>Nuevo Almacén</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('panel') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('almacenes.index') }}">Almacenes</a>
                </li>
                <li class="breadcrumb-item active">Nuevo Almacén</li>
            </ol>

            <a href="{{ route('almacenes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>

        <div class="form-container mt-4">
            
            <form action="{{ route('almacenes.store') }}" method="post">
                @csrf
                <div class="row g-3">
                    <!-- Código -->
                    <div class="col-md-6">
                        <label for="codigo" class="form-label">Código *</label>
                        <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" 
                            class="form-control @error('codigo') is-invalid @enderror" placeholder="Ej: ALM-001">
                        @error('codigo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nombre -->
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" 
                            class="form-control @error('nombre') is-invalid @enderror" >
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Dirección -->
                    <div class="col-12">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" name="direccion" id="direccion" value="{{ old('direccion') }}" 
                            class="form-control @error('direccion') is-invalid @enderror" placeholder="Dirección física">
                        @error('direccion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Descripción -->
                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="3" 
                            class="form-control @error('descripcion') is-invalid @enderror" placeholder="Opcional...">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="fas fa-save"></i> Guardar Almacén
                    </button>
                    <a href="{{ route('almacenes.index') }}" class="btn btn-outline-secondary ms-2">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
