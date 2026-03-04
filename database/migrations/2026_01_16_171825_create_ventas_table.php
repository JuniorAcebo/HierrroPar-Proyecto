<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_hora');
            $table->string('numero_comprobante')->unique();
            $table->decimal('total', 10, 2)->unsigned();
            
            $table->text('nota_personal')->nullable();
            $table->text('nota_cliente')->nullable();

            // Tipo de comprobante: BOLETA o FACTURA
            $table->enum('estado_comprobante', ['boleta', 'factura']);

            // Estado de la venta: CANCELADA, COMPLETADA, PENDIENTE
            $table->enum('estado', ['cancelada', 'completada', 'pendiente'])
                  ->default('pendiente');

            // Relaciones (cardinalidades)
            $table->foreignId('cliente_id')
                  ->constrained('clientes')
                  ->restrictOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
