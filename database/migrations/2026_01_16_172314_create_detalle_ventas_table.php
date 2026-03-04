<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_ventas', function (Blueprint $table) {

            $table->foreignId('venta_id')
                  ->constrained('ventas')
                  ->cascadeOnDelete();

            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->restrictOnDelete();

            $table->decimal('cantidad', 10, 2)->unsigned();
            $table->decimal('precio_venta', 10, 2);
            
            // Primary key compuesta
            $table->primary(['venta_id', 'producto_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
