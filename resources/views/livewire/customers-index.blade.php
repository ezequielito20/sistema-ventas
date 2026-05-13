@php
    $exchangeRate = $exchangeRate ?? 134;
@endphp

<div
    class="customers-index-v2 space-y-6"
    wire:key="customers-index-root"
    x-data="modalManagerV2()"
>
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Clientes</h1>
                <p class="ui-panel__subtitle">Listado, deudas y acciones por cliente.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($permissions['can_report'])
                    <button
                        type="button"
                        class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                        @click="openDebtReportV2()"
                    >
                        <i class="fas fa-file-invoice-dollar"></i> Deudas
                    </button>
                    <a
                        href="{{ route('admin.customers.report') }}"
                        target="_blank"
                        rel="noopener"
                        class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                    >
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    <a
                        href="{{ route('admin.customers.payment-history') }}"
                        class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                        wire:navigate
                    >
                        <i class="fas fa-history"></i> Historial
                    </a>
                @endif
                @if ($permissions['can_create'])
                    <a
                        href="{{ route('admin.customers.create') }}"
                        class="ui-btn ui-btn-primary text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                        wire:navigate
                    >
                        <i class="fas fa-plus"></i> Nuevo cliente
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-4">
        <x-ui.stat-card
            variant="info"
            icon="fas fa-users"
            trend="Base"
            label="Total clientes"
            :value="number_format($totalCustomers)"
            meta="Registrados"
        />
        <x-ui.stat-card
            variant="success"
            icon="fas fa-user-plus"
            trend="Este mes"
            label="Nuevos"
            :value="number_format($newCustomers)"
            meta="{{ $customerGrowth }}% del total"
        />
        <x-ui.stat-card
            variant="warning"
            icon="fas fa-money-bill-wave"
            trend="Ventas"
            label="Ingresos totales"
            :value="$currency->symbol . ' ' . number_format((float) $totalRevenue, 2)"
            meta="Histórico"
        />
        <x-ui.stat-card
            variant="danger"
            icon="fas fa-exclamation-triangle"
            trend="Arqueos previos"
            label="Morosos"
            :value="number_format($defaultersCount)"
            meta="Requieren atención"
        />
    </div>

    {{-- Tasa BCV + filtros: colapsados por defecto (mismo patrón que products-index) --}}
    <div
        class="ui-panel"
        x-data="{
            showFilters: (() => {
                const stored = localStorage.getItem('customers_filters_open');
                if (stored !== null) return stored === 'true';
                const initial = @js($filtersOpen);
                try { localStorage.setItem('customers_filters_open', initial); } catch (e) {}
                return initial;
            })(),
            toggleFilters() {
                this.showFilters = !this.showFilters;
                try { localStorage.setItem('customers_filters_open', this.showFilters); } catch (e) {}
            },
        }"
    >
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h2 class="ui-panel__title">Tasa y filtros</h2>
                <p class="ui-panel__subtitle">Tipo de cambio oficial, calculadora rápida y segmentación de clientes.</p>
            </div>
            <button
                type="button"
                class="ui-btn ui-btn-ghost w-full shrink-0 text-sm sm:w-auto"
                @click="toggleFilters()"
                :aria-expanded="showFilters"
            >
                <i class="fas" :class="showFilters ? 'fa-sliders-h' : 'fa-filter'"></i>
                <span x-text="showFilters ? 'Ocultar filtros' : 'Filtros avanzados'"></span>
            </button>
        </div>
        <div class="ui-panel__body space-y-5" x-show="showFilters" x-transition wire:loading.class.delay="opacity-60">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:items-start">
                <div
                    class="rounded-xl border border-slate-600/50 bg-slate-950/50 p-4 shadow-[0_0_24px_rgba(34,211,238,0.06)]"
                    x-data="exchangeRateWidget()"
                >
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-cyan-500/15 px-2.5 py-0.5 text-xs font-medium text-cyan-300">
                            <i class="fas fa-robot mr-1 text-[0.7rem]"></i>
                            Tasa actual (BCV)
                        </span>
                        <span class="text-xs text-slate-500" x-show="updatedAt" x-text="updatedAt ? 'Actualizado: ' + updatedAt : ''"></span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-sm font-medium text-slate-300">1 USD =</span>
                        <input
                            type="number"
                            x-model="exchangeRate"
                            readonly
                            class="w-28 rounded-lg border border-slate-600 bg-slate-900/80 px-3 py-2 text-center text-sm font-bold text-white tabular-nums"
                            placeholder="…"
                        >
                        <span class="text-sm font-medium text-slate-300">VES</span>
                        <button
                            type="button"
                            @click="forceUpdateFromApi()"
                            :disabled="updating"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-[0_0_20px_rgba(34,211,238,0.35)] transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50"
                            title="Actualizar tasa desde BCV ahora"
                        >
                            <i class="fas fa-sync-alt text-sm" :class="{ 'animate-spin': updating }"></i>
                        </button>
                    </div>
                    <div class="mt-4 border-t border-slate-700/60 pt-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Calcular:</span>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-2 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">$</span>
                                <input
                                    type="number"
                                    x-model="usdAmount"
                                    x-on:input="calcBs()"
                                    min="0"
                                    step="0.01"
                                    placeholder="USD"
                                    class="w-28 rounded-lg border border-slate-600 bg-slate-950/60 py-1.5 pl-6 pr-2 text-sm font-medium text-slate-100 placeholder:text-slate-600 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                                >
                            </div>
                            <span class="text-slate-500">=</span>
                            <div class="min-w-[7rem] rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-center">
                                <span class="text-sm font-bold text-emerald-300" x-text="bsResult || '— Bs'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Segmento</p>
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <button
                            type="button"
                            wire:click="setFilter('')"
                            title="Todos los clientes"
                            @class([
                                'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-cyan-500/60',
                                'border-cyan-400 bg-cyan-500/25 text-cyan-100 shadow-[0_0_18px_rgba(34,211,238,0.35)]' => $filter === '',
                                'border-slate-600 bg-slate-950/50 text-slate-400 hover:border-slate-500 hover:bg-slate-900/80' => $filter !== '',
                            ])
                        >
                            <i class="fas fa-list text-lg"></i>
                        </button>
                        <button
                            type="button"
                            wire:click="setFilter('current_debt')"
                            title="Deuda del arqueo actual"
                            @class([
                                'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/60',
                                'border-emerald-400 bg-emerald-500/25 text-emerald-100 shadow-[0_0_18px_rgba(16,185,129,0.35)]' => $filter === 'current_debt',
                                'border-slate-600 bg-slate-950/50 text-slate-400 hover:border-slate-500 hover:bg-slate-900/80' => $filter !== 'current_debt',
                            ])
                        >
                            <i class="fas fa-clock text-lg"></i>
                        </button>
                        <button
                            type="button"
                            wire:click="setFilter('defaulters')"
                            title="Clientes morosos"
                            @class([
                                'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-rose-500/60',
                                'border-rose-400 bg-rose-500/25 text-rose-100 shadow-[0_0_18px_rgba(244,63,94,0.35)]' => $filter === 'defaulters',
                                'border-slate-600 bg-slate-950/50 text-slate-400 hover:border-slate-500 hover:bg-slate-900/80' => $filter !== 'defaulters',
                            ])
                        >
                            <i class="fas fa-exclamation-triangle text-lg"></i>
                        </button>
                    </div>
                    <p class="text-right text-[0.7rem] leading-relaxed text-slate-500">
                        <span class="inline-flex items-center gap-1"><i class="fas fa-list text-cyan-500/80"></i> Todos</span>
                        ·
                        <span class="inline-flex items-center gap-1"><i class="fas fa-clock text-emerald-500/80"></i> Deuda arqueo actual</span>
                        ·
                        <span class="inline-flex items-center gap-1"><i class="fas fa-exclamation-triangle text-rose-500/80"></i> Morosos</span>
                    </p>
                </div>
            </div>

        </div>
    </div>

    <div class="ui-panel overflow-hidden">
        <div class="ui-panel__header">
            <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="shrink-0">
                    <h2 class="ui-panel__title">Listado</h2>
                    <p class="ui-panel__subtitle">
                        {{ $customers->total() }} resultado(s) · Página {{ $customers->currentPage() }} de {{ $customers->lastPage() }}
                    </p>
                </div>

                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto">
                    <div class="relative min-w-[16rem] flex-1 lg:min-w-[18rem]">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input
                            id="search-customers"
                            type="search"
                            wire:model.live.debounce.400ms="search"
                            placeholder="Buscar nombre, correo, teléfono o cédula…"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                            autocomplete="off"
                        >
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost text-sm" title="Limpiar búsqueda y filtro">
                            <i class="fas fa-eraser"></i>
                        </button>
                        @if ($permissions['can_destroy'] && ! $customers->isEmpty())
                            <button
                                type="button"
                                wire:click="toggleSelectionMode"
                                class="ui-btn {{ $selectionMode ? 'ui-btn-warning' : 'ui-btn-ghost' }} text-sm"
                            >
                                <i class="fas {{ $selectionMode ? 'fa-times-circle' : 'fa-check-square' }}"></i>
                                {{ $selectionMode ? 'Cancelar' : 'Seleccionar' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($customers->isEmpty())
            <div class="ui-panel__body">
                <p class="py-10 text-center text-sm text-slate-400">No hay clientes para los filtros seleccionados.</p>
            </div>
        @else
            @if ($selectionMode)
                <div class="flex flex-col gap-3 border-b border-slate-700/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-white">{{ count($selectedCustomerIds) }} cliente(s) seleccionado(s)</p>
                        <p class="text-xs text-slate-400">
                            La selección aplica a la página actual. No se eliminan clientes con ventas o pagos de deuda asociados.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="toggleSelectAllCurrentPage" class="ui-btn ui-btn-ghost text-sm">
                            <i class="fas {{ $allCurrentPageSelected ? 'fa-square-minus' : 'fa-square-check' }}"></i>
                            {{ $allCurrentPageSelected ? 'Limpiar página' : 'Seleccionar página' }}
                        </button>
                        <button
                            type="button"
                            wire:click="openBulkDeleteModal"
                            class="ui-btn ui-btn-danger text-sm"
                            @disabled(count($selectedCustomerIds) === 0)
                        >
                            <i class="fas fa-trash-alt"></i>
                            Eliminar seleccionados
                        </button>
                    </div>
                </div>
            @endif

            {{-- Tabla: md+ (scroll horizontal en pantallas estrechas vía .ui-table-wrap) --}}
            <div class="ui-panel__body hidden p-0 md:block" wire:loading.class.delay="opacity-60" wire:key="customers-table-wrap">
                <div class="ui-table-wrap border-0 rounded-none">
                    <table id="customersTable" class="ui-table ui-table--nowrap-actions">
                        <thead>
                            <tr>
                                @if ($selectionMode)
                                    <th class="w-12 text-center">
                                        <input
                                            type="checkbox"
                                            @checked($allCurrentPageSelected)
                                            wire:click="toggleSelectAllCurrentPage"
                                            class="rounded border-slate-500 bg-slate-900"
                                        />
                                    </th>
                                @endif
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Contacto</th>
                                <th>C.I.</th>
                                <th class="text-right">Compras</th>
                                <th class="text-right">Deuda</th>
                                <th class="text-right">Deuda Bs</th>
                                <th class="text-left">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="customersTableBody">
                                @foreach ($customers as $customer)
                                    @php
                                        $row = $customersData[$customer->id] ?? [
                                            'isDefaulter' => false,
                                            'previousDebt' => 0,
                                            'currentDebt' => 0,
                                            'hasOldSales' => false,
                                        ];
                                        $hasSales = ! empty($row['hasOldSales']) || ($row['currentDebt'] ?? 0) > 0;
                                        $purchasesTotal = ($row['previousDebt'] ?? 0) + ($row['currentDebt'] ?? 0);
                                    @endphp
                                    <tr
                                        wire:key="customer-row-{{ $customer->id }}"
                                        class="customers-v2-tr"
                                        data-customer-id="{{ $customer->id }}"
                                        data-defaulter="{{ $row['isDefaulter'] ? 'true' : 'false' }}"
                                        data-customer-name="{{ htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') }}"
                                    >
                                        @if ($selectionMode)
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    value="{{ $customer->id }}"
                                                    @checked(in_array($customer->id, $selectedCustomerIds, true))
                                                    wire:click="toggleCustomerSelection({{ $customer->id }})"
                                                    class="rounded border-slate-500 bg-slate-900"
                                                />
                                            </td>
                                        @endif
                                        <td class="tabular-nums text-slate-400">
                                            {{ $loop->iteration + ($customers->firstItem() - 1) }}
                                        </td>
                                        <td>
                                            <div class="flex min-w-0 items-center gap-3">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-cyan-500/25 bg-gradient-to-br from-slate-900 to-slate-800 text-sm font-semibold text-cyan-100 shadow-[0_0_16px_rgba(34,211,238,0.12)]">
                                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="customer-name truncate">{{ $customer->name }}</p>
                                                    <p class="customer-email truncate text-xs">{{ $customer->email ?: '—' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-slate-300">{{ $customer->phone ?: '—' }}</td>
                                        <td class="text-slate-300">
                                            <span class="id-badge">{{ $customer->nit_number ?: '—' }}</span>
                                        </td>
                                        <td class="text-right tabular-nums text-slate-200">
                                            @if ($hasSales)
                                                <span class="sales-amount">{{ $currency->symbol }} {{ number_format($purchasesTotal, 2) }}</span>
                                            @else
                                                <span class="no-sales text-slate-500">Sin ventas</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if ($customer->total_debt > 0)
                                                <div class="debt-amount debt-value inline-flex items-center justify-end gap-2">
                                                    <span class="tabular-nums text-slate-100">
                                                        {{ $currency->symbol }}
                                                        <span class="debt-amount-value">{{ number_format($customer->formatted_total_debt, 2) }}</span>
                                                    </span>
                                                    @if ($row['isDefaulter'])
                                                        <span class="text-amber-400" title="Cliente con deudas de arqueos anteriores">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                        </span>
                                                    @endif
                                                    <button
                                                        type="button"
                                                        class="ui-icon-action ui-icon-action--success"
                                                        title="Registrar pago"
                                                        onclick="spaPaymentHandlerV2.openPaymentModal({{ $customer->id }})"
                                                    >
                                                        <i class="fas fa-dollar-sign"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <span class="no-debt-badge text-slate-500">Sin deuda</span>
                                            @endif
                                        </td>
                                        <td class="text-right tabular-nums text-slate-300">
                                            @if ($customer->total_debt > 0)
                                                <span class="bs-debt" data-debt="{{ $customer->total_debt }}">
                                                    Bs. {{ number_format($customer->total_debt * $exchangeRate, 2) }}
                                                </span>
                                            @else
                                                <span class="text-slate-500">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="ui-icon-action-row flex flex-nowrap items-center justify-start gap-1.5 md:gap-2">
                                                @if ($permissions['can_show'])
                                                    <button
                                                        type="button"
                                                        class="ui-icon-action ui-icon-action--info"
                                                        title="Ver detalles"
                                                        @click="openCustomerDetailsModal({{ $customer->id }})"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endif
                                                @if ($permissions['can_edit'])
                                                    <a
                                                        href="{{ route('admin.customers.edit', $customer->id) }}"
                                                        class="ui-icon-action ui-icon-action--primary"
                                                        title="Editar"
                                                        wire:navigate
                                                    >
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                @endif
                                                @if ($permissions['can_destroy'])
                                                    <button
                                                        type="button"
                                                        class="ui-icon-action ui-icon-action--danger"
                                                        title="Eliminar"
                                                        onclick="deleteCustomer({{ $customer->id }})"
                                                    >
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                                @if ($permissions['can_create_sales'])
                                                    <a
                                                        href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                                        class="ui-icon-action text-cyan-300 hover:text-cyan-200"
                                                        title="Nueva venta"
                                                    >
                                                        <i class="fas fa-cart-plus"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
            </div>

            {{-- Tarjetas: solo en pantallas menores a md (como productos) --}}
            <div class="space-y-3 p-4 md:hidden" wire:loading.class.delay="opacity-60">
                <div id="mobileCustomersContainer" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($customers as $customer)
                        @php
                            $row = $customersData[$customer->id] ?? [
                                'isDefaulter' => false,
                                'previousDebt' => 0,
                                'currentDebt' => 0,
                                'hasOldSales' => false,
                            ];
                            $hasSales = ! empty($row['hasOldSales']) || ($row['currentDebt'] ?? 0) > 0;
                            $purchasesTotal = ($row['previousDebt'] ?? 0) + ($row['currentDebt'] ?? 0);
                        @endphp
                        <div
                            class="rounded-xl border border-slate-600/50 bg-slate-950/45 p-4 shadow-[0_0_20px_rgba(15,23,42,0.5)]"
                            wire:key="customer-card-{{ $customer->id }}"
                            data-defaulter="{{ $row['isDefaulter'] ? 'true' : 'false' }}"
                            data-customer-name="{{ htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                @if ($selectionMode)
                                    <input
                                        type="checkbox"
                                        @checked(in_array($customer->id, $selectedCustomerIds, true))
                                        wire:click="toggleCustomerSelection({{ $customer->id }})"
                                        class="mt-1 shrink-0 rounded border-slate-500 bg-slate-900"
                                    />
                                @endif
                                <div class="flex min-w-0 flex-1 gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-cyan-500/20 bg-slate-900 text-base font-bold text-cyan-200">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-50">{{ $customer->name }}</p>
                                        <p class="truncate text-xs text-slate-400">{{ $customer->email ?: 'Sin correo' }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $customer->phone ?: 'Sin teléfono' }}</p>
                                    </div>
                                </div>
                                @if ($row['isDefaulter'])
                                    <span class="text-amber-400" title="Moroso"><i class="fas fa-exclamation-triangle"></i></span>
                                @endif
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-lg border border-slate-700/50 bg-slate-900/50 p-2">
                                    <p class="text-slate-500">Compras</p>
                                    <p class="font-semibold tabular-nums text-slate-100">
                                        @if ($hasSales)
                                            {{ $currency->symbol }} {{ number_format($purchasesTotal, 2) }}
                                        @else
                                            —
                                        @endif
                                    </p>
                                </div>
                                <div class="rounded-lg border border-slate-700/50 bg-slate-900/50 p-2">
                                    <p class="text-slate-500">Deuda</p>
                                    @if ($customer->total_debt > 0)
                                        <p class="font-semibold tabular-nums text-slate-100">
                                            {{ $currency->symbol }} {{ number_format($customer->formatted_total_debt, 2) }}
                                        </p>
                                        <p class="mt-1 tabular-nums text-[0.7rem] font-medium text-slate-400">
                                            <span class="bs-debt" data-debt="{{ $customer->total_debt }}">
                                                Bs. {{ number_format($customer->total_debt * $exchangeRate, 2) }}
                                            </span>
                                        </p>
                                    @else
                                        <p class="font-semibold text-slate-500">Sin deuda</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3 flex flex-wrap items-center justify-end gap-2 border-t border-slate-700/50 pt-3">
                                @if ($permissions['can_show'])
                                    <button
                                        type="button"
                                        class="ui-btn ui-btn-ghost flex-1 text-xs sm:flex-none sm:text-sm"
                                        @click="openCustomerDetailsModal({{ $customer->id }})"
                                    >
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                @endif
                                @if ($permissions['can_edit'])
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}" class="ui-btn ui-btn-ghost flex-1 text-xs sm:flex-none sm:text-sm" wire:navigate>
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                @endif
                                @if ($customer->total_debt > 0)
                                    <button
                                        type="button"
                                        class="ui-btn ui-btn-ghost flex-1 text-xs sm:flex-none sm:text-sm"
                                        onclick="spaPaymentHandlerV2.openPaymentModal({{ $customer->id }})"
                                    >
                                        <i class="fas fa-dollar-sign"></i> Pago
                                    </button>
                                @endif
                                @if ($permissions['can_destroy'])
                                    <button
                                        type="button"
                                        class="ui-btn ui-btn-danger flex-1 text-xs sm:flex-none sm:text-sm"
                                        onclick="deleteCustomer({{ $customer->id }})"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                                @if ($permissions['can_create_sales'])
                                    <a
                                        href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                        class="ui-btn ui-btn-primary flex-1 text-xs sm:flex-none sm:text-sm"
                                    >
                                        <i class="fas fa-cart-plus"></i> Venta
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if ($customers instanceof \Illuminate\Pagination\LengthAwarePaginator && ! $customers->isEmpty())
        <div>
            <x-ui.pagination :paginator="$customers" scroll-into-view=".ui-panel.overflow-hidden" />
        </div>
    @endif

    @if ($showBulkDeleteModal)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center bg-[#020617]/90 p-4 backdrop-blur-md"
            wire:click.self="closeBulkDeleteModal"
            x-data
            x-on:keydown.escape.window="$wire.closeBulkDeleteModal()"
            aria-modal="true"
            role="dialog"
        >
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75)]">
                <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Eliminar clientes seleccionados?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                Se intentará eliminar <span class="font-medium text-white">{{ count($selectedCustomerIds) }} cliente(s)</span>.
                                Los que tengan ventas o pagos de deuda asociados no se eliminarán y se indicará el motivo.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="closeBulkDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmBulkDelete" class="ui-btn ui-btn-danger text-sm">
                        <i class="fas fa-trash-alt mr-1.5"></i>
                        Sí, eliminar seleccionados
                    </button>
                </div>
            </div>
        </div>
    @endif

    @include('admin.v2.customers.partials.customer-details-modal', ['currency' => $currency])
    @include('admin.v2.customers.partials.debt-report-modal', ['currency' => $currency])
    @include('admin.v2.customers.partials.debt-payment-modal', ['currency' => $currency])
    @include('admin.customers.partials.index-modals', ['currency' => $currency])
</div>

@once
    @push('css')
        <link rel="stylesheet" href="{{ asset('css/admin/customers/index.css') }}">
        <link rel="preload" href="{{ asset('css/admin/customers/debt-report-modal.css') }}" as="style"
            onload="this.onload=null;this.rel='stylesheet'">
        <noscript>
            <link rel="stylesheet" href="{{ asset('css/admin/customers/debt-report-modal.css') }}">
        </noscript>
        <link rel="preload" href="{{ asset('css/admin/customers/payment-history.css') }}" as="style"
            onload="this.onload=null;this.rel='stylesheet'">
        <noscript>
            <link rel="stylesheet" href="{{ asset('css/admin/customers/payment-history.css') }}">
        </noscript>
        <style>
            #debtPaymentModal { transition: opacity 0.3s ease-in-out; }
            #debtPaymentModal.show { opacity: 1; visibility: visible; }
            #debtPaymentModal.hide { opacity: 0; visibility: hidden; }
            .modal-open { overflow: hidden; }
            #debtPaymentModal .relative { animation: modalSlideIn 0.3s ease-out; }
            @keyframes modalSlideIn {
                from { opacity: 0; transform: scale(0.95) translateY(-20px); }
                to { opacity: 1; transform: scale(1) translateY(0); }
            }
        </style>
    @endpush

    @push('js')
        <script>
            window.totalCustomers = {{ (int) $totalCustomers }};
            window.exchangeRate = {{ (float) $exchangeRate }};
            window.exchangeRateUpdatedAt = @json($exchangeRateUpdatedAt ?? '');
            window.csrfToken = '{{ csrf_token() }}';
            window.exchangeRateUpdateUrl = '{{ route('admin.exchange-rate.update') }}';
        </script>
        <script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>
        <script src="{{ asset('js/admin/customers/index.js') }}" defer></script>
        <script src="{{ asset('js/admin/customers/modals.js') }}" defer></script>
        <script src="{{ asset('js/admin/customers/modals-v2.js') }}" defer></script>
    @endpush
@endonce
