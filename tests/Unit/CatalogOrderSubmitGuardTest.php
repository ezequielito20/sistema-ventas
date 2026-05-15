<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\CompanyDeliveryMethod;
use App\Models\Order;
use App\Services\Catalog\CatalogOrderSubmitGuard;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CatalogOrderSubmitGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocks_when_ip_hourly_limit_exceeded_with_exact_retry_time(): void
    {
        config([
            'catalog.order_max_per_ip_per_hour' => 3,
            'catalog.order_ip_rate_limit_decay_seconds' => 3600,
        ]);

        $company = Company::factory()->create();
        $request = Request::create('/', 'POST', server: ['REMOTE_ADDR' => '203.0.113.10']);
        $guard = app(CatalogOrderSubmitGuard::class);
        $key = 'catalog-order-submit:'.$company->id.':203.0.113.10';

        Carbon::setTestNow('2026-05-15 10:00:00');
        $guard->recordSuccessfulSubmit($request, $company);

        Carbon::setTestNow('2026-05-15 10:20:00');
        $guard->recordSuccessfulSubmit($request, $company);

        Carbon::setTestNow('2026-05-15 10:40:00');
        $guard->recordSuccessfulSubmit($request, $company);

        try {
            $guard->assertIpWithinLimit($request, $company);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('cart', $e->errors());
            $this->assertStringContainsString('11:40', $e->errors()['cart'][0]);
            $this->assertStringContainsString('15/05/2026', $e->errors()['cart'][0]);
        } finally {
            Cache::forget($key);
            Carbon::setTestNow();
        }
    }

    public function test_blocks_when_pending_order_exists_for_phone(): void
    {
        config(['catalog.order_max_pending_per_phone' => 1]);

        $company = Company::factory()->create();
        $this->createMinimalOrder($company, ['status' => 'pending']);

        $guard = app(CatalogOrderSubmitGuard::class);

        try {
            DB::transaction(fn () => $guard->assertPendingPhoneLimit($company, '04148965789'));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('customer_phone', $e->errors());
            $this->assertStringContainsString('Cancelalo', $e->errors()['customer_phone'][0]);
        }
    }

    public function test_allows_new_order_when_previous_phone_order_is_processed(): void
    {
        $company = Company::factory()->create();
        $this->createMinimalOrder($company, ['status' => 'processed']);

        $guard = app(CatalogOrderSubmitGuard::class);

        DB::transaction(fn () => $guard->assertPendingPhoneLimit($company, '04148965789'));

        $this->assertTrue(true);
    }

    public function test_allows_new_order_when_previous_phone_order_was_cancelled(): void
    {
        $company = Company::factory()->create();
        $this->createMinimalOrder($company, ['status' => 'cancelled']);

        $guard = app(CatalogOrderSubmitGuard::class);

        DB::transaction(fn () => $guard->assertPendingPhoneLimit($company, '04148965789'));

        $this->assertTrue(true);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createMinimalOrder(Company $company, array $overrides = []): Order
    {
        $delivery = CompanyDeliveryMethod::query()->create([
            'company_id' => $company->id,
            'type' => 'pickup',
            'name' => 'Retiro test',
            'is_active' => true,
        ]);

        return Order::query()->create(array_merge([
            'company_id' => $company->id,
            'customer_name' => 'Cliente Test',
            'customer_phone' => '04148965789',
            'status' => 'pending',
            'company_delivery_method_id' => $delivery->id,
            'exchange_rate_used' => 40,
            'subtotal_products_usd' => 10,
            'total_usd' => 10,
            'total_bs' => 400,
            'public_summary_token' => Order::generateSummaryToken(),
        ], $overrides));
    }
}
