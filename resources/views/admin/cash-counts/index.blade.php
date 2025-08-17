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
                                    <a href="{{ route('admin.cash-counts.show', $cashCount->id) }}"
                                       class="w-8 h-8 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg flex items-center justify-center transition-all duration-200"
                                       title="Ver movimientos">
                                        <i class="fas fa-eye text-sm"></i>
                                    </a>
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
