<div class="space-y-6">
    <div class="ui-panel">
        <div class="ui-panel__header">
            <div>
                <h1 class="ui-panel__title">Pagos y Cobros</h1>
                <p class="ui-panel__subtitle">Gestión de todas las facturas de suscripción del sistema.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-4">
        <x-ui.stat-card variant="warning" icon="fas fa-clock" trend="Pendientes" label="Pendientes" :value="number_format($stats['pending_count'])" meta="Por cobrar" />
        <x-ui.stat-card variant="success" icon="fas fa-check-circle" trend="Pagados" label="Pagados" :value="number_format($stats['paid_count'])" meta="Confirmados" />
        <x-ui.stat-card variant="danger" icon="fas fa-exclamation-triangle" trend="Vencidos" label="Vencidos" :value="number_format($stats['overdue_count'])" meta="Atrasados" />
        <x-ui.stat-card variant="info" icon="fas fa-dollar-sign" trend="Total" label="Por Cobrar" :value="'$ ' . number_format($stats['total_pending_amount'], 2)" meta="Pendiente + Vencido" />
    </div>

    {{-- Filtros --}}
    <div class="ui-panel" x-data="{ showFilters: false }">
        <div class="ui-panel__header flex items-center justify-between gap-3">
            <div>
                <h2 class="ui-panel__title">Filtros</h2>
            </div>
            <button type="button" class="ui-btn ui-btn-ghost text-sm" @click="showFilters = !showFilters">
                <i class="fas" :class="showFilters ? 'fa-sliders-h' : 'fa-filter'"></i>
                <span x-text="showFilters ? 'Ocultar filtros' : 'Filtros'"></span>
            </button>
        </div>
        <div class="ui-panel__body space-y-4" x-show="showFilters" x-transition>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-end xl:grid-cols-[10rem_12rem_10rem_10rem_auto]">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Estado</label>
                    <select wire:model.live="statusFilter" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                        <option value="">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="paid">Pagado</option>
                        <option value="overdue">Vencido</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Empresa</label>
                    <select wire:model.live="companyFilter" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                        <option value="">Todas</option>
                        @foreach ($companies as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Desde</label>
                    <input type="date" wire:model.live="dateFrom" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Hasta</label>
                    <input type="date" wire:model.live="dateTo" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                </div>
                <div>
                    <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost w-full text-sm">
                        <i class="fas fa-eraser"></i> Limpiar filtros
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="ui-panel overflow-hidden">
        <div class="ui-panel__header">
            <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="ui-panel__title">Listado de Pagos</h2>
                    <p class="ui-panel__subtitle">{{ $payments->total() }} resultado(s) · Página {{ $payments->currentPage() }} de {{ $payments->lastPage() }}</p>
                </div>
                <div class="relative min-w-[16rem] max-w-xs">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar empresa..." class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                </div>
            </div>
        </div>
        <div class="ui-panel__body p-0">
            <div class="ui-table-wrap border-0 rounded-none">
                <table class="ui-table ui-table--nowrap-actions">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Plan</th>
                            <th>Período</th>
                            <th>Monto</th>
                            <th>Vencimiento</th>
                            <th class="text-center">Estado</th>
                            <th>Fecha Pago</th>
                            <th class="text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>
                                    <p class="font-medium text-white">{{ $payment->company?->name ?? '—' }}</p>
                                    <p class="text-xs text-slate-400">{{ $payment->company?->nit ?? '' }}</p>
                                </td>
                                <td class="text-sm text-slate-300">{{ $payment->subscription?->plan?->name ?? '—' }}</td>
                                <td class="text-sm text-slate-300">{{ $payment->period_start?->format('d/m/Y') }} — {{ $payment->period_end?->format('d/m/Y') }}</td>
                                <td class="text-sm font-medium text-white">$ {{ number_format($payment->amount, 2) }}</td>
                                <td class="text-sm text-slate-300">{{ $payment->due_date->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    @if ($payment->status === 'paid')
                                        <span class="ui-badge ui-badge-success">Pagado</span>
                                    @elseif ($payment->status === 'pending')
                                        <span class="ui-badge ui-badge-warning">Pendiente</span>
                                    @elseif ($payment->status === 'overdue')
                                        <span class="ui-badge ui-badge-danger">Vencido</span>
                                    @else
                                        <span class="ui-badge ui-badge-info">{{ $payment->status }}</span>
                                    @endif
                                </td>
                                <td class="text-sm text-slate-300">{{ $payment->paid_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="text-left">
                                    <div class="ui-icon-action-row flex flex-nowrap items-center justify-start gap-1.5 md:gap-2">
                                        @if ($payment->proof_of_payment)
                                            <a href="{{ app(\App\Services\PaymentService::class)->getReceiptUrl($payment->proof_of_payment) }}" target="_blank" class="ui-icon-action ui-icon-action--info" title="Ver comprobante">
                                                <i class="fas fa-file-image"></i>
                                            </a>
                                        @endif
                                        @if (in_array($payment->status, ['pending', 'overdue']))
                                            <button type="button" wire:click="openPaymentModal({{ $payment->id }})" class="ui-icon-action ui-icon-action--success" title="Registrar pago">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" wire:click="cancelPayment({{ $payment->id }})" class="ui-icon-action ui-icon-action--danger" title="Cancelar" onclick="return confirm('¿Cancelar este pago?') || event.stopImmediatePropagation()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-8 text-sm text-slate-400">No hay pagos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="ui-panel__body border-t border-slate-700/50">
            <x-ui.pagination :paginator="$payments" scrollIntoView=".ui-panel" />
        </div>
    </div>

    {{-- Modal Registrar Pago --}}
    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" x-data x-cloak x-show="true" x-transition>
            <div class="fixed inset-0 bg-black/60" wire:click="closePaymentModal"></div>
            <div class="relative w-full max-w-lg mx-4 my-8" @click.stop>
                <div class="ui-panel">
                    <div class="ui-panel__header flex items-center justify-between">
                        <h3 class="ui-panel__title">Registrar pago</h3>
                        <button type="button" wire:click="closePaymentModal" class="text-slate-400 hover:text-slate-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="ui-panel__body space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Comprobante de pago</label>
                            <input type="file" wire:model="receiptFile" accept="image/*,.pdf" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:bg-cyan-600 file:text-sm file:text-white" />
                            @error('receiptFile') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Referencia de transacción</label>
                            <input type="text" wire:model="transactionReference" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" placeholder="Ej: Transferencia #12345" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Notas</label>
                            <textarea wire:model="paymentNotes" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none"></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="closePaymentModal" class="ui-btn ui-btn-ghost">Cancelar</button>
                            <button type="button" wire:click="markAsPaid" class="ui-btn ui-btn-success">
                                <i class="fas fa-check mr-1.5"></i> Confirmar pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
