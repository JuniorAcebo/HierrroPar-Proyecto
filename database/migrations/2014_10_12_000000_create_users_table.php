<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name',50);
            $table->string('email',50)->unique();
            $table->string('password',255);

            $table->enum('estado', [
                'activo',
                'inactivo'
            ])->default('activo');

            // Relación: muchos users pertenecen a un almacén
            $table->foreignId('almacen_id')
                  ->nullable()
                  ->constrained('almacenes')
                  ->restrictOnDelete();

            $table->foreignId('role_id')
                  ->nullable()
                  ->constrained('roles')
                  ->restrictOnDelete();
                  

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
