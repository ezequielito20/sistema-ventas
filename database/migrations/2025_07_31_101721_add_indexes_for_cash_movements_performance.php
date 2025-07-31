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
        // Índices para cash_movements
        Schema::table('cash_movements', function (Blueprint $table) {
            if (!Schema::hasIndex('cash_movements', 'idx_cash_movements_count_type')) {
                $table->index(['cash_count_id', 'type'], 'idx_cash_movements_count_type');
            }
            if (!Schema::hasIndex('cash_movements', 'idx_cash_movements_count_date')) {
                $table->index(['cash_count_id', 'created_at'], 'idx_cash_movements_count_date');
            }
            if (!Schema::hasIndex('cash_movements', 'idx_cash_movements_type_date')) {
                $table->index(['type', 'created_at'], 'idx_cash_movements_type_date');
            }
        });

        // Índices para cash_counts (evitar duplicados)
        Schema::table('cash_counts', function (Blueprint $table) {
            if (!Schema::hasIndex('cash_counts', 'idx_cash_counts_company_created')) {
                $table->index(['company_id', 'created_at'], 'idx_cash_counts_company_created');
            }
        });

        // Índices para sales y purchases
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasIndex('sales', 'idx_sales_company_created')) {
                $table->index(['company_id', 'created_at'], 'idx_sales_company_created');
            }
        });

        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasIndex('purchases', 'idx_purchases_company_created')) {
                $table->index(['company_id', 'created_at'], 'idx_purchases_company_created');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_cash_movements_count_type');
            $table->dropIndexIfExists('idx_cash_movements_count_date');
            $table->dropIndexIfExists('idx_cash_movements_type_date');
        });

        Schema::table('cash_counts', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_cash_counts_company_created');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_sales_company_created');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_purchases_company_created');
        });
    }
};
