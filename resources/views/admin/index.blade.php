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

        {{-- Widget de Categorías --}}
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
                    <canvas id="usersByRoleChart"
                        style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
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
                    <canvas id="usersPerMonthChart"
                        style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
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
                    <canvas id="productsByCategoryChart"
                        style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
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
                            @foreach ($productsByCategory as $category)
                                <tr>
                                    <td>{{ $category['name'] }}</td>
                                    <td>{{ $category['count'] }}</td>
                                    <td>
                                        @if ($productsCount > 0)
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
    {{-- Información de Proveedores --}}
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
                            @foreach ($topSuppliers as $supplier)
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
                            @foreach ($suppliersWithLowStock as $supplier)
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

    {{-- Sección de Compras --}}
    <div class="row mt-4">
        <div class="col-12">
            <h4 class="text-primary">
                <i class="fas fa-shopping-cart mr-2"></i>
                Información de Compras
            </h4>
        </div>

        {{-- Widget de Total Compras del Mes --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary shadow">
                <div class="inner">
                    <h3>{{ $currency->symbol }}{{ number_format($monthlyPurchases, 2) }}</h3>
                    <p>Compras del Mes</p>
                    <span class="text-sm">
                        <i
                            class="fas fa-arrow-{{ $purchaseGrowth >= 0 ? 'up text-success' : 'down text-danger' }} mr-1"></i>
                        {{ abs($purchaseGrowth) }}% vs mes anterior
                    </span>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <a href="{{ route('admin.purchases.index') }}" class="small-box-footer">
                    Ver compras <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Widget de Productos Más Comprados --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow">
                <div class="inner">
                    <h3>{{ $topProduct->total_quantity ?? 0 }}</h3>
                    <p>{{ Str::limit($topProduct->name ?? 'N/A', 20) }}</p>
                    <span class="text-sm">Producto más comprado</span>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
                <a href="#topProductsDetail" class="small-box-footer" data-toggle="modal">
                    Ver detalles <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Widget de Proveedor Principal --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning shadow">
                <div class="inner">
                    <h3>{{ $currency->symbol }}{{ number_format($topSupplier->total_amount ?? 0, 2) }}</h3>
                    <p>{{ Str::limit($topSupplier->name ?? 'N/A', 20) }}</p>
                    <span class="text-sm">Proveedor principal del mes</span>
                </div>
                <div class="icon">
                    <i class="fas fa-star"></i>
                </div>
                <a href="#topSuppliersDetail" class="small-box-footer" data-toggle="modal">
                    Ver ranking <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Widget de Productos en Stock Bajo --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger shadow">
                <div class="inner">
                    <h3>{{ $lowStockCount }}</h3>
                    <p>Productos Stock Bajo</p>
                    <span class="text-sm">Requieren atención inmediata</span>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="#lowStockDetail" class="small-box-footer" data-toggle="modal">
                    Ver productos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Gráficos de Compras --}}
    <div class="row mt-4">
        {{-- Gráfico de Tendencia de Compras --}}
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary">
                    <h3 class="card-title text-white">
                        <i class="fas fa-chart-line mr-2"></i>
                        Tendencia de Compras Mensuales
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="purchaseTrendsChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Productos por Volumen --}}
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-success">
                    <h3 class="card-title text-white">
                        <i class="fas fa-star mr-2"></i>
                        Top 5 Productos más Comprados
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px;">
                        <table class="table table-striped table-hover m-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cant</th>
                                    <th class="text-right">Precio/U</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topProducts as $product)
                                    <tr>
                                        <td>{{ Str::limit($product->name, 30) }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-success">
                                                {{ number_format($product->total_quantity) }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            {{ $currency->symbol }}{{ number_format($product->unit_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Información de Clientes --}}
    <div class="row mt-4">
        <div class="col-12">
            <h4 class="text-primary">
                <i class="fas fa-users mr-2"></i>
                Información de Clientes
            </h4>
        </div>

        {{-- Widget de Total Clientes --}}
        <div class="col-lg-4 col-6">
            <div class="small-box bg-gradient-primary shadow" style="min-height: 180px;">
                <div class="inner">
                    <h3>{{ $totalCustomers }}</h3>
                    <p>Total Clientes</p>
                    <span class="text-sm">
                        <i
                            class="fas fa-arrow-{{ $customerGrowth >= 0 ? 'up text-success' : 'down text-danger' }} mr-1"></i>
                        {{ abs($customerGrowth) }}% vs mes anterior
                    </span>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.customers.index') }}" class="small-box-footer">
                    Ver clientes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Widget de Nuevos Clientes --}}
        <div class="col-lg-4 col-6">
            <div class="small-box bg-gradient-success shadow" style="min-height: 180px;">
                <div class="inner">
                    <h3>{{ $newCustomers }}</h3>
                    <p>Nuevos Clientes este Mes</p>
                    <span class="text-sm">
                        Total registrados: {{ $monthlyActivity[count($monthlyActivity) - 1] ?? 0 }} este mes
                    </span>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <a href="#customerActivityChart" class="small-box-footer" data-toggle="modal">
                    Ver tendencia <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Widget de Clientes Verificados --}}
        <div class="col-lg-4 col-6">
            <div class="small-box bg-gradient-info shadow" style="min-height: 180px;">
                <div class="inner">
                    <h3>{{ $verifiedCustomers ?? 0 }}</h3>
                    <p>Clientes con NIT Verificado</p>
                    <span class="text-sm">
                        {{ $verifiedPercentage ?? 0 }}% del total de clientes
                    </span>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('admin.customers.index', ['verified' => 1]) }}" class="small-box-footer">
                    Ver listado <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Sección de Ventas --}}
    <div class="row mt-4">
        <div class="col-12">
            <h4 class="text-primary">
                <i class="fas fa-cash-register mr-2"></i>
                Información de Ventas
            </h4>
        </div>

        {{-- Widget de Ventas del Día --}}
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success shadow">
                <div class="inner">
                    <h3>{{ $currency->symbol }}{{ number_format($todaySales, 2) }}</h3>
                    <p>Ventas del Día</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cash-register"></i>
                </div>
            </div>
        </div>

        {{-- Widget de Promedio por Cliente --}}
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info shadow">
                <div class="inner">
                    <h3>{{ $currency->symbol }}{{ number_format($averageCustomerSpend, 2) }}</h3>
                    <p>Promedio de Venta por Cliente</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        {{-- Widget de Productos Rentables --}}
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning shadow">
                <div class="inner">
                    <h3>{{ $currency->symbol }}{{ number_format($mostProfitableProducts->sum('total_profit'), 2) }}</h3>
                    <p>Ganancia Total</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 10 Productos Más Vendidos --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary">
                    <h3 class="card-title">
                        <i class="fas fa-trophy mr-2"></i>
                        Top 10 Productos Más Vendidos
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Veces Vendido</th>
                                <th class="text-center">Cantidad Total</th>
                                <th class="text-right">Precio Unitario</th>
                                <th class="text-right">Ingresos Totales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topSellingProducts as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td class="text-center">{{ $product->times_sold }}</td>
                                    <td class="text-center">{{ $product->total_quantity }}</td>
                                    <td class="text-right">
                                        {{ $currency->symbol }}{{ number_format($product->sale_price, 2) }}</td>
                                    <td class="text-right">
                                        {{ $currency->symbol }}{{ number_format($product->total_revenue, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 5 Clientes y Categorías más Vendidas --}}
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-2"></i>
                        Top 5 Clientes
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th class="text-right">Total Gastado</th>
                                <th class="text-center">Productos Únicos</th>
                                <th class="text-center">Total Productos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topCustomers as $customer)
                                <tr>
                                    <td>{{ $customer->name }}</td>
                                    <td class="text-right">
                                        {{ $currency->symbol }}{{ number_format($customer->total_spent, 2) }}</td>
                                    <td class="text-center">{{ $customer->unique_products }}</td>
                                    <td class="text-center">{{ $customer->total_products }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-info">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Ventas por Categoría
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="salesByCategoryChart" style="min-height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------- --}}
    {{-- Sección de Arqueo de Caja --}}
    <div class="row mt-4">
        <div class="col-12">
            <h4 class="text-primary">
                <i class="fas fa-cash-register mr-2"></i>
                Información de Arqueo de Caja
            </h4>
        </div>

        {{-- Widget de Balance General --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success shadow">
                <div class="inner">
                    <h3>{{ $currency->symbol }}{{ number_format($currentBalance, 2) }}</h3>
                    <p>Balance Actual</p>
                    <span class="text-sm">
                        Desde:
                        {{ $currentCashCount ? Carbon\Carbon::parse($currentCashCount->opening_date)->format('d/m/Y H:i') : 'No hay caja abierta' }}
                    </span>
                </div>
                <div class="icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
            </div>
        </div>

        {{-- Widget de Eficiencia de Caja --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info shadow">
                <div class="inner">
                    @php
                        $efficiency = $todayIncome > 0 ? (($todayIncome - $todayExpenses) / $todayIncome) * 100 : 0;
                    @endphp
                    <h3>{{ number_format($efficiency, 1) }}%</h3>
                    <p>Eficiencia de Caja</p>
                    <span class="text-sm">
                        Basado en ingresos vs egresos del día
                    </span>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        {{-- Widget de Movimientos por Hora --}}
        {{-- <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning shadow">
                <div class="inner">
                    @php
                        $hoursOpen = $currentCashCount ? now()->diffInHours($currentCashCount->opening_date) + 1 : 1;
                        $movementsPerHour = $totalMovements / $hoursOpen;
                    @endphp
                    <h3>{{ number_format($movementsPerHour, 1) }}</h3>
                    <p>Movimientos por Hora</p>
                    <span class="text-sm">
                        Total hoy: {{ $totalMovements }} movimientos
                    </span>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div> --}}

        {{-- Widget de Días Críticos --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-danger shadow">
                <div class="inner">
                    @php
                        $criticalDays = collect($chartData['income'])
                            ->zip($chartData['expenses'])
                            ->filter(function ($pair) {
                                return $pair[1] > $pair[0];
                            })
                            ->count();
                    @endphp
                    <h3>{{ $criticalDays }}</h3>
                    <p>Días con Déficit</p>
                    <span class="text-sm">
                        Últimos 7 días
                    </span>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos de Análisis --}}
    <div class="row mt-4">
        {{-- Gráfico de Ingresos vs Egresos --}}
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Ingresos vs Egresos (Últimos 7 días)
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="cashFlowChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Tabla de Días Críticos --}}
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-danger">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Días con Mayor Déficit
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Déficit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chartData['labels'] as $index => $date)
                                @php
                                    $deficit = $chartData['income'][$index] - $chartData['expenses'][$index];
                                @endphp
                                @if ($deficit < 0)
                                    <tr>
                                        <td>{{ $date }}</td>
                                        <td class="text-danger">
                                            {{ $currency->symbol }}{{ number_format(abs($deficit), 2) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
            color: rgba(0, 0, 0, 0.15);
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

            // Gráfico de tendencia de compras
            new Chart(document.getElementById('purchaseTrendsChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($purchaseMonthlyLabels) !!},
                    datasets: [{
                        label: 'Total de Compras',
                        data: {!! json_encode($purchaseMonthlyData) !!},
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Total: {{ $currency->symbol }}' + context.raw
                                        .toLocaleString('es-PE');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '{{ $currency->symbol }}' + value.toLocaleString('es-PE');
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de Actividad de Clientes
            new Chart(document.getElementById('customerActivityChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($monthlyLabels) !!},
                    datasets: [{
                        label: 'Nuevos Clientes',
                        data: {!! json_encode($monthlyActivity) !!},
                        borderColor: '#28a745',
                        tension: 0.1,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
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

            new Chart(document.getElementById('salesByCategoryChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($salesByCategory->pluck('name')) !!},
                    datasets: [{
                        label: 'Ventas por Categoría',
                        data: {!! json_encode($salesByCategory->pluck('total_revenue')) !!},
                        backgroundColor: [
                            '#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'
                        ]
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
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    return label + ': {{ $currency->symbol }}' + value.toLocaleString(
                                        'es-PE');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '{{ $currency->symbol }}' + value.toLocaleString('es-PE');
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de Ingresos vs Egresos
            new Chart(document.getElementById('cashFlowChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartData['labels']) !!},
                    datasets: [{
                        label: 'Ingresos',
                        data: {!! json_encode($chartData['income']) !!},
                        backgroundColor: 'rgba(40, 167, 69, 0.5)',
                        borderColor: 'rgb(40, 167, 69)',
                        borderWidth: 1
                    }, {
                        label: 'Egresos',
                        data: {!! json_encode($chartData['expenses']) !!},
                        backgroundColor: 'rgba(220, 53, 69, 0.5)',
                        borderColor: 'rgb(220, 53, 69)',
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
                                callback: function(value) {
                                    return '{{ $currency->symbol }}' + value.toLocaleString('es-PE');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': {{ $currency->symbol }}' +
                                        context.raw.toLocaleString('es-PE');
                                }
                            }
                        }
                    }
                }
            });


        });
    </script>
@stop
