<?php

namespace App\Livewire;

use App\Models\CompanyPaymentMethod;
use App\Services\PlanEntitlementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CatalogPaymentMethodForm extends Component
{
    public ?int $paymentMethodId = null;

    public string $name = '';

    public string $instructions = '';

    public string $discountPercent = '0';

    public bool $isActive = true;

    protected function entitlement(): PlanEntitlementService
    {
        return app(PlanEntitlementService::class);
    }

    protected function authorizePayments(string $suffix): void
    {
        abort_unless(
            $this->entitlement()->tenantUserMayUseCatalogPaymentsAbility(Auth::user(), $suffix),
            403
        );
    }

    public function mount(?int $paymentMethodId = null): void
    {
        $this->paymentMethodId = $paymentMethodId;

        if ($this->paymentMethodId !== null) {
            $this->authorizePayments('edit');
            $row = CompanyPaymentMethod::query()
                ->where('company_id', Auth::user()->company_id)
                ->whereKey($this->paymentMethodId)
                ->firstOrFail();

            $this->name = $row->name;
            $this->instructions = (string) ($row->instructions ?? '');
            $this->discountPercent = (string) $row->discount_percent;
            $this->isActive = (bool) $row->is_active;

            return;
        }

        $this->authorizePayments('create');
    }

    public function save(): mixed
    {
        $suffix = $this->paymentMethodId ? 'edit' : 'create';
        $this->authorizePayments($suffix);

        $this->validate([
            'name' => 'required|string|max:255',
            'instructions' => 'nullable|string|max:5000',
            'discountPercent' => 'required|numeric|min:0|max:100',
            'isActive' => 'boolean',
        ]);

        $companyId = (int) Auth::user()->company_id;

        $payload = [
            'company_id' => $companyId,
            'name' => $this->name,
            'instructions' => $this->instructions !== '' ? $this->instructions : null,
            'discount_percent' => $this->discountPercent,
            'is_active' => $this->isActive,
        ];

        if ($this->paymentMethodId !== null) {
            CompanyPaymentMethod::query()
                ->where('company_id', $companyId)
                ->whereKey($this->paymentMethodId)
                ->update($payload);
            session()->flash('icons', 'success');
            session()->flash('message', 'Método de pago actualizado.');
        } else {
            CompanyPaymentMethod::query()->create($payload);
            session()->flash('icons', 'success');
            session()->flash('message', 'Método de pago creado.');
        }

        return $this->redirect(route('admin.catalog-payment-methods.index'), navigate: true);
    }

    public function render(): View
    {
        $headingTitle = $this->paymentMethodId ? 'Editar método de pago' : 'Nuevo método de pago';

        return view('livewire.catalog-payment-method-form', [
            'headingTitle' => $headingTitle,
        ]);
    }
}
