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
                <div class="mt-6 lg:mt-0 hero-buttons">
                    @if ($currentCashCount)
                        @can('cash-counts.store-movement')
                            <a href="{{ route('admin.cash-counts.create-movement') }}" 
                               class="inline-flex items-center justify-center px-4 sm:px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30 min-w-[120px] sm:min-w-[140px]">
                                <i class="fas fa-money-bill-wave mr-1 sm:mr-2"></i>
                                <span class="text-xs sm:text-sm">Nuevo Movimiento</span>
                            </a>
                        @endcan
                        @can('cash-counts.close')
                            <a href="{{ route('admin.cash-counts.close', $currentCashCount->id) }}" 
                               class="inline-flex items-center justify-center px-4 sm:px-6 py-3 bg-red-500 bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-red-300 border-opacity-30 min-w-[120px] sm:min-w-[140px]">
                                <i class="fas fa-cash-register mr-1 sm:mr-2"></i>
                                <span class="text-xs sm:text-sm">Cerrar Caja</span>
                            </a>
                        @endcan
                    @else
                        @can('cash-counts.report')
                            <a href="{{ route('admin.cash-counts.report') }}" 
                               class="inline-flex items-center justify-center px-4 sm:px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30 min-w-[120px] sm:min-w-[140px]"
                               target="_blank">
                                <i class="fas fa-file-pdf mr-1 sm:mr-2"></i>
                                <span class="text-xs sm:text-sm">Reporte</span>
                            </a>
                        @endcan
                        @can('cash-counts.create')
                            <a href="{{ route('admin.cash-counts.create') }}" 
                               class="inline-flex items-center justify-center px-4 sm:px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30 min-w-[120px] sm:min-w-[140px]">
                                <i class="fas fa-cash-register mr-1 sm:mr-2"></i>
                                <span class="text-xs sm:text-sm">Abrir Caja</span>
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
    <div x-data="dataTable()" class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-6 border-b border-gray-200">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-list text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Historial de Arqueos</h2>
                        <p class="text-gray-600">Registro de todos los arqueos de caja realizados</p>
                    </div>
                </div>
                
                <!-- Controles de Vista -->
                <div class="flex items-center space-x-2">
                    <!-- Botones de cambio de vista (solo en desktop/tablet) -->
                    <div class="hidden md:flex items-center bg-gray-100 rounded-lg p-1">
                        <button @click="viewMode = 'table'"
                                :class="viewMode === 'table' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                                class="px-3 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-table"></i>
                            <span>Tabla</span>
                        </button>
                        <button @click="viewMode = 'cards'"
                                :class="viewMode === 'cards' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                                class="px-3 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-th-large"></i>
                            <span>Tarjetas</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista de Tabla - Desktop/Tablet -->
        <div x-show="viewMode === 'table'" class="hidden md:block">
            <div class="overflow-x-auto">
                <table class="w-full modern-table">
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
        </div>

        <!-- Vista de Tarjetas - Móvil y Desktop (cuando se selecciona) -->
        <div x-show="viewMode === 'cards'" class="md:block" :class="{ 'block': true, 'hidden': false }">
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($cashCounts as $cashCount)
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-l-4 {{ $cashCount->closing_date ? 'border-green-500' : 'border-yellow-500' }} card-hover">
                        
                        <!-- Header de la Tarjeta -->
                        <div class="p-6 pb-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                        <i class="fas fa-cash-register"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            Arqueo #{{ str_pad($cashCount->id, 4, '0', STR_PAD_LEFT) }}
                                        </h3>
                                        <div class="flex items-center space-x-1 text-sm text-gray-500 mt-1">
                                            <i class="fas fa-calendar text-xs"></i>
                                            <span>{{ \Carbon\Carbon::parse($cashCount->opening_date)->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Estado -->
                                <div class="flex-shrink-0">
                                    @if ($cashCount->closing_date)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Cerrado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Abierto
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información Principal -->
                        <div class="px-6 pb-4">
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Fecha Apertura -->
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-calendar-plus"></i>
                                        <span>Apertura</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($cashCount->opening_date)->format('d/m/Y') }}</p>
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($cashCount->opening_date)->format('H:i') }}</p>
                                    </div>
                                </div>

                                <!-- Fecha Cierre -->
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>Cierre</span>
                                    </div>
                                    @if ($cashCount->closing_date)
                                        <div class="flex flex-col">
                                            <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($cashCount->closing_date)->format('d/m/Y') }}</p>
                                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($cashCount->closing_date)->format('H:i') }}</p>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                            En curso
                                        </span>
                                    @endif
                                </div>

                                <!-- Monto Inicial -->
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>Inicial</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $currency->symbol }} {{ number_format($cashCount->initial_amount, 2) }}
                                    </span>
                                </div>

                                <!-- Monto Final -->
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>Final</span>
                                    </div>
                                    @if ($cashCount->final_amount)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-800">
                                            {{ $currency->symbol }} {{ number_format($cashCount->final_amount, 2) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                            Pendiente
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Diferencia -->
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Diferencia</span>
                                    </div>
                                    @if ($cashCount->final_amount)
                                        @php
                                            $difference = $cashCount->final_amount - $cashCount->initial_amount;
                                            $badgeClass = $difference >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ $badgeClass }}">
                                            {{ $currency->symbol }} {{ number_format(abs($difference), 2) }}
                                            <i class="fas fa-{{ $difference >= 0 ? 'arrow-up' : 'arrow-down' }} ml-1"></i>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                            Pendiente
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="px-6 pb-6">
                            <div class="flex items-center justify-center space-x-2">
                                @can('cash-counts.show')
                                    <button @click="window.openCashCountModal({{ $cashCount->id }})"
                                            class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-600 py-2 px-3 rounded-lg flex items-center justify-center transition-all duration-200 text-sm font-medium"
                                            title="Ver movimientos">
                                        <i class="fas fa-eye mr-2"></i>
                                    </button>
                                @endcan
                                
                                @if (!$cashCount->closing_date)
                                    @can('cash-counts.edit')
                                        <a href="{{ route('admin.cash-counts.edit', $cashCount->id) }}"
                                           class="flex-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-600 py-2 px-3 rounded-lg flex items-center justify-center transition-all duration-200 text-sm font-medium"
                                           title="Editar">
                                            <i class="fas fa-edit mr-2"></i>
                                        </a>
                                    @endcan
                                @endif
                                
                                @can('cash-counts.destroy')
                                    <form action="{{ route('admin.cash-counts.destroy', $cashCount->id) }}" method="POST" class="flex-1" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este arqueo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-full bg-red-100 hover:bg-red-200 text-red-600 py-2 px-3 rounded-lg flex items-center justify-center transition-all duration-200 text-sm font-medium"
                                                title="Eliminar">
                                            <i class="fas fa-trash mr-2"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-cash-register text-gray-400 text-2xl"></i>
                            </div>
                            <div class="text-center mt-4">
                                <h3 class="text-lg font-semibold text-gray-900">No hay arqueos de caja registrados</h3>
                                <p class="text-gray-600">Comienza creando tu primer arqueo de caja</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
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
        <div class="flex min-h-full items-center justify-center p-2 sm:p-4">
            <div x-show="isOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="relative w-full max-w-7xl mx-auto bg-white rounded-lg sm:rounded-xl md:rounded-2xl shadow-2xl overflow-hidden"
                 @click.away="closeModal()">
                
                <!-- Header del Modal -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-3 sm:px-4 md:px-6 py-4 sm:py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 sm:space-x-4">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg sm:rounded-xl flex items-center justify-center">
                                <i class="fas fa-cash-register text-white text-lg sm:text-xl md:text-2xl"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-white truncate">Detalles del Arqueo de Caja</h2>
                                <p class="text-blue-100 text-sm sm:text-base truncate" x-text="cashCountData ? 'Desde ' + formatDate(cashCountData.opening_date) + ' hasta ' + (cashCountData.closing_date ? formatDate(cashCountData.closing_date) : 'actualidad') : ''"></p>
                            </div>
                        </div>
                        <button @click="closeModal()" 
                                class="w-8 h-8 sm:w-10 sm:h-10 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg sm:rounded-xl flex items-center justify-center transition-all duration-200 ml-2 sm:ml-4">
                            <i class="fas fa-times text-sm sm:text-base"></i>
                        </button>
                    </div>
                </div>

                <!-- Loading State -->


                <!-- Error State -->
                <div x-show="!cashCountData" class="p-6 sm:p-8 md:p-12 text-center">
                    <div class="flex flex-col items-center space-y-3 sm:space-y-4">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600 text-lg sm:text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900">Error al cargar datos</h3>
                            <p class="text-gray-600 text-sm sm:text-base">No se pudieron cargar los datos del arqueo de caja</p>
                        </div>
                        <button @click="closeModal()" 
                                class="px-3 py-2 sm:px-4 sm:py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
                            Cerrar
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div x-show="cashCountData && Object.keys(cashCountData).length > 0" class="p-3 sm:p-4 md:p-6">
                    <!-- Sistema de Pestañas -->
                    <div class="space-y-4 sm:space-y-6">
                        <!-- Navegación de Pestañas -->
                        <div class="border-b border-gray-200 overflow-x-auto">
                            <nav class="flex space-x-2 sm:space-x-4 md:space-x-8 min-w-max" aria-label="Tabs">
                                <!-- Pestaña Clientes -->
                                <button @click="activeTab = 'clientes'" 
                                        :class="activeTab === 'clientes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-2 sm:px-3 md:px-4 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <i class="fas fa-users text-xs sm:text-sm"></i>
                                        <span class="hidden xs:inline">Clientes</span>
                                        <span class="xs:hidden">Cli</span>
                                    </div>
                                </button>

                                <!-- Pestaña Ventas -->
                                <button @click="activeTab = 'ventas'" 
                                        :class="activeTab === 'ventas' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-2 sm:px-3 md:px-4 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <i class="fas fa-chart-bar text-xs sm:text-sm"></i>
                                        <span class="hidden xs:inline">Ventas</span>
                                        <span class="xs:hidden">Ven</span>
                                    </div>
                                </button>

                                <!-- Pestaña Pagos -->
                                <button @click="activeTab = 'pagos'" 
                                        :class="activeTab === 'pagos' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-2 sm:px-3 md:px-4 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <i class="fas fa-credit-card text-xs sm:text-sm"></i>
                                        <span class="hidden xs:inline">Pagos</span>
                                        <span class="xs:hidden">Pag</span>
                                    </div>
                                </button>

                                <!-- Pestaña Compras -->
                                <button @click="activeTab = 'compras'" 
                                        :class="activeTab === 'compras' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-2 sm:px-3 md:px-4 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <i class="fas fa-shopping-bag text-xs sm:text-sm"></i>
                                        <span class="hidden xs:inline">Compras</span>
                                        <span class="xs:hidden">Com</span>
                                    </div>
                                </button>

                                <!-- Pestaña Productos -->
                                <button @click="activeTab = 'productos'" 
                                        :class="activeTab === 'productos' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-2 sm:px-3 md:px-4 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <i class="fas fa-box text-xs sm:text-sm"></i>
                                        <span class="hidden xs:inline">Productos</span>
                                        <span class="xs:hidden">Pro</span>
                                    </div>
                                </button>

                                <!-- Pestaña Pedidos -->
                                <button @click="activeTab = 'pedidos'" 
                                        :class="activeTab === 'pedidos' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap py-2 px-2 sm:px-3 md:px-4 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <i class="fas fa-clipboard-list text-xs sm:text-sm"></i>
                                        <span class="hidden xs:inline">Pedidos</span>
                                        <span class="xs:hidden">Ped</span>
                                    </div>
                                </button>
                            </nav>
                        </div>

                        <!-- Contenido de Pestañas -->
                        <div class="space-y-6">
                            <!-- Pestaña Clientes -->
                            <div x-show="activeTab === 'clientes'" class="space-y-4 sm:space-y-6">
                                <!-- Header de la Pestaña -->
                                <div class="flex items-center space-x-2 sm:space-x-4 mb-4 sm:mb-6">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg sm:rounded-xl flex items-center justify-center">
                                        <i class="fas fa-users text-white text-sm sm:text-lg md:text-xl"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">Información por Clientes</h3>
                                        <p class="text-gray-600 text-sm sm:text-base">Análisis detallado de la actividad de clientes en este arqueo</p>
                                    </div>
                                </div>

                                <!-- 4 Widgets de Clientes con datos reales -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                                    <!-- Widget 1: Total Clientes -->
                                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-user-friends text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="cashCountData && cashCountData.customer_stats ? cashCountData.customer_stats.current.unique_customers : 0">0</div>
                                                <div class="text-blue-100 text-xs sm:text-sm">Total Clientes</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.customer_stats && cashCountData.customer_stats.comparison.unique_customers ? 
                                                    (cashCountData.customer_stats.comparison.unique_customers.is_positive ? 'text-green-200' : 'text-red-200') : 'text-blue-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.customer_stats && cashCountData.customer_stats.comparison.unique_customers ? 
                                                              (cashCountData.customer_stats.comparison.unique_customers.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.customer_stats && cashCountData.customer_stats.comparison.unique_customers ? 
                                                        (cashCountData.customer_stats.comparison.unique_customers.is_positive ? '+' : '') + cashCountData.customer_stats.comparison.unique_customers.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 2: Ventas Totales -->
                                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-shopping-cart text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.customer_stats ? cashCountData.customer_stats.current.total_sales : 0)">$0.00</div>
                                                <div class="text-green-100 text-xs sm:text-sm">Ventas Totales</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.customer_stats && cashCountData.customer_stats.comparison.total_sales ? 
                                                    (cashCountData.customer_stats.comparison.total_sales.is_positive ? 'text-green-200' : 'text-red-200') : 'text-green-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.customer_stats && cashCountData.customer_stats.comparison.total_sales ? 
                                                              (cashCountData.customer_stats.comparison.total_sales.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.customer_stats && cashCountData.customer_stats.comparison.total_sales ? 
                                                        (cashCountData.customer_stats.comparison.total_sales.is_positive ? '+' : '') + cashCountData.customer_stats.comparison.total_sales.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 3: Deudas Pendientes -->
                                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-exclamation-triangle text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.customer_stats ? cashCountData.customer_stats.current.total_debt : 0)">$0.00</div>
                                                <div class="text-red-100 text-xs sm:text-sm">Deudas Pendientes</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.customer_stats && cashCountData.customer_stats.comparison.total_debt ? 
                                                    (cashCountData.customer_stats.comparison.total_debt.is_positive ? 'text-red-200' : 'text-green-200') : 'text-red-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.customer_stats && cashCountData.customer_stats.comparison.total_debt ? 
                                                              (cashCountData.customer_stats.comparison.total_debt.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.customer_stats && cashCountData.customer_stats.comparison.total_debt ? 
                                                        (cashCountData.customer_stats.comparison.total_debt.is_positive ? '+' : '') + cashCountData.customer_stats.comparison.total_debt.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 4: Promedio por Cliente -->
                                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-chart-line text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.customer_stats ? cashCountData.customer_stats.current.average_per_customer : 0)">$0.00</div>
                                                <div class="text-purple-100 text-xs sm:text-sm">Promedio por Cliente</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.customer_stats && cashCountData.customer_stats.comparison.average_per_customer ? 
                                                    (cashCountData.customer_stats.comparison.average_per_customer.is_positive ? 'text-purple-200' : 'text-red-200') : 'text-purple-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.customer_stats && cashCountData.customer_stats.comparison.average_per_customer ? 
                                                              (cashCountData.customer_stats.comparison.average_per_customer.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.customer_stats && cashCountData.customer_stats.comparison.average_per_customer ? 
                                                        (cashCountData.customer_stats.comparison.average_per_customer.is_positive ? '+' : '') + cashCountData.customer_stats.comparison.average_per_customer.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabla de Clientes -->
                                <div class="bg-white rounded-lg sm:rounded-xl shadow-lg overflow-hidden">
                                    <div class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                                        <h4 class="text-base sm:text-lg font-semibold text-gray-900">Clientes del Arqueo</h4>
                                        <p class="text-xs sm:text-sm text-gray-600">Detalle de clientes que realizaron compras durante este período</p>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Compras</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deuda Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <template x-for="customer in (cashCountData && cashCountData.customer_stats ? cashCountData.customer_stats.current.customers_data : [])" :key="customer.name">
                                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-gray-900" x-text="customer.name"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm text-gray-500" x-text="customer.phone"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-green-600" x-text="formatCurrency(customer.total_purchases)"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium" :class="customer.total_debt > 0 ? 'text-red-600' : 'text-green-600'" x-text="formatCurrency(customer.total_debt)"></div>
                                                        </td>
                                                    </tr>
                                                </template>
                                                <!-- Estado vacío -->
                                                <tr x-show="!cashCountData || !cashCountData.customer_stats || !cashCountData.customer_stats.current.customers_data || cashCountData.customer_stats.current.customers_data.length === 0">
                                                    <td colspan="4" class="px-3 sm:px-4 md:px-6 py-6 sm:py-8 text-center">
                                                        <div class="flex flex-col items-center space-y-2 sm:space-y-3">
                                                            <i class="fas fa-users text-gray-400 text-2xl sm:text-3xl"></i>
                                                            <div class="text-gray-500">
                                                                <p class="font-medium text-sm sm:text-base">No hay clientes registrados</p>
                                                                <p class="text-xs sm:text-sm">No se encontraron clientes con compras en este arqueo</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Ventas -->
                            <div x-show="activeTab === 'ventas'" class="space-y-4 sm:space-y-6">
                                <!-- Header de la Pestaña -->
                                <div class="flex items-center space-x-2 sm:space-x-4 mb-4 sm:mb-6">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg sm:rounded-xl flex items-center justify-center">
                                        <i class="fas fa-chart-bar text-white text-sm sm:text-lg md:text-xl"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">Análisis de Ventas</h3>
                                        <p class="text-gray-600 text-sm sm:text-base">Estadísticas detalladas de ventas en este arqueo de caja</p>
                                    </div>
                                </div>

                                <!-- 4 Widgets de Ventas con datos reales -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                                    <!-- Widget 1: Ventas Totales -->
                                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-dollar-sign text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.sales_stats ? cashCountData.sales_stats.current.total_sales : 0)">$0.00</div>
                                                <div class="text-green-100 text-xs sm:text-sm">Ventas Totales</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.sales_stats && cashCountData.sales_stats.comparison.total_sales ? 
                                                    (cashCountData.sales_stats.comparison.total_sales.is_positive ? 'text-green-200' : 'text-red-200') : 'text-green-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.sales_stats && cashCountData.sales_stats.comparison.total_sales ? 
                                                              (cashCountData.sales_stats.comparison.total_sales.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.sales_stats && cashCountData.sales_stats.comparison.total_sales ? 
                                                        (cashCountData.sales_stats.comparison.total_sales.is_positive ? '+' : '') + cashCountData.sales_stats.comparison.total_sales.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 2: Balance Teórico -->
                                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-calculator text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.sales_stats ? cashCountData.sales_stats.current.theoretical_balance : 0)">$0.00</div>
                                                <div class="text-blue-100 text-xs sm:text-sm">Balance Teórico</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.sales_stats && cashCountData.sales_stats.comparison.theoretical_balance ? 
                                                    (cashCountData.sales_stats.comparison.theoretical_balance.is_positive ? 'text-blue-200' : 'text-red-200') : 'text-blue-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.sales_stats && cashCountData.sales_stats.comparison.theoretical_balance ? 
                                                              (cashCountData.sales_stats.comparison.theoretical_balance.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.sales_stats && cashCountData.sales_stats.comparison.theoretical_balance ? 
                                                        (cashCountData.sales_stats.comparison.theoretical_balance.is_positive ? '+' : '') + cashCountData.sales_stats.comparison.theoretical_balance.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 3: Promedio por Venta -->
                                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-chart-line text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.sales_stats ? cashCountData.sales_stats.current.average_per_sale : 0)">$0.00</div>
                                                <div class="text-yellow-100 text-xs sm:text-sm">Promedio por Venta</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.sales_stats && cashCountData.sales_stats.comparison.average_per_sale ? 
                                                    (cashCountData.sales_stats.comparison.average_per_sale.is_positive ? 'text-yellow-200' : 'text-red-200') : 'text-yellow-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.sales_stats && cashCountData.sales_stats.comparison.average_per_sale ? 
                                                              (cashCountData.sales_stats.comparison.average_per_sale.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.sales_stats && cashCountData.sales_stats.comparison.average_per_sale ? 
                                                        (cashCountData.sales_stats.comparison.average_per_sale.is_positive ? '+' : '') + cashCountData.sales_stats.comparison.average_per_sale.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Widget 4: Balance Real -->
                                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-coins text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.sales_stats ? cashCountData.sales_stats.current.real_balance : 0)">$0.00</div>
                                                <div class="text-purple-100 text-xs sm:text-sm">Balance Real</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.sales_stats && cashCountData.sales_stats.comparison.real_balance ? 
                                                    (cashCountData.sales_stats.comparison.real_balance.is_positive ? 'text-purple-200' : 'text-red-200') : 'text-purple-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.sales_stats && cashCountData.sales_stats.comparison.real_balance ? 
                                                              (cashCountData.sales_stats.comparison.real_balance.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.sales_stats && cashCountData.sales_stats.comparison.real_balance ? 
                                                        (cashCountData.sales_stats.comparison.real_balance.is_positive ? '+' : '') + cashCountData.sales_stats.comparison.real_balance.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabla de Ventas -->
                                <div class="bg-white rounded-lg sm:rounded-xl shadow-lg overflow-hidden">
                                    <div class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 bg-gradient-to-r from-blue-50 to-purple-50 border-b border-gray-200">
                                        <h4 class="text-base sm:text-lg font-semibold text-gray-900">Ventas del Arqueo</h4>
                                        <p class="text-xs sm:text-sm text-gray-600">Detalle de todas las ventas realizadas durante este período</p>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <template x-for="sale in (cashCountData && cashCountData.sales_stats ? cashCountData.sales_stats.current.sales_data : [])" :key="sale.invoice_number">
                                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-gray-900" x-text="sale.invoice_number"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm text-gray-500" x-text="formatDate(sale.sale_date)"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-gray-900" x-text="sale.customer_name"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-green-600" x-text="formatCurrency(sale.total_amount)"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" 
                                                                  :class="sale.payment_status === 'Pagado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                                  x-text="sale.payment_status"></span>
                                                        </td>
                                                    </tr>
                                                </template>
                                                <!-- Estado vacío -->
                                                <tr x-show="!cashCountData || !cashCountData.sales_stats || !cashCountData.sales_stats.current.sales_data || cashCountData.sales_stats.current.sales_data.length === 0">
                                                    <td colspan="5" class="px-3 sm:px-4 md:px-6 py-6 sm:py-8 text-center">
                                                        <div class="flex flex-col items-center space-y-2 sm:space-y-3">
                                                            <i class="fas fa-shopping-cart text-gray-400 text-2xl sm:text-3xl"></i>
                                                            <div class="text-gray-500">
                                                                <p class="font-medium text-sm sm:text-base">No hay ventas registradas</p>
                                                                <p class="text-xs sm:text-sm">No se encontraron ventas en este arqueo</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Pagos -->
                            <div x-show="activeTab === 'pagos'" class="space-y-4 sm:space-y-6">
                                <!-- Header de la Pestaña -->
                                <div class="flex items-center space-x-2 sm:space-x-4 mb-4 sm:mb-6">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-lg sm:rounded-xl flex items-center justify-center">
                                        <i class="fas fa-credit-card text-white text-sm sm:text-lg md:text-xl"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">Análisis de Pagos</h3>
                                        <p class="text-gray-600 text-sm sm:text-base">Estadísticas detalladas de pagos en este arqueo de caja</p>
                                    </div>
                                </div>

                                <!-- 4 Widgets de Pagos con datos reales -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                                    <!-- Total Pagos Recibidos -->
                                    <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-coins text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.payments_stats ? cashCountData.payments_stats.current.total_payments : 0)">$0.00</div>
                                                <div class="text-teal-100 text-xs sm:text-sm">Pagos Totales</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.payments_stats && cashCountData.payments_stats.comparison.total_payments ? 
                                                    (cashCountData.payments_stats.comparison.total_payments.is_positive ? 'text-teal-200' : 'text-red-200') : 'text-teal-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.payments_stats && cashCountData.payments_stats.comparison.total_payments ? 
                                                              (cashCountData.payments_stats.comparison.total_payments.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.payments_stats && cashCountData.payments_stats.comparison.total_payments ? 
                                                        (cashCountData.payments_stats.comparison.total_payments.is_positive ? '+' : '') + cashCountData.payments_stats.comparison.total_payments.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Cantidad de Pagos -->
                                    <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-money-bill-wave text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="cashCountData && cashCountData.payments_stats ? cashCountData.payments_stats.current.payments_count : 0">0</div>
                                                <div class="text-cyan-100 text-xs sm:text-sm">Transacciones</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.payments_stats && cashCountData.payments_stats.comparison.payments_count ? 
                                                    (cashCountData.payments_stats.comparison.payments_count.is_positive ? 'text-cyan-200' : 'text-red-200') : 'text-cyan-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.payments_stats && cashCountData.payments_stats.comparison.payments_count ? 
                                                              (cashCountData.payments_stats.comparison.payments_count.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.payments_stats && cashCountData.payments_stats.comparison.payments_count ? 
                                                        (cashCountData.payments_stats.comparison.payments_count.is_positive ? '+' : '') + cashCountData.payments_stats.comparison.payments_count.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Promedio por Pago -->
                                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-chart-line text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.payments_stats ? cashCountData.payments_stats.current.average_per_payment : 0)">$0.00</div>
                                                <div class="text-emerald-100 text-xs sm:text-sm">Promedio por Pago</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.payments_stats && cashCountData.payments_stats.comparison.average_per_payment ? 
                                                    (cashCountData.payments_stats.comparison.average_per_payment.is_positive ? 'text-emerald-200' : 'text-red-200') : 'text-emerald-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.payments_stats && cashCountData.payments_stats.comparison.average_per_payment ? 
                                                              (cashCountData.payments_stats.comparison.average_per_payment.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.payments_stats && cashCountData.payments_stats.comparison.average_per_payment ? 
                                                        (cashCountData.payments_stats.comparison.average_per_payment.is_positive ? '+' : '') + cashCountData.payments_stats.comparison.average_per_payment.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Deuda Restante -->
                                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-balance-scale text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.payments_stats ? cashCountData.payments_stats.current.remaining_debt : 0)">$0.00</div>
                                                <div class="text-blue-100 text-xs sm:text-sm">Deuda Restante</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.payments_stats && cashCountData.payments_stats.comparison.remaining_debt ? 
                                                    (cashCountData.payments_stats.comparison.remaining_debt.is_positive ? 'text-blue-200' : 'text-green-200') : 'text-blue-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.payments_stats && cashCountData.payments_stats.comparison.remaining_debt ? 
                                                              (cashCountData.payments_stats.comparison.remaining_debt.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.payments_stats && cashCountData.payments_stats.comparison.remaining_debt ? 
                                                        (cashCountData.payments_stats.comparison.remaining_debt.is_positive ? '+' : '') + cashCountData.payments_stats.comparison.remaining_debt.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabla de Pagos -->
                                <div class="bg-white rounded-lg sm:rounded-xl shadow-lg overflow-hidden">
                                    <div class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 bg-gradient-to-r from-teal-50 to-cyan-50 border-b border-gray-200">
                                        <h4 class="text-base sm:text-lg font-semibold text-gray-900">Pagos del Arqueo</h4>
                                        <p class="text-xs sm:text-sm text-gray-600">Detalle de pagos realizados durante este período</p>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deuda Restante</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nota</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <template x-for="payment in (cashCountData && cashCountData.payments_stats ? cashCountData.payments_stats.current.payments_data : [])" :key="payment.id">
                                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm text-gray-500" x-text="formatDateTime(payment.payment_date)"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-gray-900" x-text="payment.customer_name"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-green-600" x-text="formatCurrency(payment.payment_amount)"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium" :class="payment.remaining_debt > 0 ? 'text-red-600' : 'text-green-600'" x-text="formatCurrency(payment.remaining_debt)"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4">
                                                            <div class="text-xs sm:text-sm text-gray-600" x-text="payment.notes || '-' "></div>
                                                        </td>
                                                    </tr>
                                                </template>
                                                <!-- Estado vacío -->
                                                <tr x-show="!cashCountData || !cashCountData.payments_stats || !cashCountData.payments_stats.current.payments_data || cashCountData.payments_stats.current.payments_data.length === 0">
                                                    <td colspan="5" class="px-3 sm:px-4 md:px-6 py-6 sm:py-8 text-center">
                                                        <div class="flex flex-col items-center space-y-2 sm:space-y-3">
                                                            <i class="fas fa-receipt text-gray-400 text-2xl sm:text-3xl"></i>
                                                            <div class="text-gray-500">
                                                                <p class="font-medium text-sm sm:text-base">No hay pagos registrados</p>
                                                                <p class="text-xs sm:text-sm">No se encontraron pagos en este arqueo</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Compras -->
                            <div x-show="activeTab === 'compras'" class="space-y-4 sm:space-y-6">
                                <!-- Header de la Pestaña -->
                                <div class="flex items-center space-x-2 sm:space-x-4 mb-4 sm:mb-6">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg sm:rounded-xl flex items-center justify-center">
                                        <i class="fas fa-shopping-bag text-white text-sm sm:text-lg md:text-xl"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">Análisis de Compras</h3>
                                        <p class="text-gray-600 text-sm sm:text-base">Estadísticas de compras en este arqueo</p>
                                    </div>
                                </div>

                                <!-- 4 Widgets de Compras con datos reales -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                                    <!-- Compras Totales -->
                                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-shopping-cart text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.purchases_stats ? cashCountData.purchases_stats.current.total_purchases : 0)">$0.00</div>
                                                <div class="text-orange-100 text-xs sm:text-sm">Compras Totales</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.purchases_stats && cashCountData.purchases_stats.comparison.total_purchases ? 
                                                    (cashCountData.purchases_stats.comparison.total_purchases.is_positive ? 'text-orange-200' : 'text-red-200') : 'text-orange-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.purchases_stats && cashCountData.purchases_stats.comparison.total_purchases ? 
                                                              (cashCountData.purchases_stats.comparison.total_purchases.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.purchases_stats && cashCountData.purchases_stats.comparison.total_purchases ? 
                                                        (cashCountData.purchases_stats.comparison.total_purchases.is_positive ? '+' : '') + cashCountData.purchases_stats.comparison.total_purchases.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Cantidad de Compras -->
                                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-receipt text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="cashCountData && cashCountData.purchases_stats ? cashCountData.purchases_stats.current.purchases_count : 0">0</div>
                                                <div class="text-indigo-100 text-xs sm:text-sm">Cantidad de Compras</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.purchases_stats && cashCountData.purchases_stats.comparison.purchases_count ? 
                                                    (cashCountData.purchases_stats.comparison.purchases_count.is_positive ? 'text-indigo-200' : 'text-red-200') : 'text-indigo-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.purchases_stats && cashCountData.purchases_stats.comparison.purchases_count ? 
                                                              (cashCountData.purchases_stats.comparison.purchases_count.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.purchases_stats && cashCountData.purchases_stats.comparison.purchases_count ? 
                                                        (cashCountData.purchases_stats.comparison.purchases_count.is_positive ? '+' : '') + cashCountData.purchases_stats.comparison.purchases_count.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Promedio por Compra -->
                                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-chart-line text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.purchases_stats ? cashCountData.purchases_stats.current.average_per_purchase : 0)">$0.00</div>
                                                <div class="text-yellow-100 text-xs sm:text-sm">Promedio por Compra</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.purchases_stats && cashCountData.purchases_stats.comparison.average_per_purchase ? 
                                                    (cashCountData.purchases_stats.comparison.average_per_purchase.is_positive ? 'text-yellow-200' : 'text-red-200') : 'text-yellow-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.purchases_stats && cashCountData.purchases_stats.comparison.average_per_purchase ? 
                                                              (cashCountData.purchases_stats.comparison.average_per_purchase.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.purchases_stats && cashCountData.purchases_stats.comparison.average_per_purchase ? 
                                                        (cashCountData.purchases_stats.comparison.average_per_purchase.is_positive ? '+' : '') + cashCountData.purchases_stats.comparison.average_per_purchase.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>

                                    <!-- Margen (%) Ventas vs Compras de productos adquiridos -->
                                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-percentage text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="(cashCountData && cashCountData.purchases_stats ? cashCountData.purchases_stats.current.margin_percentage : 0) + '%'">0%</div>
                                                <div class="text-emerald-100 text-xs sm:text-sm">Margen (%)</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm" 
                                             :class="cashCountData && cashCountData.purchases_stats && cashCountData.purchases_stats.comparison.margin_percentage ? 
                                                    (cashCountData.purchases_stats.comparison.margin_percentage.is_positive ? 'text-emerald-200' : 'text-red-200') : 'text-emerald-100'">
                                            <i class="mr-1" :class="cashCountData && cashCountData.purchases_stats && cashCountData.purchases_stats.comparison.margin_percentage ? 
                                                              (cashCountData.purchases_stats.comparison.margin_percentage.is_positive ? 'fas fa-arrow-up' : 'fas fa-arrow-down') : 'fas fa-minus'"></i>
                                            <span x-text="cashCountData && cashCountData.purchases_stats && cashCountData.purchases_stats.comparison.margin_percentage ? 
                                                        (cashCountData.purchases_stats.comparison.margin_percentage.is_positive ? '+' : '') + cashCountData.purchases_stats.comparison.margin_percentage.percentage + '%' : '0%'">0%</span>
                                            <span class="ml-1">vs anterior</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabla de Compras -->
                                <div class="bg-white rounded-lg sm:rounded-xl shadow-lg overflow-hidden">
                                    <div class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 bg-gradient-to-r from-orange-50 to-red-50 border-b border-gray-200">
                                        <h4 class="text-base sm:text-lg font-semibold text-gray-900">Compras del Arqueo</h4>
                                        <p class="text-xs sm:text-sm text-gray-600">Fecha, productos únicos, totales y monto por compra</p>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Únicos</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Totales</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <template x-for="row in (cashCountData && cashCountData.purchases_stats ? cashCountData.purchases_stats.current.purchases_data : [])" :key="row.id">
                                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm text-gray-500" x-text="formatDate(row.purchase_date)"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-gray-900" x-text="row.unique_products"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-gray-900" x-text="row.total_products"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-blue-600" x-text="formatCurrency(row.total_amount)"></div>
                                                        </td>
                                                    </tr>
                                                </template>
                                                <!-- Estado vacío -->
                                                <tr x-show="!cashCountData || !cashCountData.purchases_stats || !cashCountData.purchases_stats.current.purchases_data || cashCountData.purchases_stats.current.purchases_data.length === 0">
                                                    <td colspan="4" class="px-3 sm:px-4 md:px-6 py-6 sm:py-8 text-center">
                                                        <div class="flex flex-col items-center space-y-2 sm:space-y-3">
                                                            <i class="fas fa-boxes text-gray-400 text-2xl sm:text-3xl"></i>
                                                            <div class="text-gray-500">
                                                                <p class="font-medium text-sm sm:text-base">No hay compras registradas</p>
                                                                <p class="text-xs sm:text-sm">No se encontraron compras en este arqueo</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Productos -->
                            <div x-show="activeTab === 'productos'" class="space-y-4 sm:space-y-6">
                                <!-- Header de la Pestaña -->
                                <div class="flex items-center space-x-2 sm:space-x-4 mb-4 sm:mb-6">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg sm:rounded-xl flex items-center justify-center">
                                        <i class="fas fa-box text-white text-sm sm:text-lg md:text-xl"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">Análisis de Productos</h3>
                                        <p class="text-gray-600 text-sm sm:text-base">Solo productos vendidos durante este arqueo</p>
                                    </div>
                                </div>

                                <!-- 2 Widgets reales -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 md:gap-6">
                                    <!-- Total de productos vendidos (totales y únicos) -->
                                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-boxes text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm sm:text-base text-purple-100">Vendidos (totales / únicos)</div>
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="(cashCountData && cashCountData.products_stats ? cashCountData.products_stats.current.total_quantity_sold : 0) + ' / ' + (cashCountData && cashCountData.products_stats ? cashCountData.products_stats.current.unique_products_sold : 0)">0 / 0</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Valor de inventario (stock * costo) -->
                                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 text-white shadow-lg">
                                        <div class="flex items-center justify-between mb-2 sm:mb-4">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-dollar-sign text-white text-sm sm:text-lg md:text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm sm:text-base text-blue-100">Valor Inventario (costo)</div>
                                                <div class="text-xl sm:text-2xl md:text-3xl font-bold" x-text="formatCurrency(cashCountData && cashCountData.products_stats ? cashCountData.products_stats.current.inventory_value_cost : 0)">$0.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabla de Productos Vendidos -->
                                <div class="bg-white rounded-lg sm:rounded-xl shadow-lg overflow-hidden">
                                    <div class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200 flex items-center justify-between">
                                        <div>
                                            <h4 class="text-base sm:text-lg font-semibold text-gray-900">Productos vendidos en el arqueo</h4>
                                            <p class="text-xs sm:text-sm text-gray-600">Ordenados del más vendido al menos vendido</p>
                                        </div>
                                        <div class="text-xs sm:text-sm text-gray-500" x-show="cashCountData && cashCountData.products_stats && cashCountData.products_stats.current && cashCountData.products_stats.current.products_data">
                                            <span x-text="'Total: ' + ((cashCountData && cashCountData.products_stats && cashCountData.products_stats.current && cashCountData.products_stats.current.products_data) ? (cashCountData.products_stats.current.products_data.length || 0) : 0) + ' items'"></span>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendidos</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ingresos</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Compra</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Venta</th>
                                                    <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margen</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <template x-for="row in (((cashCountData && cashCountData.products_stats && cashCountData.products_stats.current && cashCountData.products_stats.current.products_data) ? cashCountData.products_stats.current.products_data : []).slice(((productsPage||1)-1)*(productsPerPage||10), (productsPage||1)*(productsPerPage||10)))" :key="row.id">
                                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-gray-900" x-text="row.product_name"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800" x-text="row.stock"></span>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-gray-900" x-text="row.quantity_sold"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-green-600" x-text="formatCurrency(row.income)"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-blue-600" x-text="formatCurrency(row.cost)"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm text-gray-900" x-text="formatCurrency(row.purchase_price)"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm text-gray-900" x-text="formatCurrency(row.sale_price)"></div>
                                                        </td>
                                                        <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" :class="row.margin_percentage >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" x-text="row.margin_percentage.toFixed(1) + '%' "></span>
                                                        </td>
                                                    </tr>
                                                </template>
                                                <!-- Estado vacío -->
                                                <tr x-show="!cashCountData || !cashCountData.products_stats || !cashCountData.products_stats.current.products_data || cashCountData.products_stats.current.products_data.length === 0">
                                                    <td colspan="6" class="px-3 sm:px-4 md:px-6 py-6 sm:py-8 text-center">
                                                        <div class="flex flex-col items-center space-y-2 sm:space-y-3">
                                                            <i class="fas fa-box-open text-gray-400 text-2xl sm:text-3xl"></i>
                                                            <div class="text-gray-500">
                                                                <p class="font-medium text-sm sm:text-base">No hay productos vendidos</p>
                                                                <p class="text-xs sm:text-sm">No se encontraron ventas de productos en este arqueo</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Paginación -->
                                    <div class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 flex items-center justify-between">
                                        <button class="px-3 py-1 rounded-md bg-gray-100 text-gray-700 text-xs sm:text-sm disabled:opacity-50" @click="productsPage = Math.max(1, (productsPage||1) - 1)" :disabled="(productsPage||1) === 1">Anterior</button>
                                        <div class="text-xs sm:text-sm text-gray-600" x-text="(productsPage||1) + ' / ' + Math.max(1, Math.ceil((((cashCountData && cashCountData.products_stats && cashCountData.products_stats.current && cashCountData.products_stats.current.products_data) ? cashCountData.products_stats.current.products_data.length : 0) || 0) / (productsPerPage||10)))"></div>
                                        <button class="px-3 py-1 rounded-md bg-gray-100 text-gray-700 text-xs sm:text-sm disabled:opacity-50" @click="productsPage = Math.min(Math.ceil((((cashCountData && cashCountData.products_stats && cashCountData.products_stats.current && cashCountData.products_stats.current.products_data) ? cashCountData.products_stats.current.products_data.length : 0) || 0) / (productsPerPage||10)), (productsPage||1) + 1)" :disabled="(productsPage||1) >= Math.ceil((((cashCountData && cashCountData.products_stats && cashCountData.products_stats.current && cashCountData.products_stats.current.products_data) ? cashCountData.products_stats.current.products_data.length : 0) || 0) / (productsPerPage||10))">Siguiente</button>
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
    /* Estilos para la tabla moderna */
    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .modern-table th {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-bottom: 2px solid #e2e8f0;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.75rem;
    }

    .modern-table td {
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }

    .modern-table tbody tr:hover {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* Estilos para las tarjetas */
    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

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

    /* Estilos para botones del hero section */
    .hero-buttons {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        flex-wrap: nowrap;
    }

    .hero-buttons a {
        flex-shrink: 0;
        white-space: nowrap;
        text-align: center;
    }

    /* Responsive para botones del hero */
    @media (max-width: 480px) {
        .hero-buttons {
            gap: 0.25rem;
        }
        
        .hero-buttons a {
            min-width: 100px;
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
        }
    }
</style>
@endpush

@push('js')
<script>
// Variables globales para el modal
let cashCountModalInstance = null;

// Función Alpine.js para el dataTable
function dataTable() {
    return {
        viewMode: window.innerWidth >= 768 ? 'table' : 'cards', // Default: table en desktop, cards en móvil

        init() {
            // Detectar cambios de tamaño de pantalla
            window.addEventListener('resize', () => {
                // En móvil siempre mostrar cards, en desktop permitir toggle
                if (window.innerWidth < 768) {
                    this.viewMode = 'cards';
                }
            });
        }
    }
}

// Función Alpine.js para el modal de arqueos de caja
function cashCountModal() {
    return {
        isOpen: false,
        
        cashCountData: null,
        activeTab: 'clientes', // Pestaña activa por defecto
        currencySymbol: '{{ $currency->symbol }}',
        // Estado de paginación para productos
        productsPage: 1,
        productsPerPage: 10,

        init() {
            // Guardar referencia global
            cashCountModalInstance = this;
        },

        closeModal() {
            this.isOpen = false;
            this.cashCountData = null;
            // Reset paginación productos al cerrar
            this.productsPage = 1;
            
            // Restaurar scroll del body
            document.body.style.overflow = 'auto';
        },

        async loadCashCountData(cashCountId) {
            try {
                const response = await fetch(`/cash-counts/${cashCountId}/details`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                
                if (data.success && data.data) {
                    this.cashCountData = data.data;
                } else {
                    throw new Error(data.message || 'Error al cargar los datos');
                }
            } catch (error) {
                console.error('Error cargando datos:', error);
                this.cashCountData = null;
                this.showNotification(`Error al cargar los datos del arqueo: ${error.message}`, 'error');
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
        cashCountModalInstance.cashCountData = null;
        
        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
        
        // Cargar datos del arqueo de forma asíncrona
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

<!-- CSS para breakpoint personalizado xs -->
<style>
    @media (min-width: 475px) {
        .xs\:inline {
            display: inline !important;
        }
        .xs\:hidden {
            display: none !important;
        }
    }
</style>
@endpush
