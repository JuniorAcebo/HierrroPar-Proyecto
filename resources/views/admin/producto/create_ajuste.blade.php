@extends('admin.layouts.app')

@section('title', 'Nuevo Ajuste de Stock')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* TODO TU CSS ORIGINAL SE MANTIENE EXACTAMENTE IGUAL */
    .page-header {
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
        font-size: 0.9rem;
    }

    .card-clean {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: #fff;
    }

    .card-header-clean {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-header-title {
        font-weight: 600;
        font-size: 1rem;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-label {
        font-weight: 500;
        font-size: 0.9rem;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #adb5bd;
        box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.15);
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: 38px;
        display: flex;
        align-items: center;
        border-radius: 6px;
        border: 1px solid #ced4da;
        font-size: 0.9rem;
    }

    .almacen-stock-badge {
        font-size: 0.78rem;
        padding: 4px 10px;
        border-radius: 20px;
        background: #e9ecef;
        color: #495057;
        font-weight: 600;
    }

    .almacen-stock-badge.bajo {
        background: #fde8e8;
        color: #c0392b;
    }

    .almacen-stock-badge.normal {
        background: #e8f5e9;
        color: #27ae60;
    }

    .almacen-stock-badge.exceso {
        background: #fff8e1;
        color: #f39c12;
    }

    .section-divider {
        font-size: 0.75rem;
        font-weight: 600;
        color: #adb5bd;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e9ecef;
    }

    .stock-preview-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .stock-number {
        font-size: 1.05rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
@include('admin.layouts.partials.alert')

<div class="container-fluid px-2 px-md-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title">Nuevo Ajuste de Stock</h1>
        <a href="{{ route('productos.historialAjustes') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card-clean">
                <div class="card-header-clean">
                    <div class="card-header-title">
                        <i class="fas fa-boxes"></i> Ajuste de Stock
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('productos.storeAjuste') }}" method="POST" id="ajusteForm" novalidate>
                        @csrf

                        {{-- PRODUCTO --}}
                        <div class="mb-4">
                            <div class="section-divider">Selección de Producto</div>
                            <label class="form-label">Producto</label>
                            <select name="producto_id" id="productoSelect" class="form-select @error('producto_id') is-invalid @enderror">
                                <option value="">Buscar producto por nombre o código...</option>
                                @foreach($productos as $prod)
                                    <option value="{{ $prod->id }}" {{ old('producto_id', $productoSeleccionado ?? '') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->nombre }} — {{ $prod->codigo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('producto_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ALMACÉN --}}
                        <div class="mb-4">
                            <div class="section-divider">Almacén</div>
                            <label class="form-label">Sucursal / Almacén</label>
                            <select name="almacen_id" id="almacenSelect" class="form-select @error('almacen_id') is-invalid @enderror">
                                <option value="">Seleccione un almacén...</option>
                                @foreach($almacenes as $alm)
                                    <option value="{{ $alm->id }}" {{ old('almacen_id') == $alm->id ? 'selected' : '' }}>
                                        {{ $alm->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('almacen_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- AJUSTE --}}
                        <div class="mb-4">
                            <div class="section-divider">Detalle del Ajuste</div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Operación</label>
                                    <select name="tipo_ajuste" id="tipoAjuste" class="form-select @error('tipo_ajuste') is-invalid @enderror">
                                        <option value="sumar" {{ old('tipo_ajuste')=='sumar' ? 'selected':'' }}>Aumentar (+)</option>
                                        <option value="restar" {{ old('tipo_ajuste')=='restar' ? 'selected':'' }}>Restar (−)</option>
                                        <option value="fijar" {{ old('tipo_ajuste')=='fijar' ? 'selected':'' }}>Fijar Total</option>
                                    </select>
                                    @error('tipo_ajuste')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" name="cantidad" step="1" id="cantidadInput" class="form-control @error('cantidad') is-invalid @enderror" placeholder="0" value="{{ old('cantidad') }}">
                                    @error('cantidad')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Motivo (opcional)</label>
                                    <input type="text" name="motivo" class="form-control" value="{{ old('motivo') }}">
                                </div>
                            </div>

                            {{-- STOCK PREVIEW --}}
                            <div id="stockPreview" class="stock-preview-box" style="display:none;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted d-block">Stock actual</small>
                                        <div class="stock-number" id="stockActualNum">0</div>
                                    </div>

                                    <div>
                                        <small class="text-muted d-block">Cantidad después de ajuste</small>
                                        <div id="stockResultadoBadge" class="almacen-stock-badge stock-number">0</div>
                                    </div>

                                    <div>
                                        <small class="text-muted d-block">Stock mínimo</small>
                                        <div class="stock-number" id="stockMin">0</div>
                                    </div>

                                    <div>
                                        <small class="text-muted d-block">Stock máximo</small>
                                        <div class="stock-number" id="stockMax">0</div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Guardar Ajuste
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    $('#productoSelect').select2({
        placeholder: "Buscar producto...",
        allowClear: false,
        width: '100%'
    });

    $('#almacenSelect').select2({
        placeholder: "Seleccione almacén...",
        allowClear: false,
        width: '100%'
    });

    let stockActual = 0;
    let stockMin = 0;
    let stockMax = 0;

    function calcularResultado() {
        let tipo = $('#tipoAjuste').val();
        let cantidad = parseFloat($('#cantidadInput').val()) || 0;
        let resultado = stockActual;

        if (tipo === 'sumar') resultado = stockActual + cantidad;
        if (tipo === 'restar') resultado = stockActual - cantidad;
        if (tipo === 'fijar') resultado = cantidad;

        $('#stockActualNum').text(stockActual);
        $('#stockResultadoBadge')
            .removeClass('bajo normal exceso')
            .text(resultado);

        if (resultado < stockMin) {
            $('#stockResultadoBadge').addClass('bajo');
        } else if (resultado > stockMax) {
            $('#stockResultadoBadge').addClass('exceso');
        } else {
            $('#stockResultadoBadge').addClass('normal');
        }
    }

    function consultarStock() {
        let producto = $('#productoSelect').val();
        let almacen = $('#almacenSelect').val();

        if (!producto || !almacen) return;

        $.get("{{ route('productos.checkStock') }}", {
                producto_id: producto,
                almacen_id: almacen
            },
            function(data) {
                stockActual = Number(data.stock);
                stockMin = Number(data.min);
                stockMax = Number(data.max);

                $('#stockMin').text(stockMin);
                $('#stockMax').text(stockMax);
                $('#stockPreview').show();

                calcularResultado();
            }
        );
    }

    $('#productoSelect, #almacenSelect').on('change', consultarStock);
    $('#tipoAjuste, #cantidadInput').on('input change', calcularResultado);

    if ($('#productoSelect').val() && $('#almacenSelect').val()) {
        consultarStock();
    }

});
</script>
@endpush