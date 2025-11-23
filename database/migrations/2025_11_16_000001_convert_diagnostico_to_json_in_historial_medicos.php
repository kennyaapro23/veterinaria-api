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
        // Add a temporary JSON column
        Schema::table('historial_medicos', function (Blueprint $table) {
            $table->json('diagnostico_json')->nullable()->after('tipo');
        });

        // Backfill: if existing diagnostico is valid JSON, cast it; otherwise wrap it into a JSON array
        // Use DB statements to leverage MySQL JSON functions
        DB::statement("UPDATE historial_medicos SET diagnostico_json = CAST(diagnostico AS JSON) WHERE diagnostico IS NOT NULL AND JSON_VALID(diagnostico)");
        DB::statement("UPDATE historial_medicos SET diagnostico_json = JSON_ARRAY(diagnostico) WHERE diagnostico IS NOT NULL AND NOT JSON_VALID(diagnostico)");

        // Drop the old text column and rename the json column to diagnostico
        Schema::table('historial_medicos', function (Blueprint $table) {
            $table->dropColumn('diagnostico');
        });

        // Rename diagnostico_json to diagnostico using direct SQL to avoid doctrine dependency
        DB::statement("ALTER TABLE historial_medicos CHANGE COLUMN diagnostico_json diagnostico JSON NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add temporary text column
        Schema::table('historial_medicos', function (Blueprint $table) {
            $table->text('diagnostico_text')->nullable()->after('tipo');
        });

        // Backfill: if current diagnostico is JSON and an array, take first element; otherwise cast to text
        // This is a best-effort reversal and may lose multiple entries (arrays will be reduced to first element)
        DB::statement("UPDATE historial_medicos SET diagnostico_text = CASE WHEN diagnostico IS NULL THEN NULL WHEN JSON_VALID(diagnostico) THEN JSON_UNQUOTE(JSON_EXTRACT(diagnostico, '$[0]')) ELSE diagnostico END");

        // Drop JSON column and rename text column back to diagnostico
        Schema::table('historial_medicos', function (Blueprint $table) {
            $table->dropColumn('diagnostico');
        });

        DB::statement("ALTER TABLE historial_medicos CHANGE COLUMN diagnostico_text diagnostico TEXT NULL");
    }
};
