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
            $table->decimal('general_discount_value', 10, 2)->default(0)->after('total_price');
            $table->enum('general_discount_type', ['percentage', 'fixed'])->default('fixed')->after('general_discount_value');
            $table->decimal('subtotal_before_discount', 10, 2)->nullable()->after('general_discount_type');
            $table->decimal('total_with_discount', 10, 2)->nullable()->after('subtotal_before_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['general_discount_value', 'general_discount_type', 'subtotal_before_discount', 'total_with_discount']);
        });
    }
};
