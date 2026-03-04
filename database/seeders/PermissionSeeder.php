<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
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

            'ver-user','crear-user','editar-user','update-estado-user',
            
            'ver-perfil','editar-perfil',
            'ver-panel'
        ];

        foreach ($permisos as $permiso) {
            Permiso::firstOrCreate(['name' => $permiso]);
        }
    }
}