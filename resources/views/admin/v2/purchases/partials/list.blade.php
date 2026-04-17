<div id="purchases-v2-dynamic">
    <section class="ui-panel ui-panel--purchases-v2-list overflow-hidden">
        <div class="ui-panel__header">
            <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="ui-panel__title">Registros</h2>
                    <p id="purchasesV2ListMeta" class="ui-panel__subtitle">
                        {{ $purchases->total() }} compra(s) · Página {{ $purchases->currentPage() }} de {{ max(1, $purchases->lastPage()) }}
                    </p>
                </div>
                @if ($permissions['can_destroy'] && ! $purchases->isEmpty())
                    <div class="flex items-center justify-end">
                        <button type="button" @click="toggleSelectionMode()" class="ui-btn text-sm"
                            :class="selectionMode ? 'ui-btn-warning' : 'ui-btn-ghost'">
                            <i class="fas fa-check-square" :class="selectionMode ? 'fa-times-circle' : 'fa-check-square'"></i>
                            <span x-text="selectionMode ? 'Cancelar selección' : 'Seleccionar'">Seleccionar</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        @if ($permissions['can_destroy'])
            <div x-show="selectionMode" x-cloak class="flex flex-col gap-3 border-b border-slate-700/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-white"><span x-text="selectedPurchaseIds.length"></span> compra(s) seleccionada(s)</p>
                    <p class="text-xs text-slate-400">La selección aplica a la página actual.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="toggleSelectAllOnPage()" class="ui-btn ui-btn-ghost text-sm">
                        <i class="fas" :class="allCurrentPageSelected() ? 'fa-square-minus' : 'fa-square-check'"></i>
                        <span x-text="allCurrentPageSelected() ? 'Limpiar página' : 'Seleccionar página'"></span>
                    </button>
                    <button type="button" @click="deleteSelectedPurchases()" class="ui-btn ui-btn-danger text-sm" :disabled="selectedPurchaseIds.length === 0 || isDeleting">
                        <i class="fas fa-trash-alt"></i> Eliminar seleccionados
                    </button>
                </div>
            </div>
        @endif

        <div class="hidden md:block">
            <div class="ui-table-wrap border-0 rounded-none">
                <table class="ui-table purchases-v2-table ui-table--nowrap-actions" role="table" aria-label="Lista de compras">
                    <thead>
                        <tr>
                            @if ($permissions['can_destroy'])
                                <th x-show="selectionMode" x-cloak class="w-12 text-center">
                                    <input type="checkbox" class="rounded border-slate-500 bg-slate-900" :checked="allCurrentPageSelected()"
                                        @change="toggleSelectAllOnPage()">
                                </th>
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
                            <tr data-purchase-id="{{ $purchase->id }}">
                                @if ($permissions['can_destroy'])
                                    <td x-show="selectionMode" x-cloak class="text-center">
                                        <input
                                            type="checkbox"
                                            class="rounded border-slate-500 bg-slate-900"
                                            :checked="selectedPurchaseIds.includes({{ $purchase->id }})"
                                            @change="togglePurchaseSelection({{ $purchase->id }})">
                                    </td>
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
                                    {{-- Convención alineada a clientes: Ver → Editar → Eliminar (horizontal, ui-icon-action-row) --}}
                                    <div class="ui-icon-action-row inline-flex flex-nowrap items-center justify-center gap-1.5 md:gap-2">
                                        @if ($permissions['can_show'])
                                            <button type="button" class="ui-icon-action ui-icon-action--info purchases-v2-details" data-id="{{ $purchase->id }}" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                        @if ($permissions['can_edit'])
                                            <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="ui-icon-action ui-icon-action--primary" title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        @endif
                                        @if ($permissions['can_destroy'])
                                            <button type="button" class="ui-icon-action ui-icon-action--danger purchases-v2-delete" data-id="{{ $purchase->id }}" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td :colspan="selectionMode ? 8 : 7" class="py-12 text-center text-sm text-slate-400">No hay compras con los filtros actuales.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-4 md:hidden">
            <div id="purchasesV2CardsGrid" class="grid grid-cols-1 gap-4">
                @forelse ($purchases as $purchase)
                    <article class="rounded-xl border border-slate-600/60 bg-slate-900/70 p-4 shadow-[0_10px_24px_rgba(2,6,23,0.45)]" data-purchase-id="{{ $purchase->id }}">
                        @if ($permissions['can_destroy'])
                            <div x-show="selectionMode" x-cloak class="mb-3 flex items-center gap-3 border-b border-slate-700/50 pb-3">
                                <input
                                    type="checkbox"
                                    class="rounded border-slate-500 bg-slate-900"
                                    :checked="selectedPurchaseIds.includes({{ $purchase->id }})"
                                    @change="togglePurchaseSelection({{ $purchase->id }})">
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
                                <span class="inline-flex rounded border border-slate-600 bg-slate-950/60 px-2 py-0.5 text-[0.7rem] text-slate-300">{{ \Illuminate\Support\Str::limit($detail->product->name ?? '—', 28) }}</span>
                            @endforeach
                        </div>
                        <div class="ui-icon-action-row flex flex-nowrap items-center justify-end gap-1.5 border-t border-slate-700/60 pt-3">
                            @if ($permissions['can_show'])
                                <button type="button" class="ui-icon-action ui-icon-action--info purchases-v2-details text-sm" data-id="{{ $purchase->id }}" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endif
                            @if ($permissions['can_edit'])
                                <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="ui-icon-action ui-icon-action--primary text-sm" title="Editar">
                                    <i class="fas fa-pen"></i>
                                </a>
                            @endif
                            @if ($permissions['can_destroy'])
                                <button type="button" class="ui-icon-action ui-icon-action--danger purchases-v2-delete text-sm" data-id="{{ $purchase->id }}" title="Eliminar">
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

        <div id="purchasesV2PaginationMount">
            @if ($purchases->hasPages())
                <div id="purchasesV2Pagination" class="border-t border-slate-700/60 bg-slate-950/35 px-4 py-3">
                    <x-ui.pagination :paginator="$purchases" :useLivewire="false" />
                </div>
            @endif
        </div>
    </section>
</div>
