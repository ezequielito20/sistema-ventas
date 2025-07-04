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
        Schema::create('states', function (Blueprint $table) {
            $table->increments('id')->comment("Clave primaria de la tablaa");
            $table->unsignedSmallInteger('country_id')
                ->comment('ID del pais al que pertenece el estado');
            $table->foreign('country_id')->references('id')->on('countries')->cascadeOnUpdate()->restrictOnDelete()
                ->comment('Relacion con la tabla de paises');
            $table->string('name')->comment('Nombre del estado');
            $table->string('iso_3366_2', 10)->comment('Codigo ISO para principales subdivisiones de los paises');
            $table->string('category')->nullable()->comment('Nombre de la categoria');
            $table->string('zoom')->nullable()->comment('zoom del estado');
            $table->string('region')->nullable()->comment('Nombre de la region');
            $table->string('latitude_center')->nullable()->comment('Latitud para centrar el mapa con respecto al estado');
            $table->string('longitude_center')->nullable()->comment('Longitud para centrar el mapa con respecto al estado');
            $table->string('temp_state_id_mincyt', 10)->nullable()->comment('Codigo de estados usado en semilla mincyt');
            $table->timestamps();
            $table->comment('Estados, Provincias o Departamentos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('states');
    }
};
