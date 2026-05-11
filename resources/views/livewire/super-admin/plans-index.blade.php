<div class="space-y-6">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Planes de Suscripción</h1>
                <p class="ui-panel__subtitle">Gestión de planes, precios y límites del sistema.</p>
            </div>
            <div>
                <button type="button" wire:click="openCreateModal" class="ui-btn ui-btn-primary text-sm">
                    <i class="fas fa-plus"></i> Nuevo plan
                </button>
            </div>
        </div>
    </div>

    <div class="ui-panel overflow-hidden">
        <div class="ui-panel__header">
            <div class="relative min-w-[16rem] max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar plan..." class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
            </div>
        </div>
        <div class="ui-panel__body p-0">
            <div class="ui-table-wrap border-0 rounded-none">
                <table class="ui-table ui-table--nowrap-actions">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Precio Base</th>
                            <th>P/Usuario</th>
                            <th>P/Transacción</th>
                            <th>Límites</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Suscripciones</th>
                            <th class="text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($plans as $plan)
                            <tr>
                                <td>
                                    <p class="font-medium text-white">{{ $plan->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $plan->slug }}</p>
                                </td>
                                <td class="text-sm font-medium text-emerald-400">$ {{ number_format($plan->base_price, 2) }}</td>
                                <td class="text-sm text-slate-300">$ {{ number_format($plan->price_per_user, 2) }}</td>
                                <td class="text-sm text-slate-300">$ {{ number_format($plan->price_per_transaction, 2) }}</td>
                                <td class="text-sm text-slate-300">
                                    @if ($plan->max_users) <span class="ui-badge ui-badge-info text-xs">Usuarios: {{ $plan->max_users }}</span> @endif
                                    @if ($plan->max_transactions) <span class="ui-badge ui-badge-info text-xs">Trans: {{ $plan->max_transactions }}</span> @endif
                                </td>
                                <td class="text-center">
                                    <span @class(['ui-badge ui-badge-success' => $plan->is_active, 'ui-badge ui-badge-warning' => !$plan->is_active])>
                                        {{ $plan->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="text-center text-sm text-slate-300">{{ $plan->subscriptions_count }}</td>
                                <td class="text-left">
                                    <div class="ui-icon-action-row flex flex-nowrap items-center justify-start gap-1.5 md:gap-2">
                                        <button type="button" wire:click="openEditModal({{ $plan->id }})" class="ui-icon-action ui-icon-action--primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" wire:click="openDeleteModal({{ $plan->id }})" class="ui-icon-action ui-icon-action--danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-8 text-sm text-slate-400">No hay planes registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Formulario Crear/Editar --}}
    @if ($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" x-data x-cloak x-show="true" x-transition>
            <div class="fixed inset-0 bg-black/60" wire:click="closeFormModal"></div>
            <div class="relative w-full max-w-2xl mx-4 my-8" @click.stop>
                <div class="ui-panel">
                    <div class="ui-panel__header flex items-center justify-between">
                        <h3 class="ui-panel__title">{{ $isEditing ? 'Editar plan' : 'Nuevo plan' }}</h3>
                        <button type="button" wire:click="closeFormModal" class="text-slate-400 hover:text-slate-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="ui-panel__body">
                        <form wire:submit="save" class="space-y-5">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Nombre *</label>
                                    <input type="text" wire:model="name" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                                    @error('name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Slug *</label>
                                    <input type="text" wire:model="slug" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                                    @error('slug') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Descripción</label>
                                <textarea wire:model="description" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none"></textarea>
                            </div>
                            <hr class="border-slate-700">
                            <p class="text-sm font-semibold text-slate-200">Precios</p>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-xs text-slate-400">Precio base ($) *</label>
                                    <input type="number" wire:model="basePrice" step="0.01" min="0" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-400">Precio por usuario extra ($)</label>
                                    <input type="number" wire:model="pricePerUser" step="0.01" min="0" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-400">Precio por transacción extra ($)</label>
                                    <input type="number" wire:model="pricePerTransaction" step="0.01" min="0" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                                </div>
                            </div>
                            <hr class="border-slate-700">
                            <p class="text-sm font-semibold text-slate-200">Límites</p>
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div><label class="mb-1 block text-xs text-slate-400">Máx usuarios</label><input type="number" wire:model="maxUsers" min="0" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" /></div>
                                <div><label class="mb-1 block text-xs text-slate-400">Máx transacciones</label><input type="number" wire:model="maxTransactions" min="0" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" /></div>
                                <div><label class="mb-1 block text-xs text-slate-400">Máx productos</label><input type="number" wire:model="maxProducts" min="0" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" /></div>
                                <div><label class="mb-1 block text-xs text-slate-400">Máx clientes</label><input type="number" wire:model="maxCustomers" min="0" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" /></div>
                            </div>
                            <hr class="border-slate-700">
                            <p class="text-sm font-semibold text-slate-200">Módulos habilitados</p>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" wire:model="hasSales" class="rounded border-slate-500 bg-slate-900 text-cyan-500" /> Ventas</label>
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" wire:model="hasPurchases" class="rounded border-slate-500 bg-slate-900 text-cyan-500" /> Compras</label>
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" wire:model="hasReports" class="rounded border-slate-500 bg-slate-900 text-cyan-500" /> Reportes</label>
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" wire:model="hasCustomers" class="rounded border-slate-500 bg-slate-900 text-cyan-500" /> Clientes</label>
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" wire:model="hasProducts" class="rounded border-slate-500 bg-slate-900 text-cyan-500" /> Productos</label>
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" wire:model="hasCategories" class="rounded border-slate-500 bg-slate-900 text-cyan-500" /> Categorías</label>
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" wire:model="hasCashCounts" class="rounded border-slate-500 bg-slate-900 text-cyan-500" /> Arqueo Caja</label>
                            </div>
                            <div class="pt-2">
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" wire:model="isActive" class="rounded border-slate-500 bg-slate-900 text-cyan-500" /> Plan activo</label>
                            </div>
                            <div class="flex justify-end gap-2 border-t border-slate-700/50 pt-4">
                                <button type="button" wire:click="closeFormModal" class="ui-btn ui-btn-ghost">Cancelar</button>
                                <button type="submit" class="ui-btn ui-btn-primary">
                                    <i class="fas fa-save mr-1.5"></i> {{ $isEditing ? 'Actualizar' : 'Crear' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Eliminar --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center" x-data x-cloak x-show="true" x-transition>
            <div class="fixed inset-0 bg-black/60" wire:click="closeDeleteModal"></div>
            <div class="relative w-full max-w-md mx-4" @click.stop>
                <div class="ui-panel">
                    <div class="ui-panel__header"><h3 class="ui-panel__title text-rose-400">Eliminar plan</h3></div>
                    <div class="ui-panel__body space-y-4">
                        <p class="text-sm text-slate-300">¿Eliminar el plan <strong class="text-white">{{ $deleteTargetName }}</strong>?</p>
                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="closeDeleteModal" class="ui-btn ui-btn-ghost">Cancelar</button>
                            <button type="button" wire:click="confirmDelete" class="ui-btn ui-btn-danger">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
