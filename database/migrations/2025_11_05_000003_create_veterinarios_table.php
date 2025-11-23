<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('veterinarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('matricula')->nullable()->unique();
            $table->string('especialidad')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->json('disponibilidad')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('veterinarios');
    }
};
