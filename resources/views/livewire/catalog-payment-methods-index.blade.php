<div class="space-y-6" wire:key="catalog-payment-methods-index-root">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Métodos de pago del catálogo</h1>
                <p class="ui-panel__subtitle">Opciones de pago visibles en el checkout público.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($permFlags['can_report'])
                    <a
                        href="{{ route('admin.catalog-payment-methods.report') }}"
                        target="_blank"
                        rel="noopener"
                        class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                    >
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                @endif
                @if ($permFlags['can_create'])
                    <a href="{{ route('admin.catalog-payment-methods.create') }}" class="ui-btn ui-btn-primary text-sm md:py-2.5 md:px-5 md:text-[0.95rem]" wire:navigate>
                        <i class="fas fa-plus"></i> Nuevo método
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-3">
        <x-ui.stat-card variant="info" icon="fas fa-money-check-alt" trend="Total" label="Métodos" :value="number_format($stats['total'])" meta="Registrados" />
        <x-ui.stat-card variant="success" icon="fas fa-check-circle" trend="Activos" label="Activos" :value="number_format($stats['active'])" meta="Checkout" />
        <x-ui.stat-card variant="warning" icon="fas fa-shopping-bag" trend="Pedidos" label="En uso" :value="number_format($stats['with_orders'])" meta="Con pedidos" />
    </div>

    <div
        class="ui-panel"
        x-data="{
            showFilters: (() => {
                const stored = localStorage.getItem('catalog_pm_filters_open');
                if (stored !== null) return stored === 'true';
                const initial = @js($filtersOpen ?? false);
                try { localStorage.setItem('catalog_pm_filters_open', initial); } catch (e) {}
                return initial;
            })(),
            toggleFilters() {
                this.showFilters = !this.showFilters;
                try { localStorage.setItem('catalog_pm_filters_open', this.showFilters); } catch (e) {}
            },
        }"
    >
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="ui-panel__title">Filtros</h2>
                <p class="ui-panel__subtitle">Estado y segmentación rápida.</p>
            </div>
            <button type="button" class="ui-btn ui-btn-ghost text-sm" @click="toggleFilters()" :aria-expanded="showFilters">
                <i class="fas" :class="showFilters ? 'fa-sliders-h' : 'fa-filter'"></i>
                <span x-text="showFilters ? 'Ocultar filtros' : 'Filtros'"></span>
            </button>
        </div>
        <div class="ui-panel__body space-y-4" x-show="showFilters" x-transition wire:loading.class.delay="opacity-60">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-end lg:grid-cols-3">
                <div class="lg:col-span-1">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Activos</label>
                    <select
                        wire:model.live="activeOnly"
                        class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    >
                        <option value="">Todos</option>
                        <option value="yes">Activos solamente</option>
                        <option value="no">Inactivos solamente</option>
                    </select>
                </div>
                <div class="flex sm:col-span-2 lg:col-span-2 lg:justify-end">
                    <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost w-full text-sm sm:w-auto lg:w-auto">
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
                    <p class="ui-panel__subtitle">
                        {{ $paymentMethods->total() }} resultado(s) · Página {{ $paymentMethods->currentPage() }} de {{ $paymentMethods->lastPage() }}
                    </p>
                </div>

                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto">
                    <div class="relative min-w-[16rem] flex-1 lg:min-w-[18rem]">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Buscar nombre o instrucciones…"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                            autocomplete="off"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost text-sm" title="Limpiar búsqueda y filtros">
                            <i class="fas fa-eraser"></i>
                        </button>
                        @if ($permFlags['can_destroy'] && ! $paymentMethods->isEmpty())
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
            @if ($paymentMethods->isEmpty())
                <p class="px-4 py-10 text-center text-sm text-slate-400">No hay métodos que coincidan con los filtros.</p>
            @else
                @if ($selectionMode)
                    <div class="flex flex-col gap-3 border-b border-slate-700/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-white">{{ count($selectedPaymentMethodIds) }} método(s) seleccionado(s)</p>
                            <p class="text-xs text-slate-400">
                                La selección aplica a la página visible. No se eliminan registros con pedidos asociados.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" wire:click="toggleSelectAllCurrentPage" class="ui-btn ui-btn-ghost text-sm">
                                <i class="fas {{ $allCurrentPageSelected ? 'fa-square-minus' : 'fa-square-check' }}"></i>
                                {{ $allCurrentPageSelected ? 'Limpiar página' : 'Seleccionar página' }}
                            </button>
                            <button type="button" wire:click="openBulkDeleteModal" class="ui-btn ui-btn-danger text-sm" @disabled(count($selectedPaymentMethodIds) === 0)>
                                <i class="fas fa-trash-alt"></i>
                                Eliminar seleccionados
                            </button>
                        </div>
                    </div>
                @endif

                <div class="hidden md:block">
                    <div class="ui-table-wrap border-0 rounded-none" wire:key="catalog-pm-table" wire:loading.class.delay="opacity-60">
                        <table class="ui-table ui-table--nowrap-actions">
                            <thead>
                                <tr>
                                    @if ($selectionMode)
                                        <th class="w-12 text-center">
                                            <input
                                                type="checkbox"
                                                class="rounded border-slate-500 bg-slate-900"
                                                @checked($allCurrentPageSelected)
                                                wire:click="toggleSelectAllCurrentPage"
                                            />
                                        </th>
                                    @endif
                                    <th>Método</th>
                                    <th class="text-center">%</th>
                                    <th class="text-center">Pedidos</th>
                                    <th class="text-center">Orden</th>
                                    <th class="text-center">Estado</th>
                                    <th class="hidden lg:table-cell">Actualizado</th>
                                    <th class="text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($paymentMethods as $p)
                                    <tr wire:key="pm-row-{{ $p->id }}">
                                        @if ($selectionMode)
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    class="rounded border-slate-500 bg-slate-900"
                                                    @checked(in_array($p->id, $selectedPaymentMethodIds, true))
                                                    wire:click="togglePaymentMethodSelection({{ $p->id }})"
                                                />
                                            </td>
                                        @endif
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-cyan-500/15 text-sm font-semibold text-cyan-200">
                                                    <i class="fas fa-wallet"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-white">{{ $p->name }}</p>
                                                    @if ($p->instructions)
                                                        <p class="max-w-xs truncate text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($p->instructions, 48) }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center tabular-nums">{{ $p->discount_percent }}</td>
                                        <td class="text-center tabular-nums">{{ $p->orders_count }}</td>
                                        <td class="text-center tabular-nums">{{ $p->sort_order }}</td>
                                        <td class="text-center">
                                            <span class="ui-badge {{ $p->is_active ? 'ui-badge-success' : 'ui-badge-warning' }}">
                                                {{ $p->is_active ? 'Activo' : 'Off' }}
                                            </span>
                                        </td>
                                        <td class="hidden text-sm text-slate-400 lg:table-cell">{{ $p->updated_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="ui-icon-action-row flex flex-nowrap items-center justify-start gap-1.5 md:gap-2">
                                                @if ($permFlags['can_show'])
                                                    <button type="button" wire:click="openDetailModal({{ $p->id }})" class="ui-icon-action ui-icon-action--info" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endif
                                                @if ($permFlags['can_edit'])
                                                    <a href="{{ route('admin.catalog-payment-methods.edit', $p->id) }}" wire:navigate class="ui-icon-action ui-icon-action--primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                                @if ($permFlags['can_destroy'])
                                                    <button type="button" wire:click="openDeleteModal({{ $p->id }})" class="ui-icon-action ui-icon-action--danger" title="Eliminar">
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

                <div class="space-y-3 p-4 md:hidden" wire:loading.class.delay="opacity-60">
                    @foreach ($paymentMethods as $p)
                        <div wire:key="pm-card-{{ $p->id }}" class="rounded-xl border border-slate-700/60 bg-slate-950/40 p-4">
                            <div class="flex items-start justify-between gap-3">
                                @if ($selectionMode)
                                    <input
                                        type="checkbox"
                                        class="mt-1 shrink-0 rounded border-slate-500 bg-slate-900"
                                        @checked(in_array($p->id, $selectedPaymentMethodIds, true))
                                        wire:click="togglePaymentMethodSelection({{ $p->id }})"
                                    />
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-white">{{ $p->name }}</p>
                                    @if ($p->instructions)
                                        <p class="mt-1 text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($p->instructions, 80) }}</p>
                                    @endif
                                    <p class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
                                        <span class="ui-badge {{ $p->is_active ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $p->is_active ? 'Activo' : 'Inactivo' }}</span>
                                        <span><i class="fas fa-percent mr-1"></i>{{ $p->discount_percent }}</span>
                                        <span><i class="fas fa-shopping-bag mr-1"></i>{{ $p->orders_count }} pedidos</span>
                                    </p>
                                </div>
                            </div>
                            <div class="ui-icon-action-row mt-3 flex flex-wrap items-center justify-start gap-2 border-t border-slate-700/50 pt-3">
                                @if ($permFlags['can_show'])
                                    <button type="button" wire:click="openDetailModal({{ $p->id }})" class="ui-icon-action ui-icon-action--info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                                @if ($permFlags['can_edit'])
                                    <a href="{{ route('admin.catalog-payment-methods.edit', $p->id) }}" wire:navigate class="ui-icon-action ui-icon-action--primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if ($permFlags['can_destroy'])
                                    <button type="button" wire:click="openDeleteModal({{ $p->id }})" class="ui-icon-action ui-icon-action--danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-slate-700/50 px-4 py-4">
                    <x-ui.pagination :paginator="$paymentMethods" scroll-into-view=".ui-panel.overflow-hidden:first-of-type" />
                </div>
            @endif
        </div>
    </div>

    @if ($showDetailModal && $detailPaymentMethod)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center bg-[#020617]/90 p-4 backdrop-blur-md"
            wire:click.self="closeDetailModal"
            x-data
            x-on:keydown.escape.window="$wire.closeDetailModal()"
            role="dialog"
            aria-modal="true"
        >
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75)]">
                <div class="border-b border-slate-700 px-5 pb-4 pt-5">
                    <h3 class="text-lg font-semibold text-white">{{ $detailPaymentMethod['name'] }}</h3>
                    <p class="mt-2 text-sm text-slate-400">{{ $detailPaymentMethod['instructions'] }}</p>
                </div>
                <div class="space-y-3 px-5 py-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Descuento %</span>
                        <span class="font-medium text-white">{{ $detailPaymentMethod['discount_percent'] }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Orden</span>
                        <span class="tabular-nums text-slate-200">{{ $detailPaymentMethod['sort_order'] }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Activo</span>
                        <span class="text-slate-200">{{ $detailPaymentMethod['is_active'] ? 'Sí' : 'No' }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Pedidos</span>
                        <span class="tabular-nums text-slate-200">{{ $detailPaymentMethod['orders_count'] }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Creado</span>
                        <span class="text-slate-200">{{ $detailPaymentMethod['created_at'] }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Actualizado</span>
                        <span class="text-slate-200">{{ $detailPaymentMethod['updated_at'] }}</span>
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
            role="dialog"
            aria-modal="true"
        >
            <div class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75)]">
                <div class="border-b border-slate-700 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Eliminar método de pago?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se eliminará <span class="font-medium text-white">“{{ $deleteTargetName }}”</span>. No es posible si hay pedidos vinculados.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmDeletePaymentMethod" class="ui-btn ui-btn-danger text-sm">
                        <i class="fas fa-trash-alt mr-1.5"></i> Sí, eliminar
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
            role="dialog"
            aria-modal="true"
        >
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75)]">
                <div class="border-b border-slate-700 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Eliminar métodos seleccionados?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se intentará eliminar <span class="font-medium text-white">{{ count($selectedPaymentMethodIds) }}</span> método(s). Los que tengan pedidos no se
                                borrarán.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeBulkDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmBulkDelete" class="ui-btn ui-btn-danger text-sm">
                        <i class="fas fa-trash-alt mr-1.5"></i> Sí, eliminar seleccionadas
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
