@php
    $inputBase = 'w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500';
    $labelBase = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400';

    $currencySymbol = $currency->symbol ?? '$';
    $sSince = $statsSinceCashOpen;
    $sWeek = $statsThisWeek;
    $sToday = $statsToday;
    $wPct = $weekPercentages;
@endphp

<div class="space-y-6" wire:key="sales-index">

    {{-- ================================================================ --}}
    {{-- HEADER + STATS                                                   --}}
    {{-- ================================================================ --}}
    <div class="ui-panel overflow-hidden">
        <div class="ui-panel__header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Ventas</h1>
                <p class="ui-panel__subtitle">Control de ventas, ganancias y estadísticas en tiempo real.</p>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2">
                @if ($permissions['can_report'])
                    <a href="{{ route('admin.sales.report') }}" target="_blank" rel="noopener"
                        class="ui-btn ui-btn-ghost text-sm">
                        <i class="fas fa-file-pdf"></i> Reporte PDF
                    </a>
                @endif
                @if ($hasCashOpen)
                    @if ($permissions['can_create'])
                        <a href="{{ route('admin.sales.create') }}" class="ui-btn ui-btn-primary text-sm">
                            <i class="fas fa-plus"></i> Nueva venta
                        </a>
                    @endif
                @else
                    @if ($permissions['can_create'])
                        <a href="{{ route('admin.cash-counts.create') }}" class="ui-btn ui-btn-danger text-sm">
                            <i class="fas fa-cash-register"></i> Abrir caja
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        {{-- Total Ventas (since cash open) --}}
        <x-ui.stat-card variant="info" icon="fas fa-cash-register"
            trend="{{ $wPct['salesPercentage'] ?? 0 }}% de la sesión"
            label="Total Ventas"
            :value="$currencySymbol . ' ' . number_format((float) ($sSince['totalSales'] ?? 0), 2)"
            meta="Desde apertura de caja" />

        {{-- Ganancias (since cash open) --}}
        <x-ui.stat-card variant="success" icon="fas fa-chart-line"
            trend="{{ $wPct['profitPercentage'] ?? 0 }}% de la sesión"
            label="Ganancias"
            :value="$currencySymbol . ' ' . number_format((float) ($sSince['totalProfit'] ?? 0), 2)"
            meta="Cobros − Costo mercancía" />

        {{-- Ventas Realizadas --}}
        <x-ui.stat-card variant="warning" icon="fas fa-receipt"
            trend="{{ $wPct['salesCountPercentage'] ?? 0 }}% de la sesión"
            label="Ventas realizadas"
            :value="number_format((int) ($sSince['salesCount'] ?? 0))"
            meta="Desde apertura de caja" />

        {{-- Productos Vendidos --}}
        <x-ui.stat-card variant="danger" icon="fas fa-boxes-stacked"
            trend="Hoy: {{ number_format((int) ($sToday['productsQty'] ?? 0)) }}"
            label="Productos vendidos"
            :value="number_format((int) ($sSince['productsQty'] ?? 0))"
            meta="Semana: {{ number_format((int) ($sWeek['productsQty'] ?? 0)) }} uds." />
    </div>

    {{-- ================================================================ --}}
    {{-- FILTROS                                                          --}}
    {{-- ================================================================ --}}
    <div class="ui-panel" x-data="{ showFilters: false }">
        <div class="ui-panel__header flex items-center justify-between gap-3">
            <div>
                <h2 class="ui-panel__title">Filtros</h2>
                <p class="ui-panel__subtitle">Búsqueda por cliente, fecha, monto o producto.</p>
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
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-end xl:grid-cols-[minmax(0,1.5fr)_repeat(2,minmax(0,0.9fr))_repeat(2,minmax(0,0.85fr))_auto] 2xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)_repeat(2,minmax(0,0.85fr))_repeat(2,minmax(0,0.7fr))_auto]">
                {{-- Buscar --}}
                <div class="min-w-0">
                    <label class="{{ $labelBase }}">Buscar</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search"
                            placeholder="Cliente, fecha, monto o producto…"
                            class="{{ $inputBase }} pl-10"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                    </div>
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
                {{-- Monto mín. --}}
                <div>
                    <label class="{{ $labelBase }}">Monto mín.</label>
                    <input type="number" wire:model.live="amountMin" min="0" step="0.01" placeholder="0.00"
                        class="{{ $inputBase }}">
                </div>
                {{-- Monto máx. --}}
                <div>
                    <label class="{{ $labelBase }}">Monto máx.</label>
                    <input type="number" wire:model.live="amountMax" min="0" step="0.01" placeholder="0.00"
                        class="{{ $inputBase }}">
                </div>
                {{-- Limpiar --}}
                <div class="sm:col-span-2 xl:col-span-1 xl:col-start-6 xl:row-start-1 2xl:col-start-7 2xl:row-start-1">
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
    <div class="ui-panel ui-panel--sales-v2-list overflow-hidden">
        <div class="ui-panel__header">
            <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="ui-panel__title">Registros</h2>
                    <p class="ui-panel__subtitle">
                        {{ $sales->total() }} venta(s) · Página {{ $sales->currentPage() }} de {{ max(1, $sales->lastPage()) }}
                    </p>
                </div>
                @if ($permissions['can_destroy'] && ! $sales->isEmpty())
                    <div class="flex items-center justify-end">
                        <button type="button" wire:click="toggleSelectionMode" class="ui-btn text-sm"
                            :class="$wire.selectionMode ? 'ui-btn-warning' : 'ui-btn-ghost'">
                            <i class="fas {{ $selectionMode ? 'fa-times-circle' : 'fa-check-square' }}"></i>
                            <span>{{ $selectionMode ? 'Cancelar selección' : 'Seleccionar' }}</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        @if ($permissions['can_destroy'])
            @if ($selectionMode)
                <div class="flex flex-col gap-3 border-b border-slate-700/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-white">{{ count($selectedSaleIds) }} venta(s) seleccionada(s)</p>
                        <p class="text-xs text-slate-400">La selección aplica a la página actual.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="toggleSelectAllCurrentPage" class="ui-btn ui-btn-ghost text-sm">
                            <i class="fas {{ $allCurrentPageSelected ? 'fa-square-minus' : 'fa-square-check' }}"></i>
                            {{ $allCurrentPageSelected ? 'Limpiar página' : 'Seleccionar página' }}
                        </button>
                        <button type="button" wire:click="openBulkDeleteModal" class="ui-btn ui-btn-danger text-sm"
                            :disabled="{{ count($selectedSaleIds) === 0 ? 'true' : 'false' }}">
                            <i class="fas fa-trash-alt"></i> Eliminar seleccionados
                        </button>
                    </div>
                </div>
            @endif
        @endif

        {{-- Desktop table --}}
        <div class="hidden md:block">
            <div class="ui-table-wrap border-0 rounded-none">
                <table class="ui-table sales-v2-table ui-table--nowrap-actions" role="table" aria-label="Lista de ventas">
                    <thead>
                        <tr>
                            @if ($permissions['can_destroy'])
                                @if ($selectionMode)
                                    <th class="w-12 text-center">
                                        <input type="checkbox" class="rounded border-slate-500 bg-slate-900"
                                            {{ $allCurrentPageSelected ? 'checked' : '' }}
                                            wire:click="toggleSelectAllCurrentPage">
                                    </th>
                                @endif
                            @endif
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Productos</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr wire:key="sale-row-{{ $sale->id }}">
                                @if ($permissions['can_destroy'])
                                    @if ($selectionMode)
                                        <td class="text-center">
                                            <input type="checkbox" class="rounded border-slate-500 bg-slate-900"
                                                {{ in_array($sale->id, $selectedSaleIds) ? 'checked' : '' }}
                                                wire:click="toggleSaleSelection({{ $sale->id }})">
                                        </td>
                                    @endif
                                @endif
                                <td class="tabular-nums text-slate-300">{{ $sales->firstItem() + $loop->index }}</td>
                                <td class="font-medium text-slate-100">{{ $sale->customer->name ?? 'Consumidor Final' }}</td>
                                <td>
                                    <span class="font-semibold text-slate-100">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</span>
                                    <span class="block text-xs text-slate-500">{{ \Carbon\Carbon::parse($sale->sale_date)->format('H:i') }}</span>
                                </td>
                                <td>
                                    <div class="flex max-w-md flex-wrap gap-1">
                                        @foreach ($sale->saleDetails as $detail)
                                            <span class="inline-flex items-center gap-1 rounded-md border border-cyan-500/25 bg-cyan-500/10 px-2 py-0.5 text-xs text-cyan-100">
                                                <i class="fas fa-box text-[0.65rem] opacity-70"></i>
                                                {{ \Illuminate\Support\Str::limit($detail->product->name ?? '—', 32) }}
                                            </span>
                                        @endforeach
                                        <span class="inline-flex items-center gap-1 rounded-md border border-slate-600 bg-slate-800/80 px-2 py-0.5 text-xs text-slate-300">
                                            <i class="fas fa-cubes text-[0.65rem]"></i> {{ $sale->saleDetails->sum('quantity') }} u.
                                        </span>
                                    </div>
                                </td>
                                <td class="text-right tabular-nums font-semibold text-emerald-300">{{ $currencySymbol }} {{ number_format($sale->total_price, 2) }}</td>
                                <td class="text-center">
                                    <div class="ui-icon-action-row inline-flex flex-nowrap items-center justify-center gap-1.5 md:gap-2">
                                        @if ($permissions['can_show'])
                                            <button type="button" wire:click="openDetailModal({{ $sale->id }})" class="ui-icon-action ui-icon-action--info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                        @if ($permissions['can_edit'])
                                            <a href="{{ route('admin.sales.edit', $sale->id) }}" class="ui-icon-action ui-icon-action--primary" title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        @endif
                                        @if ($permissions['can_print'])
                                            <button type="button" wire:click="printSale({{ $sale->id }})" class="ui-icon-action ui-icon-action--success" title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        @endif
                                        @if ($permissions['can_destroy'])
                                            <button type="button" wire:click="openDeleteModal({{ $sale->id }})" class="ui-icon-action ui-icon-action--danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $selectionMode ? 7 : 6 }}" class="py-12 text-center text-sm text-slate-400">
                                    No hay ventas con los filtros actuales.
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
                @forelse ($sales as $sale)
                    <article class="rounded-xl border border-slate-600/60 bg-slate-900/70 p-4 shadow-[0_10px_24px_rgba(2,6,23,0.45)]" wire:key="sale-card-{{ $sale->id }}">
                        @if ($permissions['can_destroy'] && $selectionMode)
                            <div class="mb-3 flex items-center gap-3 border-b border-slate-700/50 pb-3">
                                <input type="checkbox" class="rounded border-slate-500 bg-slate-900"
                                    {{ in_array($sale->id, $selectedSaleIds) ? 'checked' : '' }}
                                    wire:click="toggleSaleSelection({{ $sale->id }})">
                                <span class="text-sm text-slate-300">Seleccionar esta venta</span>
                            </div>
                        @endif
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-slate-500">#{{ $sales->firstItem() + $loop->index }} · {{ $sale->customer->name ?? 'Consumidor Final' }}</p>
                                <p class="text-sm font-semibold text-slate-100">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="rounded-full border border-emerald-500/35 bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-300 tabular-nums">
                                {{ $currencySymbol }} {{ number_format($sale->total_price, 2) }}
                            </span>
                        </div>
                        <div class="mb-3 flex flex-wrap gap-1">
                            @foreach ($sale->saleDetails as $detail)
                                <span class="inline-flex rounded border border-slate-600 bg-slate-950/60 px-2 py-0.5 text-[0.7rem] text-slate-300">
                                    {{ \Illuminate\Support\Str::limit($detail->product->name ?? '—', 28) }}
                                </span>
                            @endforeach
                        </div>
                        <div class="ui-icon-action-row flex flex-nowrap items-center justify-end gap-1.5 border-t border-slate-700/60 pt-3">
                            @if ($permissions['can_show'])
                                <button type="button" wire:click="openDetailModal({{ $sale->id }})" class="ui-icon-action ui-icon-action--info text-sm" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endif
                            @if ($permissions['can_edit'])
                                <a href="{{ route('admin.sales.edit', $sale->id) }}" class="ui-icon-action ui-icon-action--primary text-sm" title="Editar">
                                    <i class="fas fa-pen"></i>
                                </a>
                            @endif
                            @if ($permissions['can_print'])
                                <button type="button" wire:click="printSale({{ $sale->id }})" class="ui-icon-action ui-icon-action--success text-sm" title="Imprimir">
                                    <i class="fas fa-print"></i>
                                </button>
                            @endif
                            @if ($permissions['can_destroy'])
                                <button type="button" wire:click="openDeleteModal({{ $sale->id }})" class="ui-icon-action ui-icon-action--danger text-sm" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="col-span-full py-10 text-center text-sm text-slate-400">No hay ventas con los filtros actuales.</div>
                @endforelse
            </div>
        </div>

        @if ($sales->hasPages())
            <div class="border-t border-slate-700/60 bg-slate-950/35 px-4 py-3">
                <x-ui.pagination :paginator="$sales->onEachSide(1)" />
            </div>
        @endif
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL: DETALLE DE VENTA                                        --}}
    {{-- ================================================================ --}}
    @if ($showDetailModal && $detailSale)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" wire:click.self="closeDetailModal"
            x-data x-on:keydown.escape.window="$wire.closeDetailModal()">
            <div class="relative max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-2xl border border-cyan-500/20 bg-slate-900/95 shadow-[0_0_40px_rgba(34,211,238,0.12)]">
                <div class="flex items-center justify-between border-b border-slate-700/80 bg-gradient-to-r from-cyan-500/10 to-indigo-600/10 px-5 py-4">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-receipt mr-2 text-cyan-300"></i> Detalle de la venta
                    </h3>
                    <button type="button" wire:click="closeDetailModal" class="rounded-lg border border-slate-600 bg-slate-800/80 px-3 py-1.5 text-slate-300 transition hover:bg-slate-700 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="max-h-[calc(90vh-8rem)] overflow-y-auto p-5">
                    {{-- Customer + Sale info cards --}}
                    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Cliente</p>
                            <p class="font-medium text-slate-200">{{ $detailSale['customer_name'] }}</p>
                            @if ($detailSale['customer_email'] && $detailSale['customer_email'] !== '—')
                                <p class="text-xs text-slate-400">{{ $detailSale['customer_email'] }}</p>
                            @endif
                            @if ($detailSale['customer_phone'] && $detailSale['customer_phone'] !== '—')
                                <p class="text-xs text-slate-400"><i class="fas fa-phone text-[0.6rem] mr-1"></i>{{ $detailSale['customer_phone'] }}</p>
                            @endif
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Fecha</p>
                            <p class="font-medium text-slate-200">{{ $detailSale['sale_date'] }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Desc. general</p>
                            <p class="font-medium text-cyan-200">
                                @if ($detailSale['general_discount_value'] > 0)
                                    {{ $detailSale['general_discount_value'] }}{{ $detailSale['general_discount_type'] === 'percentage' ? '%' : ' ' . $currencySymbol }}
                                @else
                                    —
                                @endif
                            </p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Total</p>
                            <p class="font-medium text-emerald-300">{{ $currencySymbol }} {{ number_format($detailSale['total_price'], 2) }}</p>
                        </div>
                    </div>

                    @if ($detailSale['note'])
                        <div class="mb-4 rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500 mb-1">Nota</p>
                            <p class="text-sm text-slate-200">{{ $detailSale['note'] }}</p>
                        </div>
                    @endif

                    <div class="ui-table-wrap rounded-xl border border-slate-700/60">
                        <table class="ui-table text-sm">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-right">P. unit.</th>
                                    <th class="text-right">Desc.</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detailSale['details'] as $detail)
                                    <tr>
                                        <td class="font-mono text-slate-300">{{ $detail['code'] }}</td>
                                        <td class="font-medium text-slate-100">{{ $detail['name'] }}</td>
                                        <td class="text-slate-400">{{ $detail['category'] }}</td>
                                        <td class="text-center">{{ $detail['quantity'] }}</td>
                                        <td class="text-right tabular-nums">{{ $currencySymbol }} {{ number_format($detail['unit_price'], 2) }}</td>
                                        <td class="text-right">
                                            @if ($detail['discount_value'] > 0)
                                                <span class="text-cyan-300">{{ $detail['discount_value'] }}{{ $detail['discount_type'] === 'percentage' ? '%' : ' ' . $currencySymbol }}</span>
                                            @else
                                                <span class="text-slate-500">—</span>
                                            @endif
                                        </td>
                                        <td class="text-right tabular-nums font-medium text-emerald-300">{{ $currencySymbol }} {{ number_format($detail['subtotal'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-slate-700/80 bg-slate-950/40">
                                @if ($detailSale['general_discount_value'] > 0)
                                    <tr>
                                        <td colspan="6" class="px-4 py-2 text-right text-xs text-slate-400">Subtotal antes de desc. general</td>
                                        <td class="px-4 py-2 text-right font-medium tabular-nums text-slate-200">{{ $currencySymbol }} {{ number_format($detailSale['subtotal_before_discount'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="px-4 py-2 text-right text-xs text-cyan-300/90">Descuento general</td>
                                        <td class="px-4 py-2 text-right font-medium tabular-nums text-cyan-200">
                                            -{{ $currencySymbol }} {{ number_format($detailSale['general_discount_value'], 2) }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right text-sm font-semibold text-white">Total</td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-xl font-bold tabular-nums text-white">{{ $currencySymbol }} {{ number_format($detailSale['total_price'], 2) }}</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="border-t border-slate-700/80 bg-slate-950/50 px-5 py-3 text-right">
                    @if ($permissions['can_print'])
                        <button type="button" wire:click="printSale({{ $detailSale['id'] }})" class="ui-btn ui-btn-ghost text-sm mr-2">
                            <i class="fas fa-print mr-1"></i> Imprimir
                        </button>
                    @endif
                    <button type="button" wire:click="closeDetailModal" class="ui-btn ui-btn-primary text-sm">Cerrar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- MODAL: BORRADO INDIVIDUAL                                      --}}
    {{-- ================================================================ --}}
    @if ($showDeleteModal && $deleteTargetId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-[#020617]/90 p-4 backdrop-blur-md"
            wire:click.self="closeDeleteModal" x-data x-on:keydown.escape.window="$wire.closeDeleteModal()">
            <div class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75),inset_0_1px_0_rgba(255,255,255,0.06)]">
                <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Eliminar esta venta?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se eliminará <span class="font-medium text-white">{{ $deleteTargetName }}</span>. Se revertirán los movimientos de stock y deuda asociados.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmDeleteSale" class="ui-btn ui-btn-danger text-sm">
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
                            <h3 class="text-base font-semibold text-white">¿Eliminar ventas seleccionadas?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se intentará eliminar <span class="font-medium text-white">{{ count($selectedSaleIds) }} venta(s)</span>.
                                Las que tengan pagos de deuda posteriores no podrán eliminarse.
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
</div>