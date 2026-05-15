<?php

namespace App\Policies\Home;

use App\Models\Home\HomeBankConnection;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HomeBankConnectionPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('home.bank.view');
    }

    public function connect(User $user): bool
    {
        return $user->can('home.bank.connect');
    }

    public function view(User $user, HomeBankConnection $connection): bool
    {
        return $user->can('home.bank.view')
            && $user->company_id === $connection->company_id;
    }

    public function disconnect(User $user, HomeBankConnection $connection): bool
    {
        return $user->can('home.bank.disconnect')
            && $user->company_id === $connection->company_id;
    }
}
