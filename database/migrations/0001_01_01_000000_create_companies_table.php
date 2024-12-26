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

            $table->string('country');
            $table->string('name');
            $table->string('business_type');
            $table->string('nit')->unique();  
            $table->string('phone');
            $table->string('email')->unique();
            $table->integer('tax_amount');
            $table->string('tax_name');
            $table->string('currency', 3);
            $table->text('address');
            $table->string('city');
            $table->string('state'); 
            $table->string('postal_code');
            $table->text('logo');

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
