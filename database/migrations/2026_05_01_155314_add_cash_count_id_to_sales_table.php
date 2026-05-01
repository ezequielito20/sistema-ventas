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
        if (!Schema::hasColumn('sales', 'cash_count_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('cash_count_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('cash_counts')
                    ->comment('ID del arqueo de caja asociado a la venta');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sales', 'cash_count_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['cash_count_id']);
                $table->dropColumn('cash_count_id');
            });
        }
    }
};