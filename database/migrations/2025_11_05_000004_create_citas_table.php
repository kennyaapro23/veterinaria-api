<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('mascota_id')->constrained('mascotas')->cascadeOnDelete();
            $table->foreignId('veterinario_id')->nullable()->constrained('veterinarios')->nullOnDelete();
            $table->dateTime('fecha');
            $table->integer('duracion_minutos')->default(30);
            $table->enum('estado', ['pendiente', 'confirmado', 'atendida', 'cancelada', 'reprogramada'])->default('pendiente');
            $table->string('motivo')->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('lugar', ['clinica', 'a_domicilio', 'teleconsulta'])->default('clinica');
            $table->string('direccion')->nullable();
            $table->timestamps();
            
            $table->index(['veterinario_id', 'fecha']);
            $table->index('cliente_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('citas');
    }
};
