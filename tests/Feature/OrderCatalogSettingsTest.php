<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderCatalogSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function subscribeCompanyToCatalogOrders(Company $company): Plan
    {
        $plan = Plan::create([
            'name' => 'Plan test catálogo',
            'slug' => 'test-catalog-orders-'.uniqid(),
            'description' => null,
            'base_price' => 0,
            'price_per_user' => 0,
            'price_per_transaction' => 0,
            'limits' => [],
            'features' => ['orders', 'catalog'],
            'max_users' => null,
            'max_transactions' => null,
            'max_products' => null,
            'max_customers' => null,
            'is_active' => true,
        ]);

        Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now()->toDateString(),
            'billing_day' => 1,
            'next_billing_date' => now()->addMonth()->toDateString(),
            'amount' => 0,
            'auto_renew' => true,
        ]);

        return $plan;
    }

    public function test_order_catalog_settings_forbidden_without_permission(): void
    {
        Permission::firstOrCreate(['name' => 'orders.settings', 'guard_name' => 'web']);
        $company = Company::factory()->create();
        $this->subscribeCompanyToCatalogOrders($company);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'security_questions_setup' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.order-catalog-settings.index'))
            ->assertForbidden();
    }

    public function test_order_catalog_settings_ok_with_orders_settings_permission(): void
    {
        Permission::firstOrCreate(['name' => 'orders.settings', 'guard_name' => 'web']);
        $company = Company::factory()->create();
        $this->subscribeCompanyToCatalogOrders($company);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'security_questions_setup' => true,
        ]);
        $user->givePermissionTo('orders.settings');

        $this->actingAs($user)
            ->get(route('admin.order-catalog-settings.index'))
            ->assertOk()
            ->assertSee('Checkout del catálogo');
    }

    public function test_order_catalog_settings_ok_with_roles_assign_fallback(): void
    {
        Permission::firstOrCreate(['name' => 'roles.assign.permissions', 'guard_name' => 'web']);
        $company = Company::factory()->create();
        $this->subscribeCompanyToCatalogOrders($company);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'security_questions_setup' => true,
        ]);
        $user->givePermissionTo('roles.assign.permissions');

        $this->actingAs($user)
            ->get(route('admin.order-catalog-settings.index'))
            ->assertOk()
            ->assertSee('Checkout del catálogo');
    }
}
