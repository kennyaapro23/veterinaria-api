<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drops `lugar` and `direccion` columns from `citas` table.
     *
     * Note: This is destructive. If you need to preserve historical data,
     * create a backup or migrate the values to another table before running.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('citas')) {
            Schema::table('citas', function (Blueprint $table) {
                if (Schema::hasColumn('citas', 'lugar')) {
                    $table->dropColumn('lugar');
                }
                if (Schema::hasColumn('citas', 'direccion')) {
                    $table->dropColumn('direccion');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     * Re-adds `lugar` enum and `direccion` string (nullable).
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('citas')) {
            Schema::table('citas', function (Blueprint $table) {
                if (!Schema::hasColumn('citas', 'lugar')) {
                    $table->enum('lugar', ['clinica', 'a_domicilio', 'teleconsulta'])->default('clinica');
                }
                if (!Schema::hasColumn('citas', 'direccion')) {
                    $table->string('direccion')->nullable();
                }
            });
        }
    }
};
