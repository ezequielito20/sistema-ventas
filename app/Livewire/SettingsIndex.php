<?php

namespace App\Livewire;

use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Currency;
use App\Models\State;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class SettingsIndex extends Component
{
    use WithFileUploads;

    // ── Form fields ──
    public string $name = '';
    public string $business_type = '';
    public string $nit = '';
    public string $phone = '';
    public string $email = '';
    public string $tax_amount = '';
    public string $tax_name = '';
    public string $currency = '';
    public string $address = '';
    public string $city_id = '';
    public string $state_id = '';
    public string $country_id = '';
    public string $postal_code = '';
    public string $ig = '';
    public string $slug = '';
    public bool $catalog_is_public = true;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $logo = null;

    public ?string $current_logo_url = null;

    // ── Selects data ──
    public $countries = [];
    public $states = [];
    public $cities = [];
    public $currencies = [];

    public function mount(): void
    {
        $company = Company::find(Auth::user()->company_id);

        $this->name = $company->name;
        $this->business_type = $company->business_type ?? '';
        $this->nit = $company->nit;
        $this->phone = $company->phone;
        $this->email = $company->email;
        $this->tax_amount = (string) (int) $company->tax_amount;
        $this->tax_name = $company->tax_name;
        $this->currency = $company->currency;
        $this->address = $company->address;
        $this->city_id = (string) $company->city;
        $this->state_id = (string) $company->state;
        $this->country_id = (string) $company->country;
        $this->postal_code = $company->postal_code ?? '';
        $this->ig = $company->ig ?? '';
        $this->slug = $company->slug ?? '';
        $this->catalog_is_public = (bool) $company->catalog_is_public;
        $this->current_logo_url = $company->logo_url;

        // Load select options
        $this->countries = Country::orderBy('name')->get();
        $this->currencies = Currency::orderBy('code')
            ->get()
            ->unique('code')
            ->values();

        if ($this->country_id) {
            $this->states = State::where('country_id', $this->country_id)
                ->orderBy('name')
                ->get();
        }

        if ($this->state_id) {
            $this->cities = City::where('state_id', $this->state_id)
                ->orderBy('name')
                ->get();
        }
    }

    public function updatedCountryId(): void
    {
        $this->states = State::where('country_id', $this->country_id)
            ->orderBy('name')
            ->get();
        $this->cities = [];
        $this->state_id = '';
        $this->city_id = '';

        // Auto-fill currency from country
        $currency = Currency::where('country_id', $this->country_id)->first();
        if ($currency) {
            $this->currency = $currency->code;
        }
    }

    public function updatedStateId(): void
    {
        $this->cities = City::where('state_id', $this->state_id)
            ->orderBy('name')
            ->get();
        $this->city_id = '';
    }

    public function save()
    {
        Gate::authorize('companies.update');

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
            'nit' => 'required|string|max:255|unique:companies,nit,' . Auth::user()->company_id,
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:companies,email,' . Auth::user()->company_id,
            'tax_amount' => 'required|integer',
            'tax_name' => 'required|string|max:255',
            'currency' => 'required|string|max:20',
            'address' => 'required|string',
            'city_id' => 'required|string|max:255',
            'state_id' => 'required|string|max:255',
            'country_id' => 'required|string|max:255',
            'postal_code' => 'required|string|max:255',
            'ig' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:companies,slug,' . Auth::user()->company_id . '|not_in:' . implode(',', Company::RESERVED_SLUGS),
            'catalog_is_public' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'business_type.required' => 'El tipo de negocio es obligatorio',
            'nit.required' => 'El NIT es obligatorio',
            'nit.unique' => 'Este NIT ya está registrado',
            'phone.required' => 'El teléfono es obligatorio',
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Debe ser un correo válido',
            'email.unique' => 'Este correo ya está registrado',
            'tax_amount.required' => 'El impuesto es obligatorio',
            'tax_amount.integer' => 'El impuesto debe ser un número entero',
            'tax_name.required' => 'El nombre del impuesto es obligatorio',
            'currency.required' => 'La moneda es obligatoria',
            'address.required' => 'La dirección es obligatoria',
            'city_id.required' => 'La ciudad es obligatoria',
            'state_id.required' => 'El estado es obligatorio',
            'country_id.required' => 'El país es obligatorio',
            'postal_code.required' => 'El código postal es obligatorio',
            'logo.image' => 'El archivo debe ser una imagen',
            'logo.mimes' => 'Debe ser JPEG, PNG o JPG',
            'logo.max' => 'El archivo no debe superar 2MB',
            'slug.not_in' => 'Este slug es una palabra reservada del sistema y no puede utilizarse.',
            'slug.unique' => 'Este slug ya está en uso por otra empresa.',
        ]);

        $company = Company::find(Auth::user()->company_id);

        // Handle logo upload — usar disco según entorno (public en local, s3 en producción)
        if ($this->logo) {
            $disk = \App\Services\ImageUrlService::getStorageDisk();

            // Delete old logo
            if ($company->logo && Storage::disk($disk)->exists($company->logo)) {
                Storage::disk($disk)->delete($company->logo);
            }

            $validated['logo'] = $this->logo->store('company_logos', $disk);
            $this->current_logo_url = null; // Force refresh
        }

        // Map select IDs back to DB columns
        $validated['country'] = $validated['country_id'];
        $validated['state'] = $validated['state_id'];
        $validated['city'] = $validated['city_id'];
        unset($validated['country_id'], $validated['state_id'], $validated['city_id']);

        // Ensure tax_amount is an integer (DB column is integer, not decimal)
        $validated['tax_amount'] = (int) $validated['tax_amount'];

        // Auto-generate slug if empty or null
        if (empty($validated['slug'])) {
            $validated['slug'] = Company::generateUniqueSlug($validated['name']);
        }

        // Ensure catalog_is_public is boolean
        $validated['catalog_is_public'] = (bool) ($validated['catalog_is_public'] ?? true);

        try {
            $company->update($validated);

            // Update superAdmin email if changed
            if (isset($validated['email'])) {
                \App\Models\User::where('company_id', $company->id)
                    ->where('name', 'superAdmin')
                    ->update(['email' => $validated['email']]);
            }

            session()->flash('message', 'Configuración guardada correctamente');
            session()->flash('icons', 'success');

            return $this->redirect(route('admin.company.edit'));
        } catch (\Throwable $e) {
            session()->flash('message', 'Error al guardar: ' . $e->getMessage());
            session()->flash('icons', 'error');

            return $this->redirect(route('admin.company.edit'));
        }
    }

    public function render(): View
    {
        return view('livewire.settings-index');
    }
}