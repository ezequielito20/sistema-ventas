@extends('layouts.app')

@section('title', 'Estadísticas del Sistema')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Estadísticas del Sistema</h1>
            <p class="text-gray-600">Análisis completo de rendimiento y métricas</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="text-sm text-gray-500">{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Ventas Totales -->
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Ventas Totales</p>
                    <p class="text-2xl font-bold text-gray-900">$45,230</p>
                    <p class="text-sm text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        +18.2%
                    </p>
                </div>
            </div>
        </div>

        <!-- Pedidos Pendientes -->
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pedidos Pendientes</p>
                    <p class="text-2xl font-bold text-gray-900">12</p>
                    <p class="text-sm text-yellow-600">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Requieren atención
                    </p>
                </div>
            </div>
        </div>

        <!-- Productos Vendidos -->
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Productos Vendidos</p>
                    <p class="text-2xl font-bold text-gray-900">1,247</p>
                    <p class="text-sm text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        +12.5%
                    </p>
                </div>
            </div>
        </div>

        <!-- Clientes Activos -->
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Clientes Activos</p>
                    <p class="text-2xl font-bold text-gray-900">89</p>
                    <p class="text-sm text-purple-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        +8.7%
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ventas por Mes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ventas por Mes</h3>
                <p class="card-subtitle">Evolución de ventas en los últimos 6 meses</p>
            </div>
            <div class="p-6">
                <canvas id="salesChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Productos Más Vendidos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Productos Más Vendidos</h3>
                <p class="card-subtitle">Top 5 productos por cantidad vendida</p>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-semibold">1</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Laptop HP Pavilion</p>
                            <p class="text-sm text-gray-500">156 unidades vendidas</p>
                        </div>
                    </div>
                    <span class="badge badge-success">$23,400</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-green-600 font-semibold">2</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Mouse Gaming RGB</p>
                            <p class="text-sm text-gray-500">89 unidades vendidas</p>
                        </div>
                    </div>
                    <span class="badge badge-success">$8,900</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <span class="text-yellow-600 font-semibold">3</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Teclado Mecánico</p>
                            <p class="text-sm text-gray-500">67 unidades vendidas</p>
                        </div>
                    </div>
                    <span class="badge badge-success">$6,700</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Actividad Reciente</h3>
            <p class="card-subtitle">Últimas transacciones del sistema</p>
        </div>
        <div class="overflow-x-auto">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-medium">Juan Pérez</td>
                        <td>Laptop HP Pavilion</td>
                        <td>1</td>
                        <td>$1,200.00</td>
                        <td><span class="badge badge-success">Completada</span></td>
                        <td>2025-08-11 14:30</td>
                    </tr>
                    <tr>
                        <td class="font-medium">María García</td>
                        <td>Mouse Gaming RGB</td>
                        <td>2</td>
                        <td>$200.00</td>
                        <td><span class="badge badge-warning">Pendiente</span></td>
                        <td>2025-08-11 13:45</td>
                    </tr>
                    <tr>
                        <td class="font-medium">Carlos López</td>
                        <td>Teclado Mecánico</td>
                        <td>1</td>
                        <td>$150.00</td>
                        <td><span class="badge badge-success">Completada</span></td>
                        <td>2025-08-11 12:15</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('vendor/chartjs/chart.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar gráfico de ventas
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
            datasets: [{
                label: 'Ventas ($)',
                data: [12000, 19000, 15000, 25000, 22000, 30000],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
