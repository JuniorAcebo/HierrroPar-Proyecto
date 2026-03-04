@extends('admin.layouts.app')

@section('title', 'Editar Producto')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<link rel="stylesheet" href="{{ asset('css/style_create_edit_producto.css') }}">
@endpush

@section('content')
<div class="container-fluid px-2 px-md-3">
    <h1>Editar Producto</h1>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('panel') }}">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('productos.index') }}">Productos</a>
            </li>
            <li class="breadcrumb-item active">Editar Producto</li>
        </ol>

        <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>

    </div>

    <div class="form-container">
        <form action="{{ route('productos.update', $producto) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <!-- Código -->
                <div class="col-md-6">
                    <label class="form-label">Código:</label>
                    <input type="text" class="form-control" value="{{ $producto->codigo }}" disabled>
                </div>

                <!-- Nombre -->
                <div class="col-md-6">
                    <label class="form-label">Nombre:</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $producto->nombre) }}">
                    @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Precio Compra -->
                <div class="col-md-6">
                    <label class="form-label">Precio Compra:</label>
                    <div class="input-group">
                        <span class="input-group-text">Bs</span>
                        <input type="number" step="0.01" name="precio_compra" class="form-control @error('precio_compra') is-invalid @enderror" value="{{ old('precio_compra', $producto->precio_compra) }}">
                    </div>
                    @error('precio_compra')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Precio Venta -->
                <div class="col-md-6">
                    <label class="form-label">Precio Venta:</label>
                    <div class="input-group">
                        <span class="input-group-text">Bs</span>
                        <input type="number" step="0.01" name="precio_venta" class="form-control @error('precio_venta') is-invalid @enderror" value="{{ old('precio_venta', $producto->precio_venta) }}">
                    </div>
                    @error('precio_venta')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Stock mínimo -->
                <div class="col-md-6">
                    <label class="form-label">Stock Mínimo:</label>
                    <input type="number" name="stock_minimo" class="form-control @error('stock_minimo') is-invalid @enderror" value="{{ old('stock_minimo', $producto->stock_minimo) }}">
                    @error('stock_minimo')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Stock máximo -->
                <div class="col-md-6">
                    <label class="form-label">Stock Máximo:</label>
                    <input type="number" name="stock_maximo" class="form-control @error('stock_maximo') is-invalid @enderror" value="{{ old('stock_maximo', $producto->stock_maximo) }}">
                    @error('stock_maximo')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="col-12">
                    <label class="form-label">Descripción:</label>
                    <textarea name="descripcion" rows="2" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    @error('descripcion')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Marca -->
                <div class="col-md-6">
                    <label class="form-label">Marca:</label>
                    <select name="marca_id" class="form-control selectpicker @error('marca_id') is-invalid @enderror">
                        @foreach($marcas as $marca)
                        <option value="{{ $marca->id }}" {{ old('marca_id', $producto->marca_id) == $marca->id ? 'selected' : '' }}>
                            {{ $marca->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('marca_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Tipo Unidad -->
                <div class="col-md-6">
                    <label class="form-label">Tipo Unidad:</label>
                    <select name="tipo_unidad_id" class="form-control selectpicker @error('tipo_unidad_id') is-invalid @enderror">
                        @foreach($tipounidades as $tipo)
                        <option value="{{ $tipo->id }}" {{ old('tipo_unidad_id', $producto->tipo_unidad_id) == $tipo->id ? 'selected' : '' }}>
                            {{ $tipo->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('tipo_unidad_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Categoría -->
                <div class="col-md-6">
                    <label class="form-label">Categoría:</label>
                    <select name="categoria_id" class="form-control selectpicker @error('categoria_id') is-invalid @enderror">
                        @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('categoria_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Actualizar
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
    $(function() {
        $('.selectpicker').selectpicker();
    });
</script>
@endpush