<?php

namespace App\Livewire;

use App\Livewire\Concerns\MergesValidationErrors;
use App\Models\Customer;
use App\Services\CustomerPersistenceService;
use App\Services\PlanEntitlementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CustomerForm extends Component
{
    use MergesValidationErrors;

    public ?int $customerId = null;

    public string $name = '';

    public string $nit_number = '';

    public string $phone = '';

    public string $email = '';

    public string $total_debt = '0';

    public ?string $returnTo = null;

    public function mount(?int $customerId = null): void
    {
        $this->customerId = $customerId;
        $this->returnTo = request()->query('return_to');

        if ($this->customerId !== null) {
            Gate::authorize('customers.edit');

            $customer = Customer::query()
                ->where('company_id', Auth::user()->company_id)
                ->where('id', $this->customerId)
                ->firstOrFail();

            $this->name = $customer->name;
            $this->nit_number = (string) ($customer->nit_number ?? '');
            $this->phone = (string) ($customer->phone ?? '');
            $this->email = (string) ($customer->email ?? '');
            $this->total_debt = (string) $customer->total_debt;

            return;
        }

        Gate::authorize('customers.create');
    }

    public function saveAndBack(CustomerPersistenceService $persistence): mixed
    {
        if ($this->customerId !== null) {
            return $this->updateCustomer($persistence);
        }

        return $this->persistCreate($persistence, false);
    }

    public function saveAndCreateAnother(CustomerPersistenceService $persistence): mixed
    {
        return $this->persistCreate($persistence, true);
    }

    protected function updateCustomer(CustomerPersistenceService $persistence): mixed
    {
        Gate::authorize('customers.edit');

        $customer = Customer::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $this->customerId)
            ->firstOrFail();

        try {
            $persistence->validateAndUpdate($customer, $this->payloadForPersistence());
        } catch (ValidationException $e) {
            $this->mergeValidationErrors($e);

            return null;
        }

        session()->flash('message', '¡Cliente actualizado exitosamente!');
        session()->flash('icons', 'success');

        return $this->redirect(route('admin.customers.index'));
    }

    protected function persistCreate(CustomerPersistenceService $persistence, bool $createAnother): mixed
    {
        Gate::authorize('customers.create');

        try {
            app(PlanEntitlementService::class)->assertCanCreate(Auth::user(), 'customers');
            $customer = $persistence->validateAndCreate($this->payloadForPersistence());
        } catch (ValidationException $e) {
            $this->mergeValidationErrors($e);

            return null;
        }

        if ($createAnother) {
            session()->flash('message', '¡Cliente creado exitosamente! Puedes crear otro cliente.');
            session()->flash('icons', 'success');

            $query = array_filter(['return_to' => $this->returnTo], fn ($v) => $v !== null && $v !== '');

            return $this->redirect(route('admin.customers.create', $query));
        }

        if ($this->returnTo === 'sales.create') {
            session()->flash('message', '¡Cliente creado exitosamente! Ya está seleccionado en la venta.');
            session()->flash('icons', 'success');

            return $this->redirect(route('admin.sales.create', ['customer_id' => $customer->id]));
        }

        session()->flash('message', '¡Cliente creado exitosamente!');
        session()->flash('icons', 'success');

        return $this->redirect(route('admin.customers.index'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function payloadForPersistence(): array
    {
        $data = [
            'name' => $this->name,
            'nit_number' => $this->nit_number !== '' ? $this->nit_number : null,
            'phone' => $this->phone !== '' ? $this->phone : null,
            'email' => $this->email !== '' ? $this->email : null,
        ];

        if ($this->customerId !== null) {
            $data['total_debt'] = $this->total_debt;
        }

        return $data;
    }

    public function render(): View
    {
        $isEdit = $this->customerId !== null;

        return view('livewire.customer-form', [
            'headingTitle' => $isEdit ? 'Editar cliente' : 'Crear cliente',
            'headingSubtitle' => $isEdit
                ? 'Actualiza los datos del cliente.'
                : 'Registra un nuevo cliente en tu empresa.',
        ]);
    }
}
