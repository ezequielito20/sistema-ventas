<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixSequences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:fix-sequences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix PostgreSQL sequences for all tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing PostgreSQL sequences...');

        $tables = [
            'customers',
            'products',
            'suppliers',
            'categories',
            'sales',
            'purchases',
            'users',
            'roles',
            'permissions',
            'cash_counts',
            'cash_movements',
            'companies'
        ];

        foreach ($tables as $table) {
            try {
                $maxId = DB::table($table)->max('id');
                if ($maxId) {
                    DB::statement("SELECT setval('{$table}_id_seq', {$maxId})");
                    $this->info("✓ Fixed sequence for {$table} table (next ID: " . ($maxId + 1) . ")");
                } else {
                    $this->info("- No records found in {$table} table");
                }
            } catch (\Exception $e) {
                $this->error("✗ Error fixing sequence for {$table}: " . $e->getMessage());
            }
        }

        $this->info('Sequences fixed successfully!');
    }
}
