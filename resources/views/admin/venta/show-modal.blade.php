<div>
    <div class="row g-4 d-flex align-items-center mb-4">
        <div class="col-md-6">
            <h5 class="mb-1 text-primary fw-bold">VENTA #{{ $venta->numero_comprobante }}</h5>
            <p class="text-muted small mb-0">Vendedor: {{ $venta->user->name ?? 'Sistema' }}</p>
        </div>
        <div class="col-md-6 text-end">
            <div class="badge bg-light text-dark border">
                <i class="far fa-clock me-1"></i> 
                {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y H:i A') }}
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <h6 class="fw-semibold border-bottom pb-2 small text-uppercase letter-spacing-05">
            <i class="fas fa-info-circle me-2 text-muted"></i>Datos Generales
        </h6>
        <div class="row g-3 mt-1">
            <div class="col-md-4">
                <label class="info-subtext mb-1">Cliente</label>
                <div class="fw-bold">{{ optional(optional($venta->cliente)->persona)->nombre_completo ?? 'N/A' }}</div>
                <div class="small text-muted">{{ optional(optional($venta->cliente)->persona)->tipo_documento ?? 'Doc' }}: {{ optional(optional($venta->cliente)->persona)->numero_documento ?? 'N/A' }}</div>
            </div>
            <div class="col-md-4">
                <label class="info-subtext mb-1">Sucursal</label>
                <div class="fw-bold">{{ optional($venta->almacen)->nombre ?? optional($venta->user->almacen)->nombre ?? 'Tienda Principal' }}</div>
            </div>
            <div class="col-md-4">
                <label class="info-subtext mb-1">Tipo Comprobante</label>
                <div class="fw-bold">{{ ucfirst($venta->estado_comprobante) ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h6 class="fw-semibold border-bottom pb-2 small text-uppercase letter-spacing-05">
            <i class="fas fa-tasks me-2 text-muted"></i>Estados y Acciones
        </h6>
        <div class="row g-3 mt-1 mb-2">
            <div class="col-md-12">
                <label class="info-subtext mb-1">Estado de la Venta</label>
                @php
                    $isLocked = in_array($venta->estado, ['cancelada', 'completada']);
                    $btnClass = match($venta->estado) {
                        'completada' => 'btn-outline-success',
                        'cancelada' => 'btn-outline-danger',
                        default => 'btn-outline-warning'
                    };
                @endphp
                <div class="dropdown">
                    <button class="btn {{ $btnClass }} btn-sm w-50" type="button" disabled>
                        {{ ucfirst($venta->estado) }}
                        @if($isLocked) <i class="fas fa-lock ms-1"></i> @endif
                    </button>
                    <small class="text-muted d-block mt-1">
                        El estado se gestiona desde el panel principal de ventas.
                    </small>
                </div>
            </div>

            <div class="col-md-6">
                <label class="info-subtext mb-1">Nota Interna</label>
                <div class="p-2 border rounded bg-light small fst-italic text-muted">
                    {{ $venta->nota_personal ?: 'Sin nota interna' }}
                </div>
            </div>
            <div class="col-md-6">
                <label class="info-subtext mb-1">Nota Cliente</label>
                <div class="p-2 border rounded bg-light small fst-italic text-muted">
                    {{ $venta->nota_cliente ?: 'Sin nota al cliente' }}
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h6 class="fw-semibold border-bottom pb-2 small text-uppercase letter-spacing-05">
            <i class="fas fa-shopping-cart me-2 text-muted"></i>Detalles de la Venta
        </h6>
        
        <div class="table-responsive mt-2">
            <table class="table table-sm table-hover border-bottom">
                <thead>
                    <tr>
                        <th width="5%" class="bg-light">#</th>
                        <th class="bg-light">Producto</th>
                        <th class="text-end bg-light">Cantidad</th>
                        <th class="text-end bg-light">Precio Unit.</th>
                        <th class="text-end bg-light">Descuento</th>
                        <th class="text-end bg-light">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($venta->detalles as $index => $detalle)
                        <tr>
                            <td class="align-middle">{{ $index + 1 }}</td>
                            <td class="align-middle">
                                <div class="fw-bold text-dark">{{ $detalle->producto->nombre }}</div>
                                <small class="text-muted">{{ $detalle->producto->codigo }}</small>
                            </td>
                            <td class="text-end align-middle">{{ number_format($detalle->cantidad, 2) }}</td>
                            <td class="text-end align-middle">{{ number_format($detalle->precio_venta, 2) }}</td>
                            <td class="text-end text-danger align-middle">{{ number_format($detalle->descuento, 2) }}</td>
                            <td class="text-end fw-bold align-middle">{{ number_format(($detalle->cantidad * $detalle->precio_venta) - $detalle->descuento, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <td colspan="5" class="text-end fw-bold">TOTAL:</td>
                        <td class="text-end fw-bold text-primary fs-6">{{ number_format($venta->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="text-end small text-muted mt-3">
        Documento generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</div>
