<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;
use App\Models\Persona;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $persona = Persona::create([
            'numero_documento' => '0',
            'documento_id'     => 1,
            'nombre_completo'  => 'Cliente General',
            'direccion'        => 'Sin dirección',
            'telefono'         => '00000000',
            'tipo_persona'     => 'natural',
        ]);

        Cliente::create([
            'persona_id' => $persona->id,
            'grupo_cliente_id' => 3,
            'estado' => 1
        ]);
    }
}
