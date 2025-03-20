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

         $table->integer('quantity')->nullable()->comment('Cantidad de productos en la compra');

         $table->foreignId('purchase_id')->nullable()->constrained('purchases')->onDelete('cascade')->comment('ID de la compra asociada');
         $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('cascade')->comment('ID del proveedor asociado');
         $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade')->comment('ID del producto asociado');

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
