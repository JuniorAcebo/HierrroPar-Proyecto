<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('almacenes', function (Blueprint $table) {
            
            $table->id();
            $table->string('codigo');

            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('direccion')->nullable();

            //ACTIVO INACTIVO TINYINT 01
            $table->tinyInteger('estado')->default(1);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('almacenes');
    }
};
