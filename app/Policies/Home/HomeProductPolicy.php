<?php

namespace App\Policies\Home;

use App\Models\Home\HomeProduct;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HomeProductPolicy
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
        return $user->can('home.inventory.index');
    }

    public function create(User $user): bool
    {
        return $user->can('home.inventory.create');
    }

    public function view(User $user, HomeProduct $product): bool
    {
        return $user->can('home.inventory.show')
            && $user->company_id === $product->company_id;
    }

    public function update(User $user, HomeProduct $product): bool
    {
        return $user->can('home.inventory.edit')
            && $user->company_id === $product->company_id;
    }

    public function delete(User $user, HomeProduct $product): bool
    {
        return $user->can('home.inventory.destroy')
            && $user->company_id === $product->company_id;
    }
}
