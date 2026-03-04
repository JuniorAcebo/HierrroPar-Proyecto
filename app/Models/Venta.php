<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'fecha_hora',
        'numero_comprobante',
        'total',
        'estado_comprobante',
        'estado',
        'cliente_id',
        'user_id',
        'nota_personal',
        'nota_cliente'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    //$venta = Venta::find($id);
    //$almacen = $venta->user->almacen; // aquí obtienes el almacén

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }
}
