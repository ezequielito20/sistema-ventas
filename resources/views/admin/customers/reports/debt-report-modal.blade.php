<!-- Header del Modal -->
<div
    class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-t-2xl">
    <div class="flex items-center space-x-4">
        <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
            <i class="fas fa-file-invoice-dollar text-white text-lg"></i>
        </div>
        <div>
            <h5 class="text-xl font-bold text-white">Reporte de Deudas</h5>
            <p class="text-sm text-blue-100">Visualiza y filtra clientes con deudas pendientes</p>
        </div>
    </div>
    <button type="button" @click="closeModal('debtReportModal')"
        class="w-10 h-10 bg-white/20 hover:bg-white/30 text-white hover:text-white rounded-lg flex items-center justify-center transition-all duration-200 backdrop-blur-sm">
        <i class="fas fa-times"></i>
    </button>
</div>

<!-- Body del Modal -->
<div class="p-6 max-h-[80vh] overflow-y-auto" x-data="{ showFilters: false }">
    <!-- Información de la Empresa y Tipo de Cambio -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Información de la Empresa -->
        <div
            class="bg-gradient-to-br from-blue-50/90 via-indigo-50/75 to-purple-50/90 rounded-xl shadow-sm border border-blue-200/60 p-4 backdrop-blur-sm">
            <div class="flex items-center space-x-3">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-md">
                    <i class="fas fa-building text-white"></i>
                </div>
                <div>
                    <h6 class="font-semibold text-gray-900">{{ $company->name }}</h6>
                    <p class="text-sm text-gray-600">
                        <i class="far fa-clock mr-1 text-blue-500"></i>{{ date('d/m/Y H:i:s') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Tipo de Cambio -->
        <div
            class="bg-gradient-to-br from-green-50/90 via-emerald-50/75 to-teal-50/90 rounded-xl shadow-sm border border-green-200/60 p-4 backdrop-blur-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-sm font-medium text-gray-700">Conversión:</span>
                    <span class="text-sm font-semibold text-gray-900">1 USD</span>
                    <input type="number" id="modalExchangeRate"
                        class="w-20 px-2 py-1 text-center border border-gray-300 rounded-lg text-sm font-semibold"
                        step="0.01" min="0.01" value="{{ $exchangeRate }}">
                </div>
                <div class="flex items-center space-x-2">
                    <!-- Botón Filtros: Tamaño Original y Mejor Visibilidad -->
                    <button type="button" @click="showFilters = !showFilters"
                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-all duration-200 flex items-center shadow-sm active:scale-95"
                        :class="showFilters ? 'bg-indigo-800 ring-2 ring-indigo-300' : ''">
                        <i class="fas fa-filter mr-1.5"></i>
                        <span class="hidden md:inline">Filtros</span>
                    </button>

                    <button
                        class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors duration-200"
                        id="updateModalExchangeRate" title="Actualizar tipo de cambio">
                        <i class="fas fa-sync-alt mr-1"></i>
                    </button>
                    <button type="button" id="downloadPdfBtn"
                        class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium transition-colors duration-200"
                        title="Ver PDF de deudores">
                        <i class="fas fa-file-pdf mr-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Profesionales con Flexbox para máxima compatibilidad de fila única -->
    <div x-show="showFilters" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 transition-all duration-300"
        style="display: none;">
        <div class="flex flex-wrap gap-3 items-end">
            <!-- Búsqueda (Un poco más ancho) -->
            <div class="flex-[1.5] min-w-[200px] space-y-1">
                <label class="text-xs font-semibold text-gray-600 flex items-center ml-1">
                    <i class="fas fa-search mr-1.5 text-blue-500"></i>Buscar
                </label>
                <input type="text" id="searchFilter"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    placeholder="Cliente...">
            </div>

            <!-- Orden -->
            <div class="flex-1 min-w-[150px] space-y-1">
                <label class="text-xs font-semibold text-gray-600 flex items-center ml-1">
                    <i class="fas fa-sort-amount-down mr-1.5 text-blue-500"></i>Vistas
                </label>
                <select id="orderFilter"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                    <option value="debt_desc" selected>Deuda Mayor</option>
                    <option value="debt_asc">Deuda Menor</option>
                    <option value="debt_date_asc">Más antiguos</option>
                    <option value="debt_date_desc">Más recientes</option>
                    <option value="name_asc">Nombre (A-Z)</option>
                    <option value="name_desc">Nombre (Z-A)</option>
                </select>
            </div>

            <!-- Tipo -->
            <div class="flex-1 min-w-[130px] space-y-1">
                <label class="text-xs font-semibold text-gray-600 flex items-center ml-1">
                    <i class="fas fa-filter mr-1.5 text-blue-500"></i>Estado
                </label>
                <select id="debtTypeFilter"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                    <option value="">Todos</option>
                    <option value="defaulters">Morosos</option>
                    <option value="current">Al día</option>
                </select>
            </div>

            <!-- Desde -->
            <div class="flex-1 min-w-[140px] space-y-1">
                <label class="text-xs font-semibold text-gray-600 flex items-center ml-1">
                    <i class="fas fa-calendar mr-1.5 text-orange-500"></i>Desde
                </label>
                <input type="date" id="dateFromFilter"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200">
            </div>

            <!-- Hasta -->
            <div class="flex-1 min-w-[140px] space-y-1">
                <label class="text-xs font-semibold text-gray-600 flex items-center ml-1">
                    <i class="fas fa-calendar mr-1.5 text-indigo-500"></i>Hasta
                </label>
                <input type="date" id="dateToFilter"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200">
            </div>

            <!-- Botón Limpiar -->
            <div class="min-w-[100px]">
                <button type="button" id="clearFiltersBtn"
                    class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center justify-center group active:scale-95 border border-gray-200">
                    <i class="fas fa-trash-alt lg:mr-0 xl:mr-2 group-hover:text-red-500 transition-colors"></i>
                    <span class="sm:inline lg:hidden xl:inline">Limpiar</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Resumen de Estadísticas -->
    <div id="debtReportStats"
        class="bg-gradient-to-br from-purple-50/95 via-pink-50/80 to-rose-50/95 rounded-xl shadow-sm border border-purple-200/70 p-6 mb-6 backdrop-blur-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Clientes -->
            <div class="flex items-center space-x-3">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-lg">
                    <i class="fas fa-users text-white"></i>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Total Clientes</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $customers->count() }}</div>
                </div>
            </div>

            <!-- Morosos -->
            <div class="flex items-center space-x-3">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg flex items-center justify-center shadow-lg">
                    <i class="fas fa-user-clock text-white"></i>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Morosos</div>
                    <div class="text-2xl font-bold text-red-600">{{ $defaultersCount }} / $
                        {{ number_format($defaultersDebt, 2) }}</div>
                </div>
            </div>

            <!-- Deuda Actual -->
            <div class="flex items-center space-x-3">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-lg flex items-center justify-center shadow-lg">
                    <i class="fas fa-user-check text-white"></i>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Deuda Actual</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $currentDebtorsCount }} / $
                        {{ number_format($currentDebt, 2) }}</div>
                </div>
            </div>

            <!-- Deuda Total -->
            <div class="flex items-center space-x-3">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg flex items-center justify-center shadow-lg">
                    <i class="fas fa-dollar-sign text-white"></i>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Deuda Total</div>
                    <div class="text-2xl font-bold text-red-600">$ {{ number_format($totalDebt, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Total en Bolívares -->
        <div class="mt-4 flex justify-end space-x-4">
            <button
                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold transition-colors duration-200">
                $ {{ number_format($totalDebt, 2) }}
            </button>
            <button
                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition-colors duration-200 modal-bs-debt"
                data-debt="{{ $totalDebt }}">
                Bs. {{ number_format($totalDebt * ($exchangeRate ?? 1), 2) }}
            </button>
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div id="debtReportTable" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-500">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-hashtag text-blue-200"></i>
                                <span>#</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-500">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-user text-blue-200"></i>
                                <span>Cliente</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-500">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-phone text-blue-200"></i>
                                <span>Contacto</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-500">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-calendar-alt text-blue-200"></i>
                                <span>Debe desde</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-500">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-dollar-sign text-blue-200"></i>
                                <span>Deuda Total</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-500">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-coins text-blue-200"></i>
                                <span>Deuda en Bs</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $index => $customer)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">
                                {{ ($customers->firstItem() ?? 0) + $index }}
                            </td>
                            <td class="px-4 py-3 border-b border-gray-100">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900">{{ $customer->name }}</span>
                                    @if ($customersData[$customer->id]['isDefaulter'])
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Moroso
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Actual
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 border-b border-gray-100">
                                {{ $customer->phone }}</td>
                            <td class="px-4 py-3 text-sm border-b border-gray-100">
                                @if ($customersData[$customer->id]['debt_since_date'])
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar-day text-orange-500"></i>
                                        <span
                                            class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($customersData[$customer->id]['debt_since_date'])->format('d/m/Y') }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs italic">Sin fecha</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 border-b border-gray-100">$
                                {{ number_format($customer->total_debt, 2) }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 border-b border-gray-100 bs-debt"
                                data-debt="{{ $customer->total_debt }}">
                                Bs. {{ number_format($customer->total_debt * ($exchangeRate ?? 1), 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($customers instanceof \Illuminate\Pagination\LengthAwarePaginator && $customers->hasPages())
        <div id="debtReportPagination" class="mt-4 px-2 pb-1 sm:px-0">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-center gap-3">
                    <p class="text-xs text-slate-400 sm:text-sm">
                        Mostrando
                        <span class="font-semibold text-slate-100">{{ $customers->firstItem() }}</span>
                        a
                        <span class="font-semibold text-slate-100">{{ $customers->lastItem() }}</span>
                        de
                        <span class="font-semibold text-slate-100">{{ $customers->total() }}</span>
                        resultados
                    </p>

                    {{-- Selector de registros por página --}}
                    <select id="perPageFilter"
                        class="rounded-lg border border-slate-600 bg-slate-950/60 py-1.5 pl-2 pr-7 text-xs text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 cursor-pointer">
                        @foreach ([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ $customers->perPage() == $size ? 'selected' : '' }}>
                                {{ $size }} por página
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 min-w-0">
                    @if ($customers->onFirstPage())
                        <span class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg border border-slate-600/60 bg-slate-800/55 px-3 text-sm text-slate-500">
                            <i class="fas fa-chevron-left text-[0.68rem]"></i>
                        </span>
                    @else
                        <a href="{{ $customers->previousPageUrl() }}" data-page="{{ $customers->currentPage() - 1 }}" class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg border border-slate-600 bg-slate-900/70 px-3 text-sm text-slate-200 transition hover:border-cyan-500/55 hover:bg-cyan-500/10 hover:text-cyan-100">
                            <i class="fas fa-chevron-left text-[0.68rem]"></i>
                        </a>
                    @endif

                    <div class="min-w-0 flex-1 overflow-x-auto">
                        <div class="inline-flex min-w-max items-center gap-1.5 pr-1">
                            @if (isset($customers->smartLinks) && is_array($customers->smartLinks))
                                @foreach ($customers->smartLinks as $link)
                                    @if ($link['isSeparator'])
                                        <span class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg px-2 text-sm text-slate-500">…</span>
                                    @elseif ($link['active'])
                                        <span class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg border border-cyan-400/55 bg-gradient-to-br from-cyan-500 to-blue-600 px-3 text-sm font-semibold text-white shadow-[0_0_16px_rgba(34,211,238,0.28)]">{{ $link['label'] }}</span>
                                    @else
                                        <a href="{{ $link['url'] }}" data-page="{{ $link['page'] }}" class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg border border-slate-600 bg-slate-900/70 px-3 text-sm text-slate-200 transition hover:border-cyan-500/55 hover:bg-cyan-500/10 hover:text-cyan-100">{{ $link['label'] }}</a>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>

                    @if ($customers->hasMorePages())
                        <a href="{{ $customers->nextPageUrl() }}" data-page="{{ $customers->currentPage() + 1 }}" class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg border border-slate-600 bg-slate-900/70 px-3 text-sm text-slate-200 transition hover:border-cyan-500/55 hover:bg-cyan-500/10 hover:text-cyan-100">
                            <i class="fas fa-chevron-right text-[0.68rem]"></i>
                        </a>
                    @else
                        <span class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg border border-slate-600/60 bg-slate-800/55 px-3 text-sm text-slate-500">
                            <i class="fas fa-chevron-right text-[0.68rem]"></i>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Footer del Modal -->
<div class="flex items-center justify-end p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
    <button type="button" @click="closeModal('debtReportModal')"
        class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors duration-200">
        <i class="fas fa-times mr-2"></i>Cerrar
    </button>
</div>

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/customers/debt-report-modal.css') }}">
@endpush

@push('js')
    <script>
        // Pasar datos de PHP a JavaScript
        window.debtReportModalData = {
            exchangeRate: {{ $exchangeRate ?? 1 }},
            totalDebt: {{ $totalDebt }},
            defaultersCount: {{ $defaultersCount }},
            defaultersDebt: {{ $defaultersDebt }},
            currentDebtorsCount: {{ $currentDebtorsCount }},
            currentDebt: {{ $currentDebt }},
            customersCount: {{ $customers->count() }}
        };
    </script>
    <script src="{{ asset('js/admin/customers/debt-report-modal.js') }}" defer></script>
@endpush
