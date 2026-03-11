@extends('admin.layouts.app')

@section('title', 'Editar Cliente')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<link rel="stylesheet" href="{{ asset('css/style_create_edit_cliente.css') }}">
@endpush

@section('content')
<div class="container">

    <h1>Editar Cliente</h1>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('panel') }}">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('clientes.index') }}">Clientes</a>
            </li>
            <li class="breadcrumb-item active">Editar Cliente</li>
        </ol>

        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>

    <div class="form-container">
        <form action="{{ route('clientes.update', $cliente) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">

                {{-- NOMBRE COMPLETO --}}
                <div class="col-md-6">
                    <label class="form-label">Nombre Completo / Razón Social *</label>
                    <input type="text"
                        name="nombre_completo"
                        class="form-control @error('nombre_completo') is-invalid @enderror"
                        value="{{ old('nombre_completo', $cliente->persona->nombre_completo) }}">
                    @error('nombre_completo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- TIPO DE PERSONA --}}
                <div class="col-md-6">
                    <label class="form-label">Tipo de Persona *</label>
                    <select name="tipo_persona"
                        class="form-control selectpicker @error('tipo_persona') is-invalid @enderror"
                        data-live-search="false">
                        <option value="">Seleccione un tipo</option>
                        <option value="natural"  {{ old('tipo_persona', $cliente->persona->tipo_persona) == 'natural'  ? 'selected' : '' }}>Natural</option>
                        <option value="juridica" {{ old('tipo_persona', $cliente->persona->tipo_persona) == 'juridica' ? 'selected' : '' }}>Jurídica</option>
                    </select>
                    @error('tipo_persona')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- TELÉFONO --}}
                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text"
                        name="telefono"
                        class="form-control @error('telefono') is-invalid @enderror"
                        value="{{ old('telefono', $cliente->persona->telefono) }}">
                    @error('telefono')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- DIRECCIÓN --}}
                <div class="col-md-6">
                    <label class="form-label">Dirección</label>
                    <input type="text"
                        name="direccion"
                        class="form-control @error('direccion') is-invalid @enderror"
                        value="{{ old('direccion', $cliente->persona->direccion) }}">
                    @error('direccion')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- TIPO DE DOCUMENTO --}}
                <div class="col-md-6">
                    <label class="form-label">Tipo de Documento *</label>
                    <select name="documento_id"
                        class="form-control selectpicker @error('documento_id') is-invalid @enderror"
                        data-live-search="true">
                        <option value="">Seleccione un documento</option>
                        @foreach ($documentos as $doc)
                            <option value="{{ $doc->id }}"
                                {{ old('documento_id', $cliente->persona->documento_id) == $doc->id ? 'selected' : '' }}>
                                {{ $doc->tipo_documento }}
                            </option>
                        @endforeach
                    </select>
                    @error('documento_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- NÚMERO DE DOCUMENTO --}}
                <div class="col-md-6">
                    <label class="form-label">Número de Documento *</label>
                    <input type="text"
                        name="numero_documento"
                        class="form-control @error('numero_documento') is-invalid @enderror"
                        value="{{ old('numero_documento', $cliente->persona->numero_documento) }}">
                    @error('numero_documento')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- GRUPO DE CLIENTE --}}
                <div class="col-md-6">
                    <label class="form-label">Grupo de Cliente *</label>
                    <select name="grupo_cliente_id"
                        class="form-control selectpicker @error('grupo_cliente_id') is-invalid @enderror"
                        data-live-search="true">
                        <option value="">Seleccione un grupo</option>
                        @foreach ($grupos as $grupo)
                            <option value="{{ $grupo->id }}"
                                {{ old('grupo_cliente_id', $cliente->grupo_cliente_id) == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->nombre }}
                                ({{ number_format($grupo->descuento_global, 2) }}% desc.)
                            </option>
                        @endforeach
                    </select>
                    @error('grupo_cliente_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> Actualizar Cliente
                </button>
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary ms-2">
                    Cancelar
                </a>
            </div>

        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>
    $(function () {
        $('.selectpicker').selectpicker();
    });
</script>
@endpush