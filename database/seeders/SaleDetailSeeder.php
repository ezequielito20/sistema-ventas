<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
    }

    protected function adjustAutoIncrement(string $table)
    {
        $maxId = DB::table($table)->max('id') + 1;

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $sequenceName = $table . '_id_seq';
            DB::statement("SELECT setval('$sequenceName', $maxId)");
        } elseif ($driver === 'mysql' or $driver === 'mariadb') {
            DB::statement("ALTER TABLE $table AUTO_INCREMENT = $maxId");
        } elseif ($driver === 'sqlite') {
            DB::statement("UPDATE sqlite_sequence SET seq = $maxId WHERE name = '$table'");
        } else {
            throw new \Exception('Unsupported database driver: ' . $driver);
        }
    }
}
