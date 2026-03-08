<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Administrador', 'Gerente', 'Vendedor'] as $name) {
            Rol::firstOrCreate(['name' => $name]);
        }
    }
}
