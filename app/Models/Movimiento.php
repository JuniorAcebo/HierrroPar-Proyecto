<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;

    protected $table = 'movimientos';

    protected $fillable = [
        'tipo',
        'referencia_id',
        'referencia_texto',
        'producto_nombre',
        'almacen_origen',
        'almacen_destino',
        'cantidad',
        'cantidad_anterior',
        'cantidad_nueva',
        'stock_inicial_origen',
        'stock_final_origen',
        'stock_inicial_destino',
        'stock_final_destino',
        'usuario',
        'motivo',
        'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora'            => 'datetime',
        'cantidad'              => 'float',
        'cantidad_anterior'     => 'float',
        'cantidad_nueva'        => 'float',
        'stock_inicial_origen'  => 'float',
        'stock_final_origen'    => 'float',
        'stock_inicial_destino' => 'float',
        'stock_final_destino'   => 'float',
    ];

    public function scopeVentas($query)   { return $query->where('tipo', 'venta'); }
    public function scopeTraslados($query){ return $query->where('tipo', 'traslado'); }
    public function scopeAjustes($query)  { return $query->where('tipo', 'ajuste_stock'); }
}