<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 9px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ventas</h1>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Comprobante</th>
                <th>Cliente</th>
                <th>Sucursal</th>
                <th>Tipo</th>
                <th class="text-right">Total (Bs.)</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @php $totalReporte = 0; @endphp
            @foreach($ventas as $venta)
                @php $totalReporte += $venta->total; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y H:i') }}</td>
                    <td>{{ $venta->numero_comprobante }}</td>
                    <td>{{ optional($venta->cliente->persona)->nombre_completo ?? optional($venta->cliente->persona)->razon_social ?? 'N/A' }}</td>
                    <td>{{ optional($venta->almacen)->nombre ?? optional($venta->user->almacen)->nombre ?? 'Tienda Principal' }}</td>
                    <td>{{ ucfirst($venta->estado_comprobante) }}</td>
                    <td class="text-right">{{ number_format($venta->total, 2) }}</td>
                    <td>{{ ucfirst($venta->estado) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #eee;">
                <td colspan="5" class="text-right">TOTAL GENERAL:</td>
                <td class="text-right">Bs. {{ number_format($totalReporte, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Pagina <span class="pagenum"></span>
    </div>
</body>
</html>
