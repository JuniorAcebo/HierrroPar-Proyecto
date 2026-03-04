<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'precio_compra',
        'precio_venta',
        'stock_minimo',
        'stock_maximo',
        'estado',
        'marca_id',
        'categoria_id',
        'tipo_unidad_id'
    ];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function tipoUnidad()
    {
        return $this->belongsTo(TipoUnidad::class, 'tipo_unidad_id');
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    public function detalleTraslados()
    {
        return $this->hasMany(DetalleTraslado::class, 'producto_id');
    }

    public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class, 'producto_id');
    }

    public function inventarioAlmacenes()
    {
        return $this->hasMany(InventarioAlmacen::class, 'producto_id');
    }

    public function ajustesStock()
    {
        return $this->hasMany(AjusteStock::class, 'producto_id');
    }
}
