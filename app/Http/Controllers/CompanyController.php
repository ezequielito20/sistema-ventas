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
    public function store(StoreCompanyRequest $request)
    {
        //
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
    public function update(UpdateCompanyRequest $request, Company $company)
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
                'currency_code' => $currency ? $currency->code : null
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
