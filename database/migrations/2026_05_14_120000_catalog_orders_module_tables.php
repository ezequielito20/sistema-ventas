<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_items')) {
            return;
        }

        Schema::create('company_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('instructions')->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('company_delivery_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // pickup | delivery
            $table->string('name');
            $table->text('instructions')->nullable();
            $table->string('pickup_address')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_delivery_method_id')->constrained('company_delivery_methods')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('extra_fee_usd', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('delivery_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_delivery_method_id')->constrained('company_delivery_methods')->cascadeOnDelete();
            $table->foreignId('delivery_zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedTinyInteger('max_orders')->default(1);
            $table->unsignedInteger('booked_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['company_id', 'starts_at']);
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->uuid('token')->unique();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(['cart_id', 'product_id']);
        });

        if (Schema::hasColumn('orders', 'product_id')) {
            Schema::rename('orders', 'orders_legacy_catalog_refactor');
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending');

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->foreignId('company_payment_method_id')->nullable()->constrained('company_payment_methods')->nullOnDelete();
            $table->foreignId('company_delivery_method_id')->constrained('company_delivery_methods');
            $table->foreignId('delivery_zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->foreignId('delivery_slot_id')->nullable()->constrained('delivery_slots')->nullOnDelete();

            $table->decimal('exchange_rate_used', 14, 4);
            $table->decimal('subtotal_products_usd', 14, 2);
            $table->decimal('payment_discount_percent_snapshot', 5, 2)->default(0);
            $table->decimal('payment_discount_amount_usd', 14, 2)->default(0);
            $table->decimal('delivery_fee_usd', 14, 2)->default(0);
            $table->decimal('total_usd', 14, 2);
            $table->decimal('total_bs', 18, 2);

            $table->string('public_summary_token', 64)->unique();
            $table->timestamp('public_summary_expires_at')->nullable();

            $table->text('payment_method_snapshot')->nullable();
            $table->text('delivery_method_snapshot')->nullable();

            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->index(['company_id', 'status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price_usd', 12, 2);
            $table->decimal('line_subtotal_usd', 14, 2);
            $table->decimal('line_discount_usd', 14, 2)->default(0);
            $table->decimal('line_total_usd', 14, 2);
            $table->timestamps();
        });

        if (Schema::hasTable('orders_legacy_catalog_refactor')) {
            Schema::drop('orders_legacy_catalog_refactor');
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            }
            if (! Schema::hasColumn('notifications', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('company_id');
                $table->index('order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'order_id')) {
                $table->dropIndex(['order_id']);
                $table->dropColumn('order_id');
            }
            if (Schema::hasColumn('notifications', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
        });

        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('delivery_slots');
        Schema::dropIfExists('delivery_zones');
        Schema::dropIfExists('company_delivery_methods');
        Schema::dropIfExists('company_payment_methods');

        // Restaurar tabla legacy orders no es trivial; en down solo recreamos estructura mínima antigua si hace falta
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('customer_name');
                $table->string('customer_phone');
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->integer('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_price', 10, 2);
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('pending');
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamp('processed_at')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }
};
