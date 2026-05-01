@php
    $inputBase = 'w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500';
    $labelBase = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400';
@endphp

<div class="space-y-6" wire:key="purchases-index">

    {{-- ================================================================ --}}
    {{-- HEADER + STATS                                                   --}}
    {{-- ================================================================ --}}
    <div class="ui-panel overflow-hidden">
        <div class="ui-panel__header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Compras</h1>
                <p class="ui-panel__subtitle">Control de adquisiciones, montos y estado de entrega en tiempo real.</p>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2">
                @if ($permFlags['can_report'])
                    <a href="{{ route('admin.purchases.report') }}" target="_blank" rel="noopener"
                        class="ui-btn ui-btn-ghost text-sm">
                        <i class="fas fa-file-pdf"></i> Reporte PDF
                    </a>
                @endif
                @if ($cashCount)
                    @if ($permFlags['can_create'])
                        <a href="{{ route('admin.purchases.create') }}" class="ui-btn ui-btn-primary text-sm">
                            <i class="fas fa-plus"></i> Nueva compra
                        </a>
                    @endif
                @else
                    @if ($permFlags['can_create'])
                        <a href="{{ route('admin.cash-counts.create') }}" class="ui-btn ui-btn-danger text-sm">
                            <i class="fas fa-cash-register"></i> Abrir caja
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-ui.stat-card variant="info" icon="fas fa-boxes" trend="Catálogo" label="Productos únicos"
            :value="number_format((int) $totalPurchases)" meta="Con compras registradas" />
        <x-ui.stat-card variant="success" icon="fas fa-chart-line" trend="Capital" label="Total invertido"
            :value="$currency->symbol . ' ' . number_format((float) $totalAmount, 2)" meta="Histórico" />
        <x-ui.stat-card variant="warning" icon="fas fa-calendar-check" trend="Mes actual" label="Compras del mes"
            :value="number_format((int) $monthlyPurchases)" meta="Actividad reciente" />
        <x-ui.stat-card variant="danger" icon="fas fa-hourglass-half" trend="Pendientes" label="Entregas pendientes"
            :value="number_format((int) $pendingDeliveries)" meta="Sin recibo de pago" />
    </div>

    {{-- ================================================================ --}}
    {{-- FILTROS                                                          --}}
    {{-- ================================================================ --}}
    <div class="ui-panel ui-panel--overflow-visible">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h2 class="ui-panel__title">Filtros</h2>
                <p class="ui-panel__subtitle">Búsqueda por recibo, producto y fechas de compra.</p>
            </div>
            <button type="button" wire:click="$toggle('showFilters')" class="ui-btn ui-btn-ghost w-full shrink-0 text-sm sm:w-auto">
                @if ($showFilters)
                    <i class="fas fa-sliders-h text-sm"></i> Ocultar filtros
                @else
                    <i class="fas fa-filter text-sm"></i> Filtros avanzados
                @endif
            </button>
        </div>

        @if ($showFilters)
            <div class="ui-panel__body space-y-4" wire:key="purchases-filters">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
                    <div class="xl:col-span-1">
                        <label class="{{ $labelBase }}">Producto</label>
                        <select wire:model.live="product_id" class="{{ $inputBase }} appearance-none pr-8">
                            <option value="">Todos los productos</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelBase }}">Desde</label>
                        <input type="date" wire:model.live="dateFrom" class="{{ $inputBase }}" style="color-scheme: dark;">
                    </div>
                    <div>
                        <label class="{{ $labelBase }}">Hasta</label>
                        <input type="date" wire:model.live="dateTo" class="{{ $inputBase }}" style="color-scheme: dark;">
                    </div>
                    <div>
                        <label class="{{ $labelBase }}">Monto mín.</label>
                        <input type="number" wire:model.live="amountMin" min="0" step="0.01" placeholder="0.00"
                            class="{{ $inputBase }}">
                    </div>
                    <div>
                        <label class="{{ $labelBase }}">Monto máx.</label>
                        <input type="number" wire:model.live="amountMax" min="0" step="0.01" placeholder="0.00"
                            class="{{ $inputBase }}">
                    </div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="{{ $labelBase }}">Buscar</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="search" wire:model.live.debounce.300ms="search"
                                    placeholder="Recibo, fecha, producto o monto…"
                                    class="{{ $inputBase }} pl-10"
                                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                            </div>
                        </div>
                        <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost shrink-0 text-sm">
                            <i class="fas fa-eraser"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ================================================================ --}}
    {{-- LISTADO                                                          --}}
    {{-- ================================================================ --}}
    <div class="ui-panel ui-panel--purchases-v2-list overflow-hidden">
        <div class="ui-panel__header">
            <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="ui-panel__title">Registros</h2>
                    <p class="ui-panel__subtitle">
                        {{ $purchases->total() }} compra(s) · Página {{ $purchases->currentPage() }} de {{ max(1, $purchases->lastPage()) }}
                    </p>
                </div>
                @if ($permFlags['can_destroy'] && ! $purchases->isEmpty())
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

        @if ($permFlags['can_destroy'])
            @if ($selectionMode)
                <div class="flex flex-col gap-3 border-b border-slate-700/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-white">{{ count($selectedPurchaseIds) }} compra(s) seleccionada(s)</p>
                        <p class="text-xs text-slate-400">La selección aplica a la página actual.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="toggleSelectAllCurrentPage" class="ui-btn ui-btn-ghost text-sm">
                            <i class="fas {{ $allCurrentPageSelected ? 'fa-square-minus' : 'fa-square-check' }}"></i>
                            {{ $allCurrentPageSelected ? 'Limpiar página' : 'Seleccionar página' }}
                        </button>
                        <button type="button" wire:click="openBulkDeleteModal" class="ui-btn ui-btn-danger text-sm"
                            :disabled="{{ count($selectedPurchaseIds) === 0 ? 'true' : 'false' }}">
                            <i class="fas fa-trash-alt"></i> Eliminar seleccionados
                        </button>
                    </div>
                </div>
            @endif
        @endif

        {{-- Desktop table --}}
        <div class="hidden md:block">
            <div class="ui-table-wrap border-0 rounded-none">
                <table class="ui-table purchases-v2-table ui-table--nowrap-actions" role="table" aria-label="Lista de compras">
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
                            <th>#</th>
                            <th>Recibo</th>
                            <th>Fecha</th>
                            <th>Productos</th>
                            <th class="text-right">Monto</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases as $purchase)
                            <tr wire:key="purchase-row-{{ $purchase->id }}">
                                @if ($permFlags['can_destroy'])
                                    @if ($selectionMode)
                                        <td class="text-center">
                                            <input type="checkbox" class="rounded border-slate-500 bg-slate-900"
                                                {{ in_array($purchase->id, $selectedPurchaseIds) ? 'checked' : '' }}
                                                wire:click="togglePurchaseSelection({{ $purchase->id }})">
                                        </td>
                                    @endif
                                @endif
                                <td class="tabular-nums text-slate-300">{{ $purchases->firstItem() + $loop->index }}</td>
                                <td class="font-medium text-slate-100">{{ $purchase->payment_receipt ?: 'Sin recibo' }}</td>
                                <td>
                                    <span class="font-semibold text-slate-100">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</span>
                                    <span class="block text-xs text-slate-500">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('H:i') }}</span>
                                </td>
                                <td>
                                    <div class="flex max-w-md flex-wrap gap-1">
                                        @foreach ($purchase->details as $detail)
                                            <span class="inline-flex items-center gap-1 rounded-md border border-cyan-500/25 bg-cyan-500/10 px-2 py-0.5 text-xs text-cyan-100">
                                                <i class="fas fa-box text-[0.65rem] opacity-70"></i>
                                                {{ \Illuminate\Support\Str::limit($detail->product->name ?? '—', 32) }}
                                            </span>
                                        @endforeach
                                        <span class="inline-flex items-center gap-1 rounded-md border border-slate-600 bg-slate-800/80 px-2 py-0.5 text-xs text-slate-300">
                                            <i class="fas fa-cubes text-[0.65rem]"></i> {{ $purchase->details->sum('quantity') }} u.
                                        </span>
                                    </div>
                                </td>
                                <td class="text-right tabular-nums font-semibold text-emerald-300">{{ $currency->symbol }} {{ number_format($purchase->total_price, 2) }}</td>
                                <td>
                                    @if ($purchase->payment_receipt)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/35 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-300">
                                            <i class="fas fa-check-circle"></i> Completado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full border border-amber-500/35 bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-200">
                                            <i class="fas fa-clock"></i> Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="ui-icon-action-row inline-flex flex-nowrap items-center justify-center gap-1.5 md:gap-2">
                                        @if ($permFlags['can_show'])
                                            <button type="button" wire:click="openDetailModal({{ $purchase->id }})" class="ui-icon-action ui-icon-action--info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                        @if ($permFlags['can_edit'])
                                            <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="ui-icon-action ui-icon-action--primary" title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        @endif
                                        @if ($permFlags['can_destroy'])
                                            <button type="button" wire:click="openDeleteModal({{ $purchase->id }})" class="ui-icon-action ui-icon-action--danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $selectionMode ? 8 : 7 }}" class="py-12 text-center text-sm text-slate-400">
                                    No hay compras con los filtros actuales.
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
                @forelse ($purchases as $purchase)
                    <article class="rounded-xl border border-slate-600/60 bg-slate-900/70 p-4 shadow-[0_10px_24px_rgba(2,6,23,0.45)]" wire:key="purchase-card-{{ $purchase->id }}">
                        @if ($permFlags['can_destroy'] && $selectionMode)
                            <div class="mb-3 flex items-center gap-3 border-b border-slate-700/50 pb-3">
                                <input type="checkbox" class="rounded border-slate-500 bg-slate-900"
                                    {{ in_array($purchase->id, $selectedPurchaseIds) ? 'checked' : '' }}
                                    wire:click="togglePurchaseSelection({{ $purchase->id }})">
                                <span class="text-sm text-slate-300">Seleccionar esta compra</span>
                            </div>
                        @endif
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-slate-500">#{{ $purchases->firstItem() + $loop->index }} · {{ $purchase->payment_receipt ?: 'Sin recibo' }}</p>
                                <p class="text-sm font-semibold text-slate-100">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="rounded-full border border-emerald-500/35 bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-300 tabular-nums">
                                {{ $currency->symbol }} {{ number_format($purchase->total_price, 2) }}
                            </span>
                        </div>
                        <div class="mb-3 flex flex-wrap gap-1">
                            @foreach ($purchase->details as $detail)
                                <span class="inline-flex rounded border border-slate-600 bg-slate-950/60 px-2 py-0.5 text-[0.7rem] text-slate-300">
                                    {{ \Illuminate\Support\Str::limit($detail->product->name ?? '—', 28) }}
                                </span>
                            @endforeach
                        </div>
                        <div class="ui-icon-action-row flex flex-nowrap items-center justify-end gap-1.5 border-t border-slate-700/60 pt-3">
                            @if ($permFlags['can_show'])
                                <button type="button" wire:click="openDetailModal({{ $purchase->id }})" class="ui-icon-action ui-icon-action--info text-sm" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endif
                            @if ($permFlags['can_edit'])
                                <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="ui-icon-action ui-icon-action--primary text-sm" title="Editar">
                                    <i class="fas fa-pen"></i>
                                </a>
                            @endif
                            @if ($permFlags['can_destroy'])
                                <button type="button" wire:click="openDeleteModal({{ $purchase->id }})" class="ui-icon-action ui-icon-action--danger text-sm" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="col-span-full py-10 text-center text-sm text-slate-400">No hay compras con los filtros actuales.</div>
                @endforelse
            </div>
        </div>

        @if ($purchases->hasPages())
            <div class="border-t border-slate-700/60 bg-slate-950/35 px-4 py-3">
                <x-ui.pagination :paginator="$purchases->onEachSide(1)" />
            </div>
        @endif
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL: DETALLE DE COMPRA                                       --}}
    {{-- ================================================================ --}}
    @if ($showDetailModal && $detailPurchase)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" wire:click.self="closeDetailModal"
            x-data x-on:keydown.escape.window="$wire.closeDetailModal()">
            <div class="relative max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-2xl border border-cyan-500/20 bg-slate-900/95 shadow-[0_0_40px_rgba(34,211,238,0.12)]">
                <div class="flex items-center justify-between border-b border-slate-700/80 bg-gradient-to-r from-cyan-500/10 to-indigo-600/10 px-5 py-4">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-receipt mr-2 text-cyan-300"></i> Detalle de la compra
                    </h3>
                    <button type="button" wire:click="closeDetailModal" class="rounded-lg border border-slate-600 bg-slate-800/80 px-3 py-1.5 text-slate-300 transition hover:bg-slate-700 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="max-h-[calc(90vh-8rem)] overflow-y-auto p-5">
                    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Recibo</p>
                            <p class="font-medium text-slate-200">{{ $detailPurchase['payment_receipt'] ?: 'Sin recibo' }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Fecha</p>
                            <p class="font-medium text-slate-200">{{ $detailPurchase['purchase_date'] }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Desc. general</p>
                            <p class="font-medium text-cyan-200">
                                @if ($detailPurchase['general_discount_value'] > 0)
                                    {{ $detailPurchase['general_discount_value'] }}{{ $detailPurchase['general_discount_type'] === 'percentage' ? '%' : ' ' . $currency->symbol }}
                                @else
                                    —
                                @endif
                            </p>
                        </div>
                        <div class="rounded-lg border border-slate-600/50 bg-slate-950/50 p-3">
                            <p class="text-xs text-slate-500">Total</p>
                            <p class="font-medium text-emerald-300">{{ $currency->symbol }} {{ number_format($detailPurchase['total_price'], 2) }}</p>
                        </div>
                    </div>

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
                                @foreach ($detailPurchase['details'] as $detail)
                                    <tr>
                                        <td class="font-mono text-slate-300">{{ $detail['code'] }}</td>
                                        <td class="font-medium text-slate-100">{{ $detail['name'] }}</td>
                                        <td class="text-slate-400">{{ $detail['category'] }}</td>
                                        <td class="text-center">{{ $detail['quantity'] }}</td>
                                        <td class="text-right tabular-nums">{{ $currency->symbol }} {{ number_format($detail['original_price'], 2) }}</td>
                                        <td class="text-right">
                                            @if ($detail['discount_value'] > 0)
                                                <span class="text-cyan-300">{{ $detail['discount_value'] }}{{ $detail['discount_type'] === 'percentage' ? '%' : ' ' . $currency->symbol }}</span>
                                            @else
                                                <span class="text-slate-500">—</span>
                                            @endif
                                        </td>
                                        <td class="text-right tabular-nums font-medium text-emerald-300">{{ $currency->symbol }} {{ number_format($detail['subtotal'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-slate-700/80 bg-slate-950/40">
                                <tr>
                                    <td colspan="6" class="px-4 py-2 text-right text-xs text-slate-400">Subtotal antes de desc. general</td>
                                    <td class="px-4 py-2 text-right font-medium tabular-nums text-slate-200">{{ $currency->symbol }} {{ number_format($detailPurchase['subtotal_before_discount'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 py-2 text-right text-xs text-cyan-300/90">Descuento general</td>
                                    <td class="px-4 py-2 text-right font-medium tabular-nums text-cyan-200">
                                        @if ($detailPurchase['general_discount_value'] > 0)
                                            -{{ $currency->symbol }} {{ number_format($detailPurchase['general_discount_value'], 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right text-sm font-semibold text-white">Total</td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-xl font-bold tabular-nums text-white">{{ $currency->symbol }} {{ number_format($detailPurchase['total_price'], 2) }}</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="border-t border-slate-700/80 bg-slate-950/50 px-5 py-3 text-right">
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
                            <h3 class="text-base font-semibold text-white">¿Eliminar esta compra?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se eliminará <span class="font-medium text-white">{{ $deleteTargetName }}</span>. Se revertirán los movimientos de stock asociados.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmDeletePurchase" class="ui-btn ui-btn-danger text-sm">
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
                            <h3 class="text-base font-semibold text-white">¿Eliminar compras seleccionadas?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se intentará eliminar <span class="font-medium text-white">{{ count($selectedPurchaseIds) }} compra(s)</span>.
                                Las que generen stock negativo no podrán eliminarse.
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
