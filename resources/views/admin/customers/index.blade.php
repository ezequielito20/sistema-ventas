@extends('layouts.app')

@section('title', 'Gestión de Clientes')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/customers/index.css') }}">
    <!-- CSS no crítico cargado de forma lazy -->
    <link rel="preload" href="{{ asset('css/admin/customers/debt-report-modal.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('css/admin/customers/debt-report-modal.css') }}"></noscript>
    
    <link rel="preload" href="{{ asset('css/admin/customers/payment-history.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('css/admin/customers/payment-history.css') }}"></noscript>
    
    <style>
        /* Estilos para el modal SPA */
        #debtPaymentModal {
            transition: opacity 0.3s ease-in-out;
        }
        
        #debtPaymentModal.show {
            opacity: 1;
            visibility: visible;
        }
        
        #debtPaymentModal.hide {
            opacity: 0;
            visibility: hidden;
        }
        
        .modal-open {
            overflow: hidden;
        }
        
        /* Animación de entrada del modal */
        #debtPaymentModal .relative {
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>
@endpush

@push('js')
    <script>
        // Pasar datos críticos a JavaScript
        window.totalCustomers = {{ $totalCustomers ?? 0 }};
        window.exchangeRate = {{ $exchangeRate ?? 134 }};
        window.csrfToken = '{{ csrf_token() }}';
    </script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('js/admin/customers/index.js') }}" defer></script>
    <script src="{{ asset('js/admin/customers/modals.js') }}" defer></script>
@endpush

