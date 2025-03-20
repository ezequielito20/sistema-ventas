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
      Schema::create('products', function (Blueprint $table) {
         $table->id();
         $table->string('code')->nullable()->comment('Código único del producto');
         $table->string('name')->nullable()->comment('Nombre del producto');
         $table->text('description')->nullable()->comment('Descripción detallada del producto');
         $table->text('image')->nullable()->comment('Ruta de la imagen del producto');
         $table->integer('stock')->nullable()->comment('Cantidad actual en inventario');
         $table->integer('min_stock')->nullable()->comment('Stock mínimo permitido');
         $table->integer('max_stock')->nullable()->comment('Stock máximo permitido');
         $table->decimal('purchase_price', 8, 2)->nullable()->comment('Precio de compra del producto');
         $table->decimal('sale_price', 8, 2)->nullable()->comment('Precio de venta al público');
         $table->date('entry_date')->nullable()->comment('Fecha de ingreso al inventario');

         $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');

         $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');

         $table->unsignedBigInteger('company_id')->nullable();

         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('products');
   }
};
