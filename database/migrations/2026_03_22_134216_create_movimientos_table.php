<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();

            // Tipo de movimiento
            $table->enum('tipo', ['ajuste_stock', 'venta', 'traslado']);

            // Referencia (sin FK)
            $table->unsignedBigInteger('referencia_id');
            $table->string('referencia_texto')->nullable();

            // Producto (desnormalizado)
            $table->string('producto_nombre');

            // Almacenes
            $table->string('almacen_origen')->nullable();
            $table->string('almacen_destino')->nullable();

            // Cantidades
            $table->decimal('cantidad', 12, 2);
            $table->decimal('cantidad_anterior', 12, 2)->nullable();
            $table->decimal('cantidad_nueva', 12, 2)->nullable();

            $table->decimal('stock_inicial_origen', 12, 2)->nullable();
            $table->decimal('stock_final_origen', 12, 2)->nullable();
            $table->decimal('stock_inicial_destino', 12, 2)->nullable();
            $table->decimal('stock_final_destino', 12, 2)->nullable();

            // Usuario y motivo
            $table->string('usuario');
            $table->text('motivo')->nullable();

            $table->timestamp('fecha_hora');
            $table->timestamps();

            // Índices
            $table->index('tipo');
            $table->index('fecha_hora');
            $table->index('referencia_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};