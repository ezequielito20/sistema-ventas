<?php

namespace App\Livewire;

use App\Models\CompanyDeliveryMethod;
use App\Models\CompanyPaymentMethod;
use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class OrderCatalogSettings extends Component
{
    public int $companyId;

    public ?int $zoneFilterMethodId = null;

    public ?int $slotFilterMethodId = null;

    /** Zone filter for the slot list (delivery only); 0 = all zones. */
    public int $slotListZoneId = 0;

    // ── Payment method form ──
    public ?int $payId = null;

    public string $payName = '';

    public string $payInstructions = '';

    public string $payDiscount = '0';

    public int $paySort = 0;

    public bool $payActive = true;

    // ── Delivery method form ──
    public ?int $delId = null;

    public string $delType = CompanyDeliveryMethod::TYPE_PICKUP;

    public string $delName = '';

    public string $delInstructions = '';

    public string $delPickupAddress = '';

    public int $delSort = 0;

    public bool $delActive = true;

    // ── Zone form ──
    public ?int $zoneId = null;

    public string $zoneName = '';

    public string $zoneFee = '0';

    public bool $zoneActive = true;

    // ── Slot form ──
    public ?int $slId = null;

    public ?int $slZoneId = null;

    public string $slStarts = '';

    public string $slEnds = '';

    public int $slMax = 1;

    public bool $slActive = true;

    public function mount(): void
    {
        Gate::authorize('orders.settings');
        $this->companyId = (int) Auth::user()->company_id;

        $first = CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->first();
        if ($first) {
            $this->slotFilterMethodId = $first->id;
        }

        $this->zoneFilterMethodId = CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->where('type', CompanyDeliveryMethod::TYPE_DELIVERY)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->value('id');
    }

    public function updatedZoneFilterMethodId(): void
    {
        $this->resetZoneForm();
    }

    public function updatedSlotFilterMethodId(): void
    {
        $this->slotListZoneId = 0;
        $this->slZoneId = null;
        $this->resetSlotForm();
    }

    public function render(): View
    {
        $payments = CompanyPaymentMethod::query()
            ->where('company_id', $this->companyId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $deliveries = CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $zoneMethod = $this->resolveDeliveryMethod($this->zoneFilterMethodId, requireDelivery: true);
        $zones = $zoneMethod
            ? DeliveryZone::query()
                ->where('company_delivery_method_id', $zoneMethod->id)
                ->orderBy('name')
                ->get()
            : collect();

        $slotMethod = $this->resolveDeliveryMethod($this->slotFilterMethodId, requireDelivery: false);
        $slots = $slotMethod
            ? DeliverySlot::query()
                ->where('company_id', $this->companyId)
                ->where('company_delivery_method_id', $slotMethod->id)
                ->when(
                    $slotMethod->isDelivery(),
                    function ($q) {
                        if ($this->slotListZoneId > 0) {
                            $q->where('delivery_zone_id', $this->slotListZoneId);
                        }
                    },
                    fn ($q) => $q->whereNull('delivery_zone_id')
                )
                ->orderBy('starts_at')
                ->with('zone')
                ->get()
            : collect();

        $slotZones = ($slotMethod && $slotMethod->isDelivery())
            ? DeliveryZone::query()
                ->where('company_delivery_method_id', $slotMethod->id)
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.order-catalog-settings', [
            'payments' => $payments,
            'deliveries' => $deliveries,
            'zones' => $zones,
            'slots' => $slots,
            'zoneMethod' => $zoneMethod,
            'slotMethod' => $slotMethod,
            'slotZones' => $slotZones,
        ]);
    }

    public function resetPaymentForm(): void
    {
        $this->payId = null;
        $this->payName = '';
        $this->payInstructions = '';
        $this->payDiscount = '0';
        $this->paySort = 0;
        $this->payActive = true;
    }

    public function editPayment(int $id): void
    {
        $row = CompanyPaymentMethod::query()
            ->where('company_id', $this->companyId)
            ->findOrFail($id);
        $this->payId = $row->id;
        $this->payName = $row->name;
        $this->payInstructions = (string) ($row->instructions ?? '');
        $this->payDiscount = (string) $row->discount_percent;
        $this->paySort = (int) $row->sort_order;
        $this->payActive = (bool) $row->is_active;
    }

    public function savePayment(): void
    {
        Gate::authorize('orders.settings');
        $this->validate([
            'payName' => 'required|string|max:255',
            'payInstructions' => 'nullable|string|max:5000',
            'payDiscount' => 'required|numeric|min:0|max:100',
            'paySort' => 'required|integer|min:0|max:65535',
            'payActive' => 'boolean',
        ]);

        $payload = [
            'company_id' => $this->companyId,
            'name' => $this->payName,
            'instructions' => $this->payInstructions ?: null,
            'discount_percent' => $this->payDiscount,
            'sort_order' => $this->paySort,
            'is_active' => $this->payActive,
        ];

        if ($this->payId) {
            CompanyPaymentMethod::query()
                ->where('company_id', $this->companyId)
                ->whereKey($this->payId)
                ->update($payload);
            session()->flash('status', 'Método de pago actualizado.');
        } else {
            CompanyPaymentMethod::query()->create($payload);
            session()->flash('status', 'Método de pago creado.');
        }
        $this->resetPaymentForm();
    }

    public function deletePayment(int $id): void
    {
        Gate::authorize('orders.settings');
        CompanyPaymentMethod::query()
            ->where('company_id', $this->companyId)
            ->whereKey($id)
            ->delete();
        session()->flash('status', 'Método de pago eliminado.');
        $this->resetPaymentForm();
    }

    public function resetDeliveryForm(): void
    {
        $this->delId = null;
        $this->delType = CompanyDeliveryMethod::TYPE_PICKUP;
        $this->delName = '';
        $this->delInstructions = '';
        $this->delPickupAddress = '';
        $this->delSort = 0;
        $this->delActive = true;
    }

    public function editDelivery(int $id): void
    {
        $row = CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->findOrFail($id);
        $this->delId = $row->id;
        $this->delType = $row->type;
        $this->delName = $row->name;
        $this->delInstructions = (string) ($row->instructions ?? '');
        $this->delPickupAddress = (string) ($row->pickup_address ?? '');
        $this->delSort = (int) $row->sort_order;
        $this->delActive = (bool) $row->is_active;
    }

    public function saveDelivery(): void
    {
        Gate::authorize('orders.settings');
        $this->validate([
            'delType' => 'required|in:'.CompanyDeliveryMethod::TYPE_PICKUP.','.CompanyDeliveryMethod::TYPE_DELIVERY,
            'delName' => 'required|string|max:255',
            'delInstructions' => 'nullable|string|max:5000',
            'delPickupAddress' => 'nullable|string|max:500',
            'delSort' => 'required|integer|min:0|max:65535',
            'delActive' => 'boolean',
        ]);
        if ($this->delType === CompanyDeliveryMethod::TYPE_PICKUP) {
            $this->validate(['delPickupAddress' => 'required|string|max:500']);
        }

        $payload = [
            'company_id' => $this->companyId,
            'type' => $this->delType,
            'name' => $this->delName,
            'instructions' => $this->delInstructions ?: null,
            'pickup_address' => $this->delType === CompanyDeliveryMethod::TYPE_PICKUP ? $this->delPickupAddress : null,
            'sort_order' => $this->delSort,
            'is_active' => $this->delActive,
        ];

        if ($this->delId) {
            if (Order::query()->where('company_delivery_method_id', $this->delId)->exists()) {
                $locked = ['type', 'company_id'];
                foreach ($locked as $k) {
                    unset($payload[$k]);
                }
            }
            CompanyDeliveryMethod::query()
                ->where('company_id', $this->companyId)
                ->whereKey($this->delId)
                ->update($payload);
            session()->flash('status', 'Método de entrega actualizado.');
        } else {
            $created = CompanyDeliveryMethod::query()->create($payload);
            $this->zoneFilterMethodId ??= $created->id;
            $this->slotFilterMethodId ??= $created->id;
            session()->flash('status', 'Método de entrega creado.');
        }
        $this->resetDeliveryForm();
    }

    public function deleteDelivery(int $id): void
    {
        Gate::authorize('orders.settings');
        if (Order::query()->where('company_delivery_method_id', $id)->exists()) {
            session()->flash('error', 'No se puede eliminar: hay pedidos que usan este método. Desactivalo en su lugar.');

            return;
        }
        CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->whereKey($id)
            ->delete();
        session()->flash('status', 'Método de entrega eliminado.');
        $this->resetDeliveryForm();
    }

    public function resetZoneForm(): void
    {
        $this->zoneId = null;
        $this->zoneName = '';
        $this->zoneFee = '0';
        $this->zoneActive = true;
    }

    public function editZone(int $id): void
    {
        $row = DeliveryZone::query()
            ->whereHas('deliveryMethod', fn ($q) => $q->where('company_id', $this->companyId))
            ->findOrFail($id);
        $this->zoneId = $row->id;
        $this->zoneFilterMethodId = $row->company_delivery_method_id;
        $this->zoneName = $row->name;
        $this->zoneFee = (string) $row->extra_fee_usd;
        $this->zoneActive = (bool) $row->is_active;
    }

    public function saveZone(): void
    {
        Gate::authorize('orders.settings');
        $method = $this->resolveDeliveryMethod($this->zoneFilterMethodId, requireDelivery: true);
        if (! $method) {
            session()->flash('error', 'Elegí un método de tipo delivery para las zonas.');

            return;
        }

        $this->validate([
            'zoneName' => 'required|string|max:255',
            'zoneFee' => 'required|numeric|min:0',
            'zoneActive' => 'boolean',
        ]);

        $payload = [
            'company_delivery_method_id' => $method->id,
            'name' => $this->zoneName,
            'extra_fee_usd' => $this->zoneFee,
            'is_active' => $this->zoneActive,
        ];

        if ($this->zoneId) {
            DeliveryZone::query()
                ->whereKey($this->zoneId)
                ->whereHas('deliveryMethod', fn ($q) => $q->where('company_id', $this->companyId))
                ->update($payload);
            session()->flash('status', 'Zona actualizada.');
        } else {
            DeliveryZone::query()->create($payload);
            session()->flash('status', 'Zona creada.');
        }
        $this->resetZoneForm();
    }

    public function deleteZone(int $id): void
    {
        Gate::authorize('orders.settings');
        if (Order::query()->where('delivery_zone_id', $id)->exists()) {
            session()->flash('error', 'No se puede eliminar: hay pedidos con esta zona.');

            return;
        }
        DeliveryZone::query()
            ->whereKey($id)
            ->whereHas('deliveryMethod', fn ($q) => $q->where('company_id', $this->companyId))
            ->delete();
        session()->flash('status', 'Zona eliminada.');
        $this->resetZoneForm();
    }

    public function resetSlotForm(bool $clearId = true): void
    {
        if ($clearId) {
            $this->slId = null;
        }
        $this->slStarts = '';
        $this->slEnds = '';
        $this->slMax = 1;
        $this->slActive = true;
    }

    public function editSlot(int $id): void
    {
        $row = DeliverySlot::query()
            ->where('company_id', $this->companyId)
            ->findOrFail($id);
        $this->slId = $row->id;
        $this->slotFilterMethodId = $row->company_delivery_method_id;
        $this->slZoneId = $row->delivery_zone_id;
        $this->slStarts = $row->starts_at->format('Y-m-d\TH:i');
        $this->slEnds = $row->ends_at->format('Y-m-d\TH:i');
        $this->slMax = (int) $row->max_orders;
        $this->slActive = (bool) $row->is_active;
    }

    public function saveSlot(): void
    {
        Gate::authorize('orders.settings');
        $method = $this->resolveDeliveryMethod($this->slotFilterMethodId, requireDelivery: false);
        if (! $method) {
            session()->flash('error', 'Elegí un método de entrega para la franja.');

            return;
        }

        $rules = [
            'slStarts' => 'required|date',
            'slEnds' => 'required|date|after:slStarts',
            'slMax' => 'required|integer|min:1|max:255',
            'slActive' => 'boolean',
        ];
        if ($method->isDelivery()) {
            $rules['slZoneId'] = 'required|integer|exists:delivery_zones,id';
        }
        $this->validate($rules);

        if ($method->isDelivery()) {
            $zone = DeliveryZone::query()
                ->whereKey((int) $this->slZoneId)
                ->where('company_delivery_method_id', $method->id)
                ->first();
            if (! $zone) {
                session()->flash('error', 'La zona no pertenece al método de entrega.');

                return;
            }
        }

        $payload = [
            'company_id' => $this->companyId,
            'company_delivery_method_id' => $method->id,
            'delivery_zone_id' => $method->isDelivery() ? $this->slZoneId : null,
            'starts_at' => $this->slStarts,
            'ends_at' => $this->slEnds,
            'max_orders' => $this->slMax,
            'is_active' => $this->slActive,
        ];

        if ($this->slId) {
            $slot = DeliverySlot::query()
                ->where('company_id', $this->companyId)
                ->whereKey($this->slId)
                ->firstOrFail();
            if ($slot->booked_count > 0) {
                $slot->update(['is_active' => $this->slActive]);
                session()->flash('status', 'Franja con reservas: solo se actualizó el estado activo.');
                $this->resetSlotForm();

                return;
            }
            $slot->update($payload);
            session()->flash('status', 'Franja actualizada.');
        } else {
            DeliverySlot::query()->create($payload);
            session()->flash('status', 'Franja creada.');
        }
        $this->resetSlotForm();
    }

    public function deleteSlot(int $id): void
    {
        Gate::authorize('orders.settings');
        $slot = DeliverySlot::query()
            ->where('company_id', $this->companyId)
            ->whereKey($id)
            ->firstOrFail();
        if ($slot->booked_count > 0) {
            session()->flash('error', 'No se puede eliminar: la franja tiene pedidos reservados.');

            return;
        }
        if (Order::query()->where('delivery_slot_id', $id)->exists()) {
            session()->flash('error', 'No se puede eliminar: hay pedidos vinculados a esta franja.');

            return;
        }
        $slot->delete();
        session()->flash('status', 'Franja eliminada.');
        $this->resetSlotForm();
    }

    protected function resolveDeliveryMethod(?int $id, bool $requireDelivery): ?CompanyDeliveryMethod
    {
        if (! $id) {
            return null;
        }
        $m = CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->whereKey($id)
            ->first();
        if (! $m) {
            return null;
        }
        if ($requireDelivery && ! $m->isDelivery()) {
            return null;
        }

        return $m;
    }
}
