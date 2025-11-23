<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('estado', ['pendiente', 'pagado', 'anulado'])->default('pendiente');
            $table->string('metodo_pago')->nullable();
            $table->json('detalles')->nullable();
            $table->timestamps();
            
            $table->index('cliente_id');
            $table->index('cita_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('facturas');
    }
};
