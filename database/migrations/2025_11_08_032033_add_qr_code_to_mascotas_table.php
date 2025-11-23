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
        Schema::table('mascotas', function (Blueprint $table) {
            // Código QR único por mascota
            $table->string('qr_code', 100)->unique()->nullable()->after('id');
            
            // Campos adicionales para info de emergencia
            $table->string('alergias')->nullable()->after('foto_url');
            $table->text('condiciones_medicas')->nullable()->after('alergias');
            $table->string('tipo_sangre', 20)->nullable()->after('condiciones_medicas');
            $table->string('microchip', 50)->nullable()->after('tipo_sangre');
            
            // Índices para búsquedas rápidas
            $table->index('qr_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            $table->dropColumn([
                'qr_code', 
                'alergias', 
                'condiciones_medicas', 
                'tipo_sangre',
                'microchip'
            ]);
        });
    }
};
