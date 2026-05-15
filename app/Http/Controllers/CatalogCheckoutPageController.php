<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Support\CatalogAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogCheckoutPageController extends Controller
{
    public function show(Request $request, Company $company): View
    {
        CatalogAccess::assert($request, $company);

        return view('catalog.checkout', [
            'company' => $company,
        ]);
    }
}
