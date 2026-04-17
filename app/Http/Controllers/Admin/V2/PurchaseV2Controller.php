<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use App\Services\Purchases\PurchaseIndexQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Listado de compras (UI v2). Las rutas legacy /purchases siguen usando PurchaseController.
 */
class PurchaseV2Controller extends Controller
{
    public $currencies;

    protected $company;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->company = Auth::user()->company;

            if ($this->company && $this->company->currency) {
                $this->currencies = DB::table('currencies')
                    ->select(['id', 'name', 'code', 'symbol', 'country_id'])
                    ->where('code', $this->company->currency)
                    ->first();
            }

            if (! $this->currencies) {
                $this->currencies = DB::table('currencies')
                    ->select(['id', 'name', 'code', 'symbol', 'country_id'])
                    ->where('code', 'USD')
                    ->first();
            }

            return $next($request);
        });
    }

    public function index(Request $request, PurchaseIndexQueryService $queryService)
    {
        try {
            $data = $queryService->build($request, $this->company, $this->currencies);

            if ($request->ajax()) {
                return view('admin.v2.purchases.partials.list', $data);
            }

            return view('admin.v2.purchases.index', $data);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('message', 'Error al cargar las compras')
                ->with('icon', 'error');
        }
    }
}