@section('content')

    <!-- Contenedor Principal con Gradiente de Fondo -->
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-100" x-data="modalManager()">

        <!-- Hero Section Compacto -->
        <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-xl shadow-lg mb-6"
            x-data="heroSection()">
            <!-- Background Pattern -->
            <div class="absolute inset-0 bg-black bg-opacity-10">
                <div class="absolute inset-0 bg-gradient-to-r from-white/5 to-transparent"></div>
                <!-- Decorative circles -->
                <div
                    class="absolute top-0 left-0 w-48 h-48 bg-white rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-blob">
                </div>
                <div
                    class="absolute top-0 right-0 w-48 h-48 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-blob animation-delay-2000">
                </div>
                <div
                    class="absolute -bottom-4 left-16 w-48 h-48 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-blob animation-delay-4000">
                </div>
            </div>

            <div class="relative px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <!-- Hero Content -->
                    <div class="flex-1 lg:pr-6">
                        <div class="flex items-center mb-2">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                    <i class="fas fa-users text-2xl text-white"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <h1 class="text-2xl sm:text-3xl font-bold text-white">
                            Gestión de Clientes
                        </h1>
                    </div>
                </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 lg:mt-0 lg:flex-shrink-0">
                        <div class="flex flex-wrap gap-2 justify-center lg:justify-end">
                        @if($permissions['can_report'])
                                <button @click="openDebtReport()"
                                    class="group relative inline-flex items-center px-3 py-2 bg-white/20 backdrop-blur-sm text-white font-medium rounded-lg hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200 transform hover:scale-105 hover:-translate-y-0.5"
                                    title="Reporte de Deudas">
                                    <i class="fas fa-file-invoice-dollar text-base mr-1.5 text-blue-200"></i>
                                    <span class="hidden sm:inline text-sm">Deudas</span>
                                    <!-- Tooltip -->
                                    <div
                                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                        Reporte de Deudas
                                    </div>
                            </button>
                        @endif

                        @if($permissions['can_report'])
                                <a href="{{ route('admin.customers.report') }}" target="_blank"
                                    class="group relative inline-flex items-center px-3 py-2 bg-white/20 backdrop-blur-sm text-white font-medium rounded-lg hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200 transform hover:scale-105 hover:-translate-y-0.5"
                                    title="Reporte PDF">
                                    <i class="fas fa-file-pdf text-base mr-1.5 text-red-200"></i>
                                    <span class="hidden sm:inline text-sm">PDF</span>
                                    <!-- Tooltip -->
                                    <div
                                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                        Reporte PDF
                                    </div>
                            </a>
                        @endif

                        @if($permissions['can_report'])
                                <a href="{{ route('admin.customers.payment-history') }}"
                                    class="group relative inline-flex items-center px-3 py-2 bg-white/20 backdrop-blur-sm text-white font-medium rounded-lg hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200 transform hover:scale-105 hover:-translate-y-0.5"
                                    title="Historial de Pagos">
                                    <i class="fas fa-history text-base mr-1.5 text-yellow-200"></i>
                                    <span class="hidden sm:inline text-sm">Historial</span>
                                    <!-- Tooltip -->
                                    <div
                                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                        Historial de Pagos
                                    </div>
                            </a>
                        @endif

                        @if($permissions['can_create'])
                                <a href="{{ route('admin.customers.create') }}"
                                    class="group relative inline-flex items-center px-4 py-2 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 hover:-translate-y-0.5 shadow-md"
                                    title="Nuevo Cliente">
                                    <i class="fas fa-plus text-base mr-1.5"></i>
                                    <span class="hidden sm:inline text-sm">Nuevo Cliente</span>
                                    <!-- Tooltip -->
                                    <div
                                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                        Crear Nuevo Cliente
                                    </div>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Stats Widgets Compactos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6">
            <!-- Total de Clientes -->
            <x-dashboard-widget 
                title="Total de Clientes"
                value="{{ $totalCustomers }}"
                valueType="number"
                icon="fas fa-users"
                trend="{{ $customerGrowth > 0 ? '+' . $customerGrowth : $customerGrowth }}%"
                trendIcon="{{ $customerGrowth > 0 ? 'fas fa-arrow-up' : ($customerGrowth < 0 ? 'fas fa-arrow-down' : 'fas fa-minus') }}"
                trendColor="{{ $customerGrowth > 0 ? 'text-green-300' : ($customerGrowth < 0 ? 'text-red-300' : 'text-gray-300') }}"
                subtitle="Crecimiento"
                subtitleIcon="fas fa-chart-line"
                gradientFrom="from-blue-500"
                gradientTo="to-blue-600"
                progressWidth="100%"
                progressGradientFrom="from-blue-400"
                progressGradientTo="to-blue-500"
            />

            <!-- Nuevos este Mes -->
            <x-dashboard-widget 
                title="Nuevos este Mes"
                value="{{ $newCustomers }}"
                valueType="number"
                icon="fas fa-user-plus"
                trend="Nuevos"
                trendIcon="fas fa-calendar-month"
                trendColor="text-yellow-300"
                subtitle="{{ $totalCustomers > 0 ? round(($newCustomers / $totalCustomers) * 100, 1) . '% del total' : '0% del total' }}"
                subtitleIcon="fas fa-percentage"
                gradientFrom="from-yellow-500"
                gradientTo="to-orange-500"
                progressWidth="{{ $totalCustomers > 0 ? ($newCustomers / $totalCustomers) * 100 : 0 }}%"
                progressGradientFrom="from-yellow-400"
                progressGradientTo="to-orange-400"
            />

            <!-- Ingresos Totales -->
            <x-dashboard-widget 
                title="Ingresos Totales"
                value="{{ $totalRevenue }}"
                valueType="currency"
                icon="fas fa-money-bill-wave"
                trend="Total"
                trendIcon="fas fa-chart-bar"
                trendColor="text-green-300"
                subtitle="Ingresos generados"
                subtitleIcon="fas fa-dollar-sign"
                gradientFrom="from-purple-500"
                gradientTo="to-indigo-600"
                progressWidth="100%"
                progressGradientFrom="from-purple-400"
                progressGradientTo="to-indigo-500"
            />

            <!-- Clientes Morosos -->
            <x-dashboard-widget 
                title="Clientes Morosos"
                value="{{ $defaultersCount }}"
                valueType="number"
                icon="fas fa-exclamation-triangle"
                trend="Atención"
                trendIcon="fas fa-exclamation-circle"
                trendColor="text-red-300"
                subtitle="{{ $totalCustomers > 0 ? round(($defaultersCount / $totalCustomers) * 100, 1) . '% del total' : '0% del total' }}"
                subtitleIcon="fas fa-percentage"
                gradientFrom="from-red-500"
                gradientTo="to-pink-600"
                progressWidth="{{ $totalCustomers > 0 ? ($defaultersCount / $totalCustomers) * 100 : 0 }}%"
                progressGradientFrom="from-red-400"
                progressGradientTo="to-pink-500"
            />
        </div>

        <!-- Filtros Rediseñados y Compactos -->
        <div class="bg-white rounded-xl shadow-md mb-6 overflow-hidden" x-data="filtersPanel()">
            <!-- Header del Panel de Filtros -->
            <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-filter text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Filtros y Búsqueda</h3>
                            <p class="text-xs text-gray-500">Personaliza la vista de tus clientes</p>
                        </div>
                    </div>

                    <!-- Toggle Button -->
                    <button @click="toggleFilters()"
                        class="group flex items-center space-x-2 px-3 py-1.5 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <span class="text-xs font-medium text-gray-700"
                            x-text="filtersOpen ? 'Ocultar Filtros' : 'Mostrar Filtros'"></span>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform duration-200 group-hover:text-gray-700 text-xs"
                            :class="{ 'rotate-180': filtersOpen }"></i>
                    </button>
                </div>

                <!-- Active Filters Indicator -->
                <div x-show="hasActiveFilters" x-transition class="mt-2 flex items-center space-x-2">
                    <span class="text-xs font-medium text-blue-600">Filtros activos:</span>
                    <div class="flex flex-wrap gap-1" id="activeFiltersContainer">
                        <!-- Los filtros activos se mostrarán aquí dinámicamente -->
                    </div>
                </div>
            </div>

            <!-- Panel de Filtros Colapsable -->
            <div x-show="filtersOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="p-4 bg-gray-50 border-t border-gray-100">

                <!-- Sección Unificada de Filtros -->
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Tipo de Cambio -->
                        <div x-data="exchangeRateWidget()">
                            

                            <!-- Input y Botón en línea -->
                            <div class="flex items-center justify-start space-x-3">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-600">1 USD =</span>
                                    <input type="number" x-model="exchangeRate" step="0.01" min="0"
                                        @if(!$permissions['can_edit']) readonly @endif @keyup.enter="updateRate()" @input="syncToModal()"
                                        class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center font-semibold text-gray-900 text-sm"
                                        placeholder="0.00">
                                    <span class="text-sm font-medium text-gray-600">VES</span>
                                </div>

                    @if($permissions['can_edit'])
                                    <button @click="updateRate()" :disabled="updating"
                                        class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Actualizar tipo de cambio">
                                        <i class="fas fa-sync-alt text-sm" :class="{ 'animate-spin': updating }"></i>
                        </button>
                    @endif
                </div>
            </div>

                        <!-- Filtros por Estado -->
                        <div>

                            <!-- Botones de Filtro por Estado -->
                            <div class="flex items-center justify-end space-x-3">
                                <!-- Botón Todos - Azul -->
                                <button type="button" @click="setFilter('all')" title="Todos los clientes"
                                    :class="currentFilter === 'all' ?
                                        'bg-blue-500 border-blue-600 text-white shadow-lg transform scale-105' :
                                        'bg-blue-100 border-blue-300 text-blue-600 hover:bg-blue-200 hover:border-blue-400'"
                                    class="flex items-center justify-center w-12 h-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <i class="fas fa-list text-lg"></i>
                        </button>



                                <!-- Botón Morosos - Rojo -->
                                <button type="button" @click="setFilter('defaulters')" title="Clientes morosos"
                                    :class="currentFilter === 'defaulters' ?
                                        'bg-red-500 border-red-600 text-white shadow-lg transform scale-105' :
                                        'bg-red-100 border-red-300 text-red-600 hover:bg-red-200 hover:border-red-400'"
                                    class="flex items-center justify-center w-12 h-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    <i class="fas fa-exclamation-triangle text-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
    .exchange-filters-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 2rem 2rem 1.5rem 2rem;
        margin-bottom: 2rem;
    }

    .exchange-filters-content {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .exchange-block {
        flex: 1 1 340px;
        min-width: 260px;
        max-width: 420px;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .header-icon {
        width: 50px;
        height: 50px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
    }

    .header-text h4 {
        margin: 0;
        font-weight: 600;
        color: var(--dark-color);
    }

    .header-text p {
        margin: 0 0 0.5rem 0;
        color: #666;
        font-size: 0.95rem;
    }

    .rate-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        flex-wrap: wrap;
    }

    .rate-label {
        font-size: 0.95rem;
        color: #666;
        margin-right: 0.5rem;
    }

    .rate-input {
        border: 2px solid #e9ecef;
        border-radius: var(--border-radius-sm);
        padding: 0.75rem;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        transition: var(--transition);
        width: 120px;
    }

    .rate-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

            .currency-symbol,
            .currency-code {
        font-weight: 600;
        color: var(--dark-color);
    }

    .update-rate-btn {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: var(--border-radius-sm);
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: 0.5rem;
    }

    .update-rate-btn:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }

    .filters-block.redesigned-right {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        flex: 2 1 500px;
        min-width: 260px;
        max-width: 700px;
        padding-left: 2rem;
    }

    .filters-title {
        font-weight: 600;
        color: var(--dark-color);
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .filters-search-row {
        display: flex;
        align-items: center;
        gap: 1.1rem;
        width: 100%;
        justify-content: flex-start;
    }

    .filters-btns {
        display: flex;
        gap: 0.7rem;
        margin-bottom: 0;
        flex-wrap: wrap;
    }

    .redesigned-search-group {
        max-width: 260px;
        min-width: 120px;
        width: 100%;
        margin-left: 0.7rem;
        flex: 0 0 auto;
    }

    .search-container {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    @media (max-width: 991px) {
        .filters-search-row {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }

        .redesigned-search-group {
            margin-left: 0;
            max-width: 100%;
        }
    }

            /* ===== TABLA MODERNA ESTILO ESTÁNDAR ===== */
            .table-container {
                overflow-x: auto;
                border-radius: 16px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                background: white;
            }

            .modern-table {
                width: 100%;
                border-collapse: collapse;
                background: white;
            }

            .modern-table thead {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }

            .modern-table th {
                padding: 1rem;
                text-align: left;
                border: none;
                position: relative;
            }

            .th-content {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                color: white;
                font-weight: 600;
                font-size: 1rem;
            }

            .modern-table td {
                padding: 1.25rem;
                border-bottom: 1px solid #e2e8f0;
                vertical-align: middle;
            }

            .table-row {
                transition: all 0.2s ease;
            }

            .table-row:hover {
                background: #f8fafc;
                transform: scale(1.01);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            }

            /* Número de fila */
            .row-number {
                width: 45px;
                height: 45px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 700;
                font-size: 1rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            }

            /* Información del cliente */
            .customer-info {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .customer-avatar .avatar-circle {
                width: 45px;
                height: 45px;
                background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #64748b;
                font-size: 1.3rem;
                font-weight: bold;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            }

            .customer-details {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .customer-name {
                font-weight: 700;
                color: #1f2937;
                font-size: 1rem;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .customer-email {
                color: #718096;
                font-size: 0.85rem;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .customer-email i {
                color: #64748b;
            }

            /* Información de contacto */
            .contact-info {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: #4a5568;
                font-size: 0.95rem;
                font-weight: 500;
            }

            .contact-info i {
                color: #64748b;
            }

            /* Badge de ID */
            .id-info {
                display: flex;
                align-items: center;
            }

            .id-badge {
                background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
                color: #4a5568;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                border: 1px solid #e2e8f0;
            }

            /* Información de ventas */
            .sales-info {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .sales-amount {
                font-weight: 700;
                color: #1f2937;
                font-size: 0.95rem;
            }

            .sales-count {
                color: #718096;
                font-size: 0.85rem;
                font-weight: 500;
            }

            .no-sales {
                background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
                color: #718096;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                border: 1px solid #e2e8f0;
            }

            /* Información de deuda */
            .debt-info {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .debt-amount {
                font-weight: 700;
                color: #e53e3e;
                font-size: 0.95rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .debt-warning-badge {
                background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
                color: #c53030;
                padding: 0.25rem 0.5rem;
                border-radius: 12px;
                font-size: 0.75rem;
                font-weight: 600;
                border: 1px solid #fbb6ce;
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
            }

            .no-debt-badge {
                background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
                color: #22543d;
                padding: 0.4rem 0.8rem;
                border-radius: 16px;
                font-size: 0.7rem;
                font-weight: 600;
                border: 1px solid #9ae6b4;
                white-space: nowrap;
            }

            .edit-debt-btn {
                background: none;
                border: none;
                color: #667eea;
                cursor: pointer;
                padding: 0.5rem;
                border-radius: 50%;
                transition: all 0.3s ease;
                font-size: 0.9rem;
            }

            .edit-debt-btn:hover {
                background: rgba(102, 126, 234, 0.1);
                color: #5a67d8;
                transform: scale(1.1);
            }

            .edit-debt-btn-small {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                color: white;
                cursor: pointer;
                padding: 0.25rem;
                border-radius: 4px;
                transition: all 0.3s ease;
                font-size: 0.75rem;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .edit-debt-btn-small:hover {
                background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
                transform: scale(1.1);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }

            /* Información de deuda en Bs */
            .debt-bs-info {
                color: #4a5568;
                font-size: 0.95rem;
                font-weight: 500;
            }

            .bs-debt {
                color: #4a5568;
                font-weight: 600;
            }

            /* Estado */
            .status-info {
                display: flex;
                align-items: center;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                border: 1px solid;
            }

            .status-active {
                background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
                color: #22543d;
                border-color: #9ae6b4;
            }

            .status-inactive {
                background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
                color: #718096;
                border-color: #e2e8f0;
            }

            /* Botones de acción */
            .action-buttons {
                display: flex;
                gap: 0.75rem;
                justify-content: center;
            }

            .btn-action {
                width: 40px;
                height: 40px;
                border: none;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 1rem;
                text-decoration: none;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            }

            .btn-view {
                background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
                color: white;
            }

            .btn-edit {
                background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
                color: white;
            }

            .btn-delete {
                background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
                color: white;
            }

            .btn-sale {
                background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%);
                color: white;
            }

            .btn-action:hover {
                transform: scale(1.15);
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            }

            .btn-view:hover {
                background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            }

            .btn-edit:hover {
                background: linear-gradient(135deg, #dd6b20 0%, #c05621 100%);
            }

            .btn-delete:hover {
                background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            }

            .btn-sale:hover {
                background: linear-gradient(135deg, #805ad5 0%, #6b46c1 100%);
            }

            /* Responsive para tabla */
            @media (max-width: 1024px) {

                .modern-table th,
                .modern-table td {
                    padding: 0.75rem;
                }

                .th-content {
                    font-size: 0.9rem;
                    gap: 0.5rem;
                }

                .row-number {
                    width: 35px;
                    height: 35px;
                    font-size: 0.9rem;
                }

                .customer-avatar .avatar-circle {
                    width: 35px;
                    height: 35px;
                    font-size: 1.1rem;
                }

                .btn-action {
                    width: 35px;
                    height: 35px;
                    font-size: 0.9rem;
                }
            }

            @media (max-width: 768px) {
                .table-container {
                    border-radius: 12px;
                }

                .modern-table th,
                .modern-table td {
                    padding: 0.5rem;
                }

                .th-content {
                    font-size: 0.8rem;
                    gap: 0.4rem;
                }

                .row-number {
                    width: 30px;
                    height: 30px;
                    font-size: 0.8rem;
                }

                .customer-avatar .avatar-circle {
                    width: 30px;
                    height: 30px;
                    font-size: 1rem;
                }

                .btn-action {
                    width: 30px;
                    height: 30px;
                    font-size: 0.8rem;
                }

                .action-buttons {
                    gap: 0.5rem;
                }
            }
    </style>

        <!-- Tabla de Clientes Rediseñada con Tailwind y Alpine.js -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden" x-data="dataTable()">

            <!-- Header de la Tabla con Toggle de Vista -->
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Lista de Clientes</h3>
                        </div>
                    </div>

                    <!-- Barra de Búsqueda -->
                    <div class="flex-1 max-w-sm mx-auto lg:mx-0 lg:ml-8">
                                                    <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                </div>
                                <input type="text" x-model="searchTerm" @keydown="handleKeydown($event)"
                                    class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-500 text-sm"
                                    placeholder="Buscar por nombre, email o teléfono...">
                                <button x-show="searchTerm.length > 0" @click="clearSearch()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                        </div>
                        
                    </div>

                    <!-- Toggle Vista - Solo visible en desktop/tablet -->
                    <div class="hidden md:flex items-center space-x-3">
                        <span class="text-sm font-medium text-gray-700">Vista:</span>
                        <div class="flex items-center bg-gray-100 rounded-lg p-1">
                            <button @click="viewMode = 'table'"
                                :class="viewMode === 'table' ? 'bg-white text-gray-900 shadow-sm' :
                                    'text-gray-600 hover:text-gray-900'"
                                class="flex items-center space-x-2 px-3 py-2 rounded-md transition-all duration-200 focus:outline-none">
                                <i class="fas fa-table text-sm"></i>
                                <span class="text-sm font-medium">Tabla</span>
                            </button>
                            <button @click="viewMode = 'cards'"
                                :class="viewMode === 'cards' ? 'bg-white text-gray-900 shadow-sm' :
                                    'text-gray-600 hover:text-gray-900'"
                                class="flex items-center space-x-2 px-3 py-2 rounded-md transition-all duration-200 focus:outline-none">
                                <i class="fas fa-th-large text-sm"></i>
                                <span class="text-sm font-medium">Tarjetas</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista de Tabla - Desktop/Tablet -->
            <div x-show="viewMode === 'table'" class="hidden md:block">
            <div class="table-container">
                    <table id="customersTable" class="modern-table">
                    <thead>
                        <tr>
                                <th>
                                    <div class="th-content">
                                        <span>#</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-user"></i>
                                        <span>Cliente</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-phone"></i>
                                        <span>Contacto</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-id-card"></i>
                                        <span>C.I</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-shopping-cart"></i>
                                        <span>Total Compras</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>Deuda Total</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-coins"></i>
                                        <span>Deuda Bs</span>
                                    </div>
                                </th>

                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-cogs"></i>
                                        <span>Acciones</span>
                                    </div>
                                </th>
                        </tr>
                    </thead>
                        <tbody id="customersTableBody">
                        @foreach ($customers as $customer)
                                @php
                                    $customerSales = $customersData[$customer->id] ?? [];
                                    $hasSales = isset($customerSales['hasOldSales']) || $customerSales['currentDebt'] > 0;
                                @endphp
                                <tr class="table-row" data-customer-id="{{ $customer->id }}"
                                    data-defaulter="{{ $customersData[$customer->id]['isDefaulter'] ? 'true' : 'false' }}"
                                    data-customer-name="{{ htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') }}">
                                    <td>
                                        <div class="row-number">
                                            {{ $loop->iteration }}
                                        </div>
                                    </td>
                                    <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">
                                            <div class="avatar-circle">
                                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="customer-details">
                                                <span class="customer-name truncate">{{ htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') }}</span>
                                            <div class="customer-email">
                                                <i class="fas fa-envelope"></i>
                                                <span class="truncate">{{ htmlspecialchars($customer->email, ENT_QUOTES, 'UTF-8') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                    <td>
                                    <div class="contact-info">
                                        <i class="fas fa-phone"></i>
                                        <span>{{ $customer->phone }}</span>
                                    </div>
                                </td>
                                    <td>
                                        <div class="id-info">
                                    <span class="id-badge">{{ $customer->nit_number }}</span>
                                        </div>
                                </td>
                                                                        <td>
                                        <div class="sales-info">
                                            @php
                                                $customerSales = $customersData[$customer->id] ?? [];
                                                $hasSales = isset($customerSales['hasOldSales']) || $customerSales['currentDebt'] > 0;
                                            @endphp
                                            @if ($hasSales)
                                                <div class="sales-amount">{{ $currency->symbol }}
                                                    {{ number_format(($customerSales['previousDebt'] ?? 0) + ($customerSales['currentDebt'] ?? 0), 2) }}</div>
                                                <div class="sales-count">Con ventas</div>
                                            @else
                                                <span class="no-sales">Sin ventas</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="debt-info">
                                            @if ($customer->total_debt > 0)
                                                <div class="debt-amount debt-value flex items-center gap-2"
                                                 data-customer-id="{{ $customer->id }}" 
                                                 data-original-value="{{ $customer->total_debt }}">
                                                    <span>{{ $currency->symbol }} <span
                                                            class="debt-amount-value">{{ number_format($customer->formatted_total_debt, 2) }}</span></span>
                                                @if ($customersData[$customer->id]['isDefaulter'])
                                                        <span class="debt-warning-badge"
                                                            title="Cliente con deudas de arqueos anteriores">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </span>
                                                @endif
                                            @if ($customer->total_debt > 0)
                                                <button class="edit-debt-btn-small" onclick="spaPaymentHandler.openPaymentModal({{ $customer->id }})">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </button>

                                            @else
                                                                                            @if($permissions['can_edit'])
                                                    <button class="edit-debt-btn-small">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                            @endif
                                            @endif
                                        </div>
                                    @else
                                                <div class="debt-amount flex items-center gap-2">
                                            <span class="no-debt-badge">Sin deuda</span>
                                        </div>
                                    @endif
                                        </div>
                                </td>
                                    <td>
                                        <div class="debt-bs-info">
                                    @if ($customer->total_debt > 0)
                                        <span class="bs-debt" data-debt="{{ $customer->total_debt }}">
                                            Bs. {{ number_format($customer->total_debt * ($exchangeRate ?? 134), 2) }}
                                        </span>
                                    @else
                                        <span class="no-debt-badge">Sin deuda</span>
                                    @endif
                                        </div>
                                </td>
                                    
                                    <td>
                                    <div class="action-buttons">
                                        @if($permissions['can_show'])
                                                <button type="button" class="btn-action btn-view"
                                                    @click="openModal('showCustomerModal'); loadCustomerDetails({{ $customer->id }})" 
                                                    data-toggle="tooltip" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                        @if($permissions['can_edit'])
                                            <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                                    class="btn-action btn-edit" data-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if($permissions['can_destroy'])
                                                <button type="button" class="btn-action btn-delete"
                                                @click="deleteCustomer({{ $customer->id }})" data-toggle="tooltip" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                        @if($permissions['can_create_sales'])
                                            <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                                    class="btn-action btn-sale" data-toggle="tooltip" title="Nueva venta">
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

            <!-- Vista de Tarjetas - Móvil y Desktop (cuando se selecciona) -->
            <div x-show="viewMode === 'cards'" class="md:block" :class="{ 'block': true, 'hidden': false }">
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="mobileCustomersContainer">
                @foreach ($customers as $customer)
                        @php
                            $customerSales = $customersData[$customer->id] ?? [];
                            $hasSales = isset($customerSales['hasOldSales']) || $customerSales['currentDebt'] > 0;
                        @endphp
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-l-4 border-blue-500"
                            data-defaulter="{{ $customersData[$customer->id]['isDefaulter'] ? 'true' : 'false' }}"
                            data-customer-name="{{ htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') }}">

                            <!-- Header de la Tarjeta -->
                            <div class="p-6 pb-4">
                                <div class="card-header-content">
                                    <div class="card-header-info">
                                        <div class="card-header-avatar">
                                            <div
                                                class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                        </div>
                                        <div class="card-header-details">
                                            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') }}
                                            </h3>
                                            <div class="flex items-center space-x-1 text-sm text-gray-500 mt-1">
                                                <i class="fas fa-envelope text-xs"></i>
                                                <span class="truncate">{{ htmlspecialchars($customer->email, ENT_QUOTES, 'UTF-8') }}</span>
                            </div>
                                        </div>
                                    </div>

                                    <!-- Estado -->
                                    <div class="card-header-status">
                                @if ($hasSales)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Activo
                                    </span>
                                @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                Inactivo
                                    </span>
                                @endif
                                    </div>
                            </div>
                        </div>
                        
                            <!-- Información Principal -->
                            <div class="px-6 pb-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Teléfono -->
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-phone"></i>
                                            <span>Teléfono</span>
                                    </div>
                                        <p class="text-sm font-medium text-gray-900">{{ $customer->phone }}</p>
                                </div>

                                    <!-- C.I -->
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-id-card"></i>
                                            <span>C.I</span>
                                    </div>
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $customer->nit_number }}
                                        </span>
                                    </div>

                                    <!-- Total Compras -->
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-shopping-cart"></i>
                                            <span>Total Compras</span>
                                    </div>
                                        @if ($hasSales)
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $currency->symbol }}
                                                    {{ number_format(($customerSales['previousDebt'] ?? 0) + ($customerSales['currentDebt'] ?? 0), 2) }}</p>
                                                <p class="text-xs text-gray-500">Con ventas</p>
                                            </div>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                                Sin ventas
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Deuda -->
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-money-bill-wave"></i>
                                            <span>Deuda</span>
                                    </div>
                                        @if ($customer->total_debt > 0)
                                            <div class="space-y-1">
                                                <div class="debt-value flex items-center gap-2" data-customer-id="{{ $customer->id }}"
                                                     data-original-value="{{ $customer->total_debt }}">
                                                    <p class="text-sm font-semibold text-red-600">
                                                        {{ $currency->symbol }} <span
                                                            class="debt-amount-value">{{ number_format($customer->formatted_total_debt, 2) }}</span>
                                                    </p>
                                                    @if ($customersData[$customer->id]['isDefaulter'])
                                                    <span class="debt-warning-badge"
                                                        title="Cliente con deudas de arqueos anteriores">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="no-debt-badge">Sin deuda</span>
                                        @endif
                                </div>
                            </div>
                        </div>
                        
                            <!-- Acciones -->
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                                <div class="flex justify-center gap-3">
                                @if($permissions['can_show'])
                                        <button type="button"
                                            class="w-10 h-10 flex items-center justify-center rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            @click="openModal('showCustomerModal'); loadCustomerDetails({{ $customer->id }})" 
                                            title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                                @if($permissions['can_edit'])
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                            class="w-10 h-10 flex items-center justify-center rounded-lg bg-green-500 hover:bg-green-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                                                    @if ($customer->total_debt > 0)
                                        <button
                                            class="w-10 h-10 flex items-center justify-center rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            onclick="spaPaymentHandler.openPaymentModal({{ $customer->id }})" 
                                            title="Pagar deuda">
                                        <i class="fas fa-dollar-sign"></i>
                                    </button>
                                    @endif
                                @if($permissions['can_create_sales'])
                                    <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                            class="w-10 h-10 flex items-center justify-center rounded-lg bg-purple-500 hover:bg-purple-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            title="Nueva venta">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                @endif
                                @if($permissions['can_destroy'])
                                        <button type="button"
                                            class="w-10 h-10 flex items-center justify-center rounded-lg bg-red-500 hover:bg-red-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            @click="deleteCustomer({{ $customer->id }})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Vista Solo Móvil (automática) -->
            <div class="block md:hidden">
                <div class="p-4 space-y-4" id="mobileOnlyContainer">
                    @foreach ($customers as $customer)
                        @php
                            $customerSales = $customersData[$customer->id] ?? [];
                            $hasSales = isset($customerSales['hasOldSales']) || $customerSales['currentDebt'] > 0;
                        @endphp
                        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 {{ $hasSales ? 'border-green-500' : 'border-gray-400' }}"
                            data-status="{{ $hasSales ? 'active' : 'inactive' }}"
                            data-defaulter="{{ $customersData[$customer->id]['isDefaulter'] ? 'true' : 'false' }}"
                            data-customer-name="{{ htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') }}">

                            <!-- Header Compacto -->
                            <div class="p-4">
                                <div class="card-header-content">
                                    <div class="card-header-info">
                                        <div class="card-header-avatar">
                                            <div
                                                class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        </div>
                                        </div>
                                        <div class="card-header-details">
                                            <h3 class="text-sm font-semibold text-gray-900 truncate">{{ htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') }}
                                            </h3>
                                            <p class="text-xs text-gray-500 truncate">{{ htmlspecialchars($customer->email, ENT_QUOTES, 'UTF-8') }}</p>
                                        </div>
                                    </div>
                                    <div class="card-header-status">
                                        @if ($hasSales)
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-times-circle mr-1"></i>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Info Compacta -->
                                <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <span class="text-gray-500">📞</span>
                                        <span class="ml-1 text-gray-900">{{ $customer->phone }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">🆔</span>
                                        <span class="ml-1 text-gray-900">{{ $customer->nit_number }}</span>
                                    </div>
                                    @if ($customer->total_debt > 0)
                                        <div class="col-span-2">
                                            <span class="text-red-600 font-medium debt-value"
                                                data-customer-id="{{ $customer->id }}"
                                                data-original-value="{{ $customer->total_debt }}">
                                                💰 {{ $currency->symbol }} <span
                                                    class="debt-amount-value">{{ number_format($customer->formatted_total_debt, 2) }}</span>
                                            </span>
                                            @if ($customersData[$customer->id]['isDefaulter'])
                                                <span
                                                    class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    ⚠️ Moroso
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Acciones Compactas -->
                                <div class="mt-3 flex justify-center gap-2">
                                    @if($permissions['can_show'])
                                        <button type="button"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            @click="openModal('showCustomerModal'); loadCustomerDetails({{ $customer->id }})" 
                                            title="Ver detalles">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>
                                    @endif
                                    @if($permissions['can_edit'])
                                        <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-green-500 hover:bg-green-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            title="Editar">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                    @endif
                                                                        @if ($customer->total_debt > 0)
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            onclick="spaPaymentHandler.openPaymentModal({{ $customer->id }})" 
                                            title="Pagar deuda">
                                        <i class="fas fa-dollar-sign text-xs"></i>
                                    </button>
                                    @endif
                                    @if($permissions['can_create_sales'])
                                        <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-purple-500 hover:bg-purple-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            title="Nueva venta">
                                            <i class="fas fa-cart-plus text-xs"></i>
                                        </a>
                                    @endif
                                    @if($permissions['can_destroy'])
                                        <button type="button"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-500 hover:bg-red-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            @click="deleteCustomer({{ $customer->id }})" title="Eliminar">
                                            <i class="fas fa-trash text-xs"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>



    {{-- Modal de Detalles del Cliente Rediseñado con Alpine.js --}}
    <div x-show="showCustomerModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal('showCustomerModal')"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                
                <!-- Header del Modal -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-purple-600 rounded-t-2xl">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-user-tie text-white text-lg"></i>
                        </div>
                        <div>
                            <h5 class="text-xl font-bold text-white">Detalles del Cliente</h5>
                            <p class="text-sm text-blue-100">Información completa y historial de ventas</p>
                        </div>
                    </div>
                    <button type="button" @click="closeModal('showCustomerModal')" class="w-10 h-10 bg-white/20 hover:bg-white/30 text-white hover:text-white rounded-lg flex items-center justify-center transition-all duration-200 backdrop-blur-sm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Body del Modal -->
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <!-- Información del Cliente -->
                    <div class="bg-gradient-to-br from-blue-50/90 via-indigo-50/75 to-purple-50/90 rounded-xl shadow-sm border border-blue-200/60 p-6 mb-6 backdrop-blur-sm">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <h6 class="text-lg font-semibold text-gray-900">Información del Cliente</h6>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-700">Cliente</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    </div>
                                    <input type="text" id="customer_name_details" readonly
                                        class="w-full pl-10 pr-3 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 text-sm">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-700">Teléfono</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    </div>
                                    <input type="text" id="customer_phone_details" readonly
                                        class="w-full pl-10 pr-3 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 text-sm">
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-semibold text-gray-700">Estado:</span>
                                <span id="customer_status_details" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <!-- Header de la Sección -->
                        <div class="flex items-center space-x-4 p-6 bg-gradient-to-r from-blue-500 to-purple-600 border-b border-gray-200">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-shopping-cart text-white"></i>
                            </div>
                            <div>
                                <h6 class="text-lg font-semibold text-white">Historial de Ventas</h6>
                                <p class="text-sm text-blue-100">Cliente: <span id="customerName" class="font-semibold text-white"></span></p>
                            </div>
                        </div>
                        
                        <!-- Filtros -->
                        <div class="p-6 border-b border-gray-100 bg-gradient-to-br from-purple-50/90 via-pink-50/75 to-rose-50/90 backdrop-blur-sm">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Rango de Fechas -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Rango de Fechas</label>
                                    <div class="flex items-center space-x-3">
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            </div>
                                            <input type="date" id="dateFrom" placeholder="Desde" 
                                                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                                        </div>
                                        <span class="text-sm text-gray-500 font-medium">hasta</span>
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            </div>
                                            <input type="date" id="dateTo" placeholder="Hasta" 
                                                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                                        </div>
                                    </div>
                                </div>

                                <!-- Rango de Monto -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Rango de Monto</label>
                                    <div class="flex items-center space-x-3">
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-medium">{{ $currency->symbol }}</span>
                                            </div>
                                            <input type="number" id="amountFrom" placeholder="Mínimo" step="0.01" min="0"
                                                class="w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                                        </div>
                                        <span class="text-sm text-gray-500 font-medium">-</span>
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-medium">{{ $currency->symbol }}</span>
                                            </div>
                                            <input type="number" id="amountTo" placeholder="Máximo" step="0.01" min="0"
                                                class="w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón Limpiar Filtros -->
                            <div class="flex justify-end mt-4">
                                <button type="button" id="clearFilters" 
                                    class="flex items-center space-x-2 px-4 py-2.5 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    <i class="fas fa-times text-sm"></i>
                                    <span class="text-sm font-medium">Limpiar Filtros</span>
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de Ventas -->
                        <div class="p-6">
                            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                                <table class="w-full sales-history-table">
                                    <thead class="bg-gradient-to-r from-blue-500 to-purple-600 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-400">Fecha</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-400">Productos</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-400">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="salesHistoryTable">
                                        <tr>
                                            <td colspan="3" class="px-4 py-12 text-center">
                                                <div class="flex flex-col items-center space-y-3">
                                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                                        <i class="fas fa-info-circle text-2xl text-gray-400"></i>
                                                    </div>
                                                    <p class="text-gray-500">No hay ventas registradas</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Footer de la Tabla -->
                            <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                                <div class="text-sm text-gray-600">
                                    <span id="salesCount" class="font-semibold">0</span> ventas mostradas
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para el reporte de deudas rediseñado con Alpine.js --}}
    <div id="debtReportModal" x-show="debtReportModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal('debtReportModal')"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-content relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                
                <!-- Header del Modal -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-red-50 to-pink-50 rounded-t-2xl">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-invoice-dollar text-white text-lg"></i>
                        </div>
                        <div>
                            <h5 class="text-xl font-bold text-gray-900">Reporte de Deudas</h5>
                            <p class="text-sm text-gray-600">Análisis detallado de deudas por cliente</p>
                        </div>
                    </div>
                    <button type="button" @click="closeModal('debtReportModal')" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 rounded-lg flex items-center justify-center transition-all duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Body del Modal -->
                <div class="modal-body p-8">
                    <div class="flex flex-col items-center justify-center py-12">
                        <!-- Spinner de Carga -->
                        <div class="w-16 h-16 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin mb-6"></div>
                        
                        <!-- Texto de Carga -->
                        <div class="text-center">
                            <h5 class="text-xl font-semibold text-gray-900 mb-2">Cargando reporte de deudas</h5>
                            <p class="text-gray-600">Preparando información detallada...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para registrar pagos de deuda --}}
    <div id="debtPaymentModal" 
         class="fixed inset-0 z-50 overflow-y-auto hidden"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="spaPaymentHandler.closePaymentModal()"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
                
                <!-- Header del Modal -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50 rounded-t-2xl">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-white text-lg"></i>
                        </div>
                        <div>
                            <h5 class="text-xl font-bold text-gray-900">Registrar Pago de Deuda</h5>
                            <p class="text-sm text-gray-600">Gestiona los pagos de tus clientes de forma eficiente</p>
                        </div>
                    </div>
                    <button type="button" onclick="spaPaymentHandler.closePaymentModal()" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 rounded-lg flex items-center justify-center transition-all duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="debtPaymentForm" method="POST">
                    <div class="p-6 max-h-[70vh] overflow-y-auto">
                        @csrf
                        <input type="hidden" id="payment_customer_id" name="customer_id">
                        
                        <div class="space-y-6">
                            <!-- Información del Cliente -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                    <h6 class="text-lg font-semibold text-gray-900">Información del Cliente</h6>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                    <label for="customer_name" class="text-sm font-semibold text-gray-700">Cliente</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        </div>
                                        <input type="text" id="customer_name" readonly
                                            class="w-full pl-10 pr-3 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 text-sm">
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="customer_phone" class="text-sm font-semibold text-gray-700">Teléfono</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            </div>
                                            <input type="text" id="customer_phone" readonly
                                                class="w-full pl-10 pr-3 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 text-sm">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-semibold text-gray-700">Estado:</span>
                                        <span id="customer_status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Estado de Deuda -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-chart-line text-white text-sm"></i>
                                    </div>
                                    <h6 class="text-lg font-semibold text-gray-900">Estado de Deuda</h6>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label for="current_debt" class="text-sm font-semibold text-gray-700">Deuda Actual</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                                                <i class="fas fa-dollar-sign text-gray-400 text-sm"></i>
                                            </div>
                                            <div id="current_debt" class="w-full pl-12 pr-12 py-2.5 bg-red-50 border border-red-200 rounded-lg text-red-700 font-semibold text-sm flex items-center">
                                                <span class="text-red-700 font-semibold">$0.00</span>
                                            </div>
                                            <button type="button" id="current_debt_btn" class="absolute inset-y-0 right-0 px-3 bg-red-500 hover:bg-red-600 text-white rounded-r-lg transition-colors duration-200" title="Deuda actual">
                                                <i class="fas fa-info text-sm"></i>
                                            </button>
                                        </div>
                                        <small class="text-xs text-gray-500">Deuda total del cliente</small>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="remaining_debt" class="text-sm font-semibold text-gray-700">Deuda Restante</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                                                <i class="fas fa-dollar-sign text-gray-400 text-sm"></i>
                                            </div>
                                            <div id="remaining_debt" class="w-full pl-12 pr-12 py-2.5 bg-orange-50 border border-orange-200 rounded-lg text-orange-700 font-semibold text-sm flex items-center">
                                                <span class="text-orange-700 font-semibold">$0.00</span>
                                            </div>
                                            <button type="button" id="remaining_debt_btn" class="absolute inset-y-0 right-0 px-3 bg-orange-500 hover:bg-orange-600 text-white rounded-r-lg transition-colors duration-200" title="Deuda restante">
                                                <i class="fas fa-calculator text-sm"></i>
                                            </button>
                                        </div>
                                        <small class="text-xs text-gray-500">Deuda después del pago</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Detalles del Pago -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-credit-card text-white text-sm"></i>
                                    </div>
                                    <h6 class="text-lg font-semibold text-gray-900">Detalles del Pago</h6>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="space-y-2">
                                        <label for="payment_amount" class="text-sm font-semibold text-gray-700">Monto del Pago</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                                                <i class="fas fa-dollar-sign text-gray-400 text-sm"></i>
                                            </div>
                                            <input type="number" id="payment_amount" name="payment_amount" step="0.01" min="0.01" required
                                                class="w-full pl-12 pr-12 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                                            <button type="button" id="max_payment_btn" class="absolute inset-y-0 right-0 px-3 bg-green-500 hover:bg-green-600 text-white rounded-r-lg transition-colors duration-200" title="Pagar deuda completa">
                                                <i class="fas fa-plus text-sm"></i>
                                            </button>
                                        </div>
                                        <small class="text-xs text-gray-500">El monto no puede ser mayor que la deuda actual</small>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <label for="payment_date" class="text-sm font-semibold text-gray-700">Fecha del Pago</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                                            </div>
                                            <input type="date" id="payment_date" name="payment_date" required
                                                class="w-full pl-12 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                                        </div>
                                        <small class="text-xs text-gray-500">La fecha no puede ser mayor a hoy</small>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <label for="payment_time" class="text-sm font-semibold text-gray-700">Hora del Pago</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                                            </div>
                                            <input type="time" id="payment_time" name="payment_time" required
                                                class="w-full pl-12 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                                        </div>
                                        <small class="text-xs text-gray-500">Hora en que se realizó el pago</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Notas Adicionales -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-sticky-note text-white text-sm"></i>
                                    </div>
                                    <h6 class="text-lg font-semibold text-gray-900">Notas Adicionales</h6>
                                </div>
                                <div class="space-y-2">
                                    <label for="payment_notes" class="text-sm font-semibold text-gray-700">Notas</label>
                                    <textarea id="payment_notes" name="notes" rows="3" placeholder="Detalles adicionales sobre este pago..."
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm resize-vertical"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer del Modal -->
                    <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                        <button type="submit" class="flex items-center space-x-2 px-6 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-md hover:shadow-lg">
                            <i class="fas fa-save text-sm"></i>
                            <span class="text-sm font-medium">Registrar Pago</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Paginación personalizada -->
    @if($customers->hasPages())
        <div class="mt-8 px-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="custom-pagination">
                    <div class="pagination-info">
                        <span id="paginationInfo">Mostrando {{ $customers->firstItem() ?? 0 }}-{{ $customers->lastItem() ?? 0 }} de {{ $customers->total() }} clientes</span>
                    </div>
                    <div class="pagination-controls">
                        @if($customers->onFirstPage())
                            <button class="pagination-btn" disabled>
                                <i class="fas fa-chevron-left"></i>
                                Anterior
                            </button>
                        @else
                            <a href="{{ $customers->previousPageUrl() }}" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i>
                                Anterior
                            </a>
                        @endif
                        
                        <div class="page-numbers">
                            @foreach($customers->getUrlRange(1, $customers->lastPage()) as $page => $url)
                                @if($page == $customers->currentPage())
                                    <span class="page-number active">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="page-number">{{ $page }}</a>
                                @endif
                            @endforeach
                        </div>
                        
                        @if($customers->hasMorePages())
                            <a href="{{ $customers->nextPageUrl() }}" class="pagination-btn">
                                Siguiente
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <button class="pagination-btn" disabled>
                                Siguiente
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop


