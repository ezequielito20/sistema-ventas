<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class UsersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $verificationStatus = '';

    public string $roleId = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public bool $showDetailModal = false;

    /** @var array<string, mixed>|null */
    public ?array $detailUser = null;

    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetName = '';

    public bool $selectionMode = false;

    /** @var array<int> */
    public array $selectedUserIds = [];

    public bool $showBulkDeleteModal = false;

    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'verificationStatus' => ['except' => ''],
        'roleId' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        Gate::authorize('users.index');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingVerificationStatus(): void
    {
        $this->resetPage();
    }

    public function updatingRoleId(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->verificationStatus = '';
        $this->roleId = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function updatingPage(): void
    {
        $this->selectedUserIds = [];
    }

    public function setPerPage(int $value): void
    {
        $allowed = [10, 25, 50, 100];
        if (! in_array($value, $allowed, true)) {
            $value = 10;
        }

        $this->perPage = $value;
        $this->resetPage();
    }

    protected function toast(string $message, string $type = 'success'): void
    {
        $titles = [
            'success' => 'Listo',
            'error' => 'Atención',
            'warning' => 'Atención',
            'info' => 'Información',
        ];
        $uiType = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
        $title = $titles[$uiType] ?? $titles['info'];
        $timeout = $uiType === 'error' ? 7200 : 4800;

        $options = json_encode([
            'type' => $uiType,
            'title' => $title,
            'timeout' => $timeout,
            'theme' => 'futuristic',
        ], JSON_THROW_ON_ERROR);

        $msg = json_encode($message, JSON_THROW_ON_ERROR);

        $this->js(
            'if (window.uiNotifications && typeof window.uiNotifications.showToast === "function") {'
            .'window.uiNotifications.showToast('.$msg.', '.$options.');}'
        );
    }

    public function openDetailModal(int $id): void
    {
        Gate::authorize('users.show');

        $user = User::query()
            ->with(['company:id,name', 'roles:id,name'])
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $this->detailUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'company_name' => $user->company?->name ?? 'N/A',
            'verified' => $user->email_verified_at !== null,
            'verified_at' => $user->email_verified_at?->format('d/m/Y H:i'),
            'created_at' => $user->created_at?->format('d/m/Y H:i'),
            'roles' => $user->roles->pluck('name')->values()->all(),
        ];

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailUser = null;
    }

    public function openDeleteModal(int $id): void
    {
        Gate::authorize('users.destroy');

        $user = User::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->first();

        if (! $user) {
            $this->toast('Usuario no encontrado.', 'error');

            return;
        }

        $this->deleteTargetId = $id;
        $this->deleteTargetName = $user->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetName = '';
    }

    public function toggleSelectionMode(): void
    {
        $this->selectionMode = ! $this->selectionMode;

        if (! $this->selectionMode) {
            $this->selectedUserIds = [];
            $this->closeBulkDeleteModal();
        }
    }

    public function toggleUserSelection(int $id): void
    {
        if (! $this->selectionMode) {
            return;
        }

        if (in_array($id, $this->selectedUserIds, true)) {
            $this->selectedUserIds = array_values(array_diff($this->selectedUserIds, [$id]));
        } else {
            $this->selectedUserIds[] = $id;
            $this->selectedUserIds = array_values(array_unique(array_map('intval', $this->selectedUserIds)));
        }
    }

    public function toggleSelectAllCurrentPage(): void
    {
        if (! $this->selectionMode) {
            return;
        }

        $pageIds = $this->usersQuery()
            ->paginate($this->perPage)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $allSelected = $pageIds !== [] && count(array_intersect($pageIds, $this->selectedUserIds)) === count($pageIds);

        if ($allSelected) {
            $this->selectedUserIds = array_values(array_diff($this->selectedUserIds, $pageIds));
        } else {
            $this->selectedUserIds = array_values(array_unique(array_merge($this->selectedUserIds, $pageIds)));
        }
    }

    public function openBulkDeleteModal(): void
    {
        Gate::authorize('users.destroy');

        if ($this->selectedUserIds === []) {
            $this->toast('Selecciona al menos un usuario para continuar.', 'warning');

            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function closeBulkDeleteModal(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function confirmDeleteUser(): void
    {
        if ($this->deleteTargetId === null) {
            return;
        }

        $id = $this->deleteTargetId;
        $this->closeDeleteModal();
        $this->deleteUser($id);
    }

    public function deleteUser(int $id): void
    {
        Gate::authorize('users.destroy');

        try {
            $user = User::query()
                ->where('company_id', Auth::user()->company_id)
                ->where('id', $id)
                ->with('roles')
                ->first();

            if (! $user) {
                $this->toast('Usuario no encontrado.', 'error');

                return;
            }

            $service = app(UserService::class);
            $guard = $service->deletionGuard($user, (int) Auth::id());
            if (! $guard['can_delete']) {
                $this->toast('No se pudo eliminar el usuario "'.$user->name.'": '.$guard['reason'].'.', 'error');

                return;
            }

            $result = $service->deleteUserWithResult($user, (int) Auth::id());

            $this->selectedUserIds = array_values(array_diff($this->selectedUserIds, [$id]));
            $this->toast('Usuario "'.$result['name'].'" eliminado correctamente.', 'success');
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->toast('Error al eliminar el usuario: '.$e->getMessage(), 'error');
        }
    }

    public function confirmBulkDelete(): void
    {
        Gate::authorize('users.destroy');

        if ($this->selectedUserIds === []) {
            $this->closeBulkDeleteModal();

            return;
        }

        try {
            $results = app(UserService::class)->bulkDeleteUsers(
                (int) Auth::user()->company_id,
                (int) Auth::id(),
                $this->selectedUserIds
            );

            $deleted = array_values(array_filter($results, fn ($result) => $result['deleted'] === true));
            $blocked = array_values(array_filter($results, fn ($result) => $result['deleted'] === false));

            $messages = [];

            if ($deleted !== []) {
                $messages[] = count($deleted).' usuario(s) eliminado(s)';
            }

            if ($blocked !== []) {
                $blockedSummary = collect($blocked)
                    ->take(4)
                    ->map(fn ($result) => $result['name'].': '.$result['reason'])
                    ->implode(' | ');

                if (count($blocked) > 4) {
                    $blockedSummary .= ' | y '.(count($blocked) - 4).' más';
                }

                $messages[] = 'No eliminados: '.$blockedSummary;
            }

            if ($messages === []) {
                $messages[] = 'No hubo cambios en los usuarios seleccionados.';
            }

            $this->closeBulkDeleteModal();
            $this->selectedUserIds = [];
            $this->selectionMode = false;
            $this->resetPage();

            $this->toast(
                implode('. ', $messages).'.',
                $blocked !== [] ? 'warning' : 'success'
            );
        } catch (\Throwable $e) {
            $this->closeBulkDeleteModal();
            $this->toast('Error al eliminar los usuarios seleccionados: '.$e->getMessage(), 'error');
        }
    }

    protected function usersQuery()
    {
        $query = User::query()
            ->with(['company:id,name', 'roles:id,name'])
            ->where('company_id', Auth::user()->company_id);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', '%'.$search.'%')
                    ->orWhere('email', 'ILIKE', '%'.$search.'%');
            });
        }

        if ($this->verificationStatus === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($this->verificationStatus === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        if ($this->roleId !== '') {
            $roleId = (int) $this->roleId;
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->orderBy('name');
    }

    public function render(UserService $userService): View
    {
        $companyId = (int) Auth::user()->company_id;
        $users = $this->usersQuery()->paginate($this->perPage);
        $currentPageUserIds = $users->pluck('id')->map(fn ($id) => (int) $id)->all();
        $allCurrentPageSelected = $currentPageUserIds !== []
            && count(array_intersect($currentPageUserIds, $this->selectedUserIds)) === count($currentPageUserIds);

        $permFlags = [
            'can_report' => Gate::allows('users.report'),
            'can_create' => Gate::allows('users.create'),
            'can_edit' => Gate::allows('users.edit'),
            'can_show' => Gate::allows('users.show'),
            'can_destroy' => Gate::allows('users.destroy'),
        ];

        return view('livewire.users-index', [
            'users' => $users,
            'stats' => $userService->statistics($companyId),
            'permFlags' => $permFlags,
            'roleOptions' => $userService->availableRoleOptions($companyId),
            'currentPageUserIds' => $currentPageUserIds,
            'allCurrentPageSelected' => $allCurrentPageSelected,
        ]);
    }
}
