<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_almacenes', function (Blueprint $table) {

            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->restrictOnDelete();

            $table->foreignId('almacen_id')
                  ->constrained('almacenes')
                  ->restrictOnDelete();

            $table->decimal('stock', 10, 2)->default(0);

            $table->primary(['producto_id', 'almacen_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_almacenes');
    }
};