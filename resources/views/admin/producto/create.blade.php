@extends('admin.layouts.app')

@section('title', 'Crear Producto')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<link rel="stylesheet" href="{{ asset('css/style_create_edit_producto.css') }}">
@endpush

@section('content')
<div class="container">

    <h1>Crear Producto</h1>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('panel') }}">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('productos.index') }}">Productos</a>
            </li>
            <li class="breadcrumb-item active">Crear Producto</li>
        </ol>

        <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>

    </div>
        

    <div class="form-container">
        <form action="{{ route('productos.store') }}" method="POST">
            @csrf

            <div class="row g-3">

                {{-- CODIGO --}}
                <div class="col-md-6">
                    <label class="form-label">Codigo *</label>
                    <div class="d-flex">
                        <input type="text"
                            name="codigo"
                            id="codigo"
                            class="form-control @error('codigo') is-invalid @enderror"
                            value="{{ old('codigo') }}">

                        <button type="button" onclick="generarCodigo()" class="generar-btn">
                            Generar
                        </button>
                    </div>
                    @error('codigo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- NOMBRE --}}
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input type="text"
                        name="nombre"
                        class="form-control @error('nombre') is-invalid @enderror"
                        value="{{ old('nombre') }}">
                    @error('nombre')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- PRECIO COMPRA --}}
                <div class="col-md-6">
                    <label class="form-label">Precio Compra *</label>
                    <div class="input-group">
                        <span class="input-group-text">Bs</span>
                        <input type="number" step="0.01" min="0"
                            name="precio_compra"
                            class="form-control @error('precio_compra') is-invalid @enderror"
                            value="{{ old('precio_compra', 0.00) }}"
>
                    </div>
                    @error('precio_compra')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- PRECIO VENTA --}}
                <div class="col-md-6">
                    <label class="form-label">Precio Venta *</label>
                    <div class="input-group">
                        <span class="input-group-text">Bs</span>
                        <input type="number" step="0.01" min="0"
                            name="precio_venta"
                            class="form-control @error('precio_venta') is-invalid @enderror"
                            value="{{ old('precio_venta', 0.00) }}"
>
                    </div>
                    @error('precio_venta')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- STOCK MINIMO --}}
                <div class="col-md-6">
                    <label class="form-label">Stock Minimo</label>
                    <input type="number" min="0"
                        name="stock_minimo"
                        class="form-control @error('stock_minimo') is-invalid @enderror"
                        value="{{ old('stock_minimo', 0) }}">
                    @error('stock_minimo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- STOCK MAXIMO --}}
                <div class="col-md-6">
                    <label class="form-label">Stock Maximo</label>
                    <input type="number" min="0"
                        name="stock_maximo"
                        class="form-control @error('stock_maximo') is-invalid @enderror"
                        value="{{ old('stock_maximo', 0) }}">
                    @error('stock_maximo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- DESCRIPCION --}}
                <div class="col-12">
                    <label class="form-label">Descripcion</label>
                    <textarea name="descripcion"
                        rows="2"
                        class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- MARCA --}}
                <div class="col-md-6">
                    <label class="form-label">Marca *</label>
                    <select name="marca_id"
                        class="form-control selectpicker @error('marca_id') is-invalid @enderror"
                        data-live-search="true">
                        <option value="">Seleccione una marca</option>
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}"
                                {{ old('marca_id') == $marca->id ? 'selected' : '' }}>
                                {{ $marca->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('marca_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- TIPO UNIDAD --}}
                <div class="col-md-6">
                    <label class="form-label">Tipo Unidad *</label>
                    <select name="tipo_unidad_id"
                        class="form-control selectpicker @error('tipo_unidad_id') is-invalid @enderror"
                        data-live-search="true">
                        <option value="">Seleccione tipo de unidad</option>
                        @foreach($tipounidades as $tipo)
                            <option value="{{ $tipo->id }}"
                                {{ old('tipo_unidad_id') == $tipo->id ? 'selected' : '' }}>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_unidad_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- CATEGORIA --}}
                <div class="col-md-6">
                    <label class="form-label">Categoria *</label>
                    <select name="categoria_id"
                        class="form-control selectpicker @error('categoria_id') is-invalid @enderror"
                        data-live-search="true">
                        <option value="">Seleccione categoria</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('categoria_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Guardar
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

<script>
function generarCodigo() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = 'PROD-';
    for (let i = 0; i < 10; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('codigo').value = code;
}

$(function () {
    $('.selectpicker').selectpicker();

    if (!$('#codigo').val()) {
        generarCodigo();
    }
});
</script>
@endpush