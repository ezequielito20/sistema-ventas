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

    /** @var ''|'pickup'|'delivery' */
    public string $delivery_kind = '';

    public string $delivery_zone_selection = '';

    /** Texto cuando el cliente elige «Otro» lugar de delivery */
    public string $delivery_custom_zone = '';

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

        $pickups = CompanyDeliveryMethod::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('type', CompanyDeliveryMethod::TYPE_PICKUP)
            ->count();
        $deliveries = CompanyDeliveryMethod::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('type', CompanyDeliveryMethod::TYPE_DELIVERY)
            ->count();
        if ($pickups > 0 && $deliveries === 0) {
            $this->delivery_kind = CompanyDeliveryMethod::TYPE_PICKUP;
        } elseif ($deliveries > 0 && $pickups === 0) {
            $this->delivery_kind = CompanyDeliveryMethod::TYPE_DELIVERY;
        }
    }

    public function updatedDeliveryKind(): void
    {
        $this->company_delivery_method_id = null;
        $this->delivery_zone_id = null;
        $this->delivery_slot_id = null;
        $this->delivery_zone_selection = '';
        $this->delivery_custom_zone = '';
    }

    public function preferHomeDeliveryInstead(): void
    {
        if (! $this->hasHomeDeliveryMethods) {
            return;
        }
        $this->delivery_kind = CompanyDeliveryMethod::TYPE_DELIVERY;
        $this->company_delivery_method_id = null;
        $this->delivery_zone_id = null;
        $this->delivery_slot_id = null;
        $this->delivery_zone_selection = '';
        $this->delivery_custom_zone = '';
    }

    public function updatedCompanyDeliveryMethodId(): void
    {
        $this->delivery_zone_id = null;
        $this->delivery_slot_id = null;
        $this->delivery_zone_selection = '';
        $this->delivery_custom_zone = '';

        if (! $this->company_delivery_method_id) {
            return;
        }

        /** @var CompanyDeliveryMethod|null $method */
        $method = CompanyDeliveryMethod::query()->find($this->company_delivery_method_id);
        if ($method && $method->isDelivery()) {
            $hasZones = DeliveryZone::query()
                ->where('company_delivery_method_id', $method->id)
                ->where('is_active', true)
                ->exists();
            if (! $hasZones) {
                $this->delivery_zone_selection = 'custom';
            }
        }
    }

    public function updatedDeliveryZoneSelection(?string $value): void
    {
        $this->delivery_slot_id = null;

        if ($value === '' || $value === null || $value === 'custom') {
            $this->delivery_zone_id = null;
            if ($value !== 'custom') {
                $this->delivery_custom_zone = '';
            }

            return;
        }

        if (preg_match('/^z:(\d+)$/', (string) $value, $m)) {
            $this->delivery_zone_id = (int) $m[1];

            return;
        }

        $this->delivery_zone_id = null;
    }

    public function getCompanyProperty(): Company
    {
        return Company::query()->findOrFail($this->companyId);
    }

    public function getHasPickupDeliveryMethodsProperty(): bool
    {
        return CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->where('is_active', true)
            ->where('type', CompanyDeliveryMethod::TYPE_PICKUP)
            ->exists();
    }

    public function getHasHomeDeliveryMethodsProperty(): bool
    {
        return CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->where('is_active', true)
            ->where('type', CompanyDeliveryMethod::TYPE_DELIVERY)
            ->exists();
    }

    /**
     * Métodos de entrega según pickup / delivery ya elegidos.
     */
    public function getDeliveryMethodsFilteredProperty(): Collection
    {
        if ($this->delivery_kind !== CompanyDeliveryMethod::TYPE_PICKUP && $this->delivery_kind !== CompanyDeliveryMethod::TYPE_DELIVERY) {
            return collect();
        }

        return CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->where('is_active', true)
            ->where('type', $this->delivery_kind)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
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

    /**
     * Vista previa del total con descuento por método de pago (misma fórmula que CatalogOrderCheckoutService).
     *
     * @return array{subtotal: float, discount_percent: float, discount_amount: float, final: float, has_payment_discount: bool}
     */
    public function getPaymentDiscountPreviewProperty(): array
    {
        $subtotal = (float) $this->cartTotalUsd;
        $percent = 0.0;
        if ($this->company_payment_method_id) {
            $pm = CompanyPaymentMethod::query()
                ->where('company_id', $this->companyId)
                ->where('is_active', true)
                ->find($this->company_payment_method_id);
            if ($pm) {
                $percent = (float) $pm->discount_percent;
            }
        }
        $discountAmount = $percent > 0 ? round($subtotal * ($percent / 100), 2) : 0.0;
        $final = round($subtotal - $discountAmount, 2);

        return [
            'subtotal' => $subtotal,
            'discount_percent' => $percent,
            'discount_amount' => $discountAmount,
            'final' => $final,
            'has_payment_discount' => $percent > 0,
        ];
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
            ->with(['zone:id,name'])
            ->orderBy('weekday_iso')
            ->orderBy('delivery_time');
        if (Schema::hasColumn('delivery_slots', 'delivery_time_end')) {
            $q->orderByRaw('COALESCE(delivery_time_end, delivery_time)');
        }

        if ($method->isPickup()) {
            $q->whereNull('delivery_zone_id');
        } else {
            $zones = $this->zones;

            if ($zones->isEmpty()) {
                return collect();
            }

            // Con «Otro» se listan las franjas de todas las zonas del método (referencia logística).
            if ($this->delivery_zone_selection === 'custom') {
                $q->whereIn('delivery_zone_id', $zones->pluck('id')->all())->orderBy('delivery_zone_id');
            } elseif ($this->delivery_zone_id) {
                $q->where('delivery_zone_id', $this->delivery_zone_id);
            } else {
                return collect();
            }
        }

        return $q->get()->filter(fn (DeliverySlot $s) => $s->hasCapacity())->values();
    }

    /** Indica si el cliente describe la zona manualmente («Otro» o método sin zonas listadas). */
    public function getIsCustomDeliveryZoneProperty(): bool
    {
        if ($this->delivery_kind !== CompanyDeliveryMethod::TYPE_DELIVERY || ! $this->company_delivery_method_id) {
            return false;
        }
        $method = CompanyDeliveryMethod::query()->find($this->company_delivery_method_id);
        if (! $method || ! $method->isDelivery()) {
            return false;
        }

        if ($this->delivery_zone_selection === 'custom') {
            return true;
        }

        return $this->zones->isEmpty();
    }

    public function submit(Request $request, CatalogCartService $cartService, CatalogOrderCheckoutService $checkoutService): void
    {
        $company = $this->company;
        CatalogAccess::assert($request, $company);

        $this->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'digits:11'],
            'notes' => 'nullable|string|max:2000',
            'delivery_kind' => 'required|in:pickup,delivery',
            'company_payment_method_id' => 'required|integer|exists:company_payment_methods,id',
            'company_delivery_method_id' => 'required|integer|exists:company_delivery_methods,id',
            'delivery_zone_id' => 'nullable|integer|exists:delivery_zones,id',
            'delivery_slot_id' => 'nullable|integer|exists:delivery_slots,id',
            'delivery_custom_zone' => 'nullable|string|max:2000',
        ], [
            'customer_phone.required' => 'El teléfono es obligatorio',
            'customer_phone.digits' => 'El teléfono debe tener exactamente 11 dígitos numéricos (ej: 04148965789).',
            'delivery_kind.required' => 'Elegí si preferís retiro en punto o delivery.',
        ]);

        $dm = CompanyDeliveryMethod::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->whereKey($this->company_delivery_method_id)
            ->first();

        if (! $dm) {
            $this->addError('company_delivery_method_id', 'Método de entrega no válido.');

            return;
        }

        $expectedKind = $dm->isPickup() ? CompanyDeliveryMethod::TYPE_PICKUP : CompanyDeliveryMethod::TYPE_DELIVERY;
        if ($this->delivery_kind !== $expectedKind) {
            $this->addError('delivery_kind', 'El método elegido no coincide con retiro / delivery seleccionados.');

            return;
        }

        $customPayload = null;

        if ($dm->isPickup()) {
            $this->delivery_zone_selection = '';
            $this->delivery_custom_zone = '';
            $this->delivery_zone_id = null;
            $customPayload = null;
        } elseif ($dm->isDelivery()) {
            $zonesCount = DeliveryZone::query()
                ->where('company_delivery_method_id', $dm->id)
                ->where('is_active', true)
                ->count();

            $isExplicitCustom = ($this->delivery_zone_selection === 'custom');

            if ($zonesCount > 0 && $isExplicitCustom) {
                $this->validate([
                    'delivery_custom_zone' => 'required|string|min:5|max:2000',
                ], [
                    'delivery_custom_zone.required' => 'Describí la zona o dirección donde querés el delivery.',
                ]);
                $this->delivery_zone_id = null;
                $customPayload = trim($this->delivery_custom_zone);
            } elseif ($zonesCount > 0) {
                $this->validate([
                    'delivery_zone_id' => 'required|integer|exists:delivery_zones,id',
                ]);
                $customPayload = null;
            } else {
                // Delivery sin zonas configuradas → solo texto
                $this->validate([
                    'delivery_custom_zone' => 'required|string|min:5|max:2000',
                ]);
                $this->delivery_zone_id = null;
                $customPayload = trim($this->delivery_custom_zone);
            }

            // Coherencia: si seleccionó lista, zona debe pertenecer al método
            if ($customPayload === null && $this->delivery_zone_id) {
                $ok = DeliveryZone::query()
                    ->whereKey($this->delivery_zone_id)
                    ->where('company_delivery_method_id', $dm->id)
                    ->where('is_active', true)
                    ->exists();
                if (! $ok) {
                    $this->addError('delivery_zone_id', 'La zona no es válida para este método.');

                    return;
                }
            }

            unset($zonesCount, $isExplicitCustom);
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
            'delivery_zone_id' => $dm->isDelivery() ? $this->delivery_zone_id : null,
            'delivery_custom_location' => $dm->isDelivery() ? ($customPayload ?? null) : null,
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
