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
      Schema::create('suppliers', function (Blueprint $table) {
         $table->id();

         $table->string('company_name')->nullable()->comment('Nombre de la empresa proveedora');
         $table->string('company_address')->nullable()->comment('Dirección de la empresa proveedora');
         $table->string('company_phone')->nullable()->comment('Teléfono de la empresa proveedora');
         $table->string('company_email')->nullable()->comment('Correo electrónico de la empresa proveedora');

         $table->string('supplier_name')->nullable()->comment('Nombre del proveedor');
         $table->string('supplier_phone')->nullable()->comment('Teléfono del proveedor');

         $table->unsignedBigInteger('company_id');

         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('suppliers');
   }
};
