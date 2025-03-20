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
      Schema::create('purchase_tmps', function (Blueprint $table) {
         $table->id();

         $table->integer('quantity')->nullable()->comment('Cantidad de productos en la compra');
         $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade')->comment('ID del producto asociado');
         $table->string('session_id')->nullable()->comment('ID de la sesión de compra');

         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('purchase_tmps');
   }
};
