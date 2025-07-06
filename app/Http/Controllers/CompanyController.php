<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\City;
use App\Models\State;
use App\Models\Company;
use App\Models\Country;
use App\Models\Currency;
use Nnjeim\World\World;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Services\ImageUrlService;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::all();
        $states = State::all();
        $cities = City::all();
        $currencies = Currency::all();
        return view('admin.companies.create', compact('countries', 'states', 'cities', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request data
            $validated = $request->validate([
                'country' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'business_type' => 'required|string|max:255',
                'nit' => 'required|string|max:255|unique:companies',
                'phone' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:companies',
                'tax_amount' => 'required|integer',
                'tax_name' => 'required|string|max:255',
                'currency' => 'required|string|max:20',
                'address' => 'required|string',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'postal_code' => 'required|string|max:255',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'ig' => 'nullable|string|max:255'
            ], [
                'country.required' => 'El país es requerido',
                'country.string' => 'El país debe ser texto',
                'country.max' => 'El país no debe exceder 255 caracteres',
                'name.required' => 'El nombre es requerido',
                'name.string' => 'El nombre debe ser texto',
                'name.max' => 'El nombre no debe exceder 255 caracteres',
                'business_type.required' => 'El tipo de negocio es requerido',
                'business_type.string' => 'El tipo de negocio debe ser texto',
                'business_type.max' => 'El tipo de negocio no debe exceder 255 caracteres',
                'nit.required' => 'El NIT es requerido',
                'nit.string' => 'El NIT debe ser texto',
                'nit.max' => 'El NIT no debe exceder 255 caracteres',
                'nit.unique' => 'Este NIT ya está registrado',
                'phone.required' => 'El teléfono es requerido',
                'phone.string' => 'El teléfono debe ser texto',
                'phone.max' => 'El teléfono no debe exceder 255 caracteres',
                'email.required' => 'El correo electrónico es requerido',
                'email.email' => 'Debe ingresar un correo electrónico válido',
                'email.max' => 'El correo electrónico no debe exceder 255 caracteres',
                'email.unique' => 'Este correo electrónico ya está registrado',
                'tax_amount.required' => 'El monto del impuesto es requerido',
                'tax_amount.integer' => 'El monto del impuesto debe ser un número entero',
                'tax_name.required' => 'El nombre del impuesto es requerido',
                'tax_name.string' => 'El nombre del impuesto debe ser texto',
                'tax_name.max' => 'El nombre del impuesto no debe exceder 255 caracteres',
                'currency.required' => 'La moneda es requerida',
                'currency.string' => 'La moneda debe ser texto',
                'currency.size' => 'La moneda debe tener exactamente 20 caracteres',
                'address.required' => 'La dirección es requerida',
                'address.string' => 'La dirección debe ser texto',
                'city.required' => 'La ciudad es requerida',
                'city.string' => 'La ciudad debe ser texto',
                'city.max' => 'La ciudad no debe exceder 255 caracteres',
                'state.required' => 'El estado es requerido',
                'state.string' => 'El estado debe ser texto',
                'state.max' => 'El estado no debe exceder 255 caracteres',
                'postal_code.required' => 'El código postal es requerido',
                'postal_code.string' => 'El código postal debe ser texto',
                'postal_code.max' => 'El código postal no debe exceder 255 caracteres',

                'logo.image' => 'El archivo debe ser una imagen',
                'logo.mimes' => 'El archivo debe ser una imagen con formato jpeg, png o jpg',
                'logo.max' => 'El archivo no debe pesar más de 2MB',
                'ig.string' => 'El usuario de Instagram debe ser texto',
                'ig.max' => 'El usuario de Instagram no debe exceder 255 caracteres'
            ]);

            // Handle logo upload
            $logoPath = null;
            if ($request->hasFile('logo')) {
                try {
                    $disk = config('app.env') === 'production' ? 'public' : 'public';
                    $logoPath = $request->file('logo')->store('company_logos', $disk);
                    Log::info('Logo uploaded successfully', ['path' => $logoPath, 'disk' => $disk]);
                } catch (\Exception $e) {
                    Log::error('Error uploading logo: ' . $e->getMessage());
                    throw new \Exception('Error al subir el logo: ' . $e->getMessage());
                }
            }

            // Create new company
            $company = Company::create([
                'country' => $validated['country'],
                'name' => $validated['name'],
                'business_type' => $validated['business_type'],
                'nit' => $validated['nit'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'tax_amount' => $validated['tax_amount'],
                'tax_name' => $validated['tax_name'],
                'currency' => $validated['currency'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postal_code' => $validated['postal_code'],
                'logo' => $logoPath,
                'ig' => $validated['ig'] ?? null
            ]);

            // Crear el rol de administrador específico para esta empresa
            $adminRole = \App\Models\Role::create([
                'name' => 'administrador',
                'guard_name' => 'web',
                'company_id' => $company->id
            ]);

            // Asignar todos los permisos disponibles al rol administrador
            $allPermissions = \Spatie\Permission\Models\Permission::all();
            $adminRole->syncPermissions($allPermissions);

            // Create default admin user for the company
            $user = \App\Models\User::create([
                'name' => 'superAdmin',
                'email' => $validated['email'],
                'password' => Hash::make('12345'), // Default password
                'company_id' => $company->id,
                'email_verified_at' => now(),
            ]);

            // Asignar rol de administrador al usuario
            $user->assignRole($adminRole);

            DB::commit();

            // Iniciar sesión
            Auth::login($user);

            if (Auth::check()) {
                return redirect()->route('admin.index')
                    ->with('message', 'Empresa y usuario administrador creados exitosamente.')
                    ->with('icons', 'success');
            } else {
                return redirect()->route('admin.company.create')
                    ->with('message', 'Error al iniciar sesión después de crear la empresa.')
                    ->with('icons', 'error')
                    ->withInput();
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            if (isset($logoPath)) {
                $disk = config('app.env') === 'production' ? 'public' : 'public';
                if (Storage::disk($disk)->exists($logoPath)) {
                    Storage::disk($disk)->delete($logoPath);
                }
            }

            return redirect()->route('admin.company.create')
                ->with('error', $e->validator->errors()->first())
                ->with('icons', 'error')
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($logoPath)) {
                $disk = config('app.env') === 'production' ? 'public' : 'public';
                if (Storage::disk($disk)->exists($logoPath)) {
                    Storage::disk($disk)->delete($logoPath);
                }
            }

            return redirect()->route('admin.company.create')
                ->with('error', 'Error al crear la empresa: ' . $e->getMessage())
                ->with('icons', 'error')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        $countries = Country::all();
        $states = State::all();
        $cities = City::all();
        $currencies = Currency::all();
        $company = Auth::user()->company;
        return view('admin.companies.edit', compact('countries', 'states', 'cities', 'currencies', 'company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Encuentra la compañía por ID
        $company = Company::findOrFail($id);

        // Valida los datos de entrada
        $validated = $request->validate([
            'country' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
            'nit' => 'required|string|max:255|unique:companies,nit,' . $id,
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:companies,email,' . $id,
            'tax_amount' => 'required|integer',
            'tax_name' => 'required|string|max:255',
            'currency' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'ig' => 'nullable|string|max:255'
        ], [
            'country.required' => 'El país es requerido',
            'country.string' => 'El país debe ser texto',
            'country.max' => 'El país no debe exceder 255 caracteres',
            'name.required' => 'El nombre es requerido',
            'name.string' => 'El nombre debe ser texto',
            'name.max' => 'El nombre no debe exceder 255 caracteres',
            'business_type.required' => 'El tipo de negocio es requerido',
            'business_type.string' => 'El tipo de negocio debe ser texto',
            'business_type.max' => 'El tipo de negocio no debe exceder 255 caracteres',
            'nit.required' => 'El NIT es requerido',
            'nit.string' => 'El NIT debe ser texto',
            'nit.max' => 'El NIT no debe exceder 255 caracteres',
            'nit.unique' => 'Este NIT ya está registrado',
            'phone.required' => 'El teléfono es requerido',
            'phone.string' => 'El teléfono debe ser texto',
            'phone.max' => 'El teléfono no debe exceder 255 caracteres',
            'email.required' => 'El correo electrónico es requerido',
            'email.email' => 'Debe ingresar un correo electrónico válido',
            'email.max' => 'El correo electrónico no debe exceder 255 caracteres',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'tax_amount.required' => 'El monto del impuesto es requerido',
            'tax_amount.integer' => 'El monto del impuesto debe ser un número entero',
            'tax_name.required' => 'El nombre del impuesto es requerido',
            'tax_name.string' => 'El nombre del impuesto debe ser texto',
            'tax_name.max' => 'El nombre del impuesto no debe exceder 255 caracteres',
            'currency.required' => 'La moneda es requerida',
            'currency.string' => 'La moneda debe ser texto',
            'currency.max' => 'La moneda no debe tener más de 20 caracteres',
            'address.required' => 'La dirección es requerida',
            'address.string' => 'La dirección debe ser texto',
            'city.required' => 'La ciudad es requerida',
            'city.string' => 'La ciudad debe ser texto',
            'city.max' => 'La ciudad no debe exceder 255 caracteres',
            'state.required' => 'El estado es requerido',
            'state.string' => 'El estado debe ser texto',
            'state.max' => 'El estado no debe exceder 255 caracteres',
            'postal_code.required' => 'El código postal es requerido',
            'postal_code.string' => 'El código postal debe ser texto',
            'postal_code.max' => 'El código postal no debe exceder 255 caracteres',
            'logo.image' => 'El archivo debe ser una imagen',
            'logo.mimes' => 'El archivo debe ser una imagen con formato jpeg, png o jpg',
            'logo.max' => 'El archivo no debe pesar más de 2MB',
            'ig.string' => 'El usuario de Instagram debe ser texto',
            'ig.max' => 'El usuario de Instagram no debe exceder 255 caracteres'
        ]);

        try {
            // Maneja la actualización del logo si se proporciona uno nuevo
            if ($request->hasFile('logo')) {
                try {
                    $disk = config('app.env') === 'production' ? 'public' : 'public';
                    
                    // Elimina el logo anterior si existe
                    if ($company->logo && Storage::disk($disk)->exists($company->logo)) {
                        Storage::disk($disk)->delete($company->logo);
                        Log::info('Old logo deleted', ['path' => $company->logo]);
                    }
                    
                    // Guarda el nuevo logo
                    $logoPath = $request->file('logo')->store('company_logos', $disk);
                    $validated['logo'] = $logoPath;
                    Log::info('New logo uploaded', ['path' => $logoPath, 'disk' => $disk]);
                } catch (\Exception $e) {
                    Log::error('Error handling logo upload: ' . $e->getMessage());
                    throw new \Exception('Error al procesar el logo: ' . $e->getMessage());
                }
            }

            // Actualiza la compañía con los datos validados
            $company->update($validated);

            // Update associated user's email if it changed
            if (isset($validated['email'])) {
                User::where('company_id', $company->id)
                    ->where('name', 'superAdmin')
                    ->update(['email' => $validated['email']]);
            }

            return redirect()->route('admin.index')
                ->with('message', 'Empresa actualizada correctamente.')
                ->with('icons', 'success');
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error updating company: ' . $e->getMessage(), [
                'company_id' => $id,
                'user_id' => Auth::id(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.company.edit')
                ->with('message', 'Hubo un problema al actualizar la empresa: ' . $e->getMessage())
                ->with('icons', 'error');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        //
    }

    public function search_country($id_country)
    {
        try {
            // Get country and related states
            $country = Country::find($id_country);
            $states = State::where('country_id', $id_country)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            // Get currency for this country
            $currency = Currency::where('country_id', $id_country)->first();

            return response()->json([
                'states' => $states,
                'postal_code' => $country->phone_code ?? '',
                'currency_code' => $currency ? $currency->code . ' - ' . $currency->symbol : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'states' => [],
                'postal_code' => '',
                'currency_code' => null
            ], 500);
        }
    }

    public function search_state($id_state)
    {
        try {
            // Get cities and state information
            $state = State::find($id_state);

            $cities = City::where('state_id', $id_state)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            // No enviamos el código postal en la respuesta del estado
            return response()->json([
                'cities' => $cities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'cities' => []
            ], 500);
        }
    }
}
