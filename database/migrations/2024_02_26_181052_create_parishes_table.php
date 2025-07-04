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
        Schema::create('parishes', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment("Clave primaria de la tabla");
            $table->string('name', 100)->comment("Nombre de la parroquia");
            $table->unsignedInteger('municipality_id')->comment('ID del municipio'); //->default(0)
            $table->foreign('municipality_id')->references('id')->on('municipalities')
                ->cascadeOnUpdate()->restrictOnDelete()
                ->comment('Relacion con la tabla de Municipios');
            $table->string('id_municipio', 20)->index()->comment('ID del municipio migracion');
            $table->string('temp_parish_id_mincyt', 10)->nullable()->comment('Codigo de parroquias usado en semilla mincyt');
            $table->timestamps();
            $table->comment('Parroquias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parishes');
    }
};
