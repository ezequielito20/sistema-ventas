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
            $table->decimal('discount_value', 10, 2)->default(0)->after('sale_price');
            $table->enum('discount_type', ['percentage', 'fixed'])->default('fixed')->after('discount_value');
            $table->decimal('original_price', 10, 2)->nullable()->after('discount_type');
            $table->decimal('final_price', 10, 2)->nullable()->after('original_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn(['discount_value', 'discount_type', 'original_price', 'final_price']);
        });
    }
};
