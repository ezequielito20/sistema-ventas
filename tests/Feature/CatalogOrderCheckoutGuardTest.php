<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyDeliveryMethod;
use App\Models\Order;
use App\Services\Catalog\CatalogOrderSubmitGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Pruebas del guard de checkout (SQLite en memoria; no toca la BD de desarrollo).
 */
class CatalogOrderCheckoutGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_phone_lock_query_runs_without_aggregate_for_update_error(): void
    {
        $company = Company::factory()->create();
        $phone = '04142643109';

        DB::transaction(function () use ($company, $phone): void {
            Order::query()->create([
                'company_id' => $company->id,
                'customer_name' => 'Test',
                'customer_phone' => $phone,
                'status' => 'pending',
                'company_delivery_method_id' => CompanyDeliveryMethod::query()->create([
                    'company_id' => $company->id,
                    'type' => 'pickup',
                    'name' => 'Retiro',
                    'is_active' => true,
                ])->id,
                'exchange_rate_used' => 40,
                'subtotal_products_usd' => 1,
                'total_usd' => 1,
                'total_bs' => 40,
                'public_summary_token' => Order::generateSummaryToken(),
            ]);

            $locked = Order::query()
                ->where('company_id', $company->id)
                ->where('customer_phone', $phone)
                ->where('status', 'pending')
                ->orderBy('id')
                ->lockForUpdate()
                ->limit(1)
                ->get(['id']);

            $this->assertCount(1, $locked);

            try {
                app(CatalogOrderSubmitGuard::class)->assertPendingPhoneLimit($company, $phone);
                $this->fail('Se esperaba ValidationException por teléfono con pending.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('customer_phone', $e->errors());
            }
        });
    }

    public function test_pending_phone_guard_allows_when_no_pending_order(): void
    {
        $company = Company::factory()->create();
        $phone = '04140001122';

        DB::transaction(function () use ($company, $phone): void {
            app(CatalogOrderSubmitGuard::class)->assertPendingPhoneLimit($company, $phone);
        });

        $this->assertTrue(true);
    }
}
