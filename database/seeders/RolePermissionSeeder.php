<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Permiso;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ADMINISTRADOR
        $admin = Rol::where('name', 'ADMINISTRADOR')->first();
        $allPermIds = Permiso::pluck('id');
        $admin->permisos()->sync($allPermIds);

        // GERENTE
        $gerente = Rol::where('name', 'GERENTE')->first();
        $gerenteNames = [
            'ver-tipounidad','crear-tipounidad','editar-tipounidad',
            'ver-categoria','crear-categoria','editar-categoria',
            'ver-marca','crear-marca','editar-marca',

            'ver-almacen','crear-almacen','editar-almacen','update-estado-almacen',
            'ver-traslado','crear-traslado','editar-traslado','update-estado-traslado',

            'ver-cliente','crear-cliente','editar-cliente','update-estado-cliente',
            'ver-proveedor','crear-proveedor','editar-proveedor','update-estado-proveedor',

            'ver-compra','crear-compra','editar-compra','update-estado-compra',
            'ver-venta','crear-venta','editar-venta','update-estado-venta',

            'ver-producto','crear-producto','editar-producto','update-estado-producto','exportar-productos',
            'ajustar-stock','ver-historial-stock',
            'ver-grupocliente','crear-grupocliente','editar-grupocliente','update-estado-grupocliente',
            'ver-perfil','editar-perfil',
            'ver-panel'
        ];
        $gerentePermIds = Permiso::whereIn('name', $gerenteNames)->pluck('id');
        $gerente->permisos()->sync($gerentePermIds);

        // VENDEDOR
        $vendedor = Rol::where('name', 'VENDEDOR')->first();
        $vendedorNames = [
            'ver-venta','crear-venta','ver-producto','ver-cliente','ver-perfil'
        ];
        $vendedorPermIds = Permiso::whereIn('name', $vendedorNames)->pluck('id');
        $vendedor->permisos()->sync($vendedorPermIds);
    }
}
