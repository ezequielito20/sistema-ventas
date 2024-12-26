<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Nnjeim\World\World;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;

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
        $countries = DB::table('countries')->get();
        $states = DB::table('states')->get();
        $cities = DB::table('cities')->get();
        $currencies = DB::table('currencies')->get();
        return view('admin.companies.create', compact('countries', 'states', 'cities', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'currency' => 'required|string|size:20',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('company_logos', 'public');
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
            'logo' => $logoPath
        ]);

        return redirect()->route('admin.companies.index')
            ->with('success', 'Empresa creada exitosamente.');
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
    public function edit(Company $company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        //
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
            $country = DB::table('countries')->where('id', $id_country)->first();
            $states = DB::table('states')
                ->where('country_id', $id_country)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
            
            // Get currency for this country
            $currency = DB::table('currencies')
                ->where('country_id', $id_country)
                ->first();

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
            $state = DB::table('states')
                ->where('id', $id_state)
                ->first();
            
            $cities = DB::table('cities')
                ->where('state_id', $id_state)
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
