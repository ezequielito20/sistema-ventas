<div class="space-y-6" wire:key="categories-index-root">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Categorías</h1>
                <p class="ui-panel__subtitle">Organiza productos por categorías dentro de tu empresa.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($permFlags['can_report'])
                    <a
                        href="{{ route('admin.categories.report') }}"
                        target="_blank"
                        rel="noopener"
                        class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                    >
                        <i class="fas fa-file-pdf"></i> Ver PDF
                    </a>
                @endif
                @if ($permFlags['can_create'])
                    <a
                        href="{{ route('admin.categories.create') }}"
                        class="ui-btn ui-btn-primary text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                        wire:navigate
                    >
                        <i class="fas fa-plus"></i> Nueva categoría
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-4">
        <x-ui.stat-card
            variant="info"
            icon="fas fa-tags"
            trend="Total"
            label="Categorías"
            :value="number_format($stats['total'])"
            meta="Registradas"
        />
        <x-ui.stat-card
            variant="warning"
            icon="fas fa-calendar-week"
            trend="7 días"
            label="Esta semana"
            :value="number_format($stats['weekly'])"
            meta="Nuevas"
        />
        <x-ui.stat-card
            variant="success"
            icon="fas fa-box"
            trend="Uso"
            label="Con productos"
            :value="number_format($stats['with_products'])"
            meta="En uso"
        />
        <x-ui.stat-card
            variant="danger"
            icon="fas fa-inbox"
            trend="Vacías"
            label="Sin productos"
            :value="number_format($stats['empty'])"
            meta="Sin asignar"
        />
    </div>

    <div class="ui-panel" x-data="{ showFilters: false }">
        <div class="ui-panel__header flex items-center justify-between gap-3">
            <div>
                <h2 class="ui-panel__title">Filtros</h2>
                <p class="ui-panel__subtitle">Búsqueda y segmentación de categorías.</p>
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
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-end 2xl:grid-cols-[minmax(0,1.35fr)_10rem_9rem_9rem_6.5rem_6.5rem_auto]">
                <div class="min-w-0">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Buscar</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fas fa-search"></i>
                        </span>
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Nombre o descripción..."
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-10 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Productos</label>
                    <select
                        wire:model.live="hasProducts"
                        class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    >
                        <option value="">Todos</option>
                        <option value="yes">Con productos</option>
                        <option value="no">Sin productos</option>
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
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Mín. prod.</label>
                    <input
                        type="number"
                        min="0"
                        wire:model.live="productsMin"
                        placeholder="0"
                        class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Máx. prod.</label>
                    <input
                        type="number"
                        min="0"
                        wire:model.live="productsMax"
                        placeholder="∞"
                        class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    />
                </div>
                <div class="sm:col-span-2 2xl:col-span-1">
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="ui-btn ui-btn-ghost w-full text-sm 2xl:w-auto"
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
                    <p class="ui-panel__subtitle">{{ $categories->total() }} resultado(s) · Página {{ $categories->currentPage() }} de {{ $categories->lastPage() }}</p>
                </div>
                @if ($permFlags['can_destroy'] && ! $categories->isEmpty())
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
            @if ($categories->isEmpty())
                <p class="px-4 py-10 text-center text-sm text-slate-400">No hay categorías que coincidan con los filtros.</p>
            @else
                @if ($selectionMode)
                    <div class="flex flex-col gap-3 border-b border-slate-700/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-white">{{ count($selectedCategoryIds) }} categoría(s) seleccionada(s)</p>
                            <p class="text-xs text-slate-400">
                                La selección aplica a la página actual. No se eliminan categorías con productos asociados.
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
                                @disabled(count($selectedCategoryIds) === 0)
                            >
                                <i class="fas fa-trash-alt"></i>
                                Eliminar seleccionadas
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
                                    <th>Categoría</th>
                                    <th class="hidden lg:table-cell">Descripción</th>
                                    <th class="text-center">Productos</th>
                                    <th class="hidden xl:table-cell">Creado</th>
                                    <th class="text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr wire:key="cat-row-{{ $category->id }}">
                                        @if ($selectionMode)
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    value="{{ $category->id }}"
                                                    @checked(in_array($category->id, $selectedCategoryIds, true))
                                                    wire:click="toggleCategorySelection({{ $category->id }})"
                                                    class="rounded border-slate-500 bg-slate-900"
                                                />
                                            </td>
                                        @endif
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-cyan-500/15 text-sm font-semibold text-cyan-200">
                                                    <i class="fas fa-tag"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-white">{{ $category->name }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hidden max-w-xs truncate text-sm text-slate-400 lg:table-cell">
                                            {{ $category->description ? \Illuminate\Support\Str::limit($category->description, 60) : '—' }}
                                        </td>
                                        <td class="text-center">
                                            <span class="ui-badge {{ $category->products_count > 0 ? 'ui-badge-success' : 'ui-badge-warning' }}">
                                                {{ $category->products_count }}
                                            </span>
                                        </td>
                                        <td class="hidden text-sm text-slate-400 xl:table-cell">
                                            {{ $category->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="text-left">
                                            <div class="ui-icon-action-row flex flex-nowrap items-center justify-start gap-1.5 md:gap-2">
                                                @if ($permFlags['can_show'])
                                                    <button
                                                        type="button"
                                                        wire:click="openDetailModal({{ $category->id }})"
                                                        class="ui-icon-action ui-icon-action--info"
                                                        title="Ver detalle"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endif
                                                @if ($permFlags['can_edit'])
                                                    <a
                                                        href="{{ route('admin.categories.edit', $category->id) }}"
                                                        class="ui-icon-action ui-icon-action--primary"
                                                        wire:navigate
                                                        title="Editar"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                                @if ($permFlags['can_destroy'])
                                                    <button
                                                        type="button"
                                                        wire:click="openDeleteModal({{ $category->id }})"
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
                    @foreach ($categories as $category)
                        <div
                            class="rounded-xl border border-slate-700/60 bg-slate-950/40 p-4"
                            wire:key="cat-card-{{ $category->id }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-white">{{ $category->name }}</p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $category->description ? \Illuminate\Support\Str::limit($category->description, 80) : 'Sin descripción' }}
                                    </p>
                                    <p class="mt-2 text-xs text-slate-500">
                                        <i class="fas fa-box mr-1"></i> {{ $category->products_count }} producto(s)
                                        · {{ $category->created_at->format('d/m/Y') }}
                                    </p>
                                </div>
                                @if ($selectionMode)
                                    <input
                                        type="checkbox"
                                        @checked(in_array($category->id, $selectedCategoryIds, true))
                                        wire:click="toggleCategorySelection({{ $category->id }})"
                                        class="mt-1 rounded border-slate-500 bg-slate-900"
                                    />
                                @endif
                            </div>
                            <div class="ui-icon-action-row mt-3 flex flex-wrap items-center justify-start gap-2">
                                @if ($permFlags['can_show'])
                                    <button type="button" wire:click="openDetailModal({{ $category->id }})" class="ui-icon-action ui-icon-action--info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                                @if ($permFlags['can_edit'])
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="ui-icon-action ui-icon-action--primary" wire:navigate title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if ($permFlags['can_destroy'])
                                    <button type="button" wire:click="openDeleteModal({{ $category->id }})" class="ui-icon-action ui-icon-action--danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-slate-700/50 px-4 py-4">
                    <x-ui.pagination :paginator="$categories" />
                </div>
            @endif
        </div>
    </div>

    @if ($showDetailModal && $detailCategory)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center bg-[#020617]/90 p-4 backdrop-blur-md"
            wire:click.self="closeDetailModal"
            x-data
            x-on:keydown.escape.window="$wire.closeDetailModal()"
            aria-modal="true"
            role="dialog"
        >
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75)]">
                <div class="border-b border-slate-700 px-5 pb-4 pt-5">
                    <h3 class="text-lg font-semibold text-white">{{ $detailCategory['name'] }}</h3>
                    <p class="mt-2 text-sm text-slate-400">{{ $detailCategory['description'] }}</p>
                </div>
                <div class="space-y-3 px-5 py-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Productos</span>
                        <span class="font-medium text-white">{{ $detailCategory['products_count'] }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Creado</span>
                        <span class="text-slate-200">{{ $detailCategory['created_at'] }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Actualizado</span>
                        <span class="text-slate-200">{{ $detailCategory['updated_at'] }}</span>
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
            <div class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75)]">
                <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Eliminar esta categoría?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se eliminará <span class="font-medium text-white">“{{ $deleteTargetName }}”</span>. Solo puede hacerse si no tiene productos asociados.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmDeleteCategory" class="ui-btn ui-btn-danger text-sm">
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
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75)]">
                <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Eliminar categorías seleccionadas?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se intentará eliminar <span class="font-medium text-white">{{ count($selectedCategoryIds) }} categoría(s)</span>.
                                Las que tengan productos no se eliminarán y se indicará el motivo.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeBulkDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmBulkDelete" class="ui-btn ui-btn-danger text-sm">
                        <i class="fas fa-trash-alt mr-1.5"></i>
                        Sí, eliminar seleccionadas
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
