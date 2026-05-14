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
                    <button wire:click="switchTab('users')" @class(['pb-3 pt-4 px-2 text-sm font-medium border-b-2 transition-colors', 'border-cyan-400 text-cyan-400' => $activeTab === 'users', 'border-transparent text-slate-400 hover:text-slate-200' => $activeTab !== 'users'])>
                        <i class="fas fa-users mr-1.5"></i> Usuarios
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
                @php
                    $ds = $dashboardStats ?? [];
                    $salesAnalysis = $ds['sales_analysis'] ?? [];
                    $topProducts = $ds['top_products'] ?? collect();
                    $topCustomers = $ds['top_customers'] ?? collect();
                    $salesByCategory = $ds['sales_by_category'] ?? ['labels' => [], 'data' => [], 'categories' => collect()];
                    $monthlySales = $ds['monthly_sales'] ?? ['labels' => [], 'sales_data' => [], 'profit_data' => [], 'transactions_data' => []];
                    $cashCount = $ds['cash_count'] ?? ['current_cash_data' => [], 'closed_cash_counts' => collect()];
                    $customerStats = $ds['customer_stats'] ?? [];
                    $currentCashData = $cashCount['current_cash_data'] ?? [];
                    $closedCashCounts = $cashCount['closed_cash_counts'] ?? collect();
                @endphp

                <div class="p-6 space-y-6">

                    {{-- 1. Basic stat cards (existing) --}}
                    <div class="grid grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-5">
                        <x-ui.stat-card variant="info" icon="fas fa-users" trend="Total" label="Usuarios" :value="number_format($stats['users_count'] ?? 0)" />
                        <x-ui.stat-card variant="info" icon="fas fa-user-friends" trend="Total" label="Clientes" :value="number_format($stats['customers_count'] ?? 0)" />
                        <x-ui.stat-card variant="info" icon="fas fa-box" trend="Total" label="Productos" :value="number_format($stats['products_count'] ?? 0)" />
                        <x-ui.stat-card variant="success" icon="fas fa-shopping-cart" trend="Total" label="Ventas" :value="number_format($stats['sales_count'] ?? 0)" />
                        <x-ui.stat-card variant="warning" icon="fas fa-dollar-sign" trend="Total" label="Facturación" :value="'$ ' . number_format($stats['total_revenue'] ?? 0, 0)" />
                    </div>

                    {{-- 2. Sales Analysis Widgets --}}
                    <div class="ui-panel">
                        <div class="ui-panel__header">
                            <div>
                                <h2 class="ui-panel__title">Análisis de Ventas</h2>
                                <p class="ui-panel__subtitle">Métricas y rendimiento comercial</p>
                            </div>
                        </div>
                        <div class="ui-panel__body">
                            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                                <x-ui.stat-card variant="info" icon="fas fa-calendar-week"
                                    trend="Semanal"
                                    label="Ventas de la Semana"
                                    :value="'$ ' . number_format($salesAnalysis['weekly_sales'] ?? 0, 2)"
                                    meta="Hoy: $ {{ number_format($salesAnalysis['today_sales'] ?? 0, 2) }}" />

                                <x-ui.stat-card variant="success" icon="fas fa-receipt"
                                    trend="Promedio"
                                    label="Ticket Promedio"
                                    :value="'$ ' . number_format($salesAnalysis['average_customer_spend'] ?? 0, 2)"
                                    meta="Por venta en el período" />

                                <x-ui.stat-card variant="warning" icon="fas fa-chart-pie"
                                    trend="Margen"
                                    label="Ganancia Total Teórica"
                                    :value="'$ ' . number_format($salesAnalysis['total_profit'] ?? 0, 2)"
                                    meta="Margen de productos vendidos" />

                                <x-ui.stat-card variant="danger" icon="fas fa-calendar-alt"
                                    trend="Mensual"
                                    label="Rendimiento Mensual"
                                    :value="'$ ' . number_format($salesAnalysis['monthly_sales'] ?? 0, 2)"
                                    meta="Mes calendario actual" />
                            </div>
                        </div>
                    </div>

                    {{-- 3. Customer Stats --}}
                    <div class="ui-panel">
                        <div class="ui-panel__header">
                            <div>
                                <h2 class="ui-panel__title">Información de Clientes</h2>
                                <p class="ui-panel__subtitle">Gestión y análisis de clientes</p>
                            </div>
                        </div>
                        <div class="ui-panel__body">
                            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                                <x-ui.stat-card variant="info" icon="fas fa-users"
                                    trend="Total"
                                    label="Total Clientes"
                                    :value="number_format($customerStats['total_customers'] ?? 0)" />

                                <x-ui.stat-card variant="success" icon="fas fa-user-plus"
                                    trend="Nuevos"
                                    label="Nuevos Clientes"
                                    :value="number_format($customerStats['new_customers'] ?? 0)"
                                    meta="Registrados este mes" />

                                <x-ui.stat-card variant="warning" icon="fas fa-check-circle"
                                    trend="Verificados"
                                    label="Clientes Verificados"
                                    :value="number_format($customerStats['verified_customers'] ?? 0)"
                                    :meta="($customerStats['total_customers'] ?? 0) > 0 ? round(($customerStats['verified_customers'] ?? 0) / ($customerStats['total_customers'] ?? 1) * 100, 1) . '% del total' : 'Sin datos'" />

                                <x-ui.stat-card variant="danger" icon="fas fa-chart-pulse"
                                    trend="Actividad"
                                    label="Deuda Pendiente"
                                    :value="'$ ' . number_format($currentCashData['debt'] ?? 0, 2)"
                                    meta="Total por cobrar" />
                            </div>
                        </div>
                    </div>

                    {{-- 4. Cash Count Section --}}
                    <div class="ui-panel">
                        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="ui-panel__title">Arqueo de Caja</h2>
                                <p class="ui-panel__subtitle">Control financiero y gestión de efectivo</p>
                            </div>
                            @if ($currentCashData['opening_date'] ?? null)
                                <span class="ui-badge ui-badge-success text-xs">Caja Abierta</span>
                            @else
                                <span class="ui-badge ui-badge-danger text-xs">Caja Cerrada</span>
                            @endif
                        </div>
                        <div class="ui-panel__body">
                            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                                <x-ui.stat-card variant="info" icon="fas fa-balance-scale"
                                    trend="Balance"
                                    label="Balance Actual"
                                    :value="'$ ' . number_format($currentCashData['balance'] ?? 0, 2)"
                                    meta="Período seleccionado" />

                                <x-ui.stat-card variant="success" icon="fas fa-chart-line"
                                    :trend="'$ ' . number_format($salesAnalysis['monthly_sales'] ?? 0, 2)"
                                    label="Ventas del Período"
                                    :value="'$ ' . number_format($currentCashData['sales'] ?? 0, 2)"
                                    meta="Compras: $ {{ number_format($currentCashData['purchases'] ?? 0, 2) }}" />

                                <x-ui.stat-card variant="warning" icon="fas fa-hourglass-half"
                                    trend="Pendiente"
                                    label="Por Cobrar"
                                    :value="'$ ' . number_format($currentCashData['debt'] ?? 0, 2)"
                                    meta="Deudas pendientes" />

                                <x-ui.stat-card variant="neutral" icon="fas fa-hand-holding-usd"
                                    trend="Recibidos"
                                    label="Pagos de Deuda"
                                    :value="'$ ' . number_format($currentCashData['debt_payments'] ?? 0, 2)"
                                    meta="Este período" />
                            </div>

                            @if ($closedCashCounts->isNotEmpty())
                                <div class="mt-4 border-t border-slate-700/50 pt-4">
                                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Arqueos Cerrados Recientes</h4>
                                    <div class="overflow-x-auto">
                                        <table class="ui-table">
                                            <thead>
                                                <tr>
                                                    <th>Apertura</th>
                                                    <th>Cierre</th>
                                                    <th class="text-right">Monto Inicial</th>
                                                    <th class="text-right">Monto Final</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($closedCashCounts as $cc)
                                                    <tr>
                                                        <td class="text-sm text-slate-300">{{ $cc['opening_date_formatted'] }}</td>
                                                        <td class="text-sm text-slate-300">{{ $cc['closing_date_formatted'] }}</td>
                                                        <td class="text-right tabular-nums text-sm">$ {{ number_format($cc['initial_amount'], 2) }}</td>
                                                        <td class="text-right tabular-nums text-sm font-medium">$ {{ number_format($cc['final_amount'], 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 5. Top 10 Products + Top 5 Customers + Category Chart --}}
                    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                        {{-- Top 10 Productos --}}
                        <div class="ui-panel">
                            <div class="ui-panel__header">
                                <div>
                                    <h2 class="ui-panel__title">Top 10 Productos Más Vendidos</h2>
                                    <p class="ui-panel__subtitle">Ranking de productos con mejor rendimiento</p>
                                </div>
                            </div>
                            <div class="ui-panel__body !p-0">
                                {{-- Vista móvil: cards --}}
                                <div class="md:hidden space-y-1.5 p-2">
                                    @forelse ($topProducts as $index => $product)
                                        <div class="relative flex items-center gap-3 rounded-xl border border-slate-700/50 bg-slate-800/40 px-3 py-2.5 transition hover:bg-slate-800/70 overflow-hidden
                                            {{ $index < 3 ? 'border-l-[3px] border-l-amber-500/80' : '' }}">
                                            @if ($index < 3)
                                                <div class="absolute inset-0 bg-gradient-to-r from-amber-500/5 to-transparent pointer-events-none"></div>
                                            @endif
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[11px] font-bold flex-shrink-0
                                                {{ $index < 3 ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md shadow-amber-500/20' : 'bg-slate-700 text-slate-400' }}">
                                                {{ $index + 1 }}
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[13px] font-semibold text-slate-100 truncate">{{ $product->name }}</div>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-500/10 px-1.5 py-px text-[10px] font-semibold text-amber-400">
                                                        <i class="fas fa-chart-line text-[9px]"></i> {{ $product->times_sold }}x
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-500/10 px-1.5 py-px text-[10px] font-semibold text-emerald-400">
                                                        <i class="fas fa-cubes text-[9px]"></i> {{ $product->total_quantity }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-right flex-shrink-0">
                                                <div class="text-sm font-bold tabular-nums bg-gradient-to-r from-cyan-400 to-purple-400 bg-clip-text text-transparent">$ {{ number_format($product->total_revenue, 2) }}</div>
                                                <div class="text-[10px] text-slate-500 tabular-nums mt-px">c/u $ {{ number_format($product->sale_price, 2) }}</div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="px-3 py-8 text-center text-slate-500 text-sm">Sin datos de productos vendidos.</div>
                                    @endforelse
                                </div>

                                {{-- Vista desktop: tabla --}}
                                <div class="hidden md:block ui-table-wrap !rounded-none !border-0">
                                    <table class="ui-table">
                                        <thead>
                                            <tr>
                                                <th class="w-12 text-center">#</th>
                                                <th>Producto</th>
                                                <th class="text-center">Veces</th>
                                                <th class="text-center">Cant.</th>
                                                <th class="text-right">Precio</th>
                                                <th class="text-right">Ingresos</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($topProducts as $index => $product)
                                                <tr>
                                                    <td class="text-center">
                                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                                                            {{ $index < 3 ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white' : 'bg-slate-700 text-slate-400' }}">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    </td>
                                                    <td class="font-medium">{{ $product->name }}</td>
                                                    <td class="text-center">
                                                        <span class="ui-badge ui-badge-warning text-xs">{{ $product->times_sold }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="ui-badge ui-badge-success text-xs">{{ $product->total_quantity }}</span>
                                                    </td>
                                                    <td class="text-right tabular-nums">$ {{ number_format($product->sale_price, 2) }}</td>
                                                    <td class="text-right tabular-nums font-semibold">
                                                        $ {{ number_format($product->total_revenue, 2) }}
                                                        <div class="mt-1 h-1.5 w-full rounded-full bg-slate-700/60 overflow-hidden">
                                                            <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-purple-600"
                                                                style="width: {{ $topProducts->max('total_revenue') > 0 ? min(100, ($product->total_revenue / $topProducts->max('total_revenue')) * 100) : 0 }}%">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-center text-slate-500 py-8">Sin datos de productos vendidos.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Top 5 Clientes + Ventas por Categoría --}}
                        <div class="space-y-6">
                            {{-- Top 5 Clientes --}}
                            <div class="ui-panel">
                                <div class="ui-panel__header">
                                    <div>
                                        <h2 class="ui-panel__title">Top 5 Clientes</h2>
                                        <p class="ui-panel__subtitle">Mayor volumen de compras</p>
                                    </div>
                                </div>
                                <div class="ui-panel__body !p-0">
                                    {{-- Vista móvil: cards --}}
                                    <div class="md:hidden space-y-1.5 p-2">
                                        @forelse ($topCustomers as $index => $customer)
                                            <div class="relative flex items-center gap-3 rounded-xl border border-slate-700/50 bg-slate-800/40 px-3 py-2.5 transition hover:bg-slate-800/70 overflow-hidden
                                                {{ $index < 3 ? 'border-l-[3px] border-l-amber-500/80' : '' }}">
                                                @if ($index < 3)
                                                    <div class="absolute inset-0 bg-gradient-to-r from-amber-500/5 to-transparent pointer-events-none"></div>
                                                @endif
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[11px] font-bold flex-shrink-0
                                                    {{ $index < 3 ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md shadow-amber-500/20' : 'bg-slate-700 text-slate-400' }}">
                                                    {{ $index + 1 }}
                                                </span>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-[13px] font-semibold text-slate-100 truncate">{{ $customer->name }}</div>
                                                    <div class="flex items-center gap-1.5 mt-0.5">
                                                        <span class="inline-flex items-center gap-1 rounded-md bg-purple-500/10 px-1.5 py-px text-[10px] font-semibold text-purple-400">
                                                            <i class="fas fa-shopping-bag text-[9px]"></i> {{ $customer->total_sales }} ventas
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="text-right flex-shrink-0">
                                                    <div class="text-sm font-bold tabular-nums bg-gradient-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent">$ {{ number_format($customer->total_spent, 2) }}</div>
                                                    <div class="text-[10px] text-slate-500 mt-px">gastado</div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="px-3 py-8 text-center text-slate-500 text-sm">Sin datos de clientes.</div>
                                        @endforelse
                                    </div>

                                    {{-- Vista desktop: tabla --}}
                                    <div class="hidden md:block ui-table-wrap !rounded-none !border-0">
                                        <table class="ui-table">
                                            <thead>
                                                <tr>
                                                    <th class="w-12 text-center">#</th>
                                                    <th>Cliente</th>
                                                    <th class="text-right">Total Gastado</th>
                                                    <th class="text-center">Ventas</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($topCustomers as $index => $customer)
                                                    <tr>
                                                        <td class="text-center">
                                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                                                                {{ $index < 3 ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white' : 'bg-slate-700 text-slate-400' }}">
                                                                {{ $index + 1 }}
                                                            </span>
                                                        </td>
                                                        <td class="font-medium">{{ $customer->name }}</td>
                                                        <td class="text-right tabular-nums font-semibold">
                                                            $ {{ number_format($customer->total_spent, 2) }}
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="text-slate-300">{{ $customer->total_sales }}</span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="4" class="text-center text-slate-500 py-8">Sin datos de clientes.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- Ventas por Categoría Chart --}}
                            <div class="ui-panel">
                                <div class="ui-panel__header">
                                    <div>
                                        <h2 class="ui-panel__title">Ventas por Categoría</h2>
                                        <p class="ui-panel__subtitle">Distribución comercial principal</p>
                                    </div>
                                </div>
                                <div class="ui-panel__body">
                                    <div class="h-72">
                                        <canvas id="superAdminSalesByCategoryChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 6. Monthly Trends Chart --}}
                    <div class="ui-panel">
                        <div class="ui-panel__header">
                            <div>
                                <h2 class="ui-panel__title">Rendimiento Mensual de Ventas</h2>
                                <p class="ui-panel__subtitle">Ingresos, ganancias y volumen de transacciones</p>
                            </div>
                            @if (count($monthlySales['sales_data'] ?? []) > 0)
                                <div class="flex items-center gap-3 text-sm text-slate-400">
                                    <span>Prom: $ {{ number_format(collect($monthlySales['sales_data'])->avg(), 2) }}</span>
                                    <span>Máx: $ {{ number_format(collect($monthlySales['sales_data'])->max(), 2) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="ui-panel__body">
                            <div class="h-80">
                                <canvas id="superAdminSalesTrendsChart"></canvas>
                            </div>
                        </div>
                    </div>

                </div>
            @endif

            @if ($activeTab === 'users')
                <div class="p-6">
                    <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                        <div>
                            <h2 class="ui-panel__title">Usuarios de la Empresa</h2>
                            <p class="ui-panel__subtitle">Gestión de accesos y credenciales</p>
                        </div>
                    </div>
                    <div class="ui-table-wrap border-0 rounded-none">
                        <table class="ui-table ui-table--nowrap-actions">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr wire:key="user-row-{{ $user->id }}">
                                        <td class="font-medium text-white">{{ $user->name }}</td>
                                        <td class="text-sm text-slate-300">{{ $user->email }}</td>
                                        <td class="text-sm text-slate-300">
                                            {{ $user->roles->pluck('name')->join(', ') ?: 'Sin rol' }}
                                        </td>
                                        <td class="text-center">
                                            @if ($user->email_verified_at)
                                                <span class="ui-badge ui-badge-success">Verificado</span>
                                            @else
                                                <span class="ui-badge ui-badge-warning">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-left">
                                            <div class="ui-icon-action-row flex flex-nowrap items-center justify-start gap-1.5 md:gap-2">
                                                <button type="button" wire:click="openEditUserModal({{ $user->id }})" class="ui-icon-action ui-icon-action--primary" title="Editar usuario">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <button type="button"
                                                    x-on:click="
                                                        Swal.fire({
                                                            title: '¿Resetear preguntas?',
                                                            text: '{{ $user->name }} deberá configurar nuevas preguntas de seguridad al iniciar sesión.',
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#f59e0b',
                                                            cancelButtonColor: '#6b7280',
                                                            confirmButtonText: 'Sí, resetear',
                                                            cancelButtonText: 'Cancelar',
                                                            background: '#0f172a',
                                                            color: '#e2e8f0',
                                                            customClass: { popup: 'border border-slate-700 rounded-xl' }
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                $wire.resetSecurityQuestions({{ $user->id }});
                                                            }
                                                        });
                                                    "
                                                    class="ui-icon-action ui-icon-action--warning" title="Resetear preguntas de seguridad">
                                                    <i class="fas fa-shield-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-sm text-slate-400 py-8">No hay usuarios registrados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

                @push('js')
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Chart === 'undefined') {
                        console.error('[SuperAdmin Stats] Chart.js no está cargado.');
                        return;
                    }

                    Chart.defaults.color = '#94a3b8';
                    Chart.defaults.font.family = "'Inter', 'Nunito', system-ui, sans-serif";

                    initSuperAdminSalesTrendsChart();
                    initSuperAdminSalesByCategoryChart();
                });

                function initSuperAdminSalesTrendsChart() {
                    const canvas = document.getElementById('superAdminSalesTrendsChart');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');

                    const labels = @json($monthlySales['labels'] ?? []);
                    const salesData = @json($monthlySales['sales_data'] ?? []);
                    const profitData = @json($monthlySales['profit_data'] ?? []);
                    const transactionsData = @json($monthlySales['transactions_data'] ?? []);

                    const gradientSales = ctx.createLinearGradient(0, 0, 0, 350);
                    gradientSales.addColorStop(0, 'rgba(34, 211, 238, 0.35)');
                    gradientSales.addColorStop(1, 'rgba(34, 211, 238, 0.02)');

                    const gradientProfit = ctx.createLinearGradient(0, 0, 0, 350);
                    gradientProfit.addColorStop(0, 'rgba(167, 139, 250, 0.35)');
                    gradientProfit.addColorStop(1, 'rgba(167, 139, 250, 0.02)');

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Ventas Totales',
                                    data: salesData,
                                    type: 'line',
                                    borderColor: '#22d3ee',
                                    backgroundColor: gradientSales,
                                    borderWidth: 2.5,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#22d3ee',
                                    pointBorderColor: '#0f172a',
                                    pointBorderWidth: 2,
                                    pointHoverRadius: 7,
                                    pointRadius: 4,
                                    order: 1
                                },
                                {
                                    label: 'Ganancia Neta',
                                    data: profitData,
                                    type: 'line',
                                    borderColor: '#a78bfa',
                                    backgroundColor: gradientProfit,
                                    borderWidth: 2.5,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#a78bfa',
                                    pointBorderColor: '#0f172a',
                                    pointBorderWidth: 2,
                                    pointHoverRadius: 7,
                                    pointRadius: 4,
                                    order: 2
                                },
                                {
                                    label: 'Transacciones',
                                    data: transactionsData,
                                    type: 'bar',
                                    backgroundColor: 'rgba(251, 191, 36, 0.35)',
                                    borderColor: 'rgba(251, 191, 36, 0.8)',
                                    borderWidth: 1.5,
                                    borderRadius: 6,
                                    barPercentage: 0.5,
                                    yAxisID: 'y1',
                                    order: 3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: {
                                    labels: { usePointStyle: true, padding: 20, font: { size: 11 } }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                    padding: 12,
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) label += ': ';
                                            if (context.dataset.yAxisID === 'y1') {
                                                label += context.parsed.y + ' ventas';
                                            } else {
                                                label += '$' + (context.parsed.y || 0).toLocaleString('es-PE', { minimumFractionDigits: 2 });
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    position: 'left',
                                    title: { display: true, text: 'Monto ($)', font: { size: 11 } },
                                    grid: { color: 'rgba(148, 163, 184, 0.08)' },
                                    ticks: { callback: v => '$' + v.toLocaleString('es-PE') }
                                },
                                y1: {
                                    beginAtZero: true,
                                    position: 'right',
                                    title: { display: true, text: 'N° de Ventas', font: { size: 11 } },
                                    grid: { drawOnChartArea: false }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }

                function initSuperAdminSalesByCategoryChart() {
                    const canvas = document.getElementById('superAdminSalesByCategoryChart');
                    if (!canvas) return;

                    new Chart(canvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: @json($salesByCategory['labels'] ?? []),
                            datasets: [{
                                data: @json($salesByCategory['data'] ?? []),
                                backgroundColor: ['#22d3ee', '#60a5fa', '#6366f1', '#a78bfa', '#34d399', '#fb7185'],
                                borderColor: 'rgba(15, 23, 42, 0.8)',
                                borderWidth: 2,
                                hoverOffset: 8,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '58%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 16,
                                        font: { size: 11 }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.label + ': $' + context.parsed.toLocaleString('es-PE', { minimumFractionDigits: 2 });
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
                </script>
                @endpush
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
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Facturación</label>
                                <select wire:model.live="editBillingMode" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none">
                                    <option value="from_plan">Calcular según plan (precio lista + extras − descuento)</option>
                                    <option value="custom">Monto mensual acordado (fijo)</option>
                                </select>
                                @error('editBillingMode') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                            </div>
                            @if ($editBillingMode === 'custom')
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Monto mensual acordado (USD, antes del descuento)</label>
                                <input type="number" step="0.01" wire:model="editCustomRecurringAmount" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                                @error('editCustomRecurringAmount') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                            </div>
                            @endif
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

    {{-- Modal Editar Usuario --}}
    @if ($showEditUserModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" x-data x-cloak x-show="true" x-transition>
            <div class="fixed inset-0 bg-black/60" wire:click="closeEditUserModal"></div>
            <div class="relative w-full max-w-md mx-4 my-8" @click.stop>
                <div class="ui-panel">
                    <div class="ui-panel__header flex items-center justify-between">
                        <h3 class="ui-panel__title"><i class="fas fa-user-edit mr-2 text-cyan-400"></i>Editar Usuario</h3>
                        <button type="button" wire:click="closeEditUserModal" class="text-slate-400 hover:text-slate-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="ui-panel__body space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Nombre</label>
                            <input type="text" wire:model="editUserName" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                            @error('editUserName') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Email</label>
                            <input type="email" wire:model="editUserEmail" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" />
                            @error('editUserEmail') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="border-t border-slate-700/50 pt-4">
                            <p class="text-xs text-slate-400 mb-3">Dejá en blanco para mantener la contraseña actual.</p>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Nueva contraseña</label>
                                    <input type="password" wire:model="editUserPassword" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" placeholder="Mínimo 8 caracteres" />
                                    @error('editUserPassword') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Confirmar contraseña</label>
                                    <input type="password" wire:model="editUserPasswordConfirmation" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none" placeholder="Repetir contraseña" />
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 border-t border-slate-700/50 pt-4">
                            <button type="button" wire:click="closeEditUserModal" class="ui-btn ui-btn-ghost">Cancelar</button>
                            <button type="button" wire:click="saveUser" class="ui-btn ui-btn-primary">
                                <i class="fas fa-save mr-1.5"></i> Guardar cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
