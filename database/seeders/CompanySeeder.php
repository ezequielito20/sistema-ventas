<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'id' => 1,
            'country' => '162',
            'name' => 'Test Company',
            'business_type' => 'Commercial',
            'nit' => '1234567890',
            'phone' => '+1-555-0123',
            'email' => 'superAdmin@gmail.com',
            'tax_amount' => 8,
            'tax_name' => 'Sales Tax',
            'currency' => 'USD - $',
            'address' => '123 Main Street',
            'city' => '538',        // New York City ID (primera ciudad de Estados Unidos en el seeder)
            'state' => '30032',     // New York State ID
            'postal_code' => '10001',
            'logo' => 'logo.png',
        ]);
        
        $this->adjustAutoIncrement('companies');
        
        $this->command->info('Se ha creado 1 empresa.');
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
