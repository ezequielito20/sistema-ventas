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
        Schema::create('currencies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('country_id')->unsigned();
            $table->string('name');
            $table->string('code');
            $table->tinyInteger('precision')->default(2);
            $table->string('symbol');
            $table->string('symbol_native');
            $table->tinyInteger('symbol_first')->default(1);
            $table->string('decimal_mark', 1);
            $table->string('thousands_separator', 1);
            $table->timestamps();
            
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
