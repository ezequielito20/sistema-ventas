<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

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
         'country' => '48',
         'name' => 'Empresa de prueba',
         'business_type' => 'Comercial',
         'nit' => '1234567890',
         'phone' => '1234567890',
         'email' => 'superAdmin@gmail.com',
         'tax_amount' => 19,
         'tax_name' => 'IVA',
         'currency' => 'COP',
         'address' => 'Calle Principal #123',
         'city' => 'Bogotá',
         'state' => 'Cundinamarca',
         'postal_code' => '110111',
         'logo' => 'logo.png',
      ]);

      User::factory()->create([
         'name' => 'superAdmin',
         'email' => 'superAdmin@gmail.com',
         'password' => Hash::make('12345'),
         'company_id' => 1,
      ]);

      $this->call([
         WorldSeeder::class,
         SupplierSeeder::class,
         CategorySeeder::class,
         ProductSeeder::class,
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
