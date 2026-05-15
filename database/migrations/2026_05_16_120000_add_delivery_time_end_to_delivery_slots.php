<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('delivery_slots')) {
            return;
        }

        if (! Schema::hasColumn('delivery_slots', 'delivery_time_end')) {
            Schema::table('delivery_slots', function (Blueprint $table) {
                $table->time('delivery_time_end')->nullable()->after('delivery_time');
            });
        }

        if (Schema::hasColumn('delivery_slots', 'delivery_time')
            && Schema::hasColumn('delivery_slots', 'delivery_time_end')
        ) {
            DB::table('delivery_slots')->update([
                'delivery_time_end' => DB::raw('delivery_time'),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('delivery_slots')) {
            return;
        }

        if (Schema::hasColumn('delivery_slots', 'delivery_time_end')) {
            Schema::table('delivery_slots', function (Blueprint $table) {
                $table->dropColumn('delivery_time_end');
            });
        }
    }
};
