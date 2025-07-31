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
        // Índices para productos
        Schema::table('products', function (Blueprint $table) {
            $table->index(['company_id', 'stock'], 'idx_products_company_stock');
            $table->index(['code', 'company_id'], 'idx_products_code_company');
        });

        // Índices para clientes
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['company_id', 'name'], 'idx_customers_company_name');
        });

        // Índices para ventas
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['company_id', 'sale_date'], 'idx_sales_company_date');
            $table->index(['customer_id', 'sale_date'], 'idx_sales_customer_date');
        });

        // Índices para detalles de venta
        Schema::table('sale_details', function (Blueprint $table) {
            $table->index(['sale_id'], 'idx_sale_details_sale');
            $table->index(['product_id'], 'idx_sale_details_product');
        });

        // Índices para caja
        Schema::table('cash_counts', function (Blueprint $table) {
            $table->index(['company_id', 'closing_date'], 'idx_cash_counts_company_closing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_company_stock');
            $table->dropIndex('idx_products_code_company');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_company_name');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_company_date');
            $table->dropIndex('idx_sales_customer_date');
        });

        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropIndex('idx_sale_details_sale');
            $table->dropIndex('idx_sale_details_product');
        });

        Schema::table('cash_counts', function (Blueprint $table) {
            $table->dropIndex('idx_cash_counts_company_closing');
        });
    }
};
