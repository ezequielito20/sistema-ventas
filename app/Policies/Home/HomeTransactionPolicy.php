<?php

namespace App\Policies\Home;

use App\Models\Home\HomeTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HomeTransactionPolicy
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
        return $user->can('home.finances.transactions');
    }

    public function create(User $user): bool
    {
        return $user->can('home.finances.transactions');
    }

    public function view(User $user, HomeTransaction $transaction): bool
    {
        return $user->can('home.finances.transactions')
            && $user->company_id === $transaction->company_id;
    }

    public function update(User $user, HomeTransaction $transaction): bool
    {
        return $user->can('home.finances.transactions')
            && $user->company_id === $transaction->company_id;
    }

    public function delete(User $user, HomeTransaction $transaction): bool
    {
        return $user->can('home.finances.transactions')
            && $user->company_id === $transaction->company_id;
    }
}
