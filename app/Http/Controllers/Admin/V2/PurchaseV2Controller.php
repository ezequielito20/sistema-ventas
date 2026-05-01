<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Listado de compras (UI v2) — ahora delegado a Livewire PurchasesIndex.
 * Este controller solo retorna la vista wrapper.
 */
class PurchaseV2Controller extends Controller
{
    public function index(Request $request)
    {
        return view('admin.v2.purchases.index');
    }
}
