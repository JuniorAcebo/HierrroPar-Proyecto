@extends('admin.layouts.app')

@section('title', 'Realizar venta')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/style_create_edit_venta.css') }}">
@endpush

@section('content')
    @include('admin.layouts.partials.alert')
    <div class="container-fluid px-4">
        <h1 class="mt-4 fs-4 fw-bold">Crear Venta</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('panel') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('ventas.index') }}">Ventas</a>
                </li>
                <li class="breadcrumb-item active">Crear Venta</li>
            </ol>

            <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>

        </div>
    </div>

    <form action="{{ route('ventas.store') }}" method="post" id="ventaForm">
        @csrf
        <div class="container-lg">
            <div class="border-section">
                <div class="section-title"><i class="fas fa-info-circle"></i> Datos Generales</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Cliente: <span class="text-danger">*</span></label>
                        <select name="cliente_id" id="cliente_id" class="form-control form-control-sm selectpicker show-tick" data-live-search="true" title="Seleccione un cliente">
                            @foreach ($clientes as $item)
                                <option value="{{ $item->id }}" {{ old('cliente_id') == $item->id ? 'selected' : '' }} data-descuento="{{ $item->grupoCliente->descuento_global ?? 0 }}">{{ $item->persona->nombre_completo }}</option>
                            @endforeach
                        </select>
                        <small id="val_cliente" class="text-danger mt-1 d-none">* Obligatorio</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipo de Comprobante: <span class="text-danger">*</span></label>
                        <select name="estado_comprobante" id="estado_comprobante" class="form-control form-control-sm selectpicker">
                            <option value="boleta" {{ old('estado_comprobante') == 'boleta' ? 'selected' : '' }}>Boleta de Venta</option>
                            <option value="factura" {{ old('estado_comprobante') == 'factura' ? 'selected' : '' }}>Factura</option>
                        </select>
                        <small id="val_comprobante" class="text-danger mt-1 d-none">* Obligatorio</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Número:</label>
                        <input type="text" name="numero_comprobante" class="form-control form-control-sm" value="{{ $nextComprobanteNumber }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha:</label>
                        <input type="date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" readonly>
                        <input type="hidden" name="fecha_hora" value="{{ now()->toDateTimeString() }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vendedor:</label>
                        <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->name }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Almacén: <span class="text-danger">*</span></label>
                        @if(auth()->user()->almacen_id)
                            <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->almacen->nombre }}" readonly>
                            <input type="hidden" name="almacen_id" id="almacen_id" value="{{ auth()->user()->almacen_id }}">
                        @else
                            <select name="almacen_id" id="almacen_id" class="form-control form-control-sm selectpicker" title="Seleccione Almacén">
                                @foreach($almacenes as $al)
                                    <option value="{{ $al->id }}" {{ old('almacen_id') == $al->id ? 'selected' : '' }}>{{ $al->nombre }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
            </div>

            <div class="border-section">
                <div class="section-title"><i class="fas fa-shopping-cart"></i> Detalles de la Venta</div>
                
                <div class="search-wrapper mb-3">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="producto_search" class="form-control form-control-sm" placeholder="Buscar por codigo o nombre del producto...">
                    <div class="products-dropdown" id="products_dropdown"></div>
                </div>

                <div class="product-selection-card" id="selection_card">
                    <div class="selection-title">
                        <span><i class="fas fa-box-open"></i> <span id="sel_name">Producto</span></span>
                        <span id="sel_stock_badge" class="badge-stock stock-ok">Stock: 0</span>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Código</label>
                            <input type="text" id="sel_codigo" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted">P. Compra (Ref)</label>
                            <input type="text" id="sel_precio_compra" class="form-control form-control-sm bg-light text-muted" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Precio Venta (Bs.)</label>
                            <input type="number" id="sel_precio" class="form-control form-control-sm" step="0.01">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Cantidad</label>
                            <input type="number" id="sel_cantidad" class="form-control form-control-sm" value="1" step="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-primary fw-bold">Subtotal (Pre)</label>
                            <input type="text" id="sel_subtotal" class="form-control form-control-sm fw-bold border-primary bg-light" value="0.00" readonly>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btn_add_item" class="btn btn-primary btn-sm w-100 h-32"><i class="fas fa-plus me-1"></i> Añadir</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tabla_detalle" class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>Producto</th>
                                <th width="12%">Cantidad</th>
                                <th width="12%">P. Venta</th>
                                <th width="18%">Descuento</th>
                                <th width="13%">Subtotal</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="row justify-content-end">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light border rounded">
                            <span class="fw-bold">TOTAL VENTA:</span>
                            <span class="fs-5 fw-bold text-primary">Bs. <span id="label_total">0.00</span></span>
                            <input type="hidden" name="total" id="input_total" value="0">
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Nota Interna:</label>
                        <textarea name="nota_personal" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nota Cliente:</label>
                        <textarea name="nota_cliente" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button id="btn_cancelar" type="button" class="btn btn-outline-danger btn-sm px-4" style="display:none;">Cancelar</button>
                    <button id="btn_guardar" type="submit" class="btn btn-success btn-sm px-5 fw-bold" style="display:none;">Realizar Venta</button>
                </div>
            </div>
        </div>
        <div class="alert alert-danger shadow-sm mb-4" id="form-errors" style="display:none; border-left: 5px solid #dc3545;">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-exclamation-triangle fs-4 me-2"></i>
                <h5 class="mb-0 fw-bold">Por favor corrija los siguientes errores antes de continuar:</h5>
            </div>
            <ul class="mb-0" id="form-errors-list"></ul>
        </div>
    </form>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <script>
        $(document).ready(function() {
            const PRODUCTOR_RAW = '{!! addslashes(json_encode($productos)) !!}';
            const PRODUCTOS = JSON.parse(PRODUCTOR_RAW);
            let itemsAgregados = new Set();
            let selectedItem = null;
            let rowCount = 0;
            let previousAlmacenId = $('#almacen_id').val();
            let stockRequestSeq = 0;
            let suppressAlmacenChange = false;
            let currentClientDiscount = 0;

            $('.selectpicker').selectpicker();

            if ($('#cliente_id').val()) {
                const opt = $('#cliente_id').find('option:selected');
                currentClientDiscount = parseFloat(opt.data('descuento')) || 0;
            }

            $('#cliente_id').on('changed.bs.select', function() {
                const option = $(this).find('option:selected');
                currentClientDiscount = parseFloat(option.data('descuento')) || 0;
                
                // Ya no preguntamos automáticamente para no saturar al usuario.
                // El descuento se aplica a nuevos items o si el usuario usa una herramienta de "Sincronizar descuentos".
            });

            function applyGlobalDiscount(discountPercentage) {
                $('#tabla_detalle tbody tr').each(function() {
                    const tr = $(this);
                    if (discountPercentage > 0) {
                        tr.find('.t-desc-type').val('%');
                        tr.find('.t-desc-input').val(discountPercentage.toFixed(2));
                    } else {
                        tr.find('.t-desc-type').val('bs');
                        tr.find('.t-desc-input').val('0.00');
                    }
                    recalculateRow(tr);
                });
                updateTotals();
            }

            function setStockBadge(stock, ilimitado) {
                const badge = $('#sel_stock_badge');
                badge.removeClass('stock-ok stock-low stock-out');

                if (ilimitado) {
                    badge.text('Stock: Ilimitado');
                    badge.addClass('stock-ok');
                    return;
                }

                const stockNum = parseFloat(stock) || 0;
                badge.text(`Stock: ${stockNum}`);
                if (stockNum <= 0) badge.addClass('stock-out');
                else if (stockNum < 10) badge.addClass('stock-low');
                else badge.addClass('stock-ok');
            }

            async function fetchStock(productoId, almacenId) {
                const mySeq = ++stockRequestSeq;
                const res = await $.ajax({
                    url: '{{ route("ventas.check-stock") }}',
                    method: 'GET',
                    data: { producto_id: productoId, almacen_id: almacenId }
                });
                return { res, mySeq };
            }

            function resetVentaItems() {
                $('#tabla_detalle tbody').empty();
                itemsAgregados.clear();
                rowCount = 0;
                updateTotals();
                checkVisibility();
                $('#selection_card').hide();
                selectedItem = null;
                $('#producto_search').val('');
            }

            function calculatePreSubtotal() {
                const qty = parseFloat($('#sel_cantidad').val()) || 0;
                const price = parseFloat($('#sel_precio').val()) || 0;
                $('#sel_subtotal').val((qty * price).toFixed(2));
            }

            $('#sel_cantidad, #sel_precio').on('input', calculatePreSubtotal);

            // --- SEARCH LOGIC ---
            $('#producto_search').on('input', function() {
                const q = $(this).val().toLowerCase().trim();
                const dropdown = $('#products_dropdown');
                if (q.length < 1) { dropdown.hide(); return; }
                
                const matches = PRODUCTOS.filter(p => p.nombre.toLowerCase().includes(q) || p.codigo.toLowerCase().includes(q)).slice(0, 10);
                
                dropdown.empty();
                if (matches.length === 0) {
                    dropdown.append('<div class="p-3 text-muted small text-center">No se encontraron productos</div>').show();
                    return;
                }

                matches.forEach(p => {
                    const isAdded = itemsAgregados.has(p.id);
                    const item = $(`
                        <div class="product-item ${isAdded ? 'disabled' : ''}">
                            <div>
                                <div class="prod-main">${p.nombre}</div>
                                <div class="prod-sub">${p.codigo}</div>
                            </div>
                            <div class="prod-price">Bs. ${parseFloat(p.precio_venta).toFixed(2)}</div>
                        </div>
                    `);
                    
                    if (!isAdded) {
                        item.on('click', () => selectProduct(p));
                    }
                    dropdown.append(item);
                });
                dropdown.show();
            });

            $(document).on('click', (e) => {
                if (!$(e.target).closest('.search-wrapper').length) $('#products_dropdown').hide();
            });

            async function selectProduct(p) {
                $('#products_dropdown').hide();
                $('#producto_search').val('');
                
                const storageId = $('#almacen_id').val();
                
                if (!storageId) {
                    Swal.fire("Atención", "Debe seleccionar un almacén o sucursal antes de seleccionar un producto.", "warning");
                    return;
                }

                // Show loading state
                Swal.showLoading();
                
                try {
                    const { res, mySeq } = await fetchStock(p.id, storageId);
                    
                    Swal.close();
                    if (mySeq !== stockRequestSeq) return;
                    if (res.success) {
                        const ilimitado = !!res.ilimitado;
                        const stockValue = ilimitado ? Number.POSITIVE_INFINITY : parseFloat(res.stock);
                        selectedItem = { ...p, stock: stockValue, ilimitado };
                        $('#sel_name').text(p.nombre);
                        $('#sel_codigo').val(p.codigo);
                        $('#sel_precio_compra').val(parseFloat(p.precio_compra).toFixed(2));
                        $('#sel_precio').val(parseFloat(p.precio_venta).toFixed(2));
                        $('#sel_cantidad').val('1').focus();
                        calculatePreSubtotal();
                        
                        setStockBadge(res.stock, ilimitado);

                        $('#selection_card').slideDown();
                    } else {
                        Swal.fire("Error", "No se pudo consultar el stock", "error");
                    }
                } catch (err) {
                    Swal.fire("Error", "Error en el servidor", "error");
                }
            }

            async function refreshSelectedStockForAlmacen(almacenId) {
                if (!selectedItem) return;
                if (!$('#selection_card').is(':visible')) return;

                try {
                    Swal.showLoading();
                    const { res, mySeq } = await fetchStock(selectedItem.id, almacenId);
                    Swal.close();
                    if (mySeq !== stockRequestSeq) return;
                    if (!res.success) {
                        Swal.fire("Error", "No se pudo consultar el stock", "error");
                        return;
                    }

                    const ilimitado = !!res.ilimitado;
                    selectedItem.stock = ilimitado ? Number.POSITIVE_INFINITY : parseFloat(res.stock);
                    selectedItem.ilimitado = ilimitado;
                    setStockBadge(res.stock, ilimitado);
                } catch (err) {
                    Swal.fire("Error", "Error en el servidor", "error");
                }
            }

            $('#almacen_id').on('changed.bs.select', async function() {
                if (suppressAlmacenChange) return;
                const newAlmacenId = $(this).val();
                
                const hasItems = $('#tabla_detalle tbody tr').length > 0;

                if (hasItems) {
                    const result = await Swal.fire({
                        title: 'Cambiar sucursal',
                        text: 'Al cambiar la sucursal se borrarán los items agregados para evitar inconsistencias de stock. ¿Desea continuar?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Sí, cambiar',
                        cancelButtonText: 'No'
                    });

                    if (!result.isConfirmed) {
                        suppressAlmacenChange = true;
                        $('#almacen_id').selectpicker('val', previousAlmacenId);
                        suppressAlmacenChange = false;
                        return;
                    }

                    resetVentaItems();
                }

                previousAlmacenId = newAlmacenId;
                await refreshSelectedStockForAlmacen(newAlmacenId);
            });

            // --- TABLE LOGIC ---
            $('#btn_add_item').on('click', function() {
                if (!selectedItem) return;
                const qty = parseFloat($('#sel_cantidad').val()) || 0;
                const price = parseFloat($('#sel_precio').val()) || 0;

                if (qty <= 0) { Swal.fire("Error", "Ingrese una cantidad valida", "warning"); return; }
                if (qty > selectedItem.stock) {
                    const msg = selectedItem.ilimitado ? 'Stock ilimitado' : `Solo dispone de ${parseFloat(selectedItem.stock) || 0}`;
                    Swal.fire("Stock Insuficiente", msg, "error");
                    return;
                }

                addItem(selectedItem, qty, price);
                $('#selection_card').hide();
                selectedItem = null;
            });

            function addItem(p, qty, price, discValue = null) {
                rowCount++;
                itemsAgregados.add(p.id);
                
                let descTypeHtml = '';
                let descInputVal = discValue !== null ? parseFloat(discValue).toFixed(2) : '0.00';
                let activeType = 'bs';
                
                if (discValue === null && currentClientDiscount > 0) {
                    activeType = '%';
                    descInputVal = currentClientDiscount.toFixed(2);
                }

                if (activeType === '%') {
                    descTypeHtml = `
                        <option value="bs">Bs.</option>
                        <option value="%" selected>%</option>
                    `;
                } else {
                    descTypeHtml = `
                        <option value="bs" selected>Bs.</option>
                        <option value="%">%</option>
                    `;
                }

                const row = `
                    <tr id="row_${rowCount}" data-id="${p.id}">
                        <td class="row-index">${rowCount}</td>
                        <td>
                            <div class="fw-bold">${p.nombre}</div>
                            <div class="small text-muted">${p.codigo}</div>
                            <input type="hidden" name="arrayidproducto[]" value="${p.id}">
                        </td>
                        <td><input type="number" name="arraycantidad[]" class="form-control form-control-sm t-qty" value="${qty.toFixed(0)}" step="1"></td>
                        <td><input type="number" name="arrayprecioventa[]" class="form-control form-control-sm t-price" value="${price.toFixed(2)}" step="0.01"></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <select class="form-select t-desc-type shadow-none" style="max-width: 60px; padding-left: 5px; padding-right: 15px;">
                                    ${descTypeHtml}
                                </select>
                                <input type="number" class="form-control t-desc-input" value="${descInputVal}" step="0.01">
                                <input type="hidden" name="arraydescuento[]" class="t-desc-hidden" value="0.00">
                            </div>
                            <small class="text-muted t-desc-label d-none" style="font-size: 0.75rem;">Equiv: Bs. <span class="t-desc-equiv">0.00</span></small>
                        </td>
                        <td class="text-end fw-bold align-middle">Bs. <span class="t-sub">0.00</span></td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-link text-danger p-0 delete-row"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                `;
                $('#tabla_detalle tbody').append(row);
                
                const newTr = $(`#row_${rowCount}`);
                recalculateRow(newTr);
                updateTotals();
                checkVisibility();
            }

            // Restore items from old input (e.g. after validation error)
            @if(old('arrayidproducto'))
                @foreach(old('arrayidproducto') as $index => $id)
                    (function() {
                        const pId = {{ $id }};
                        const p = PRODUCTOS.find(x => x.id == pId);
                        if (p) {
                            const qty = {{ old('arraycantidad')[$index] ?? 0 }};
                            const price = {{ old('arrayprecioventa')[$index] ?? 0 }};
                            const disc = {{ old('arraydescuento')[$index] ?? 0 }};
                            addItem(p, qty, price, disc);
                        }
                    })();
                @endforeach
            @endif

            function recalculateRow(tr) {
                const q = parseFloat(tr.find('.t-qty').val()) || 0;
                const p = parseFloat(tr.find('.t-price').val()) || 0;
                let dInput = parseFloat(tr.find('.t-desc-input').val()) || 0;
                const type = tr.find('.t-desc-type').val();
                
                const subtotalSinDesc = q * p;
                let descuentoMonto = 0;
                
                if (type === '%') {
                    descuentoMonto = subtotalSinDesc * (dInput / 100);
                    tr.find('.t-desc-label').removeClass('d-none');
                    tr.find('.t-desc-equiv').text(descuentoMonto.toFixed(2));
                } else {
                    descuentoMonto = dInput;
                    tr.find('.t-desc-label').addClass('d-none');
                }
                
                if (descuentoMonto > subtotalSinDesc) {
                    descuentoMonto = subtotalSinDesc;
                }

                tr.find('.t-desc-hidden').val(descuentoMonto.toFixed(2));
                const sub = (subtotalSinDesc - descuentoMonto).toFixed(2);
                tr.find('.t-sub').text(sub);
            }

            $(document).on('input change', '.t-qty, .t-price, .t-desc-input, .t-desc-type', function() {
                recalculateRow($(this).closest('tr'));
                updateTotals();
            });

            $(document).on('click', '.delete-row', function() {
                const tr = $(this).closest('tr');
                itemsAgregados.delete(parseInt(tr.data('id')));
                tr.remove();
                renumber();
                updateTotals();
                checkVisibility();
            });

            function renumber() {
                $('#tabla_detalle tbody tr').each((i, el) => $(el).find('.row-index').text(i + 1));
            }

            function updateTotals() {
                const storageId = $('#almacen_id').val();
                let total = 0;
                $('.t-sub').each(function() { total += parseFloat($(this).text()) || 0; });
                $('#label_total').text(total.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                $('#input_total').val(total.toFixed(2));
            }

            function checkVisibility() {
                const count = $('#tabla_detalle tbody tr').length;
                if (count > 0) { $('#btn_guardar, #btn_cancelar').fadeIn(); }
                else { $('#btn_guardar, #btn_cancelar').fadeOut(); }
            }

            $('#btn_cancelar').on('click', function() {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "Se borrarán todos los items agregados.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        resetVentaItems();
                    }
                });
            });

            $('#ventaForm').on('submit', function(e) {
                const errorList = $('#form-errors-list');
                const errorBox = $('#form-errors');
                errorList.empty();
                errorBox.hide();
                
                let errors = [];

                if(!$('#cliente_id').val()) {
                    errors.push('Debe seleccionar un <strong>Cliente</strong> obligatoriamente.');
                    $('#val_cliente').removeClass('d-none').addClass('d-block');
                } else {
                    $('#val_cliente').removeClass('d-block').addClass('d-none');
                }
                
                if(!$('#almacen_id').val()) {
                    errors.push('Debe seleccionar una <strong>Sucursal</strong> obligatoriamente.');
                    $('#val_sucursal').removeClass('d-none').addClass('d-block');
                } else {
                    $('#val_sucursal').removeClass('d-block').addClass('d-none');
                }
                
                if(!$('#estado_comprobante').val()) {
                    errors.push('Debe seleccionar un <strong>Comprobante</strong> obligatoriamente.');
                    $('#val_comprobante').removeClass('d-none').addClass('d-block');
                } else {
                    $('#val_comprobante').removeClass('d-block').addClass('d-none');
                }

                if($('#tabla_detalle tbody tr').length === 0) {
                    errors.push('Debe añadir al menos un <strong>Producto</strong> a la venta.');
                }

                if(errors.length > 0) {
                    e.preventDefault();
                    errors.forEach(err => {
                        errorList.append(`<li>${err}</li>`);
                    });
                    errorBox.hide().slideDown();
                    
                    if ($('#tabla_detalle tbody tr').length === 0) {
                        Swal.fire({
                            title: 'Error de validación',
                            text: 'Debe añadir al menos un producto a la tabla de venta.',
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                    }

                    $('html, body').animate({
                        scrollTop: $("#ventaForm").offset().top - 100
                    }, 500);
                }
            });
        });
    </script>
@endpush
