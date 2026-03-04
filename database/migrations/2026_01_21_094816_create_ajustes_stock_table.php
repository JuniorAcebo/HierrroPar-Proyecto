<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ajustes_stock', function (Blueprint $table) {

            $table->id();

            // Almacén 
            $table->foreignId('almacen_id')
                  ->constrained('almacenes')
                  ->restrictOnDelete();

            // Producto 
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->restrictOnDelete();

            // Usuario
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // Datos del ajuste
            $table->timestamp('fecha_hora')->useCurrent();
            $table->decimal('cantidad_anterior', 10, 2);
            $table->decimal('cantidad_nueva', 10, 2);
            $table->string('motivo')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ajustes_stock');
    }
};
