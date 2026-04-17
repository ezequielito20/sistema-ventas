<section class="ui-panel overflow-hidden">
    <div class="ui-panel__header">
        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="ui-panel__title">Movimientos</h2>
                <p class="ui-panel__subtitle" id="paymentHistoryListMeta">
                    {{ $payments->total() }} registro(s) · Página {{ $payments->currentPage() }} de {{ $payments->lastPage() }}
                </p>
            </div>
            <div class="flex items-center justify-end">
                <button type="button" @click="toggleSelectionMode()" class="ui-btn text-sm"
                    :class="selectionMode ? 'ui-btn-warning' : 'ui-btn-ghost'">
                    <i class="fas" :class="selectionMode ? 'fa-times-circle' : 'fa-check-square'"></i>
                    <span x-text="selectionMode ? 'Cancelar selección' : 'Seleccionar'"></span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="selectionMode" x-cloak class="flex flex-col gap-3 border-b border-slate-700/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-white"><span x-text="selectedPaymentIds.length"></span> pago(s) seleccionado(s)</p>
            <p class="text-xs text-slate-400">Puedes eliminar en lote los pagos seleccionados.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="toggleSelectAllOnPage()" class="ui-btn ui-btn-ghost text-sm">
                <i class="fas" :class="allCurrentPageSelected() ? 'fa-square-minus' : 'fa-square-check'"></i>
                <span x-text="allCurrentPageSelected() ? 'Limpiar página' : 'Seleccionar página'"></span>
            </button>
            <button type="button" @click="deleteSelectedPayments()" class="ui-btn ui-btn-danger text-sm" :disabled="selectedPaymentIds.length === 0">
                <i class="fas fa-trash-alt"></i> Eliminar seleccionados
            </button>
        </div>
    </div>

    <div class="hidden md:block">
        <div class="ui-table-wrap border-0 rounded-none">
            <table class="ui-table ui-table--nowrap-actions">
                <thead>
                    <tr>
                        <th x-show="selectionMode" x-cloak class="w-12 text-center">
                            <input type="checkbox" class="rounded border-slate-500 bg-slate-900" :checked="allCurrentPageSelected()"
                                @change="toggleSelectAllOnPage()">
                        </th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th class="text-right">Deuda anterior</th>
                        <th class="text-right">Monto pagado</th>
                        <th class="text-right">Deuda restante</th>
                        <th>Registrado por</th>
                        <th>Notas</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr data-payment-id="{{ $payment->id }}">
                            <td x-show="selectionMode" x-cloak class="text-center">
                                <input
                                    type="checkbox"
                                    class="rounded border-slate-500 bg-slate-900"
                                    :checked="selectedPaymentIds.includes({{ $payment->id }})"
                                    @change="togglePaymentSelection({{ $payment->id }})">
                            </td>
                            <td>
                                <p class="font-semibold text-slate-100">{{ $payment->created_at->format('d/m/Y') }}</p>
                                <p class="text-xs text-slate-400">{{ $payment->created_at->format('H:i') }}</p>
                            </td>
                            <td>
                                <div class="flex min-w-0 items-center gap-2">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-cyan-500/30 bg-slate-900 text-cyan-200">
                                        {{ strtoupper(substr($payment->customer->name, 0, 1)) }}
                                    </div>
                                    <span class="truncate font-medium text-slate-100">{{ $payment->customer->name }}</span>
                                </div>
                            </td>
                            <td class="text-right tabular-nums text-rose-300">{{ $currency->symbol }} {{ number_format($payment->previous_debt, 2) }}</td>
                            <td class="text-right tabular-nums text-emerald-300">{{ $currency->symbol }} {{ number_format($payment->payment_amount, 2) }}</td>
                            <td class="text-right tabular-nums text-amber-300">{{ $currency->symbol }} {{ number_format($payment->remaining_debt, 2) }}</td>
                            <td class="text-slate-200">{{ $payment->user->name }}</td>
                            <td class="max-w-[220px]">
                                <span class="block truncate text-sm text-slate-300">{{ $payment->notes ?: 'Sin notas' }}</span>
                            </td>
                            <td class="text-center">
                                <button
                                    onclick="deletePayment({{ $payment->id }}, '{{ $payment->customer->name }}', {{ $payment->payment_amount }})"
                                    class="ui-icon-action ui-icon-action--danger"
                                    title="Eliminar pago">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td :colspan="selectionMode ? 9 : 8" class="py-10 text-center text-sm text-slate-400">No se encontraron pagos con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="p-4 md:hidden md:p-0">
        <div id="paymentHistoryCardsGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse ($payments as $payment)
                <article class="rounded-xl border border-slate-600/60 bg-slate-900/70 p-4 shadow-[0_10px_24px_rgba(2,6,23,0.45)]">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-100">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-slate-400">{{ $payment->customer->name }}</p>
                        </div>
                        <span class="rounded-full border border-emerald-500/35 bg-emerald-500/15 px-2.5 py-1 text-xs font-semibold text-emerald-300">
                            {{ $currency->symbol }} {{ number_format($payment->payment_amount, 2) }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-lg border border-rose-500/25 bg-rose-950/20 p-2">
                            <p class="text-rose-300/85">Deuda anterior</p>
                            <p class="mt-1 font-semibold tabular-nums text-rose-200">{{ $currency->symbol }} {{ number_format($payment->previous_debt, 2) }}</p>
                        </div>
                        <div class="rounded-lg border border-amber-500/25 bg-amber-950/20 p-2">
                            <p class="text-amber-300/85">Deuda restante</p>
                            <p class="mt-1 font-semibold tabular-nums text-amber-200">{{ $currency->symbol }} {{ number_format($payment->remaining_debt, 2) }}</p>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1 text-xs text-slate-300">
                        <p><span class="text-slate-500">Usuario:</span> {{ $payment->user->name }}</p>
                        <p><span class="text-slate-500">Notas:</span> {{ $payment->notes ?: 'Sin notas' }}</p>
                    </div>
                    <div class="mt-3 flex justify-end border-t border-slate-700/60 pt-3">
                        <button
                            onclick="deletePayment({{ $payment->id }}, '{{ $payment->customer->name }}', {{ $payment->payment_amount }})"
                            class="ui-btn ui-btn-danger text-xs">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </article>
            @empty
                <div class="col-span-full py-10 text-center text-sm text-slate-400">No se encontraron pagos con los filtros actuales.</div>
            @endforelse
        </div>
    </div>

    <div id="paymentHistoryPaginationMount">
        @if ($payments->hasPages())
            <div id="paymentHistoryPagination" class="border-t border-slate-700/60 bg-slate-950/35 px-4 py-3">
                <x-ui.pagination :paginator="$payments" :useLivewire="false" />
            </div>
        @endif
    </div>
</section>
