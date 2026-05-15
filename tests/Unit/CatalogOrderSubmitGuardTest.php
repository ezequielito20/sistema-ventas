<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\CompanyDeliveryMethod;
use App\Models\Order;
use App\Services\Catalog\CatalogOrderSubmitGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CatalogOrderSubmitGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocks_when_ip_hourly_limit_exceeded(): void
    {
        config(['catalog.order_max_per_ip_per_hour' => 3]);

        $company = Company::factory()->create();
        $request = Request::create('/', 'POST', server: ['REMOTE_ADDR' => '203.0.113.10']);
        $guard = app(CatalogOrderSubmitGuard::class);
        $key = 'catalog-order-submit:'.$company->id.':203.0.113.10';

        for ($i = 0; $i < 3; $i++) {
            $guard->recordSuccessfulSubmit($request, $company);
        }

        try {
            $guard->assertIpWithinLimit($request, $company);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('cart', $e->errors());
        } finally {
            RateLimiter::clear($key);
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
