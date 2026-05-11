<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('price_per_user', 10, 2)->default(0);
            $table->decimal('price_per_transaction', 10, 2)->default(0);
            $table->json('limits')->nullable()->comment('Ej: {"max_users":5, "max_transactions":1000, "max_products":500, "max_customers":2000}');
            $table->json('features')->nullable()->comment('Ej: ["sales", "purchases", "reports", "orders"]');
            $table->integer('max_users')->nullable();
            $table->integer('max_transactions')->nullable();
            $table->integer('max_products')->nullable();
            $table->integer('max_customers')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
