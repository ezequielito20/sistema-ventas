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
      Schema::create('purchase_details', function (Blueprint $table) {
         $table->id();

         $table->integer('quantity');
         $table->decimal('product_price', 10, 2);

         $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
         $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
         $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('purchase_details');
   }
};