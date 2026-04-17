<?php

namespace App\Services\Purchases;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\CashCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Consulta de listado de compras para la vista v2 (admin/v2/purchases).
 * El módulo legacy conserva su propia lógica en PurchaseController.
 */
class PurchaseIndexQueryService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Request $request, object $company, object $currency): array
    {
        $companyId = $company->id;

        $cashCount = CashCount::where('company_id', $company->id)
            ->whereNull('closing_date')
            ->first();

        $query = Purchase::select(['id', 'purchase_date', 'payment_receipt', 'total_price', 'company_id'])
            ->with([
                'details' => function ($query) {
                    $query->select(['id', 'purchase_id', 'product_id', 'supplier_id', 'quantity']);
                },
                'details.product' => function ($query) {
                    $query->select(['id', 'name', 'code', 'image']);
                },
            ])
            ->where('company_id', $companyId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_receipt', 'ILIKE', "%{$search}%")
                    ->orWhereRaw('purchase_date::text ILIKE ?', ["%{$search}%"]);

                if (is_numeric($search)) {
                    $q->orWhereRaw('total_price::text ILIKE ?', ["%{$search}%"]);
                }

                $q->orWhereHas('details.product', function ($q2) use ($search) {
                    $q2->where('name', 'ILIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('product_id')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('product_id', $request->input('product_id'));
            });
        }

        if ($request->filled('payment_status')) {
            $status = $request->input('payment_status');
            if ($status === 'completed') {
                $query->whereNotNull('payment_receipt');
            } elseif ($status === 'pending') {
                $query->whereNull('payment_receipt');
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('amount_min')) {
            $query->where('total_price', '>=', $request->input('amount_min'));
        }

        if ($request->filled('amount_max')) {
            $query->where('total_price', '<=', $request->input('amount_max'));
        }

        $allowedPerPage = [10, 15, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 15;
        }

        $purchases = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $permissions = [
            'can_show' => Gate::allows('purchases.show'),
            'can_edit' => Gate::allows('purchases.edit'),
            'can_destroy' => Gate::allows('purchases.destroy'),
            'can_create' => Gate::allows('purchases.create'),
            'can_report' => Gate::allows('purchases.report'),
        ];

        $totalPurchases = 0;
        $totalAmount = 0.0;
        $monthlyPurchases = 0;
        $pendingDeliveries = 0;

        if (! $request->ajax()) {
            $totalPurchases = DB::table('purchase_details')
                ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
                ->where('purchases.company_id', $companyId)
                ->distinct('purchase_details.product_id')
                ->count('purchase_details.product_id');

            $totalAmount = (float) DB::table('purchases')
                ->where('company_id', $companyId)
                ->sum('total_price');

            $monthlyPurchases = (int) DB::table('purchases')
                ->where('company_id', $companyId)
                ->whereYear('purchase_date', now()->year)
                ->whereMonth('purchase_date', now()->month)
                ->count();

            $pendingDeliveries = (int) DB::table('purchases')
                ->where('company_id', $companyId)
                ->whereNull('payment_receipt')
                ->count();
        }

        $products = collect();
        if (! $request->ajax()) {
            $products = Product::select(['id', 'name', 'code', 'category_id', 'company_id'])
                ->with(['category' => function ($query) {
                    $query->select(['id', 'name']);
                }])
                ->where('company_id', $companyId)
                ->orderBy('name')
                ->get();
        }

        return [
            'purchases' => $purchases,
            'currency' => $currency,
            'company' => $company,
            'totalPurchases' => $totalPurchases,
            'totalAmount' => $totalAmount,
            'monthlyPurchases' => $monthlyPurchases,
            'pendingDeliveries' => $pendingDeliveries,
            'cashCount' => $cashCount,
            'permissions' => $permissions,
            'products' => $products,
        ];
    }
}
