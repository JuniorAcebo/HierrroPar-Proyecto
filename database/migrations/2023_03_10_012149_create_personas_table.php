<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();

            // Tipo de persona (natural o juridica)
            $table->enum('tipo_persona', ['natural', 'juridica']);

            $table->string('numero_documento');

            // Relación muchos a uno con documentos (catálogo)
            $table->foreignId('documento_id')
                  ->constrained('documentos')
                  ->restrictOnDelete();

            $table->timestamps();

            // Evita duplicar mismo número para mismo tipo
            $table->unique(['numero_documento', 'documento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
