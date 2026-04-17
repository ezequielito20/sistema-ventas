<section class="ui-panel" x-data="{ open: false }">
    <div class="ui-panel__header flex items-center justify-between gap-3">
        <div>
            <h2 class="ui-panel__title">Filtros</h2>
            <p class="ui-panel__subtitle">Búsqueda fluida por cliente y rango de fechas.</p>
        </div>
        <button type="button" class="ui-btn ui-btn-ghost text-sm" @click="open = !open">
            <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
        </button>
    </div>

    <div class="ui-panel__body" x-show="open" x-transition>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="customer_search_header" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Cliente</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" x-model="searchTerm" id="customer_search_header"
                        placeholder="Nombre, email o teléfono..."
                        class="w-full rounded-lg border border-slate-600 bg-slate-950/70 py-2.5 pl-10 pr-10 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                    <div x-show="isSearching" class="absolute inset-y-0 right-9 flex items-center text-cyan-400">
                        <i class="fas fa-circle-notch fa-spin"></i>
                    </div>
                    <button x-show="searchTerm.length > 0" @click="clearSearch()" type="button"
                        class="absolute inset-y-0 right-0 px-3 text-slate-500 transition hover:text-rose-400">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div>
                <label for="date_from" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Desde</label>
                <input type="date" id="date_from" value="{{ request('date_from') }}"
                    class="w-full rounded-lg border border-slate-600 bg-slate-950/70 px-3 py-2.5 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    style="color-scheme: dark;">
            </div>
            <div>
                <label for="date_to" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Hasta</label>
                <input type="date" id="date_to" value="{{ request('date_to') }}"
                    class="w-full rounded-lg border border-slate-600 bg-slate-950/70 px-3 py-2.5 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    style="color-scheme: dark;">
            </div>
        </div>
        <div class="mt-3 flex justify-end">
            <button type="button" @click="resetFilters()" class="ui-btn ui-btn-ghost text-sm">
                <i class="fas fa-eraser"></i> Limpiar filtros
            </button>
        </div>
    </div>
</section>
