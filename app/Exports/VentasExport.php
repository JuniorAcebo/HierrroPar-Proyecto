<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VentasExport implements FromCollection, WithHeadings, WithMapping
{
    protected $ventas;

    public function __construct($ventas)
    {
        $this->ventas = $ventas;
    }

    public function collection()
    {
        return $this->ventas;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha y Hora',
            'Nro. Comprobante',
            'Cliente',
            'Sucursal',
            'Tipo Comprobante',
            'Total (Bs.)',
            'Estado',
            'Vendedor',
            'Nota'
        ];
    }

    public function map($venta): array
    {
        return [
            $venta->id,
            $venta->fecha_hora,
            $venta->numero_comprobante,
            optional($venta->cliente->persona)->nombre_completo ?? optional($venta->cliente->persona)->razon_social ?? 'N/A',
            optional($venta->almacen)->nombre ?? optional($venta->user->almacen)->nombre ?? 'Tienda Principal',
            ucfirst($venta->estado_comprobante),
            number_format($venta->total, 2),
            ucfirst($venta->estado),
            optional($venta->user)->name ?? 'N/A',
            $venta->nota_personal ?? $venta->nota_cliente ?? ''
        ];
    }
}