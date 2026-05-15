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

    /** @var ''|'schedule'|'ship' — Pautar entrega (punto) vs envío por delivery */
    public string $logistics_mode = '';

    /** Envío: '' | 'z:{zoneId}' | 'other' */
    public string $ship_zone_choice = '';

    /** Envío con zona listada: fecha/hora libres (calendario + reloj), sin franjas fijas */
    public string $ship_zone_calendar_date = '';

    public string $ship_zone_calendar_time = '';

    public string $delivery_zone_selection = '';

    public string $delivery_custom_zone = '';

    public string $ship_other_place_name = '';

    public string $ship_other_date = '';

    public string $ship_other_time = '';

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
            $this->logistics_mode = 'schedule';
        } elseif ($deliveries > 0 && $pickups === 0) {
            $this->logistics_mode = 'ship';
        }
    }

    public function updatedLogisticsMode(): void
    {
        $this->resetLogisticsFields();
    }

    protected function resetLogisticsFields(): void
    {
        $this->company_delivery_method_id = null;
        $this->ship_zone_choice = '';
        $this->ship_zone_calendar_date = '';
        $this->ship_zone_calendar_time = '';
        $this->delivery_zone_id = null;
        $this->delivery_slot_id = null;
        $this->delivery_zone_selection = '';
        $this->delivery_custom_zone = '';
        $this->ship_other_place_name = '';
        $this->ship_other_date = '';
        $this->ship_other_time = '';
    }

    public function updatedCompanyDeliveryMethodId(): void
    {
        if ($this->logistics_mode !== 'schedule') {
            return;
        }

        $this->delivery_slot_id = null;
    }

    public function updatedShipZoneChoice(?string $value): void
    {
        if ($this->logistics_mode !== 'ship') {
            return;
        }

        $this->delivery_slot_id = null;
        $this->ship_zone_calendar_date = '';
        $this->ship_zone_calendar_time = '';
        $this->delivery_zone_id = null;
        $this->company_delivery_method_id = null;
        $this->delivery_zone_selection = '';
        $this->delivery_custom_zone = '';
        $this->ship_other_place_name = '';
        $this->ship_other_date = '';
        $this->ship_other_time = '';

        if ($value === 'other') {
            $anchor = $this->firstActiveDeliveryMethod();
            $this->company_delivery_method_id = $anchor?->id;

            return;
        }

        if (preg_match('/^z:(\d+)$/', (string) $value, $m)) {
            $zone = DeliveryZone::query()
                ->whereKey((int) $m[1])
                ->where('is_active', true)
                ->whereHas('deliveryMethod', function ($q) {
                    $q->where('company_id', $this->companyId)
                        ->where('is_active', true)
                        ->where('type', CompanyDeliveryMethod::TYPE_DELIVERY);
                })
                ->with('deliveryMethod')
                ->first();
            if ($zone) {
                $this->delivery_zone_id = $zone->id;
                $this->company_delivery_method_id = (int) $zone->company_delivery_method_id;
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

    protected function firstActiveDeliveryMethod(): ?CompanyDeliveryMethod
    {
        return CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->where('is_active', true)
            ->where('type', CompanyDeliveryMethod::TYPE_DELIVERY)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->first();
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

    public function getLogisticsRequiresChoiceProperty(): bool
    {
        return $this->hasPickupDeliveryMethods && $this->hasHomeDeliveryMethods;
    }

    /**
     * Zonas de delivery activas (todas las rutas) para el selector con costo.
     */
    public function getShipZonesCatalogProperty(): Collection
    {
        return DeliveryZone::query()
            ->where('is_active', true)
            ->whereHas('deliveryMethod', function ($q) {
                $q->where('company_id', $this->companyId)
                    ->where('is_active', true)
                    ->where('type', CompanyDeliveryMethod::TYPE_DELIVERY);
            })
            ->with(['deliveryMethod:id,name'])
            ->orderBy('name')
            ->get();
    }

    public function getPickupMethodsCatalogProperty(): Collection
    {
        if ($this->logistics_mode !== 'schedule') {
            return collect();
        }

        return CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->where('is_active', true)
            ->where('type', CompanyDeliveryMethod::TYPE_PICKUP)
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

            if ($this->logistics_mode === 'ship' && $this->ship_zone_choice === 'other') {
                $q->whereIn('delivery_zone_id', $zones->pluck('id')->all())->orderBy('delivery_zone_id');
            } elseif ($this->delivery_zone_id) {
                $q->where('delivery_zone_id', $this->delivery_zone_id);
            } else {
                return collect();
            }
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
            'logistics_mode' => 'required|in:schedule,ship',
            'company_payment_method_id' => 'required|integer|exists:company_payment_methods,id',
            'company_delivery_method_id' => 'required|integer|exists:company_delivery_methods,id',
            'delivery_zone_id' => 'nullable|integer|exists:delivery_zones,id',
            'delivery_slot_id' => 'nullable|integer|exists:delivery_slots,id',
            'delivery_custom_zone' => 'nullable|string|max:2000',
        ], [
            'customer_phone.required' => 'El teléfono es obligatorio',
            'customer_phone.digits' => 'El teléfono debe tener exactamente 11 dígitos numéricos (ej: 04148965789).',
            'logistics_mode.required' => 'Elegí cómo querés recibir tu pedido.',
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

        if ($this->logistics_mode === 'schedule') {
            if (! $dm->isPickup()) {
                $this->addError('logistics_mode', 'En «Pautar entrega» debés elegir un punto de la lista.');

                return;
            }
        } elseif ($this->logistics_mode === 'ship') {
            if (! $dm->isDelivery()) {
                $this->addError('logistics_mode', 'En «Enviar por delivery» debés elegir una zona o «Otro lugar».');

                return;
            }
            if ($this->ship_zone_choice === '') {
                $this->addError('ship_zone_choice', 'Elegí zona de envío u «Otro lugar».');

                return;
            }
            if ($this->ship_zone_choice !== 'other') {
                if (! preg_match('/^z:(\d+)$/', $this->ship_zone_choice, $sm) || (int) $sm[1] !== (int) $this->delivery_zone_id) {
                    $this->addError('ship_zone_choice', 'Elegí una zona de envío válida.');

                    return;
                }
            }
        }

        $customPayload = null;
        $catalogShipNoSlot = false;
        $catalogRequestedDate = null;
        $catalogDeliveryZoneCalendar = false;

        if ($dm->isPickup()) {
            $this->delivery_zone_selection = '';
            $this->delivery_custom_zone = '';
            $this->delivery_zone_id = null;
            $customPayload = null;
        } elseif ($dm->isDelivery()) {
            if ($this->ship_zone_choice === 'other') {
                $this->validate([
                    'ship_other_place_name' => 'required|string|min:3|max:500',
                    'ship_other_date' => ['required', 'date_format:Y-m-d'],
                    'ship_other_time' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
                ], [
                    'ship_other_place_name.required' => 'Indicá el nombre o referencia del lugar de entrega.',
                    'ship_other_date.required' => 'Elegí la fecha deseada para el delivery.',
                    'ship_other_time.required' => 'Indicá la hora deseada.',
                ]);
                $this->delivery_zone_id = null;
                $time = $this->ship_other_time;
                $customPayload = trim($this->ship_other_place_name)
                    ."\n".'Fecha deseada: '.$this->ship_other_date
                    ."\n".'Hora deseada: '.$time;
                $catalogShipNoSlot = true;
                $catalogRequestedDate = $this->ship_other_date;
            } else {
                $zonesCount = DeliveryZone::query()
                    ->where('company_delivery_method_id', $dm->id)
                    ->where('is_active', true)
                    ->count();

                if ($zonesCount > 0) {
                    $this->validate([
                        'delivery_zone_id' => 'required|integer|exists:delivery_zones,id',
                    ]);
                    $ok = DeliveryZone::query()
                        ->whereKey($this->delivery_zone_id)
                        ->where('company_delivery_method_id', $dm->id)
                        ->where('is_active', true)
                        ->exists();
                    if (! $ok) {
                        $this->addError('delivery_zone_id', 'La zona no es válida para esta ruta.');

                        return;
                    }
                    $customPayload = null;

                    $this->validate([
                        'ship_zone_calendar_date' => ['required', 'date_format:Y-m-d'],
                        'ship_zone_calendar_time' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
                    ], [
                        'ship_zone_calendar_date.required' => 'Elegí la fecha del delivery.',
                        'ship_zone_calendar_time.required' => 'Indicá la hora del delivery.',
                    ]);
                    $this->delivery_slot_id = null;
                    $catalogDeliveryZoneCalendar = true;
                } else {
                    $this->validate([
                        'delivery_custom_zone' => 'required|string|min:5|max:2000',
                    ]);
                    $this->delivery_zone_id = null;
                    $customPayload = trim($this->delivery_custom_zone);
                }
            }
        }

        if ($dm->isPickup() && $this->deliverySlots->isNotEmpty()) {
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
            'catalog_ship_no_slot' => $catalogShipNoSlot,
            'catalog_requested_delivery_date' => $catalogRequestedDate,
            'catalog_delivery_zone_calendar' => $catalogDeliveryZoneCalendar,
            'catalog_scheduled_delivery_date' => $catalogDeliveryZoneCalendar ? $this->ship_zone_calendar_date : null,
            'catalog_scheduled_delivery_time' => $catalogDeliveryZoneCalendar ? $this->ship_zone_calendar_time : null,
        ];

        $order = $checkoutService->checkout($company, $cart, $payload, $request);

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
