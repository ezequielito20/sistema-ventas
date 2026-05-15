<?php

namespace App\Livewire;

use App\Models\CompanyDeliveryMethod;
use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Services\PlanEntitlementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CatalogDeliveryMethodForm extends Component
{
    public ?int $deliveryMethodId = null;

    protected int $companyId = 0;

    public string $delType = CompanyDeliveryMethod::TYPE_PICKUP;

    public string $delName = '';

    public string $delInstructions = '';

    public string $delPickupAddress = '';

    public int $delSort = 0;

    public bool $delActive = true;

    public ?int $zoneId = null;

    public string $zoneName = '';

    public string $zoneFee = '0';

    public bool $zoneActive = true;

    public ?int $slId = null;

    public ?int $slZoneId = null;

    public string $slStarts = '';

    public string $slEnds = '';

    public int $slMax = 1;

    public bool $slActive = true;

    public int $slotListZoneId = 0;

    protected function toast(string $message, string $type = 'success'): void
    {
        $titles = [
            'success' => 'Listo',
            'error' => 'Atención',
            'warning' => 'Atención',
            'info' => 'Información',
        ];
        $uiType = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
        $title = $titles[$uiType] ?? $titles['info'];
        $timeout = $uiType === 'error' ? 7200 : 4800;

        $options = json_encode([
            'type' => $uiType,
            'title' => $title,
            'timeout' => $timeout,
            'theme' => 'futuristic',
        ], JSON_THROW_ON_ERROR);

        $msg = json_encode($message, JSON_THROW_ON_ERROR);

        $this->js(
            'if (window.uiNotifications && typeof window.uiNotifications.showToast === "function") {'
            .'window.uiNotifications.showToast('.$msg.', '.$options.');}'
        );
    }

    protected function entitlement(): PlanEntitlementService
    {
        return app(PlanEntitlementService::class);
    }

    protected function authorizeDeliveries(string $abilitySuffix): void
    {
        abort_unless(
            $this->entitlement()->tenantUserMayUseCatalogDeliveriesAbility(Auth::user(), $abilitySuffix),
            403
        );
    }

    protected function deliveryMethod(): ?CompanyDeliveryMethod
    {
        if ($this->deliveryMethodId === null) {
            return null;
        }

        return CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->whereKey($this->deliveryMethodId)
            ->first();
    }

    public function mount(?int $deliveryMethodId = null): void
    {
        $this->companyId = (int) Auth::user()->company_id;
        $this->deliveryMethodId = $deliveryMethodId;

        if ($this->deliveryMethodId !== null) {
            $this->authorizeDeliveries('edit');
            $row = CompanyDeliveryMethod::query()
                ->where('company_id', $this->companyId)
                ->whereKey($this->deliveryMethodId)
                ->firstOrFail();
            $this->fillMethodFields($row);

            return;
        }

        $this->authorizeDeliveries('create');
    }

    protected function fillMethodFields(CompanyDeliveryMethod $row): void
    {
        $this->delType = $row->type;
        $this->delName = $row->name;
        $this->delInstructions = (string) ($row->instructions ?? '');
        $this->delPickupAddress = (string) ($row->pickup_address ?? '');
        $this->delSort = (int) $row->sort_order;
        $this->delActive = (bool) $row->is_active;
    }

    public function updatedDelType(): void
    {
        $this->resetZoneForm();
        $this->resetSlotForm();
        $this->slotListZoneId = 0;
        $this->slZoneId = null;
    }

    public function saveDeliveryMethod(): mixed
    {
        $this->authorizeDeliveries($this->deliveryMethodId ? 'edit' : 'create');

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
            'instructions' => $this->delInstructions !== '' ? $this->delInstructions : null,
            'pickup_address' => $this->delType === CompanyDeliveryMethod::TYPE_PICKUP ? $this->delPickupAddress : null,
            'sort_order' => $this->delSort,
            'is_active' => $this->delActive,
        ];

        if ($this->deliveryMethodId !== null) {
            $id = $this->deliveryMethodId;
            if (Order::query()->where('company_delivery_method_id', $id)->exists()) {
                $locked = ['type', 'company_id'];
                foreach ($locked as $k) {
                    unset($payload[$k]);
                }
            }
            CompanyDeliveryMethod::query()
                ->where('company_id', $this->companyId)
                ->whereKey($id)
                ->update($payload);

            $this->toast('Método de entrega actualizado.', 'success');

            return null;
        }

        $created = CompanyDeliveryMethod::query()->create($payload);
        session()->flash('icons', 'success');
        session()->flash('message', 'Método de entrega creado. Ahora podés agregar zonas y franjas si aplica.');

        return $this->redirect(route('admin.catalog-delivery-methods.edit', $created->id), navigate: true);
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
        $this->authorizeDeliveries('edit');
        $method = $this->deliveryMethod();
        if (! $method?->isDelivery()) {
            return;
        }
        $row = DeliveryZone::query()
            ->where('company_delivery_method_id', $method->id)
            ->whereHas('deliveryMethod', fn ($q) => $q->where('company_id', $this->companyId))
            ->findOrFail($id);
        $this->zoneId = $row->id;
        $this->zoneName = $row->name;
        $this->zoneFee = (string) $row->extra_fee_usd;
        $this->zoneActive = (bool) $row->is_active;
    }

    public function saveZone(): void
    {
        $this->authorizeDeliveries($this->zoneId ? 'edit' : 'create');
        $method = $this->deliveryMethod();
        if (! $method?->isDelivery()) {
            $this->toast('Las zonas solo aplican a métodos de tipo delivery.', 'error');

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
                ->where('company_delivery_method_id', $method->id)
                ->update($payload);
            $this->toast('Zona actualizada.', 'success');
        } else {
            DeliveryZone::query()->create($payload);
            $this->toast('Zona creada.', 'success');
        }
        $this->resetZoneForm();
    }

    public function deleteZone(int $id): void
    {
        $this->authorizeDeliveries('destroy');
        if (Order::query()->where('delivery_zone_id', $id)->exists()) {
            $this->toast('No se puede eliminar: hay pedidos con esta zona.', 'error');

            return;
        }
        $method = $this->deliveryMethod();
        if (! $method) {
            return;
        }
        DeliveryZone::query()
            ->whereKey($id)
            ->where('company_delivery_method_id', $method->id)
            ->delete();
        $this->toast('Zona eliminada.', 'success');
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

    public function updatedSlotListZoneId(): void
    {
        $this->resetSlotForm();
    }

    public function editSlot(int $id): void
    {
        $this->authorizeDeliveries('edit');
        $method = $this->deliveryMethod();
        if (! $method) {
            return;
        }
        $row = DeliverySlot::query()
            ->where('company_id', $this->companyId)
            ->where('company_delivery_method_id', $method->id)
            ->findOrFail($id);
        $this->slId = $row->id;
        $this->slZoneId = $row->delivery_zone_id;
        $this->slStarts = $row->starts_at->format('Y-m-d\TH:i');
        $this->slEnds = $row->ends_at->format('Y-m-d\TH:i');
        $this->slMax = (int) $row->max_orders;
        $this->slActive = (bool) $row->is_active;
    }

    public function saveSlot(): void
    {
        $this->authorizeDeliveries($this->slId ? 'edit' : 'create');
        $method = $this->deliveryMethod();
        if (! $method) {
            $this->toast('Primero creá el método de entrega.', 'error');

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
                $this->toast('La zona no pertenece a este método.', 'error');

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
                $this->toast('Franja con reservas: solo se actualizó el estado activo.', 'info');
                $this->resetSlotForm();

                return;
            }
            $slot->update($payload);
            $this->toast('Franja actualizada.', 'success');
        } else {
            DeliverySlot::query()->create($payload);
            $this->toast('Franja creada.', 'success');
        }
        $this->resetSlotForm();
    }

    public function deleteSlot(int $id): void
    {
        $this->authorizeDeliveries('destroy');
        $slot = DeliverySlot::query()
            ->where('company_id', $this->companyId)
            ->whereKey($id)
            ->firstOrFail();
        if ($slot->booked_count > 0) {
            $this->toast('No se puede eliminar: la franja tiene pedidos reservados.', 'error');

            return;
        }
        if (Order::query()->where('delivery_slot_id', $id)->exists()) {
            $this->toast('No se puede eliminar: hay pedidos vinculados a esta franja.', 'error');

            return;
        }
        $slot->delete();
        $this->toast('Franja eliminada.', 'success');
        $this->resetSlotForm();
    }

    public function render(): View
    {
        $method = $this->deliveryMethod();
        $zones = collect();
        $slots = collect();
        $slotZones = collect();

        if ($method?->isDelivery()) {
            $zones = DeliveryZone::query()
                ->where('company_delivery_method_id', $method->id)
                ->orderBy('name')
                ->get();
        }

        if ($method) {
            $slots = DeliverySlot::query()
                ->where('company_id', $this->companyId)
                ->where('company_delivery_method_id', $method->id)
                ->when(
                    $method->isDelivery() && $this->slotListZoneId > 0,
                    fn ($q) => $q->where('delivery_zone_id', $this->slotListZoneId)
                )
                ->when(
                    ! $method->isDelivery(),
                    fn ($q) => $q->whereNull('delivery_zone_id')
                )
                ->orderBy('starts_at')
                ->with('zone')
                ->get();

            if ($method->isDelivery()) {
                $slotZones = DeliveryZone::query()
                    ->where('company_delivery_method_id', $method->id)
                    ->orderBy('name')
                    ->get();
            }
        }

        $headingTitle = $this->deliveryMethodId ? 'Editar método de entrega' : 'Nuevo método de entrega';

        return view('livewire.catalog-delivery-method-form', [
            'headingTitle' => $headingTitle,
            'methodModel' => $method,
            'zones' => $zones,
            'slots' => $slots,
            'slotZones' => $slotZones,
        ]);
    }
}
