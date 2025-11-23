<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['vacuna', 'tratamiento', 'baño', 'consulta', 'cirugía', 'otro'])->default('consulta');
            $table->integer('duracion_minutos')->default(30);
            $table->decimal('precio', 10, 2)->default(0);
            $table->boolean('requiere_vacuna_info')->default(false);
            $table->timestamps();
            
            $table->index('codigo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('servicios');
    }
};
