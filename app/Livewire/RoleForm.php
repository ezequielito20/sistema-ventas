<?php

namespace App\Livewire;

use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RoleForm extends Component
{
    public ?int $roleId = null;

    public string $name = '';

    public function mount(?int $roleId = null): void
    {
        $this->roleId = $roleId;

        if ($this->roleId !== null) {
            Gate::authorize('roles.edit');
            $role = Role::query()
                ->where('company_id', Auth::user()->company_id)
                ->where('id', $this->roleId)
                ->firstOrFail();
            $this->name = $role->name;
        } else {
            Gate::authorize('roles.create');
        }
    }

    protected function rules(): array
    {
        $companyId = Auth::user()->company_id;

        $nameRules = [
            'required',
            'string',
            'max:255',
            'regex:/^[a-zA-Z0-9\s-]+$/',
        ];

        if ($this->roleId === null) {
            $nameRules[] = Rule::unique('roles', 'name')->where(function ($query) use ($companyId) {
                return $query->where('guard_name', 'web')
                    ->where('company_id', $companyId);
            });
        } else {
            $role = Role::query()
                ->where('company_id', $companyId)
                ->where('id', $this->roleId)
                ->firstOrFail();

            $nameRules[] = Rule::unique('roles', 'name')->ignore($role->id)->where(function ($query) use ($companyId) {
                return $query->where('guard_name', 'web')
                    ->where('company_id', $companyId);
            });
        }

        return [
            'name' => $nameRules,
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nombre del rol',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio',
            'name.string' => 'El nombre debe ser una cadena de texto',
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'name.unique' => 'Este nombre de rol ya existe',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios y guiones',
        ];
    }

    public function save(RoleService $roleService)
    {
        if ($this->roleId !== null) {
            Gate::authorize('roles.edit');
        } else {
            Gate::authorize('roles.create');
        }

        $validated = $this->validate();

        try {
            if ($this->roleId === null) {
                $roleService->createRole(Auth::user()->company_id, $validated['name']);

                session()->flash('message', 'Rol creado exitosamente');
                session()->flash('icons', 'success');
            } else {
                $role = Role::query()
                    ->where('company_id', Auth::user()->company_id)
                    ->where('id', $this->roleId)
                    ->firstOrFail();

                $roleService->updateRole($role, $validated['name']);

                session()->flash('message', 'Rol actualizado exitosamente');
                session()->flash('icons', 'success');
            }

            return $this->redirect(route('admin.roles.index'));
        } catch (\RuntimeException $e) {
            $this->addError('name', $e->getMessage());

            return null;
        } catch (\Throwable $e) {
            $this->addError('name', 'Error al guardar el rol: '.$e->getMessage());

            return null;
        }
    }

    public function render(): View
    {
        $isEdit = $this->roleId !== null;

        return view('livewire.role-form', [
            'isEdit' => $isEdit,
            'headingTitle' => $isEdit ? 'Editar rol' : 'Crear rol',
            'headingSubtitle' => $isEdit
                ? 'Modifica el nombre del rol.'
                : 'Define un nombre único para el nuevo rol.',
        ]);
    }
}
