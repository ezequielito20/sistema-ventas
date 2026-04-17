{{-- Layout responsive solicitado:
    >=1300px: Producto | Desde | Hasta | Buscar | Limpiar
    640px-1299px: Producto | Desde | Hasta | Limpiar / Buscar abajo en fila completa
    <640px: todo en filas individuales --}}
<style>
    .purchases-v2-filters-grid {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: minmax(0, 1fr);
    }

    .purchases-v2-filter-product,
    .purchases-v2-filter-from,
    .purchases-v2-filter-to,
    .purchases-v2-filter-search,
    .purchases-v2-filter-clear {
        grid-area: auto;
    }

    @media (min-width: 640px) and (max-width: 1299.98px) {
        .purchases-v2-filter-product { grid-area: product; }
        .purchases-v2-filter-from { grid-area: from; }
        .purchases-v2-filter-to { grid-area: to; }
        .purchases-v2-filter-search { grid-area: search; }
        .purchases-v2-filter-clear { grid-area: clear; }

        .purchases-v2-filters-grid {
            align-items: end;
            grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr) minmax(0, 1fr) auto;
            grid-template-areas:
                "product from to clear"
                "search search search search";
        }
    }

    @media (min-width: 1300px) {
        .purchases-v2-filter-product { grid-area: product; }
        .purchases-v2-filter-from { grid-area: from; }
        .purchases-v2-filter-to { grid-area: to; }
        .purchases-v2-filter-search { grid-area: search; }
        .purchases-v2-filter-clear { grid-area: clear; }

        .purchases-v2-filters-grid {
            align-items: end;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1.5fr) auto;
            grid-template-areas: "product from to search clear";
        }

        .purchases-v2-filter-clear .ui-btn {
            width: auto;
        }
    }
</style>
<div class="ui-panel ui-panel--overflow-visible">
    <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h2 class="ui-panel__title">Filtros</h2>
            <p class="ui-panel__subtitle">Búsqueda por recibo, producto y fechas de compra.</p>
        </div>
        <button
            type="button"
            class="ui-btn ui-btn-ghost w-full shrink-0 text-sm sm:w-auto"
            @click="showFilters = !showFilters"
            :aria-expanded="showFilters"
        >
            {{-- Iconos con clase FA completa (fa-filter / fa-sliders-h) para no depender de :class sobre <i class="fas"> vacío --}}
            <i class="fas fa-filter text-sm" x-show="!showFilters"></i>
            <i class="fas fa-sliders-h text-sm" x-show="showFilters"></i>
            <span x-text="showFilters ? 'Ocultar filtros' : 'Filtros avanzados'">Filtros avanzados</span>
        </button>
    </div>

    <div class="ui-panel__body space-y-4" x-show="showFilters" x-transition>
        <div class="purchases-v2-filters-grid">
            <div class="relative z-10 min-w-0 purchases-v2-filter-product" @click.away="productMenuOpen = false">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Producto</label>
                <button type="button" @click="productMenuOpen = !productMenuOpen"
                    class="flex w-full items-center justify-between rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-left text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                    <span class="truncate" x-text="selectedProductName"></span>
                    <i class="fas fa-chevron-down ml-2 text-xs text-slate-500" :class="{ 'rotate-180': productMenuOpen }"></i>
                </button>
                <div x-show="productMenuOpen" x-cloak x-transition
                    class="absolute z-[200] mt-1 w-full overflow-hidden rounded-lg border border-slate-600 bg-slate-900 shadow-xl">
                    <div class="border-b border-slate-700/80 p-2">
                        <input type="text" x-model="productQuery" placeholder="Filtrar lista..."
                            class="w-full rounded-md border border-slate-600 bg-slate-950/80 px-2 py-1.5 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none"
                            autocomplete="off">
                    </div>
                    <div class="max-h-52 overflow-y-auto py-1">
                        <button type="button" @click="selectProduct(null); productMenuOpen = false"
                            class="flex w-full px-3 py-2 text-left text-sm text-slate-200 hover:bg-slate-800">Todos los productos</button>
                        <template x-for="p in filteredProducts()" :key="p.id">
                            <button type="button" @click="selectProduct(p); productMenuOpen = false"
                                class="flex w-full flex-col px-3 py-2 text-left text-sm hover:bg-slate-800">
                                <span class="font-medium text-slate-100" x-text="p.name"></span>
                                <span class="text-xs text-slate-500" x-text="p.code + (p.category ? ' · ' + p.category.name : '')"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="purchases-v2-filter-from">
                <label for="date_from_v2" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Desde</label>
                <input type="date" id="date_from_v2" x-model="dateFrom" @change="executeServerSearch()"
                    class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    style="color-scheme: dark;">
            </div>
            <div class="purchases-v2-filter-to">
                <label for="date_to_v2" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Hasta</label>
                <input type="date" id="date_to_v2" x-model="dateTo" @change="executeServerSearch()"
                    class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    style="color-scheme: dark;">
            </div>
            <div class="min-w-0 purchases-v2-filter-search">
                <label for="purchases_v2_search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Buscar</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        id="purchases_v2_search"
                        type="search"
                        x-model="searchTerm"
                        placeholder="Recibo, fecha, producto o monto…"
                        class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-10 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        autocomplete="off"
                        autocorrect="off"
                        autocapitalize="off"
                        spellcheck="false"
                    >
                </div>
            </div>
            <div class="purchases-v2-filter-clear">
                <button type="button" @click="resetFilters()" class="ui-btn ui-btn-ghost w-full text-sm">
                    <i class="fas fa-eraser"></i> Limpiar filtros
                </button>
            </div>
        </div>
    </div>
</div>
