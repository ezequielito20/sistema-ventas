<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\CompanyDeliveryMethod;
use App\Models\CompanyPaymentMethod;
use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use App\Services\Catalog\CatalogCartService;
use App\Services\Catalog\CatalogOrderCheckoutService;
use App\Support\CatalogAccess;
use App\Support\CatalogUrlGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class CatalogCheckout extends Component
{
    public int $companyId;

    public string $customer_name = '';

    public string $customer_phone = '';

    public ?string $notes = null;

    public ?int $company_payment_method_id = null;

    public ?int $company_delivery_method_id = null;

    public ?int $delivery_zone_id = null;

    public ?int $delivery_slot_id = null;

    /** @var array<int, array<string, mixed>> */
    public array $cartItems = [];

    public string $cartTotalUsd = '0.00';

    public bool $submitted = false;

    public ?int $createdOrderId = null;

    public function mount(Request $request, int $companyId): void
    {
        $this->companyId = $companyId;
        $company = Company::query()->findOrFail($companyId);
        CatalogAccess::assert($request, $company);
        $this->loadCart($request, $company);
    }

    public function updatedCompanyDeliveryMethodId(): void
    {
        $this->delivery_zone_id = null;
        $this->delivery_slot_id = null;
    }

    public function updatedDeliveryZoneId(): void
    {
        $this->delivery_slot_id = null;
    }

    public function getCompanyProperty(): Company
    {
        return Company::query()->findOrFail($this->companyId);
    }

    public function getPaymentMethodsProperty(): Collection
    {
        return CompanyPaymentMethod::query()
            ->where('company_id', $this->companyId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function getDeliveryMethodsProperty(): Collection
    {
        return CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function getZonesProperty(): Collection
    {
        if (! $this->company_delivery_method_id) {
            return collect();
        }
        $method = CompanyDeliveryMethod::query()->find($this->company_delivery_method_id);
        if (! $method || ! $method->isDelivery()) {
            return collect();
        }

        return DeliveryZone::query()
            ->where('company_delivery_method_id', $method->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function updatedCustomerPhone(): void
    {
        $digits = preg_replace('/\D+/', '', (string) $this->customer_phone);

        $this->customer_phone = substr($digits, 0, 11);
    }

    public function getDeliverySlotsProperty(): Collection
    {
        if (! $this->company_delivery_method_id) {
            return collect();
        }
        $method = CompanyDeliveryMethod::query()->find($this->company_delivery_method_id);
        if (! $method) {
            return collect();
        }

        $q = DeliverySlot::query()
            ->where('company_id', $this->companyId)
            ->where('company_delivery_method_id', $method->id)
            ->where('is_active', true)
            ->orderBy('weekday_iso')
            ->orderBy('delivery_time');
        if (Schema::hasColumn('delivery_slots', 'delivery_time_end')) {
            $q->orderByRaw('COALESCE(delivery_time_end, delivery_time)');
        }

        if ($method->isDelivery()) {
            if (! $this->delivery_zone_id) {
                return collect();
            }
            $q->where('delivery_zone_id', $this->delivery_zone_id);
        } else {
            $q->whereNull('delivery_zone_id');
        }

        return $q->get()->filter(fn (DeliverySlot $s) => $s->hasCapacity())->values();
    }

    public function submit(Request $request, CatalogCartService $cartService, CatalogOrderCheckoutService $checkoutService): void
    {
        $company = $this->company;
        CatalogAccess::assert($request, $company);

        $this->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'digits:11'],
            'notes' => 'nullable|string|max:2000',
            'company_payment_method_id' => 'required|integer|exists:company_payment_methods,id',
            'company_delivery_method_id' => 'required|integer|exists:company_delivery_methods,id',
            'delivery_zone_id' => 'nullable|integer|exists:delivery_zones,id',
            'delivery_slot_id' => 'nullable|integer|exists:delivery_slots,id',
        ], [
            'customer_phone.required' => 'El teléfono es obligatorio',
            'customer_phone.digits' => 'El teléfono debe tener exactamente 11 dígitos numéricos (ej: 04148965789).',
        ]);

        $dm = CompanyDeliveryMethod::query()
            ->where('company_id', $company->id)
            ->whereKey($this->company_delivery_method_id)
            ->first();
        if ($dm && $dm->isDelivery()) {
            $this->validate([
                'delivery_zone_id' => 'required|integer|exists:delivery_zones,id',
            ]);
        }

        if ($this->deliverySlots->isNotEmpty()) {
            $this->validate([
                'delivery_slot_id' => 'required|integer|exists:delivery_slots,id',
            ]);
        }

        $resolved = $cartService->resolveOrCreateCart($request, $company);
        $cart = $resolved['cart'];

        $payload = [
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'notes' => $this->notes,
            'company_payment_method_id' => $this->company_payment_method_id,
            'company_delivery_method_id' => $this->company_delivery_method_id,
            'delivery_zone_id' => $this->delivery_zone_id,
            'delivery_slot_id' => $this->delivery_slot_id,
        ];

        $order = $checkoutService->checkout($company, $cart, $payload);

        $this->submitted = true;
        $this->createdOrderId = $order->id;

        Cookie::queue($resolved['cookie']);
    }

    protected function loadCart(Request $request, Company $company): void
    {
        $cartService = app(CatalogCartService::class);
        $cart = $cartService->resolveCartFromRequest($request, $company);
        if (! $cart) {
            $this->cartItems = [];
            $this->cartTotalUsd = '0.00';

            return;
        }

        $lines = $cartService->itemsWithProducts($cart);
        $total = 0.0;
        $this->cartItems = $lines->map(function ($row) use ($company, &$total) {
            $p = $row->product;
            if (! $p || (int) $p->company_id !== (int) $company->id || ! $p->isVisibleInPublicCatalog()) {
                return null;
            }
            $unit = (float) $p->final_price;
            $line = round($unit * $row->quantity, 2);
            $total += $line;

            return [
                'product_id' => $p->id,
                'name' => $p->name,
                'quantity' => $row->quantity,
                'unit_usd' => $unit,
                'line_usd' => $line,
            ];
        })->filter()->values()->all();

        $this->cartTotalUsd = number_format($total, 2, '.', '');
    }

    public function render(): View
    {
        $company = $this->company;

        return view('livewire.catalog-checkout', [
            'company' => $company,
            'catalogHomeUrl' => CatalogUrlGenerator::catalogIndex($company),
        ]);
    }
}
