<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion');
            $table->string('tabla')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('cambios')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('user_id');
            $table->index(['tabla', 'registro_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
