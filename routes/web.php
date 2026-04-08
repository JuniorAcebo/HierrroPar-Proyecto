<?php

use App\Http\Controllers\Admin\AlmacenController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\LogoutController;
use App\Http\Controllers\Admin\MovimientoController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\TrasladoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VentaController;
use App\Models\Cliente;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');

Route::prefix('admin')->group(function () {

    // --- Autenticacion ---
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'index')->name('login');
        Route::post('/login', 'login');

    });

    Route::middleware('auth')->group(function () {

        // --- Dashboard ---
        Route::get('/panel', [DashboardController::class, 'index'])->name('panel');

        // --- Gestion de Productos ---
        Route::prefix('productos')->name('productos.')->group(function () {
            // Ajustes de Stock
            Route::get('/historial-ajustes', [ProductoController::class, 'historialAjustes'])->name('historialAjustes');
            Route::get('/crear-ajuste', [ProductoController::class, 'createAjuste'])->name('createAjuste');
            Route::post('/store-ajuste', [ProductoController::class, 'storeAjuste'])->name('storeAjuste');
            Route::get('/{producto}/ajuste-cantidad', [ProductoController::class, 'ajusteCantidad'])->name('ajusteCantidad');
            Route::post('/{producto}/ajuste-cantidad', [ProductoController::class, 'updateCantidad'])->name('updateCantidad');

            // Utilidades
            Route::get('/check-stock', [ProductoController::class, 'checkStock'])->name('checkStock');

            // reportes wip
            Route::post('/export-excel', [ProductoController::class, 'exportExcel'])->name('export.excel');
            Route::post('/export-pdf', [ProductoController::class, 'exportPdf'])->name('export.pdf');

            // categorias
            Route::get('/categorias', [ProductoController::class, 'indexCategorias'])->name('indexCategorias');
            Route::post('/categorias', [ProductoController::class, 'storeCategoria'])->name('storeCategoria');
            Route::put('/categorias/{categoria}', [ProductoController::class, 'updateCategoria'])->name('updateCategoria');
            Route::delete('/categorias/{categoria}', [ProductoController::class, 'destroyCategoria'])->name('destroyCategoria');

            // tipos de unidad
            Route::get('/tipo-unidades',[ProductoController::class, 'indexTipoUnidades'])->name('indexTipoUnidades');
            Route::post('/tipo-unidades',[ProductoController::class, 'storeTipoUnidad'])->name('storeTipoUnidad');
            Route::put('/tipo-unidades/{tipoUnidad}',[ProductoController::class, 'updateTipoUnidad'])->name('updateTipoUnidad');
            Route::delete('/tipo-unidades/{tipoUnidad}',[ProductoController::class, 'destroyTipoUnidad'])->name('destroyTipoUnidad');

            // marcas
            Route::get('/marcas',[ProductoController::class, 'indexMarcas'])->name('indexMarcas');
            Route::post('/marcas',[ProductoController::class, 'storeMarca'])->name('storeMarca');
            Route::put('/marcas/{marca}',[ProductoController::class, 'updateMarca'])->name('updateMarca');
            Route::delete('/marcas/{marca}', [ProductoController::class, 'destroyMarca'])->name('destroyMarca');
        });

        // --- Gestion de Ventas ---
        Route::prefix('ventas')->name('ventas.')->group(function () {
            Route::get('/check-stock', [VentaController::class, 'checkStock'])->name('check-stock');
            Route::get('/{venta}/pdf', [VentaController::class, 'pdf'])->name('pdf');
            Route::put('/{venta}/estado', [VentaController::class, 'updateEstado'])->name('updateEstado');
            
            // Exportacion
            Route::post('/export-excel', [VentaController::class, 'exportExcel'])->name('export.excel');
            Route::post('/export-pdf', [VentaController::class, 'exportPdf'])->name('export.pdf');
        });

        // --- Gestion de Traslados ---
        Route::prefix('traslados')->name('traslados.')->group(function () {
            Route::get('/productos/check-stock', [ProductoController::class, 'checkStock'])->name('productos.checkStock');

            Route::get('/{traslado}/detalles', [TrasladoController::class, 'getDetalles'])->name('getDetalles');
            Route::post('/export-excel', [TrasladoController::class, 'exportExcel'])->name('export-excel');
            Route::post('/export-pdf', [TrasladoController::class, 'exportPdf'])->name('export-pdf');
        });

        // --- Movimientos ---
        Route::prefix('movimientos')->name('movimientos.')->group(function () {
            Route::get('/',              [MovimientoController::class, 'index'])       ->name('index');
            Route::post('/export-excel', [MovimientoController::class, 'exportExcel']) ->name('export.excel');
            Route::post('/export-pdf',   [MovimientoController::class, 'exportPdf'])   ->name('export.pdf');
        });

        // --- Recursos---
        Route::resources([
            'productos'     => ProductoController::class,
            'ventas'        => VentaController::class,
            'traslados'     => TrasladoController::class,
            'users'         => UserController::class,
            'roles'         => RolController::class,
            'clientes'      => ClienteController::class,
            ]);

        Route::resource('almacenes', AlmacenController::class)->parameters([
            'almacenes' => 'almacen'
        ]);

        // --- (activar/desactivar) ---
        Route::patch('users/{user}/estado', [UserController::class, 'updateEstado'])->name('users.updateEstado');
        Route::patch('roles/{role}/estado', [RolController::class, 'updateEstado'])->name('roles.updateEstado');
        Route::patch('productos/{producto}/estado', [ProductoController::class, 'updateEstado'])->name('productos.updateEstado');
        Route::patch('clientes/{cliente}/estado', [ClienteController::class, 'updateEstado'])->name('clientes.updateEstado');
        Route::patch('almacenes/{almacen}/estado', [AlmacenController::class, 'updateEstado'])->name('almacenes.updateEstado');
        Route::patch('traslados/{traslado}/estado', [TrasladoController::class, 'updateEstado'])->name('traslados.updateEstado');

        // --- Logout ---   
        Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');
    });


    // --- Paginas de Error ---
    Route::get('/401', fn() => view('admin.pages.401'));
    Route::get('/404', fn() => view('admin.pages.404'));
    Route::get('/500', fn() => view('admin.pages.500'));
});
