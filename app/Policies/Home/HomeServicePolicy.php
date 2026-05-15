<?php

namespace App\Policies\Home;

use App\Models\Home\HomeService;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HomeServicePolicy
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
        return $user->can('home.finances.services');
    }

    public function create(User $user): bool
    {
        return $user->can('home.finances.services');
    }

    public function view(User $user, HomeService $service): bool
    {
        return $user->can('home.finances.services')
            && $user->company_id === $service->company_id;
    }

    public function update(User $user, HomeService $service): bool
    {
        return $user->can('home.finances.services')
            && $user->company_id === $service->company_id;
    }

    public function delete(User $user, HomeService $service): bool
    {
        return $user->can('home.finances.services')
            && $user->company_id === $service->company_id;
    }
}
