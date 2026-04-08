<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>VENTA #{{ $venta->numero_comprobante }}</title>
    <link rel="stylesheet" href="{{ asset('css/style_venta_pdf.css') }}">
</head>
<body>

@if($venta->estado_comprobante === 'factura')
    <!-- ==============================================
         DISEÑO DE FACTURA FORMAL
         ============================================== -->
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td width="60%" style="vertical-align: top;">
                <div style="font-size: 24px; font-weight: bold; color: #c0392b; margin-bottom: 5px;">HIERRO PAR</div>
                <div style="font-size: 10px; line-height: 1.4;">
                    <strong>Casa Matriz</strong><br>
                    Comercial Hierro-Par<br>
                    Ballivián entre 13 y 14<br>
                    Telf: 71190122<br>
                    Santa Cruz - Bolivia
                </div>
            </td>
            <td width="40%" style="vertical-align: top;">
                <div class="factura-box">
                    <div style="font-size: 14px; font-weight: bold;">NIT: 102938475019</div>
                    <div class="factura-title">FACTURA</div>
                    <div style="font-size: 12px; margin-bottom: 5px;"><strong>Nro. {{ str_pad($venta->numero_comprobante, 6, '0', STR_PAD_LEFT) }}</strong></div>
                    <div style="font-size: 9px; color: #333;">Autorización: 3948573901</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-box" style="border-radius: 8px; border-color: #aaa;">
        <table style="width: 100%; font-size: 11px;">
            <tr>
                <td width="15%" style="padding: 4px 0;"><strong>Lugar y Fecha:</strong></td>
                <td width="45%" style="padding: 4px 0;">Santa Cruz, {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y') }}</td>
                <td width="15%" style="padding: 4px 0;"><strong>NIT/CI:</strong></td>
                <td width="25%" style="padding: 4px 0;">{{ $venta->cliente->persona->numero_documento }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;"><strong>Señor(es):</strong></td>
                <td style="padding: 4px 0;">{{ $venta->cliente->persona->nombre_completo }}</td>
                <td style="padding: 4px 0;"><strong>Sucursal:</strong></td>
                <td style="padding: 4px 0;">{{ $venta->almacen->nombre ?? ($venta->user->almacen->nombre ?? 'N/A') }}</td>
            </tr>
        </table>
    </div>

    <table class="table-details" style="margin-top: 15px;">
        <thead>
            <tr>
                <th width="10%">CÓDIGO</th>
                <th width="10%" class="text-right">CANT.</th>
                <th>DESCRIPCIÓN</th>
                <th width="15%" class="text-right">P. UNIT (Bs)</th>
                <th width="15%" class="text-right">DESC (Bs)</th>
                <th width="15%" class="text-right">SUBTOTAL (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($venta->detalles as $detalle)
            <tr>
                <td>{{ $detalle->producto->codigo }}</td>
                <td class="text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                <td>{{ $detalle->producto->nombre }}</td>
                <td class="text-right">{{ number_format($detalle->precio_venta, 2) }}</td>
                <td class="text-right">{{ number_format($detalle->descuento, 2) }}</td>
                <td class="text-right">{{ number_format(($detalle->cantidad * $detalle->precio_venta) - $detalle->descuento, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right" style="padding-top:15px; font-size: 12px;"><strong>TOTAL A PAGAR:</strong></td>
                <td class="text-right" style="padding-top:15px; font-size: 14px; color: #c0392b;"><strong>Bs. {{ number_format($venta->total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="leyenda-factura">
        <p style="margin-bottom: 3px; font-weight: bold;">"ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO DE ÉSTA SERÁ SANCIONADO DE ACUERDO A LEY"</p>
        <p style="margin: 0;">Ley Nro 453: El proveedor deberá entregar el producto en las modalidades y plazos ofertados o convenidos.</p>
    </div>

@else
    <!-- ==============================================
         DISEÑO DE BOLETA / RECIBO (Simplicado)
         ============================================== -->
    <table class="header-table">
        <tr>
            <td width="60%">
                <div class="company-name" style="color: #27ae60;">HIERRO PAR</div>
                <div style="font-size:10px;">Comercial Hierro-Par - Telf: 71190122</div>
            </td>
            <td width="40%" class="text-right">
                <div class="doc-title" style="color: #27ae60;">BOLETA DE VENTA</div>
                <div style="font-size:12px;">Nro: {{ $venta->numero_comprobante }}</div>
                <div style="font-size:9px; color:#555;">{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y H:i A') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-box">
        <div class="section-title">DATOS DEL COMPROBANTE</div>
        <table class="info-table">
            <tr>
                <td width="50%">
                    <div class="label">CLIENTE</div>
                    <div class="value">{{ $venta->cliente->persona->nombre_completo }}</div>
                    <div style="font-size:9px;">{{ $venta->cliente->persona->tipo_documento }} {{ $venta->cliente->persona->numero_documento }}</div>
                </td>
                <td width="50%">
                    <div class="label">SUCURSAL</div>
                    <div class="value">{{ $venta->almacen->nombre ?? ($venta->user->almacen->nombre ?? 'N/A') }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label" style="margin-top:5px;">VENDEDOR</div>
                    <div class="value">{{ $venta->user->name ?? 'Sistema' }}</div>
                </td>
                <td>
                     <div class="label" style="margin-top:5px;">ESTADO</div>
                     <div class="value" style="font-weight: bold;">{{ $venta->estado !== 'cancelada' ? 'ACTIVA' : 'ANULADA' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-box">
        <div class="section-title">DETALLES DE LA COMPRA</div>
        <table class="table-details">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>PRODUCTO</th>
                    <th width="10%" class="text-right">CANT.</th>
                    <th width="15%" class="text-right">PRECIO</th>
                    <th width="15%" class="text-right">DESC.</th>
                    <th width="15%" class="text-right">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($venta->detalles as $index => $detalle)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $detalle->producto->nombre }}
                        <br><span style="color:#777; font-size:8px;">{{ $detalle->producto->codigo }}</span>
                    </td>
                    <td class="text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                    <td class="text-right">{{ number_format($detalle->precio_venta, 2) }}</td>
                    <td class="text-right">{{ number_format($detalle->descuento, 2) }}</td>
                    <td class="text-right">{{ number_format(($detalle->cantidad * $detalle->precio_venta) - $detalle->descuento, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right" style="padding-top:10px;"><strong>TOTAL:</strong></td>
                    <td class="text-right" style="padding-top:10px; font-size:12px;"><strong>Bs. {{ number_format($venta->total, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <table style="width: 100%; margin-top: 50px;">
        <tr>
            <td width="35%" class="text-center">
                <div style="border-top: 1px solid #777; padding-top: 5px; font-size: 9px;">
                    ENTREGUE CONFORME (Vendedor)
                </div>
            </td>
            <td width="30%"></td>
            <td width="35%" class="text-center">
                <div style="border-top: 1px solid #777; padding-top: 5px; font-size: 9px;">
                    RECIBI CONFORME (Cliente)
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Usuario: {{ auth()->user()->name }} | Impreso: {{ now()->format('d/m/Y H:i') }}<br>
        Documento no válido como crédito fiscal
    </div>
@endif

</body>
</html>

