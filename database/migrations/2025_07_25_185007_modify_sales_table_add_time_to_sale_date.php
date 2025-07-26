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
        Schema::table('sales', function (Blueprint $table) {
            // Cambiar el campo sale_date de date a datetime para incluir hora
            $table->datetime('sale_date')->nullable()->change()->comment('Fecha y hora de la venta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Revertir el campo sale_date de datetime a date
            $table->date('sale_date')->nullable()->change()->comment('Fecha de la venta');
        });
    }
};
