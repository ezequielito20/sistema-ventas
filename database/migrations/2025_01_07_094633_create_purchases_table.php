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
      Schema::create('purchases', function (Blueprint $table) {
         $table->id();

         $table->date('purchase_date');
         $table->string('payment_receipt')->nullable();
         $table->decimal('total_price', 10, 2);

         $table->unsignedBigInteger('company_id');

         $table->timestamps();

         // $table->string('purchase_number')->unique(); // Número de compra único
         // $table->unsignedBigInteger('supplier_id');
         // $table->unsignedBigInteger('company_id');
         // $table->unsignedBigInteger('user_id'); // Usuario que registra la compra
         // $table->date('purchase_date');
         // $table->decimal('subtotal', 10, 2);
         // $table->decimal('tax', 10, 2);
         // $table->decimal('total', 10, 2);
         // $table->string('status')->default('pending'); // pending, completed, cancelled
         // $table->text('notes')->nullable();
         // $table->string('payment_method'); // cash, transfer, credit
         // $table->string('payment_status')->default('pending'); // pending, paid, partial
         // $table->decimal('amount_paid', 10, 2)->default(0);
         // $table->date('payment_due_date')->nullable();
         // $table->string('document_number')->nullable(); // Número de factura o documento del proveedor
         // $table->timestamps();
         // $table->softDeletes();

         // // Relaciones
         // $table->foreign('supplier_id')->references('id')->on('suppliers');
         // $table->foreign('company_id')->references('id')->on('companies');
         // $table->foreign('user_id')->references('id')->on('users');
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('purchases');
   }
};
