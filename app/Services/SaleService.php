<?php

namespace App\Services;

use App\Models\CashCount;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleService
{
    // ─── STATS ───────────────────────────────────────────────

    /**
     * Estadísticas desde la apertura de la caja actual.
     *
     * @return array{totalSales: float, totalProfit: float, salesCount: int, productsQty: int, averageTicket: float}
     */
    public function getStatsSinceCashOpen(int $companyId): array
    {
        $cashCount = $this->getCurrentCashCount($companyId);

        if (! $cashCount) {
            return [
                'totalSales' => 0,
                'totalProfit' => 0,
                'salesCount' => 0,
                'productsQty' => 0,
                'averageTicket' => 0,
            ];
        }

        $stats = DB::table('sales')
            ->where('company_id', $companyId)
            ->where('sale_date', '>=', $cashCount->opening_date)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_price), 0) as total')
            ->first();

        $totalSales = (float) ($stats->total ?? 0);
        $salesCount = (int) ($stats->count ?? 0);

        $cogs = $this->sumMerchandiseCostForCompanySalesFrom($companyId, $cashCount->opening_date);
        $collected = $this->sumDebtPaymentsCollectedSince($companyId, $cashCount->opening_date);
        $totalProfit = $collected - $cogs;

        $productsQty = (int) DB::table('sale_details as sd')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->where('s.company_id', $companyId)
            ->where('s.sale_date', '>=', $cashCount->opening_date)
            ->sum('sd.quantity');

        $averageTicket = $salesCount > 0 ? $totalSales / $salesCount : 0;

        return [
            'totalSales' => $totalSales,
            'totalProfit' => $totalProfit,
            'salesCount' => $salesCount,
            'productsQty' => $productsQty,
            'averageTicket' => round($averageTicket, 2),
        ];
    }

    /**
     * Estadísticas de la semana actual.
     *
     * @return array{totalSales: float, totalProfit: float, salesCount: int, productsQty: int}
     */
    public function getStatsThisWeek(int $companyId): array
    {
        $startOfWeek = Carbon::now()->copy()->startOfWeek();
        $endOfWeek = Carbon::now()->copy()->endOfWeek();

        $stats = DB::table('sales')
            ->where('company_id', $companyId)
            ->whereBetween('sale_date', [$startOfWeek, $endOfWeek])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_price), 0) as total')
            ->first();

        $totalSales = (float) ($stats->total ?? 0);
        $salesCount = (int) ($stats->count ?? 0);

        $cogs = $this->sumMerchandiseCostForCompanySalesBetween($companyId, $startOfWeek, $endOfWeek);
        $collected = $this->sumDebtPaymentsCollectedBetween($companyId, $startOfWeek, $endOfWeek);
        $totalProfit = $collected - $cogs;

        $productsQty = (int) DB::table('sale_details as sd')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->where('s.company_id', $companyId)
            ->whereBetween('s.sale_date', [$startOfWeek, $endOfWeek])
            ->sum('sd.quantity');

        return [
            'totalSales' => $totalSales,
            'totalProfit' => $totalProfit,
            'salesCount' => $salesCount,
            'productsQty' => $productsQty,
        ];
    }

    /**
     * Estadísticas del día de hoy.
     *
     * @return array{totalSales: float, totalProfit: float, salesCount: int, productsQty: int}
     */
    public function getStatsToday(int $companyId): array
    {
        $startOfToday = Carbon::today();
        $endOfToday = Carbon::today()->copy()->endOfDay();

        $stats = DB::table('sales')
            ->where('company_id', $companyId)
            ->where('sale_date', '>=', $startOfToday)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_price), 0) as total')
            ->first();

        $totalSales = (float) ($stats->total ?? 0);
        $salesCount = (int) ($stats->count ?? 0);

        $cogs = $this->sumMerchandiseCostForCompanySalesBetween($companyId, $startOfToday, $endOfToday);
        $collected = $this->sumDebtPaymentsCollectedOnDate($companyId, Carbon::today());
        $totalProfit = $collected - $cogs;

        $productsQty = (int) DB::table('sale_details as sd')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->where('s.company_id', $companyId)
            ->where('s.sale_date', '>=', $startOfToday)
            ->sum('sd.quantity');

        return [
            'totalSales' => $totalSales,
            'totalProfit' => $totalProfit,
            'salesCount' => $salesCount,
            'productsQty' => $productsQty,
        ];
    }

    /**
     * Porcentajes de la semana vs. desde la apertura de caja.
     *
     * @return array{salesPercentage: float, profitPercentage: float, salesCountPercentage: float}
     */
    public function getWeekPercentages(int $companyId): array
    {
        $cashCount = $this->getCurrentCashCount($companyId);

        if (! $cashCount) {
            return [
                'salesPercentage' => 0,
                'profitPercentage' => 0,
                'salesCountPercentage' => 0,
            ];
        }

        $sinceCashOpen = $this->getStatsSinceCashOpen($companyId);
        $thisWeek = $this->getStatsThisWeek($companyId);

        $salesPercentage = $sinceCashOpen['totalSales'] > 0
            ? round(($thisWeek['totalSales'] / $sinceCashOpen['totalSales']) * 100, 1)
            : 0;

        $profitPercentage = abs($sinceCashOpen['totalProfit']) > 0.00001
            ? round(($thisWeek['totalProfit'] / $sinceCashOpen['totalProfit']) * 100, 1)
            : 0;

        $salesCountPercentage = $sinceCashOpen['salesCount'] > 0
            ? round(($thisWeek['salesCount'] / $sinceCashOpen['salesCount']) * 100, 1)
            : 0;

        return [
            'salesPercentage' => $salesPercentage,
            'profitPercentage' => $profitPercentage,
            'salesCountPercentage' => $salesCountPercentage,
        ];
    }

    // ─── DETAIL ─────────────────────────────────────────────

    /**
     * Obtener una venta con detalles para el modal.
     */
    public function getSaleDetails(int $saleId, int $companyId): ?Sale
    {
        return Sale::with(['customer:id,name,email,phone', 'saleDetails.product.category'])
            ->where('company_id', $companyId)
            ->where('id', $saleId)
            ->first();
    }

    // ─── SEARCH ──────────────────────────────────────────────

    /**
     * Buscar ventas con filtros y paginación.
     */
    public function searchSales(int $companyId, array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Sale::select(['id', 'sale_date', 'total_price', 'customer_id', 'company_id', 'note'])
            ->with([
                'customer:id,name,email,phone',
                'saleDetails:id,sale_id,product_id,quantity,unit_price,subtotal',
                'saleDetails.product:id,code,name,image,category_id',
                'saleDetails.product.category:id,name',
            ])
            ->where('company_id', $companyId);

        // Search term
        $search = $filters['search'] ?? '';
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                    ->orWhereRaw("CAST(total_price AS TEXT) ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("CAST(sale_date AS TEXT) ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(sale_date, 'DD/MM/YY') ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(sale_date, 'DD/MM/YYYY') ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(sale_date, 'DD-MM-YY') ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(sale_date, 'DD-MM-YYYY') ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(sale_date, 'DD.MM.YY') ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(sale_date, 'DD.MM.YYYY') ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(sale_date, 'DD/MM') ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(sale_date, 'DD-MM') ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(sale_date, 'DD.MM') ILIKE ?", ["%{$search}%"])
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->whereRaw('name ILIKE ?', ["%{$search}%"])
                            ->orWhereRaw('email ILIKE ?', ["%{$search}%"])
                            ->orWhereRaw('phone ILIKE ?', ["%{$search}%"]);
                    })
                    ->orWhereHas('saleDetails.product', function ($pq) use ($search) {
                        $pq->whereRaw('code ILIKE ?', ["%{$search}%"])
                            ->orWhereRaw('name ILIKE ?', ["%{$search}%"])
                            ->orWhereHas('category', function ($catq) use ($search) {
                                $catq->whereRaw('name ILIKE ?', ["%{$search}%"]);
                            });
                    });
            });
        }

        // Date range filters
        $dateFrom = $filters['dateFrom'] ?? '';
        if ($dateFrom !== '') {
            $query->whereDate('sale_date', '>=', $dateFrom);
        }

        $dateTo = $filters['dateTo'] ?? '';
        if ($dateTo !== '') {
            $query->whereDate('sale_date', '<=', $dateTo);
        }

        // Amount range filters
        $amountMin = $filters['amountMin'] ?? '';
        if ($amountMin !== '' && is_numeric($amountMin)) {
            $query->where('total_price', '>=', (float) $amountMin);
        }

        $amountMax = $filters['amountMax'] ?? '';
        if ($amountMax !== '' && is_numeric($amountMax)) {
            $query->where('total_price', '<=', (float) $amountMax);
        }

        return $query->orderBy('sale_date', 'desc')->paginate($perPage);
    }

    // ─── DELETE ──────────────────────────────────────────────

    /**
     * Eliminar una venta (revertir stock, deuda y movimientos de caja).
     *
     * @return array{success: bool, message: string, type: string}
     */
    public function deleteSale(int $saleId, int $companyId): array
    {
        $sale = Sale::where('company_id', $companyId)->find($saleId);

        if (! $sale) {
            return [
                'success' => false,
                'message' => 'Venta no encontrada.',
                'type' => 'error',
            ];
        }

        return DB::transaction(function () use ($companyId, $sale) {
            // Verificar pagos de deuda posteriores a la venta
            $debtPayments = DB::table('debt_payments')
                ->where('customer_id', $sale->customer_id)
                ->where('company_id', $companyId)
                ->where('created_at', '>', $sale->sale_date)
                ->get();

            if ($debtPayments->count() > 0) {
                $totalPaid = $debtPayments->sum('payment_amount');
                $customerName = $sale->customer->name ?? 'Cliente';

                return [
                    'success' => false,
                    'message' => "No se puede eliminar: el cliente {$customerName} tiene {$debtPayments->count()} pago(s) de deuda posterior(es) por \$" . number_format((float) $totalPaid, 2) . ". Elimine los pagos primero.",
                    'type' => 'warning',
                ];
            }

            // Revertir deuda del cliente
            $customer = Customer::find($sale->customer_id);
            if ($customer) {
                $customer->total_debt = max(0, $customer->total_debt - $sale->total_price);
                $customer->save();
            }

            // Restaurar stock
            foreach ($sale->saleDetails as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->stock += $detail->quantity;
                    $product->save();
                }
            }

            // Eliminar movimientos de caja asociados
            \App\Models\CashMovement::where('description', 'Venta #' . $sale->id)
                ->whereHas('cashCount', fn ($q) => $q->where('company_id', $companyId))
                ->delete();

            // Eliminar pagos de deuda de esta venta (nota automática)
            DB::table('debt_payments')
                ->where('company_id', $companyId)
                ->where('customer_id', $sale->customer_id)
                ->where('notes', 'LIKE', '%Venta #' . $sale->id . '%')
                ->delete();

            $sale->delete();

            return [
                'success' => true,
                'message' => '¡Venta eliminada exitosamente!',
                'type' => 'success',
            ];
        });
    }

    /**
     * Borrado masivo de ventas.
     *
     * @param  array<int>  $ids
     * @return array<int, array{id:int, name:string, deleted:bool, reason:?string}>
     */
    public function bulkDeleteSales(array $ids, int $companyId): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $sales = Sale::where('company_id', $companyId)
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        $results = [];
        foreach ($sales as $sale) {
            $result = $this->deleteSale($sale->id, $companyId);
            $results[] = [
                'id' => $sale->id,
                'name' => 'Venta #' . $sale->id,
                'deleted' => $result['success'],
                'reason' => $result['success'] ? null : $result['message'],
            ];
        }

        return $results;
    }

    // ─── PRIVATE HELPERS ─────────────────────────────────────

    /**
     * Costo de mercancía vendida (COGS) en un rango de fechas.
     */
    private function sumMerchandiseCostForCompanySalesBetween(int $companyId, Carbon $from, Carbon $to): float
    {
        $sum = DB::table('sale_details as sd')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->join('products as p', 'sd.product_id', '=', 'p.id')
            ->where('s.company_id', $companyId)
            ->where('s.sale_date', '>=', $from)
            ->where('s.sale_date', '<=', $to)
            ->sum(DB::raw('sd.quantity * COALESCE(p.purchase_price, 0)'));

        return (float) $sum;
    }

    /**
     * Cobros (debt_payments) en un rango de fechas.
     */
    private function sumDebtPaymentsCollectedBetween(int $companyId, Carbon $from, Carbon $to): float
    {
        $sum = DB::table('debt_payments')
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$from, $to])
            ->sum('payment_amount');

        return (float) $sum;
    }

    /**
     * Cobros en una fecha concreta.
     */
    private function sumDebtPaymentsCollectedOnDate(int $companyId, Carbon $date): float
    {
        $sum = DB::table('debt_payments')
            ->where('company_id', $companyId)
            ->whereDate('created_at', $date->toDateString())
            ->sum('payment_amount');

        return (float) $sum;
    }

    /**
     * Cobros desde una fecha/hora (apertura de arqueo).
     */
    private function sumDebtPaymentsCollectedSince(int $companyId, $since): float
    {
        $sum = DB::table('debt_payments')
            ->where('company_id', $companyId)
            ->where('created_at', '>=', $since)
            ->sum('payment_amount');

        return (float) $sum;
    }

    /**
     * COGS desde una fecha/hora (apertura de arqueo).
     */
    private function sumMerchandiseCostForCompanySalesFrom(int $companyId, $since): float
    {
        $sum = DB::table('sale_details as sd')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->join('products as p', 'sd.product_id', '=', 'p.id')
            ->where('s.company_id', $companyId)
            ->where('s.sale_date', '>=', $since)
            ->sum(DB::raw('sd.quantity * COALESCE(p.purchase_price, 0)'));

        return (float) $sum;
    }

    /**
     * Obtener arqueo de caja abierto (closing_date null).
     */
    private function getCurrentCashCount(int $companyId): ?object
    {
        return CashCount::select('id', 'opening_date')
            ->where('company_id', $companyId)
            ->whereNull('closing_date')
            ->first();
    }
}