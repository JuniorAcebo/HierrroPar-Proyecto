<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 50)->unique();
            $table->string('nombre', 150);
            $table->string('descripcion', 255)->nullable();

            $table->decimal('precio_compra', 10, 2)->default(0);
            $table->decimal('precio_venta', 10, 2)->default(0);

            // Control de inventario
            $table->integer('stock_minimo')->default(0);
            $table->integer('stock_maximo')->default(0);

            // Estado lógico TINYINT 0/1 (para activo/inactivo)
            $table->tinyInteger('estado')->default(1);

            // Relaciones
            $table->foreignId('marca_id')
                  ->constrained('marcas')
                  ->restrictOnDelete();

            $table->foreignId('categoria_id')
                  ->constrained('categorias')
                  ->restrictOnDelete();

            $table->foreignId('tipo_unidad_id')
                  ->constrained('tipo_unidades')
                  ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
