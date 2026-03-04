<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';

    protected $fillable = [
        'fecha_hora',
        'numero_comprobante',
        'total',
        'nota_personal',
        'estado',
        'proveedor_id',
        'user_id'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
	
	//$compra = Compra::find($id);
    //$almacen = $compra->user->almacen; // aquí obtienes el almacén

    public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }
}