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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('country')->nullable()->comment('País de la empresa');
            $table->string('name')->nullable()->comment('Nombre de la empresa');
            $table->string('business_type')->nullable()->comment('Tipo de negocio de la empresa');
            $table->string('nit')->unique()->nullable()->comment('Número de Identificación Tributaria (NIT)');
            $table->string('phone')->nullable()->comment('Número de teléfono de la empresa');
            $table->string('email')->unique()->nullable()->comment('Correo electrónico de la empresa');
            $table->integer('tax_amount')->nullable()->comment('Monto del impuesto aplicado');
            $table->string('tax_name')->nullable()->comment('Nombre del impuesto');
            $table->string('currency', 20)->nullable()->comment('Moneda utilizada por la empresa');
            $table->text('address')->nullable()->comment('Dirección de la empresa');
            $table->string('city')->nullable()->comment('Ciudad donde se ubica la empresa');
            $table->string('state')->nullable()->comment('Estado donde se ubica la empresa');
            $table->string('postal_code')->nullable()->comment('Código postal de la empresa');
            $table->text('logo')->nullable()->comment('Ruta del logo de la empresa');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
