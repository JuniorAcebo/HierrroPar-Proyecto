<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_hora');
            $table->string('numero_comprobante');
            $table->decimal('total', 10, 2)->unsigned();
            $table->text('nota_personal')->nullable();
            
            // CANCELADA, COMPLETADA, PENDIENTE
            $table->enum('estado', ['cancelada', 'completada', 'pendiente'])
            ->default('pendiente');

            // Relaciones
            $table->foreignId('proveedor_id')->constrained('proveedores')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};