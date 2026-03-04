<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_traslados', function (Blueprint $table) {

            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->restrictOnDelete();

            $table->foreignId('traslado_id')
                  ->constrained('traslados')
                  ->cascadeOnDelete();

            $table->decimal('cantidad', 10, 2);

            $table->primary(['producto_id', 'traslado_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_traslados');
    }
};
