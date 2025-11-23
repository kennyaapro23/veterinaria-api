<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('especie');
            $table->string('raza')->nullable();
            $table->enum('sexo', ['macho', 'hembra', 'desconocido'])->default('desconocido');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('color')->nullable();
            $table->string('chip_id')->nullable()->unique();
            $table->string('foto_url')->nullable();
            $table->timestamps();
            
            $table->index('cliente_id');
            $table->index('chip_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mascotas');
    }
};
