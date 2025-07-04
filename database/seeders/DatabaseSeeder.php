<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\CityTableSeeder;
use Database\Seeders\StateTableSeeder;
use Database\Seeders\ParishTableSeeder;
use Database\Seeders\CountryTableSeeder;
use Spatie\Permission\Models\Permission;
use Database\Seeders\MunicipalityTableSeeder;

class DatabaseSeeder extends Seeder
{
   /**
    * Seed the application's database.
    */
   public function run(): void
   {
      // User::factory(10)->create();

      $company = Company::create([
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
         'state' => '30032', // New York State ID
         'postal_code' => '10001',
         'logo' => 'logo.png',
      ]);

      User::factory()->create([
         'name' => 'superAdmin',
         'email' => 'superAdmin@gmail.com',
         'password' => Hash::make('12345'),
         'company_id' => 1,
      ]);
      $this->call([CountryTableSeeder::class]);
      $this->call([StateTableSeeder::class]);
      // $this->call([MunicipalityTableSeeder::class]);
      // $this->call([ParishTableSeeder::class]);
      $this->call([CityTableSeeder::class]);
      $this->call([CurrencySeeder::class]);

      $this->call([
         // WorldSeeder::class, // Comentado hasta recibir el seeder personalizado

         // SupplierSeeder::class,
         // CategorySeeder::class,
         // ProductSeeder::class,
         PermissionSeeder::class,
      ]);

      // Create default admin role
      $adminRole = \Spatie\Permission\Models\Role::create([
         'name' => 'administrador',
         'guard_name' => 'web'
      ]);

      $adminRole->givePermissionTo(Permission::all());

      // Assign admin role to superAdmin user
      User::where('email', 'superAdmin@gmail.com')->first()->assignRole($adminRole);
   }
}
