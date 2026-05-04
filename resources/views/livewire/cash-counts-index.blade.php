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
            <div class="flex flex-wrap items-center gap-2">
                @if ($permFlags['can_report'])
                    <a href="{{ route('admin.cash-counts.report') }}" target="_blank" rel="noopener"
                        class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]">
                        <i class="fas fa-file-pdf"></i> Reporte PDF
                    </a>
                @endif
                @if (! $currentCashCount && $permFlags['can_create'])
                    <a href="{{ route('admin.cash-counts.create') }}" class="ui-btn ui-btn-primary text-sm md:py-2.5 md:px-5 md:text-[0.95rem]">
                        <i class="fas fa-plus"></i> Abrir caja
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- STATS                                                            --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-4">
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
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-end lg:grid-cols-4">
                {{-- Estado --}}
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Estado</label>
                    <select wire:model.live="status" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                        <option value="">Todos</option>
                        <option value="open">Abierto</option>
                        <option value="closed">Cerrado</option>
                    </select>
                </div>
                {{-- Desde --}}
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Desde</label>
                    <input type="date" wire:model.live="dateFrom" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" style="color-scheme: dark;">
                </div>
                {{-- Hasta --}}
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Hasta</label>
                    <input type="date" wire:model.live="dateTo" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" style="color-scheme: dark;">
                </div>
                {{-- Limpiar --}}
                <div>
                    <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost w-full text-sm md:py-2.5 md:px-5 md:text-[0.95rem]">
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
                        <button type="button" wire:click="openBulkDeleteModal" class="ui-btn ui-btn-danger text-sm"
                            :disabled="{{ count($selectedCashCountIds) === 0 ? 'true' : 'false' }}">
                            <i class="fas fa-trash-alt"></i> Eliminar seleccionados
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
                                        <button type="button" wire:click="openDetailModal({{ $cashCount->id }})" class="ui-icon-action ui-icon-action--info" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
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
                                                wire:click="openDeleteModal({{ $cashCount->id }}, '#{{ str_pad($cashCount->id, 4, '0', STR_PAD_LEFT) }}')"
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
                            <button type="button" wire:click="openDetailModal({{ $cashCount->id }})" class="ui-icon-action ui-icon-action--info text-sm" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
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
                                    wire:click="openDeleteModal({{ $cashCount->id }}, '#{{ str_pad($cashCount->id, 4, '0', STR_PAD_LEFT) }}')"
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

    {{-- ================================================================ --}}
    {{-- MODAL: CONFIRMAR ELIMINACIÓN                                     --}}
    {{-- ================================================================ --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-[#020617]/90 p-4 backdrop-blur-md"
            wire:click.self="closeDeleteModal" x-data x-on:keydown.escape.window="$wire.closeDeleteModal()">
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75),inset_0_1px_0_rgba(255,255,255,0.06)]">
                <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Eliminar este arqueo?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se eliminará el arqueo <span class="font-medium text-white">{{ $deleteTargetName }}</span>.
                                Esta acción no se puede deshacer.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmDeleteCashCount" class="ui-btn ui-btn-danger text-sm">
                        <i class="fas fa-trash-alt mr-1.5"></i> Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- MODAL: BORRADO MASIVO                                          --}}
    {{-- ================================================================ --}}
    @if ($showBulkDeleteModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-[#020617]/90 p-4 backdrop-blur-md"
            wire:click.self="closeBulkDeleteModal" x-data x-on:keydown.escape.window="$wire.closeBulkDeleteModal()">
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75),inset_0_1px_0_rgba(255,255,255,0.06)]">
                <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Eliminar arqueos seleccionados?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se intentará eliminar <span class="font-medium text-white">{{ count($selectedCashCountIds) }} arqueo(s)</span>.
                                Los que estén abiertos o tengan registros asociados no se eliminarán.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeBulkDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmBulkDelete" class="ui-btn ui-btn-danger text-sm">
                        <i class="fas fa-trash-alt mr-1.5"></i> Sí, eliminar seleccionados
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- MODAL: DETALLE DEL ARQUEO                                      --}}
    {{-- ================================================================ --}}
    @if ($showDetailModal && $detailCashCountId)
        @php
            $cc = $detailGeneralInfo;
            $openDate = isset($cc['opening_date']) ? \Carbon\Carbon::parse($cc['opening_date'])->timezone(config('app.timezone'))->format('d/m/Y H:i') : '—';
            $closeDate = isset($cc['closing_date']) ? \Carbon\Carbon::parse($cc['closing_date'])->timezone(config('app.timezone'))->format('d/m/Y H:i') : 'En curso';
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            wire:click.self="closeDetailModal" x-data x-on:keydown.escape.window="$wire.closeDetailModal()">
            <div class="relative max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-2xl border border-cyan-500/20 bg-slate-900/95 shadow-[0_0_40px_rgba(34,211,238,0.12)]">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-700/80 bg-gradient-to-r from-cyan-500/10 to-indigo-600/10 px-5 py-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-500/20 text-cyan-300">
                            <i class="fas fa-cash-register text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-semibold text-white truncate">
                                Arqueo #{{ str_pad($cc['id'] ?? 0, 4, '0', STR_PAD_LEFT) }}
                            </h3>
                            <p class="text-xs text-slate-400 truncate">
                                {{ $openDate }} → {{ $closeDate }}
                                @if (isset($cc['observations']) && $cc['observations'])
                                    · {{ $cc['observations'] }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="rounded-lg border border-slate-600 bg-slate-800/80 px-3 py-1.5 text-slate-300 transition hover:bg-slate-700 hover:text-white shrink-0">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="max-h-[calc(90vh-8rem)] overflow-y-auto p-5">
                    {{-- Info general cards --}}
                    <div class="mb-5 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Monto inicial</p>
                            <p class="font-semibold text-slate-100">{{ $currencySymbol }} {{ number_format($cc['initial_amount'] ?? 0, 2) }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Ingresos</p>
                            <p class="font-semibold text-emerald-300">{{ $currencySymbol }} {{ number_format($cc['total_income'] ?? 0, 2) }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Egresos</p>
                            <p class="font-semibold text-rose-300">{{ $currencySymbol }} {{ number_format($cc['total_expenses'] ?? 0, 2) }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Balance</p>
                            <p class="font-semibold text-cyan-300">{{ $currencySymbol }} {{ number_format($cc['current_balance'] ?? 0, 2) }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Movimientos</p>
                            <p class="font-semibold text-slate-100">{{ $cc['movements_count'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Estado</p>
                            <p class="font-semibold {{ isset($cc['closing_date']) ? 'text-slate-300' : 'text-emerald-300' }}">
                                {{ isset($cc['closing_date']) ? 'Cerrado' : 'Abierto' }}
                            </p>
                        </div>
                    </div>

                    {{-- Tabs --}}
                    <div class="mb-4 border-b border-slate-700/60">
                        <nav class="flex gap-1 overflow-x-auto pb-1" aria-label="Tabs">
                            @foreach ([
                                ['key' => 'clientes', 'label' => 'Clientes', 'icon' => 'fa-users'],
                                ['key' => 'ventas', 'label' => 'Ventas', 'icon' => 'fa-chart-bar'],
                                ['key' => 'pagos', 'label' => 'Pagos', 'icon' => 'fa-credit-card'],
                                ['key' => 'compras', 'label' => 'Compras', 'icon' => 'fa-shopping-bag'],
                                ['key' => 'productos', 'label' => 'Productos', 'icon' => 'fa-box'],
                                ['key' => 'pedidos', 'label' => 'Pedidos', 'icon' => 'fa-clipboard-list'],
                            ] as $t)
                                <button type="button" wire:click="setDetailTab('{{ $t['key'] }}')"
                                    class="shrink-0 rounded-t-lg px-3 py-2 text-xs font-semibold transition sm:text-sm
                                        {{ $detailActiveTab === $t['key']
                                            ? 'border-b-2 border-cyan-400 bg-cyan-500/10 text-cyan-300'
                                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' }}">
                                    <i class="fas {{ $t['icon'] }} mr-1 text-[0.65rem]"></i>{{ $t['label'] }}
                                </button>
                            @endforeach
                        </nav>
                    </div>

                    {{-- ==================== TAB CLIENTES ==================== --}}
                    @if ($detailActiveTab === 'clientes')
                        @php
                            $cCurrent = $detailCustomerStats['current'] ?? [];
                            $cComp = $detailCustomerStats['comparison'] ?? [];
                        @endphp
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                <div class="rounded-lg border border-blue-500/20 bg-blue-500/10 p-3">
                                    <p class="text-xs text-blue-300/80">Clientes únicos</p>
                                    <p class="text-xl font-bold text-blue-200">{{ $cCurrent['unique_customers'] ?? 0 }}</p>
                                    @if (isset($cComp['unique_customers']))
                                        <p class="text-[10px] {{ $cComp['unique_customers']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $cComp['unique_customers']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $cComp['unique_customers']['is_positive'] ? '+' : '' }}{{ number_format($cComp['unique_customers']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3">
                                    <p class="text-xs text-emerald-300/80">Ventas totales</p>
                                    <p class="text-xl font-bold text-emerald-200">{{ $currencySymbol }} {{ number_format($cCurrent['total_sales'] ?? 0, 2) }}</p>
                                    @if (isset($cComp['total_sales']))
                                        <p class="text-[10px] {{ $cComp['total_sales']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $cComp['total_sales']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $cComp['total_sales']['is_positive'] ? '+' : '' }}{{ number_format($cComp['total_sales']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-rose-500/20 bg-rose-500/10 p-3">
                                    <p class="text-xs text-rose-300/80">Deudas pendientes</p>
                                    <p class="text-xl font-bold text-rose-200">{{ $currencySymbol }} {{ number_format($cCurrent['total_debt'] ?? 0, 2) }}</p>
                                    @if (isset($cComp['total_debt']))
                                        <p class="text-[10px] {{ $cComp['total_debt']['is_positive'] ? 'text-rose-400' : 'text-emerald-400' }}">
                                            <i class="fas fa-arrow-{{ $cComp['total_debt']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $cComp['total_debt']['is_positive'] ? '+' : '' }}{{ number_format($cComp['total_debt']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-purple-500/20 bg-purple-500/10 p-3">
                                    <p class="text-xs text-purple-300/80">Promedio por cliente</p>
                                    <p class="text-xl font-bold text-purple-200">{{ $currencySymbol }} {{ number_format($cCurrent['average_per_customer'] ?? 0, 2) }}</p>
                                    @if (isset($cComp['average_per_customer']))
                                        <p class="text-[10px] {{ $cComp['average_per_customer']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $cComp['average_per_customer']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $cComp['average_per_customer']['is_positive'] ? '+' : '' }}{{ number_format($cComp['average_per_customer']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="ui-table-wrap rounded-xl border border-slate-700/60">
                                <table class="ui-table text-sm">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Teléfono</th>
                                            <th class="text-right">Total compras</th>
                                            <th class="text-right">Deuda</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($cCurrent['customers_data'] ?? [] as $customer)
                                            <tr>
                                                <td class="font-medium text-slate-100">{{ $customer['name'] }}</td>
                                                <td class="text-slate-400">{{ $customer['phone'] ?? '—' }}</td>
                                                <td class="text-right tabular-nums text-emerald-300">{{ $currencySymbol }} {{ number_format($customer['total_purchases'] ?? 0, 2) }}</td>
                                                <td class="text-right tabular-nums {{ ($customer['total_debt'] ?? 0) > 0 ? 'text-rose-300' : 'text-emerald-300' }}">{{ $currencySymbol }} {{ number_format($customer['total_debt'] ?? 0, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-8 text-center text-slate-400">No hay clientes registrados en este arqueo.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- ==================== TAB VENTAS ==================== --}}
                    @if ($detailActiveTab === 'ventas')
                        @php
                            $sCurrent = $detailSalesStats['current'] ?? [];
                            $sComp = $detailSalesStats['comparison'] ?? [];
                        @endphp
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3">
                                    <p class="text-xs text-emerald-300/80">Ventas totales</p>
                                    <p class="text-xl font-bold text-emerald-200">{{ $currencySymbol }} {{ number_format($sCurrent['total_sales'] ?? 0, 2) }}</p>
                                    @if (isset($sComp['total_sales']))
                                        <p class="text-[10px] {{ $sComp['total_sales']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $sComp['total_sales']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $sComp['total_sales']['is_positive'] ? '+' : '' }}{{ number_format($sComp['total_sales']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-blue-500/20 bg-blue-500/10 p-3">
                                    <p class="text-xs text-blue-300/80">Balance teórico</p>
                                    <p class="text-xl font-bold text-blue-200">{{ $currencySymbol }} {{ number_format($sCurrent['theoretical_balance'] ?? 0, 2) }}</p>
                                    @if (isset($sComp['theoretical_balance']))
                                        <p class="text-[10px] {{ $sComp['theoretical_balance']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $sComp['theoretical_balance']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $sComp['theoretical_balance']['is_positive'] ? '+' : '' }}{{ number_format($sComp['theoretical_balance']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-yellow-500/20 bg-yellow-500/10 p-3">
                                    <p class="text-xs text-yellow-300/80">Promedio por venta</p>
                                    <p class="text-xl font-bold text-yellow-200">{{ $currencySymbol }} {{ number_format($sCurrent['average_per_sale'] ?? 0, 2) }}</p>
                                    @if (isset($sComp['average_per_sale']))
                                        <p class="text-[10px] {{ $sComp['average_per_sale']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $sComp['average_per_sale']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $sComp['average_per_sale']['is_positive'] ? '+' : '' }}{{ number_format($sComp['average_per_sale']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-purple-500/20 bg-purple-500/10 p-3">
                                    <p class="text-xs text-purple-300/80">Balance real</p>
                                    <p class="text-xl font-bold text-purple-200">{{ $currencySymbol }} {{ number_format($sCurrent['real_balance'] ?? 0, 2) }}</p>
                                    @if (isset($sComp['real_balance']))
                                        <p class="text-[10px] {{ $sComp['real_balance']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $sComp['real_balance']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $sComp['real_balance']['is_positive'] ? '+' : '' }}{{ number_format($sComp['real_balance']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="ui-table-wrap rounded-xl border border-slate-700/60">
                                <table class="ui-table text-sm">
                                    <thead>
                                        <tr>
                                            <th>Factura</th>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th class="text-right">Total</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($sCurrent['sales_data'] ?? [] as $sale)
                                            <tr>
                                                <td class="font-mono text-slate-300">{{ $sale['invoice_number'] }}</td>
                                                <td class="text-slate-400">{{ isset($sale['sale_date']) ? \Carbon\Carbon::parse($sale['sale_date'])->format('d/m/Y') : '—' }}</td>
                                                <td class="font-medium text-slate-100">{{ $sale['customer_name'] ?? '—' }}</td>
                                                <td class="text-right tabular-nums text-emerald-300">{{ $currencySymbol }} {{ number_format($sale['total_amount'] ?? 0, 2) }}</td>
                                                <td>
                                                    @if (($sale['payment_status'] ?? '') === 'Pagado')
                                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-300"><i class="fas fa-check-circle text-[0.6rem]"></i> Pagado</span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 rounded-full border border-rose-500/30 bg-rose-500/10 px-2 py-0.5 text-xs font-medium text-rose-300"><i class="fas fa-clock text-[0.6rem]"></i> Pendiente</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="py-8 text-center text-slate-400">No hay ventas en este arqueo.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- ==================== TAB PAGOS ==================== --}}
                    @if ($detailActiveTab === 'pagos')
                        @php
                            $pCurrent = $detailPaymentsStats['current'] ?? [];
                            $pComp = $detailPaymentsStats['comparison'] ?? [];
                        @endphp
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                <div class="rounded-lg border border-teal-500/20 bg-teal-500/10 p-3">
                                    <p class="text-xs text-teal-300/80">Pagos totales</p>
                                    <p class="text-xl font-bold text-teal-200">{{ $currencySymbol }} {{ number_format($pCurrent['total_payments'] ?? 0, 2) }}</p>
                                    @if (isset($pComp['total_payments']))
                                        <p class="text-[10px] {{ $pComp['total_payments']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $pComp['total_payments']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $pComp['total_payments']['is_positive'] ? '+' : '' }}{{ number_format($pComp['total_payments']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-cyan-500/20 bg-cyan-500/10 p-3">
                                    <p class="text-xs text-cyan-300/80">Transacciones</p>
                                    <p class="text-xl font-bold text-cyan-200">{{ $pCurrent['payments_count'] ?? 0 }}</p>
                                    @if (isset($pComp['payments_count']))
                                        <p class="text-[10px] {{ $pComp['payments_count']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $pComp['payments_count']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $pComp['payments_count']['is_positive'] ? '+' : '' }}{{ number_format($pComp['payments_count']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3">
                                    <p class="text-xs text-emerald-300/80">Promedio por pago</p>
                                    <p class="text-xl font-bold text-emerald-200">{{ $currencySymbol }} {{ number_format($pCurrent['average_per_payment'] ?? 0, 2) }}</p>
                                    @if (isset($pComp['average_per_payment']))
                                        <p class="text-[10px] {{ $pComp['average_per_payment']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $pComp['average_per_payment']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $pComp['average_per_payment']['is_positive'] ? '+' : '' }}{{ number_format($pComp['average_per_payment']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-blue-500/20 bg-blue-500/10 p-3">
                                    <p class="text-xs text-blue-300/80">Deuda restante</p>
                                    <p class="text-xl font-bold text-blue-200">{{ $currencySymbol }} {{ number_format($pCurrent['remaining_debt'] ?? 0, 2) }}</p>
                                    @if (isset($pComp['remaining_debt']))
                                        <p class="text-[10px] {{ $pComp['remaining_debt']['is_positive'] ? 'text-rose-400' : 'text-emerald-400' }}">
                                            <i class="fas fa-arrow-{{ $pComp['remaining_debt']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $pComp['remaining_debt']['is_positive'] ? '+' : '' }}{{ number_format($pComp['remaining_debt']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="ui-table-wrap rounded-xl border border-slate-700/60">
                                <table class="ui-table text-sm">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th class="text-right">Monto</th>
                                            <th class="text-right">Deuda restante</th>
                                            <th>Nota</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pCurrent['payments_data'] ?? [] as $payment)
                                            <tr>
                                                <td class="text-slate-400">{{ isset($payment['payment_date']) ? \Carbon\Carbon::parse($payment['payment_date'])->format('d/m/Y H:i') : '—' }}</td>
                                                <td class="font-medium text-slate-100">{{ $payment['customer_name'] ?? '—' }}</td>
                                                <td class="text-right tabular-nums text-emerald-300">{{ $currencySymbol }} {{ number_format($payment['payment_amount'] ?? 0, 2) }}</td>
                                                <td class="text-right tabular-nums {{ ($payment['remaining_debt'] ?? 0) > 0 ? 'text-rose-300' : 'text-emerald-300' }}">{{ $currencySymbol }} {{ number_format($payment['remaining_debt'] ?? 0, 2) }}</td>
                                                <td class="text-slate-400">{{ $payment['notes'] ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="py-8 text-center text-slate-400">No hay pagos en este arqueo.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- ==================== TAB COMPRAS ==================== --}}
                    @if ($detailActiveTab === 'compras')
                        @php
                            $purCurrent = $detailPurchasesStats['current'] ?? [];
                            $purComp = $detailPurchasesStats['comparison'] ?? [];
                        @endphp
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                <div class="rounded-lg border border-orange-500/20 bg-orange-500/10 p-3">
                                    <p class="text-xs text-orange-300/80">Compras totales</p>
                                    <p class="text-xl font-bold text-orange-200">{{ $currencySymbol }} {{ number_format($purCurrent['total_purchases'] ?? 0, 2) }}</p>
                                    @if (isset($purComp['total_purchases']))
                                        <p class="text-[10px] {{ $purComp['total_purchases']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $purComp['total_purchases']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $purComp['total_purchases']['is_positive'] ? '+' : '' }}{{ number_format($purComp['total_purchases']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-indigo-500/20 bg-indigo-500/10 p-3">
                                    <p class="text-xs text-indigo-300/80">Cantidad</p>
                                    <p class="text-xl font-bold text-indigo-200">{{ $purCurrent['purchases_count'] ?? 0 }}</p>
                                    @if (isset($purComp['purchases_count']))
                                        <p class="text-[10px] {{ $purComp['purchases_count']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $purComp['purchases_count']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $purComp['purchases_count']['is_positive'] ? '+' : '' }}{{ number_format($purComp['purchases_count']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-yellow-500/20 bg-yellow-500/10 p-3">
                                    <p class="text-xs text-yellow-300/80">Promedio</p>
                                    <p class="text-xl font-bold text-yellow-200">{{ $currencySymbol }} {{ number_format($purCurrent['average_per_purchase'] ?? 0, 2) }}</p>
                                    @if (isset($purComp['average_per_purchase']))
                                        <p class="text-[10px] {{ $purComp['average_per_purchase']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $purComp['average_per_purchase']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $purComp['average_per_purchase']['is_positive'] ? '+' : '' }}{{ number_format($purComp['average_per_purchase']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3">
                                    <p class="text-xs text-emerald-300/80">Margen (%)</p>
                                    <p class="text-xl font-bold text-emerald-200">{{ number_format($purCurrent['margin_percentage'] ?? 0, 1) }}%</p>
                                    @if (isset($purComp['margin_percentage']))
                                        <p class="text-[10px] {{ $purComp['margin_percentage']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $purComp['margin_percentage']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $purComp['margin_percentage']['is_positive'] ? '+' : '' }}{{ number_format($purComp['margin_percentage']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="ui-table-wrap rounded-xl border border-slate-700/60">
                                <table class="ui-table text-sm">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th class="text-center">Únicos</th>
                                            <th class="text-center">Totales</th>
                                            <th class="text-right">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($purCurrent['purchases_data'] ?? [] as $purchase)
                                            <tr>
                                                <td class="text-slate-400">{{ isset($purchase['purchase_date']) ? \Carbon\Carbon::parse($purchase['purchase_date'])->format('d/m/Y') : '—' }}</td>
                                                <td class="text-center text-slate-100">{{ $purchase['unique_products'] ?? 0 }}</td>
                                                <td class="text-center text-slate-100">{{ $purchase['total_products'] ?? 0 }}</td>
                                                <td class="text-right tabular-nums text-blue-300">{{ $currencySymbol }} {{ number_format($purchase['total_amount'] ?? 0, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="py-8 text-center text-slate-400">No hay compras en este arqueo.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- ==================== TAB PRODUCTOS ==================== --}}
                    @if ($detailActiveTab === 'productos')
                        @php
                            $prodCurrent = $detailProductsStats['current'] ?? [];
                            $prodData = collect($prodCurrent['products_data'] ?? []);
                            $prodTotal = $prodData->count();
                            $prodMaxPage = max(1, (int) ceil($prodTotal / $detailProductsPerPage));
                            $prodOffset = ($detailProductsPage - 1) * $detailProductsPerPage;
                            $prodSlice = $prodData->slice($prodOffset, $detailProductsPerPage)->values()->all();
                        @endphp
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-2">
                                <div class="rounded-lg border border-purple-500/20 bg-purple-500/10 p-3">
                                    <p class="text-xs text-purple-300/80">Vendidos (totales / únicos)</p>
                                    <p class="text-xl font-bold text-purple-200">{{ $prodCurrent['total_quantity_sold'] ?? 0 }} / {{ $prodCurrent['unique_products_sold'] ?? 0 }}</p>
                                </div>
                                <div class="rounded-lg border border-blue-500/20 bg-blue-500/10 p-3">
                                    <p class="text-xs text-blue-300/80">Valor inventario (costo)</p>
                                    <p class="text-xl font-bold text-blue-200">{{ $currencySymbol }} {{ number_format($prodCurrent['inventory_value_cost'] ?? 0, 2) }}</p>
                                </div>
                            </div>
                            <div class="ui-table-wrap rounded-xl border border-slate-700/60">
                                <table class="ui-table text-sm">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center">Vendidos</th>
                                            <th class="text-right">Ingresos</th>
                                            <th class="text-right">Costo</th>
                                            <th class="text-right">P. compra</th>
                                            <th class="text-right">P. venta</th>
                                            <th class="text-right">Margen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($prodSlice as $row)
                                            <tr>
                                                <td class="font-medium text-slate-100">{{ $row['product_name'] }}</td>
                                                <td class="text-center"><span class="inline-flex items-center rounded-full bg-slate-700/60 px-2 py-0.5 text-xs text-slate-300">{{ $row['stock'] ?? 0 }}</span></td>
                                                <td class="text-center text-slate-100">{{ $row['quantity_sold'] ?? 0 }}</td>
                                                <td class="text-right tabular-nums text-emerald-300">{{ $currencySymbol }} {{ number_format($row['income'] ?? 0, 2) }}</td>
                                                <td class="text-right tabular-nums text-blue-300">{{ $currencySymbol }} {{ number_format($row['cost'] ?? 0, 2) }}</td>
                                                <td class="text-right tabular-nums text-slate-300">{{ $currencySymbol }} {{ number_format($row['purchase_price'] ?? 0, 2) }}</td>
                                                <td class="text-right tabular-nums text-slate-300">{{ $currencySymbol }} {{ number_format($row['sale_price'] ?? 0, 2) }}</td>
                                                <td class="text-right">
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ ($row['margin_percentage'] ?? 0) >= 0 ? 'bg-emerald-500/10 text-emerald-300' : 'bg-rose-500/10 text-rose-300' }}">
                                                        {{ number_format($row['margin_percentage'] ?? 0, 1) }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="8" class="py-8 text-center text-slate-400">No hay productos vendidos en este arqueo.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if ($prodMaxPage > 1)
                                <div class="flex items-center justify-between px-2">
                                    <button type="button" wire:click="detailProductsPrevPage" class="ui-btn ui-btn-ghost text-xs" @disabled($detailProductsPage <= 1)>Anterior</button>
                                    <span class="text-xs text-slate-400">{{ $detailProductsPage }} / {{ $prodMaxPage }} · {{ $prodTotal }} items</span>
                                    <button type="button" wire:click="detailProductsNextPage" class="ui-btn ui-btn-ghost text-xs" @disabled($detailProductsPage >= $prodMaxPage)>Siguiente</button>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ==================== TAB PEDIDOS ==================== --}}
                    @if ($detailActiveTab === 'pedidos')
                        @php
                            $ordCurrent = $detailOrdersStats['current'] ?? [];
                            $ordComp = $detailOrdersStats['comparison'] ?? [];
                            $ordData = collect($ordCurrent['orders_data'] ?? []);
                            $ordTotal = $ordData->count();
                            $ordMaxPage = max(1, (int) ceil($ordTotal / $detailOrdersPerPage));
                            $ordOffset = ($detailOrdersPage - 1) * $detailOrdersPerPage;
                            $ordSlice = $ordData->slice($ordOffset, $detailOrdersPerPage)->values()->all();
                        @endphp
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                <div class="rounded-lg border border-red-500/20 bg-red-500/10 p-3">
                                    <p class="text-xs text-red-300/80">Total pedidos</p>
                                    <p class="text-xl font-bold text-red-200">{{ $ordCurrent['total_orders'] ?? 0 }}</p>
                                    @if (isset($ordComp['total_orders']))
                                        <p class="text-[10px] {{ $ordComp['total_orders']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $ordComp['total_orders']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $ordComp['total_orders']['is_positive'] ? '+' : '' }}{{ number_format($ordComp['total_orders']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-yellow-500/20 bg-yellow-500/10 p-3">
                                    <p class="text-xs text-yellow-300/80">Pendientes</p>
                                    <p class="text-xl font-bold text-yellow-200">{{ $ordCurrent['pending'] ?? 0 }}</p>
                                    @if (isset($ordComp['pending']))
                                        <p class="text-[10px] {{ $ordComp['pending']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $ordComp['pending']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $ordComp['pending']['is_positive'] ? '+' : '' }}{{ number_format($ordComp['pending']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3">
                                    <p class="text-xs text-emerald-300/80">Completados</p>
                                    <p class="text-xl font-bold text-emerald-200">{{ $ordCurrent['completed'] ?? 0 }}</p>
                                    @if (isset($ordComp['completed']))
                                        <p class="text-[10px] {{ $ordComp['completed']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $ordComp['completed']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $ordComp['completed']['is_positive'] ? '+' : '' }}{{ number_format($ordComp['completed']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-blue-500/20 bg-blue-500/10 p-3">
                                    <p class="text-xs text-blue-300/80">Valor total</p>
                                    <p class="text-xl font-bold text-blue-200">{{ $currencySymbol }} {{ number_format($ordCurrent['total_value'] ?? 0, 2) }}</p>
                                    @if (isset($ordComp['total_value']))
                                        <p class="text-[10px] {{ $ordComp['total_value']['is_positive'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                            <i class="fas fa-arrow-{{ $ordComp['total_value']['is_positive'] ? 'up' : 'down' }}"></i>
                                            {{ $ordComp['total_value']['is_positive'] ? '+' : '' }}{{ number_format($ordComp['total_value']['percentage'] ?? 0, 1) }}% vs ant.
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="ui-table-wrap rounded-xl border border-slate-700/60">
                                <table class="ui-table text-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th class="text-center">Únicos</th>
                                            <th class="text-center">Totales</th>
                                            <th class="text-right">Total</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($ordSlice as $row)
                                            <tr>
                                                <td class="font-mono text-slate-300">#{{ $row['id'] }}</td>
                                                <td class="text-slate-400">{{ isset($row['order_date']) ? \Carbon\Carbon::parse($row['order_date'])->format('d/m/Y H:i') : '—' }}</td>
                                                <td class="font-medium text-slate-100">{{ $row['customer_name'] ?? '—' }}</td>
                                                <td class="text-center text-slate-100">{{ $row['unique_products'] ?? 0 }}</td>
                                                <td class="text-center text-slate-100">{{ $row['total_products'] ?? 0 }}</td>
                                                <td class="text-right tabular-nums text-emerald-300">{{ $currencySymbol }} {{ number_format($row['total_amount'] ?? 0, 2) }}</td>
                                                <td>
                                                    @if (($row['status'] ?? '') === 'processed')
                                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-300"><i class="fas fa-check-circle text-[0.6rem]"></i> Completado</span>
                                                    @elseif (($row['status'] ?? '') === 'pending')
                                                        <span class="inline-flex items-center gap-1 rounded-full border border-yellow-500/30 bg-yellow-500/10 px-2 py-0.5 text-xs font-medium text-yellow-300"><i class="fas fa-clock text-[0.6rem]"></i> Pendiente</span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 rounded-full border border-rose-500/30 bg-rose-500/10 px-2 py-0.5 text-xs font-medium text-rose-300"><i class="fas fa-times-circle text-[0.6rem]"></i> Cancelado</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="py-8 text-center text-slate-400">No hay pedidos en este arqueo.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if ($ordMaxPage > 1)
                                <div class="flex items-center justify-between px-2">
                                    <button type="button" wire:click="detailOrdersPrevPage" class="ui-btn ui-btn-ghost text-xs" @disabled($detailOrdersPage <= 1)>Anterior</button>
                                    <span class="text-xs text-slate-400">{{ $detailOrdersPage }} / {{ $ordMaxPage }} · {{ $ordTotal }} items</span>
                                    <button type="button" wire:click="detailOrdersNextPage" class="ui-btn ui-btn-ghost text-xs" @disabled($detailOrdersPage >= $ordMaxPage)>Siguiente</button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex flex-shrink-0 justify-end border-t border-slate-700/80 bg-slate-950/50 px-4 py-3 sm:px-6">
                    <button type="button" wire:click="closeDetailModal" class="ui-btn ui-btn-primary text-sm">
                        <i class="fas fa-times mr-1.5"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
