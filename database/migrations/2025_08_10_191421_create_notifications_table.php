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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Usuario que recibe la notificación');
            $table->string('type')->comment('Tipo de notificación (new_order, etc.)');
            $table->string('title')->comment('Título de la notificación');
            $table->text('message')->comment('Mensaje de la notificación');
            $table->json('data')->nullable()->comment('Datos adicionales (order_id, etc.)');
            $table->boolean('is_read')->default(false)->comment('Si la notificación fue leída');
            $table->timestamp('read_at')->nullable()->comment('Fecha de lectura');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
