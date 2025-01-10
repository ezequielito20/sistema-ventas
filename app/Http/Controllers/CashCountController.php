<?php

namespace App\Http\Controllers;

use App\Models\CashCount;
use App\Http\Requests\StoreCashCountRequest;
use App\Http\Requests\UpdateCashCountRequest;

class CashCountController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      return view('admin.cash-counts.index');
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
