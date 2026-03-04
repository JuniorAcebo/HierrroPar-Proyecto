<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();

            // Relación 1 a 1 con personas
            $table->foreignId('persona_id')
                  ->unique()
                  ->constrained('personas')
                  ->restrictOnDelete();

            // Estado lógico TINY INT (activo/inactivo)
            $table->tinyInteger('estado')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
