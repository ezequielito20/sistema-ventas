<div class="space-y-6" wire:poll.300s="loadCompany">
    @if (!$company)
        <p class="text-slate-400">Cargando...</p>
    @else
        {{-- Header --}}
        <div class="ui-panel">
            <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('super-admin.companies.index') }}" class="text-slate-400 hover:text-slate-200" wire:navigate>
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/15 text-lg font-bold uppercase text-cyan-300">
                            {{ mb_substr($company->name, 0, 2) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="ui-panel__title">{{ $company->name }}</h1>
                                @if ($company->subscription_status === 'active')
                                    <span class="ui-badge ui-badge-success text-xs">Activo</span>
                                @elseif ($company->subscription_status === 'trial')
                                    <span class="ui-badge ui-badge-info text-xs">Trial</span>
                                @else
                                    <span class="ui-badge ui-badge-danger text-xs">Suspendido</span>
                                @endif
                            </div>
                            <p class="ui-panel__subtitle">Plan: {{ $subscription?->plan?->name ?? 'Sin plan' }} · NIT: {{ $company->nit }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="openChangePlanModal" class="ui-btn ui-btn-ghost text-sm">
                        <i class="fas fa-exchange-alt mr-1.5"></i> Cambiar plan
                    </button>
                    @if ($subscription && $subscription->isSuspended())
                        <button type="button" wire:click="activate" class="ui-btn ui-btn-success text-sm">
                            <i class="fas fa-check-circle mr-1.5"></i> Reactivar
                        </button>
                    @elseif ($subscription)
                        <button type="button" wire:click="openSuspendModal" class="ui-btn ui-btn-danger text-sm">
                            <i class="fas fa-ban mr-1.5"></i> Suspender
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="ui-panel overflow-hidden">
            <div class="border-b border-slate-700/50">
                <nav class="-mb-px flex gap-x-10 px-6">
                    <button wire:click="switchTab('info')" @class(['pb-3 pt-4 px-2 text-sm font-medium border-b-2 transition-colors', 'border-cyan-400 text-cyan-400' => $activeTab === 'info', 'border-transparent text-slate-400 hover:text-slate-200' => $activeTab !== 'info'])>
                        <i class="fas fa-info-circle mr-1.5"></i> Información
                    </button>
                    <button wire:click="switchTab('payments')" @class(['pb-3 pt-4 px-2 text-sm font-medium border-b-2 transition-colors', 'border-cyan-400 text-cyan-400' => $activeTab === 'payments', 'border-transparent text-slate-400 hover:text-slate-200' => $activeTab !== 'payments'])>
                        <i class="fas fa-file-invoice-dollar mr-1.5"></i> Historial de Pagos
                    </button>
                    <button wire:click="switchTab('stats')" @class(['pb-3 pt-4 px-2 text-sm font-medium border-b-2 transition-colors', 'border-cyan-400 text-cyan-400' => $activeTab === 'stats', 'border-transparent text-slate-400 hover:text-slate-200' => $activeTab !== 'stats'])>
                        <i class="fas fa-chart-bar mr-1.5"></i> Estadísticas
                    </button>
                </nav>
            </div>

            @if ($activeTab === 'info')
                <div class="p-6 space-y-6">
                    {{-- Datos de la Empresa --}}
                    <div>
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">
                            <i class="fas fa-building mr-2 text-cyan-400"></i>Datos de la Empresa
                        </h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            {{-- Nombre --}}
                            <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-400">
                                    <i class="fas fa-building text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-500">Nombre</p>
                                    <p class="truncate text-sm font-medium text-slate-200">{{ $company->name }}</p>
                                </div>
                            </div>
                            {{-- NIT --}}
                            <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-400">
                                    <i class="fas fa-id-card text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-500">NIT</p>
                                    <p class="truncate text-sm font-medium text-slate-200">{{ $company->nit ?: '—' }}</p>
                                </div>
                            </div>
                            {{-- Email --}}
                            <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-400">
                                    <i class="fas fa-envelope text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-500">Email</p>
                                    <p class="truncate text-sm font-medium text-slate-200">{{ $company->email ?: '—' }}</p>
                                </div>
                            </div>
                            {{-- Teléfono --}}
                            <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-400">
                                    <i class="fas fa-phone text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-500">Teléfono</p>
                                    <p class="truncate text-sm font-medium text-slate-200">{{ $company->phone ?: '—' }}</p>
                                </div>
                            </div>
                            {{-- Tipo Negocio --}}
                            <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-400">
                                    <i class="fas fa-briefcase text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-500">Tipo Negocio</p>
                                    <p class="truncate text-sm font-medium text-slate-200">{{ $company->business_type ?: '—' }}</p>
                                </div>
                            </div>
                            {{-- Dirección --}}
                            <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-400">
                                    <i class="fas fa-map-marker-alt text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-500">Dirección</p>
                                    <p class="truncate text-sm font-medium text-slate-200">{{ $company->address ?: '—' }}</p>
                                </div>
                            </div>
                            {{-- Moneda --}}
                            <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-400">
                                    <i class="fas fa-coins text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-500">Moneda</p>
                                    <p class="truncate text-sm font-medium text-slate-200">{{ $company->currency ?: '—' }}</p>
                                </div>
                            </div>
                            {{-- Creado --}}
                            <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-400">
                                    <i class="fas fa-calendar-plus text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-500">Creado</p>
                                    <p class="truncate text-sm font-medium text-slate-200">{{ $company->created_at?->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-700">

                    {{-- Datos de Suscripción --}}
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">
                                <i class="fas fa-credit-card mr-2 text-cyan-400"></i>Datos de Suscripción
                            </h3>
                            @if ($subscription)
                                <button type="button" wire:click="openEditSubscriptionModal" class="ui-btn ui-btn-ghost text-xs">
                                    <i class="fas fa-pen mr-1"></i> Editar suscripción
                                </button>
                            @endif
                        </div>

                        @if (!$subscription)
                            <div class="rounded-lg border border-slate-700/50 bg-slate-800/30 p-6 text-center">
                                <i class="fas fa-exclamation-circle mb-2 text-2xl text-slate-500"></i>
                                <p class="text-sm text-slate-400">Esta empresa no tiene suscripción.</p>
                            </div>
                        @else
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                                {{-- Plan --}}
                                <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                        <i class="fas fa-layer-group text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500">Plan</p>
                                        <p class="truncate text-sm font-medium text-slate-200">{{ $subscription->plan?->name ?? 'Sin plan' }}</p>
                                    </div>
                                </div>
                                {{-- Estado --}}
                                <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                        <i class="fas fa-signal text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500">Estado</p>
                                        <p class="truncate text-sm font-medium text-slate-200 capitalize">{{ $subscription->status }}</p>
                                    </div>
                                </div>
                                {{-- Inicio --}}
                                <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                        <i class="fas fa-play-circle text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500">Inicio</p>
                                        <p class="truncate text-sm font-medium text-slate-200">{{ $subscription->started_at?->format('d/m/Y') ?? '—' }}</p>
                                    </div>
                                </div>
                                {{-- Día Cobro --}}
                                <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                        <i class="fas fa-calendar-day text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500">Día Cobro</p>
                                        <p class="truncate text-sm font-medium text-slate-200">Día {{ $subscription->billing_day }}</p>
                                    </div>
                                </div>
                                {{-- Próx. Cobro --}}
                                <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                        <i class="fas fa-calendar-check text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500">Próx. Cobro</p>
                                        <p class="truncate text-sm font-medium text-slate-200">{{ $subscription->next_billing_date?->format('d/m/Y') ?? '—' }}</p>
                                    </div>
                                </div>
                                {{-- Fecha Corte --}}
                                <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                        <i class="fas fa-hourglass-end text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500">Fecha Corte</p>
                                        <p class="truncate text-sm font-medium text-slate-200">{{ $subscription->grace_period_end?->format('d/m/Y') ?? '—' }}</p>
                                    </div>
                                </div>
                                {{-- Monto --}}
                                <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-emerald-500/5 p-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                        <i class="fas fa-dollar-sign text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500">Monto Mensual</p>
                                        <p class="truncate text-lg font-bold text-emerald-400">$ {{ number_format($subscription->amount, 2) }}</p>
                                    </div>
                                </div>
                                {{-- Descuento --}}
                                <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 {{ $subscription->discount_amount > 0 ? 'bg-amber-500/5' : 'bg-slate-800/30' }} p-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $subscription->discount_amount > 0 ? 'bg-amber-500/10 text-amber-400' : 'bg-slate-700/50 text-slate-500' }}">
                                        <i class="fas fa-tags text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500">Descuento</p>
                                        <p class="truncate text-sm font-medium {{ $subscription->discount_amount > 0 ? 'text-amber-400' : 'text-slate-200' }}">
                                            {{ $subscription->discount_amount > 0 ? '$ ' . number_format($subscription->discount_amount, 2) : 'Sin descuento' }}
                                        </p>
                                        @if ($subscription->discount_reason)
                                            <p class="truncate text-xs text-slate-500">{{ $subscription->discount_reason }}</p>
                                        @endif
                                    </div>
                                </div>
                                {{-- Auto-renovación --}}
                                <div class="flex items-center gap-3 rounded-lg border border-slate-700/50 bg-slate-800/30 p-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $subscription->auto_renew ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-700/50 text-slate-500' }}">
                                        <i class="fas {{ $subscription->auto_renew ? 'fa-check-circle' : 'fa-times-circle' }} text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500">Auto-renovación</p>
                                        <p class="truncate text-sm font-medium text-slate-200">{{ $subscription->auto_renew ? 'Activada' : 'Desactivada' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if ($activeTab === 'payments')
                <div class="p-6">
                    <div class="ui-table-wrap border-0 rounded-none">
                        <table class="ui-table ui-table--nowrap-actions">
                            <thead>
                                <tr>
                                    <th>Período</th>
                                    <th>Vencimiento</th>
                                    <th>Monto</th>
                                    <th class="text-center">Estado</th>
                                    <th>Fecha Pago</th>
                                    <th>Ref.</th>
                                    <th class="text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payments as $payment)
                                    <tr>
                                        <td class="text-sm text-slate-300">{{ $payment->period_start?->format('d/m/Y') }} — {{ $payment->period_end?->format('d/m/Y') }}</td>
                                        <td class="text-sm text-slate-300">{{ $payment->due_date->format('d/m/Y') }}</td>
                                        <td class="text-sm font-medium text-white">$ {{ number_format($payment->amount, 2) }}</td>
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
                                        <td class="text-sm text-slate-400">{{ $payment->transaction_reference ?: '—' }}</td>
                                        <td class="text-left">
                                            @if ($payment->proof_of_payment)
                                                <a href="{{ app(\App\Services\PaymentService::class)->getReceiptUrl($payment->proof_of_payment) }}" target="_blank" class="ui-icon-action ui-icon-action--info" title="Ver comprobante">
                                                    <i class="fas fa-file-image"></i>
                                                </a>
                                            @endif
                                            @if (in_array($payment->status, ['pending', 'overdue']))
                                                <button type="button" wire:click="openPaymentModal({{ $payment->id }})" class="ui-icon-action ui-icon-action--success" title="Registrar pago">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-sm text-slate-400 py-8">No hay pagos registrados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($payments->hasPages())
                        <div class="mt-4 border-t border-slate-700/50 pt-4">
                            {{ $payments->links() }}
                        </div>
                    @endif
                </div>
            @endif

            @if ($activeTab === 'stats')
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-5">
                        <x-ui.stat-card variant="info" icon="fas fa-users" trend="Total" label="Usuarios" :value="number_format($stats['users_count'])" />
                        <x-ui.stat-card variant="info" icon="fas fa-user-friends" trend="Total" label="Clientes" :value="number_format($stats['customers_count'])" />
                        <x-ui.stat-card variant="info" icon="fas fa-box" trend="Total" label="Productos" :value="number_format($stats['products_count'])" />
                        <x-ui.stat-card variant="success" icon="fas fa-shopping-cart" trend="Total" label="Ventas" :value="number_format($stats['sales_count'])" />
                        <x-ui.stat-card variant="warning" icon="fas fa-dollar-sign" trend="Total" label="Facturación" :value="'$ ' . number_format($stats['total_revenue'], 0)" />
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Modal Suspender --}}
    @if ($showSuspendModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center" x-data x-cloak x-show="true" x-transition>
            <div class="fixed inset-0 bg-black/60" wire:click="closeSuspendModal"></div>
            <div class="relative w-full max-w-md mx-4" @click.stop>
                <div class="ui-panel">
                    <div class="ui-panel__header">
                        <h3 class="ui-panel__title text-rose-400">Suspender servicio</h3>
                    </div>
                    <div class="ui-panel__body space-y-4">
                        <p class="text-sm text-slate-300">¿Confirmás la suspensión de <strong class="text-white">{{ $company->name }}</strong>?</p>
                        <div>
                            <label class="mb-1 block text-xs text-slate-400">Motivo (opcional)</label>
                            <textarea wire:model="suspendReason" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" placeholder="Ej: Falta de pago"></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="closeSuspendModal" class="ui-btn ui-btn-ghost">Cancelar</button>
                            <button type="button" wire:click="suspend" class="ui-btn ui-btn-danger">Suspender</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Cambiar Plan --}}
    @if ($showChangePlanModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center" x-data x-cloak x-show="true" x-transition>
            <div class="fixed inset-0 bg-black/60" wire:click="closeChangePlanModal"></div>
            <div class="relative w-full max-w-md mx-4" @click.stop>
                <div class="ui-panel">
                    <div class="ui-panel__header">
                        <h3 class="ui-panel__title">Cambiar plan</h3>
                    </div>
                    <div class="ui-panel__body space-y-4">
                        <p class="text-sm text-slate-300">Plan actual: <strong class="text-white">{{ $subscription?->plan?->name }}</strong></p>
                        <div>
                            <label class="mb-1 block text-xs text-slate-400">Nuevo plan</label>
                            <select wire:model="newPlanId" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none">
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} — ${{ number_format($plan->base_price, 2) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="closeChangePlanModal" class="ui-btn ui-btn-ghost">Cancelar</button>
                            <button type="button" wire:click="changePlan" class="ui-btn ui-btn-primary">Cambiar plan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Editar Suscripción --}}
    @if ($showEditSubscriptionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" x-data x-cloak x-show="true" x-transition>
            <div class="fixed inset-0 bg-black/60" wire:click="closeEditSubscriptionModal"></div>
            <div class="relative w-full max-w-2xl mx-4 my-8" @click.stop>
                <div class="ui-panel">
                    <div class="ui-panel__header flex items-center justify-between">
                        <h3 class="ui-panel__title"><i class="fas fa-pen mr-2 text-cyan-400"></i>Editar Suscripción</h3>
                        <button type="button" wire:click="closeEditSubscriptionModal" class="text-slate-400 hover:text-slate-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="ui-panel__body space-y-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Fecha de inicio</label>
                                <input type="date" wire:model="editStartedAt" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                                @error('editStartedAt') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Día de cobro (1-28)</label>
                                <input type="number" wire:model="editBillingDay" min="1" max="28" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                                @error('editBillingDay') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Próxima fecha de cobro</label>
                                <input type="date" wire:model="editNextBillingDate" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                                @error('editNextBillingDate') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Fecha de corte (grace period)</label>
                                <input type="date" wire:model="editGracePeriodEnd" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                                @error('editGracePeriodEnd') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Monto mensual (USD)</label>
                                <input type="number" step="0.01" wire:model="editAmount" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                                @error('editAmount') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Descuento (USD)</label>
                                <input type="number" step="0.01" wire:model="editDiscountAmount" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                                @error('editDiscountAmount') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Motivo del descuento</label>
                            <input type="text" wire:model="editDiscountReason" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" placeholder="Ej: Promoción de lanzamiento" />
                            @error('editDiscountReason') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="editAutoRenew" wire:model="editAutoRenew" class="rounded border-slate-500 bg-slate-900 text-cyan-500 focus:ring-cyan-500" />
                            <label for="editAutoRenew" class="text-sm text-slate-200">Auto-renovación activada</label>
                        </div>
                        <div class="flex justify-end gap-2 border-t border-slate-700/50 pt-4">
                            <button type="button" wire:click="closeEditSubscriptionModal" class="ui-btn ui-btn-ghost">Cancelar</button>
                            <button type="button" wire:click="saveSubscription" class="ui-btn ui-btn-primary">
                                <i class="fas fa-save mr-1.5"></i> Guardar cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                            <p class="mt-1 text-xs text-slate-500">Formatos: JPG, PNG, PDF. Máx 4MB.</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Referencia de transacción</label>
                            <input type="text" wire:model="transactionReference" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" placeholder="Ej: Transferencia #12345" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Notas</label>
                            <textarea wire:model="paymentNotes" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" placeholder="Notas adicionales"></textarea>
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
