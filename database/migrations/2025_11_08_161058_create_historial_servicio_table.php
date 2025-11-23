<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('historial_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historial_medico_id')->constrained('historial_medicos')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->integer('cantidad')->default(1); // Cuántas veces se aplicó el servicio
            $table->decimal('precio_unitario', 10, 2); // Precio al momento de aplicar (puede variar del precio base)
            $table->text('notas')->nullable(); // Notas específicas de este servicio en esta consulta
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index(['historial_medico_id', 'servicio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_servicio');
    }
};
