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
            $table->json('tratamiento_json')->nullable()->after('diagnostico');
        });

        // Backfill: if existing tratamiento is valid JSON, cast it; otherwise wrap it into a JSON array
        DB::statement("UPDATE historial_medicos SET tratamiento_json = CAST(tratamiento AS JSON) WHERE tratamiento IS NOT NULL AND JSON_VALID(tratamiento)");
        DB::statement("UPDATE historial_medicos SET tratamiento_json = JSON_ARRAY(tratamiento) WHERE tratamiento IS NOT NULL AND NOT JSON_VALID(tratamiento)");

        // Drop the old text column and rename the json column to tratamiento
        Schema::table('historial_medicos', function (Blueprint $table) {
            $table->dropColumn('tratamiento');
        });

        // Rename tratamiento_json to tratamiento using direct SQL to avoid doctrine dependency
        DB::statement("ALTER TABLE historial_medicos CHANGE COLUMN tratamiento_json tratamiento JSON NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add temporary text column
        Schema::table('historial_medicos', function (Blueprint $table) {
            $table->text('tratamiento_text')->nullable()->after('diagnostico');
        });

        // Backfill: if current tratamiento is JSON and an array, take first element; otherwise cast to text
        // This is a best-effort reversal and may lose structure (arrays will be reduced to first element)
        DB::statement("UPDATE historial_medicos SET tratamiento_text = CASE WHEN tratamiento IS NULL THEN NULL WHEN JSON_VALID(tratamiento) THEN JSON_UNQUOTE(JSON_EXTRACT(tratamiento, '$[0]')) ELSE tratamiento END");

        // Drop JSON column and rename text column back to tratamiento
        Schema::table('historial_medicos', function (Blueprint $table) {
            $table->dropColumn('tratamiento');
        });

        DB::statement("ALTER TABLE historial_medicos CHANGE COLUMN tratamiento_text tratamiento TEXT NULL");
    }
};
