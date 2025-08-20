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
        // Índices para la tabla customers
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['company_id', 'name']);
            $table->index(['company_id', 'created_at']);
            $table->index(['company_id', 'total_debt']);
        });

        // Índices para la tabla sales
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['company_id', 'customer_id']);
            $table->index(['company_id', 'sale_date']);
            $table->index(['customer_id', 'sale_date']);
            $table->index(['company_id', 'customer_id', 'sale_date']);
        });

        // Índices para la tabla debt_payments (si existe)
        if (Schema::hasTable('debt_payments')) {
            Schema::table('debt_payments', function (Blueprint $table) {
                $table->index(['company_id', 'customer_id']);
                $table->index(['customer_id', 'created_at']);
            });
        }

        // Índices para la tabla cash_counts
        Schema::table('cash_counts', function (Blueprint $table) {
            $table->index(['company_id', 'closing_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover índices de customers
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'name']);
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropIndex(['company_id', 'total_debt']);
        });

        // Remover índices de sales
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'customer_id']);
            $table->dropIndex(['company_id', 'sale_date']);
            $table->dropIndex(['customer_id', 'sale_date']);
            $table->dropIndex(['company_id', 'customer_id', 'sale_date']);
        });

        // Remover índices de debt_payments (si existe)
        if (Schema::hasTable('debt_payments')) {
            Schema::table('debt_payments', function (Blueprint $table) {
                $table->dropIndex(['company_id', 'customer_id']);
                $table->dropIndex(['customer_id', 'created_at']);
            });
        }

        // Remover índices de cash_counts
        Schema::table('cash_counts', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'closing_date']);
        });
    }
};
