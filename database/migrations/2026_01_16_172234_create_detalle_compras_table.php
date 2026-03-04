<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_compras', function (Blueprint $table) {

            $table->foreignId('compra_id')
                  ->constrained('compras')
                  ->cascadeOnDelete();

            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->restrictOnDelete();

            $table->decimal('cantidad', 10, 2)->unsigned();
            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2);

            // Primary key compuesta
            $table->primary(['compra_id', 'producto_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_compras');
    }
};
