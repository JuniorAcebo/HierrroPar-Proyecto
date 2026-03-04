<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoUnidad;

class TipoUnidadSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Unidad','descripcion' => 'Productos que se venden por unidad (piezas)'],
            ['nombre' => 'Metro','descripcion' => 'Productos que se venden por metro (cables, perfiles)'],
            ['nombre' => 'Docena','descripcion' => 'Conjunto de 12 unidades'],
            ['nombre' => 'Juego/Kit','descripcion' => 'Conjunto de piezas que forman un item'],
        ];

        foreach ($tipos as $tipo) {
            TipoUnidad::create($tipo);
        }
    }
}