<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Services\PlanEntitlementService;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return app(PlanEntitlementService::class)->tenantUserMayBrowseOrdersConsole($user);
    }

    public function view(User $user, Order $order): bool
    {
        if ((int) $user->company_id !== (int) $order->company_id) {
            return false;
        }

        return app(PlanEntitlementService::class)->tenantUserMayBrowseOrdersConsole($user);
    }

    public function update(User $user, Order $order): bool
    {
        return $this->view($user, $order) && $user->can('orders.update');
    }

    public function cancel(User $user, Order $order): bool
    {
        return $this->view($user, $order) && $user->can('orders.cancel');
    }
}
