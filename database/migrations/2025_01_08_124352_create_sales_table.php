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

         $table->date('sale_date')->nullable()->comment('Fecha de la venta');
         $table->decimal('total_price', 10, 2)->nullable()->comment('Precio total de la venta');

         $table->unsignedBigInteger('company_id')->nullable()->comment('ID de la empresa asociada a la venta');
         $table->foreignId('customer_id')->nullable()->constrained('customers')->comment('ID del cliente asociado a la venta');
         $table->foreignId('cash_count_id')->nullable()->constrained('cash_counts')->comment('ID del arqueo de caja asociado a la venta');

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
