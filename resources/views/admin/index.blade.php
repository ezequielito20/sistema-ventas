@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="text-dark font-weight-bold">Dashboard</h1>
@stop

@section('content')
<div class="row">
    {{-- Widget de Usuarios --}}
    <div class="col-lg-3 col-12">
        <div class="small-box bg-info shadow zoomP">
            <div class="inner">
                <h3>{{ $usersCount }}</h3>
                <p>Usuarios Registrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                Ver usuarios <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Widget de Roles --}}
    <div class="col-lg-3 col-12">
        <div class="small-box bg-success shadow zoomP">
            <div class="inner">
                <h3>{{ $rolesCount }}</h3>
                <p>Roles del Sistema</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="small-box-footer">
                Ver roles <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-12">
        <div class="small-box bg-warning shadow zoomP">
            <div class="inner">
                <h3>{{ $categoriesCount }}</h3>
                <p>Categorías Registradas</p>
            </div>
            <div class="icon">
                <i class="fas fa-tags"></i>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="small-box-footer">
                Total de categorías <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Widget de Productos --}}
    <div class="col-lg-3 col-12">
        <div class="small-box bg-danger shadow zoomP">
            <div class="inner">
                <h3>{{ $productsCount }}</h3>
                <p>Productos Registrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-box"></i>
            </div>
            <a href="{{ route('admin.products.index') }}" class="small-box-footer">
                Ver productos <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>
    
{{-- Gráficos o estadísticas adicionales --}}
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Distribución de Usuarios por Rol
                </h3>
            </div>
            <div class="card-body">
                <canvas id="usersByRoleChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-1"></i>
                    Usuarios Registrados por Mes
                </h3>
            </div>
            <div class="card-body">
                <canvas id="usersPerMonthChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Nueva fila para gráficos de productos --}}
<div class="row">
    {{-- Gráfico de productos por categoría --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-1"></i>
                    Productos por Categoría
                </h3>
            </div>
            <div class="card-body">
                <canvas id="productsByCategoryChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    {{-- Tabla de resumen de productos --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Resumen de Productos por Categoría
                </h3>
            </div>
            <div class="card-body table-responsive p-0" style="height: 250px;">
                <table class="table table-head-fixed text-nowrap">
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productsByCategory as $category)
                        <tr>
                            <td>{{ $category['name'] }}</td>
                            <td>{{ $category['count'] }}</td>
                            <td>
                                @if($productsCount > 0)
                                    {{ round(($category['count'] / $productsCount) * 100, 1) }}%
                                @else
                                    0%
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Después de los widgets existentes, agregar nueva fila para Proveedores --}}
<div class="row mt-4">
    <div class="col-12">
        <h4 class="text-primary">
            <i class="fas fa-truck mr-2"></i>
            Información de Proveedores
        </h4>
    </div>
    
    {{-- Widget de Total Proveedores --}}
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary shadow">
            <div class="inner">
                <h3>{{ $suppliersCount }}</h3>
                <p>Total Proveedores</p>
            </div>
            <div class="icon">
                <i class="fas fa-truck"></i>
            </div>
            <a href="{{ route('admin.suppliers.index') }}" class="small-box-footer">
                Ver proveedores <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Widget de Proveedores con Stock Bajo --}}
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning shadow">
            <div class="inner">
                <h3>{{ $suppliersWithLowStock->count() }}</h3>
                <p>Proveedores con Stock Bajo</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="#supplierLowStockTable" class="small-box-footer" data-toggle="collapse">
                Ver detalles <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Widget de Proveedores Nuevos --}}
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success shadow">
            <div class="inner">
                <h3>{{ $suppliersPerMonth->last()->count ?? 0 }}</h3>
                <p>Proveedores Nuevos este Mes</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <a href="#supplierTrendsChart" class="small-box-footer" data-toggle="collapse">
                Ver tendencia <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Widget de Valor Total de Inventario --}}
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info shadow">
            <div class="inner">
                <h3>${{ number_format($supplierInventoryValue->sum('total_value'), 2) }}</h3>
                <p>Valor Total de Inventario</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <a href="#supplierValueTable" class="small-box-footer" data-toggle="collapse">
                Ver detalles <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- Tablas y Gráficos Detallados --}}
<div class="row mt-4">
    {{-- Top 5 Proveedores --}}
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary">
                <h3 class="card-title">
                    <i class="fas fa-trophy mr-2"></i>
                    Top 5 Proveedores por Productos
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>Productos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topSuppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->company_name }}</td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $supplier->products_count }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Proveedores con Stock Bajo --}}
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-warning">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Proveedores con Productos en Stock Bajo
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>Productos en Stock Bajo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliersWithLowStock as $supplier)
                        <tr>
                            <td>{{ $supplier->company_name }}</td>
                            <td>
                                <span class="badge badge-warning">
                                    {{ $supplier->low_stock_products }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Gráfico de Tendencias de Proveedores --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-success">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    Tendencia de Nuevos Proveedores
                </h3>
            </div>
            <div class="card-body">
                <canvas id="supplierTrendsChart" style="min-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
// Gráfico de tendencias de proveedores
new Chart(document.getElementById('supplierTrendsChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($suppliersPerMonth->pluck('month')) !!},
        datasets: [{
            label: 'Nuevos Proveedores',
            data: {!! json_encode($suppliersPerMonth->pluck('count')) !!},
            borderColor: '#28a745',
            tension: 0.1,
            fill: false
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
@endpush
@stop

@section('css')
<style>
    .small-box {
        transition: transform .3s;
    }
    
    .small-box:hover {
        transform: translateY(-5px);
    }

    .small-box .icon {
        transition: all .3s linear;
        position: absolute;
        top: 5px;
        right: 10px;
        z-index: 0;
        font-size: 70px;
        color: rgba(0,0,0,0.15);
    }

    .small-box:hover .icon {
        font-size: 75px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de usuarios por rol
    new Chart(document.getElementById('usersByRoleChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($usersByRole->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($usersByRole->pluck('count')) !!},
                backgroundColor: [
                    '#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Gráfico de usuarios por mes
    new Chart(document.getElementById('usersPerMonthChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($usersPerMonth->pluck('month')) !!},
            datasets: [{
                label: 'Usuarios Registrados',
                data: {!! json_encode($usersPerMonth->pluck('count')) !!},
                fill: false,
                borderColor: '#00a65a',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Gráfico de productos por categoría
    new Chart(document.getElementById('productsByCategoryChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($productsByCategory->pluck('name')) !!},
            datasets: [{
                label: 'Cantidad de Productos',
                data: {!! json_encode($productsByCategory->pluck('count')) !!},
                backgroundColor: [
                    '#f56954',
                    '#00a65a',
                    '#f39c12',
                    '#00c0ef',
                    '#3c8dbc',
                    '#d2d6de'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@stop