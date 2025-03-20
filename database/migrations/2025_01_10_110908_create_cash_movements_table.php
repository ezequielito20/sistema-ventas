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
      Schema::create('cash_movements', function (Blueprint $table) {
         $table->id();

         $table->enum('type', ['income', 'expense'])->nullable()->comment('Tipo de movimiento: ingreso o gasto');
         $table->decimal('amount', 10, 2)->nullable()->comment('Monto del movimiento de caja');
         $table->string('description')->nullable()->comment('Descripción del movimiento de caja');
         
         $table->foreignId('cash_count_id')->nullable()->constrained('cash_counts')->onDelete('cascade')->comment('ID del conteo de caja asociado');
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('cash_movements');
   }
};
