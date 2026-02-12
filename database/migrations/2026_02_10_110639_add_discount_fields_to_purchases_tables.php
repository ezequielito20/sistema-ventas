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
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('general_discount_value', 10, 2)->default(0)->after('total_price');
            $table->string('general_discount_type')->default('fixed')->after('general_discount_value'); // 'fixed' or 'percentage'
            $table->decimal('subtotal_before_discount', 10, 2)->nullable()->after('general_discount_type');
            $table->decimal('total_with_discount', 10, 2)->nullable()->after('subtotal_before_discount');
        });

        Schema::table('purchase_details', function (Blueprint $table) {
            $table->decimal('discount_value', 10, 2)->default(0)->after('purchase_id');
            $table->string('discount_type')->default('fixed')->after('discount_value'); // 'fixed' or 'percentage'
            $table->decimal('original_price', 10, 2)->nullable()->after('discount_type');
            $table->decimal('final_price', 10, 2)->nullable()->after('original_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'general_discount_value',
                'general_discount_type',
                'subtotal_before_discount',
                'total_with_discount'
            ]);
        });

        Schema::table('purchase_details', function (Blueprint $table) {
            $table->dropColumn([
                'discount_value',
                'discount_type',
                'original_price',
                'final_price'
            ]);
        });
    }
};
