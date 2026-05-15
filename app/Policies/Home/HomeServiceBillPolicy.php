<?php

namespace App\Policies\Home;

use App\Models\Home\HomeServiceBill;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HomeServiceBillPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function index(User $user): bool
    {
        return $user->can('home.finances.bills');
    }

    public function create(User $user): bool
    {
        return $user->can('home.finances.bills');
    }

    public function view(User $user, HomeServiceBill $bill): bool
    {
        return $user->can('home.finances.bills')
            && $user->company_id === $bill->service->company_id;
    }

    public function update(User $user, HomeServiceBill $bill): bool
    {
        return $user->can('home.finances.bills')
            && $user->company_id === $bill->service->company_id;
    }

    public function delete(User $user, HomeServiceBill $bill): bool
    {
        return $user->can('home.finances.bills')
            && $user->company_id === $bill->service->company_id;
    }
}
