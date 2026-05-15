<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderCatalogSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_catalog_settings_forbidden_without_permission(): void
    {
        Permission::create(['name' => 'orders.settings', 'guard_name' => 'web']);
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'security_questions_setup' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.order-catalog-settings.index'))
            ->assertForbidden();
    }

    public function test_order_catalog_settings_ok_with_permission(): void
    {
        Permission::create(['name' => 'orders.settings', 'guard_name' => 'web']);
        $company = Company::factory()->create();
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
}
