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
     * Retornar el query builder de búsqueda SIN paginar.
     * Usado por toggleSelectAllCurrentPage() para obtener los IDs de la página actual.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchSalesQuery(int $companyId, array $filters = [])
    {
        $query = Sale::select(['id', 'sale_date', 'total_price', 'customer_id', 'company_id', 'note'])
            ->with([
                'customer:id,name,email,phone',
                'saleDetails:id,sale_id,product_id,quantity,unit_price,subtotal',
                'saleDetails.product:id,code,name,image,category_id',
                'saleDetails.product.category:id,name',
            ])
            ->where('company_id', $companyId);

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

        $dateFrom = $filters['dateFrom'] ?? '';
        if ($dateFrom !== '') {
            $query->whereDate('sale_date', '>=', $dateFrom);
        }

        $dateTo = $filters['dateTo'] ?? '';
        if ($dateTo !== '') {
            $query->whereDate('sale_date', '<=', $dateTo);
        }

        $amountMin = $filters['amountMin'] ?? '';
        if ($amountMin !== '' && is_numeric($amountMin)) {
            $query->where('total_price', '>=', (float) $amountMin);
        }

        $amountMax = $filters['amountMax'] ?? '';
        if ($amountMax !== '' && is_numeric($amountMax)) {
            $query->where('total_price', '<=', (float) $amountMax);
        }

        return $query->orderBy('sale_date', 'desc');
    }

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

            $customer = Customer::find($sale->customer_id);

            // Revertir pagos de deuda automáticos asociados a esta venta
            $autoPayments = DB::table('debt_payments')
                ->where('company_id', $companyId)
                ->where('customer_id', $sale->customer_id)
                ->where(function ($q) use ($sale) {
                    $q->where('notes', 'like', '%Venta Masiva #' . $sale->id . '%')
                      ->orWhere('notes', 'like', '%Venta #' . $sale->id . '%');
                })
                ->get();

            if ($customer) {
                foreach ($autoPayments as $payment) {
                    $customer->total_debt += (float) $payment->payment_amount;
                }

                // Revertir la deuda de la venta
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
                ->where(function ($q) use ($sale) {
                    $q->where('notes', 'like', '%Venta Masiva #' . $sale->id . '%')
                      ->orWhere('notes', 'like', '%Venta #' . $sale->id . '%');
                })
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

    // ─── VALIDATION ──────────────────────────────────────────

    /**
     * Reglas de validación para crear una venta.
     *
     * @return array<string, mixed>
     */
    public function rulesForCreate(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'sale_date' => ['required', 'date'],
            'sale_time' => ['required', 'date_format:H:i'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'in:fixed,percentage'],
            'general_discount_value' => ['nullable', 'numeric', 'min:0'],
            'general_discount_type' => ['nullable', 'in:fixed,percentage'],
            'note' => ['nullable', 'string', 'max:1000'],
            'already_paid' => ['required', 'boolean'],
        ];
    }

    /**
     * Reglas de validación para actualizar una venta.
     *
     * @return array<string, mixed>
     */
    public function rulesForUpdate(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'sale_date' => ['required', 'date'],
            'sale_time' => ['required', 'date_format:H:i'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'in:fixed,percentage'],
            'general_discount_value' => ['nullable', 'numeric', 'min:0'],
            'general_discount_type' => ['nullable', 'in:fixed,percentage'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Mensajes de validación en español.
     *
     * @return array<string, string>
     */
    public function validationMessages(): array
    {
        return [
            'customer_id.required' => 'Debe seleccionar un cliente.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'sale_date.required' => 'La fecha de venta es obligatoria.',
            'sale_time.required' => 'La hora de venta es obligatoria.',
            'items.required' => 'Debe agregar al menos un producto a la venta.',
            'items.min' => 'Debe agregar al menos un producto a la venta.',
            'items.*.product_id.required' => 'El producto es obligatorio.',
            'items.*.product_id.exists' => 'El producto seleccionado no existe.',
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
            'items.*.price.required' => 'El precio unitario es obligatorio.',
            'items.*.price.min' => 'El precio unitario debe ser mayor o igual a 0.',
        ];
    }

    // ─── CALCULATIONS ─────────────────────────────────────────

    /**
     * Calcula el subtotal (suma de precio × cantidad) antes de descuento general.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    public function calculateSubtotal(array $items): float
    {
        return round(
            collect($items)->sum(function ($item) {
                $price = (float) ($item['price'] ?? 0);
                $qty = (float) ($item['quantity'] ?? 0);
                return $price * $qty;
            }),
            2
        );
    }

    /**
     * Calcula el subtotal de un ítem (precio final × cantidad).
     */
    public function calculateItemSubtotal(array $item): float
    {
        $finalPrice = $this->calculateItemFinalPrice($item);
        return round($finalPrice * (float) ($item['quantity'] ?? 0), 2);
    }

    /**
     * Calcula el total final aplicando descuento general.
     */
    public function calculateTotalAmount(array $items, float $generalDiscountValue, string $generalDiscountType): float
    {
        $subtotal = $this->calculateSubtotal($items);

        if ($generalDiscountValue <= 0) {
            return $subtotal;
        }

        $discount = $generalDiscountType === 'percentage'
            ? $subtotal * ($generalDiscountValue / 100)
            : $generalDiscountValue;

        return round(max(0, $subtotal - $discount), 2);
    }

    /**
     * Calcula el precio final de un ítem aplicando su descuento individual.
     */
    public function calculateItemFinalPrice(array $item): float
    {
        $price = (float) ($item['price'] ?? 0);
        $discountValue = (float) ($item['discount_value'] ?? 0);
        $discountType = $item['discount_type'] ?? 'fixed';

        if ($discountValue <= 0) {
            return $price;
        }

        $discount = $discountType === 'percentage'
            ? $price * ($discountValue / 100)
            : $discountValue;

        return round(max(0, $price - $discount), 2);
    }

    // ─── CREATE / UPDATE ───────────────────────────────────────

    /**
     * Crear una venta con todos sus detalles.
     *
     * @param  array<string, mixed>  $data
     */
    public function createSale(array $data, int $companyId): Sale
    {
        $validated = \Illuminate\Support\Facades\Validator::make(
            $data,
            $this->rulesForCreate(),
            $this->validationMessages()
        )->validate();

        // 1. Verificar caja abierta
        $currentCashCount = CashCount::where('company_id', $companyId)
            ->whereNull('closing_date')
            ->first();

        if (! $currentCashCount) {
            throw new \RuntimeException('No hay una caja abierta. Debe abrir una caja antes de realizar ventas.');
        }

        // 2. Calcular totales
        $totalPrice = $this->calculateTotalAmount(
            $validated['items'],
            $validated['general_discount_value'] ?? 0,
            $validated['general_discount_type'] ?? 'fixed'
        );

        $subtotalBeforeDiscount = $this->calculateSubtotal($validated['items']);

        return DB::transaction(function () use ($validated, $companyId, $currentCashCount, $totalPrice, $subtotalBeforeDiscount) {
            // 3. Validar stock y bloquear productos
            $requestedQuantities = collect($validated['items'])
                ->groupBy('product_id')
                ->map(fn ($items) => $items->sum('quantity'));

            $productIds = $requestedQuantities->keys()->all();

            $products = Product::where('company_id', $companyId)
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($requestedQuantities as $productId => $requestedQty) {
                $product = $products->get((int) $productId);
                if (! $product) {
                    throw new \RuntimeException("Producto no encontrado o no pertenece a esta empresa (ID: {$productId}).");
                }
                if ((float) $requestedQty > (float) $product->stock) {
                    throw new \RuntimeException("Stock insuficiente para {$product->name}. Disponible: {$product->stock}, solicitado: {$requestedQty}.");
                }
            }

            // 4. Obtener cliente
            $customer = Customer::findOrFail($validated['customer_id']);

            // 5. Crear la venta
            $sale = Sale::create([
                'sale_date' => $validated['sale_date'] . ' ' . $validated['sale_time'],
                'total_price' => $totalPrice,
                'company_id' => $companyId,
                'customer_id' => $validated['customer_id'],
                'cash_count_id' => $currentCashCount->id,
                'note' => $validated['note'] ?? null,
                'general_discount_value' => $validated['general_discount_value'] ?? 0,
                'general_discount_type' => $validated['general_discount_type'] ?? 'fixed',
                'subtotal_before_discount' => $subtotalBeforeDiscount,
                'total_with_discount' => $totalPrice,
            ]);

            // 6. Crear detalles y reducir stock
            foreach ($validated['items'] as $item) {
                $product = $products->get((int) $item['product_id']);

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $this->calculateItemSubtotal($item),
                    'discount_value' => $item['discount_value'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'fixed',
                    'original_price' => $item['price'],
                    'final_price' => $this->calculateItemFinalPrice($item),
                ]);

                // Reducir stock
                $product->stock -= (float) $item['quantity'];
                $product->save();
            }

            // 7. Manejar already_paid: si pagó, registrar pago de deuda; si no, incrementar deuda
            if ($validated['already_paid']) {
                $previousDebt = $customer->total_debt;
                DB::table('debt_payments')->insert([
                    'company_id' => $companyId,
                    'customer_id' => $validated['customer_id'],
                    'previous_debt' => $previousDebt,
                    'payment_amount' => $totalPrice,
                    'remaining_debt' => $previousDebt,
                    'notes' => 'Pago automático registrado al crear la venta #' . $sale->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Incrementar deuda del cliente
                $customer->total_debt = $customer->total_debt + $totalPrice;
                $customer->save();
            }

            // 8. Registrar movimiento de caja (ingreso)
            $currentCashCount->movements()->create([
                'type' => 'income',
                'amount' => $totalPrice,
                'description' => 'Venta #' . $sale->id,
            ]);

            return $sale;
        });
    }

    /**
     * Actualizar una venta existente (edit).
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSale(int $saleId, array $data, int $companyId): Sale
    {
        $validated = \Illuminate\Support\Facades\Validator::make(
            $data,
            $this->rulesForUpdate(),
            $this->validationMessages()
        )->validate();

        return DB::transaction(function () use ($validated, $companyId, $saleId) {
            $sale = Sale::with('saleDetails')
                ->where('company_id', $companyId)
                ->findOrFail($saleId);

            // Guardar valores originales ANTES de cualquier modificación
            $originalTotalPrice = (float) $sale->total_price;
            $oldCustomerId = $sale->customer_id;
            $newCustomerId = (int) $validated['customer_id'];

            $totalPrice = $this->calculateTotalAmount(
                $validated['items'],
                $validated['general_discount_value'] ?? 0,
                $validated['general_discount_type'] ?? 'fixed'
            );

            $subtotalBeforeDiscount = $this->calculateSubtotal($validated['items']);

            // ================================================================
            // 1. REVERTIR STOCK (devolver todo lo que se vendió originalmente)
            // ================================================================
            foreach ($sale->saleDetails as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->stock += $detail->quantity;
                    $product->save();
                }
            }

            // ================================================================
            // 2. REVERTIR DEUDA Y PAGOS del cliente original
            // ================================================================
            // Buscar y eliminar pagos de deuda automáticos asociados a esta venta
            $autoPayments = DB::table('debt_payments')
                ->where('company_id', $companyId)
                ->where('customer_id', $oldCustomerId)
                ->where('notes', 'like', '%Venta Masiva #' . $sale->id . '%')
                ->orWhere(function ($q) use ($sale, $oldCustomerId, $companyId) {
                    $q->where('company_id', $companyId)
                      ->where('customer_id', $oldCustomerId)
                      ->where('notes', 'like', '%Venta #' . $sale->id . '%');
                })
                ->get();

            $oldCustomer = Customer::find($oldCustomerId);
            if ($oldCustomer) {
                // Revertir la deuda sumada por esta venta original
                $oldCustomer->total_debt = max(0, $oldCustomer->total_debt - $originalTotalPrice);

                // Revertir el efecto de los pagos automáticos (si los hubo)
                foreach ($autoPayments as $payment) {
                    // Si fue un pago total (no aumentó deuda), revertimos el pago sumando
                    // Si fue parcial, la lógica de create ya ajustó la deuda correctamente
                    // En ambos casos, revertimos el payment_amount de la deuda
                    $oldCustomer->total_debt += (float) $payment->payment_amount;
                }

                $oldCustomer->save();

                // Eliminar los pagos automáticos asociados a esta venta
                DB::table('debt_payments')
                    ->where('company_id', $companyId)
                    ->where('customer_id', $oldCustomerId)
                    ->where(function ($q) use ($sale) {
                        $q->where('notes', 'like', '%Venta Masiva #' . $sale->id . '%')
                          ->orWhere('notes', 'like', '%Venta #' . $sale->id . '%');
                    })
                    ->delete();
            }

            // ================================================================
            // 3. ACTUALIZAR LA VENTA
            // ================================================================
            $sale->sale_date = $validated['sale_date'] . ' ' . $validated['sale_time'];
            $sale->customer_id = $validated['customer_id'];
            $sale->total_price = $totalPrice;
            $sale->note = $validated['note'] ?? null;
            $sale->general_discount_value = $validated['general_discount_value'] ?? 0;
            $sale->general_discount_type = $validated['general_discount_type'] ?? 'fixed';
            $sale->subtotal_before_discount = $subtotalBeforeDiscount;
            $sale->total_with_discount = $totalPrice;
            $sale->save();

            // Eliminar detalles anteriores
            $sale->saleDetails()->delete();

            // ================================================================
            // 4. CREAR NUEVOS DETALLES Y DESCONTAR STOCK
            // ================================================================
            $requestedQuantities = collect($validated['items'])
                ->groupBy('product_id')
                ->map(fn ($items) => $items->sum('quantity'));

            $productIds = $requestedQuantities->keys()->all();

            $products = Product::where('company_id', $companyId)
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($validated['items'] as $item) {
                $product = $products->get((int) $item['product_id']);
                if (! $product) {
                    throw new \RuntimeException("Producto no encontrado o no pertenece a esta empresa (ID: {$item['product_id']}).");
                }

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $this->calculateItemSubtotal($item),
                    'discount_value' => $item['discount_value'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'fixed',
                    'original_price' => $item['price'],
                    'final_price' => $this->calculateItemFinalPrice($item),
                ]);

                $product->stock -= (float) $item['quantity'];
                $product->save();
            }

            // ================================================================
            // 5. APLICAR NUEVA DEUDA AL CLIENTE (puede ser el mismo o diferente)
            // ================================================================
            $targetCustomer = Customer::find($newCustomerId);
            if ($targetCustomer) {
                // Sumar la nueva deuda total
                $targetCustomer->total_debt += $totalPrice;
                $targetCustomer->save();
            }

            // ================================================================
            // 6. ACTUALIZAR MOVIMIENTO DE CAJA
            // ================================================================
            \App\Models\CashMovement::where('description', 'Venta #' . $sale->id)
                ->whereHas('cashCount', fn ($q) => $q->where('company_id', $companyId))
                ->update(['amount' => $totalPrice]);

            return $sale;
        });
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