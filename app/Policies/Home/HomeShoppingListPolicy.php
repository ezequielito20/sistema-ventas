<?php

namespace App\Policies\Home;

use App\Models\Home\HomeShoppingList;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HomeShoppingListPolicy
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
        return $user->can('home.shopping_list.index');
    }

    public function create(User $user): bool
    {
        return $user->can('home.shopping_list.create');
    }

    public function view(User $user, HomeShoppingList $list): bool
    {
        return $user->can('home.shopping_list.index')
            && $user->company_id === $list->company_id;
    }

    public function update(User $user, HomeShoppingList $list): bool
    {
        return $user->can('home.shopping_list.edit')
            && $user->company_id === $list->company_id;
    }

    public function delete(User $user, HomeShoppingList $list): bool
    {
        return $user->can('home.shopping_list.destroy')
            && $user->company_id === $list->company_id;
    }
}
