<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('user_count')->default(0);
            $table->integer('transaction_count')->default(0);
            $table->integer('sale_count')->default(0);
            $table->integer('product_count')->default(0);
            $table->integer('customer_count')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('calculated_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_usage_logs');
    }
};
