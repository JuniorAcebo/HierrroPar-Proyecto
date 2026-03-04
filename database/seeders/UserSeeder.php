<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Rol;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar roles
        $adminRol    = Rol::where('name', 'ADMINISTRADOR')->first();
        $gerenteRol  = Rol::where('name', 'GERENTE')->first();
        $vendedorRol = Rol::where('name', 'VENDEDOR')->first();

        // ADMINISTRADOR
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrador Principal',
                'password' => bcrypt('12345678'),
                'estado' => 'activo',
                'role_id' => $adminRol->id
            ]
        );

        // GERENTE
        User::firstOrCreate(
            ['email' => 'gerente@gmail.com'],
            [
                'name' => 'Gerente General',
                'password' => bcrypt('12345678'),
                'estado' => 'activo',
                'role_id' => $gerenteRol->id
            ]
        );

        // VENDEDOR
        User::firstOrCreate(
            ['email' => 'vendedor@gmail.com'],
            [
                'name' => 'Vendedor Comercial',
                'password' => bcrypt('12345678'),
                'estado' => 'activo',
                'role_id' => $vendedorRol->id
            ]
        );
    }
}