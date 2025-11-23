<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('telefono')->nullable();
            $table->string('email')->unique();
            $table->string('documento_tipo')->nullable();
            $table->string('documento_num')->nullable();
            $table->string('direccion')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientes');
    }
};
