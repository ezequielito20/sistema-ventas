<?php

namespace App\Services;

use App\Models\CompanyDeliveryMethod;
use App\Models\CompanyPaymentMethod;
use App\Models\Order;

/**
 * Eliminaciones masivas en los módulos de checkout del catálogo (zonas/franjas se eliminan en cascada vía FK).
 *
 * @return list<array{deleted: bool, id: int, name: string, reason?: string}>
 */
class CatalogCheckoutBulkDeleteService
{
    /**
     * @param  list<int|string>  $ids
     * @return list<array{deleted: bool, id: int, name: string, reason?: string}>
     */
    public function bulkDeletePaymentMethods(int $companyId, array $ids): array
    {
        $out = [];

        foreach ($ids as $rawId) {
            $id = (int) $rawId;

            $m = CompanyPaymentMethod::query()
                ->where('company_id', $companyId)
                ->whereKey($id)
                ->first();

            if (! $m) {
                $out[] = ['deleted' => false, 'id' => $id, 'name' => '#'.$id, 'reason' => 'No encontrado'];

                continue;
            }

            if (Order::query()->where('company_payment_method_id', $id)->exists()) {
                $out[] = ['deleted' => false, 'id' => $id, 'name' => $m->name, 'reason' => 'Hay pedidos asociados'];

                continue;
            }

            $name = $m->name;
            $m->delete();

            $out[] = ['deleted' => true, 'id' => $id, 'name' => $name];
        }

        return $out;
    }

    /**
     * @param  list<int|string>  $ids
     * @return list<array{deleted: bool, id: int, name: string, reason?: string}>
     */
    public function bulkDeleteDeliveryMethods(int $companyId, array $ids): array
    {
        $out = [];

        foreach ($ids as $rawId) {
            $id = (int) $rawId;

            $m = CompanyDeliveryMethod::query()
                ->where('company_id', $companyId)
                ->whereKey($id)
                ->first();

            if (! $m) {
                $out[] = ['deleted' => false, 'id' => $id, 'name' => '#'.$id, 'reason' => 'No encontrado'];

                continue;
            }

            if (Order::query()->where('company_delivery_method_id', $id)->exists()) {
                $out[] = ['deleted' => false, 'id' => $id, 'name' => $m->name, 'reason' => 'Hay pedidos asociados'];

                continue;
            }

            $name = $m->name;
            $m->delete();

            $out[] = ['deleted' => true, 'id' => $id, 'name' => $name];
        }

        return $out;
    }
}
