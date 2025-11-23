<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            // Add missing columns required by FacturaController
            $table->string('numero_factura', 50)->unique()->after('id');
            $table->date('fecha_emision')->after('numero_factura');
            $table->decimal('subtotal', 10, 2)->default(0)->after('fecha_emision');
            $table->decimal('impuestos', 10, 2)->default(0)->after('subtotal');
            $table->date('fecha_pago')->nullable()->after('estado');
            $table->text('notas')->nullable()->after('metodo_pago');
            
            // Add index for faster queries by year
            $table->index('fecha_emision');
        });
        
        // Backfill existing records: set fecha_emision and numero_factura for existing rows
        DB::statement("
            UPDATE facturas 
            SET 
                fecha_emision = DATE(created_at),
                numero_factura = CONCAT('FAC-', YEAR(created_at), '-', LPAD(id, 5, '0'))
            WHERE fecha_emision IS NULL OR numero_factura IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropIndex(['fecha_emision']);
            $table->dropColumn([
                'numero_factura',
                'fecha_emision',
                'subtotal',
                'impuestos',
                'fecha_pago',
                'notas',
            ]);
        });
    }
};
