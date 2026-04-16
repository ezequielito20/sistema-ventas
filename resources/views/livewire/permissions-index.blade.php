<div class="space-y-6" wire:key="permissions-index-root">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Permisos</h1>
                <p class="ui-panel__subtitle">Catálogo base del sistema y asignación por empresa.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($permFlags['can_report'])
                    <a
                        href="{{ route('admin.permissions.report') }}"
                        target="_blank"
                        rel="noopener"
                        class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                    >
                        <i class="fas fa-file-pdf"></i> Ver PDF
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-4">
        <x-ui.stat-card
            variant="info"
            icon="fas fa-key"
            trend="Catálogo"
            label="Total"
            :value="number_format($stats['total'])"
            meta="Permisos registrados"
        />
        <x-ui.stat-card
            variant="success"
            icon="fas fa-check-circle"
            trend="Uso"
            label="Activos"
            :value="number_format($stats['active'])"
            meta="Con roles o usuarios"
        />
        <x-ui.stat-card
            variant="warning"
            icon="fas fa-user-shield"
            trend="Roles"
            label="Con permisos"
            :value="number_format($stats['roles_with_permissions'])"
            meta="Roles distintos"
        />
        <x-ui.stat-card
            variant="danger"
            icon="fas fa-exclamation-triangle"
            trend="Revisión"
            label="Sin usar"
            :value="number_format($stats['unused'])"
            meta="Sin roles ni usuarios"
        />
    </div>

    <div class="ui-panel" x-data="{ showFilters: false }">
        <div class="ui-panel__header flex items-center justify-between gap-3">
            <div>
                <h2 class="ui-panel__title">Filtros</h2>
                <p class="ui-panel__subtitle">Búsqueda y segmentación del catálogo.</p>
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
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-end xl:grid-cols-[minmax(0,1fr)_10rem_7rem_auto]">
                <div class="min-w-0">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Buscar</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                <i class="fas fa-search"></i>
                            </span>
                            <input
                                type="search"
                                wire:model.live.debounce.300ms="search"
                                placeholder="Nombre o guard..."
                                class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-10 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Módulo</label>
                        <select
                            wire:model.live="module"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        >
                            <option value="">Todos</option>
                            @foreach ($moduleOptions as $mod)
                                <option value="{{ $mod }}">{{ $mod }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Guard</label>
                        <select
                            wire:model.live="guard"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        >
                            <option value="">Todos</option>
                            <option value="web">web</option>
                        </select>
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
            <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="ui-panel__title">Listado</h2>
                    <p class="ui-panel__subtitle">
                        {{ $permissions->total() }} resultado(s) · Página {{ $permissions->currentPage() }} de {{ $permissions->lastPage() }}
                    </p>
                </div>
                @if ($permFlags['can_destroy'] && ! $permissions->isEmpty())
                    <div class="flex w-full flex-wrap items-center justify-end gap-2 sm:w-auto">
                        <button
                            type="button"
                            wire:click="toggleSelectionMode"
                            class="ui-btn {{ $selectionMode ? 'ui-btn-warning' : 'ui-btn-ghost' }} text-sm"
                        >
                            <i class="fas {{ $selectionMode ? 'fa-times-circle' : 'fa-check-square' }}"></i>
                            {{ $selectionMode ? 'Cancelar selección' : 'Seleccionar' }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
        <div class="ui-panel__body p-0">
            @if ($permissions->isEmpty())
                <p class="px-4 py-10 text-center text-sm text-slate-400">No hay permisos que coincidan con los filtros.</p>
            @else
                @if ($selectionMode)
                    <div class="flex flex-col gap-3 border-b border-slate-700/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-white">
                                {{ count($selectedPermissionIds) }} permiso(s) seleccionado(s)
                            </p>
                            <p class="text-xs text-slate-400">
                                La selección aplica a la página actual. Los permisos en uso por roles o usuarios no se eliminarán.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                wire:click="toggleSelectAllCurrentPage"
                                class="ui-btn ui-btn-ghost text-sm"
                            >
                                <i class="fas {{ $allCurrentPageSelected ? 'fa-square-minus' : 'fa-square-check' }}"></i>
                                {{ $allCurrentPageSelected ? 'Limpiar página' : 'Seleccionar página' }}
                            </button>
                            <button
                                type="button"
                                wire:click="openBulkDeleteModal"
                                class="ui-btn ui-btn-danger text-sm"
                                @disabled(count($selectedPermissionIds) === 0)
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
                                    <th>Permiso</th>
                                    <th class="hidden lg:table-cell">Etiqueta</th>
                                    <th class="text-center">Guard</th>
                                    <th class="text-center">Roles</th>
                                    <th class="text-center">Usuarios</th>
                                    <th class="hidden xl:table-cell">Creado</th>
                                    <th class="text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions as $permission)
                                    <tr wire:key="perm-row-{{ $permission->id }}">
                                        @if ($selectionMode)
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    value="{{ $permission->id }}"
                                                    @checked(in_array($permission->id, $selectedPermissionIds, true))
                                                    wire:click="togglePermissionSelection({{ $permission->id }})"
                                                    class="rounded border-slate-500 bg-slate-900"
                                                />
                                            </td>
                                        @endif
                                        <td class="font-mono text-sm text-white">{{ $permission->name }}</td>
                                        <td class="hidden max-w-xs truncate text-sm text-slate-300 lg:table-cell" title="{{ $permission->friendly_label }}">
                                            {{ $permission->friendly_label }}
                                        </td>
                                        <td class="text-center text-xs text-slate-300">{{ $permission->guard_name }}</td>
                                        <td class="text-center tabular-nums">{{ $permission->roles_count }}</td>
                                        <td class="text-center tabular-nums">{{ $permission->users_count }}</td>
                                        <td class="hidden text-sm text-slate-400 xl:table-cell">{{ $permission->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-left">
                                            <div class="ui-icon-action-row flex flex-nowrap items-center justify-start gap-1.5 md:gap-2">
                                                @if ($permFlags['can_show'])
                                                    <button
                                                        type="button"
                                                        wire:click="openDetailModal({{ $permission->id }})"
                                                        class="ui-icon-action ui-icon-action--info"
                                                        title="Ver detalle"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endif
                                                @if ($permFlags['can_edit'])
                                                    <a
                                                        href="{{ route('admin.permissions.edit', $permission->id) }}"
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
                                                        wire:click="openDeleteModal({{ $permission->id }})"
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
                    @foreach ($permissions as $permission)
                        <div class="rounded-xl border border-slate-600/50 bg-slate-950/40 p-4" wire:key="perm-card-{{ $permission->id }}">
                            @if ($selectionMode)
                                <div class="mb-3 flex items-center justify-end">
                                    <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                                        <input
                                            type="checkbox"
                                            value="{{ $permission->id }}"
                                            @checked(in_array($permission->id, $selectedPermissionIds, true))
                                            wire:click="togglePermissionSelection({{ $permission->id }})"
                                            class="rounded border-slate-500 bg-slate-900"
                                        />
                                        Seleccionar
                                    </label>
                                </div>
                            @endif
                            <p class="font-mono text-sm font-medium text-white">{{ $permission->name }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $permission->friendly_label }}</p>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-400">
                                <span><i class="fas fa-shield-alt mr-1"></i>{{ $permission->guard_name }}</span>
                                <span><i class="fas fa-user-shield mr-1"></i>{{ $permission->roles_count }} roles</span>
                                <span><i class="fas fa-users mr-1"></i>{{ $permission->users_count }} usuarios</span>
                            </div>
                            <div class="ui-icon-action-row mt-3 flex flex-wrap items-center justify-start gap-2">
                                @if ($permFlags['can_show'])
                                    <button
                                        type="button"
                                        wire:click="openDetailModal({{ $permission->id }})"
                                        class="ui-icon-action ui-icon-action--info"
                                        title="Ver"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                                @if ($permFlags['can_edit'])
                                    <a
                                        href="{{ route('admin.permissions.edit', $permission->id) }}"
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
                                        wire:click="openDeleteModal({{ $permission->id }})"
                                        class="ui-icon-action ui-icon-action--danger"
                                        title="Eliminar"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($permissions->hasPages())
                <div class="border-t border-slate-700/50 px-4 py-3">
                    <x-ui.pagination :paginator="$permissions->onEachSide(1)" />
                </div>
            @endif
        </div>
    </div>

    @if ($showDetailModal && $detailPermission)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            wire:click.self="closeDetailModal"
        >
            <div class="ui-panel max-h-[90vh] w-full max-w-lg overflow-y-auto" @keydown.escape.window="$wire.closeDetailModal()">
                <div class="ui-panel__header">
                    <div>
                        <h3 class="ui-panel__title">{{ $detailPermission['label'] }}</h3>
                        <p class="ui-panel__subtitle font-mono text-xs text-slate-400">{{ $detailPermission['name'] }}</p>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="ui-btn ui-btn-ghost px-2 py-1 text-lg leading-none">&times;</button>
                </div>
                <div class="ui-panel__body space-y-4 text-sm text-slate-200">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Guard</p>
                            <p class="font-medium">{{ $detailPermission['guard_name'] }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Roles asignados</p>
                            <p class="font-medium tabular-nums">{{ $detailPermission['roles_count'] }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Usuarios (vía roles)</p>
                            <p class="font-medium tabular-nums">{{ $detailPermission['users_count'] }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Creado</p>
                            <p class="font-medium">{{ $detailPermission['created_at'] }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3 sm:col-span-2">
                            <p class="text-xs text-slate-500">Actualizado</p>
                            <p class="font-medium">{{ $detailPermission['updated_at'] }}</p>
                        </div>
                    </div>
                    @if (! empty($detailPermission['roles']))
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Roles con este permiso</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach (array_slice($detailPermission['roles'], 0, 24) as $rname)
                                    <span class="ui-badge ui-badge-success">{{ $rname }}</span>
                                @endforeach
                                @if (count($detailPermission['roles']) > 24)
                                    <span class="text-xs text-slate-500">+{{ count($detailPermission['roles']) - 24 }} más</span>
                                @endif
                            </div>
                        </div>
                    @endif
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
            <div
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75),inset_0_1px_0_rgba(255,255,255,0.06)]"
                wire:key="delete-perm-modal-{{ $deleteTargetId }}"
            >
                <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200"
                        >
                            <i class="fas fa-trash-alt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Eliminar este permiso?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se eliminará <span class="font-mono font-medium text-white">{{ $deleteTargetName }}</span>. Solo puede hacerse si no está asignado a roles ni usuarios.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmDeletePermission" class="ui-btn ui-btn-danger text-sm">
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
                            <h3 class="text-base font-semibold text-white">¿Eliminar permisos seleccionados?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se intentará eliminar <span class="font-medium text-white">{{ count($selectedPermissionIds) }} permiso(s)</span>.
                                Los permisos asignados a roles o usuarios no podrán eliminarse.
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
