<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traslados', function (Blueprint $table) {
            $table->id();

            $table->dateTime('fecha_hora');

            // Almacén origen
            $table->foreignId('origen_almacen_id')
                  ->constrained('almacenes')
                  ->restrictOnDelete();

            // Almacén destino
            $table->foreignId('destino_almacen_id')
                  ->constrained('almacenes')
                  ->restrictOnDelete();

            $table->decimal('costo_envio', 10, 2)->default(0);

            // Usuario que realiza el traslado
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // Estado del traslado: PENDIENTE, CANCELADO, COMPLETADO
            $table->enum('estado', ['pendiente', 'cancelado', 'completado'])
                  ->default('pendiente');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traslados');
    }
};
