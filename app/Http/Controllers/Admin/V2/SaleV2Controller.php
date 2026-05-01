<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Listado de ventas (UI v2) — ahora delegado a Livewire SalesIndex.
 * Este controller solo retorna la vista wrapper.
 */
class SaleV2Controller extends Controller
{
    public function index(Request $request)
    {
        return view('admin.v2.sales.index');
    }
}