@php
    $inputBase = 'w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500';
    $labelBase = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400';

    $currencySymbol = $currency->symbol ?? '$';
@endphp

<div class="space-y-6" wire:key="cash-counts-index">

    {{-- ================================================================ --}}
    {{-- HEADER                                                           --}}
    {{-- ================================================================ --}}
    <div class="ui-panel overflow-hidden">
        <div class="ui-panel__header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Arqueo de Caja</h1>
                <p class="ui-panel__subtitle">Control de aperturas, cierres y movimientos de caja.</p>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2">
                @if ($permFlags['can_report'])
                    <a href="{{ route('admin.cash-counts.report') }}" target="_blank" rel="noopener"
                        class="ui-btn ui-btn-ghost text-sm">
                        <i class="fas fa-file-pdf"></i> Reporte PDF
                    </a>
                @endif
                @if (! $currentCashCount && $permFlags['can_create'])
                    <a href="{{ route('admin.cash-counts.create') }}" class="ui-btn ui-btn-primary text-sm">
                        <i class="fas fa-plus"></i> Abrir caja
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- STATS                                                            --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        {{-- Caja actual --}}
        <x-ui.stat-card
            variant="{{ $currentCashCount ? 'success' : 'danger' }}"
            icon="fas fa-cash-register"
            trend="{{ $currentCashCount ? 'Abierta' : 'Cerrada' }}"
            label="Estado de caja"
            :value="$currentCashCount ? 'Abierta' : 'Cerrada'"
            meta="{{ $currentCashCount ? 'Desde: ' . \Carbon\Carbon::parse($currentCashCount->opening_date)->format('d/m/Y H:i') : 'Sin caja abierta' }}"
        />

        {{-- Monto inicial --}}
        <x-ui.stat-card
            variant="info"
            icon="fas fa-wallet"
            trend="{{ $currentCashCount ? 'Activo' : '—' }}"
            label="Monto inicial"
            :value="$currencySymbol . ' ' . number_format((float) ($currentCashCount->initial_amount ?? 0), 2)"
            meta="Caja actual"
        />

        {{-- Total arqueos --}}
        <x-ui.stat-card
            variant="warning"
            icon="fas fa-list-ol"
            trend="Histórico"
            label="Total arqueos"
            :value="$cashCounts->total()"
            meta="Registros históricos"
        />

        {{-- Abiertos --}}
        <x-ui.stat-card
            variant="danger"
            icon="fas fa-door-open"
            trend="En curso"
            label="Abiertos"
            :value="$cashCounts->whereNull('closing_date')->count()"
            meta="Cajas sin cerrar"
        />
    </div>

    {{-- ================================================================ --}}
    {{-- FILTROS                                                          --}}
    {{-- ================================================================ --}}
    <div class="ui-panel" x-data="{ showFilters: false }">
        <div class="ui-panel__header flex items-center justify-between gap-3">
            <div>
                <h2 class="ui-panel__title">Filtros</h2>
                <p class="ui-panel__subtitle">Búsqueda por fecha, estado o número de arqueo.</p>
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
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-end xl:grid-cols-[minmax(0,0.9fr)_minmax(0,0.9fr)_minmax(0,0.9fr)_auto]">
                {{-- Estado --}}
                <div>
                    <label class="{{ $labelBase }}">Estado</label>
                    <select wire:model.live="status" class="{{ $inputBase }}">
                        <option value="">Todos</option>
                        <option value="open">Abierto</option>
                        <option value="closed">Cerrado</option>
                    </select>
                </div>
                {{-- Desde --}}
                <div>
                    <label class="{{ $labelBase }}">Desde</label>
                    <input type="date" wire:model.live="dateFrom" class="{{ $inputBase }}" style="color-scheme: dark;">
                </div>
                {{-- Hasta --}}
                <div>
                    <label class="{{ $labelBase }}">Hasta</label>
                    <input type="date" wire:model.live="dateTo" class="{{ $inputBase }}" style="color-scheme: dark;">
                </div>
                {{-- Limpiar --}}
                <div class="sm:col-span-2 xl:col-span-1">
                    <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost w-full text-sm 2xl:w-auto">
                        <i class="fas fa-eraser"></i> Limpiar filtros
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- LISTADO                                                          --}}
    {{-- ================================================================ --}}
    <div class="ui-panel ui-panel--cash-counts-v2-list overflow-hidden">
        <div class="ui-panel__header">
            <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="shrink-0">
                    <h2 class="ui-panel__title">Registros</h2>
                    <p class="ui-panel__subtitle">
                        {{ $cashCounts->total() }} arqueo(s) · Página {{ $cashCounts->currentPage() }} de {{ max(1, $cashCounts->lastPage()) }}
                    </p>
                </div>

                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto">
                    <div class="relative min-w-[16rem] flex-1 lg:min-w-[18rem]">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar número, fecha de apertura o cierre…"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost text-sm" title="Limpiar búsqueda y filtros">
                            <i class="fas fa-eraser"></i>
                        </button>
                        @if ($permFlags['can_destroy'] && ! $cashCounts->isEmpty())
                            <button type="button" wire:click="toggleSelectionMode" class="ui-btn text-sm"
                                :class="$wire.selectionMode ? 'ui-btn-warning' : 'ui-btn-ghost'">
                                <i class="fas {{ $selectionMode ? 'fa-times-circle' : 'fa-check-square' }}"></i>
                                <span>{{ $selectionMode ? 'Cancelar' : 'Seleccionar' }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($permFlags['can_destroy'])
            @if ($selectionMode)
                <div class="flex flex-col gap-3 border-b border-slate-700/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-white">{{ count($selectedCashCountIds) }} arqueo(s) seleccionado(s)</p>
                        <p class="text-xs text-slate-400">La selección aplica a la página actual.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="toggleSelectAllCurrentPage" class="ui-btn ui-btn-ghost text-sm">
                            <i class="fas {{ $allCurrentPageSelected ? 'fa-square-minus' : 'fa-square-check' }}"></i>
                            {{ $allCurrentPageSelected ? 'Limpiar página' : 'Seleccionar página' }}
                        </button>
                    </div>
                </div>
            @endif
        @endif

        {{-- Desktop table --}}
        <div class="hidden md:block">
            <div class="ui-table-wrap border-0 rounded-none">
                <table class="ui-table cash-counts-v2-table ui-table--nowrap-actions" role="table" aria-label="Lista de arqueos de caja">
                    <thead>
                        <tr>
                            @if ($permFlags['can_destroy'])
                                @if ($selectionMode)
                                    <th class="w-12 text-center">
                                        <input type="checkbox" class="rounded border-slate-500 bg-slate-900"
                                            {{ $allCurrentPageSelected ? 'checked' : '' }}
                                            wire:click="toggleSelectAllCurrentPage">
                                    </th>
                                @endif
                            @endif
                            <th class="w-16 text-center">#</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th class="text-right">Inicial</th>
                            <th class="text-right">Final</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cashCounts as $cashCount)
                            <tr wire:key="cash-count-row-{{ $cashCount->id }}">
                                @if ($permFlags['can_destroy'])
                                    @if ($selectionMode)
                                        <td class="text-center">
                                            <input type="checkbox" class="rounded border-slate-500 bg-slate-900"
                                                {{ in_array((string) $cashCount->id, $selectedCashCountIds) ? 'checked' : '' }}
                                                wire:click="toggleCashCountSelection({{ $cashCount->id }})">
                                        </td>
                                    @endif
                                @endif
                                <td class="text-center text-slate-500">{{ $cashCount->id }}</td>
                                <td>
                                    <div class="text-sm font-medium text-slate-100">
                                        {{ \Carbon\Carbon::parse($cashCount->opening_date)->timezone(config('app.timezone'))->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ \Carbon\Carbon::parse($cashCount->opening_date)->timezone(config('app.timezone'))->format('H:i') }}
                                    </div>
                                </td>
                                <td>
                                    @if ($cashCount->closing_date)
                                        <div class="text-sm font-medium text-slate-100">
                                            {{ \Carbon\Carbon::parse($cashCount->closing_date)->timezone(config('app.timezone'))->format('d/m/Y') }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ \Carbon\Carbon::parse($cashCount->closing_date)->timezone(config('app.timezone'))->format('H:i') }}
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="text-right tabular-nums font-medium text-slate-100">
                                    {{ $currencySymbol }} {{ number_format((float) $cashCount->initial_amount, 2) }}
                                </td>
                                <td class="text-right tabular-nums font-medium text-slate-100">
                                    @if ($cashCount->final_amount !== null)
                                        {{ $currencySymbol }} {{ number_format((float) $cashCount->final_amount, 2) }}
                                    @else
                                        <span class="text-xs text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($cashCount->closing_date)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-600 bg-slate-800/80 px-2.5 py-0.5 text-xs font-medium text-slate-300">
                                            <i class="fas fa-lock text-[0.6rem]"></i> Cerrado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/35 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-300">
                                            <i class="fas fa-door-open text-[0.6rem]"></i> Abierto
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="ui-icon-action-row inline-flex flex-nowrap items-center justify-center gap-1.5 md:gap-2">
                                        <a href="{{ route('admin.cash-counts.show', $cashCount->id) }}" class="ui-icon-action ui-icon-action--info" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if (! $cashCount->closing_date && $permFlags['can_edit'])
                                            <a href="{{ route('admin.cash-counts.edit', $cashCount->id) }}" class="ui-icon-action ui-icon-action--primary" title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        @endif
                                        @if (! $cashCount->closing_date && $permFlags['can_close'])
                                            <a href="{{ route('admin.cash-counts.edit', $cashCount->id) }}" class="ui-icon-action ui-icon-action--success" title="Cerrar caja">
                                                <i class="fas fa-lock"></i>
                                            </a>
                                        @endif
                                        @if ($permFlags['can_destroy'])
                                            <button type="button"
                                                wire:click="deleteCashCount({{ $cashCount->id }})"
                                                wire:confirm="¿Eliminar este arqueo? Esta acción no se puede deshacer."
                                                class="ui-icon-action ui-icon-action--danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $selectionMode ? 9 : 8 }}" class="py-12 text-center text-sm text-slate-400">
                                    No hay arqueos con los filtros actuales.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="p-4 md:hidden">
            <div class="grid grid-cols-1 gap-4">
                @forelse ($cashCounts as $cashCount)
                    <article class="rounded-xl border border-slate-600/60 bg-slate-900/70 p-4 shadow-[0_10px_24px_rgba(2,6,23,0.45)]" wire:key="cash-count-card-{{ $cashCount->id }}">
                        @if ($permFlags['can_destroy'] && $selectionMode)
                            <div class="mb-3 flex items-center gap-3 border-b border-slate-700/50 pb-3">
                                <input type="checkbox" class="rounded border-slate-500 bg-slate-900"
                                    {{ in_array((string) $cashCount->id, $selectedCashCountIds) ? 'checked' : '' }}
                                    wire:click="toggleCashCountSelection({{ $cashCount->id }})">
                                <span class="text-sm text-slate-300">Seleccionar este arqueo</span>
                            </div>
                        @endif
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-slate-500">#{{ $cashCount->id }}</p>
                                <p class="text-sm font-semibold text-slate-100">
                                    {{ \Carbon\Carbon::parse($cashCount->opening_date)->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            @if ($cashCount->closing_date)
                                <span class="inline-flex items-center gap-1 rounded-full border border-slate-600 bg-slate-800/80 px-2.5 py-1 text-xs font-medium text-slate-300">
                                    <i class="fas fa-lock text-[0.6rem]"></i> Cerrado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/35 bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-300">
                                    <i class="fas fa-door-open text-[0.6rem]"></i> Abierto
                                </span>
                            @endif
                        </div>
                        <div class="mb-3 grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <p class="text-xs text-slate-500">Inicial</p>
                                <p class="font-semibold text-slate-100">{{ $currencySymbol }} {{ number_format((float) $cashCount->initial_amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Final</p>
                                <p class="font-semibold text-slate-100">
                                    @if ($cashCount->final_amount !== null)
                                        {{ $currencySymbol }} {{ number_format((float) $cashCount->final_amount, 2) }}
                                    @else
                                        <span class="text-xs text-slate-500">—</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="ui-icon-action-row flex flex-nowrap items-center justify-end gap-1.5 border-t border-slate-700/60 pt-3">
                            <a href="{{ route('admin.cash-counts.show', $cashCount->id) }}" class="ui-icon-action ui-icon-action--info text-sm" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if (! $cashCount->closing_date && $permFlags['can_edit'])
                                <a href="{{ route('admin.cash-counts.edit', $cashCount->id) }}" class="ui-icon-action ui-icon-action--primary text-sm" title="Editar">
                                    <i class="fas fa-pen"></i>
                                </a>
                            @endif
                            @if (! $cashCount->closing_date && $permFlags['can_close'])
                                <a href="{{ route('admin.cash-counts.edit', $cashCount->id) }}" class="ui-icon-action ui-icon-action--success text-sm" title="Cerrar caja">
                                    <i class="fas fa-lock"></i>
                                </a>
                            @endif
                            @if ($permFlags['can_destroy'])
                                <button type="button"
                                    wire:click="deleteCashCount({{ $cashCount->id }})"
                                    wire:confirm="¿Eliminar este arqueo? Esta acción no se puede deshacer."
                                    class="ui-icon-action ui-icon-action--danger text-sm" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="col-span-full py-10 text-center text-sm text-slate-400">No hay arqueos con los filtros actuales.</div>
                @endforelse
            </div>
        </div>

        {{-- Pagination --}}
        @if ($cashCounts->hasPages())
            <div class="border-t border-slate-700/50 px-4 py-3">
                <x-ui.pagination :paginator="$cashCounts" scroll-into-view=".ui-panel--cash-counts-v2-list" />
            </div>
        @endif
    </div>

</div>
