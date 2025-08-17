@extends('layouts.app')

@section('title', 'Gestión de Caja')

@section('content')
<div class="space-y-6">
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative px-6 py-8 sm:px-8 sm:py-12">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-cash-register text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                                Gestión de Caja
                            </h1>
                            <p class="text-blue-100 text-lg">
                                Control y administración del flujo de efectivo de la empresa
                            </p>
                            @if ($currentCashCount)
                                <div class="mt-4 flex items-center space-x-2 text-blue-100">
                                    <i class="fas fa-clock"></i>
                                    <span>Caja abierta desde: {{ \Carbon\Carbon::parse($currentCashCount->opening_date)->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-3">
                    @if ($currentCashCount)
                        @can('cash-counts.store-movement')
                            <a href="{{ route('admin.cash-counts.create-movement') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30">
                                <i class="fas fa-money-bill-wave mr-2"></i>
                                <span class="hidden sm:inline">Nuevo Movimiento</span>
                            </a>
                        @endcan
                        @can('cash-counts.close')
                            <a href="{{ route('admin.cash-counts.close', $currentCashCount->id) }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-red-500 bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-red-300 border-opacity-30">
                                <i class="fas fa-cash-register mr-2"></i>
                                <span class="hidden sm:inline">Cerrar Caja</span>
                            </a>
                        @endcan
                    @else
                        @can('cash-counts.report')
                            <a href="{{ route('admin.cash-counts.report') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30"
                               target="_blank">
                                <i class="fas fa-file-pdf mr-2"></i>
                                <span class="hidden sm:inline">Reporte</span>
                            </a>
                        @endcan
                        @can('cash-counts.create')
                            <a href="{{ route('admin.cash-counts.create') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30">
                                <i class="fas fa-cash-register mr-2"></i>
                                <span class="hidden sm:inline">Abrir Caja</span>
                            </a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Estado Actual de Caja -->
    @if ($currentCashCount)
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-6 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-cash-register text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Caja Actual</h2>
                        <p class="text-gray-600">Abierta desde: {{ \Carbon\Carbon::parse($currentCashCount->opening_date)->format('d/m/Y H:i') }} | Monto Inicial: {{ $currency->symbol }} {{ number_format($currentCashCount->initial_amount, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Balance Actual -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-wallet text-white text-xl"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-2xl font-bold text-white">{{ $currency->symbol }} {{ number_format($currentBalance, 2) }}</h3>
                        <p class="text-blue-100 text-sm">Balance Actual</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ingresos del Día -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-arrow-up text-white text-xl"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-2xl font-bold text-white">{{ $currency->symbol }} {{ number_format($todayIncome, 2) }}</h3>
                        <p class="text-green-100 text-sm">Ingresos del Día</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Egresos del Día -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-red-600 to-pink-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-arrow-down text-white text-xl"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-2xl font-bold text-white">{{ $currency->symbol }} {{ number_format($todayExpenses, 2) }}</h3>
                        <p class="text-red-100 text-sm">Egresos del Día</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movimientos del Día -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-600 to-orange-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exchange-alt text-white text-xl"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-2xl font-bold text-white">{{ $totalMovements }}</h3>
                        <p class="text-yellow-100 text-sm">Movimientos del Día</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Arqueos -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-6 border-b border-gray-200">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-list text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Historial de Arqueos</h2>
                    <p class="text-gray-600">Registro de todos los arqueos de caja realizados</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">ID</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Fecha Apertura</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Fecha Cierre</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Monto Inicial</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Monto Final</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Diferencia</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Estado</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($cashCounts as $cashCount)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ str_pad($cashCount->id, 4, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($cashCount->opening_date)->format('d/m/Y') }}</span>
                                <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($cashCount->opening_date)->format('H:i') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if ($cashCount->closing_date)
                                <div class="flex flex-col">
                                    <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($cashCount->closing_date)->format('d/m/Y') }}</span>
                                    <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($cashCount->closing_date)->format('H:i') }}</span>
                                </div>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    En curso
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $currency->symbol }} {{ number_format($cashCount->initial_amount, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if ($cashCount->final_amount)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ $currency->symbol }} {{ number_format($cashCount->final_amount, 2) }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    Pendiente
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($cashCount->final_amount)
                                @php
                                    $difference = $cashCount->final_amount - $cashCount->initial_amount;
                                    $badgeClass = $difference >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $badgeClass }}">
                                    {{ $currency->symbol }} {{ number_format(abs($difference), 2) }}
                                    <i class="fas fa-{{ $difference >= 0 ? 'arrow-up' : 'arrow-down' }} ml-1"></i>
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    Pendiente
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($cashCount->closing_date)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    Cerrado
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    Abierto
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                @can('cash-counts.show')
                                    <button @click="window.openCashCountModal({{ $cashCount->id }})"
                                            class="w-8 h-8 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg flex items-center justify-center transition-all duration-200"
                                            title="Ver movimientos">
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>
                                @endcan
                                
                                <!-- Botón de prueba temporal -->
                                <button @click="window.testModal()"
                                        class="w-8 h-8 bg-green-100 hover:bg-green-200 text-green-600 rounded-lg flex items-center justify-center transition-all duration-200 ml-1"
                                        title="Probar modal">
                                    <i class="fas fa-bug text-sm"></i>
                                </button>
                                @if (!$cashCount->closing_date)
                                    @can('cash-counts.edit')
                                        <a href="{{ route('admin.cash-counts.edit', $cashCount->id) }}"
                                           class="w-8 h-8 bg-yellow-100 hover:bg-yellow-200 text-yellow-600 rounded-lg flex items-center justify-center transition-all duration-200"
                                           title="Editar">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                    @endcan
                                @endif
                                @can('cash-counts.destroy')
                                    <form action="{{ route('admin.cash-counts.destroy', $cashCount->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este arqueo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg flex items-center justify-center transition-all duration-200"
                                                title="Eliminar">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center space-y-4">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-cash-register text-gray-400 text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">No hay arqueos de caja registrados</h3>
                                    <p class="text-gray-600">Comienza creando tu primer arqueo de caja</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($cashCounts->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                    Mostrando {{ $cashCounts->firstItem() ?? 0 }} a {{ $cashCounts->lastItem() ?? 0 }} de {{ $cashCounts->total() }} registros
                </div>
                <div class="flex justify-center">
                    {{ $cashCounts->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gráfico de Movimientos -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-6 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Movimientos de Caja</h2>
                        <p class="text-gray-600">Tendencia de ingresos y egresos</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="cashMovementsChart" height="250"></canvas>
            </div>
        </div>

        <!-- Gráfico de Distribución -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-6 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-pie text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Distribución de Movimientos</h2>
                        <p class="text-gray-600">Proporción de ingresos vs egresos</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="movementsDistributionChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar detalles del arqueo de caja -->
    <div x-data="cashCountModal()" 
         x-init="init()"
         x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="isOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="relative w-full max-w-6xl bg-white rounded-2xl shadow-2xl overflow-hidden"
                 @click.away="closeModal()">
                
                <!-- Header del Modal -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                <i class="fas fa-cash-register text-white text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-white">Detalles del Arqueo de Caja</h2>
                                <p class="text-blue-100" x-text="cashCountData ? 'Desde ' + formatDate(cashCountData.opening_date) + ' hasta ' + (cashCountData.closing_date ? formatDate(cashCountData.closing_date) : 'actualidad') : ''"></p>
                            </div>
                        </div>
                        <button @click="closeModal()" 
                                class="w-10 h-10 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-xl flex items-center justify-center transition-all duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="p-12 text-center">
                    <div class="inline-flex items-center space-x-3">
                        <div class="w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                        <span class="text-gray-600 font-medium">Cargando información...</span>
                    </div>
                </div>

                <!-- Error State -->
                <div x-show="!loading && !cashCountData" class="p-12 text-center">
                    <div class="flex flex-col items-center space-y-4">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Error al cargar datos</h3>
                            <p class="text-gray-600">No se pudieron cargar los datos del arqueo de caja</p>
                        </div>
                        <button @click="closeModal()" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            Cerrar
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div x-show="!loading && cashCountData && Object.keys(cashCountData).length > 0" class="p-6">
                    <!-- Sistema de Pestañas -->
                    <div class="space-y-6">
                        <!-- Navegación de Pestañas -->
                        <div class="border-b border-gray-200">
                            <nav class="flex space-x-8" aria-label="Tabs">
                                <!-- Pestaña Clientes -->
                                <button @click="activeTab = 'clientes'" 
                                        :class="activeTab === 'clientes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-users"></i>
                                        <span>Clientes</span>
                                    </div>
                                </button>

                                <!-- Pestaña Ventas -->
                                <button @click="activeTab = 'ventas'" 
                                        :class="activeTab === 'ventas' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-chart-bar"></i>
                                        <span>Ventas</span>
                                    </div>
                                </button>

                                <!-- Pestaña Compras -->
                                <button @click="activeTab = 'compras'" 
                                        :class="activeTab === 'compras' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-shopping-bag"></i>
                                        <span>Compras</span>
                                    </div>
                                </button>

                                <!-- Pestaña Productos -->
                                <button @click="activeTab = 'productos'" 
                                        :class="activeTab === 'productos' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-box"></i>
                                        <span>Productos</span>
                                    </div>
                                </button>

                                <!-- Pestaña Pedidos -->
                                <button @click="activeTab = 'pedidos'" 
                                        :class="activeTab === 'pedidos' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-clipboard-list"></i>
                                        <span>Pedidos</span>
                                    </div>
                                </button>
                            </nav>
                        </div>

                        <!-- Contenido de Pestañas -->
                        <div class="space-y-6">
                            <!-- Pestaña Clientes -->
                            <div x-show="activeTab === 'clientes'" class="space-y-6">
                                <!-- Header de la Pestaña -->
                                <div class="flex items-center space-x-4 mb-6">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-users text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900">Información por Clientes</h3>
                                        <p class="text-gray-600">Análisis detallado de la actividad de clientes en este arqueo</p>
                                    </div>
                                </div>

                                <!-- 4 Widgets de Clientes -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <!-- Widget 1 -->
                                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-user-friends text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="0">0</div>
                                                <div class="text-blue-100 text-sm">Total Clientes</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-blue-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 2 -->
                                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-shopping-cart text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="formatCurrency(0)">$0.00</div>
                                                <div class="text-green-100 text-sm">Ventas Totales</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-green-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 3 -->
                                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="formatCurrency(0)">$0.00</div>
                                                <div class="text-red-100 text-sm">Deudas Pendientes</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-red-100 text-sm">
                                            <i class="fas fa-arrow-down mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 4 -->
                                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-chart-line text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="formatCurrency(0)">$0.00</div>
                                                <div class="text-purple-100 text-sm">Promedio por Cliente</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-purple-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mensaje de Estado -->
                                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center">
                                    <div class="flex items-center justify-center space-x-3">
                                        <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                                        <div>
                                            <h4 class="text-lg font-semibold text-blue-900">Pestaña de Clientes</h4>
                                            <p class="text-blue-700">Aquí se mostrará la información detallada de clientes para este arqueo de caja</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Ventas -->
                            <div x-show="activeTab === 'ventas'" class="space-y-6">
                                <!-- Header de la Pestaña -->
                                <div class="flex items-center space-x-4 mb-6">
                                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-chart-bar text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900">Análisis de Ventas</h3>
                                        <p class="text-gray-600">Estadísticas detalladas de ventas en este arqueo de caja</p>
                                    </div>
                                </div>

                                <!-- 4 Widgets de Ventas -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <!-- Widget 1 -->
                                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-dollar-sign text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="formatCurrency(0)">$0.00</div>
                                                <div class="text-green-100 text-sm">Ventas Totales</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-green-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 2 -->
                                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-receipt text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="0">0</div>
                                                <div class="text-blue-100 text-sm">Facturas</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-blue-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 3 -->
                                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-chart-line text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="formatCurrency(0)">$0.00</div>
                                                <div class="text-purple-100 text-sm">Promedio por Venta</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-purple-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 4 -->
                                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-calendar-day text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="0">0</div>
                                                <div class="text-orange-100 text-sm">Ventas Hoy</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-orange-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mensaje de Estado -->
                                <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
                                    <div class="flex items-center justify-center space-x-3">
                                        <i class="fas fa-info-circle text-green-600 text-xl"></i>
                                        <div>
                                            <h4 class="text-lg font-semibold text-green-900">Pestaña de Ventas</h4>
                                            <p class="text-green-700">Aquí se mostrará la información detallada de ventas para este arqueo de caja</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Compras -->
                            <div x-show="activeTab === 'compras'" class="space-y-6">
                                <!-- Header de la Pestaña -->
                                <div class="flex items-center space-x-4 mb-6">
                                    <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-shopping-bag text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900">Análisis de Compras</h3>
                                        <p class="text-gray-600">Estadísticas detalladas de compras en este arqueo de caja</p>
                                    </div>
                                </div>

                                <!-- 4 Widgets de Compras -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <!-- Widget 1 -->
                                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-shopping-cart text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="formatCurrency(0)">$0.00</div>
                                                <div class="text-orange-100 text-sm">Compras Totales</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-orange-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 2 -->
                                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-truck text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="0">0</div>
                                                <div class="text-red-100 text-sm">Proveedores</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-red-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 3 -->
                                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-boxes text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="0">0</div>
                                                <div class="text-yellow-100 text-sm">Productos Comprados</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-yellow-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 4 -->
                                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-chart-pie text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="formatCurrency(0)">$0.00</div>
                                                <div class="text-indigo-100 text-sm">Promedio por Compra</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-indigo-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mensaje de Estado -->
                                <div class="bg-orange-50 border border-orange-200 rounded-xl p-6 text-center">
                                    <div class="flex items-center justify-center space-x-3">
                                        <i class="fas fa-info-circle text-orange-600 text-xl"></i>
                                        <div>
                                            <h4 class="text-lg font-semibold text-orange-900">Pestaña de Compras</h4>
                                            <p class="text-orange-700">Aquí se mostrará la información detallada de compras para este arqueo de caja</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Productos -->
                            <div x-show="activeTab === 'productos'" class="space-y-6">
                                <!-- Header de la Pestaña -->
                                <div class="flex items-center space-x-4 mb-6">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-box text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900">Análisis de Productos</h3>
                                        <p class="text-gray-600">Estadísticas detalladas de productos en este arqueo de caja</p>
                                    </div>
                                </div>

                                <!-- 4 Widgets de Productos -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <!-- Widget 1 -->
                                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-boxes text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="0">0</div>
                                                <div class="text-purple-100 text-sm">Total Productos</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-purple-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 2 -->
                                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="0">0</div>
                                                <div class="text-red-100 text-sm">Stock Bajo</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-red-100 text-sm">
                                            <i class="fas fa-arrow-down mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 3 -->
                                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-fire text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="0">0</div>
                                                <div class="text-green-100 text-sm">Más Vendidos</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-green-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 4 -->
                                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-dollar-sign text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="formatCurrency(0)">$0.00</div>
                                                <div class="text-blue-100 text-sm">Valor Inventario</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-blue-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mensaje de Estado -->
                                <div class="bg-purple-50 border border-purple-200 rounded-xl p-6 text-center">
                                    <div class="flex items-center justify-center space-x-3">
                                        <i class="fas fa-info-circle text-purple-600 text-xl"></i>
                                        <div>
                                            <h4 class="text-lg font-semibold text-purple-900">Pestaña de Productos</h4>
                                            <p class="text-purple-700">Aquí se mostrará la información detallada de productos para este arqueo de caja</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Pedidos -->
                            <div x-show="activeTab === 'pedidos'" class="space-y-6">
                                <!-- Header de la Pestaña -->
                                <div class="flex items-center space-x-4 mb-6">
                                    <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-clipboard-list text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900">Análisis de Pedidos</h3>
                                        <p class="text-gray-600">Estadísticas detalladas de pedidos en este arqueo de caja</p>
                                    </div>
                                </div>

                                <!-- 4 Widgets de Pedidos -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <!-- Widget 1 -->
                                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-clipboard-list text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="0">0</div>
                                                <div class="text-red-100 text-sm">Total Pedidos</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-red-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 2 -->
                                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-clock text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="0">0</div>
                                                <div class="text-yellow-100 text-sm">Pendientes</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-yellow-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 3 -->
                                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-check-circle text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="0">0</div>
                                                <div class="text-green-100 text-sm">Completados</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-green-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 4 -->
                                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-dollar-sign text-white text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold" x-text="formatCurrency(0)">$0.00</div>
                                                <div class="text-blue-100 text-sm">Valor Total</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-blue-100 text-sm">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>0% vs anterior</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mensaje de Estado -->
                                <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
                                    <div class="flex items-center justify-center space-x-3">
                                        <i class="fas fa-info-circle text-red-600 text-xl"></i>
                                        <div>
                                            <h4 class="text-lg font-semibold text-red-900">Pestaña de Pedidos</h4>
                                            <p class="text-red-700">Aquí se mostrará la información detallada de pedidos para este arqueo de caja</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
// Variables globales para el modal
let cashCountModalInstance = null;

// Función Alpine.js para el modal de arqueos de caja
function cashCountModal() {
    return {
        isOpen: false,
        loading: false,
        cashCountData: null,
        activeTab: 'clientes', // Pestaña activa por defecto
        currencySymbol: '{{ $currency->symbol }}',

        init() {
            // Guardar referencia global
            cashCountModalInstance = this;
        },

        closeModal() {
            this.isOpen = false;
            this.cashCountData = null;
            
            // Restaurar scroll del body
            document.body.style.overflow = 'auto';
        },

        async loadCashCountData(cashCountId) {
            try {
                console.log('Cargando datos para arqueo ID:', cashCountId);
                
                const response = await fetch(`/cash-counts/${cashCountId}/details`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                console.log('Response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success && data.data) {
                    this.cashCountData = data.data;
                    console.log('Datos cargados exitosamente:', this.cashCountData);
                } else {
                    throw new Error(data.message || 'Error al cargar los datos');
                }
            } catch (error) {
                console.error('Error cargando datos:', error);
                this.cashCountData = null;
                this.showNotification(`Error al cargar los datos del arqueo: ${error.message}`, 'error');
            } finally {
                this.loading = false;
            }
        },

        formatCurrency(amount) {
            if (amount === null || amount === undefined || amount === '') {
                return this.currencySymbol + ' 0.00';
            }
            const num = parseFloat(amount);
            if (isNaN(num)) {
                return this.currencySymbol + ' 0.00';
            }
            return this.currencySymbol + ' ' + num.toFixed(2);
        },

        formatDate(dateString) {
            if (!dateString || dateString === 'null' || dateString === 'undefined') {
                return 'N/A';
            }
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    return 'N/A';
                }
                return date.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            } catch (error) {
                console.error('Error formateando fecha:', error);
                return 'N/A';
            }
        },

        formatDateTime(dateString) {
            if (!dateString || dateString === 'null' || dateString === 'undefined') {
                return 'N/A';
            }
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    return 'N/A';
                }
                return date.toLocaleString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (error) {
                console.error('Error formateando fecha/hora:', error);
                return 'N/A';
            }
        },

        showNotification(message, type = 'info') {
            console.log(`[${type.toUpperCase()}] ${message}`);
            
            // Implementar notificación (puedes usar SweetAlert2 o similar)
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'error' ? 'Error' : 'Información',
                    text: message,
                    icon: type,
                    confirmButtonText: 'OK',
                    timer: type === 'error' ? null : 3000,
                    timerProgressBar: type !== 'error'
                });
            } else {
                // Fallback a alert nativo
                const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';
                alert(`${icon} ${message}`);
            }
        }
    }
}

