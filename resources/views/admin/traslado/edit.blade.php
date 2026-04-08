@extends('admin.layouts.app')
@section('title','Editar Traslado')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/style_create_edit_traslado.css') }}">
@endpush

@section('content')
@include('admin.layouts.partials.alert')

<div class="container-fluid px-2 px-md-3">
    <h1 class="mt-4 fs-4 fw-bold">Editar Traslado #{{ $traslado->id }}</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('panel') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('traslados.index') }}">Traslados</a>
                </li>
                <li class="breadcrumb-item active">Editar Traslado</li>
            </ol>

            <a href="{{ route('traslados.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>

        </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card-clean">

                <div class="card-header-clean">
                    <div class="card-header-title"><i class="fas fa-truck"></i>Datos del Traslado</div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('traslados.update', $traslado) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <div class="section-divider">Datos del traslado</div>
                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Almacén Origen</label>

                                    <select name="origen_almacen_id" id="origen_almacen_id" class="form-select @error('origen_almacen_id') is-invalid @enderror" {{ $origenFijo ? 'disabled' : '' }}>
                                        <option value=""></option>
                                        @foreach($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}" {{ old('origen_almacen_id', $traslado->origen_almacen_id ?? $user->almacen_id) == $almacen->id ? 'selected' : '' }}>
                                            {{ $almacen->nombre }}
                                        </option>
                                        @endforeach
                                    </select>

                                    @if($origenFijo)
                                    <input type="hidden" name="origen_almacen_id" value="{{ $user->almacen_id }}">
                                    @endif

                                    @error('origen_almacen_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Almacén Destino</label>
                                    <select name="destino_almacen_id" id="destino_almacen_id" class="form-select @error('destino_almacen_id') is-invalid @enderror">
                                        <option value=""></option>
                                        @foreach($almacenesDestino as $almacen)
                                        <option value="{{ $almacen->id }}" {{ old('destino_almacen_id', $traslado->destino_almacen_id)==$almacen->id?'selected':'' }}>
                                            {{ $almacen->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('destino_almacen_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha</label>
                                    <input class="form-control" disabled value="{{ \Carbon\Carbon::parse($traslado->fecha_hora)->format('d/m/Y H:i') }}">
                                    <input type="hidden" name="fecha_hora" value="{{ \Carbon\Carbon::parse($traslado->fecha_hora)->format('Y-m-d H:i:s') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Costo de Envío</label>
                                    <input type="number" name="costo_envio" class="form-control @error('costo_envio') is-invalid @enderror" step="0.01" value="{{ old('costo_envio', $traslado->costo_envio) }}">
                                    @error('costo_envio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                            </div>
                        </div>

                        <!-- Productos con preview -->
                        <div class="mb-4" id="productos_section">

                            <div class="section-divider">Productos del traslado</div>

                            <div class="row align-items-end mb-3">
                                <div class="col-md-5 mb-3">
                                    <label class="form-label">Producto</label>
                                    <select id="productoSelect" class="form-select">
                                        <option value="">Seleccione producto</option>
                                        @foreach($productos as $producto)
                                        <option value="{{ $producto->id }}" data-categoria="{{ $producto->categoria? $producto->categoria->nombre:'' }}">
                                            {{ $producto->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Stock origen</label>
                                    <input type="text" id="stock" class="form-control" disabled value="0">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" id="cantidad" class="form-control" value="1">
                                </div>
                                <div class="col-md-3 mb-3 text-end">
                                    <button type="button" id="btn_agregar" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Agregar producto</button>
                                </div>
                            </div>

                            <!-- Preview de stock -->
                            <div id="stockImpactPreview" style="display:none;">
                                <div class="row text-center align-items-center">

                                    <div class="col-md-2">
                                        <small class="text-muted d-block">Cantidad</small>
                                        <div class="fw-bold fs-5 text-primary" id="stockCantidad">0</div>
                                    </div>

                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Stock Origen</small>
                                        <div class="fw-bold fs-5">
                                            <span id="stockOrigenActual">0</span>
                                            <span class="text-muted mx-1">→</span>
                                            <span id="stockOrigenFinal" class="stock-value stock-ok-text">0</span>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Stock Destino</small>
                                        <div class="fw-bold fs-5">
                                            <span id="stockDestinoActual">0</span>
                                            <span class="text-muted mx-1">→</span>
                                            <span id="stockDestinoFinal" class="stock-value stock-ok-text">0</span>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <small class="text-muted d-block">Stock mínimo</small>
                                        <div class="fw-bold text-warning" id="stockMinimoPreview">0</div>
                                    </div>

                                    <div class="col-md-2">
                                        <small class="text-muted d-block">Stock máximo</small>
                                        <div class="fw-bold text-danger" id="stockMaximoPreview">0</div>
                                    </div>

                                </div>

                                <div class="row text-center mt-2">
                                    <div class="col-md-3 offset-md-2">
                                        <div id="stockMensajeOrigen" class="text-danger fw-semibold" style="font-size:.85rem;"></div>
                                    </div>

                                    <div class="col-md-3">
                                        <div id="stockMensajeDestino" class="text-danger fw-semibold" style="font-size:.85rem;"></div>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <!-- Mensaje de error general de productos -->
                            <div id="mensajeErrorProducto" class="alert alert-danger py-2 px-3 mb-3" style="display:none;">
                                @if ($errors->has('error'))
                                {{ $errors->first('error') }}
                                @endif
                            </div>
                            <!-- Tabla de productos -->

                            <div class="product-table-box mt-3">
                                <div class="product-table-header">
                                    <span>Productos del traslado</span>
                                    <span>Total: <span id="totalProductos">0</span></span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table product-table align-middle">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Categoría</th>
                                                <th class="text-center">Cantidad</th>
                                                <th class="text-center">Origen</th>
                                                <th class="text-center">Destino</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_detalle"></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Actualizar Traslado</button>
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

        // --- Inicializar Select2 ---
        $('#productoSelect').select2({
            width: '100%',
            placeholder: "Seleccione producto"
        });
        $('#origen_almacen_id').select2({
            width: '100%',
            placeholder: "Seleccione almacén"
        });
        $('#destino_almacen_id').select2({
            width: '100%',
            placeholder: "Seleccionar Almacén",
            allowClear: true  // ← esto genera la X
        });

        // validacion de los select visual
        if (@json($errors -> has('origen_almacen_id')))
            $('#origen_almacen_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');
        if (@json($errors -> has('destino_almacen_id')))
            $('#destino_almacen_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');

        // limpiar errores
        $('#productoSelect,#cantidad,#btn_agregar').on('input change click', function() {
            $('div.text-danger.small').hide();
            $('#mensajeErrorProducto').hide();
        });

        let stockActual = 0,
            stockMin = 0,
            stockMax = 0,
            stockDestinoActual = 0;

        function actualizarPreview() {
            let cantidad = parseFloat($('#cantidad').val()) || 0;
            let finalOrigen = stockActual - cantidad;
            let finalDestino = stockDestinoActual + cantidad;

            $('#stockCantidad').text(cantidad);
            $('#stockOrigenActual').text(stockActual);
            $('#stockDestinoActual').text(stockDestinoActual);
            $('#stockOrigenFinal').text(finalOrigen);
            $('#stockDestinoFinal').text(finalDestino);
            $('#stockMinimoPreview').text(stockMin);
            $('#stockMaximoPreview').text(stockMax);

            let mensajeOrigen = "",
                mensajeDestino = "";

            function evaluar(valor, elemento, tipo) {
                let clase = "stock-ok-text";
                if (valor < stockMin) {
                    if (tipo === "origen") {
                        clase = "text-warning fw-bold";
                        mensajeOrigen = "Stock origen bajo";
                    } else {
                        clase = "stock-low-text";
                        mensajeDestino = "Stock destino bajo";
                    }
                } else if (stockMax > 0 && valor > stockMax) {
                    if (tipo === "origen") {
                        clase = "text-warning fw-bold";
                        mensajeOrigen = "Stock origen excesivo";
                    } else {
                        clase = "stock-low-text";
                        mensajeDestino = "Stock destino excesivo";
                    }
                }
                $(elemento).removeClass("stock-ok-text stock-low-text text-warning fw-bold").addClass(clase);
            }

            evaluar(finalOrigen, "#stockOrigenFinal", "origen");
            evaluar(finalDestino, "#stockDestinoFinal", "destino");

            $('#stockMensajeOrigen').text(mensajeOrigen);
            $('#stockMensajeDestino').text(mensajeDestino);
            $('#stockImpactPreview').show();
        }

        function consultarStock() {
            let producto = $('#productoSelect').val();
            let origen = $('#origen_almacen_id').val();
            let destino = $('#destino_almacen_id').val();
            if (!producto || !origen) return;

            $.get("{{ route('productos.checkStock') }}", {
                producto_id: producto,
                almacen_id: origen
            }, function(data) {
                stockActual = Number(data.stock);
                stockMin = Number(data.min);
                stockMax = Number(data.max);
                $('#stock').val(stockActual);

                if (destino) {
                    $.get("{{ route('productos.checkStock') }}", {
                        producto_id: producto,
                        almacen_id: destino
                    }, function(dataDestino) {
                        stockDestinoActual = Number(dataDestino.stock);
                        actualizarPreview();
                    });
                } else {
                    stockDestinoActual = 0;
                    actualizarPreview();
                }
            });
        }

        // cambios refresh de preview
        $('#productoSelect,#origen_almacen_id,#destino_almacen_id').on('change', consultarStock);
        $('#cantidad').on('input', actualizarPreview);

        function aplicarRestriccionesSelects() {
            let origen = $('#origen_almacen_id').val();
            let destino = $('#destino_almacen_id').val();
            $('#destino_almacen_id option, #origen_almacen_id option').prop('disabled', false);
            if (origen) $('#destino_almacen_id option[value="' + origen + '"]').prop('disabled', true);
            if (destino) $('#origen_almacen_id option[value="' + destino + '"]').prop('disabled', true);
            $('#origen_almacen_id').trigger('change.select2');
            $('#destino_almacen_id').trigger('change.select2');
        }
        aplicarRestriccionesSelects();

        //refresh de tabla
        $('#origen_almacen_id,#destino_almacen_id').change(function() {
            aplicarRestriccionesSelects();
            if ($('#origen_almacen_id').val() && $('#destino_almacen_id').val()) $('#productos_section').show();
            consultarStock();
            recalcularTabla();
        });

        $('#productoSelect').on('change', function() {
            $('#cantidad').val(1);
            consultarStock();
            $('#mensajeErrorProducto').hide();
        });

        // add producto
        $('#btn_agregar').click(function() {
            let btn = $(this);
            btn.prop('disabled', true);
            btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Agregando...');

            setTimeout(function() {
                let productoId = $('#productoSelect').val();
                let productoOption = $('#productoSelect option:selected');
                let productoText = productoOption.text();
                let categoria = productoOption.data('categoria') || '';
                let cantidad = parseFloat($('#cantidad').val()) || 0;
                let mensaje = "";

                if (!productoId) mensaje = "No se seleccionó un producto.";
                else if (cantidad <= 0) mensaje = "La cantidad debe ser mayor a 0.";
                else if ($('#fila_' + productoId).length) mensaje = "Se está tratando de agregar un producto repetido.";

                if (mensaje !== "") {
                    $('#mensajeErrorProducto').text(mensaje).show();
                    btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i>Agregar producto');
                    return;
                }

                $('#mensajeErrorProducto').hide();

                let final = stockActual - cantidad;
                let clase = (final < stockMin || (stockMax > 0 && final > stockMax)) ? 'stock-low' : 'stock-ok';

                let finalDestino = stockDestinoActual + cantidad;
                let claseDestino = (finalDestino < stockMin || (stockMax > 0 && finalDestino > stockMax)) ? 'stock-low' : 'stock-ok';

                let fila = `<tr id="fila_${productoId}">
<td>${productoText}<input type="hidden" name="arrayidproducto[]" value="${productoId}"></td>
<td>${categoria}</td>
<td class="text-center">${cantidad}<input type="hidden" name="arraycantidad[]" value="${cantidad}"></td>
<td class="text-center"><span class="stock-badge ${clase}">${stockActual} → ${final}</span></td>
<td class="text-center"><span class="stock-badge ${claseDestino}">${stockDestinoActual} → ${finalDestino}</span></td>
<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger eliminar"><i class="fas fa-trash"></i></button></td>
</tr>`;

                $('#tbody_detalle').append(fila);
                $('#totalProductos').text($('#tbody_detalle tr').length);

                btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i>Agregar producto');
            }, 500);
        });

        $(document).on('click', '.eliminar', function() {
            $(this).closest('tr').remove();
            $('#totalProductos').text($('#tbody_detalle tr').length);
        });

        //si dos selects tienen valor entonces mostramos tabla
        function verificarMostrarProductos() {
            if ($('#origen_almacen_id').val() && $('#destino_almacen_id').val()) $('#productos_section').show();
        }
        verificarMostrarProductos();

        function recalcularTabla() {
            $('#tbody_detalle tr').each(function() {
                let fila = $(this);
                let productoId = fila.find('input[name="arrayidproducto[]"]').val();
                let cantidad = parseFloat(fila.find('input[name="arraycantidad[]"]').val()) || 0;
                let origen = $('#origen_almacen_id').val();
                let destino = $('#destino_almacen_id').val();
                if (!productoId || !origen) return;

                $.get("{{ route('productos.checkStock') }}", {
                    producto_id: productoId,
                    almacen_id: origen
                }, function(dataOrigen) {
                    let stockOrigen = Number(dataOrigen.stock);
                    let stockMinO = Number(dataOrigen.min);
                    let stockMaxO = Number(dataOrigen.max);
                    let finalOrigen = stockOrigen - cantidad;
                    let claseOrigen = (finalOrigen < stockMinO || (stockMaxO > 0 && finalOrigen > stockMaxO)) ? 'stock-low' : 'stock-ok';
                    fila.find('td').eq(3).html(`<span class="stock-badge ${claseOrigen}">${stockOrigen} → ${finalOrigen}</span>`);

                    if (destino) {
                        $.get("{{ route('productos.checkStock') }}", {
                            producto_id: productoId,
                            almacen_id: destino
                        }, function(dataDestino) {
                            let stockDestino = Number(dataDestino.stock);
                            let finalDestino = stockDestino + cantidad;
                            let claseDestino = (finalDestino < stockMinO || (stockMaxO > 0 && finalDestino > stockMaxO)) ? 'stock-low' : 'stock-ok';
                            fila.find('td').eq(4).html(`<span class="stock-badge ${claseDestino}">${stockDestino} → ${finalDestino}</span>`);
                        });
                    } else {
                        fila.find('td').eq(4).html(`<span class="stock-badge stock-ok">0 → ${cantidad}</span>`);
                    }
                });
            });
        }

        // --- Recuperar productos antiguos si existe old() ---
        let oldProductos = @json(old('arrayidproducto'));
        let oldCantidades = @json(old('arraycantidad'));
        if (oldProductos && oldProductos.length) {
            oldProductos.forEach(function(productoId, index) {
                let cantidad = oldCantidades[index];
                let option = $('#productoSelect option[value="' + productoId + '"]');
                let nombre = option.text();
                let categoria = option.data('categoria') || '';
                let fila = `<tr id="fila_${productoId}">
<td>${nombre}<input type="hidden" name="arrayidproducto[]" value="${productoId}"></td>
<td>${categoria}</td>
<td class="text-center">${cantidad}<input type="hidden" name="arraycantidad[]" value="${cantidad}"></td>
<td class="text-center"><span class="stock-badge stock-ok">-</span></td>
<td class="text-center"><span class="stock-badge stock-ok">-</span></td>
<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger eliminar"><i class="fas fa-trash"></i></button></td>
</tr>`;
                $('#tbody_detalle').append(fila);
            });
            $('#totalProductos').text($('#tbody_detalle tr').length);

        }
        
        else {
            let detalles = @json($traslado -> detalleTraslados ?? []);
            if (detalles.length > 0) {
                let origen = $('#origen_almacen_id').val();
                let destino = $('#destino_almacen_id').val();
                detalles.forEach(function(d) {
                    let nombre = d.producto?.nombre || '';
                    let categoria = d.producto?.categoria?.nombre || '';
                    let cantidad = parseInt(d.cantidad);
                    let fila = `<tr id="fila_${d.producto_id}">
<td>${nombre}<input type="hidden" name="arrayidproducto[]" value="${d.producto_id}"></td>
<td>${categoria}</td>
<td class="text-center">${cantidad}<input type="hidden" name="arraycantidad[]" value="${cantidad}"></td>
<td class="text-center"><span class="stock-badge stock-ok">-</span></td>
<td class="text-center"><span class="stock-badge stock-ok">-</span></td>
<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger eliminar"><i class="fas fa-trash"></i></button></td>
</tr>`;
                    $('#tbody_detalle').append(fila);

                    $.get("{{ route('productos.checkStock') }}", {
                        producto_id: d.producto_id,
                        almacen_id: origen
                    }, function(dataOrigen) {
                        let finalO = Math.floor(Number(dataOrigen.stock) - cantidad);
                        let claseO = (finalO < Math.floor(Number(dataOrigen.min)) || (Math.floor(Number(dataOrigen.max)) > 0 && finalO > Math.floor(Number(dataOrigen.max)))) ? 'stock-low' : 'stock-ok';
                        $(`#fila_${d.producto_id} td:nth-child(4) span`).text(`${Math.floor(Number(dataOrigen.stock))} → ${finalO}`).removeClass('stock-ok stock-low').addClass(claseO);
                    });

                    // --- Consultar stock destino ---
                    $.get("{{ route('productos.checkStock') }}", {
                        producto_id: d.producto_id,
                        almacen_id: destino
                    }, function(dataDestino) {
                        let finalD = Math.floor(Number(dataDestino.stock) + cantidad);
                        let claseD = (finalD < Math.floor(Number(dataDestino.min)) || (Math.floor(Number(dataDestino.max)) > 0 && finalD > Math.floor(Number(dataDestino.max)))) ? 'stock-low' : 'stock-ok';
                        $(`#fila_${d.producto_id} td:nth-child(5) span`).text(`${Math.floor(Number(dataDestino.stock))} → ${finalD}`).removeClass('stock-ok stock-low').addClass(claseD);
                    });
                });
                $('#totalProductos').text($('#tbody_detalle tr').length);
            }
        }

        @if($errors -> has('arrayidproducto'))
        $('#mensajeErrorProducto').text(@json($errors -> first('arrayidproducto'))).show();
        @endif


        // bloquear punto en cantidad antes de que entre al input
$('#cantidad').on('keypress', function(e){
    if(e.key === '.') {
        e.preventDefault();
    }
})

        // inputs cantidad y costo_envio
$('#cantidad, input[name="costo_envio"]').on('input', function() {
    let isCantidad = $(this).attr('id') === 'cantidad';
    let val = this.value;
    let cursor = this.selectionStart;

    if(isCantidad){
        // solo números enteros, máximo 6 dígitos
        val = val.replace(/\D/g,'').slice(0,6);
        this.value = val;
    } else {
        // precio/envío: números y un punto, limitar parte entera y decimal
        let parts = val.split('.');
        let intPart = parts[0].replace(/\D/g,'').slice(0,7);
        let decPart = parts[1] ? parts[1].replace(/\D/g,'').slice(0,2) : '';
        let clean = intPart + (decPart ? '.' + decPart : '');
        if(clean !== val){
            this.value = clean;
            // ajusta cursor para no saltar al principio
            this.setSelectionRange(cursor, cursor);
        }
    }
});

// corregir formato al salir del input
$('#cantidad, input[name="costo_envio"]').on('blur', function(){
    let isCantidad = $(this).attr('id') === 'cantidad';
    let val = this.value || '0';
    let step = parseFloat($(this).attr('step')) || 1;
    let decimals = isCantidad ? 0 : (step.toString().split('.')[1] || []).length;

    this.value = isCantidad ? parseInt(val,10).toString() : parseFloat(val).toFixed(decimals);
});

        //esperar para completar submit
        $('form').on('submit', function() {
            if ($(this).data('submitted')) return false;
            $(this).data('submitted', true);
            let btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true);
            btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
        });

    });
</script>
@endpush