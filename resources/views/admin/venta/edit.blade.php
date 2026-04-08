@extends('admin.layouts.app')

@section('title', 'Editar venta')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/style_create_edit_venta.css') }}">
@endpush

@section('content')
    @include('admin.layouts.partials.alert')
    <div class="container-fluid px-4">
        <h1 class="mt-4 fs-4 fw-bold">Editar Venta #{{ str_pad($venta->id, 8, '0', STR_PAD_LEFT) }}</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('panel') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('ventas.index') }}">Ventas</a>
                </li>
                <li class="breadcrumb-item active">Editar Venta</li>
            </ol>

            <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>

        </div>
    </div>

    <form action="{{ route('ventas.update', $venta->id) }}" method="post" id="ventaForm">
        @csrf
        @method('PUT')
        <div class="container-lg">
            <div class="border-section">
                <div class="section-title"><i class="fas fa-edit"></i> Encabezado de Venta</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Cliente:</label>
                        <select name="cliente_id" id="cliente_id" class="form-control form-control-sm selectpicker show-tick" data-live-search="true" title="Seleccione cliente" required>
                            @foreach ($clientes as $item)
                                <option value="{{ $item->id }}" {{ $venta->cliente_id == $item->id ? 'selected' : '' }}>
                                    {{ $item->persona->razon_social ?? $item->persona->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sucursal (Almacén Origen):</label>
                        <select name="almacen_id" id="almacen_id" class="form-control form-control-sm selectpicker" required>
                            @foreach ($almacenes as $item)
                                <option value="{{ $item->id }}" {{ $venta->almacen_id == $item->id ? 'selected' : '' }}>
                                    {{ $item->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Comprobante:</label>
                        <select name="estado_comprobante" id="estado_comprobante" class="form-control form-control-sm selectpicker" required>
                            <option value="boleta" {{ $venta->estado_comprobante == 'boleta' ? 'selected' : '' }}>Boleta</option>
                            <option value="factura" {{ $venta->estado_comprobante == 'factura' ? 'selected' : '' }}>Factura</option>
                            <option value="ticket" {{ $venta->estado_comprobante == 'ticket' ? 'selected' : '' }}>Ticket</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Número:</label>
                        <input type="text" name="numero_comprobante" class="form-control form-control-sm" value="{{ $venta->numero_comprobante }}" readonly>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Estado Venta:</label>
                        <select name="estado" id="estado" class="form-control form-control-sm selectpicker" required>
                            <option value="pendiente" {{ $venta->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="completada" {{ $venta->estado == 'completada' ? 'selected' : '' }}>Completada</option>
                            <option value="cancelada" {{ $venta->estado == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha Venta:</label>
                        <input type="datetime-local" name="fecha_hora" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Vendedor:</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $venta->user->name }}" readonly>
                        <input type="hidden" name="user_id" value="{{ $venta->user_id }}">
                    </div>
                </div>
            </div>

            <div class="border-section">
                <div class="section-title"><i class="fas fa-boxes"></i> Articulos de la Venta</div>
                
                <div class="search-wrapper mb-3">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="producto_search" class="form-control form-control-sm" placeholder="Añadir mas productos a esta venta...">
                    <div class="products-dropdown" id="products_dropdown"></div>
                </div>

                <div class="product-selection-card" id="selection_card">
                    <div class="selection-title">
                        <span><i class="fas fa-plus-circle"></i> <span id="sel_name">Producto</span></span>
                        <span id="sel_stock_badge" class="badge-stock stock-ok">Stock: 0</span>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Código</label>
                            <input type="text" id="sel_codigo" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Precio Venta (Bs.)</label>
                            <input type="number" id="sel_precio" class="form-control form-control-sm" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" id="sel_cantidad" class="form-control form-control-sm" value="1" step="1">
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="btn_add_item" class="btn btn-primary btn-sm w-100 h-32"><i class="fas fa-plus me-1"></i> Añadir Item</button>
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
                                <th width="12%">Descuento</th>
                                <th width="15%">Subtotal</th>
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
                        <textarea name="nota_personal" class="form-control form-control-sm" rows="2">{{ $venta->nota_personal }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nota Cliente:</label>
                        <textarea name="nota_cliente" class="form-control form-control-sm" rows="2">{{ $venta->nota_cliente }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary btn-sm px-4">Volver</a>
                    <button id="btn_guardar" type="submit" class="btn btn-primary btn-sm px-5 fw-bold">Actualizar Venta</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <script>
        $(document).ready(function() {
            const PRODUCTOR_RAW = '{!! addslashes(json_encode($productos)) !!}';
            const PRODUCTOS = JSON.parse(PRODUCTOR_RAW);
            const EXISTENTES = @json($venta->detalles->load('producto'));
            let itemsAgregados = new Set();
            let selectedItem = null;
            let rowCount = 0;
            let previousAlmacenId = $('#almacen_id').val();
            let stockRequestSeq = 0;
            let suppressAlmacenChange = false;

            $('.selectpicker').selectpicker();

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
                $('#selection_card').hide();
                selectedItem = null;
                $('#producto_search').val('');
            }

            // --- LOAD DATA ---
            EXISTENTES.forEach(det => {
                addItem(det.producto, parseFloat(det.cantidad), parseFloat(det.precio_venta), parseFloat(det.descuento || 0));
            });

            // --- SEARCH LOGIC ---
            $('#producto_search').on('input', function() {
                const q = $(this).val().toLowerCase().trim();
                const dropdown = $('#products_dropdown');
                if (q.length < 1) { dropdown.hide(); return; }
                
                const matches = PRODUCTOS.filter(p => p.nombre.toLowerCase().includes(q) || p.codigo.toLowerCase().includes(q)).slice(0, 10);
                
                dropdown.empty();
                if (matches.length === 0) {
                    dropdown.append('<div class="p-3 text-muted small text-center">No encontrado</div>').show();
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
                    
                    if (!isAdded) item.on('click', () => selectProduct(p));
                    dropdown.append(item);
                });
                dropdown.show();
            });

            async function selectProduct(p) {
                $('#products_dropdown').hide();
                $('#producto_search').val('');
                const storageId = $('#almacen_id').val();
                
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
                        $('#sel_precio').val(parseFloat(p.precio_venta).toFixed(2));
                        $('#sel_cantidad').val('1.000').focus();
                        
                        setStockBadge(res.stock, ilimitado);

                        $('#selection_card').slideDown();
                    }
                } catch (e) { Swal.fire("Error", "Error al consultar stock", "error"); }
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
                        Swal.fire("Error", "Error al consultar stock", "error");
                        return;
                    }

                    const ilimitado = !!res.ilimitado;
                    selectedItem.stock = ilimitado ? Number.POSITIVE_INFINITY : parseFloat(res.stock);
                    selectedItem.ilimitado = ilimitado;
                    setStockBadge(res.stock, ilimitado);
                } catch (err) {
                    Swal.fire("Error", "Error al consultar stock", "error");
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
                    const msg = selectedItem.ilimitado ? 'Stock ilimitado' : 'Stock insuficiente';
                    Swal.fire("Error", msg, "error");
                    return;
                }

                addItem(selectedItem, qty, price, 0);
                $('#selection_card').hide();
                selectedItem = null;
            });

            function addItem(p, qty, price, desc) {
                rowCount++;
                itemsAgregados.add(p.id);
                const sub = ((qty * price) - desc).toFixed(2);
                
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
                        <td><input type="number" name="arraydescuento[]" class="form-control form-control-sm t-desc" value="${desc.toFixed(2)}" step="0.01"></td>
                        <td class="text-end fw-bold">Bs. <span class="t-sub">${sub}</span></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-link text-danger p-0 delete-row"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                `;
                $('#tabla_detalle tbody').append(row);
                updateTotals();
            }

            $(document).on('input', '.t-qty, .t-price, .t-desc', function() {
                const tr = $(this).closest('tr');
                const q = parseFloat(tr.find('.t-qty').val()) || 0;
                const p = parseFloat(tr.find('.t-price').val()) || 0;
                const d = parseFloat(tr.find('.t-desc').val()) || 0;
                const sub = ((q * p) - d).toFixed(2);
                tr.find('.t-sub').text(sub);
                updateTotals();
            });

            $(document).on('click', '.delete-row', function() {
                const tr = $(this).closest('tr');
                itemsAgregados.delete(parseInt(tr.data('id')));
                tr.remove();
                renumber();
                updateTotals();
            });

            function renumber() { $('#tabla_detalle tbody tr').each((i, el) => $(el).find('.row-index').text(i + 1)); }
            function updateTotals() {
                let total = 0;
                $('.t-sub').each(function() { total += parseFloat($(this).text()) || 0; });
                $('#label_total').text(total.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                $('#input_total').val(total.toFixed(2));
            }
            
            // Validate form
            $('#ventaForm').on('submit', function(e) {
                if ($('#tabla_detalle tbody tr').length === 0) {
                    e.preventDefault();
                    Swal.fire("Error", "Debe agregar al menos un producto a la venta.", "warning");
                    return false;
                }
            });
        });
    </script>
@endpush
