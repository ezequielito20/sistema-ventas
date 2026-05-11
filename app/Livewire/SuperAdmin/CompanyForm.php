<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Company;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class CompanyForm extends Component
{
    public string $companyName = '';

    public string $companyNit = '';

    public string $email = '';

    public string $adminName = '';

    public string $adminEmail = '';

    public string $adminPassword = '';

    public string $adminPasswordConfirmation = '';

    public string $planId = '';

    public int $billingDay = 1;

    public function mount(): void
    {
        if (!auth()->user() || !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $defaultPlan = Plan::where('slug', 'basico')->first();
        $this->planId = (string) ($defaultPlan?->id ?? '');
    }

    protected function rules(): array
    {
        return [
            'companyName' => ['required', 'string', 'max:255'],
            'companyNit' => ['required', 'string', 'max:255', 'unique:companies,nit'],
            'email' => ['required', 'email', 'max:255', 'unique:companies,email'],
            'adminName' => ['required', 'string', 'max:255'],
            'adminEmail' => ['required', 'email', 'max:255', 'unique:users,email'],
            'adminPassword' => ['required', 'string', 'min:8', 'confirmed'],
            'planId' => ['required', 'exists:plans,id'],
            'billingDay' => ['required', 'integer', 'min:1', 'max:28'],
        ];
    }

    protected function messages(): array
    {
        return [
            'companyName.required' => 'El nombre de la empresa es requerido.',
            'companyNit.required' => 'El NIT de la empresa es requerido.',
            'companyNit.unique' => 'Este NIT ya está registrado.',
            'email.required' => 'El correo de la empresa es requerido.',
            'email.unique' => 'Este correo de empresa ya está registrado.',
            'adminName.required' => 'El nombre del administrador es requerido.',
            'adminEmail.required' => 'El correo del administrador es requerido.',
            'adminEmail.unique' => 'Este correo ya está registrado.',
            'adminPassword.required' => 'La contraseña es requerida.',
            'adminPassword.min' => 'La contraseña debe tener al menos :min caracteres.',
            'adminPassword.confirmed' => 'Las contraseñas no coinciden.',
            'planId.required' => 'Debe seleccionar un plan.',
            'billingDay.required' => 'El día de cobro es requerido.',
        ];
    }

    protected function toast(string $message, string $type = 'success'): void
    {
        $titles = ['success' => 'Listo', 'error' => 'Atención', 'warning' => 'Atención', 'info' => 'Información'];
        $uiType = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
        $title = $titles[$uiType] ?? $titles['info'];
        $timeout = $uiType === 'error' ? 7200 : 4800;
        $options = json_encode(['type' => $uiType, 'title' => $title, 'timeout' => $timeout, 'theme' => 'futuristic'], JSON_THROW_ON_ERROR);
        $msg = json_encode($message, JSON_THROW_ON_ERROR);
        $this->js('if (window.uiNotifications && typeof window.uiNotifications.showToast === "function") {'
            . 'window.uiNotifications.showToast(' . $msg . ', ' . $options . ');}');
    }

    public function save(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $company = Company::create([
                'name' => $this->companyName,
                'nit' => $this->companyNit,
                'email' => $this->email,
                'country' => '162',
                'city' => '538',
                'state' => '30032',
                'postal_code' => '10001',
                'currency' => 'USD - $',
                'tax_amount' => 0,
                'tax_name' => 'IVA',
                'business_type' => 'General',
                'phone' => '',
                'address' => '',
                'subscription_status' => 'active',
                'billing_day' => $this->billingDay,
            ]);

            $adminRole = \App\Models\Role::create([
                'name' => 'administrador',
                'guard_name' => 'web',
                'company_id' => $company->id,
            ]);

            $allPermissions = \Spatie\Permission\Models\Permission::where('name', 'not like', 'system.%')
                ->where('name', 'not like', 'plans.%')
                ->where('name', 'not like', 'subscriptions.%')
                ->where('name', '!=', 'super-admin.access')
                ->get();
            $adminRole->syncPermissions($allPermissions);

            $user = \App\Models\User::create([
                'name' => $this->adminName,
                'email' => $this->adminEmail,
                'password' => Hash::make($this->adminPassword),
                'company_id' => $company->id,
                'email_verified_at' => now(),
            ]);

            $user->assignRole($adminRole);

            $plan = Plan::findOrFail((int) $this->planId);
            app(SubscriptionService::class)->createForCompany($company, $plan, $this->billingDay);

            DB::commit();

            session()->flash('message', "Empresa \"{$this->companyName}\" creada correctamente con usuario administrador.");
            session()->flash('icons', 'success');

            $this->redirect(route('super-admin.companies.index'));
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast('Error al crear la empresa: ' . $e->getMessage(), 'error');
        }
    }

    public function saveAndCreateAnother(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $company = Company::create([
                'name' => $this->companyName,
                'nit' => $this->companyNit,
                'email' => $this->email,
                'country' => '162',
                'city' => '538',
                'state' => '30032',
                'postal_code' => '10001',
                'currency' => 'USD - $',
                'tax_amount' => 0,
                'tax_name' => 'IVA',
                'business_type' => 'General',
                'phone' => '',
                'address' => '',
                'subscription_status' => 'active',
                'billing_day' => $this->billingDay,
            ]);

            $adminRole = \App\Models\Role::create([
                'name' => 'administrador',
                'guard_name' => 'web',
                'company_id' => $company->id,
            ]);

            $allPermissions = \Spatie\Permission\Models\Permission::where('name', 'not like', 'system.%')
                ->where('name', 'not like', 'plans.%')
                ->where('name', 'not like', 'subscriptions.%')
                ->where('name', '!=', 'super-admin.access')
                ->get();
            $adminRole->syncPermissions($allPermissions);

            $user = \App\Models\User::create([
                'name' => $this->adminName,
                'email' => $this->adminEmail,
                'password' => Hash::make($this->adminPassword),
                'company_id' => $company->id,
                'email_verified_at' => now(),
            ]);

            $user->assignRole($adminRole);

            $plan = Plan::findOrFail((int) $this->planId);
            app(SubscriptionService::class)->createForCompany($company, $plan, $this->billingDay);

            DB::commit();

            $this->reset(['companyName', 'companyNit', 'email', 'adminName', 'adminEmail', 'adminPassword', 'adminPasswordConfirmation', 'billingDay']);
            $this->billingDay = 1;
            $defaultPlan = Plan::where('slug', 'basico')->first();
            $this->planId = (string) ($defaultPlan?->id ?? '');

            $this->toast("Empresa \"{$company->name}\" creada. Podés crear otra.", 'success');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast('Error al crear la empresa: ' . $e->getMessage(), 'error');
        }
    }

    public function render(): View
    {
        $plans = Plan::active()->orderBy('name')->get();

        return view('livewire.super-admin.company-form', [
            'plans' => $plans,
        ]);
    }
}
