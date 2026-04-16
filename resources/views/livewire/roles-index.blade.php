<div class="space-y-6" wire:key="roles-index-root">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Roles</h1>
                <p class="ui-panel__subtitle">Listado y permisos en tiempo real.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($permFlags['can_report'])
                    <a
                        href="{{ route('admin.roles.report') }}"
                        target="_blank"
                        rel="noopener"
                        class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                    >
                        <i class="fas fa-file-pdf"></i> Ver PDF
                    </a>
                @endif
                @if ($permFlags['can_create'])
                    <a
                        href="{{ route('admin.roles.create') }}"
                        class="ui-btn ui-btn-primary text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                        wire:navigate
                    >
                        <i class="fas fa-plus"></i> Nuevo rol
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-4">
        <x-ui.stat-card
            variant="info"
            icon="fas fa-user-shield"
            trend="Total"
            label="Roles"
            :value="number_format($stats['roles_total'])"
            meta="En tu empresa"
        />
        <x-ui.stat-card
            variant="success"
            icon="fas fa-users"
            trend="Empresa"
            label="Usuarios"
            :value="number_format($stats['users_company'])"
            meta="Cuentas registradas"
        />
        <x-ui.stat-card
            variant="warning"
            icon="fas fa-key"
            trend="Catálogo"
            label="Permisos"
            :value="number_format($stats['permissions_total'])"
            meta="Disponibles en el sistema"
        />
        <x-ui.stat-card
            variant="danger"
            icon="fas fa-shield-alt"
            trend="Base"
            label="Roles de sistema"
            :value="number_format($stats['system_roles'])"
            meta="admin / user / superadmin"
        />
    </div>

    <div class="ui-panel">
        <div class="ui-panel__body space-y-4">
            {{-- flex + nowrap: una sola fila (sin depender de grid arbitrario en el CSS compilado) --}}
            <div class="flex w-full min-w-0 flex-row flex-nowrap items-end gap-3 sm:gap-4">
                <div class="min-w-0 flex-1 basis-0">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Buscar</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fas fa-search"></i>
                        </span>
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Nombre del rol..."
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-10 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        />
                    </div>
                </div>
                <div class="w-36 shrink-0 sm:w-44">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Tipo</label>
                    <select
                        wire:model.live="role_type"
                        class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    >
                        <option value="">Todos</option>
                        <option value="system">Sistema</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="ui-panel overflow-hidden">
        <div class="ui-panel__header">
            <div>
                <h2 class="ui-panel__title">Listado</h2>
                <p class="ui-panel__subtitle">{{ $roles->total() }} resultado(s) · Página {{ $roles->currentPage() }} de {{ $roles->lastPage() }}</p>
            </div>
        </div>
        <div class="ui-panel__body p-0">
            @if ($roles->isEmpty())
                <p class="px-4 py-10 text-center text-sm text-slate-400">No hay roles que coincidan con los filtros.</p>
            @else
                <div class="hidden md:block">
                    <div class="ui-table-wrap border-0 rounded-none">
                        <table class="ui-table ui-table--nowrap-actions">
                            <thead>
                                <tr>
                                    <th>Rol</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Usuarios</th>
                                    <th class="text-center">Permisos</th>
                                    <th class="text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    @php
                                        $isSys = in_array($role->name, ['admin', 'user', 'superadmin'], true);
                                    @endphp
                                    <tr wire:key="role-row-{{ $role->id }}">
                                        <td class="font-medium text-white">{{ $role->name }}</td>
                                        <td>
                                            @if ($isSys)
                                                <span class="ui-badge ui-badge-warning">Sistema</span>
                                            @else
                                                <span class="ui-badge ui-badge-success">Personalizado</span>
                                            @endif
                                        </td>
                                        <td class="text-center tabular-nums">{{ $role->users_count }}</td>
                                        <td class="text-center tabular-nums">{{ $role->permissions_count }}</td>
                                        <td class="text-left">
                                            <div class="ui-icon-action-row flex flex-nowrap items-center justify-start gap-1.5 md:gap-2">
                                                @if ($permFlags['can_show'])
                                                    <button
                                                        type="button"
                                                        wire:click="openDetailModal({{ $role->id }})"
                                                        class="ui-icon-action ui-icon-action--info"
                                                        title="Ver detalle"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endif
                                                @if ($permFlags['can_assign_permissions'])
                                                    <button
                                                        type="button"
                                                        wire:click="openPermissionsModal({{ $role->id }})"
                                                        class="ui-icon-action ui-icon-action--warning"
                                                        title="Permisos"
                                                    >
                                                        <i class="fas fa-key"></i>
                                                    </button>
                                                @endif
                                                @if ($permFlags['can_edit'])
                                                    <a
                                                        href="{{ route('admin.roles.edit', $role->id) }}"
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
                                                        wire:click="openDeleteModal({{ $role->id }})"
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
                    @foreach ($roles as $role)
                        @php
                            $isSys = in_array($role->name, ['admin', 'user', 'superadmin'], true);
                        @endphp
                        <div class="rounded-xl border border-slate-600/50 bg-slate-950/40 p-4" wire:key="role-card-{{ $role->id }}">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-white">{{ $role->name }}</p>
                                    @if ($isSys)
                                        <span class="ui-badge ui-badge-warning mt-1">Sistema</span>
                                    @else
                                        <span class="ui-badge ui-badge-success mt-1">Personalizado</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-400">
                                <span><i class="fas fa-users mr-1"></i>{{ $role->users_count }} usuarios</span>
                                <span><i class="fas fa-key mr-1"></i>{{ $role->permissions_count }} permisos</span>
                            </div>
                            <div class="ui-icon-action-row mt-3 flex flex-wrap items-center justify-start gap-2">
                                @if ($permFlags['can_show'])
                                    <button
                                        type="button"
                                        wire:click="openDetailModal({{ $role->id }})"
                                        class="ui-icon-action ui-icon-action--info"
                                        title="Ver"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                                @if ($permFlags['can_assign_permissions'])
                                    <button
                                        type="button"
                                        wire:click="openPermissionsModal({{ $role->id }})"
                                        class="ui-icon-action ui-icon-action--warning"
                                        title="Permisos"
                                    >
                                        <i class="fas fa-key"></i>
                                    </button>
                                @endif
                                @if ($permFlags['can_edit'])
                                    <a
                                        href="{{ route('admin.roles.edit', $role->id) }}"
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
                                        wire:click="openDeleteModal({{ $role->id }})"
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

            @if ($roles->hasPages())
                <div class="border-t border-slate-700/50 px-4 py-3">
                    <x-ui.pagination :paginator="$roles->onEachSide(1)" />
                </div>
            @endif
        </div>
    </div>

    @if ($showDetailModal && $detailRole)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" wire:click.self="closeDetailModal">
            <div class="ui-panel max-h-[90vh] w-full max-w-lg overflow-y-auto" @keydown.escape.window="$wire.closeDetailModal()">
                <div class="ui-panel__header">
                    <div>
                        <h3 class="ui-panel__title">{{ $detailRole['name'] }}</h3>
                        <p class="ui-panel__subtitle">Detalle del rol</p>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="ui-btn ui-btn-ghost px-2 py-1 text-lg leading-none">&times;</button>
                </div>
                <div class="ui-panel__body space-y-3 text-sm text-slate-200">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Creado</p>
                            <p class="font-medium">{{ $detailRole['created_at'] }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Actualizado</p>
                            <p class="font-medium">{{ $detailRole['updated_at'] }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Usuarios</p>
                            <p class="font-medium">{{ $detailRole['users_count'] }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Permisos</p>
                            <p class="font-medium">{{ $detailRole['permissions_count'] }}</p>
                        </div>
                    </div>
                    <div>
                        @if ($detailRole['is_system_role'])
                            <span class="ui-badge ui-badge-warning">Rol de sistema</span>
                        @else
                            <span class="ui-badge ui-badge-success">Rol personalizado</span>
                        @endif
                    </div>
                </div>
                <div class="flex justify-end border-t border-slate-700/50 px-4 py-3">
                    <button type="button" wire:click="closeDetailModal" class="ui-btn ui-btn-primary text-sm">Cerrar</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showPermissionsModal && $permissionsRoleId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" wire:click.self="closePermissionsModal">
            <div class="ui-panel flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden" wire:key="perm-modal-{{ $permissionsRoleId }}">
                <div class="ui-panel__header shrink-0">
                    <div>
                        <h3 class="ui-panel__title">Permisos · {{ $permissionsRoleName }}</h3>
                        <p class="ui-panel__subtitle">Marca los permisos y guarda. Los roles de sistema no se listan como editables desde el servidor.</p>
                    </div>
                    <button type="button" wire:click="closePermissionsModal" class="ui-btn ui-btn-ghost px-2 py-1 text-lg leading-none">&times;</button>
                </div>
                <div class="ui-panel__body flex min-h-0 flex-1 flex-col gap-3 overflow-hidden border-t border-slate-700/40">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="relative w-full sm:max-w-md">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                <i class="fas fa-search"></i>
                            </span>
                            <input
                                type="search"
                                wire:model.live.debounce.250ms="permissionSearch"
                                placeholder="Buscar permiso..."
                                class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-10 pr-3 text-sm text-slate-100"
                            />
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <button type="button" wire:click="selectAllPermissions(true)" class="ui-btn ui-btn-ghost px-2 py-1 text-xs">Marcar todos</button>
                            <button type="button" wire:click="selectAllPermissions(false)" class="ui-btn ui-btn-ghost px-2 py-1 text-xs">Limpiar</button>
                        </div>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto pr-1">
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3">
                            @foreach ($permissionsGrouped as $module => $modulePermissions)
                                <div class="rounded-xl border border-slate-600/40 bg-slate-950/40" wire:key="mod-{{ $module }}">
                                    <div class="flex items-center justify-between gap-2 border-b border-slate-700/50 px-3 py-2">
                                        <span class="text-sm font-semibold text-cyan-200"><i class="fas fa-folder mr-1 text-slate-400"></i>{{ $module }}</span>
                                        <button type="button" wire:click="toggleModulePermissions(@js($module))" class="text-xs text-cyan-400 hover:text-cyan-200">
                                            Alternar módulo
                                        </button>
                                    </div>
                                    <div class="max-h-56 space-y-1 overflow-y-auto px-2 py-2">
                                        @foreach ($modulePermissions as $permission)
                                            @php
                                                $needle = strtolower($permissionSearch);
                                                $visible =
                                                    $permissionSearch === '' ||
                                                    str_contains(strtolower($permission->friendly_name), $needle) ||
                                                    str_contains(strtolower($permission->name), $needle);
                                            @endphp
                                            @if ($visible)
                                                <label class="flex cursor-pointer items-start gap-2 rounded-lg px-2 py-1.5 hover:bg-slate-800/60">
                                                    <input
                                                        type="checkbox"
                                                        class="mt-0.5 rounded border-slate-500 bg-slate-900"
                                                        wire:model="selectedPermissionIds"
                                                        value="{{ $permission->id }}"
                                                    />
                                                    <span class="text-xs leading-snug text-slate-200" title="{{ $permission->name }}">{{ $permission->friendly_name }}</span>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex shrink-0 flex-wrap justify-end gap-2 border-t border-slate-700/50 px-4 py-3">
                    <button type="button" wire:click="closePermissionsModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="savePermissions" class="ui-btn ui-btn-warning text-sm">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showDeleteModal && $deleteTargetId)
        {{-- Capa de atención: oscurece todo el sistema; el panel es opaco (sin “cristal” sobre la tabla) --}}
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
                wire:key="delete-modal-{{ $deleteTargetId }}"
            >
                <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200"
                        >
                            <i class="fas fa-trash-alt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Eliminar este rol?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se eliminará <span class="font-medium text-white">“{{ $deleteTargetName }}”</span>. Solo puede hacerse si no tiene usuarios asignados.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmDeleteRole" class="ui-btn ui-btn-danger text-sm">
                        <i class="fas fa-trash-alt mr-1.5"></i>
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
