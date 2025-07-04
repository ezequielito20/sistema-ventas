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
use Database\Seeders\CompanySeeder;
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

      // Primero ejecutar los seeders de ubicación y empresa
      $this->call([CountryTableSeeder::class]);
      $this->call([StateTableSeeder::class]);
      // $this->call([MunicipalityTableSeeder::class]);
      // $this->call([ParishTableSeeder::class]);
      $this->call([CityTableSeeder::class]);
      $this->call([CurrencySeeder::class]);
      $this->call([CompanySeeder::class]);

      // Luego crear el usuario que depende de la empresa
      User::factory()->create([
         'name' => 'superAdmin',
         'email' => 'superAdmin@gmail.com',
         'password' => Hash::make('12345'),
         'company_id' => 1,
      ]);

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
