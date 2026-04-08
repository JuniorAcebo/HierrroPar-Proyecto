<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .date {
            text-align: right;
            font-size: 9px;
            margin-bottom: 10px;
        }

        .resumen {
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-left: 3px solid #444;
        }

        .resumen p {
            margin: 3px 0;
        }

        /* ===== TARJETAS ===== */

        .traslado-card {
            border: 1px solid #ddd;
            border-left: 4px solid #999;
            border-radius: 6px;
            margin-bottom: 12px;
            padding: 10px;
            page-break-inside: avoid;
        }

        /* estados con SOLO borde */
        .traslado-card.pendiente {
            border-left: 4px solid #aaa;
        }

        .traslado-card.en_curso {
            border-left: 4px solid #888;
        }

        .traslado-card.completado {
            border-left: 4px solid #555;
        }

        .traslado-card.cancelado {
            border-left: 4px solid #222;
        }

        .traslado-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .ruta {
            font-size: 11px;
        }

        .estado {
            font-size: 9px;
            padding: 2px 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #fff;
        }

        .info {
            font-size: 9px;
            margin-bottom: 5px;
        }

        /* ===== TABLAS LIMPIAS ===== */

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th {
            text-align: left;
            font-weight: bold;
            border-bottom: 1px solid #999;
            padding: 4px;
            font-size: 9px;
        }

        td {
            border-bottom: 1px solid #ddd;
            padding: 4px;
            font-size: 9px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* alineación columna cantidad */
        td:nth-child(2) {
            text-align: center;
            width: 80px;
        }

        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 5px;
            font-size: 10px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h3>{{ $title }}</h3>
    </div>

    <div class="date">
        Generado: {{ $date }}
    </div>

    <!-- RESUMEN -->
    <div class="resumen">
        <p><strong>Total traslados:</strong> {{ $resumen['total_traslados'] }}</p>

        @if($resumen['fecha_inicio'] && $resumen['fecha_fin'])
        <p><strong>Periodo:</strong> {{ $resumen['fecha_inicio'] }} - {{ $resumen['fecha_fin'] }}</p>
        @endif

        <p><strong>Total productos movidos:</strong> {{ $resumen['total_productos'] }}</p>

        @if(!is_null($resumen['costo_total']))
        <p><strong>Costo total:</strong> Bs {{ number_format($resumen['costo_total'], 2) }}</p>
        @endif

        <p><strong>Estados incluidos:</strong>
            {{ $resumen['estados']->map(fn($e) => ucfirst(str_replace('_',' ',$e)))->implode(', ') }}
        </p>
    </div>

    <!-- TARJETAS -->
    @foreach($traslados as $t)
    <div class="traslado-card {{ $t->estado }}">

        <!-- HEADER -->
        <div class="traslado-header">
            <div class="ruta">
                #{{ $t->id }} | {{ $t->origenAlmacen->nombre ?? 'N/A' }}
                → {{ $t->destinoAlmacen->nombre ?? 'N/A' }}
            </div>

            <div class="estado">
                {{ ucfirst(str_replace('_',' ',$t->estado)) }}
            </div>
        </div>

        <!-- INFO -->
        <div class="info">
            Inicio: {{ \Carbon\Carbon::parse($t->fecha_hora)->format('d/m/Y H:i') }}

            @if(in_array($t->estado, ['completado','cancelado']))
            | Finalizado: {{ \Carbon\Carbon::parse($t->updated_at)->format('d/m/Y H:i') }}
            @else
            | Última actualización: {{ \Carbon\Carbon::parse($t->updated_at)->format('d/m/Y H:i') }}
            @endif

            @if($includeUsuario)
            | Usuario: {{ $t->user->name ?? 'N/A' }}
            @endif

            @if($includeCosto)
            | Costo: Bs {{ number_format($t->costo_envio, 2) }}
            @endif
        </div>

        <!-- PRODUCTOS -->
        @if($includeDetalles && $t->detalleTraslados->count())
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($t->detalleTraslados as $d)
                <tr>
                    <td>{{ $d->producto->nombre ?? 'N/A' }}</td>
                    <td style="text-align:center;">{{ $d->cantidad }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total">
            Total unidades: {{ $t->detalleTraslados->sum('cantidad') }}
        </div>
        @endif

    </div>
    @endforeach

</body>

</html>