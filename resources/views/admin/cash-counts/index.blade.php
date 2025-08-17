@extends('layouts.app')

@section('title', 'Gestión de Caja')

@section('content')
<div x-data="cashManagement()" class="space-y-6">
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
                            <button @click="openNewMovementModal()" 
                                    class="inline-flex items-center justify-center px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30">
                                <i class="fas fa-money-bill-wave mr-2"></i>
                                <span class="hidden sm:inline">Nuevo Movimiento</span>
                            </button>
                        @endcan
                        @can('cash-counts.close')
                            <button @click="openCloseCashModal()" 
                                    class="inline-flex items-center justify-center px-6 py-3 bg-red-500 bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-red-300 border-opacity-30">
                                <i class="fas fa-cash-register mr-2"></i>
                                <span class="hidden sm:inline">Cerrar Caja</span>
                            </button>
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
                            <button @click="openCashModal()" 
                                    class="inline-flex items-center justify-center px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30">
                                <i class="fas fa-cash-register mr-2"></i>
                                <span class="hidden sm:inline">Abrir Caja</span>
                            </button>
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
                                    <button @click="viewMovements({{ $cashCount->id }})"
                                            class="w-8 h-8 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg flex items-center justify-center transition-all duration-200"
                                            title="Ver movimientos">
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>
                                    <button @click="viewHistory({{ $cashCount->id }})"
                                            class="w-8 h-8 bg-purple-100 hover:bg-purple-200 text-purple-600 rounded-lg flex items-center justify-center transition-all duration-200"
                                            title="Historial Completo">
                                        <i class="fas fa-history text-sm"></i>
                                    </button>
                                @endcan
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
                                    <button @click="deleteCashCount({{ $cashCount->id }})"
                                            class="w-8 h-8 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg flex items-center justify-center transition-all duration-200"
                                            title="Eliminar">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
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
</div>

    <!-- Modales -->
    <!-- Modal para Abrir Caja -->
    <div x-show="showOpenCashModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal('showOpenCashModal')"></div>
            
            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-cash-register text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Abrir Nueva Caja</h3>
                    </div>
                    <button @click="closeModal('showOpenCashModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitOpenCash()">
                    <div class="space-y-6">
                        <!-- Monto Inicial -->
                        <div>
                            <label for="initial_amount" class="block text-sm font-semibold text-gray-700 mb-2">
                                Monto Inicial <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" 
                                       id="initial_amount"
                                       x-model="openCashForm.initial_amount"
                                       step="0.01"
                                       class="block w-full pl-8 pr-12 py-3 border-2 border-gray-200 rounded-xl text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       required>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label for="observations" class="block text-sm font-semibold text-gray-700 mb-2">
                                Observaciones
                            </label>
                            <textarea id="observations"
                                      x-model="openCashForm.observations"
                                      rows="3"
                                      class="block w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Observaciones adicionales..."></textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3 mt-8">
                        <button type="button" 
                                @click="closeModal('showOpenCashModal')"
                                class="px-6 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 font-semibold rounded-xl transition-all duration-200">
                            Cancelar
                        </button>
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-save mr-2"></i>
                            <span x-text="isSubmitting ? 'Abriendo...' : 'Abrir Caja'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Nuevo Movimiento -->
    <div x-show="showNewMovementModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal('showNewMovementModal')"></div>
            
            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Nuevo Movimiento</h3>
                    </div>
                    <button @click="closeModal('showNewMovementModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitNewMovement()">
                    <div class="space-y-6">
                        <!-- Tipo de Movimiento -->
                        <div>
                            <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tipo de Movimiento
                            </label>
                            <select id="type"
                                    x-model="newMovementForm.type"
                                    class="block w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required>
                                <option value="income">Ingreso</option>
                                <option value="expense">Egreso</option>
                            </select>
                        </div>

                        <!-- Monto -->
                        <div>
                            <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">
                                Monto
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" 
                                       id="amount"
                                       x-model="newMovementForm.amount"
                                       step="0.01"
                                       class="block w-full pl-8 pr-12 py-3 border-2 border-gray-200 rounded-xl text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       required>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                Descripción
                            </label>
                            <textarea id="description"
                                      x-model="newMovementForm.description"
                                      rows="3"
                                      class="block w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Descripción del movimiento..."
                                      required></textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3 mt-8">
                        <button type="button" 
                                @click="closeModal('showNewMovementModal')"
                                class="px-6 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 font-semibold rounded-xl transition-all duration-200">
                            Cancelar
                        </button>
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-save mr-2"></i>
                            <span x-text="isSubmitting ? 'Guardando...' : 'Guardar Movimiento'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Cerrar Caja -->
    <div x-show="showCloseCashModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal('showCloseCashModal')"></div>
            
            <div class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-cash-register text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Cerrar Caja</h3>
                    </div>
                    <button @click="closeModal('showCloseCashModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Monto Inicial</p>
                                <p class="font-semibold text-gray-900">{{ $currency->symbol }} {{ number_format($currentCashCount->initial_amount ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-arrow-up text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Ingresos</p>
                                <p class="font-semibold text-gray-900">{{ $currency->symbol }} {{ number_format($todayIncome, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-red-50 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-arrow-down text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Egresos</p>
                                <p class="font-semibold text-gray-900">{{ $currency->symbol }} {{ number_format($todayExpenses, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-purple-50 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cash-register text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Balance Actual</p>
                                <p class="font-semibold text-gray-900">{{ $currency->symbol }} {{ number_format($currentBalance, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitCloseCash()">
                    <div class="space-y-6">
                        <!-- Monto Final -->
                        <div>
                            <label for="final_amount" class="block text-sm font-semibold text-gray-700 mb-2">
                                Monto Final en Caja
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" 
                                       id="final_amount"
                                       x-model="closeCashForm.final_amount"
                                       step="0.01"
                                       class="block w-full pl-8 pr-12 py-3 border-2 border-gray-200 rounded-xl text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       required>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label for="closing_observations" class="block text-sm font-semibold text-gray-700 mb-2">
                                Observaciones del Cierre
                            </label>
                            <textarea id="closing_observations"
                                      x-model="closeCashForm.observations"
                                      rows="3"
                                      class="block w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Observaciones del cierre..."></textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3 mt-8">
                        <button type="button" 
                                @click="closeModal('showCloseCashModal')"
                                class="px-6 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 font-semibold rounded-xl transition-all duration-200">
                            Cancelar
                        </button>
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-semibold rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-lock mr-2"></i>
                            <span x-text="isSubmitting ? 'Cerrando...' : 'Cerrar Caja'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Detalles del Arqueo -->
    <div x-show="showDetailsModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal('showDetailsModal')"></div>
            
            <div class="inline-block w-full max-w-7xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-chart-bar text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">Detalles del Arqueo</h3>
                            <p class="text-gray-600">Información completa del período de caja</p>
                        </div>
                    </div>
                    <button @click="closeModal('showDetailsModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <!-- Pestañas -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8">
                        <button @click="changeTab('customers')" 
                                :class="activeTab === 'customers' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                            <i class="fas fa-users mr-2"></i>
                            Clientes
                        </button>
                        <button @click="changeTab('sales')" 
                                :class="activeTab === 'sales' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Ventas
                        </button>
                        <button @click="changeTab('purchases')" 
                                :class="activeTab === 'purchases' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                            <i class="fas fa-truck mr-2"></i>
                            Compras
                        </button>
                        <button @click="changeTab('products')" 
                                :class="activeTab === 'products' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                            <i class="fas fa-box mr-2"></i>
                            Productos
                        </button>
                    </nav>
                </div>

                <!-- Filtros -->
                <div class="bg-gray-50 rounded-xl p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                        <!-- Búsqueda -->
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="text" 
                                   x-model="searchTerm" 
                                   @input="applyFilters()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Buscar...">
                        </div>
                        
                        <!-- Monto mínimo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monto mínimo</label>
                            <input type="number" 
                                   x-model="minAmount" 
                                   @input="applyFilters()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="0.00">
                        </div>
                        
                        <!-- Monto máximo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monto máximo</label>
                            <input type="number" 
                                   x-model="maxAmount" 
                                   @input="applyFilters()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="999999.99">
                        </div>
                        
                        <!-- Fecha inicio -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                            <input type="date" 
                                   x-model="startDate" 
                                   @input="applyFilters()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <!-- Fecha fin -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                            <input type="date" 
                                   x-model="endDate" 
                                   @input="applyFilters()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    
                    <div class="mt-4 flex justify-between items-center">
                        <button @click="clearFilters()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Limpiar filtros
                        </button>
                        
                        <div class="text-sm text-gray-600">
                            <span x-text="`${filteredCustomers.length || filteredSales.length || filteredPurchases.length || filteredProducts.length} registros encontrados`"></span>
                        </div>
                    </div>
                </div>

                <!-- Contenido de las pestañas -->
                <div class="min-h-96">
                    <!-- Loading -->
                    <div x-show="loading" class="flex items-center justify-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                    </div>

                    <!-- Pestaña Clientes -->
                    <div x-show="!loading && activeTab === 'customers'" class="space-y-4">
                        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                                <div>
                                    <p class="text-sm text-gray-600">Total Clientes</p>
                                    <p class="text-2xl font-bold text-gray-900" x-text="filteredCustomers.length"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Ventas</p>
                                    <p class="text-2xl font-bold text-green-600" x-text="formatCurrency(getTotalAmount(filteredCustomers, 'total_sales'))"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Deuda</p>
                                    <p class="text-2xl font-bold text-red-600" x-text="formatCurrency(getTotalAmount(filteredCustomers, 'total_debt'))"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Ventas</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deuda</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="customer in filteredCustomers" :key="customer.id">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900" x-text="customer.name"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500" x-text="customer.phone"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" x-text="customer.sales_count"></span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-green-600" x-text="formatCurrency(customer.total_sales)"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-red-600" x-text="formatCurrency(customer.total_debt)"></div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña Ventas -->
                    <div x-show="!loading && activeTab === 'sales'" class="space-y-4">
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-center">
                                <div>
                                    <p class="text-sm text-gray-600">Total Ventas</p>
                                    <p class="text-2xl font-bold text-gray-900" x-text="filteredSales.length"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Monto Total</p>
                                    <p class="text-2xl font-bold text-green-600" x-text="formatCurrency(getTotalAmount(filteredSales, 'amount'))"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="sale in filteredSales" :key="sale.id">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900" x-text="sale.customer_name"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-green-600" x-text="formatCurrency(sale.amount)"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500" x-text="new Date(sale.date).toLocaleDateString('es-AR')"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span :class="sale.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" 
                                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                                          x-text="sale.status === 'completed' ? 'Completada' : 'Pendiente'"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña Compras -->
                    <div x-show="!loading && activeTab === 'purchases'" class="space-y-4">
                        <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-xl p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-center">
                                <div>
                                    <p class="text-sm text-gray-600">Total Compras</p>
                                    <p class="text-2xl font-bold text-gray-900" x-text="filteredPurchases.length"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Monto Total</p>
                                    <p class="text-2xl font-bold text-red-600" x-text="formatCurrency(getTotalAmount(filteredPurchases, 'amount'))"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="purchase in filteredPurchases" :key="purchase.id">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900" x-text="purchase.supplier_name"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-red-600" x-text="formatCurrency(purchase.amount)"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500" x-text="new Date(purchase.date).toLocaleDateString('es-AR')"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span :class="purchase.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" 
                                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                                          x-text="purchase.status === 'completed' ? 'Completada' : 'Pendiente'"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña Productos -->
                    <div x-show="!loading && activeTab === 'products'" class="space-y-4">
                        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                                <div>
                                    <p class="text-sm text-gray-600">Total Productos</p>
                                    <p class="text-2xl font-bold text-gray-900" x-text="filteredProducts.length"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Ventas</p>
                                    <p class="text-2xl font-bold text-blue-600" x-text="getTotalAmount(filteredProducts, 'sales_count')"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Ganancias</p>
                                    <p class="text-2xl font-bold text-green-600" x-text="formatCurrency(getTotalAmount(filteredProducts, 'total_profit'))"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Compra</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Venta</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ganancia</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="product in filteredProducts" :key="product.id">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900" x-text="product.name"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" x-text="product.sales_count"></span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500" x-text="formatCurrency(product.purchase_price)"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500" x-text="formatCurrency(product.sale_price)"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-green-600" x-text="formatCurrency(product.total_profit)"></div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
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
// Aislar Alpine.js de Livewire para evitar conflictos
(function() {
    'use strict';
    
    // Verificar que Alpine.js esté disponible
    if (typeof Alpine === 'undefined') {
        console.error('Alpine.js no está disponible');
        return;
    }
    
    // Función principal con validación robusta
    function cashManagement() {
        return {
            // Estados de modales con valores por defecto seguros
            showOpenCashModal: false,
            showNewMovementModal: false,
            showCloseCashModal: false,
            showDetailsModal: false,
            isSubmitting: false,
            loading: false,
            activeTab: 'customers',
            currentCashCountId: null,
            
            // Formularios con estructura completa
            openCashForm: { 
                initial_amount: 0.00, 
                observations: '' 
            },
            newMovementForm: { 
                type: 'income', 
                amount: '', 
                description: '' 
            },
            closeCashForm: { 
                final_amount: {{ $currentBalance ?? 0 }}, 
                observations: '' 
            },
            
            // Datos del modal de detalles con arrays vacíos
            customersData: [],
            salesData: [],
            purchasesData: [],
            productsData: [],
            filteredCustomers: [],
            filteredSales: [],
            filteredPurchases: [],
            filteredProducts: [],
            
            // Filtros con valores por defecto
            searchTerm: '',
            minAmount: '',
            maxAmount: '',
            startDate: '',
            endDate: '',
            
            // Inicialización robusta
            init() {
                console.log('Inicializando cashManagement...');
                
                // Verificar que todas las variables estén definidas
                this.ensureVariablesDefined();
                
                // Inicializar variables filtradas
                this.initializeFilteredArrays();
                
                // Inicializar gráficos cuando el DOM esté listo
                this.$nextTick(() => {
                    this.initializeCharts();
                });
                
                console.log('cashManagement inicializado correctamente');
            },
            
            // Función para asegurar que todas las variables estén definidas
            ensureVariablesDefined() {
                const requiredVars = [
                    'showOpenCashModal', 'showNewMovementModal', 'showCloseCashModal', 'showDetailsModal',
                    'isSubmitting', 'loading', 'activeTab', 'currentCashCountId',
                    'openCashForm', 'newMovementForm', 'closeCashForm',
                    'customersData', 'salesData', 'purchasesData', 'productsData',
                    'filteredCustomers', 'filteredSales', 'filteredPurchases', 'filteredProducts',
                    'searchTerm', 'minAmount', 'maxAmount', 'startDate', 'endDate'
                ];
                
                requiredVars.forEach(varName => {
                    if (this[varName] === undefined) {
                        console.warn(`Variable ${varName} no definida, inicializando...`);
                        if (varName.includes('Data') || varName.includes('filtered')) {
                            this[varName] = [];
                        } else if (varName.includes('Form')) {
                            this[varName] = {};
                        } else if (typeof this[varName] === 'boolean') {
                            this[varName] = false;
                        } else if (typeof this[varName] === 'string') {
                            this[varName] = '';
                        } else {
                            this[varName] = null;
                        }
                    }
                });
            },
            
            // Inicializar arrays filtrados
            initializeFilteredArrays() {
                this.filteredCustomers = [];
                this.filteredSales = [];
                this.filteredPurchases = [];
                this.filteredProducts = [];
            },

            // Funciones para abrir modales con validación
            openCashModal() { 
                this.showOpenCashModal = true; 
                console.log('Modal abrir caja abierto');
            },
            openNewMovementModal() { 
                this.showNewMovementModal = true; 
                console.log('Modal nuevo movimiento abierto');
            },
            openCloseCashModal() { 
                this.closeCashForm.final_amount = {{ $currentBalance ?? 0 }}; 
                this.showCloseCashModal = true; 
                console.log('Modal cerrar caja abierto');
            },
            closeModal(modalName) { 
                if (this[modalName] !== undefined) {
                    this[modalName] = false; 
                    this.resetForms(); 
                    console.log(`Modal ${modalName} cerrado`);
                } else {
                    console.error(`Modal ${modalName} no encontrado`);
                }
            },
            
            // Reset de formularios con validación
            resetForms() {
                this.openCashForm = { initial_amount: 0.00, observations: '' };
                this.newMovementForm = { type: 'income', amount: '', description: '' };
                this.closeCashForm = { final_amount: {{ $currentBalance ?? 0 }}, observations: '' };
                this.isSubmitting = false;
                console.log('Formularios reseteados');
            },

            // Funciones de formularios con manejo de errores robusto
            async submitOpenCash() {
                if (this.isSubmitting) {
                    console.log('Ya se está procesando una solicitud');
                    return;
                }
                
                console.log('Enviando formulario abrir caja...');
                this.isSubmitting = true;
                
                try {
                    const response = await fetch('{{ route('admin.cash-counts.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.openCashForm)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.showAlert('¡Caja abierta exitosamente!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showAlert(data.message || 'Error al abrir la caja', 'error');
                    }
                } catch (error) {
                    console.error('Error en submitOpenCash:', error);
                    this.showAlert('Error de conexión. Intente nuevamente.', 'error');
                } finally {
                    this.isSubmitting = false;
                }
            },

            async submitNewMovement() {
                if (this.isSubmitting) {
                    console.log('Ya se está procesando una solicitud');
                    return;
                }
                
                console.log('Enviando formulario nuevo movimiento...');
                this.isSubmitting = true;
                
                try {
                    const response = await fetch('{{ route('admin.cash-counts.store-movement') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newMovementForm)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.showAlert('¡Movimiento registrado exitosamente!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showAlert(data.message || 'Error al registrar el movimiento', 'error');
                    }
                } catch (error) {
                    console.error('Error en submitNewMovement:', error);
                    this.showAlert('Error de conexión. Intente nuevamente.', 'error');
                } finally {
                    this.isSubmitting = false;
                }
            },

            async submitCloseCash() {
                if (this.isSubmitting) {
                    console.log('Ya se está procesando una solicitud');
                    return;
                }
                
                console.log('Enviando formulario cerrar caja...');
                this.isSubmitting = true;
                
                try {
                    const response = await fetch('{{ route('admin.cash-counts.close', $currentCashCount->id ?? 0) }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.closeCashForm)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.showAlert('¡Caja cerrada exitosamente!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showAlert(data.message || 'Error al cerrar la caja', 'error');
                    }
                } catch (error) {
                    console.error('Error en submitCloseCash:', error);
                    this.showAlert('Error de conexión. Intente nuevamente.', 'error');
                } finally {
                    this.isSubmitting = false;
                }
            },

            // Funciones de visualización con validación
            async viewMovements(cashCountId) {
                console.log('Abriendo modal de detalles para arqueo:', cashCountId);
                this.currentCashCountId = cashCountId;
                this.showDetailsModal = true;
                this.activeTab = 'customers';
                this.loading = true;
                
                try {
                    await this.loadTabData();
                } catch (error) {
                    console.error('Error al cargar datos:', error);
                    alert('Error al cargar los datos');
                } finally {
                    this.loading = false;
                }
            },

            async viewHistory(cashCountId) {
                console.log('Abriendo historial para arqueo:', cashCountId);
                try {
                    const response = await fetch(`/cash-counts/${cashCountId}/history`);
                    const data = await response.json();

                    if (data.success) {
                        this.showAlert('Funcionalidad en desarrollo', 'info');
                    } else {
                        this.showAlert(data.message || 'Error al cargar el historial', 'error');
                    }
                } catch (error) {
                    console.error('Error en viewHistory:', error);
                    this.showAlert('Error de conexión', 'error');
                }
            },

            async deleteCashCount(cashCountId) {
                console.log('Eliminando arqueo:', cashCountId);
                const result = await this.showConfirmAlert(
                    '¿Estás seguro?',
                    'Esta acción no se puede revertir. Se eliminará el arqueo de caja y todos sus movimientos.',
                    'warning'
                );

                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`/cash-counts/delete/${cashCountId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showAlert('¡Arqueo eliminado exitosamente!', 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            this.showAlert(data.message || 'Error al eliminar el arqueo', 'error');
                        }
                    } catch (error) {
                        console.error('Error en deleteCashCount:', error);
                        this.showAlert('Error de conexión', 'error');
                    }
                }
            },

            // Funciones del modal de detalles con validación robusta
            async loadTabData() {
                if (!this.currentCashCountId) {
                    console.warn('No hay ID de arqueo seleccionado');
                    return;
                }
                
                console.log('Cargando datos para pestaña:', this.activeTab);
                
                try {
                    const response = await fetch(`/cash-counts/${this.currentCashCountId}/${this.activeTab}`);
                    const data = await response.json();
                    
                    if (response.ok) {
                        this[`${this.activeTab}Data`] = data.data || [];
                        
                        // Inicializar variables filtradas de forma segura
                        this.initializeFilteredData();
                        this.applyFilters();
                        
                        console.log(`Datos cargados para ${this.activeTab}:`, this[`${this.activeTab}Data`].length, 'registros');
                    } else {
                        console.error('Error en respuesta:', data.message);
                        alert(data.message || 'Error al cargar los datos');
                    }
                } catch (error) {
                    console.error('Error en loadTabData:', error);
                    alert('Error de conexión');
                }
            },

            // Inicializar datos filtrados de forma segura
            initializeFilteredData() {
                if (this.activeTab === 'customers') {
                    this.filteredCustomers = [...(this.customersData || [])];
                } else if (this.activeTab === 'sales') {
                    this.filteredSales = [...(this.salesData || [])];
                } else if (this.activeTab === 'purchases') {
                    this.filteredPurchases = [...(this.purchasesData || [])];
                } else if (this.activeTab === 'products') {
                    this.filteredProducts = [...(this.productsData || [])];
                }
            },

            changeTab(tab) {
                console.log('Cambiando a pestaña:', tab);
                this.activeTab = tab;
                this.loadTabData();
            },

            applyFilters() {
                const data = this[`${this.activeTab}Data`] || [];
                let filtered = [...data];

                // Filtro de búsqueda
                if (this.searchTerm) {
                    const term = this.searchTerm.toLowerCase();
                    filtered = filtered.filter(item => {
                        if (this.activeTab === 'customers') {
                            return item.name?.toLowerCase().includes(term) || 
                                   item.phone?.toLowerCase().includes(term);
                        } else if (this.activeTab === 'sales') {
                            return item.customer_name?.toLowerCase().includes(term);
                        } else if (this.activeTab === 'purchases') {
                            return item.supplier_name?.toLowerCase().includes(term);
                        } else if (this.activeTab === 'products') {
                            return item.name?.toLowerCase().includes(term);
                        }
                        return true;
                    });
                }

                // Filtro de monto mínimo
                if (this.minAmount) {
                    filtered = filtered.filter(item => {
                        const amount = this.activeTab === 'customers' ? item.total_sales : 
                                     this.activeTab === 'products' ? item.total_profit : 
                                     item.amount;
                        return amount >= parseFloat(this.minAmount);
                    });
                }

                // Filtro de monto máximo
                if (this.maxAmount) {
                    filtered = filtered.filter(item => {
                        const amount = this.activeTab === 'customers' ? item.total_sales : 
                                     this.activeTab === 'products' ? item.total_profit : 
                                     item.amount;
                        return amount <= parseFloat(this.maxAmount);
                    });
                }

                // Filtro de fecha
                if (this.startDate) {
                    filtered = filtered.filter(item => {
                        const itemDate = new Date(item.date || item.created_at);
                        const startDate = new Date(this.startDate);
                        return itemDate >= startDate;
                    });
                }

                if (this.endDate) {
                    filtered = filtered.filter(item => {
                        const itemDate = new Date(item.date || item.created_at);
                        const endDate = new Date(this.endDate);
                        return itemDate <= endDate;
                    });
                }

                // Asignar el resultado filtrado de forma segura
                if (this.activeTab === 'customers') {
                    this.filteredCustomers = filtered;
                } else if (this.activeTab === 'sales') {
                    this.filteredSales = filtered;
                } else if (this.activeTab === 'purchases') {
                    this.filteredPurchases = filtered;
                } else if (this.activeTab === 'products') {
                    this.filteredProducts = filtered;
                }
                
                console.log(`Filtros aplicados para ${this.activeTab}:`, filtered.length, 'registros');
            },

            clearFilters() {
                this.searchTerm = '';
                this.minAmount = '';
                this.maxAmount = '';
                this.startDate = '';
                this.endDate = '';
                this.applyFilters();
                console.log('Filtros limpiados');
            },

            getTotalAmount(data, field) {
                if (!Array.isArray(data)) return 0;
                return data.reduce((sum, item) => sum + (parseFloat(item[field]) || 0), 0);
            },

            formatCurrency(amount) {
                if (amount === null || amount === undefined) return '$0.00';
                return new Intl.NumberFormat('es-AR', {
                    style: 'currency',
                    currency: 'ARS'
                }).format(amount);
            },

            // Funciones de utilidad con fallback
            showAlert(message, type = 'info') {
                console.log(`Alert [${type}]:`, message);
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
                console.log(`Confirm Alert [${icon}]:`, title, text);
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
            },

            // Inicialización de gráficos con validación
            initializeCharts() {
                if (typeof Chart === 'undefined') {
                    console.warn('Chart.js no está disponible');
                    return;
                }

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
        };
    }
    
    // Registrar la función globalmente solo si Alpine.js está disponible
    if (typeof Alpine !== 'undefined') {
        window.cashManagement = cashManagement;
        console.log('cashManagement registrado globalmente');
    } else {
        console.error('Alpine.js no está disponible, no se puede registrar cashManagement');
    }
})();
</script>
@endpush
