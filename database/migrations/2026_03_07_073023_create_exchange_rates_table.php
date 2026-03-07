<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('rate', 12, 2)->default(134.00)->comment('Tasa BCV Bs/USD');
            $table->string('source', 50)->default('manual')->comment('manual | auto_bcv');
            $table->string('currency_pair', 10)->default('USD/VES');
            $table->timestamp('fetched_at')->nullable()->comment('Cuando se obtuvo de la API');
            $table->timestamps();
        });

        // Insertar registro inicial con valor por defecto
        DB::table('exchange_rates')->insert([
            'rate'          => 134.00,
            'source'        => 'manual',
            'currency_pair' => 'USD/VES',
            'fetched_at'    => now(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
