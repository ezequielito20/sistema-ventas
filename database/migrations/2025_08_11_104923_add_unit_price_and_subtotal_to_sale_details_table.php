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
        Schema::table('sale_details', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable()->after('product_id')->comment('Precio unitario del producto');
            $table->decimal('subtotal', 10, 2)->nullable()->after('unit_price')->comment('Subtotal del detalle (quantity * unit_price)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'subtotal']);
        });
    }
};
