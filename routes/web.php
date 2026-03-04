<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\LogoutController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\UserController;
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
        Route::get('/', [DashboardController::class, 'index'])->name('panel');

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
            Route::patch('/{producto}/estado', [ProductoController::class, 'updateEstado'])->name('updateEstado');

            // reportes wip
            Route::post('/export-excel', [ProductoController::class, 'exportExcel'])->name('export.excel');
            Route::post('/export-pdf', [ProductoController::class, 'exportPdf'])->name('export.pdf');
        });

        // --- usuarios---
        Route::resources([
            'productos'     => ProductoController::class,
            'users'         => UserController::class
        ]);

        // --- Estado de usuario (activar/desactivar) ---
        Route::patch('users/{user}/estado', [UserController::class, 'updateEstado'])->name('users.updateEstado');


        // --- Logout ---
        Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');
    });


    // --- Paginas de Error ---
    Route::get('/401', fn() => view('admin.pages.401'));
    Route::get('/404', fn() => view('admin.pages.404'));
    Route::get('/500', fn() => view('admin.pages.500'));
});
