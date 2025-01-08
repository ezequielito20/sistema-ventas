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
      Schema::create('sales', function (Blueprint $table) {
         $table->id();

         $table->date('sale_date');
         $table->decimal('total_price', 10, 2);

         $table->unsignedBigInteger('company_id');
         $table->foreignId('customer_id')->constrained('customers');

         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('sales');
   }
};
