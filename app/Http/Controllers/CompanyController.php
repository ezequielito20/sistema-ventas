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
            $states = DB::table('states')->where('country_id', $country->id)->get();

            // Build select element with states
            $html = '<select name="state" class="form-control" required>';
            $html .= '<option value="">Estado</option>';
            
            foreach($states as $state) {
                $html .= '<option value="'.$state->id.'">'.$state->name.'</option>';
            }
            
            $html .= '</select>';

            return $html;

        } catch (\Exception $e) {
            return '<select name="state" class="form-control" required><option value="">Error al cargar estados</option></select>';
        }
        
    }
}
