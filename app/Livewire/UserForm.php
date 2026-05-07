<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class UserForm extends Component
{
    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    /** @var array<int> */
    public array $roleIds = [];

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(?int $userId = null): void
    {
        $this->userId = $userId;

        if ($this->userId !== null) {
            Gate::authorize('users.edit');

            $user = User::query()
                ->where('company_id', Auth::user()->company_id)
                ->with('roles:id,name')
                ->findOrFail($this->userId);

            $this->name = $user->name;
            $this->email = $user->email;
            $this->roleIds = $user->roles->pluck('id')->map(fn ($id) => (int) $id)->all();

            return;
        }

        Gate::authorize('users.create');
    }

    protected function rules(): array
    {
        $userService = app(UserService::class);
        $companyId = (int) Auth::user()->company_id;

        if ($this->userId === null) {
            return $userService->rulesForCreate($companyId);
        }

        $user = User::query()
            ->where('company_id', $companyId)
            ->findOrFail($this->userId);

        $changingPassword = $this->password !== '' || $this->password_confirmation !== '';

        return $userService->rulesForUpdate($user, $companyId, $changingPassword);
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'roleIds' => 'roles',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
        ];
    }

    protected function messages(): array
    {
        return app(UserService::class)->validationMessages();
    }

    public function saveAndBack(UserService $userService)
    {
        return $this->persist($userService, false);
    }

    public function saveAndCreateAnother(UserService $userService)
    {
        return $this->persist($userService, true);
    }

    protected function persist(UserService $userService, bool $createAnother)
    {
        if ($this->userId !== null) {
            Gate::authorize('users.edit');
        } else {
            Gate::authorize('users.create');
        }

        $validated = $this->validate();
        $companyId = (int) Auth::user()->company_id;

        try {
            if ($this->userId === null) {
                $userService->createUser($companyId, $validated);

                session()->flash(
                    'message',
                    $createAnother
                        ? 'Usuario creado correctamente. Puedes registrar otro desde este formulario.'
                        : 'Usuario creado correctamente'
                );
                session()->flash('icons', 'success');

                if ($createAnother) {
                    $this->reset(['name', 'email', 'roleIds', 'password', 'password_confirmation']);

                    return $this->redirect(route('admin.users.create'));
                }
            } else {
                $user = User::query()
                    ->where('company_id', $companyId)
                    ->findOrFail($this->userId);

                $userService->updateUser($user, $companyId, $validated);

                session()->flash('message', 'Usuario actualizado correctamente');
                session()->flash('icons', 'success');
            }

            return $this->redirect(route('admin.users.index'));
        } catch (\Throwable $e) {
            $this->addError('name', 'Error al guardar el usuario: '.$e->getMessage());

            return null;
        }
    }

    public function render(): View
    {
        $companyId = (int) Auth::user()->company_id;
        $isEdit = $this->userId !== null;
        $userService = app(UserService::class);

        return view('livewire.user-form', [
            'isEdit' => $isEdit,
            'headingTitle' => $isEdit ? 'Editar usuario' : 'Crear usuario',
            'headingSubtitle' => $isEdit
                ? 'Actualiza los datos base, roles y contraseña si es necesario.'
                : 'Registra un nuevo usuario para tu empresa con sus roles.',
            'company' => $userService->companySummary($companyId),
            'roleOptions' => $userService->availableRoles($companyId),
        ]);
    }
}
