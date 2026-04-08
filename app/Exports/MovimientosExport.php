<?php

namespace App\Exports;

use App\Models\Movimiento;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MovimientosExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected Collection $movimientos;

    public function __construct(Collection $movimientos)
    {
        $this->movimientos = $movimientos;
    }

    public function collection(): Collection
    {
        return $this->movimientos->map(function (Movimiento $m) {

            if ($m->tipo === 'ajuste_stock') {
                $diff = ($m->cantidad_nueva ?? 0) - ($m->cantidad_anterior ?? 0);
                $diffStr = ($diff >= 0 ? '+' : '') . number_format($diff, 0);
            } elseif ($m->tipo === 'venta') {
                $diffStr = '-' . number_format($m->cantidad, 0);
            } else {
                $diffStr = '+' . number_format($m->cantidad, 0);
            }

            $fmt = fn($v) => $v !== null ? number_format($v, 0) : '—';

            return [
                'Fecha'                  => $m->fecha_hora ? $m->fecha_hora->format('d/m/Y H:i') : '—',
                'Tipo'                   => $this->formatTipo($m->tipo),
                'Referencia'             => $m->referencia_texto ?? '—',
                'Producto'               => $m->producto_nombre,
                'Almacén Origen'         => $m->almacen_origen  ?? '—',
                'Almacén Destino'        => $m->almacen_destino ?? '—',
                'Diferencia'             => $diffStr,
                'Cant. Movida'           => number_format($m->cantidad, 0),
                'Stock Inicial Origen'   => $fmt($m->stock_inicial_origen),
                'Stock Final Origen'     => $fmt($m->stock_final_origen),
                'Stock Inicial Destino'  => $fmt($m->stock_inicial_destino),
                'Stock Final Destino'    => $fmt($m->stock_final_destino),
                'Usuario'                => $m->usuario,
                'Motivo'                 => $m->motivo ?? '—',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha', 'Tipo', 'Referencia', 'Producto',
            'Almacén Origen', 'Almacén Destino',
            'Diferencia', 'Cant. Movida',
            'Stock Inicial Origen', 'Stock Final Origen',
            'Stock Inicial Destino', 'Stock Final Destino',
            'Usuario', 'Motivo',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 10,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1F2A38'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    private function formatTipo(string $tipo): string
    {
        return match ($tipo) {
            'ajuste_stock' => 'Ajuste de Stock',
            'venta'        => 'Venta',
            'traslado'     => 'Traslado',
            default        => ucfirst($tipo),
        };
    }
}