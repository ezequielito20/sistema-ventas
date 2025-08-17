@extends('layouts.app')

@section('title', 'Historial de Pagos')

@section('content')
<div x-data="paymentHistory()" class="space-y-6">
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative px-6 py-8 sm:px-8 sm:py-12">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-history text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                                Historial de Pagos
                            </h1>
                            <p class="text-blue-100 text-lg">
                                Registro histórico de todos los pagos de deudas realizados por los clientes
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('admin.customers.index') }}" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span class="hidden sm:inline">Volver a Clientes</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-6 border-b border-gray-200">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-filter text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Filtros</h2>
                    <p class="text-gray-600">Filtre los pagos por cliente y fechas</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Cliente -->
                <div class="space-y-2">
                    <label for="customer_search" class="flex items-center space-x-2 text-sm font-semibold text-gray-700">
                        <i class="fas fa-search text-blue-500"></i>
                        <span>Buscar Cliente</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="customer_search"
                               x-model="filters.customer_search"
                               placeholder="Buscar por nombre del cliente..."
                               class="w-full px-4 py-3 pr-10 border-2 border-gray-200 rounded-xl text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Fecha Desde -->
                <div class="space-y-2">
                    <label for="date_from" class="flex items-center space-x-2 text-sm font-semibold text-gray-700">
                        <i class="fas fa-calendar-alt text-blue-500"></i>
                        <span>Fecha desde</span>
                    </label>
                    <input type="date" 
                           id="date_from"
                           x-model="filters.date_from"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Fecha Hasta -->
                <div class="space-y-2">
                    <label for="date_to" class="flex items-center space-x-2 text-sm font-semibold text-gray-700">
                        <i class="fas fa-calendar-check text-blue-500"></i>
                        <span>Fecha hasta</span>
                    </label>
                    <input type="date" 
                           id="date_to"
                           x-model="filters.date_to"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Botón Reiniciar -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 opacity-0">Acción</label>
                    <button @click="resetFilters()" 
                            class="w-full px-4 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-xl transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-undo"></i>
                        <span>Reiniciar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Pagos Recibidos -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-white text-xl"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-2xl font-bold text-white">{{ $currency->symbol }} <span x-text="filteredPayments.reduce((sum, payment) => sum + parseFloat(payment.payment_amount), 0).toFixed(2)"></span></h3>
                        <p class="text-blue-100 text-sm">Total Pagos</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Número de Pagos -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-receipt text-white text-xl"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-2xl font-bold text-white" x-text="filteredPayments.length"></h3>
                        <p class="text-green-100 text-sm">Número de Pagos</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pago Promedio -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-600 to-orange-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calculator text-white text-xl"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-2xl font-bold text-white">{{ $currency->symbol }} <span x-text="filteredPayments.length > 0 ? (filteredPayments.reduce((sum, payment) => sum + parseFloat(payment.payment_amount), 0) / filteredPayments.length).toFixed(2) : '0.00'"></span></h3>
                        <p class="text-yellow-100 text-sm">Pago Promedio</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deuda Total Restante -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-file-invoice-dollar text-white text-xl"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-2xl font-bold text-white">{{ $currency->symbol }} {{ number_format($totalRemainingDebt, 2) }}</h3>
                        <p class="text-purple-100 text-sm">Deuda Restante</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla y Tarjetas -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-list text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Historial de Pagos</h2>
                        <p class="text-gray-600">Registro detallado de todos los pagos realizados</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Vista:</span>
                    <button @click="viewMode = 'table'" 
                            :class="viewMode === 'table' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'"
                            class="px-3 py-2 rounded-lg transition-all duration-200">
                        <i class="fas fa-table"></i>
                    </button>
                    <button @click="viewMode = 'cards'" 
                            :class="viewMode === 'cards' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'"
                            class="px-3 py-2 rounded-lg transition-all duration-200">
                        <i class="fas fa-th-large"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Vista de Tabla (Desktop/Tablet) -->
        <div x-show="viewMode === 'table'" x-cloak class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Fecha</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Cliente</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Deuda Anterior</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Monto Pagado</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Deuda Restante</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Registrado por</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Notas</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="payment in paginatedPayments" :key="payment.id">
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-900" x-text="new Date(payment.created_at).toLocaleDateString('es-ES')"></span>
                                <span class="text-sm text-gray-500" x-text="new Date(payment.created_at).toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'})"></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-white text-xs"></i>
                                </div>
                                <span class="font-medium text-gray-900" x-text="payment.customer.name"></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                {{ $currency->symbol }} <span x-text="parseFloat(payment.previous_debt).toFixed(2)"></span>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                {{ $currency->symbol }} <span x-text="parseFloat(payment.payment_amount).toFixed(2)"></span>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                {{ $currency->symbol }} <span x-text="parseFloat(payment.remaining_debt).toFixed(2)"></span>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-user-cog text-gray-400"></i>
                                <span class="text-gray-900" x-text="payment.user.name"></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600 max-w-xs truncate block" x-text="payment.notes || 'Sin notas'"></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button @click="deletePayment(payment.id, payment.customer.name, payment.payment_amount)"
                                    class="w-8 h-8 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg flex items-center justify-center transition-all duration-200"
                                    title="Eliminar Pago">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </td>
                    </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Vista de Tarjetas (Móvil) -->
        <div x-show="viewMode === 'cards'" x-cloak class="p-6">
            <div class="grid grid-cols-1 gap-6">
                <template x-for="payment in paginatedPayments" :key="payment.id">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900" x-text="new Date(payment.created_at).toLocaleDateString('es-ES')"></h3>
                                    <p class="text-sm text-gray-500" x-text="new Date(payment.created_at).toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'})"></p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                {{ $currency->symbol }} <span x-text="parseFloat(payment.payment_amount).toFixed(2)"></span>
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cliente</label>
                                <p class="text-sm font-medium text-gray-900" x-text="payment.customer.name"></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Deuda Anterior</label>
                                <p class="text-sm font-medium text-red-600">{{ $currency->symbol }} <span x-text="parseFloat(payment.previous_debt).toFixed(2)"></span></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Deuda Restante</label>
                                <p class="text-sm font-medium text-yellow-600">{{ $currency->symbol }} <span x-text="parseFloat(payment.remaining_debt).toFixed(2)"></span></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Registrado por</label>
                                <p class="text-sm font-medium text-gray-900" x-text="payment.user.name"></p>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Notas</label>
                            <p class="text-sm text-gray-600" x-text="payment.notes || 'Sin notas'"></p>
                        </div>
                        
                        <div class="flex justify-end">
                            <button @click="deletePayment(payment.id, payment.customer.name, payment.payment_amount)"
                                    class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg flex items-center space-x-2 transition-all duration-200">
                                <i class="fas fa-trash text-sm"></i>
                                <span class="text-sm font-medium">Eliminar</span>
                            </button>
                        </div>
                    </div>
                </div>
                </template>
            </div>
        </div>

        <!-- Paginación -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                    Mostrando <span x-text="((currentPage - 1) * itemsPerPage) + 1"></span> a <span x-text="Math.min(currentPage * itemsPerPage, filteredPayments.length)"></span> de <span x-text="filteredPayments.length"></span> registros
                </div>
                <div class="flex justify-center space-x-2">
                    <!-- Botón Anterior -->
                    <button @click="prevPage()" 
                            :disabled="!hasPrevPage"
                            class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        <i class="fas fa-chevron-left mr-1"></i>
                        Anterior
                    </button>
                    
                    <!-- Números de página -->
                    <template x-for="page in Math.min(5, totalPages)" :key="page">
                        <button @click="goToPage(page)" 
                                :class="page === currentPage ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium transition-all duration-200"
                                x-text="page">
                        </button>
                    </template>
                    
                    <!-- Botón Siguiente -->
                    <button @click="nextPage()" 
                            :disabled="!hasNextPage"
                            class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        Siguiente
                        <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gráfico por Día de la Semana -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-6 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-bar text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Pagos por Día de la Semana</h2>
                        <p class="text-gray-600">Distribución de pagos durante la semana</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="weekdayChart" height="250"></canvas>
            </div>
        </div>

        <!-- Gráfico por Mes -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-6 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Pagos por Mes</h2>
                        <p class="text-gray-600">Tendencia de pagos a lo largo del tiempo</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="monthlyChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    /* Estilos para la paginación de Laravel */
    .pagination {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
        justify-content: center;
    }

    .page-item {
        list-style: none;
        margin: 0;
    }

    .page-link {
        background: white;
        border: 2px solid #e9ecef;
        color: #374151;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        text-decoration: none;
        transition: all 0.2s;
        font-weight: 500;
        min-width: 44px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-link:hover {
        background: #667eea;
        border-color: #667eea;
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: transparent;
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .page-item.disabled .page-link {
        background: #f8f9fa;
        border-color: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .page-item.disabled .page-link:hover {
        background: #f8f9fa;
        border-color: #e9ecef;
        color: #6c757d;
        transform: none;
        box-shadow: none;
    }

    /* Responsive para paginación */
    @media (max-width: 640px) {
        .pagination {
            gap: 0.3rem;
        }

        .page-link {
            padding: 0.6rem 0.8rem;
            font-size: 0.9rem;
            min-width: 40px;
        }
    }
</style>
@endpush

@push('js')
<script>
function paymentHistory() {
    return {
        viewMode: window.innerWidth >= 1024 ? 'table' : 'cards',
        filters: {
            customer_search: '',
            date_from: '',
            date_to: ''
        },
        filteredPayments: [],
        allPayments: @json($payments->items()),
        currentPage: 1,
        itemsPerPage: 15,
        isDeleting: false,

        init() {
            // Detectar el modo de vista inicial basado en el tamaño de pantalla
            this.updateViewMode();
            
            // Escuchar cambios de tamaño de ventana
            window.addEventListener('resize', () => {
                this.updateViewMode();
            });

            // Inicializar pagos filtrados
            this.filteredPayments = [...this.allPayments];
            
            // Configurar watchers para filtros
            this.$watch('filters.customer_search', () => this.applyFilters());
            this.$watch('filters.date_from', () => this.applyFilters());
            this.$watch('filters.date_to', () => this.applyFilters());
        },

        updateViewMode() {
            if (window.innerWidth >= 1024) {
                this.viewMode = 'table';
            } else {
                this.viewMode = 'cards';
            }
        },

        applyFilters() {
            // Filtrar pagos en tiempo real
            this.filteredPayments = this.allPayments.filter(payment => {
                let matches = true;
                
                // Filtro por nombre de cliente
                if (this.filters.customer_search) {
                    const customerName = payment.customer.name.toLowerCase();
                    const searchTerm = this.filters.customer_search.toLowerCase();
                    matches = matches && customerName.includes(searchTerm);
                }
                
                // Filtro por fecha desde
                if (this.filters.date_from) {
                    const paymentDate = new Date(payment.created_at);
                    const fromDate = new Date(this.filters.date_from);
                    matches = matches && paymentDate >= fromDate;
                }
                
                // Filtro por fecha hasta
                if (this.filters.date_to) {
                    const paymentDate = new Date(payment.created_at);
                    const toDate = new Date(this.filters.date_to);
                    toDate.setHours(23, 59, 59); // Incluir todo el día
                    matches = matches && paymentDate <= toDate;
                }
                
                return matches;
            });
            
            // Resetear a la primera página
            this.currentPage = 1;
        },

        resetFilters() {
            this.filters = {
                customer_search: '',
                date_from: '',
                date_to: ''
            };
            this.filteredPayments = [...this.allPayments];
            this.currentPage = 1;
        },

        // Computed properties para paginación
        get paginatedPayments() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredPayments.slice(start, end);
        },

        get totalPages() {
            return Math.ceil(this.filteredPayments.length / this.itemsPerPage);
        },

        get hasNextPage() {
            return this.currentPage < this.totalPages;
        },

        get hasPrevPage() {
            return this.currentPage > 1;
        },

        nextPage() {
            if (this.hasNextPage) {
                this.currentPage++;
            }
        },

        prevPage() {
            if (this.hasPrevPage) {
                this.currentPage--;
            }
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },

        async deletePayment(paymentId, customerName, paymentAmount) {
            if (this.isDeleting) return;

            const result = await this.showConfirmAlert(
                '¿Estás seguro?',
                `Vas a eliminar el pago de ${paymentAmount} {{ $currency->symbol }} del cliente ${customerName}. Esta acción restaurará la deuda al cliente.`,
                'warning'
            );

            if (result.isConfirmed) {
                this.isDeleting = true;

                try {
                    const response = await fetch(`/admin/customers/payment-history/${paymentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.showAlert('¡Pago eliminado exitosamente!', 'success');
                        
                        // Recargar la página para actualizar datos
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showAlert(data.message || 'Error al eliminar el pago', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showAlert('Error de conexión. Intente nuevamente.', 'error');
                } finally {
                    this.isDeleting = false;
                }
            }
        },

        showAlert(message, type = 'info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? '¡Éxito!' : type === 'error' ? 'Error' : 'Información',
                    text: message,
                    icon: type,
                    confirmButtonText: 'Entendido',
                    timer: type === 'success' ? 3000 : undefined,
                    timerProgressBar: type === 'success'
                });
            } else {
                alert(message);
            }
        },

        async showConfirmAlert(title, text, icon = 'warning') {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                });
                return result;
            } else {
                return { isConfirmed: confirm(text) };
            }
        }
    }
}

// Variables globales para los gráficos
let weekdayChartInstance = null;
let monthlyChartInstance = null;

// Función para inicializar gráficos de forma segura
function initializeCharts() {
    // Verificar si Chart.js está disponible
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js no está disponible. Los gráficos no se mostrarán.');
        return;
    }

    // Verificar si los elementos del canvas existen
    const weekdayChart = document.getElementById('weekdayChart');
    const monthlyChart = document.getElementById('monthlyChart');

    if (!weekdayChart && !monthlyChart) {
        console.warn('No se encontraron elementos de gráficos en la página.');
        return;
    }

    // Destruir gráficos existentes antes de crear nuevos
    if (weekdayChartInstance) {
        weekdayChartInstance.destroy();
        weekdayChartInstance = null;
    }
    if (monthlyChartInstance) {
        monthlyChartInstance.destroy();
        monthlyChartInstance = null;
    }

    // Gráfico por día de la semana
    if (document.getElementById('weekdayChart')) {
        const weekdayCtx = document.getElementById('weekdayChart').getContext('2d');
        weekdayChartInstance = new Chart(weekdayCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($weekdayLabels) !!},
                datasets: [{
                    label: 'Pagos por día de la semana',
                    data: {!! json_encode($weekdayData) !!},
                    backgroundColor: 'rgba(102, 126, 234, 0.6)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(31, 41, 55, 0.9)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(255, 255, 255, 0.2)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return '{{ $currency->symbol }} ' + context.raw.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '{{ $currency->symbol }} ' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico por mes
    if (document.getElementById('monthlyChart')) {
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        monthlyChartInstance = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthlyLabels) !!},
                datasets: [{
                    label: 'Pagos por mes',
                    data: {!! json_encode($monthlyData) !!},
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 3,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(31, 41, 55, 0.9)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(255, 255, 255, 0.2)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return '{{ $currency->symbol }} ' + context.raw.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '{{ $currency->symbol }} ' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }
}

// Variable para controlar si ya se inicializaron los gráficos
let chartsInitialized = false;

// Función para inicializar gráficos una sola vez
function initializeChartsOnce() {
    if (chartsInitialized) {
        return;
    }
    
    if (typeof Chart !== 'undefined') {
        initializeCharts();
        chartsInitialized = true;
    }
}

// Función para limpiar gráficos al salir de la página
function cleanupCharts() {
    if (weekdayChartInstance) {
        weekdayChartInstance.destroy();
        weekdayChartInstance = null;
    }
    if (monthlyChartInstance) {
        monthlyChartInstance.destroy();
        monthlyChartInstance = null;
    }
    chartsInitialized = false;
}

// Limpiar gráficos cuando se navegue fuera de la página
window.addEventListener('beforeunload', cleanupCharts);

// Inicializar gráficos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un poco más para asegurar que Chart.js esté cargado
    setTimeout(initializeChartsOnce, 100);
});

// También intentar inicializar cuando la ventana esté completamente cargada
window.addEventListener('load', function() {
    if (typeof Chart === 'undefined') {
        setTimeout(initializeChartsOnce, 200);
    } else {
        initializeChartsOnce();
    }
});
</script>
@endpush 