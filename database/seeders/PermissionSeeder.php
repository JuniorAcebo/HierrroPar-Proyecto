<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [

            'Panel' => ['ver-panel'],

            'Usuario' => ['ver-user','crear-user','editar-user','update-estado-user',],
            'Rol' => ['ver-role','crear-role','editar-role','update-estado-role','ver-permisos-role',],

            'Perfil' => ['ver-perfil','editar-perfil',],

            'Inventario - Reportes' => [
                'ver-movimiento','exportar-movimientos',
            ],

            'Producto' => ['ver-producto','crear-producto','editar-producto','update-estado-producto',
                        'exportar-productos','ajustar-stock-producto','ver-historial-stock-producto',],

            'Venta' => ['ver-venta','crear-venta','editar-venta','update-estado-venta',
                        'exportar-ventas','ver-pdf-venta',],

            'Compra' => ['ver-compra','crear-compra','editar-compra','update-estado-compra',],

            'Almacen' => [ 'ver-almacen','crear-almacen','editar-almacen','update-estado-almacen',],

            'Traslado' => ['ver-traslado', 'crear-traslado','editar-traslado','update-estado-traslado','exportar-traslados',],

            'Cliente' => ['ver-cliente','crear-cliente','editar-cliente','update-estado-cliente',],

            'Proveedor' => ['ver-proveedor','crear-proveedor','editar-proveedor','update-estado-proveedor',],

            'Grupo Cliente' => ['ver-grupocliente','crear-grupocliente','editar-grupocliente','update-estado-grupocliente',],

            'Categoria' => ['ver-categoria','crear-categoria','editar-categoria','eliminar-categoria',],

            'Tipo Unidad' => ['ver-tipounidad','crear-tipounidad','editar-tipounidad','eliminar-tipounidad'],
            
            'Marca' => ['ver-marca','crear-marca','editar-marca','eliminar-marca',],
        ];

        foreach ($permisos as $modulo => $listaPermisos) {

            foreach ($listaPermisos as $permiso) {

                Permiso::firstOrCreate([
                    'name' => $permiso
                ], [
                    'modulo' => $modulo
                ]);

            }

        }
    }
}