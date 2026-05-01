<div class="space-y-6" wire:key="users-index-root">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Usuarios</h1>
                <p class="ui-panel__subtitle">Gestión centralizada de cuentas, verificación y roles.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($permFlags['can_report'])
                    <a
                        href="{{ route('admin.users.report') }}"
                        target="_blank"
                        rel="noopener"
                        class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                    >
                        <i class="fas fa-file-pdf"></i> Ver PDF
                    </a>
                @endif
                @if ($permFlags['can_create'])
                    <a
                        href="{{ route('admin.users.create') }}"
                        class="ui-btn ui-btn-primary text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                        wire:navigate
                    >
                        <i class="fas fa-user-plus"></i> Nuevo usuario
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-4">
        <x-ui.stat-card
            variant="info"
            icon="fas fa-users"
            trend="Total"
            label="Usuarios"
            :value="number_format($stats['total'])"
            meta="En tu empresa"
        />
        <x-ui.stat-card
            variant="success"
            icon="fas fa-user-check"
            trend="Verificación"
            label="Verificados"
            :value="number_format($stats['verified'])"
            meta="Con correo validado"
        />
        <x-ui.stat-card
            variant="warning"
            icon="fas fa-user-clock"
            trend="Pendientes"
            label="Sin verificar"
            :value="number_format($stats['pending'])"
            meta="Correo pendiente"
        />
        <x-ui.stat-card
            variant="danger"
            icon="fas fa-user-shield"
            trend="Acceso"
            label="Con roles"
            :value="number_format($stats['with_roles'])"
            meta="Roles asignados"
        />
    </div>

    <div class="ui-panel" x-data="{ showFilters: false }">
        <div class="ui-panel__header flex items-center justify-between gap-3">
            <div>
                <h2 class="ui-panel__title">Filtros</h2>
                <p class="ui-panel__subtitle">Búsqueda y segmentación de usuarios.</p>
            </div>
            <button
                type="button"
                class="ui-btn ui-btn-ghost text-sm"
                @click="showFilters = !showFilters"
                :aria-expanded="showFilters"
            >
                <i class="fas" :class="showFilters ? 'fa-sliders-h' : 'fa-filter'"></i>
                <span x-text="showFilters ? 'Ocultar filtros' : 'Filtros avanzados'"></span>
            </button>
        </div>
        <div class="ui-panel__body space-y-4" x-show="showFilters" x-transition>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-end xl:grid-cols-[10rem_10rem_9rem_9rem_auto]">
                <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Estado</label>
                        <select
                            wire:model.live="verificationStatus"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        >
                            <option value="">Todos</option>
                            <option value="verified">Verificados</option>
                            <option value="unverified">Pendientes</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Rol</label>
                        <select
                            wire:model.live="roleId"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        >
                            <option value="">Todos</option>
                            @foreach ($roleOptions as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Desde</label>
                        <input
                            type="date"
                            wire:model.live="dateFrom"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Hasta</label>
                        <input
                            type="date"
                            wire:model.live="dateTo"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        />
                    </div>
                    <div class="sm:col-span-2 xl:col-span-1">
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="ui-btn ui-btn-ghost w-full text-sm xl:w-auto"
                        >
                            <i class="fas fa-eraser"></i> Limpiar filtros
                        </button>
                    </div>
            </div>
        </div>
    </div>

    <div class="ui-panel overflow-hidden">
        <div class="ui-panel__header">
            <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="shrink-0">
                    <h2 class="ui-panel__title">Listado</h2>
                    <p class="ui-panel__subtitle">{{ $users->total() }} resultado(s) · Página {{ $users->currentPage() }} de {{ $users->lastPage() }}</p>
                </div>

                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto">
                    <div class="relative min-w-[16rem] flex-1 lg:min-w-[18rem]">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Buscar nombre o correo..."
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost text-sm" title="Limpiar búsqueda y filtros">
                            <i class="fas fa-eraser"></i>
                        </button>
                        @if ($permFlags['can_destroy'] && ! $users->isEmpty())
                            <button
                                type="button"
                                wire:click="toggleSelectionMode"
                                class="ui-btn {{ $selectionMode ? 'ui-btn-warning' : 'ui-btn-ghost' }} text-sm"
                            >
                                <i class="fas {{ $selectionMode ? 'fa-times-circle' : 'fa-check-square' }}"></i>
                                {{ $selectionMode ? 'Cancelar' : 'Seleccionar' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="ui-panel__body p-0">
            @if ($users->isEmpty())
                <p class="px-4 py-10 text-center text-sm text-slate-400">No hay usuarios que coincidan con los filtros.</p>
            @else
                @if ($selectionMode)
                    <div class="flex flex-col gap-3 border-b border-slate-700/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-white">{{ count($selectedUserIds) }} usuario(s) seleccionado(s)</p>
                            <p class="text-xs text-slate-400">
                                La selección aplica a la página actual. No se eliminarán administradores ni tu propia cuenta.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" wire:click="toggleSelectAllCurrentPage" class="ui-btn ui-btn-ghost text-sm">
                                <i class="fas {{ $allCurrentPageSelected ? 'fa-square-minus' : 'fa-square-check' }}"></i>
                                {{ $allCurrentPageSelected ? 'Limpiar página' : 'Seleccionar página' }}
                            </button>
                            <button
                                type="button"
                                wire:click="openBulkDeleteModal"
                                class="ui-btn ui-btn-danger text-sm"
                                @disabled(count($selectedUserIds) === 0)
                            >
                                <i class="fas fa-trash-alt"></i>
                                Eliminar seleccionados
                            </button>
                        </div>
                    </div>
                @endif

                <div class="hidden md:block">
                    <div class="ui-table-wrap border-0 rounded-none">
                        <table class="ui-table ui-table--nowrap-actions">
                            <thead>
                                <tr>
                                    @if ($selectionMode)
                                        <th class="w-12 text-center">
                                            <input
                                                type="checkbox"
                                                @checked($allCurrentPageSelected)
                                                wire:click="toggleSelectAllCurrentPage"
                                                class="rounded border-slate-500 bg-slate-900"
                                            />
                                        </th>
                                    @endif
                                    <th>Usuario</th>
                                    <th>Correo</th>
                                    <th class="text-center">Estado</th>
                                    <th>Roles</th>
                                    <th class="hidden xl:table-cell">Creado</th>
                                    <th class="text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr wire:key="user-row-{{ $user->id }}">
                                        @if ($selectionMode)
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    value="{{ $user->id }}"
                                                    @checked(in_array($user->id, $selectedUserIds, true))
                                                    wire:click="toggleUserSelection({{ $user->id }})"
                                                    class="rounded border-slate-500 bg-slate-900"
                                                />
                                            </td>
                                        @endif
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-cyan-500/15 text-sm font-semibold uppercase text-cyan-200">
                                                    {{ mb_substr($user->name, 0, 2) }}
                                                </div>
                                                <div>
                                                    <p class="font-medium text-white">{{ $user->name }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-sm text-slate-300">{{ $user->email }}</td>
                                        <td class="text-center">
                                            @if ($user->email_verified_at)
                                                <span class="ui-badge ui-badge-success">Verificado</span>
                                            @else
                                                <span class="ui-badge ui-badge-warning">Pendiente</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap gap-1.5">
                                                @forelse ($user->roles as $role)
                                                    <span class="ui-badge ui-badge-info">{{ $role->name }}</span>
                                                @empty
                                                    <span class="text-xs text-slate-500">Sin rol asignado</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="hidden text-sm text-slate-400 xl:table-cell">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-left">
                                            <div class="ui-icon-action-row flex flex-nowrap items-center justify-start gap-1.5 md:gap-2">
                                                @if ($permFlags['can_show'])
                                                    <button
                                                        type="button"
                                                        wire:click="openDetailModal({{ $user->id }})"
                                                        class="ui-icon-action ui-icon-action--info"
                                                        title="Ver detalle"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endif
                                                @if ($permFlags['can_edit'])
                                                    <a
                                                        href="{{ route('admin.users.edit', $user->id) }}"
                                                        class="ui-icon-action ui-icon-action--primary"
                                                        title="Editar"
                                                        wire:navigate
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                                @if ($permFlags['can_destroy'])
                                                    <button
                                                        type="button"
                                                        wire:click="openDeleteModal({{ $user->id }})"
                                                        class="ui-icon-action ui-icon-action--danger"
                                                        title="Eliminar"
                                                    >
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-3 p-4 md:hidden">
                    @foreach ($users as $user)
                        <div class="rounded-xl border border-slate-600/50 bg-slate-950/40 p-4" wire:key="user-card-{{ $user->id }}">
                            @if ($selectionMode)
                                <div class="mb-3 flex items-center justify-end">
                                    <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                                        <input
                                            type="checkbox"
                                            value="{{ $user->id }}"
                                            @checked(in_array($user->id, $selectedUserIds, true))
                                            wire:click="toggleUserSelection({{ $user->id }})"
                                            class="rounded border-slate-500 bg-slate-900"
                                        />
                                        Seleccionar
                                    </label>
                                </div>
                            @endif
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cyan-500/15 text-sm font-semibold uppercase text-cyan-200">
                                    {{ mb_substr($user->name, 0, 2) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-white">{{ $user->name }}</p>
                                    <p class="truncate text-sm text-slate-400">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($user->email_verified_at)
                                    <span class="ui-badge ui-badge-success">Verificado</span>
                                @else
                                    <span class="ui-badge ui-badge-warning">Pendiente</span>
                                @endif
                                @forelse ($user->roles as $role)
                                    <span class="ui-badge ui-badge-info">{{ $role->name }}</span>
                                @empty
                                    <span class="text-xs text-slate-500">Sin rol asignado</span>
                                @endforelse
                            </div>
                            <div class="ui-icon-action-row mt-3 flex flex-wrap items-center justify-start gap-2">
                                @if ($permFlags['can_show'])
                                    <button type="button" wire:click="openDetailModal({{ $user->id }})" class="ui-icon-action ui-icon-action--info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                                @if ($permFlags['can_edit'])
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="ui-icon-action ui-icon-action--primary" title="Editar" wire:navigate>
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if ($permFlags['can_destroy'])
                                    <button type="button" wire:click="openDeleteModal({{ $user->id }})" class="ui-icon-action ui-icon-action--danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($users->hasPages())
                <div class="border-t border-slate-700/50 px-4 py-3">
                    <x-ui.pagination :paginator="$users->onEachSide(1)" />
                </div>
            @endif
        </div>
    </div>

    @if ($showDetailModal && $detailUser)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" wire:click.self="closeDetailModal">
            <div class="ui-panel max-h-[90vh] w-full max-w-lg overflow-y-auto" @keydown.escape.window="$wire.closeDetailModal()">
                <div class="ui-panel__header">
                    <div>
                        <h3 class="ui-panel__title">{{ $detailUser['name'] }}</h3>
                        <p class="ui-panel__subtitle">{{ $detailUser['email'] }}</p>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="ui-btn ui-btn-ghost px-2 py-1 text-lg leading-none">&times;</button>
                </div>
                <div class="ui-panel__body space-y-4 text-sm text-slate-200">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Empresa</p>
                            <p class="font-medium">{{ $detailUser['company_name'] }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Estado</p>
                            <p class="font-medium">{{ $detailUser['verified'] ? 'Verificado' : 'Pendiente' }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Verificado el</p>
                            <p class="font-medium">{{ $detailUser['verified_at'] ?? 'No verificado' }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Creado</p>
                            <p class="font-medium">{{ $detailUser['created_at'] ?? 'N/D' }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Roles asignados</p>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($detailUser['roles'] as $roleName)
                                <span class="ui-badge ui-badge-info">{{ $roleName }}</span>
                            @empty
                                <span class="text-xs text-slate-500">Sin rol asignado</span>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="flex justify-end border-t border-slate-700/50 px-4 py-3">
                    <button type="button" wire:click="closeDetailModal" class="ui-btn ui-btn-primary text-sm">Cerrar</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showDeleteModal && $deleteTargetId)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center bg-[#020617]/90 p-4 backdrop-blur-md"
            wire:click.self="closeDeleteModal"
            x-data
            x-on:keydown.escape.window="$wire.closeDeleteModal()"
            aria-modal="true"
            role="dialog"
        >
            <div class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75),inset_0_1px_0_rgba(255,255,255,0.06)]">
                <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Eliminar este usuario?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se eliminará <span class="font-medium text-white">“{{ $deleteTargetName }}”</span>. No puedes eliminar administradores ni tu propia cuenta.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmDeleteUser" class="ui-btn ui-btn-danger text-sm">
                        <i class="fas fa-trash-alt mr-1.5"></i>
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showBulkDeleteModal)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center bg-[#020617]/90 p-4 backdrop-blur-md"
            wire:click.self="closeBulkDeleteModal"
            x-data
            x-on:keydown.escape.window="$wire.closeBulkDeleteModal()"
            aria-modal="true"
            role="dialog"
        >
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75),inset_0_1px_0_rgba(255,255,255,0.06)]">
                <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Eliminar usuarios seleccionados?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se intentará eliminar <span class="font-medium text-white">{{ count($selectedUserIds) }} usuario(s)</span>.
                                Los administradores o tu propia cuenta no podrán eliminarse.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeBulkDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmBulkDelete" class="ui-btn ui-btn-danger text-sm">
                        <i class="fas fa-trash-alt mr-1.5"></i>
                        Sí, eliminar seleccionados
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
