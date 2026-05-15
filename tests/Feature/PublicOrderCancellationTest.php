<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyDeliveryMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicOrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_cancel_pending_order_with_matching_phone(): void
    {
        config(['catalog.order_public_cancel_window_minutes' => 30]);
        Carbon::setTestNow('2026-05-15 12:00:00');

        $company = Company::factory()->create(['slug' => 'tienda-test']);
        $delivery = CompanyDeliveryMethod::query()->create([
            'company_id' => $company->id,
            'type' => 'pickup',
            'name' => 'Retiro',
            'is_active' => true,
        ]);
        $product = Product::factory()->create([
            'company_id' => $company->id,
            'stock' => 0,
            'include_in_catalog' => true,
        ]);
        $order = Order::query()->create([
            'company_id' => $company->id,
            'customer_name' => 'Ana',
            'customer_phone' => '04148965789',
            'status' => 'pending',
            'company_delivery_method_id' => $delivery->id,
            'exchange_rate_used' => 40,
            'subtotal_products_usd' => 5,
            'total_usd' => 5,
            'total_bs' => 200,
            'public_summary_token' => 'test-token-cancel-ok',
            'public_summary_expires_at' => now()->addDays(7),
            'created_at' => now(),
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price_usd' => 2.5,
            'line_subtotal_usd' => 5,
            'line_discount_usd' => 0,
            'line_total_usd' => 5,
        ]);

        $response = $this->post(route('order.summary.cancel', ['token' => 'test-token-cancel-ok']), [
            'customer_phone' => '04148965789',
            'confirm_cancel' => '1',
        ]);

        $response->assertRedirect(route('order.summary.show', ['token' => 'test-token-cancel-ok']));
        $response->assertSessionHas('order_cancelled');

        $order->refresh();
        $product->refresh();

        $this->assertSame('cancelled', $order->status);
        $this->assertSame(2, $product->stock);
        $this->assertTrue($product->isVisibleInPublicCatalog());

        Carbon::setTestNow();
    }

    public function test_cancel_rejects_wrong_phone(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        $company = Company::factory()->create();
        $delivery = CompanyDeliveryMethod::query()->create([
            'company_id' => $company->id,
            'type' => 'pickup',
            'name' => 'Retiro',
            'is_active' => true,
        ]);
        Order::query()->create([
            'company_id' => $company->id,
            'customer_name' => 'Ana',
            'customer_phone' => '04148965789',
            'status' => 'pending',
            'company_delivery_method_id' => $delivery->id,
            'exchange_rate_used' => 40,
            'subtotal_products_usd' => 5,
            'total_usd' => 5,
            'total_bs' => 200,
            'public_summary_token' => 'test-token-wrong-phone',
            'public_summary_expires_at' => now()->addDays(7),
            'created_at' => now(),
        ]);

        $response = $this->from(route('order.summary.show', ['token' => 'test-token-wrong-phone']))
            ->post(route('order.summary.cancel', ['token' => 'test-token-wrong-phone']), [
                'customer_phone' => '04140000000',
                'confirm_cancel' => '1',
            ]);

        $response->assertRedirect(route('order.summary.show', ['token' => 'test-token-wrong-phone']));
        $response->assertSessionHasErrors('customer_phone');

        Carbon::setTestNow();
    }

    public function test_cancel_rejected_after_window_expires(): void
    {
        config(['catalog.order_public_cancel_window_minutes' => 30]);

        $company = Company::factory()->create();
        $delivery = CompanyDeliveryMethod::query()->create([
            'company_id' => $company->id,
            'type' => 'pickup',
            'name' => 'Retiro',
            'is_active' => true,
        ]);
        Carbon::setTestNow('2026-05-15 10:00:00');
        $order = Order::query()->create([
            'company_id' => $company->id,
            'customer_name' => 'Ana',
            'customer_phone' => '04148965789',
            'status' => 'pending',
            'company_delivery_method_id' => $delivery->id,
            'exchange_rate_used' => 40,
            'subtotal_products_usd' => 5,
            'total_usd' => 5,
            'total_bs' => 200,
            'public_summary_token' => 'test-token-expired-window',
            'public_summary_expires_at' => now()->addDays(7),
        ]);

        Carbon::setTestNow('2026-05-15 11:00:00');

        $this->assertFalse($order->fresh()->canBeCancelledByCustomer());

        Carbon::setTestNow();
    }
}
