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
        Schema::create('factura_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->foreignId('historial_medico_id')->constrained('historial_medicos')->cascadeOnDelete();
            $table->decimal('subtotal', 10, 2); // Subtotal de este historial (total_servicios)
            $table->timestamps();
            
            // Índices
            $table->index(['factura_id', 'historial_medico_id']);
            $table->unique(['factura_id', 'historial_medico_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura_historial');
    }
};
