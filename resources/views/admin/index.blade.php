@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="dashboard-header">
        <div class="header-content">
            <h1 class="dashboard-title">
                <span class="title-icon">
                    <i class="fas fa-chart-line"></i>
                </span>
                Dashboard Ejecutivo
            </h1>
            <p class="dashboard-subtitle">Panel de control y análisis en tiempo real</p>
        </div>
        <div class="header-stats">
            <div class="quick-stat">
                <span class="stat-value">{{ date('H:i') }}</span>
                <span class="stat-label">Hora actual</span>
            </div>
            <div class="quick-stat">
                <span class="stat-value">{{ date('d/m/Y') }}</span>
                <span class="stat-label">Fecha</span>
            </div>
        </div>
    </div>
@stop

@section('content')
    {{-- Sección de Arqueo de Caja --}}
    <div class="dashboard-section" data-aos="fade-up" data-aos-duration="800">
        <div class="section-header">
            <div class="section-title">
                <div class="title-icon cash-register">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div class="title-content">
                    <h3>Información de Arqueo de Caja</h3>
                    <p>Estado actual de la caja y movimientos</p>
                </div>
            </div>
            <div class="section-status">
                <span class="status-indicator {{ $currentCashCount ? 'active' : 'inactive' }}">
                    <i class="fas fa-circle"></i>
                    {{ $currentCashCount ? 'Caja Abierta' : 'Caja Cerrada' }}
                </span>
            </div>
        </div>

        <div class="row">
        {{-- Widget de Balance General --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="premium-widget balance-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon balance-icon">
                    <i class="fas fa-balance-scale"></i>
                            </div>
                            <div class="widget-trend positive">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $currentBalance }}">
                                {{ $currency->symbol }}{{ number_format($currentBalance, 2) }}
                            </div>
                            <div class="widget-label">Balance Actual</div>
                            <div class="widget-meta">
                                <i class="fas fa-clock"></i>
                                Desde: {{ $currentCashCount ? Carbon\Carbon::parse($currentCashCount->opening_date)->format('d/m H:i') : 'Cerrada' }}
                            </div>
                        </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar" style="width: 85%"></div>
                </div>
            </div>
        </div>

            {{-- Widget de Ventas desde Apertura --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="premium-widget sales-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon sales-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                            <div class="widget-trend positive">
                                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $salesSinceCashOpen }}">
                                {{ $currency->symbol }}{{ number_format($salesSinceCashOpen, 2) }}
                </div>
                            <div class="widget-label">Ventas desde Apertura</div>
                            <div class="widget-meta">
                                <i class="fas fa-shopping-cart"></i>
                                Compras: {{ $currency->symbol }}{{ number_format($purchasesSinceCashOpen, 2) }}
                </div>
            </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar" style="width: 72%"></div>
                    </div>
                </div>
            </div>

            {{-- Widget de Por Cobrar --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="300">
                <div class="premium-widget debt-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon debt-icon">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="widget-trend warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $debtSinceCashOpen }}">
                                {{ $currency->symbol }}{{ number_format($debtSinceCashOpen, 2) }}
                            </div>
                            <div class="widget-label">Por Cobrar en Arqueo</div>
                            <div class="widget-meta">
                                <i class="fas fa-users"></i>
                        Ventas pendientes desde apertura
                </div>
                </div>
                        <div class="widget-action">
                            <a href="{{ route('admin.customers.index') }}" class="action-btn">
                                <i class="fas fa-eye"></i>
                                Ver Deudas
                            </a>
                        </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar warning" style="width: 45%"></div>
                    </div>
                </div>
            </div>

            {{-- Widget de Total por Cobrar --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="400">
                <div class="premium-widget total-debt-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                    </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon total-debt-icon">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <div class="widget-trend negative">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                        </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $totalPendingDebt }}">
                                {{ $currency->symbol }}{{ number_format($totalPendingDebt, 2) }}
                            </div>
                            <div class="widget-label">Total por Cobrar</div>
                            <div class="widget-meta">
                                <i class="fas fa-calendar-alt"></i>
                                Deudas acumuladas de clientes
                            </div>
                        </div>
                        <div class="widget-action">
                            <a href="{{ route('admin.customers.index') }}" class="action-btn">
                                <i class="fas fa-users"></i>
                                Ver Clientes
                            </a>
                        </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar danger" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sección de Ventas --}}
    <div class="dashboard-section" data-aos="fade-up" data-aos-duration="800">
        <div class="section-header">
            <div class="section-title">
                <div class="title-icon sales">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="title-content">
                    <h3>Información de Ventas</h3>
                    <p>Rendimiento y métricas de ventas</p>
                </div>
            </div>
            <div class="section-actions">
                <button class="btn-action" onclick="refreshSalesData()">
                    <i class="fas fa-sync-alt"></i>
                    Actualizar
                </button>
            </div>
        </div>

        <div class="row">
        {{-- Widget de Ventas de la Semana --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="premium-widget weekly-sales-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                    </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon weekly-icon">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                            <div class="widget-trend positive">
                                <i class="fas fa-trending-up"></i>
                                <span>+12%</span>
                            </div>
                        </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $weeklySales }}">
                                {{ $currency->symbol }}{{ number_format($weeklySales, 2) }}
                            </div>
                            <div class="widget-label">Ventas de la Semana</div>
                            <div class="widget-meta">
                                <i class="fas fa-calendar-day"></i>
                        Hoy: {{ $currency->symbol }}{{ number_format($todaySales, 2) }}
                </div>
                        </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar success" style="width: 78%"></div>
                </div>
            </div>
        </div>

        {{-- Widget de Promedio por Cliente --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="premium-widget average-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon average-icon">
                                <i class="fas fa-user-chart"></i>
                            </div>
                            <div class="widget-trend positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>+8%</span>
                            </div>
                        </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $averageCustomerSpend }}">
                                {{ $currency->symbol }}{{ number_format($averageCustomerSpend, 2) }}
                            </div>
                            <div class="widget-label">Promedio por Cliente</div>
                            <div class="widget-meta">
                                <i class="fas fa-users"></i>
                                Ticket promedio de venta
                            </div>
                        </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar info" style="width: 65%"></div>
                </div>
            </div>
        </div>

            {{-- Widget de Ganancia Teórica --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="300">
                <div class="premium-widget profit-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon profit-icon">
                    <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="widget-trend positive">
                                <i class="fas fa-percentage"></i>
                                <span>+15%</span>
                            </div>
                        </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $mostProfitableProducts->sum('total_profit') }}">
                                {{ $currency->symbol }}{{ number_format($mostProfitableProducts->sum('total_profit'), 2) }}
                            </div>
                            <div class="widget-label">Ganancia Total Teórica</div>
                            <div class="widget-meta">
                                <i class="fas fa-coins"></i>
                                Margen de productos vendidos
                            </div>
                        </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar warning" style="width: 88%"></div>
                </div>
            </div>
        </div>

            {{-- Widget de Rendimiento Mensual --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="400">
                <div class="premium-widget monthly-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon monthly-icon">
                                <i class="fas fa-calendar-alt"></i>
                </div>
                            <div class="widget-trend positive">
                                <i class="fas fa-rocket"></i>
                                <span>+22%</span>
                            </div>
                        </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $monthlyPurchases }}">
                                {{ $currency->symbol }}{{ number_format($monthlyPurchases, 2) }}
                            </div>
                            <div class="widget-label">Rendimiento Mensual</div>
                            <div class="widget-meta">
                                <i class="fas fa-chart-bar"></i>
                                Comparado con mes anterior
                            </div>
                        </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar primary" style="width: 92%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="row">
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
    </div> --}}

    {{-- Gráficos o estadísticas adicionales --}}
    {{-- <div class="row">
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
    </div> --}}

    {{-- Nueva fila para gráficos de productos --}}
    {{-- <div class="row">
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
    </div> --}}

    {{-- Información de Proveedores --}}
    {{-- <div class="row mt-4">
        <div class="col-12">
            <h4 class="text-primary">
                <i class="fas fa-truck mr-2"></i>
                Información de Proveedores
            </h4>
        </div>

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

        <div class="col-lg-3 col-6">
            <div class="small-box bg-info shadow">
                <div class="inner">
                    <h2>{{ $currency->symbol }}{{ number_format($supplierInventoryValue->sum('total_value'), 2) }}</h2>
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
    </div> --}}

    {{-- Tablas y Gráficos Detallados --}}
    {{-- <div class="row mt-4">
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
    </div> --}}

    {{-- Sección de Compras --}}
    <div class="dashboard-section" data-aos="fade-up" data-aos-duration="800">
        <div class="section-header">
            <div class="section-title">
                <div class="title-icon purchases">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="title-content">
                    <h3>Información de Compras</h3>
                    <p>Análisis de compras y proveedores</p>
                </div>
            </div>
            <div class="section-actions">
                <button class="btn-action" onclick="refreshPurchaseData()">
                    <i class="fas fa-sync-alt"></i>
                    Actualizar
                </button>
            </div>
        </div>

        <div class="row">
        {{-- Widget de Total Compras del Mes --}}
            <div class="col-xl-6 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="premium-widget purchases-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon purchases-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                            <div class="widget-trend {{ $purchaseGrowth >= 0 ? 'positive' : 'negative' }}">
                                <i class="fas fa-arrow-{{ $purchaseGrowth >= 0 ? 'up' : 'down' }}"></i>
                                <span>{{ abs($purchaseGrowth) }}%</span>
                            </div>
                        </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $monthlyPurchases }}">
                                {{ $currency->symbol }}{{ number_format($monthlyPurchases, 2) }}
                            </div>
                            <div class="widget-label">Compras del Mes</div>
                            <div class="widget-meta">
                                <i class="fas fa-calendar-alt"></i>
                                Comparado con mes anterior
                            </div>
                        </div>
                        <div class="widget-action">
                            <a href="{{ route('admin.purchases.index') }}" class="action-btn">
                                <i class="fas fa-list"></i>
                                Ver Compras
                            </a>
                        </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar primary" style="width: {{ min(100, ($monthlyPurchases / 10000) * 100) }}%"></div>
                    </div>
            </div>
        </div>

        {{-- Widget de Productos Más Comprados --}}
            <div class="col-xl-6 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="premium-widget top-product-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon product-icon">
                    <i class="fas fa-box"></i>
                </div>
                            <div class="widget-trend positive">
                                <i class="fas fa-crown"></i>
                                <span>TOP</span>
            </div>
        </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $topProduct->total_quantity ?? 0 }}">
                                {{ $topProduct->total_quantity ?? 0 }}
                </div>
                            <div class="widget-label">{{ Str::limit($topProduct->name ?? 'N/A', 25) }}</div>
                            <div class="widget-meta">
                    <i class="fas fa-star"></i>
                                Producto más comprado
                </div>
                        </div>
                        <div class="widget-action">
                            <a href="#topProductsDetail" class="action-btn" data-toggle="modal">
                                <i class="fas fa-eye"></i>
                                Ver Detalles
                </a>
            </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar success" style="width: 95%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos de Tendencias --}}
    <div class="dashboard-section" data-aos="fade-up" data-aos-duration="800">
        {{-- <div class="section-header">
            <div class="section-title">
                <div class="title-icon trends">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="title-content">
                    <h3>Tendencias de Negocio</h3>
                    <p>Análisis de ventas y compras mensuales</p>
                </div>
            </div>
            <div class="section-actions">
                <button class="btn-action" onclick="refreshTrendsData()">
                    <i class="fas fa-sync-alt"></i>
                    Actualizar
                </button>
    </div>
        </div> --}}

        <div class="row">
    {{-- Gráfico de Ventas Mensuales --}}
            <div class="col-12 mb-4">
                <div class="premium-chart-container large-chart" data-aos="zoom-in" data-aos-delay="100">
                    <div class="chart-header">
                        <div class="chart-title">
                            <div class="title-icon-chart sales-trend">
                                <i class="fas fa-chart-area"></i>
                </div>
                            <div class="title-content-chart">
                                <h3>Tendencia de Ventas Mensuales</h3>
                                <p>Evolución de ventas en los últimos meses</p>
                </div>
            </div>
                        <div class="chart-actions">
                            <div class="chart-stats">
                                <div class="stat-item">
                                    <div class="stat-label">Promedio</div>
                                    <div class="stat-value">{{ $currency->symbol }}{{ number_format(collect($salesMonthlyData)->avg(), 2) }}</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Máximo</div>
                                    <div class="stat-value">{{ $currency->symbol }}{{ number_format(collect($salesMonthlyData)->max(), 2) }}</div>
                                </div>
                            </div>
                            <button class="btn-chart-action" onclick="exportSalesChart()">
                                <i class="fas fa-download"></i>
                                Exportar
                            </button>
                            <button class="btn-chart-action" onclick="refreshSalesChart()">
                                <i class="fas fa-sync-alt"></i>
                                Actualizar
                            </button>
                        </div>
                    </div>
                    <div class="chart-content">
                        <canvas id="salesTrendsChart" style="min-height: 350px;"></canvas>
                    </div>
        </div>
    </div>

            {{-- Gráfico de Compras Mensuales --}}
            <div class="col-md-8 mb-4">
                <div class="premium-chart-container" data-aos="fade-right" data-aos-delay="200">
                    <div class="chart-header">
                        <div class="chart-title">
                            <div class="title-icon-chart purchases-trend">
                                <i class="fas fa-chart-line"></i>
                </div>
                            <div class="title-content-chart">
                                <h3>Tendencia de Compras Mensuales</h3>
                                <p>Evolución de compras e inventario</p>
                            </div>
                        </div>
                        <div class="chart-actions">
                            <button class="btn-chart-action" onclick="exportPurchasesChart()">
                                <i class="fas fa-download"></i>
                                Exportar
                            </button>
                            <button class="btn-chart-action" onclick="refreshPurchasesChart()">
                                <i class="fas fa-sync-alt"></i>
                                Actualizar
                            </button>
                        </div>
                    </div>
                    <div class="chart-content">
                    <canvas id="purchaseTrendsChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>

            {{-- Widget de Estadísticas de Compras --}}
            <div class="col-md-4 mb-4">
                <div class="premium-stats-container" data-aos="fade-left" data-aos-delay="300">
                    <div class="stats-header">
                        <div class="stats-title">
                            <div class="title-icon-stats purchases">
                                <i class="fas fa-star"></i>
                </div>
                            <div class="title-content-stats">
                                <h3>Top Productos</h3>
                                <p>Más comprados</p>
                            </div>
                        </div>
                    </div>
                    <div class="stats-content">
                        <div class="stats-list">
                            @foreach ($topProducts as $index => $product)
                                <div class="stats-item" data-aos="fade-up" data-aos-delay="{{ 100 + ($index * 50) }}">
                                    <div class="stats-rank">
                                        <span class="rank-number {{ $index < 3 ? 'top-rank' : '' }}">{{ $index + 1 }}</span>
                                    </div>
                                    <div class="stats-info">
                                        <div class="stats-name">{{ Str::limit($product->name, 20) }}</div>
                                        <div class="stats-meta">
                                            <span class="quantity-badge">{{ number_format($product->total_quantity) }} unidades</span>
                                            <span class="price-badge">{{ $currency->symbol }}{{ number_format($product->unit_price, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="stats-progress">
                                        <div class="progress-bar-mini" style="width: {{ min(100, ($product->total_quantity / $topProducts->first()->total_quantity) * 100) }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                    </div>
                        <div class="stats-footer">
                            <a href="{{ route('admin.products.index') }}" class="stats-action-btn">
                                <i class="fas fa-eye"></i>
                                Ver Todos los Productos
                            </a>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Información de Clientes --}}
    <div class="dashboard-section" data-aos="fade-up" data-aos-duration="800">
        <div class="section-header">
            <div class="section-title">
                <div class="title-icon customers">
                    <i class="fas fa-users"></i>
                </div>
                <div class="title-content">
                    <h3>Información de Clientes</h3>
                    <p>Gestión y análisis de clientes</p>
                </div>
            </div>
            <div class="section-actions">
                <button class="btn-action" onclick="refreshCustomerData()">
                    <i class="fas fa-sync-alt"></i>
                    Actualizar
                </button>
            </div>
        </div>

        <div class="row">
        {{-- Widget de Total Clientes --}}
            <div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="premium-widget total-customers-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon customers-icon">
                    <i class="fas fa-users"></i>
                </div>
                            <div class="widget-trend {{ $customerGrowth >= 0 ? 'positive' : 'negative' }}">
                                <i class="fas fa-arrow-{{ $customerGrowth >= 0 ? 'up' : 'down' }}"></i>
                                <span>{{ abs($customerGrowth) }}%</span>
                            </div>
                        </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $totalCustomers }}">
                                {{ $totalCustomers }}
                            </div>
                            <div class="widget-label">Total Clientes</div>
                            <div class="widget-meta">
                                <i class="fas fa-chart-line"></i>
                                Comparado con mes anterior
                            </div>
                        </div>
                        <div class="widget-action">
                            <a href="{{ route('admin.customers.index') }}" class="action-btn">
                                <i class="fas fa-list"></i>
                                Ver Clientes
                            </a>
                        </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar primary" style="width: {{ min(100, ($totalCustomers / 100) * 100) }}%"></div>
                    </div>
            </div>
        </div>

        {{-- Widget de Nuevos Clientes --}}
            <div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="premium-widget new-customers-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon new-customers-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                            <div class="widget-trend positive">
                                <i class="fas fa-trending-up"></i>
                                <span>Nuevo</span>
                            </div>
                        </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $newCustomers }}">
                                {{ $newCustomers }}
                            </div>
                            <div class="widget-label">Nuevos Clientes</div>
                            <div class="widget-meta">
                                <i class="fas fa-calendar-alt"></i>
                                Registrados este mes
                            </div>
                        </div>
                        <div class="widget-action">
                            <a href="#customerActivityChart" class="action-btn" data-toggle="modal">
                                <i class="fas fa-chart-bar"></i>
                                Ver Tendencia
                            </a>
                        </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar success" style="width: {{ min(100, ($newCustomers / 10) * 100) }}%"></div>
                    </div>
            </div>
        </div>

            {{-- Widget de Actividad de Clientes --}}
            <div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-4" data-aos="zoom-in" data-aos-delay="300">
                <div class="premium-widget customer-activity-widget">
                    <div class="widget-background">
                        <div class="bg-pattern"></div>
                        <div class="widget-gradient"></div>
                </div>
                    <div class="widget-content">
                        <div class="widget-header">
                            <div class="widget-icon activity-icon">
                                <i class="fas fa-chart-pulse"></i>
                </div>
                            <div class="widget-trend positive">
                                <i class="fas fa-fire"></i>
                                <span>Activo</span>
                            </div>
                        </div>
                        <div class="widget-body">
                            <div class="widget-value" data-value="{{ $monthlyActivity[count($monthlyActivity) - 1] ?? 0 }}">
                                {{ $monthlyActivity[count($monthlyActivity) - 1] ?? 0 }}
                            </div>
                            <div class="widget-label">Actividad Mensual</div>
                            <div class="widget-meta">
                                <i class="fas fa-pulse"></i>
                                Clientes activos este mes
                            </div>
                        </div>
                        <div class="widget-action">
                            <a href="{{ route('admin.customers.index') }}" class="action-btn">
                                <i class="fas fa-eye"></i>
                                Ver Actividad
                </a>
            </div>
                    </div>
                    <div class="widget-progress">
                        <div class="progress-bar info" style="width: 75%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 10 Productos Más Vendidos --}}
    <div class="dashboard-section" data-aos="fade-up" data-aos-duration="800">
        <div class="premium-table-container">
            <div class="table-header">
                <div class="table-title">
                    <div class="title-icon-table products">
                        <i class="fas fa-trophy"></i>
                </div>
                    <div class="title-content-table">
                        <h3>Top 10 Productos Más Vendidos</h3>
                        <p>Ranking de productos con mejor rendimiento</p>
                    </div>
                </div>
                <div class="table-actions">
                    <button class="btn-table-action" onclick="exportTopProducts()">
                        <i class="fas fa-download"></i>
                        Exportar
                    </button>
                    <button class="btn-table-action" onclick="refreshTopProducts()">
                        <i class="fas fa-sync-alt"></i>
                        Actualizar
                    </button>
                </div>
            </div>
            
            <div class="premium-table-wrapper">
                <table class="premium-table">
                        <thead>
                            <tr>
                            <th class="rank-column">#</th>
                            <th class="product-column">Producto</th>
                            <th class="center-column">Veces Vendido</th>
                            <th class="center-column">Cantidad Total</th>
                            <th class="right-column">Precio Unitario</th>
                            <th class="right-column">Ingresos Totales</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($topSellingProducts as $index => $product)
                            <tr class="table-row" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 50 }}">
                                <td class="rank-cell">
                                    <div class="rank-badge rank-{{ $index + 1 <= 3 ? 'top' : 'regular' }}">
                                        {{ $index + 1 }}
                                    </div>
                                </td>
                                <td class="product-cell">
                                    <div class="product-info">
                                        <div class="product-icon">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div class="product-details">
                                            <span class="product-name">{{ $product->name }}</span>
                                            <span class="product-category">Categoría principal</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="center-cell">
                                    <div class="metric-badge sales">
                                        <i class="fas fa-chart-line"></i>
                                        {{ $product->times_sold }}
                                    </div>
                                </td>
                                <td class="center-cell">
                                    <div class="metric-badge quantity">
                                        <i class="fas fa-cubes"></i>
                                        {{ $product->total_quantity }}
                                    </div>
                                </td>
                                <td class="right-cell">
                                    <div class="price-display">
                                        {{ $currency->symbol }}{{ number_format($product->sale_price, 2) }}
                                    </div>
                                </td>
                                <td class="right-cell">
                                    <div class="revenue-display">
                                        <span class="revenue-amount">{{ $currency->symbol }}{{ number_format($product->total_revenue, 2) }}</span>
                                        <div class="revenue-bar">
                                            <div class="revenue-progress" style="width: {{ min(100, ($product->total_revenue / $topSellingProducts->max('total_revenue')) * 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
        </div>
    </div>

    {{-- Top 5 Clientes --}}
    <div class="dashboard-section" data-aos="fade-up" data-aos-duration="800">
        <div class="row">
            <div class="col-lg-6 col-12 mb-4">
                <div class="premium-table-container customers-table">
                    <div class="table-header">
                        <div class="table-title">
                            <div class="title-icon-table customers">
                                <i class="fas fa-users"></i>
                </div>
                            <div class="title-content-table">
                                <h3>Top 5 Clientes</h3>
                                <p>Clientes con mayor volumen de compras</p>
                            </div>
                        </div>
                        <div class="table-actions">
                            <button class="btn-table-action" onclick="viewAllCustomers()">
                                <i class="fas fa-eye"></i>
                                Ver Todos
                            </button>
                        </div>
                    </div>
                    
                    <div class="premium-table-wrapper">
                        <table class="premium-table customers">
                        <thead>
                            <tr>
                                    <th class="rank-column">#</th>
                                    <th class="customer-column">Cliente</th>
                                    <th class="right-column">Total Gastado</th>
                                    <th class="center-column">Productos</th>
                            </tr>
                        </thead>
                        <tbody>
                                @foreach ($topCustomers as $index => $customer)
                                    <tr class="table-row" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                                        <td class="rank-cell">
                                            <div class="rank-badge rank-{{ $index + 1 <= 3 ? 'top' : 'regular' }}">
                                                {{ $index + 1 }}
                                            </div>
                                        </td>
                                        <td class="customer-cell">
                                            <div class="customer-info">
                                                <div class="customer-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="customer-details">
                                                    <span class="customer-name">{{ $customer->name }}</span>
                                                    <span class="customer-status">Cliente VIP</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="right-cell">
                                            <div class="spending-display">
                                                <span class="spending-amount">{{ $currency->symbol }}{{ number_format($customer->total_spent, 2) }}</span>
                                                <div class="spending-bar">
                                                    <div class="spending-progress" style="width: {{ min(100, ($customer->total_spent / $topCustomers->max('total_spent')) * 100) }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="center-cell">
                                            <div class="products-stats">
                                                <div class="stat-item">
                                                    <span class="stat-value">{{ $customer->unique_products }}</span>
                                                    <span class="stat-label">Únicos</span>
                                                </div>
                                                <div class="stat-divider">|</div>
                                                <div class="stat-item">
                                                    <span class="stat-value">{{ $customer->total_products }}</span>
                                                    <span class="stat-label">Total</span>
                                                </div>
                                            </div>
                                        </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

            <div class="col-lg-6 col-12 mb-4">
                <div class="premium-chart-container" data-aos="fade-left" data-aos-delay="200">
                    <div class="chart-header">
                        <div class="chart-title">
                            <div class="title-icon-chart analytics">
                                <i class="fas fa-chart-pie"></i>
                </div>
                            <div class="title-content-chart">
                                <h3>Análisis de Ventas</h3>
                                <p>Distribución por categorías</p>
                </div>
            </div>
                        <div class="chart-actions">
                            <button class="btn-chart-action" onclick="toggleChartView()">
                                <i class="fas fa-exchange-alt"></i>
                                Cambiar Vista
                            </button>
                        </div>
                    </div>
                    <div class="chart-content">
                        <canvas id="salesByCategoryChart" style="min-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------- --}}
    

    {{-- Gráficos de Análisis --}}
    <div class="dashboard-section" data-aos="fade-up" data-aos-duration="800">
        <div class="row">
        {{-- Gráfico de Ingresos vs Egresos --}}
            <div class="col-12">
                <div class="premium-chart-container large-chart" data-aos="zoom-in" data-aos-delay="100">
                    <div class="chart-header">
                        <div class="chart-title">
                            <div class="title-icon-chart cashflow">
                                <i class="fas fa-chart-bar"></i>
                </div>
                            <div class="title-content-chart">
                                <h3>Análisis de Flujo de Caja</h3>
                                <p>Ingresos vs Egresos - Últimos 7 días</p>
                </div>
            </div>
                        <div class="chart-actions">
                            <div class="chart-legend">
                                <div class="legend-item income">
                                    <div class="legend-color"></div>
                                    <span>Ingresos</span>
        </div>
                                <div class="legend-item expenses">
                                    <div class="legend-color"></div>
                                    <span>Egresos</span>
                </div>
                </div>
                            <button class="btn-chart-action" onclick="exportCashFlowChart()">
                                <i class="fas fa-download"></i>
                                Exportar
                            </button>
                            <button class="btn-chart-action" onclick="refreshCashFlowChart()">
                                <i class="fas fa-sync-alt"></i>
                                Actualizar
                            </button>
            </div>
                    </div>
                    <div class="chart-content large">
                        <div class="chart-stats">
                            <div class="stat-card income">
                                <div class="stat-icon">
                                    <i class="fas fa-arrow-up"></i>
                                </div>
                                <div class="stat-info">
                                    <span class="stat-value">{{ $currency->symbol }}{{ number_format(array_sum($chartData['income']), 2) }}</span>
                                    <span class="stat-label">Total Ingresos</span>
                                </div>
                            </div>
                            <div class="stat-card expenses">
                                <div class="stat-icon">
                                    <i class="fas fa-arrow-down"></i>
                                </div>
                                <div class="stat-info">
                                    <span class="stat-value">{{ $currency->symbol }}{{ number_format(array_sum($chartData['expenses']), 2) }}</span>
                                    <span class="stat-label">Total Egresos</span>
                                </div>
                            </div>
                            <div class="stat-card balance">
                                <div class="stat-icon">
                                    <i class="fas fa-equals"></i>
                                </div>
                                <div class="stat-info">
                                    <span class="stat-value">{{ $currency->symbol }}{{ number_format(array_sum($chartData['income']) - array_sum($chartData['expenses']), 2) }}</span>
                                    <span class="stat-label">Balance Neto</span>
                                </div>
                            </div>
                        </div>
                        <div class="chart-canvas-wrapper">
                            <canvas id="cashFlowChart" style="min-height: 400px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
    <style>
        /* Variables CSS para tema consistente */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --danger-gradient: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
            --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            
            --shadow-light: 0 2px 10px rgba(0,0,0,0.1);
            --shadow-medium: 0 8px 30px rgba(0,0,0,0.12);
            --shadow-heavy: 0 15px 35px rgba(0,0,0,0.15);
            
            --border-radius: 20px;
            --border-radius-sm: 12px;
            --transition-smooth: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --transition-bounce: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        /* Dashboard Header Styles */
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: var(--shadow-medium);
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        .title-icon {
            background: rgba(255,255,255,0.2);
            padding: 0.8rem;
            border-radius: var(--border-radius-sm);
            backdrop-filter: blur(10px);
            animation: pulse 2s infinite;
        }

        .dashboard-subtitle {
            margin: 0.5rem 0 0 0;
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .header-stats {
            display: flex;
            gap: 2rem;
            position: relative;
            z-index: 1;
        }

        .quick-stat {
            text-align: center;
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            backdrop-filter: blur(10px);
            min-width: 100px;
        }

        .stat-value {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Dashboard Sections */
        .dashboard-section {
            margin-bottom: 3rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: rgba(255,255,255,0.8);
            border-radius: var(--border-radius);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-light);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-title .title-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            animation: float 3s ease-in-out infinite;
        }

        .section-title .title-icon.cash-register {
            background: var(--success-gradient);
        }

        .section-title .title-icon.sales {
            background: var(--primary-gradient);
        }

        .section-title .title-icon.purchases {
            background: var(--warning-gradient);
        }

        .section-title .title-icon.customers {
            background: var(--info-gradient);
        }

        .title-content h3 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .title-content p {
            margin: 0;
            color: #7f8c8d;
            font-size: 1rem;
        }

        .status-indicator {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-indicator.active {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
        }

        .status-indicator.inactive {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .status-indicator i {
            animation: blink 1.5s infinite;
        }

        .section-actions .btn-action {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-smooth);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-actions .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        /* Premium Widgets */
        .premium-widget {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-light);
            transition: var(--transition-smooth);
            position: relative;
            height: 200px;
            cursor: pointer;
        }

        .premium-widget:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-heavy);
        }

        .widget-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
        }

        .bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
            opacity: 0.5;
        }

        .widget-gradient {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .balance-widget .widget-gradient {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .sales-widget .widget-gradient {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .debt-widget .widget-gradient {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .total-debt-widget .widget-gradient {
            background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
        }

        .weekly-sales-widget .widget-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .average-widget .widget-gradient {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .profit-widget .widget-gradient {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .monthly-widget .widget-gradient {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .purchases-widget .widget-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .top-product-widget .widget-gradient {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .total-customers-widget .widget-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .new-customers-widget .widget-gradient {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .customer-activity-widget .widget-gradient {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .widget-content {
            position: relative;
            z-index: 2;
            padding: 1.5rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            color: white;
        }

        .widget-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .widget-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--border-radius-sm);
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            backdrop-filter: blur(10px);
            animation: rotate 4s linear infinite;
        }

        .widget-trend {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.9rem;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }

        .widget-trend.positive {
            color: #2ecc71;
        }

        .widget-trend.warning {
            color: #f39c12;
        }

        .widget-trend.negative {
            color: #e74c3c;
        }

        .widget-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .widget-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            animation: countUp 2s ease-out;
        }

        .widget-label {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }

        .widget-meta {
            font-size: 0.9rem;
            opacity: 0.8;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .widget-action {
            margin-top: 1rem;
        }

        .action-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition-smooth);
            backdrop-filter: blur(10px);
        }

        .action-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .widget-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(255,255,255,0.2);
            z-index: 3;
        }

        .progress-bar {
            height: 100%;
            background: rgba(255,255,255,0.8);
            transition: width 2s ease-out;
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
            animation: shimmer 2s infinite;
        }

        .progress-bar.success {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
        }

        .progress-bar.info {
            background: linear-gradient(90deg, #3498db, #2980b9);
        }

        .progress-bar.warning {
            background: linear-gradient(90deg, #f39c12, #e67e22);
        }

        .progress-bar.danger {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
        }

        .progress-bar.primary {
            background: linear-gradient(90deg, #9b59b6, #8e44ad);
        }

        /* Animaciones */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        @keyframes countUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .header-stats {
                flex-direction: row;
                justify-content: center;
            }

            .dashboard-title {
                font-size: 2rem;
            }

            .section-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .premium-widget {
                height: 180px;
            }

            .widget-value {
                font-size: 1.5rem;
            }

            .widget-content {
                padding: 1rem;
            }
        }

        @media (max-width: 576px) {
            .dashboard-title {
                font-size: 1.5rem;
            }

            .premium-widget {
                height: 160px;
            }

            .widget-value {
                font-size: 1.2rem;
            }

            .quick-stat {
                min-width: 80px;
                padding: 0.8rem;
            }
        }

        /* Efectos de hover adicionales */
        .premium-widget:hover .widget-icon {
            animation-play-state: paused;
            transform: scale(1.1);
        }

        .premium-widget:hover .widget-value {
            transform: scale(1.05);
        }

        .premium-widget:hover .progress-bar::after {
            animation-duration: 1s;
        }

        /* Mejoras para accesibilidad */
        .premium-widget:focus {
            outline: 3px solid #3498db;
            outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Premium Tables Styles */
        .premium-table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            overflow: hidden;
            margin-bottom: 2rem;
            transition: var(--transition-smooth);
        }

        .premium-table-container:hover {
            box-shadow: var(--shadow-heavy);
        }

        .table-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .customers-table .table-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .table-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .title-icon-table {
            width: 50px;
            height: 50px;
            border-radius: var(--border-radius-sm);
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            backdrop-filter: blur(10px);
        }

        .title-content-table h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .title-content-table p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-table-action {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.6rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-smooth);
            display: flex;
            align-items: center;
            gap: 0.4rem;
            backdrop-filter: blur(10px);
        }

        .btn-table-action:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }

        .premium-table-wrapper {
            overflow-x: auto;
        }

        .premium-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .premium-table thead th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 700;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #e9ecef;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .premium-table tbody tr {
            transition: var(--transition-smooth);
            border-bottom: 1px solid #e9ecef;
        }

        .premium-table tbody tr:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            transform: translateX(5px);
        }

        .premium-table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .rank-column {
            width: 60px;
            text-align: center;
        }

        .product-column, .customer-column {
            min-width: 200px;
        }

        .center-column {
            text-align: center;
            width: 120px;
        }

        .right-column {
            text-align: right;
            width: 140px;
        }

        .rank-badge {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            margin: 0 auto;
        }

        .rank-badge.rank-top {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(240, 147, 251, 0.4);
        }

        .rank-badge.rank-regular {
            background: #e9ecef;
            color: #6c757d;
        }

        .product-info, .customer-info {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .product-icon, .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius-sm);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .customer-avatar {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .product-details, .customer-details {
            display: flex;
            flex-direction: column;
        }

        .product-name, .customer-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .product-category, .customer-status {
            font-size: 0.8rem;
            color: #7f8c8d;
        }

        .metric-badge {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .metric-badge.quantity {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .price-display {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1rem;
        }

        .revenue-display, .spending-display {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.3rem;
        }

        .revenue-amount, .spending-amount {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1rem;
        }

        .revenue-bar, .spending-bar {
            width: 80px;
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }

        .revenue-progress {
            height: 100%;
            background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%);
            transition: width 2s ease-out;
        }

        .spending-progress {
            height: 100%;
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            transition: width 2s ease-out;
        }

        .products-stats {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stat-value {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1rem;
        }

        .stat-label {
            font-size: 0.7rem;
            color: #7f8c8d;
            text-transform: uppercase;
        }

        .stat-divider {
            color: #dee2e6;
            font-weight: 300;
        }

        /* Premium Charts Styles */
        .premium-chart-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            overflow: hidden;
            margin-bottom: 2rem;
            transition: var(--transition-smooth);
        }

        .premium-chart-container:hover {
            box-shadow: var(--shadow-heavy);
        }

        .chart-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chart-header .analytics {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .chart-header .cashflow {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .chart-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .title-icon-chart {
            width: 50px;
            height: 50px;
            border-radius: var(--border-radius-sm);
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            backdrop-filter: blur(10px);
        }

        .title-content-chart h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .title-content-chart p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .chart-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .chart-legend {
            display: flex;
            gap: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .legend-item.income .legend-color {
            background: #2ecc71;
        }

        .legend-item.expenses .legend-color {
            background: #e74c3c;
        }

        .btn-chart-action {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.6rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-smooth);
            display: flex;
            align-items: center;
            gap: 0.4rem;
            backdrop-filter: blur(10px);
        }

        .btn-chart-action:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }

        .chart-content {
            padding: 2rem;
        }

        .chart-content.large {
            padding: 1.5rem 2rem 2rem;
        }

        .chart-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: center;
        }

        .stat-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 180px;
            transition: var(--transition-smooth);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-light);
        }

        .stat-card.income {
            border-left: 4px solid #2ecc71;
        }

        .stat-card.expenses {
            border-left: 4px solid #e74c3c;
        }

        .stat-card.balance {
            border-left: 4px solid #3498db;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: white;
        }

        .stat-card.income .stat-icon {
            background: #2ecc71;
        }

        .stat-card.expenses .stat-icon {
            background: #e74c3c;
        }

        .stat-card.balance .stat-icon {
            background: #3498db;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-card .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .stat-card .stat-label {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin: 0;
        }

        .chart-canvas-wrapper {
            position: relative;
            background: #fafbfc;
            border-radius: var(--border-radius-sm);
            padding: 1rem;
        }

        /* Responsive Design for Tables and Charts */
        @media (max-width: 768px) {
            .table-header, .chart-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .table-actions, .chart-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .chart-legend {
                flex-direction: column;
                gap: 0.5rem;
            }

            .chart-stats {
                flex-direction: column;
                align-items: center;
            }

            .stat-card {
                min-width: auto;
                width: 100%;
                max-width: 280px;
            }

            .premium-table {
                font-size: 0.8rem;
            }

            .premium-table td, .premium-table th {
                padding: 0.8rem 0.5rem;
            }

            .product-info, .customer-info {
                gap: 0.5rem;
            }

            .product-icon, .customer-avatar {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .rank-column {
                width: 40px;
            }

            .rank-badge {
                width: 30px;
                height: 30px;
                font-size: 0.8rem;
            }

            .metric-badge {
                padding: 0.3rem 0.6rem;
                font-size: 0.75rem;
            }

            .revenue-bar, .spending-bar {
                width: 60px;
            }

            .chart-content {
                padding: 1rem;
            }

            .stats-item {
                padding: 0.75rem;
            }

            .stats-meta {
                flex-direction: column;
                gap: 0.25rem;
            }

            .quantity-badge, .price-badge {
                font-size: 0.75rem;
            }
        }

        /* Estilos para contenedores de estadísticas premium */
        .premium-stats-container {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .premium-stats-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .stats-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1.5rem;
            color: white;
        }

        .stats-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .title-icon-stats {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .title-content-stats h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .title-content-stats p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .stats-content {
            padding: 1.5rem;
        }

        .stats-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .stats-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .stats-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .stats-rank {
            flex-shrink: 0;
        }

        .rank-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        .rank-number.top-rank {
            background: linear-gradient(135deg, #ffd700, #ffb347);
            color: #333;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
        }

        .stats-info {
            flex: 1;
        }

        .stats-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .stats-meta {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .quantity-badge, .price-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .quantity-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .price-badge {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
        }

        .stats-progress {
            width: 60px;
            height: 4px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-bar-mini {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
            transition: width 0.8s ease;
        }

        .stats-footer {
            margin-top: 1.5rem;
            text-align: center;
        }

        .stats-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .stats-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        /* Iconos específicos para gráficos */
        .title-icon-chart.sales-trend {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .title-icon-chart.purchases-trend {
            background: linear-gradient(135deg, #007bff, #6610f2);
        }

        .title-icon-chart.trends {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        /* Estilos para estadísticas de gráficos */
        .chart-stats {
            display: flex;
            gap: 1rem;
            margin-right: 1rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1rem;
            font-weight: 600;
            color: white;
        }

        @media (max-width: 768px) {
            .chart-stats {
                flex-direction: column;
                gap: 0.5rem;
            }

            .stat-item {
                text-align: left;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar AOS (Animate On Scroll)
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                mirror: false
            });

            // Animación de contadores
            function animateCounters() {
                const counters = document.querySelectorAll('.widget-value');
                counters.forEach(counter => {
                    const target = parseFloat(counter.dataset.value) || 0;
                    const text = counter.textContent;
                    const symbol = text.match(/[^\d.,]/g)?.join('') || '';
                    
                    let current = 0;
                    const increment = target / 100;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        counter.textContent = symbol + current.toLocaleString('es-PE', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }, 20);
                });
            }

            // Ejecutar animación de contadores después de un delay
            setTimeout(animateCounters, 500);

            // Función para actualizar datos de ventas
            window.refreshSalesData = function() {
                const button = document.querySelector('.btn-action');
                const icon = button.querySelector('i');
                
                icon.style.animation = 'rotate 1s linear infinite';
                button.disabled = true;
                
                // Simular actualización (aquí puedes agregar AJAX)
                setTimeout(() => {
                    icon.style.animation = '';
                    button.disabled = false;
                    
                    // Mostrar notificación de éxito
                    showNotification('Datos de ventas actualizados correctamente', 'success');
                }, 2000);
            };

            // Función para actualizar datos de compras
            window.refreshPurchaseData = function() {
                const button = event.target.closest('.btn-action');
                const icon = button.querySelector('i');
                
                icon.style.animation = 'rotate 1s linear infinite';
                button.disabled = true;
                
                // Simular actualización (aquí puedes agregar AJAX)
                setTimeout(() => {
                    icon.style.animation = '';
                    button.disabled = false;
                    
                    // Mostrar notificación de éxito
                    showNotification('Datos de compras actualizados correctamente', 'success');
                }, 2000);
            };

            // Función para actualizar datos de clientes
            window.refreshCustomerData = function() {
                const button = event.target.closest('.btn-action');
                const icon = button.querySelector('i');
                
                icon.style.animation = 'rotate 1s linear infinite';
                button.disabled = true;
                
                // Simular actualización (aquí puedes agregar AJAX)
                setTimeout(() => {
                    icon.style.animation = '';
                    button.disabled = false;
                    
                    // Mostrar notificación de éxito
                    showNotification('Datos de clientes actualizados correctamente', 'success');
                }, 2000);
            };

            // Funciones para tablas
            window.exportTopProducts = function() {
                showNotification('Exportando productos más vendidos...', 'info');
                // Aquí puedes agregar la lógica de exportación
            };

            window.refreshTopProducts = function() {
                const button = event.target.closest('.btn-table-action');
                const icon = button.querySelector('i');
                
                icon.style.animation = 'rotate 1s linear infinite';
                button.disabled = true;
                
                setTimeout(() => {
                    icon.style.animation = '';
                    button.disabled = false;
                    showNotification('Productos actualizados correctamente', 'success');
                }, 1500);
            };

            window.viewAllCustomers = function() {
                window.location.href = '{{ route("admin.customers.index") }}';
            };

            // Funciones para gráficos
            window.toggleChartView = function() {
                showNotification('Cambiando vista del gráfico...', 'info');
                // Aquí puedes agregar la lógica para cambiar el tipo de gráfico
            };

            window.exportCashFlowChart = function() {
                showNotification('Exportando gráfico de flujo de caja...', 'info');
                // Aquí puedes agregar la lógica de exportación
            };

            window.refreshCashFlowChart = function() {
                const button = event.target.closest('.btn-chart-action');
                const icon = button.querySelector('i');
                
                icon.style.animation = 'rotate 1s linear infinite';
                button.disabled = true;
                
                setTimeout(() => {
                    icon.style.animation = '';
                    button.disabled = false;
                    showNotification('Gráfico actualizado correctamente', 'success');
                }, 1500);
            };

            // Funciones para tendencias
            window.refreshTrendsData = function() {
                const button = event.target.closest('.btn-action');
                const icon = button.querySelector('i');
                
                icon.style.animation = 'rotate 1s linear infinite';
                button.disabled = true;
                
                setTimeout(() => {
                    icon.style.animation = '';
                    button.disabled = false;
                    showNotification('Datos de tendencias actualizados correctamente', 'success');
                }, 2000);
            };

            window.exportSalesChart = function() {
                showNotification('Exportando gráfico de ventas...', 'info');
                // Aquí puedes agregar la lógica de exportación
            };

            window.refreshSalesChart = function() {
                const button = event.target.closest('.btn-chart-action');
                const icon = button.querySelector('i');
                
                icon.style.animation = 'rotate 1s linear infinite';
                button.disabled = true;
                
                setTimeout(() => {
                    icon.style.animation = '';
                    button.disabled = false;
                    showNotification('Gráfico de ventas actualizado correctamente', 'success');
                }, 1500);
            };

            window.exportPurchasesChart = function() {
                showNotification('Exportando gráfico de compras...', 'info');
                // Aquí puedes agregar la lógica de exportación
            };

            window.refreshPurchasesChart = function() {
                const button = event.target.closest('.btn-chart-action');
                const icon = button.querySelector('i');
                
                icon.style.animation = 'rotate 1s linear infinite';
                button.disabled = true;
                
                setTimeout(() => {
                    icon.style.animation = '';
                    button.disabled = false;
                    showNotification('Gráfico de compras actualizado correctamente', 'success');
                }, 1500);
            };

            // Animaciones para barras de progreso en tablas
            function animateProgressBars() {
                const progressBars = document.querySelectorAll('.revenue-progress, .spending-progress');
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 500);
                });
            }

            // Ejecutar animaciones después de cargar
            setTimeout(animateProgressBars, 1000);

            // Efecto hover mejorado para filas de tabla
            document.querySelectorAll('.table-row').forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px) scale(1.01)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0) scale(1)';
                });
            });

            // Sistema de notificaciones
            function showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `notification notification-${type}`;
                notification.innerHTML = `
                    <div class="notification-content">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                        <span>${message}</span>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Animar entrada
                setTimeout(() => notification.classList.add('show'), 100);
                
                // Remover después de 3 segundos
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }

            // Agregar estilos para notificaciones
            const notificationStyles = document.createElement('style');
            notificationStyles.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
                    padding: 1rem 1.5rem;
                    z-index: 9999;
                    transform: translateX(100%);
                    transition: transform 0.3s ease;
                    border-left: 4px solid #3498db;
                }
                
                .notification.notification-success {
                    border-left-color: #2ecc71;
                }
                
                .notification.show {
                    transform: translateX(0);
                }
                
                .notification-content {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    color: #2c3e50;
                    font-weight: 600;
                }
                
                .notification-content i {
                    color: #3498db;
                }
                
                .notification-success .notification-content i {
                    color: #2ecc71;
                }
            `;
            document.head.appendChild(notificationStyles);

            // Efectos de hover mejorados para widgets
            const widgets = document.querySelectorAll('.premium-widget');
            widgets.forEach(widget => {
                widget.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                widget.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Actualizar hora en tiempo real
            function updateTime() {
                const timeElement = document.querySelector('.stat-value');
                if (timeElement && timeElement.textContent.includes(':')) {
                    const now = new Date();
                    timeElement.textContent = now.toLocaleTimeString('es-PE', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
            
            // Actualizar cada minuto
            setInterval(updateTime, 60000);

            // Parallax effect para el header
            window.addEventListener('scroll', function() {
                const header = document.querySelector('.dashboard-header');
                if (header) {
                    const scrolled = window.pageYOffset;
                    header.style.transform = `translateY(${scrolled * 0.5}px)`;
                }
            });

            // Lazy loading para gráficos
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const chartObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const chartId = entry.target.id;
                        if (chartId && !entry.target.classList.contains('chart-loaded')) {
                            entry.target.classList.add('chart-loaded');
                            // Aquí se inicializarían los gráficos específicos
                        }
                    }
                });
            }, observerOptions);

            // Observar todos los canvas de gráficos
            document.querySelectorAll('canvas').forEach(canvas => {
                chartObserver.observe(canvas);
            });

            // Efecto de typing para el título
            function typeWriter(element, text, speed = 100) {
                let i = 0;
                element.textContent = '';
                
                function type() {
                    if (i < text.length) {
                        element.textContent += text.charAt(i);
                        i++;
                        setTimeout(type, speed);
                    }
                }
                
                type();
            }

            // Aplicar efecto typing al título después de cargar
            setTimeout(() => {
                const titleElement = document.querySelector('.dashboard-title');
                if (titleElement) {
                    const originalText = titleElement.textContent;
                    typeWriter(titleElement, originalText, 50);
                }
            }, 1000);
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

            // Gráfico de tendencia de ventas mensuales
            new Chart(document.getElementById('salesTrendsChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($salesMonthlyLabels) !!},
                    datasets: [{
                        label: 'Total de Ventas',
                        data: {!! json_encode($salesMonthlyData) !!},
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
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
                                    return 'Ventas: {{ $currency->symbol }}' + context.raw
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
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
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
