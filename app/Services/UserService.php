<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    /**
     * @return array{total:int, verified:int, pending:int, with_roles:int}
     */
    public function statistics(int $companyId): array
    {
        $base = User::query()->where('company_id', $companyId);

        $total = (clone $base)->count();
        $verified = (clone $base)->whereNotNull('email_verified_at')->count();
        $pending = (clone $base)->whereNull('email_verified_at')->count();
        $withRoles = (clone $base)->whereHas('roles')->count();

        return [
            'total' => $total,
            'verified' => $verified,
            'pending' => $pending,
            'with_roles' => $withRoles,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function availableRoleOptions(int $companyId): array
    {
        return Role::query()
            ->byCompany($companyId)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array{can_delete: bool, reason: ?string}
     */
    public function deletionGuard(User $user, int $authUserId): array
    {
        if ($user->id === $authUserId) {
            return [
                'can_delete' => false,
                'reason' => 'No puede eliminarse a sí mismo',
            ];
        }

        if ($user->hasRole('admin')) {
            return [
                'can_delete' => false,
                'reason' => 'Es un usuario administrador',
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
        ];
    }

    /**
     * @return array{id:int,name:string,deleted:bool,reason:?string}
     */
    public function deleteUserWithResult(User $user, int $authUserId): array
    {
        $guard = $this->deletionGuard($user, $authUserId);

        if (! $guard['can_delete']) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'deleted' => false,
                'reason' => $guard['reason'],
            ];
        }

        $user->delete();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'deleted' => true,
            'reason' => null,
        ];
    }

    /**
     * @param  array<int>  $userIds
     * @return array<int, array{id:int,name:string,deleted:bool,reason:?string}>
     */
    public function bulkDeleteUsers(int $companyId, int $authUserId, array $userIds): array
    {
        /** @var Collection<int, User> $users */
        $users = User::query()
            ->where('company_id', $companyId)
            ->whereIn('id', array_map('intval', $userIds))
            ->with('roles')
            ->orderBy('name')
            ->get();

        $results = [];

        /** @var User $user */
        foreach ($users as $user) {
            $results[] = $this->deleteUserWithResult($user, $authUserId);
        }

        return $results;
    }
}
