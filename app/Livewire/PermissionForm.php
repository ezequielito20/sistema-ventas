<?php

namespace App\Livewire;

use App\Services\PermissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class PermissionForm extends Component
{
    public ?int $permissionId = null;

    public string $name = '';

    public function mount(?int $permissionId = null): void
    {
        $this->permissionId = $permissionId;

        if ($this->permissionId !== null) {
            Gate::authorize('permissions.edit');
            $permission = Permission::query()->where('id', $this->permissionId)->firstOrFail();
            $this->name = $permission->name;
        } else {
            Gate::authorize('permissions.create');
        }
    }

    protected function rules(): array
    {
        $service = app(PermissionService::class);

        if ($this->permissionId === null) {
            return $service->rulesForCreate();
        }

        $permission = Permission::query()->where('id', $this->permissionId)->firstOrFail();

        return $service->rulesForUpdate($permission);
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nombre del permiso',
        ];
    }

    protected function messages(): array
    {
        return app(PermissionService::class)->validationMessages();
    }

    public function save(PermissionService $permissionService)
    {
        if ($this->permissionId !== null) {
            Gate::authorize('permissions.edit');
        } else {
            Gate::authorize('permissions.create');
        }

        $validated = $this->validate();

        try {
            if ($this->permissionId === null) {
                $permissionService->createPermission($validated['name']);

                session()->flash('message', 'Permiso creado correctamente');
                session()->flash('icons', 'success');
            } else {
                $permission = Permission::query()->where('id', $this->permissionId)->firstOrFail();
                $permissionService->updatePermission($permission, $validated['name']);

                session()->flash('message', 'Permiso actualizado correctamente');
                session()->flash('icons', 'success');
            }

            return $this->redirect(route('admin.permissions.index'));
        } catch (\Throwable $e) {
            $this->addError('name', $e->getMessage());

            return null;
        }
    }

    public function render(): View
    {
        $isEdit = $this->permissionId !== null;

        return view('livewire.permission-form', [
            'isEdit' => $isEdit,
            'headingTitle' => $isEdit ? 'Editar permiso' : 'Crear permiso',
            'headingSubtitle' => $isEdit
                ? 'Modifica el nombre respetando el formato modulo.accion.'
                : 'Define un nombre único con formato modulo.accion (minúsculas).',
        ]);
    }
}
