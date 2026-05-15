<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('delivery_slots')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'scheduled_delivery_date')) {
                $table->date('scheduled_delivery_date')->nullable()->after('delivery_slot_id');
            }
        });

        Schema::table('delivery_slots', function (Blueprint $table) {
            if (! Schema::hasColumn('delivery_slots', 'weekday_iso')) {
                $table->unsignedTinyInteger('weekday_iso')->nullable()->after('delivery_zone_id');
            }
            if (! Schema::hasColumn('delivery_slots', 'delivery_time')) {
                $table->time('delivery_time')->nullable()->after('weekday_iso');
            }
        });

        if (
            Schema::hasColumn('delivery_slots', 'starts_at')
            && Schema::hasColumn('delivery_slots', 'weekday_iso')
        ) {
            DB::table('delivery_slots')
                ->orderBy('id')
                ->lazyById()
                ->each(function (object $row): void {
                    if (empty($row->starts_at)) {
                        return;
                    }
                    $dt = Carbon::parse($row->starts_at)->timezone(config('app.timezone'));
                    DB::table('delivery_slots')
                        ->where('id', $row->id)
                        ->update([
                            'weekday_iso' => (int) $dt->isoWeekday(),
                            'delivery_time' => $dt->format('H:i:s'),
                        ]);
                });
        }

        if (
            Schema::hasColumn('orders', 'scheduled_delivery_date')
            && Schema::hasColumn('delivery_slots', 'starts_at')
        ) {
            $rows = DB::table('orders')
                ->whereNotNull('delivery_slot_id')
                ->whereNull('scheduled_delivery_date')
                ->select(['id', 'delivery_slot_id'])
                ->get();

            foreach ($rows as $row) {
                $starts = DB::table('delivery_slots')->whereKey((int) $row->delivery_slot_id)->value('starts_at');
                if (! $starts) {
                    continue;
                }
                DB::table('orders')->whereKey((int) $row->id)->update([
                    'scheduled_delivery_date' => Carbon::parse($starts)->timezone(config('app.timezone'))->format('Y-m-d'),
                ]);
            }
        }

        try {
            Schema::table('delivery_slots', function (Blueprint $table) {
                $table->dropIndex(['company_id', 'starts_at']);
            });
        } catch (Throwable) {
            try {
                DB::statement('DROP INDEX IF EXISTS delivery_slots_company_id_starts_at_index');
            } catch (Throwable) {
                // ignore drivers that name indexes differently
            }
        }

        if (
            Schema::hasColumn('delivery_slots', 'starts_at')
            && Schema::hasColumn('delivery_slots', 'ends_at')
            && Schema::hasColumn('delivery_slots', 'booked_count')
        ) {
            Schema::table('delivery_slots', function (Blueprint $table) {
                $table->dropColumn(['starts_at', 'ends_at', 'booked_count']);
            });
        }

        DB::table('delivery_slots')
            ->whereNull('weekday_iso')
            ->update(['weekday_iso' => 1, 'delivery_time' => '09:00:00']);

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE delivery_slots ALTER COLUMN weekday_iso SET NOT NULL');
            DB::statement('ALTER TABLE delivery_slots ALTER COLUMN delivery_time SET NOT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('delivery_slots')) {
            return;
        }

        if (
            Schema::hasColumn('delivery_slots', 'weekday_iso')
            && Schema::getConnection()->getDriverName() === 'pgsql'
        ) {
            try {
                DB::statement('ALTER TABLE delivery_slots ALTER COLUMN weekday_iso DROP NOT NULL');
                DB::statement('ALTER TABLE delivery_slots ALTER COLUMN delivery_time DROP NOT NULL');
            } catch (Throwable) {
                // ignore
            }
        }

        Schema::table('delivery_slots', function (Blueprint $table) {
            if (! Schema::hasColumn('delivery_slots', 'starts_at')) {
                $table->dateTime('starts_at')->nullable();
                $table->dateTime('ends_at')->nullable();
                $table->unsignedInteger('booked_count')->default(0);
            }
        });

        Schema::table('delivery_slots', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_slots', 'weekday_iso')) {
                $table->dropColumn(['weekday_iso', 'delivery_time']);
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'scheduled_delivery_date')) {
                $table->dropColumn('scheduled_delivery_date');
            }
        });

        if (Schema::hasColumn('delivery_slots', 'starts_at')) {
            Schema::table('delivery_slots', function (Blueprint $table) {
                $table->index(['company_id', 'starts_at']);
            });
        }
    }
};
