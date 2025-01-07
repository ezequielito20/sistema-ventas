<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener el ID de la compañía del usuario autenticado
        $companyId = Auth::user()->company_id;

        // Obtener todas las compras de la compañía con sus relaciones
        $purchases = Purchase::with(['supplier', 'product'])
            ->where('company_id', $companyId)
            ->latest()
            ->get();

        // Calcular estadísticas para los widgets
        $totalPurchases = $purchases->count();
        $totalAmount = $purchases->sum('total_price');
        
        // Calcular compras del mes actual
        $monthlyPurchases = $purchases->filter(function($purchase) {
            return $purchase->purchase_date->isCurrentMonth();
        })->count();

        // Calcular compras pendientes (las que no tienen recibo de pago)
        $pendingDeliveries = $purchases->whereNull('payment_receipt')->count();

        return view('admin.purchases.index', compact(
            'purchases',
            'totalPurchases',
            'totalAmount',
            'monthlyPurchases',
            'pendingDeliveries'
        ));
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
    public function store(StorePurchaseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        //
    }
}
