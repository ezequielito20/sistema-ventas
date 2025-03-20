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
      Schema::create('cash_counts', function (Blueprint $table) {
         $table->id();

         $table->dateTime('opening_date')->nullable()->comment('Fecha y hora de apertura del conteo de caja');
         $table->dateTime('closing_date')->nullable()->comment('Fecha y hora de cierre del conteo de caja');
         $table->decimal('initial_amount', 10, 2)->nullable()->comment('Monto inicial en caja al inicio del conteo');
         $table->decimal('final_amount', 10, 2)->nullable()->comment('Monto final en caja al cierre del conteo');
         $table->text('observations')->nullable()->comment('Observaciones relacionadas con el conteo de caja');

         $table->unsignedBigInteger('company_id');

         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('cash_counts');
   }
};
