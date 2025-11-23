<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agendas_disponibilidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veterinario_id')->constrained('veterinarios')->cascadeOnDelete();
            $table->tinyInteger('dia_semana')->comment('0=domingo, 6=sábado');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('intervalo_minutos')->default(30);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('veterinario_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('agendas_disponibilidad');
    }
};
