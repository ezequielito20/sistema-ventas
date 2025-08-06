@extends('adminlte::page')

@section('title', 'Gestión de Productos')

@section('content_header')
    <div class="modern-header">
        <div class="header-content">
            <div class="title-section">
                <div class="icon-wrapper">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="title-text">
                    <h1 class="main-title">Gestión de Productos</h1>
                    <p class="subtitle">Administra y visualiza todos tus productos con herramientas avanzadas de control</p>
                </div>
            </div>
            <div class="header-actions">
                @can('products.report')
                    <a href="{{ route('admin.products.report') }}" class="action-btn info-btn" target="_blank" data-toggle="tooltip" title="Generar Reporte PDF">
                        <i class="fas fa-file-pdf"></i>
                        <span>Reporte</span>
                    </a>
                @endcan
                @can('products.create')
                    <a href="{{ route('admin.products.create') }}" class="action-btn primary-btn" data-toggle="tooltip" title="Crear Nuevo Producto">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nuevo Producto</span>
                    </a>
                @endcan
            </div>
        </div>
        <div class="header-decoration"></div>
    </div>
@stop

@section('content')
    {{-- Panel de Estadísticas --}}
    <div class="stats-panel">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $totalProducts }}</div>
                    <div class="stat-label">Total Productos</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $currency->symbol }} {{ number_format($totalPurchaseValue, 2) }}</div>
                    <div class="stat-label">Valor de Compra</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $currency->symbol }} {{ number_format($totalSaleValue, 2) }}</div>
                    <div class="stat-label">Valor de Venta</div>
                </div>
            </div>
            
            <div class="stat-card highlight">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $currency->symbol }} {{ number_format($potentialProfit, 2) }}</div>
                    <div class="stat-label">Ganancia Potencial ({{ number_format($profitPercentage, 2) }}%)</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel Principal de Productos --}}
    <div class="main-panel">
        <div class="panel-header">
            <div class="header-content">
                <div class="title-section">
                    <div class="title-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="title-text">
                        <h3 class="panel-title">Lista de Productos</h3>
                        <p class="panel-subtitle">Gestiona y visualiza todos tus productos</p>
                    </div>
                </div>
                <div class="header-controls">
                    <div class="search-group">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="search-input" id="productSearch" placeholder="Buscar producto...">
                            <button type="button" class="search-clear" id="clearSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="filter-group">
                        <button type="button" class="filter-btn active" data-filter="all">
                            <i class="fas fa-list"></i>
                            <span>Todos</span>
                        </button>
                        <button type="button" class="filter-btn" data-filter="low">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Stock Bajo</span>
                        </button>
                        <button type="button" class="filter-btn" data-filter="normal">
                            <i class="fas fa-check-circle"></i>
                            <span>Stock Normal</span>
                        </button>
                    </div>
                    <button type="button" class="panel-toggle" data-card-widget="collapse">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="th-code">Código</th>
                            <th class="th-product">Producto</th>
                            <th class="th-category">Categoría</th>
                            <th class="th-stock">Stock</th>
                            <th class="th-price">Precio Compra</th>
                            <th class="th-price">Precio Venta</th>
                            <th class="th-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr class="fade-in-up">
                                <td>
                                    <div class="code-cell">
                                        <span class="product-code">{{ $product->code }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-item">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image">
                                        <div class="product-info">
                                            <div class="product-name">{{ $product->name }}</div>
                                            <div class="product-code">{{ $product->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="category-badge">
                                        <i class="fas fa-tag"></i>
                                        <span>{{ $product->category->name }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="stock-badge badge badge-{{ $product->stock_status_label === 'Bajo' ? 'danger' : ($product->stock_status_label === 'Normal' ? 'warning' : 'success') }}">
                                        <i class="fas fa-boxes"></i>
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <span class="price-text purchase-price">
                                        {{ $currency->symbol }} {{ number_format($product->purchase_price, 2) }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <span class="price-text sale-price">
                                        {{ $currency->symbol }} {{ number_format($product->sale_price, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        @can('products.show')
                                            <button type="button" class="action-btn info-btn show-product" data-id="{{ $product->id }}" data-tooltip="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endcan
                                        @can('products.edit')
                                            <a href="{{ route('admin.products.edit', $product->id) }}" class="action-btn warning-btn" data-tooltip="Editar Producto">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('products.destroy')
                                            <button type="button" class="action-btn danger-btn delete-product" data-id="{{ $product->id }}" data-tooltip="Eliminar Producto">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Estado vacío -->
            <div id="emptyState" class="empty-state" style="display: none;">
                <div class="empty-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h5 class="empty-title">No hay productos disponibles</h5>
                <p class="empty-description">No se encontraron productos que coincidan con los filtros aplicados</p>
            </div>
        </div>
    </div>
        </div>
    </div>

    @if (isset($product))
        {{-- Modal para mostrar producto --}}
        <div class="modal fade" id="showProductModal" tabindex="-1" role="dialog" aria-labelledby="showProductModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="showProductModalLabel">
                            <i class="fas fa-box mr-2"></i>
                            Detalles del Producto
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            {{-- Imagen del producto --}}
                            <div class="col-md-3 ">
                                                                    <img src="{{ $product->image_url }}" alt="Imagen del producto"
                                    style="width: 100%; height: 100%; 
                            border-radius: 8px;">
                                <p id="noImage" class="text-muted mt-3" style="display: none;">Sin imagen</p>
                            </div>


                            {{-- Información básica --}}
                            <div class="col-md-8">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 30%">Código:</th>
                                        <td><span id="productCode"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Nombre:</th>
                                        <td><span id="productName"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Categoría:</th>
                                        <td><span id="productCategory"></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Descripción</h6>
                                    </div>
                                    <div class="card-body">
                                        <p id="productDescription" class="mb-0"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Información de stock --}}
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-boxes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Stock Actual</span>
                                        <span class="info-box-number" id="productStock"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i
                                            class="fas fa-exclamation-triangle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Stock Mínimo</span>
                                        <span class="info-box-number" id="productMinStock"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-warehouse"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Stock Máximo</span>
                                        <span class="info-box-number" id="productMaxStock"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Información de precios --}}
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Precio de Compra</h6>
                                    </div>
                                    <div class="card-body">
                                        <h4 class="text-primary mb-0">{{ $currency->symbol }}<span
                                                id="productPurchasePrice"></span></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Precio de Venta</h6>
                                    </div>
                                    <div class="card-body">
                                        <h4 class="text-success mb-0">{{ $currency->symbol }}<span
                                                id="productSalePrice"></span></h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Fechas --}}
                        <div class="row mt-3">
                            <div class="col-12">
                                <table class="table table-sm">
                                    <tr>
                                        <th>Fecha de Ingreso:</th>
                                        <td>
                                            <span id="productEntryDate"></span>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-clock mr-1"></i>
                                                <span id="productEntryDaysAgo"></span>
                                            </small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Creado:</th>
                                        <td id="productCreatedAt"></td>
                                    </tr>
                                    <tr>
                                        <th>Última Actualización:</th>
                                        <td id="productUpdatedAt"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-2"></i>Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@section('css')
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-light: #6366f1;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Header Moderno */
        .modern-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .icon-wrapper {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .icon-wrapper i {
            font-size: 1.25rem;
            color: white;
        }

        .title-text {
            color: white;
        }

        .main-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            margin-bottom: 0.25rem;
        }

        .subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            min-width: 120px;
            justify-content: center;
        }

        .primary-btn {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            color: var(--primary-color);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .info-btn {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            color: inherit;
            text-decoration: none;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn:active {
            transform: translateY(-1px);
        }

        .action-btn i {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .action-btn:hover i {
            transform: scale(1.1);
        }

        .header-decoration {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        }

        /* Panel de Estadísticas */
        .stats-panel {
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.highlight {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: white;
            font-size: 1.25rem;
        }

        .stat-card.highlight .stat-icon {
            background: rgba(255, 255, 255, 0.2);
        }

        .stat-content {
            flex: 1;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            opacity: 0.8;
        }

        /* Panel Principal */
        .main-panel {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .panel-header {
            background: #f8fafc;
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .title-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .panel-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            margin-bottom: 0.25rem;
        }

        .panel-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .search-group {
            position: relative;
        }

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            color: #9ca3af;
            z-index: 2;
        }

        .search-input {
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #d1d5db;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            width: 300px;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .search-clear {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 50%;
            transition: var(--transition);
        }

        .search-clear:hover {
            background: #f3f4f6;
            color: #6b7280;
        }

        .filter-group {
            display: flex;
            gap: 0.5rem;
        }

        .filter-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-btn.active,
        .filter-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .panel-toggle {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: var(--transition);
        }

        .panel-toggle:hover {
            background: #f3f4f6;
            color: var(--dark-color);
        }

        .panel-body {
            padding: 1.5rem;
        }

        /* Tabla Moderna */
        .table-container {
            overflow-x: auto;
            border-radius: var(--border-radius);
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            min-width: 600px;
        }

        .modern-table th {
            background: #f8fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: 2px solid #e5e7eb;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .modern-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .modern-table tr:hover {
            background: #f8fafc;
        }

        /* Ancho de columnas específicas */
        .th-code {
            min-width: 80px;
            width: 10%;
        }

        .th-product {
            min-width: 200px;
            width: 35%;
        }

        .th-category {
            min-width: 120px;
            width: 20%;
        }

        .th-stock {
            min-width: 80px;
            width: 10%;
        }

        .th-price {
            min-width: 100px;
            width: 12%;
        }

        .th-actions {
            min-width: 100px;
            width: 13%;
        }

        /* Celdas de la tabla */
        .code-cell {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.875rem;
        }

        .product-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .product-image {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #e5e7eb;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .product-image:hover {
            transform: scale(1.1);
            border-color: var(--primary-color);
        }

        .product-info {
            flex: 1;
            min-width: 0;
        }

        .product-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .product-code {
            font-size: 0.75rem;
            color: #6b7280;
            font-family: 'Courier New', monospace;
        }

        .category-badge {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.625rem;
            background: #f3f4f6;
            border-radius: 16px;
            font-size: 0.75rem;
            color: #6b7280;
            white-space: nowrap;
        }

        .category-badge i {
            color: var(--primary-color);
            font-size: 0.7rem;
        }

        .stock-badge {
            padding: 0.375rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .price-text {
            font-weight: 600;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .purchase-price {
            color: var(--info-color);
        }

        .sale-price {
            color: var(--success-color);
        }

        .action-buttons {
            display: flex;
            gap: 0.375rem;
            justify-content: center;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .info-btn {
            background: var(--info-color);
            color: white;
        }

        .warning-btn {
            background: var(--warning-color);
            color: white;
        }

        .danger-btn {
            background: var(--danger-color);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Estado Vacío */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-icon {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .empty-description {
            color: #9ca3af;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Animaciones */
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .header-content {
                flex-direction: column;
                gap: 1.25rem;
                align-items: stretch;
            }

            .header-actions {
                justify-content: center;
                gap: 0.75rem;
            }

            .action-btn {
                min-width: 110px;
                padding: 0.7rem 1.4rem;
            }

            .header-controls {
                flex-direction: column;
                gap: 1rem;
            }

            .search-input {
                width: 100%;
            }

            .filter-group {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .modern-header {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .main-title {
                font-size: 1.5rem;
            }

            .subtitle {
                font-size: 0.8rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .title-section {
                justify-content: center;
                text-align: center;
            }

            .icon-wrapper {
                width: 45px;
                height: 45px;
            }

            .icon-wrapper i {
                font-size: 1.1rem;
            }

            .header-actions {
                flex-direction: row;
                gap: 0.5rem;
                justify-content: center;
                flex-wrap: wrap;
            }

            .action-btn {
                flex: 1;
                min-width: 100px;
                max-width: 150px;
                padding: 0.625rem 1rem;
                font-size: 0.8rem;
                gap: 0.375rem;
            }

            .action-btn i {
                font-size: 0.9rem;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .stat-value {
                font-size: 1.25rem;
            }

            .panel-header {
                padding: 1rem;
            }

            .title-section {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .header-controls {
                flex-direction: column;
                gap: 0.75rem;
            }

            .search-input {
                width: 100%;
            }

            .filter-group {
                justify-content: center;
                flex-wrap: wrap;
            }

            .table-container {
                margin: 0 -0.5rem;
                border-radius: 0;
            }

            .modern-table {
                font-size: 0.8rem;
                min-width: 500px;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.625rem 0.5rem;
            }

            .th-code {
                min-width: 70px;
            }

            .th-product {
                min-width: 180px;
            }

            .th-category {
                min-width: 100px;
            }

            .th-stock {
                min-width: 70px;
            }

            .th-price {
                min-width: 85px;
            }

            .th-actions {
                min-width: 90px;
            }

            .product-item {
                flex-direction: column;
                gap: 0.375rem;
                text-align: center;
            }

            .product-image {
                width: 35px;
                height: 35px;
            }

            .product-name {
                font-size: 0.8rem;
            }

            .product-code {
                font-size: 0.7rem;
            }

            .action-btn {
                width: 28px;
                height: 28px;
                font-size: 0.7rem;
            }
        }

        @media (max-width: 480px) {
            .modern-header {
                padding: 0.75rem;
                margin-bottom: 0.75rem;
            }

            .main-title {
                font-size: 1.25rem;
            }

            .subtitle {
                font-size: 0.75rem;
            }

            .header-content {
                gap: 0.75rem;
            }

            .title-section {
                gap: 0.75rem;
            }

            .icon-wrapper {
                width: 40px;
                height: 40px;
            }

            .icon-wrapper i {
                font-size: 1rem;
            }

            .header-actions {
                gap: 0.375rem;
            }

            .action-btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.75rem;
                gap: 0.25rem;
                min-width: 80px;
                max-width: 120px;
            }

            .action-btn i {
                font-size: 0.8rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .stat-card {
                padding: 0.75rem;
            }

            .stat-icon {
                width: 35px;
                height: 35px;
                font-size: 0.875rem;
            }

            .stat-value {
                font-size: 1.125rem;
            }

            .panel-header {
                padding: 0.75rem;
            }

            .modern-table {
                font-size: 0.75rem;
                min-width: 400px;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.5rem 0.375rem;
            }

            .th-code {
                min-width: 60px;
            }

            .th-product {
                min-width: 150px;
            }

            .th-category {
                min-width: 80px;
            }

            .th-stock {
                min-width: 60px;
            }

            .th-price {
                min-width: 70px;
            }

            .th-actions {
                min-width: 80px;
            }

            .product-image {
                width: 30px;
                height: 30px;
            }

            .product-name {
                font-size: 0.75rem;
            }

            .product-code {
                font-size: 0.65rem;
            }

            .category-badge {
                padding: 0.25rem 0.5rem;
                font-size: 0.65rem;
                gap: 0.25rem;
            }

            .category-badge i {
                font-size: 0.6rem;
            }

            .stock-badge {
                padding: 0.25rem 0.5rem;
                font-size: 0.65rem;
            }

            .price-text {
                font-size: 0.7rem;
            }

            .action-btn {
                width: 24px;
                height: 24px;
                font-size: 0.65rem;
            }
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Cargar todas las librerías necesarias
            loadDataTables(function() {
                // Inicializar tooltips
                $('[data-tooltip]').tooltip();

                // Función para filtrar productos
                function filterProducts(filter) {
                    const rows = $('.modern-table tbody tr');
                    let visibleCount = 0;

                    rows.each(function() {
                        const row = $(this);
                        const stockBadge = row.find('.stock-badge');
                        let show = false;

                        if (filter === 'all') {
                            show = true;
                        } else if (filter === 'low') {
                            // Verificar si el badge tiene la clase 'badge-danger' (stock bajo)
                            show = stockBadge.hasClass('badge-danger');
                        } else if (filter === 'normal') {
                            // Verificar si el badge tiene la clase 'badge-warning' o 'badge-success' (stock normal/alto)
                            show = stockBadge.hasClass('badge-warning') || stockBadge.hasClass('badge-success');
                        }

                        if (show) {
                            row.show();
                            visibleCount++;
                        } else {
                            row.hide();
                        }
                    });

                    // Mostrar/ocultar estado vacío
                    if (visibleCount === 0) {
                        $('.table-container').hide();
                        $('#emptyState').show();
                    } else {
                        $('.table-container').show();
                        $('#emptyState').hide();
                    }
                }

                // Función para buscar productos
                function searchProducts(searchTerm) {
                    const rows = $('.modern-table tbody tr');
                    let visibleCount = 0;

                    rows.each(function() {
                        const row = $(this);
                        const productName = row.find('.product-name').text().toLowerCase();
                        const productCode = row.find('.product-code').text().toLowerCase();
                        const categoryName = row.find('.category-badge span').text().toLowerCase();

                        if (productName.includes(searchTerm) || 
                            productCode.includes(searchTerm) || 
                            categoryName.includes(searchTerm)) {
                            row.show();
                            visibleCount++;
                        } else {
                            row.hide();
                        }
                    });

                    // Mostrar/ocultar estado vacío
                    if (visibleCount === 0) {
                        $('.table-container').hide();
                        $('#emptyState').show();
                    } else {
                        $('.table-container').show();
                        $('#emptyState').hide();
                    }
                }

                // Búsqueda en tiempo real
                $('#productSearch').on('keyup', function() {
                    const searchTerm = $(this).val().toLowerCase();
                    searchProducts(searchTerm);
                });

                // Limpiar búsqueda
                $('#clearSearch').on('click', function() {
                    $('#productSearch').val('');
                    searchProducts('');
                });

                // Filtros
                $('.filter-btn').click(function() {
                    $('.filter-btn').removeClass('active');
                    $(this).addClass('active');

                    const filter = $(this).data('filter');
                    filterProducts(filter);
                });

                // Mostrar detalles del producto
                $('.show-product').click(function() {
                    const id = $(this).data('id');

                    $.ajax({
                        url: `/products/${id}`,
                        type: 'GET',
                        success: function(response) {
                            if (response.status === 'success') {
                                const product = response.product;

                                // Actualizar imagen
                                if (product.image) {
                                    $('#productImage').attr('src', product.image).show();
                                    $('#noImage').hide();
                                } else {
                                    $('#productImage').hide();
                                    $('#noImage').show();
                                }

                                // Actualizar información básica
                                $('#productCode').text(product.code);
                                $('#productName').text(product.name);
                                $('#productCategory').text(product.category);
                                $('#productDescription').text(product.description);

                                // Actualizar stock
                                $('#productStock').text(product.stock);
                                $('#productMinStock').text(product.min_stock);
                                $('#productMaxStock').text(product.max_stock);

                                // Actualizar precios
                                $('#productPurchasePrice').text(product.purchase_price);
                                $('#productSalePrice').text(product.sale_price);

                                // Actualizar fechas
                                $('#productEntryDate').text(product.entry_date);
                                $('#productEntryDaysAgo').text(product.entry_days_ago);
                                $('#productCreatedAt').text(product.created_at);
                                $('#productUpdatedAt').text(product.updated_at);

                                $('#showProductModal').modal('show');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudo cargar la información del producto', 'error');
                        }
                    });
                });

                // Manejo de eliminación de productos
                $('.delete-product').click(function() {
                    const productId = $(this).data('id');

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "Esta acción no se puede revertir",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/products/delete/${productId}`,
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    if (response.status === 'success') {
                                        Swal.fire({
                                            title: '¡Eliminado!',
                                            text: response.message,
                                            icon: 'success'
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    const response = xhr.responseJSON;
                                    Swal.fire('Error', response.message || 'No se pudo eliminar el producto', 'error');
                                }
                            });
                        }
                    });
                });

                // Animación de entrada para las filas
                $('.modern-table tbody tr').each(function(index) {
                    $(this).css('animation-delay', (index * 0.1) + 's');
                });

                // Toggle del panel
                $('.panel-toggle').click(function() {
                    const icon = $(this).find('i');
                    const panelBody = $('.panel-body');
                    
                    if (panelBody.is(':visible')) {
                        panelBody.slideUp();
                        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    } else {
                        panelBody.slideDown();
                        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    }
                });
            });
            
            // Cargar SweetAlert2
            loadSweetAlert2(function() {
                console.log('SweetAlert2 cargado para productos');
            });
        });
    </script>
@stop