// Funciones globales para abrir el modal
window.openCashCountModal = function(cashCountId) {
    if (cashCountModalInstance) {
        cashCountModalInstance.isOpen = true;
        cashCountModalInstance.loading = true;
        cashCountModalInstance.cashCountData = null;
        
        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
        
        // Cargar datos del arqueo
        cashCountModalInstance.loadCashCountData(cashCountId);
    } else {
        console.error('Modal instance not found');
        alert('Error: Modal no disponible');
    }
};

window.testModal = function() {
    if (cashCountModalInstance) {
        console.log('Probando modal...');
        cashCountModalInstance.isOpen = true;
        cashCountModalInstance.loading = false;
        cashCountModalInstance.cashCountData = {
            id: 999,
            initial_amount: 1000.00,
            final_amount: null,
            opening_date: '2024-01-01T00:00:00.000000Z',
            closing_date: null,
            observations: 'Arqueo de prueba',
            total_income: 500.00,
            total_expenses: 200.00,
            current_balance: 1300.00,
            movements_count: 3,
            movements: [
                {
                    id: 1,
                    type: 'income',
                    amount: 300.00,
                    description: 'Venta de productos',
                    created_at: '2024-01-01T10:00:00.000000Z'
                },
                {
                    id: 2,
                    type: 'income',
                    amount: 200.00,
                    description: 'Pago de deuda',
                    created_at: '2024-01-01T11:00:00.000000Z'
                },
                {
                    id: 3,
                    type: 'expense',
                    amount: 200.00,
                    description: 'Compra de suministros',
                    created_at: '2024-01-01T12:00:00.000000Z'
                }
            ]
        };
        
        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
        
        cashCountModalInstance.showNotification('Modal de prueba cargado exitosamente', 'success');
    } else {
        console.error('Modal instance not found');
        alert('Error: Modal no disponible');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // Inicialización de gráficos
    if (typeof Chart !== 'undefined') {
        console.log('Inicializando gráficos...');

        // Gráfico de Movimientos
        const movementsCtx = document.getElementById('cashMovementsChart');
        if (movementsCtx) {
            new Chart(movementsCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartData['labels']) !!},
                    datasets: [{
                        label: 'Ingresos',
                        data: {!! json_encode($chartData['income']) !!},
                        borderColor: '#4facfe',
                        backgroundColor: 'rgba(79, 172, 254, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#4facfe',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }, {
                        label: 'Egresos',
                        data: {!! json_encode($chartData['expenses']) !!},
                        borderColor: '#fa709a',
                        backgroundColor: 'rgba(250, 112, 154, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#fa709a',
                        pointBorderColor: '#fff',
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
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '600'
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
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de Distribución
        const distributionCtx = document.getElementById('movementsDistributionChart');
        if (distributionCtx) {
            new Chart(distributionCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Ingresos', 'Egresos'],
                    datasets: [{
                        data: [{{ $todayIncome }}, {{ $todayExpenses }}],
                        backgroundColor: ['#4facfe', '#fa709a'],
                        borderWidth: 0,
                        hoverBorderWidth: 3,
                        hoverBorderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }
        
        console.log('Gráficos inicializados correctamente');
    }
});
</script>
@endpush
