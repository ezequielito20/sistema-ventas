<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Http\Requests\StoreCashMovementRequest;
use App\Http\Requests\UpdateCashMovementRequest;

class CashMovementController extends Controller
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCashMovementRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CashMovement $cashMovement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CashMovement $cashMovement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCashMovementRequest $request, CashMovement $cashMovement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashMovement $cashMovement)
    {
        //
    }
}
