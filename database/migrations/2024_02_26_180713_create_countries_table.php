<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->smallIncrements('id')->comment("Clave primaria de la tabla");
            $table->string('name')->comment('Nombre del pais');
            $table->string('iso_3366_1', 10)->comment('Codigo ISO 3366-1 alpha-2 para paises');
            $table->string('temp_country_id_mincyt', 36)->nullable()->comment('Codigo de paises usado en semilla mincyt (UUID con nombre id)');
            $table->timestamps();
            $table->comment('Paises');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('countries');
    }
};
