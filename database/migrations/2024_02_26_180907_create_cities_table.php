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
        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id')->comment("Clave primaria de la tabla");
            $table->string('name')->comment('Nombre de la ciudad');
            $table->unsignedInteger('state_id')->comment('ID del Estado al que pertenece');
            $table->foreign('state_id')->references('id')->on('states')
                ->cascadeOnUpdate()->restrictOnDelete()
                ->comment('Relación con la tabla de Estados');
            $table->boolean('capital')->default(false)->comment('Es capital del país');
            $table->timestamps();
            $table->comment('Ciudades');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cities');
    }
};
