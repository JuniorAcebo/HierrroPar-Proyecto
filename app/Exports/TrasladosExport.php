<?php

namespace App\Exports;

use App\Models\Traslado;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TrasladosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $traslados;
    protected $includeDetalles;

    public function __construct(Collection $traslados, $includeDetalles = true)
    {
        $this->traslados = $traslados;
        $this->includeDetalles = $includeDetalles;
    }

    public function collection()
    {
        return $this->traslados;
    }

    public function headings(): array
    {
        $base = [
            'ID',
            'Fecha',
            'Origen',
            'Destino',
            'Total Unidades',
            'Responsable',
            'Estado',
            'Costo Envío'
        ];

        if ($this->includeDetalles) {
            $base[] = 'Productos';
        }

        return $base;
    }

    public function map($traslado): array
    {
        $row = [
            $traslado->id,
            $traslado->fecha_hora 
        ? \Carbon\Carbon::parse($traslado->fecha_hora)->format('d/m/Y H:i') 
        : '',
            $traslado->origenAlmacen->nombre ?? 'N/A',
            $traslado->destinoAlmacen->nombre ?? 'N/A',
            $traslado->total_items ?? $traslado->detalleTraslados->sum('cantidad'),
            $traslado->user->name ?? 'N/A',
            ucfirst(str_replace('_', ' ', $traslado->estado)),
            $traslado->costo_envio
        ];

        if ($this->includeDetalles) {
            $productos = $traslado->detalleTraslados->map(function ($d) {
                return ($d->producto->nombre ?? 'N/A') . ' (x' . $d->cantidad . ')';
            })->implode(' | ');

            $row[] = $productos;
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // fila 1 en negrita
        ];
    }
}