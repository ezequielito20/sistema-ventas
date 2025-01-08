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

         $table->string('company_name');
         $table->string('company_address');
         $table->string('company_phone');
         $table->string('company_email');

         $table->string('supplier_name');
         $table->string('supplier_phone');

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
