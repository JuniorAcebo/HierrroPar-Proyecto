<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 7pt;
            color: #222;
            padding: 12px 14px;
        }
        .report-header { text-align: center; margin-bottom: 10px; }
        .report-header h1 { font-size: 12pt; color: #1f2a38; margin-bottom: 3px; }
        .report-header .subtitle { font-size: 7pt; color: #666; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead tr { background-color: #1f2a38; color: #ffffff; }
        thead th {
            padding: 5px 4px;
            font-size: 6.5pt;
            text-align: left;
            font-weight: bold;
            white-space: nowrap;
        }
        tbody tr:nth-child(even) { background-color: #f5f7f9; }
        tbody tr:nth-child(odd)  { background-color: #ffffff; }
        tbody td {
            padding: 4px 4px;
            border-bottom: 1px solid #e0e4ea;
            vertical-align: middle;
            font-size: 6.5pt;
        }

        .tipo-venta          { color: #1a7a4a; font-weight: bold; }
        .tipo-traslado-salida{ color: #c0392b; font-weight: bold; }
        .tipo-traslado-entrada{ color: #1a6fa8; font-weight: bold; }
        .tipo-ajuste         { color: #c8960a; font-weight: bold; }

        .diff-pos { color: #1a7a4a; font-weight: bold; }
        .diff-neg { color: #c0392b; font-weight: bold; }
        .diff-neu { color: #888; }

        .stock-cell { text-align: center; }
        .stock-arrow { color: #aaa; margin: 0 2px; }

        .report-footer {
            margin-top: 10px;
            text-align: right;
            font-size: 6pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .no-data { text-align: center; padding: 20px; color: #999; font-style: italic; }
    </style>
</head>
<body>

    <div class="report-header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">
            Generado el {{ $date }}
            @if($filtroTipo)
                &nbsp;·&nbsp; Tipo: <strong>{{ ucfirst(str_replace('_', ' ', $filtroTipo)) }}</strong>
            @endif
            @if($busqueda)
                &nbsp;·&nbsp; Búsqueda: <strong>"{{ $busqueda }}"</strong>
            @endif
            &nbsp;·&nbsp; Total: <strong>{{ $movimientos->count() }} registros</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:8%">Fecha</th>
                <th style="width:9%">Tipo</th>
                <th style="width:6%">Referencia</th>
                <th style="width:14%">Producto</th>
                <th style="width:10%">Almacén Origen</th>
                <th style="width:10%">Almacén Destino</th>
                <th style="width:6%;text-align:center;">Diferencia</th>
                <th style="width:12%;text-align:center;">Stock Inicial → Final</th>
                <th style="width:9%">Usuario</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $m)
            @php
                $esSalida  = $m->tipo === 'traslado' && str_contains($m->motivo ?? '', 'Salida');
                $esEntrada = $m->tipo === 'traslado' && str_contains($m->motivo ?? '', 'Entrada');

                // Stock a mostrar
                if ($esEntrada) {
                    $stockIni = $m->stock_inicial_destino;
                    $stockFin = $m->stock_final_destino;
                } else {
                    $stockIni = $m->stock_inicial_origen;
                    $stockFin = $m->stock_final_origen;
                }

                // Diferencia
                if ($m->tipo === 'ajuste_stock') {
                    $diff = ($m->cantidad_nueva ?? 0) - ($m->cantidad_anterior ?? 0);
                } elseif ($m->tipo === 'venta') {
                    $diff = -$m->cantidad;
                } elseif ($esSalida) {
                    $diff = -$m->cantidad;
                } else {
                    $diff = $m->cantidad;
                }

                $diffStr   = ($diff > 0 ? '+' : '') . number_format($diff, 0);
                $diffClass = $diff > 0 ? 'diff-pos' : ($diff < 0 ? 'diff-neg' : 'diff-neu');

                $tipoLabel = match(true) {
                    $m->tipo === 'venta'        => 'Venta',
                    $esSalida                   => 'Traslado Salida',
                    $esEntrada                  => 'Traslado Entrada',
                    $m->tipo === 'traslado'     => 'Traslado',
                    $m->tipo === 'ajuste_stock' => 'Ajuste',
                    default                     => ucfirst($m->tipo),
                };
                $tipoClass = match(true) {
                    $m->tipo === 'venta'    => 'tipo-venta',
                    $esSalida               => 'tipo-traslado-salida',
                    $esEntrada              => 'tipo-traslado-entrada',
                    $m->tipo === 'traslado' => 'tipo-traslado-entrada',
                    default                 => 'tipo-ajuste',
                };
            @endphp
            <tr>
                <td>{{ $m->fecha_hora->format('d/m/Y H:i') }}</td>
                <td class="{{ $tipoClass }}">{{ $tipoLabel }}</td>
                <td>{{ $m->referencia_texto ?? '—' }}</td>
                <td>{{ $m->producto_nombre }}</td>
                <td>{{ $m->almacen_origen  ?? '—' }}</td>
                <td>{{ $m->almacen_destino ?? '—' }}</td>
                <td class="{{ $diffClass }}" style="text-align:center;">{{ $diffStr }}</td>
                <td class="stock-cell">
                    @if($stockIni !== null)
                        {{ number_format($stockIni, 0) }}
                        <span class="stock-arrow">→</span>
                        <strong>{{ number_format($stockFin, 0) }}</strong>
                    @else
                        —
                    @endif
                </td>
                <td>{{ $m->usuario }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="no-data">Sin movimientos registrados</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="report-footer">
        Sistema de Inventario &mdash; {{ $date }}
    </div>

</body>
</html>