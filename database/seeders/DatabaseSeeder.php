<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\CityTableSeeder;
use Database\Seeders\StateTableSeeder;
use Database\Seeders\ParishTableSeeder;
use Database\Seeders\CountryTableSeeder;
use Database\Seeders\CompanySeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Database\Seeders\MunicipalityTableSeeder;

class DatabaseSeeder extends Seeder
{
   /**
    * Seed the application's database.
    */
   public function run(): void
   {
      // Primero ejecutar los seeders de ubicación y empresa
      $this->call([CountryTableSeeder::class]);
      $this->call([StateTableSeeder::class]);
      $this->call([CityTableSeeder::class]);
      $this->call([CurrencySeeder::class]);
      $this->call([CompanySeeder::class]);

      // Crear permisos y plan
      $this->call([PermissionSeeder::class]);
      $this->call([PlanSeeder::class]);

      // Crear el usuario super admin (dueño del sistema)
      $superAdminUser = User::factory()->create([
         'name' => 'superAdmin',
         'email' => 'superAdmin@gmail.com',
         'password' => Hash::make('12345'),
         'company_id' => 1,
         'is_super_admin' => true,
      ]);

      // Crear rol global super-admin (sin company_id, para el dueño del sistema)
      $superAdminRole = Role::create([
         'name' => 'super-admin',
         'guard_name' => 'web',
         'company_id' => null,
      ]);

      // Asignar TODOS los permisos al rol super-admin
      $superAdminRole->givePermissionTo(Permission::all());

      // Asignar el rol super-admin al usuario dueño
      $superAdminUser->assignRole($superAdminRole);

      // Crear rol administrador para la empresa (roles por empresa)
      $adminRole = Role::create([
         'name' => 'administrador',
         'guard_name' => 'web',
         'company_id' => 1,
      ]);

      // Asignar permisos al rol administrador (todos menos los de super-admin)
      $companyPermissions = Permission::where('name', 'not like', 'system.%')
         ->where('name', 'not like', 'plans.%')
         ->where('name', 'not like', 'subscriptions.%')
         ->where('name', '!=', 'super-admin.access')
         ->get();
      $adminRole->givePermissionTo($companyPermissions);

      // Asignar el rol administrador de empresa al super admin (para que también pueda usar el sistema normal)
      $superAdminUser->assignRole($adminRole);

      // Crear suscripción inicial para empresa 1 con plan Básico
      $basicPlan = Plan::where('slug', 'basico')->first();
      Subscription::create([
         'company_id' => 1,
         'plan_id' => $basicPlan->id,
         'status' => 'active',
         'started_at' => now(),
         'billing_day' => 1,
         'next_billing_date' => now()->addMonth()->startOfMonth(),
         'amount' => $basicPlan->base_price,
         'auto_renew' => true,
      ]);

      $this->command->info('Sistema inicializado correctamente con super-admin y plan Básico.');
   }
}
