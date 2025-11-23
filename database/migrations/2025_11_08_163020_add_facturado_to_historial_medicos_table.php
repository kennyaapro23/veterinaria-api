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
        Schema::table('historial_medicos', function (Blueprint $table) {
            $table->boolean('facturado')->default(false)->after('archivos_meta');
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete()->after('facturado');
            
            $table->index('facturado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_medicos', function (Blueprint $table) {
            $table->dropForeign(['factura_id']);
            $table->dropColumn(['facturado', 'factura_id']);
        });
    }
};
