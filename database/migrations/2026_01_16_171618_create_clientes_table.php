<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            // 1 a 1 con personas
            $table->foreignId('persona_id')
                  ->unique()
                  ->constrained('personas')
                  ->restrictOnDelete();

            // Muchos clientes pertenecen a un grupo
            $table->foreignId('grupo_cliente_id')
                  ->constrained('grupos_clientes')
                  ->restrictOnDelete();

            // TINYINT 01 ACTIVO INACTIVO
            $table->tinyInteger('estado')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
