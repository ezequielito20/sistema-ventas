<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DebtPayment;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Eliminación masiva con las mismas reglas que {@see \App\Http\Controllers\CustomerController::destroy}.
 */
class CustomerBulkDeleteService
{
    /**
     * @return array{id: int, name: string, deleted: bool, reason: ?string}
     */
    public function deleteCustomerWithResult(Customer $customer, ?int $salesCount = null, ?int $paymentsCount = null): array
    {
        if ($salesCount === null) {
            $salesCount = $customer->sales()->count();
        }
        if ($paymentsCount === null) {
            $paymentsCount = (int) DebtPayment::where('customer_id', $customer->id)->count();
        }

        if ($salesCount > 0) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'deleted' => false,
                'reason' => 'tiene ventas asociadas',
            ];
        }

        if ($paymentsCount > 0) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'deleted' => false,
                'reason' => 'tiene pagos de deuda asociados',
            ];
        }

        try {
            DB::beginTransaction();
            $customer->delete();
            DB::commit();

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'deleted' => true,
                'reason' => null,
            ];
        } catch (QueryException $e) {
            DB::rollBack();

            $sqlState = $e->errorInfo[0] ?? '';
            if (in_array($sqlState, ['23000', '23503'], true)) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'deleted' => false,
                    'reason' => 'tiene registros asociados en el sistema',
                ];
            }

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * @param  array<int|string>  $customerIds
     * @return array<int, array{id: int, name: string, deleted: bool, reason: ?string}>
     */
    public function bulkDeleteCustomers(int $companyId, array $customerIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $customerIds)));

        if ($ids === []) {
            return [];
        }

        $customers = Customer::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $ids)
            ->withCount('sales')
            ->orderBy('name')
            ->get();

        $paymentCounts = DebtPayment::query()
            ->where('company_id', $companyId)
            ->whereIn('customer_id', $customers->pluck('id'))
            ->selectRaw('customer_id, COUNT(*) as c')
            ->groupBy('customer_id')
            ->pluck('c', 'customer_id');

        $results = [];

        foreach ($customers as $customer) {
            $payCount = (int) ($paymentCounts[$customer->id] ?? 0);
            $results[] = $this->deleteCustomerWithResult($customer, (int) $customer->sales_count, $payCount);
        }

        return $results;
    }
}
