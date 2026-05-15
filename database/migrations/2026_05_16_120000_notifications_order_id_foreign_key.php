<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications') || ! Schema::hasColumn('notifications', 'order_id')) {
            return;
        }

        if (! Schema::hasTable('orders')) {
            return;
        }

        if ($this->notificationsOrderIdForeignKeyExists()) {
            return;
        }

        DB::table('notifications')
            ->whereNotNull('order_id')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('orders')
                    ->whereColumn('orders.id', 'notifications.order_id');
            })
            ->update(['order_id' => null]);

        Schema::table('notifications', function (Blueprint $table): void {
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('notifications') || ! Schema::hasColumn('notifications', 'order_id')) {
            return;
        }

        if (! $this->notificationsOrderIdForeignKeyExists()) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropForeign(['order_id']);
        });
    }

    private function notificationsOrderIdForeignKeyExists(): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        return match ($driver) {
            'mysql', 'mariadb' => $this->mysqlOrderIdForeignExists($connection->getDatabaseName()),
            'pgsql' => $this->pgsqlOrderIdForeignExists(),
            'sqlite' => $this->sqliteOrderIdForeignExists(),
            default => false,
        };
    }

    private function mysqlOrderIdForeignExists(string $database): bool
    {
        $row = DB::selectOne(
            'SELECT 1 AS ok FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
             AND REFERENCED_TABLE_NAME = ?
             LIMIT 1',
            [$database, 'notifications', 'order_id', 'orders']
        );

        return $row !== null;
    }

    private function pgsqlOrderIdForeignExists(): bool
    {
        $schema = Schema::getConnection()->getConfig('schema') ?? 'public';

        $row = DB::selectOne(
            'SELECT 1 AS ok
             FROM information_schema.table_constraints tc
             INNER JOIN information_schema.key_column_usage kcu
                 ON tc.constraint_schema = kcu.constraint_schema
                 AND tc.constraint_name = kcu.constraint_name
             INNER JOIN information_schema.constraint_column_usage ccu
                 ON ccu.constraint_schema = tc.constraint_schema
                 AND ccu.constraint_name = tc.constraint_name
             WHERE tc.table_schema = ?
               AND tc.table_name = ?
               AND tc.constraint_type = ?
               AND kcu.column_name = ?
               AND ccu.table_name = ?
             LIMIT 1',
            [$schema, 'notifications', 'FOREIGN KEY', 'order_id', 'orders']
        );

        return $row !== null;
    }

    private function sqliteOrderIdForeignExists(): bool
    {
        foreach (DB::select('PRAGMA foreign_key_list(notifications)') as $fk) {
            $from = is_object($fk) ? ($fk->from ?? null) : ($fk['from'] ?? null);
            $table = is_object($fk) ? ($fk->table ?? null) : ($fk['table'] ?? null);
            if ($from === 'order_id' && $table === 'orders') {
                return true;
            }
        }

        return false;
    }
};
