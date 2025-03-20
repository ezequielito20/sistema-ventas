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
      Schema::create('sale_details', function (Blueprint $table) {
         $table->id();

         $table->integer('quantity')->nullable()->comment('Cantidad de productos vendidos');
         $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('cascade')->comment('ID de la venta asociada');
         $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade')->comment('ID del producto asociado');

         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('sale_details');
   }
};
