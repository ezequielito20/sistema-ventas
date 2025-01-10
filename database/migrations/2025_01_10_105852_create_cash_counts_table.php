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

         $table->dateTime('opening_date');
         $table->dateTime('closing_date')->nullable();
         $table->decimal('initial_amount', 10, 2)->nullable();
         $table->decimal('final_amount', 10, 2)->nullable();
         $table->text('observations')->nullable();

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
