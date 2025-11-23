<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->string('relacionado_tipo');
            $table->unsignedBigInteger('relacionado_id');
            $table->string('nombre');
            $table->string('url');
            $table->string('tipo_mime')->nullable();
            $table->integer('size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['relacionado_tipo', 'relacionado_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('archivos');
    }
};
