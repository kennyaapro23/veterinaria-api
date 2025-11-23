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
        Schema::table('clientes', function (Blueprint $table) {
            $table->boolean('es_walk_in')->default(false)->after('user_id')
                  ->comment('Cliente sin cuenta registrada (atendido directamente por recepción)');
            
            // Hacer email nullable ya que walk-ins no requieren email
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('es_walk_in');
            
            // Revertir email a required (si es necesario)
            $table->string('email')->nullable(false)->change();
        });
    }
};
