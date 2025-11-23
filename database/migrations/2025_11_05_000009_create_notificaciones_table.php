<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('titulo');
            $table->text('cuerpo');
            $table->boolean('leida')->default(false);
            $table->json('meta')->nullable();
            $table->enum('sent_via', ['push', 'email', 'sms'])->default('push');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('tipo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notificaciones');
    }
};
