<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;
    protected $table = 'almacenes';

    protected $fillable = ['codigo','nombre','descripcion','direccion','estado'];

    public function users()
    {
        return $this->hasMany(User::class, 'almacen_id');
    }

    public function ajustesStock()
    {
        return $this->hasMany(AjusteStock::class, 'almacen_id');
    }

    public function trasladosOrigen()
    {
        return $this->hasMany(Traslado::class, 'origen_almacen_id');
    }

    public function trasladosDestino()
    {
        return $this->hasMany(Traslado::class, 'destino_almacen_id');
    }

    public function inventarioAlmacenes()
    {
        return $this->hasMany(InventarioAlmacen::class, 'almacen_id');
    }
}
