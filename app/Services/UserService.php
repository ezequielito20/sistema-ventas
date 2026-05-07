<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
     * @return array<int, array{id:int,name:string}>
     */
    public function availableRoles(int $companyId): array
    {
        return Role::query()
            ->select(['id', 'name'])
            ->byCompany($companyId)
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])
            ->all();
    }

    public function companySummary(int $companyId): ?Company
    {
        return Company::query()
            ->select(['id', 'name'])
            ->find($companyId);
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForCreate(int $companyId): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
            'password_confirmation' => ['required', 'same:password'],
            'roleIds' => [
                'required',
                'array',
                'min:1',
            ],
            'roleIds.*' => [
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForUpdate(User $user, int $companyId, bool $changingPassword): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'roleIds' => [
                'required',
                'array',
                'min:1',
            ],
            'roleIds.*' => [
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
        ];

        if ($changingPassword) {
            $rules['password'] = [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ];
            $rules['password_confirmation'] = ['required', 'same:password'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function validationMessages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.',
            'password_confirmation.required' => 'Debes confirmar la contraseña.',
            'password_confirmation.same' => 'Las contraseñas no coinciden.',
            'roleIds.required' => 'Debes seleccionar al menos un rol.',
            'roleIds.min' => 'Debes seleccionar al menos un rol.',
            'roleIds.*.exists' => 'Uno de los roles seleccionados no es válido para tu empresa.',
        ];
    }

    public function createUser(int $companyId, array $validated): User
    {
        $roles = Role::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $validated['roleIds'])
            ->get();

        $user = User::create([
            'name' => trim((string) $validated['name']),
            'email' => strtolower((string) $validated['email']),
            'password' => Hash::make((string) $validated['password']),
            'company_id' => $companyId,
            'email_verified_at' => now(),
        ]);

        $user->syncRoles($roles);

        return $user;
    }

    public function updateUser(User $user, int $companyId, array $validated): void
    {
        if ($user->company_id !== $companyId) {
            throw new \RuntimeException('No tiene permisos para editar este usuario.');
        }

        $roles = Role::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $validated['roleIds'])
            ->get();

        $payload = [
            'name' => trim((string) $validated['name']),
            'email' => strtolower((string) $validated['email']),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make((string) $validated['password']);
        }

        $user->update($payload);
        $user->syncRoles($roles);
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
