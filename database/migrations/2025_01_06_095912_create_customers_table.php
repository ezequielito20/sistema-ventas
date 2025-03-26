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
      Schema::create('customers', function (Blueprint $table) {
         $table->id();

         $table->string('name')->nullable()->comment('Nombre del cliente');
         $table->string('nit_number')->nullable()->comment('Número de NIT del cliente');
         $table->string('phone')->nullable()->comment('Teléfono del cliente');
         $table->string('email')->nullable()->comment('Correo electrónico del cliente');
         $table->decimal('total_debt', 10, 2)->default(0)->comment('Deuda total del cliente');

         $table->foreignId('company_id')->nullable()->constrained('companies')->comment('ID de la empresa asociada al cliente');
         
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('customers');
   }
};
