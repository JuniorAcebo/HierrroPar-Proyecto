<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoUnidad extends Model
{
    use HasFactory;
    protected $table = 'tipo_unidades';

    protected $fillable = ['nombre','descripcion','estado'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'tipo_unidad_id');
    }
}
