<?php

namespace App\Http\Controllers;

use App\Models\CashCount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreCashCountRequest;
use App\Http\Requests\UpdateCashCountRequest;

class CashCountController extends Controller
{
   public $currencies;
   protected $company;

   public function __construct()
   {
      $this->middleware(function ($request, $next) {
         $this->company = Auth::user()->company;
         $this->currencies = DB::table('currencies')
            ->where('country_id', $this->company->country)
            ->first();

         return $next($request);
      });
   }
   public function index()
   {
      $cashCounts = CashCount::with('movements')
         ->where('company_id', $this->company->id)
         ->get();
      $currency = $this->currencies;
      return view('admin.cash-counts.index', compact('cashCounts', 'currency'));
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      //
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(StoreCashCountRequest $request)
   {
      //
   }

   /**
    * Display the specified resource.
    */
   public function show(CashCount $cashCount)
   {
      //
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(CashCount $cashCount)
   {
      //
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(UpdateCashCountRequest $request, CashCount $cashCount)
   {
      //
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(CashCount $cashCount)
   {
      //
   }
}
