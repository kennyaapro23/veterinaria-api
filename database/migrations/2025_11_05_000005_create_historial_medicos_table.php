<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historial_medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mascota_id')->constrained('mascotas')->cascadeOnDelete();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->dateTime('fecha')->useCurrent();
            $table->enum('tipo', ['consulta', 'vacuna', 'procedimiento', 'control', 'otro'])->default('consulta');
            $table->text('diagnostico')->nullable();
            $table->text('tratamiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('realizado_por')->nullable()->constrained('veterinarios')->nullOnDelete();
            $table->json('archivos_meta')->nullable();
            $table->timestamps();
            
            $table->index('mascota_id');
            $table->index('fecha');
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_medicos');
    }
};
