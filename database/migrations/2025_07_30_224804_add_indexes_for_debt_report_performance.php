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
            // Índice compuesto para consultas de deudas por empresa
            $table->index(['company_id', 'total_debt'], 'customers_company_debt_index');
            
            // Índice para búsquedas por nombre, teléfono, email
            $table->index(['name', 'phone', 'email', 'nit_number'], 'customers_search_index');
            
            // Índice para ordenamiento por deuda
            $table->index('total_debt', 'customers_debt_index');
        });

        // Índices para la tabla sales
        Schema::table('sales', function (Blueprint $table) {
            // Índice compuesto para consultas de ventas por cliente y empresa
            $table->index(['customer_id', 'company_id'], 'sales_customer_company_index');
            
            // Índice para filtros por fecha de venta
            $table->index('sale_date', 'sales_date_index');
            
            // Índice compuesto para consultas de ventas por fecha y empresa
            $table->index(['sale_date', 'company_id'], 'sales_date_company_index');
        });

        // Índices para la tabla cash_counts
        Schema::table('cash_counts', function (Blueprint $table) {
            // Índice para consultas de arqueos abiertos por empresa
            $table->index(['company_id', 'closing_date'], 'cash_counts_company_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices de customers
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_company_debt_index');
            $table->dropIndex('customers_search_index');
            $table->dropIndex('customers_debt_index');
        });

        // Eliminar índices de sales
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_customer_company_index');
            $table->dropIndex('sales_date_index');
            $table->dropIndex('sales_date_company_index');
        });

        // Eliminar índices de cash_counts
        Schema::table('cash_counts', function (Blueprint $table) {
            $table->dropIndex('cash_counts_company_status_index');
        });
    }
};
