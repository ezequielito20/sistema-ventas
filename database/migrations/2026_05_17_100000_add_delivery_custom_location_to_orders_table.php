<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'delivery_custom_location')) {
                $table->text('delivery_custom_location')->nullable()->after('delivery_zone_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'delivery_custom_location')) {
                $table->dropColumn('delivery_custom_location');
            }
        });
    }
};
