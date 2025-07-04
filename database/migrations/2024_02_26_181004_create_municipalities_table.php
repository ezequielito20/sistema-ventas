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
        Schema::create('municipalities', function (Blueprint $table) {
            $table->increments('id')->comment("Clave primaria de la tabla");
            $table->string('name')->comment('Nombre del municipio');
            
            $table->unsignedInteger('state_id')->comment('ID del Estado al que pertenece');
            $table->foreign('state_id')->references('id')->on('states')
                ->cascadeOnUpdate()->restrictOnDelete()
                ->comment('Relación con la tabla de Estados');

            $table->string('id_municipio', 20)->nullable()->index()->comment('ID del municipio migracion');

            $table->string('temp_municipality_id_mincyt', 10)->nullable()->comment('Codigo de municipios usado en semilla mincyt');

            $table->timestamps();
            $table->comment('Municipios');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('municipalities');
    }
};
