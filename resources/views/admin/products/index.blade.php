@extends('layouts.app')

@section('title', 'Gestión de Productos')

@section('content')
<!-- Background Pattern -->
<div class="page-background"></div>

<!-- Main Container -->
<div class="main-container">
    <!-- Floating Header -->
    <div class="floating-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon-wrapper">
                    <div class="header-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="icon-glow"></div>
                </div>
                <div class="header-text">
                    <h1 class="header-title">Gestión de Productos</h1>
                    <p class="header-subtitle">Administra y visualiza todos tus productos con herramientas avanzadas de control</p>
                </div>
            </div>
            <div class="header-actions">
                @can('products.report')
                    <a href="{{ route('admin.products.report') }}" class="btn-glass btn-secondary-glass" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                        <span>Reporte</span>
                        <div class="btn-ripple"></div>
                    </a>
                @endcan
                @can('products.create')
                    <a href="{{ route('admin.products.create') }}" class="btn-glass btn-primary-glass">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nuevo Producto</span>
                        <div class="btn-ripple"></div>
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Stats Dashboard -->
    <div class="stats-dashboard">
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $totalProducts }}</div>
                    <div class="stat-label">Total Productos</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+{{ $totalProducts }}%</span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $currency->symbol }} {{ number_format($totalPurchaseValue, 2) }}</div>
                    <div class="stat-label">Valor de Compra</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+{{ number_format($totalPurchaseValue, 0) }}%</span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $currency->symbol }} {{ number_format($totalSaleValue, 2) }}</div>
                    <div class="stat-label">Valor de Venta</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+{{ number_format($totalSaleValue, 0) }}%</span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $currency->symbol }} {{ number_format($potentialProfit, 2) }}</div>
                    <div class="stat-label">Ganancia Potencial</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>{{ number_format($profitPercentage, 2) }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <div class="filters-header" id="filtersHeader">
            <div class="filters-title">
                <div class="filters-icon">
                    <i class="fas fa-filter"></i>
                </div>
                <div class="filters-text">
                    <h3>Filtros Avanzados</h3>
                    <p>Refina tu búsqueda de productos</p>
                </div>
            </div>
            <button class="filters-toggle" id="filtersToggle">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        
        <div class="filters-content" id="filtersContent">
            <div class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-search"></i>
                        <span>Buscar Producto</span>
                    </label>
                    <div class="filter-input-wrapper">
                        <div class="filter-input-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <input type="text" class="filter-input" id="productSearch" placeholder="Buscar por nombre, código o categoría...">
                        <div class="filter-input-border"></div>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-tag"></i>
                        <span>Categoría</span>
                    </label>
                    <div class="filter-input-wrapper">
                        <div class="filter-input-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <select class="filter-input" id="categoryFilter">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="filter-input-border"></div>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-boxes"></i>
                        <span>Estado de Stock</span>
                    </label>
                    <div class="filter-input-wrapper">
                        <div class="filter-input-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <select class="filter-input" id="stockFilter">
                            <option value="">Todos los estados</option>
                            <option value="low">Stock Bajo</option>
                            <option value="normal">Stock Normal</option>
                            <option value="high">Stock Alto</option>
                        </select>
                        <div class="filter-input-border"></div>
                    </div>
                </div>
            </div>
            
            <div class="filters-actions">
                <div class="filters-status">
                    <span class="status-text">Filtros activos:</span>
                    <div class="active-filters" id="activeFilters">
                        <span class="filter-badge">Todos los productos</span>
                    </div>
                </div>
                <div class="filters-buttons">
                    <button class="btn-modern btn-apply" id="applyFilters">
                        <div class="btn-content">
                            <i class="fas fa-filter"></i>
                            <span>Aplicar Filtros</span>
                        </div>
                        <div class="btn-bg"></div>
                    </button>
                    <button class="btn-modern btn-clear" id="clearFilters">
                        <div class="btn-content">
                            <i class="fas fa-times"></i>
                            <span>Limpiar</span>
                        </div>
                        <div class="btn-bg"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-container">
        <div class="content-card">
            <!-- Content Header -->
            <div class="content-header">
                <div class="content-title">
                    <div class="title-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="title-text">
                        <h3>Lista de Productos</h3>
                        <p>Gestiona y visualiza todos tus productos del sistema</p>
                    </div>
                </div>
                <div class="content-actions">
                    <div class="search-container">
                        <div class="search-wrapper">
                            <div class="search-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <input type="text" placeholder="Buscar productos..." id="searchInput" class="search-input">
                            <div class="search-border"></div>
                        </div>
                    </div>
                    <div class="view-toggles desktop-only">
                        <button class="view-toggle active" data-view="table">
                            <i class="fas fa-table"></i>
                            <span>Tabla</span>
                        </button>
                        <button class="view-toggle" data-view="cards">
                            <i class="fas fa-th-large"></i>
                            <span>Tarjetas</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Content Body -->
            <div class="content-body">
                {{-- Vista de tarjetas (por defecto) --}}
                <div class="desktop-view" id="desktopCardsView">
                    <div class="cards-grid" id="cardsGrid">
                        @foreach ($products as $product)
                            <div class="product-card" data-product-id="{{ $product->id }}" data-search="{{ strtolower($product->name . ' ' . $product->code . ' ' . ($product->category->name ?? '')) }}">
                                <div class="card-header">
                                    <div class="card-avatar">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image">
                                    </div>
                                    <div class="card-badge">
                                        <span>{{ $product->code }}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h4 class="card-title">{{ $product->name }}</h4>
                                    <p class="card-description">{{ Str::limit($product->description, 100) ?? 'Sin descripción' }}</p>
                                    <div class="card-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-tag"></i>
                                            <span>{{ $product->category->name ?? 'Sin categoría' }}</span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-boxes"></i>
                                            <span>Stock: {{ $product->stock }}</span>
                                        </div>
                                    </div>
                                    <div class="card-prices">
                                        <div class="price-item">
                                            <span class="price-label">Compra:</span>
                                            <span class="price-value purchase-price">{{ $currency->symbol }} {{ number_format($product->purchase_price, 2) }}</span>
                                        </div>
                                        <div class="price-item">
                                            <span class="price-label">Venta:</span>
                                            <span class="price-value sale-price">{{ $currency->symbol }} {{ number_format($product->sale_price, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    @can('products.show')
                                        <button type="button" class="card-btn card-btn-view" onclick="showProductDetails({{ $product->id }})" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                            <span>Ver</span>
                                        </button>
                                    @endcan
                                    @can('products.edit')
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="card-btn card-btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                            <span>Editar</span>
                                        </a>
                                    @endcan
                                    @can('products.destroy')
                                        <button type="button" class="card-btn card-btn-delete" onclick="deleteProduct({{ $product->id }}, '{{ $product->name }}')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                            <span>Eliminar</span>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Paginación para tarjetas --}}
                    <div class="custom-pagination">
                        <div class="pagination-info">
                            <span id="cardsPaginationInfo">Mostrando 1-{{ min(12, $products->count()) }} de {{ $products->count() }} registros</span>
                        </div>
                        <div class="pagination-controls">
                            <button id="cardsPrevPage" class="pagination-btn" disabled>
                                <i class="fas fa-chevron-left"></i>
                                Anterior
                            </button>
                            <div id="cardsPageNumbers" class="page-numbers"></div>
                            <button id="cardsNextPage" class="pagination-btn">
                                Siguiente
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Vista de tabla (solo desktop) --}}
                <div class="desktop-view" id="desktopTableView" style="display: none;">
                    <div class="table-container">
                        <table id="productsTable" class="modern-table">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="th-content">
                                            <span>#</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="th-content">
                                            <i class="fas fa-box"></i>
                                            <span>Producto</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="th-content">
                                            <i class="fas fa-tag"></i>
                                            <span>Categoría</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="th-content">
                                            <i class="fas fa-boxes"></i>
                                            <span>Stock</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="th-content">
                                            <i class="fas fa-shopping-cart"></i>
                                            <span>Precio Compra</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="th-content">
                                            <i class="fas fa-cash-register"></i>
                                            <span>Precio Venta</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="th-content">
                                            <i class="fas fa-cogs"></i>
                                            <span>Acciones</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                @foreach ($products as $product)
                                    <tr class="table-row" data-product-id="{{ $product->id }}">
                                        <td>
                                            <div class="row-number">
                                                {{ $loop->iteration }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="product-info">
                                                <div class="product-avatar">
                                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image">
                                                </div>
                                                <div class="product-details">
                                                    <span class="product-name">{{ $product->name }}</span>
                                                    <span class="product-code">{{ $product->code }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="category-info">
                                                <span class="category-text">{{ $product->category->name ?? 'Sin categoría' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="stock-info">
                                                <span class="stock-badge badge badge-{{ $product->stock_status_label === 'Bajo' ? 'danger' : ($product->stock_status_label === 'Normal' ? 'warning' : 'success') }}">
                                                    <i class="fas fa-boxes"></i>
                                                    {{ $product->stock }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="price-info">
                                                <span class="price-text purchase-price">{{ $currency->symbol }} {{ number_format($product->purchase_price, 2) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="price-info">
                                                <span class="price-text sale-price">{{ $currency->symbol }} {{ number_format($product->sale_price, 2) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                @can('products.show')
                                                    <button type="button" class="btn-action btn-view" onclick="showProductDetails({{ $product->id }})" data-toggle="tooltip" title="Ver Detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endcan
                                                @can('products.edit')
                                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-action btn-edit" data-toggle="tooltip" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('products.destroy')
                                                    <button type="button" class="btn-action btn-delete" onclick="deleteProduct({{ $product->id }}, '{{ $product->name }}')" data-toggle="tooltip" title="Eliminar">
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

                    {{-- Paginación personalizada --}}
                    <div class="custom-pagination">
                        <div class="pagination-info">
                            <span id="paginationInfo">Mostrando 1-{{ min(10, $products->count()) }} de {{ $products->count() }} registros</span>
                        </div>
                        <div class="pagination-controls">
                            <button id="prevPage" class="pagination-btn" disabled>
                                <i class="fas fa-chevron-left"></i>
                                Anterior
                            </button>
                            <div id="pageNumbers" class="page-numbers"></div>
                            <button id="nextPage" class="pagination-btn">
                                Siguiente
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Vista móvil (siempre tarjetas) --}}
                <div class="mobile-view">
                    <div class="mobile-cards" id="mobileCards">
                        @foreach ($products as $product)
                            <div class="mobile-card" data-product-id="{{ $product->id }}" data-search="{{ strtolower($product->name . ' ' . $product->code . ' ' . ($product->category->name ?? '')) }}">
                                <div class="mobile-card-header">
                                    <div class="mobile-avatar">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image">
                                    </div>
                                    <div class="mobile-info">
                                        <h4 class="mobile-title">{{ $product->name }}</h4>
                                        <span class="mobile-code">{{ $product->code }}</span>
                                    </div>
                                    <div class="mobile-actions">
                                        @can('products.show')
                                            <button type="button" class="mobile-btn mobile-btn-view" onclick="showProductDetails({{ $product->id }})" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endcan
                                        @can('products.edit')
                                            <a href="{{ route('admin.products.edit', $product->id) }}" class="mobile-btn mobile-btn-edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('products.destroy')
                                            <button type="button" class="mobile-btn mobile-btn-delete" onclick="deleteProduct({{ $product->id }}, '{{ $product->name }}')" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="mobile-card-body">
                                    <p class="mobile-description">{{ Str::limit($product->description, 100) ?? 'Sin descripción' }}</p>
                                    <div class="mobile-meta">
                                        <div class="mobile-category">
                                            <i class="fas fa-tag"></i>
                                            <span>{{ $product->category->name ?? 'Sin categoría' }}</span>
                                        </div>
                                        <div class="mobile-stock">
                                            <i class="fas fa-boxes"></i>
                                            <span>{{ $product->stock }}</span>
                                        </div>
                                    </div>
                                    <div class="mobile-prices">
                                        <div class="mobile-price">
                                            <span class="price-label">Compra:</span>
                                            <span class="price-value">{{ $currency->symbol }} {{ number_format($product->purchase_price, 2) }}</span>
                                        </div>
                                        <div class="mobile-price">
                                            <span class="price-label">Venta:</span>
                                            <span class="price-value">{{ $currency->symbol }} {{ number_format($product->sale_price, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal para mostrar detalles de producto --}}
<div class="modal-overlay" id="showProductModal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-box mr-2"></i>
                Detalles del Producto
            </h3>
            <button type="button" class="modal-close" onclick="closeProductModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-content-grid">
                <div class="modal-content-column">
                    <div class="modal-detail-item">
                        <div class="detail-label">
                            <i class="fas fa-box"></i>
                            Nombre del Producto
                        </div>
                        <div class="detail-value" id="modalProductName">-</div>
                    </div>
                    <div class="modal-detail-item">
                        <div class="detail-label">
                            <i class="fas fa-barcode"></i>
                            Código
                        </div>
                        <div class="detail-value" id="modalProductCode">-</div>
                    </div>
                    <div class="modal-detail-item">
                        <div class="detail-label">
                            <i class="fas fa-tag"></i>
                            Categoría
                        </div>
                        <div class="detail-value" id="modalProductCategory">-</div>
                    </div>
                    <div class="modal-detail-item">
                        <div class="detail-label">
                            <i class="fas fa-align-left"></i>
                            Descripción
                        </div>
                        <div class="detail-value" id="modalProductDescription">-</div>
                    </div>
                </div>
                <div class="modal-content-column">
                    <div class="modal-detail-item">
                        <div class="detail-label">
                            <i class="fas fa-boxes"></i>
                            Stock Actual
                        </div>
                        <div class="detail-value" id="modalProductStock">-</div>
                    </div>
                    <div class="modal-detail-item">
                        <div class="detail-label">
                            <i class="fas fa-shopping-cart"></i>
                            Precio de Compra
                        </div>
                        <div class="detail-value" id="modalProductPurchasePrice">-</div>
                    </div>
                    <div class="modal-detail-item">
                        <div class="detail-label">
                            <i class="fas fa-cash-register"></i>
                            Precio de Venta
                        </div>
                        <div class="detail-value" id="modalProductSalePrice">-</div>
                    </div>
                    <div class="modal-detail-item">
                        <div class="detail-label">
                            <i class="fas fa-calendar"></i>
                            Fecha de Creación
                        </div>
                        <div class="detail-value" id="modalProductCreated">-</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modern btn-secondary" onclick="closeProductModal()">
                <div class="btn-content">
                    <i class="fas fa-times"></i>
                    <span>Cerrar</span>
                </div>
                <div class="btn-bg"></div>
            </button>
        </div>
    </div>
</div>

@push('css')
<style>
    /* Variables CSS */
    :root {
        --primary-color: #667eea;
        --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --info-color: #3b82f6;
        --danger-color: #ef4444;
        --dark-color: #1f2937;
        --light-color: #f8fafc;
        --border-color: #e2e8f0;
        --shadow-light: 0 2px 8px rgba(0,0,0,0.07);
        --shadow-medium: 0 4px 16px rgba(0,0,0,0.12);
        --shadow-heavy: 0 20px 40px rgba(0,0,0,0.1);
    }

    /* Estilos básicos */
    .page-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 30%, rgba(139, 92, 246, 0.08) 0%, transparent 50%),
            radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.06) 0%, transparent 50%),
            radial-gradient(circle at 40% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 50%),
            linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%);
        z-index: -1;
    }

    .page-background::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%236366f1' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3Ccircle cx='10' cy='10' r='1'/%3E%3Ccircle cx='50' cy='10' r='1'/%3E%3Ccircle cx='10' cy='50' r='1'/%3E%3Ccircle cx='50' cy='50' r='1'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        background-size: 60px 60px;
        animation: backgroundFloat 20s ease-in-out infinite;
    }

    @keyframes backgroundFloat {
        0%, 100% { transform: translate(0, 0); }
        25% { transform: translate(-10px, -10px); }
        50% { transform: translate(10px, -5px); }
        75% { transform: translate(-5px, 10px); }
    }

    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1.5rem;
        position: relative;
        z-index: 1;
    }

    .floating-header {
        position: relative;
        width: 100%;
        padding: 1rem 1.5rem;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        z-index: 10;
        margin-bottom: 1.5rem;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-icon-wrapper {
        position: relative;
        width: 48px;
        height: 48px;
    }

    .header-icon {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.9) 0%, rgba(59, 130, 246, 0.9) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.4rem;
        position: relative;
        z-index: 1;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
    }

    .icon-glow {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 120%;
        height: 120%;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.2) 0%, transparent 70%);
        border-radius: 50%;
        opacity: 0.6;
        z-index: -1;
        animation: pulse 3s ease-in-out infinite;
    }

    .header-text {
        flex: 1;
    }

    .header-title {
        font-size: 1.8rem;
        font-weight: 700;
        background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
        line-height: 1.2;
    }

    .header-subtitle {
        font-size: 0.9rem;
        color: #64748b;
        margin-top: 0.25rem;
        font-weight: 500;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
    }

    .btn-glass {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.625rem 1.25rem;
        border: 1px solid transparent;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        backdrop-filter: blur(10px);
    }

    .btn-glass:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .btn-secondary-glass {
        background: rgba(255, 255, 255, 0.8);
        border-color: rgba(226, 232, 240, 0.6);
        color: #64748b;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .btn-secondary-glass:hover {
        background: rgba(248, 250, 252, 0.9);
        border-color: rgba(203, 213, 224, 0.8);
        color: #475569;
    }

    .btn-primary-glass {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.9) 0%, rgba(59, 130, 246, 0.9) 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
    }

    .btn-primary-glass:hover {
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.95) 0%, rgba(37, 99, 235, 0.95) 100%);
        color: white;
        box-shadow: 0 8px 30px rgba(139, 92, 246, 0.4);
    }

    .btn-ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        opacity: 0.6;
        transition: transform 0.6s ease-out, opacity 0.6s ease-out;
    }

    .btn-glass:hover .btn-ripple {
        transform: scale(10);
        opacity: 0;
    }

    .stats-dashboard {
        margin-bottom: 1.5rem;
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    .stat-card {
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    .stat-primary {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(99, 102, 241, 0.05) 100%);
        border-color: rgba(139, 92, 246, 0.2);
    }

    .stat-primary::before {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(99, 102, 241, 0.08) 100%);
    }

    .stat-success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .stat-success::before {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.08) 100%);
    }

    .stat-warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.05) 100%);
        border-color: rgba(245, 158, 11, 0.2);
    }

    .stat-warning::before {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.08) 100%);
    }

    .stat-info {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(29, 78, 216, 0.05) 100%);
        border-color: rgba(59, 130, 246, 0.2);
    }

    .stat-info::before {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(29, 78, 216, 0.08) 100%);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.4rem;
        position: relative;
        z-index: 2;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .stat-primary .stat-icon {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.9) 0%, rgba(99, 102, 241, 0.9) 100%);
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
    }

    .stat-success .stat-icon {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.9) 0%, rgba(5, 150, 105, 0.9) 100%);
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
    }

    .stat-warning .stat-icon {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.9) 0%, rgba(217, 119, 6, 0.9) 100%);
        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
    }

    .stat-info .stat-icon {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.9) 0%, rgba(29, 78, 216, 0.9) 100%);
        box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
    }

    .stat-glow {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 120%;
        height: 120%;
        border-radius: 50%;
        opacity: 0.3;
        z-index: 1;
        animation: pulse 3s ease-in-out infinite;
    }

    .stat-primary .stat-glow {
        background: radial-gradient(circle, rgba(139, 92, 246, 0.2) 0%, transparent 70%);
    }

    .stat-success .stat-glow {
        background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%);
    }

    .stat-warning .stat-glow {
        background: radial-gradient(circle, rgba(245, 158, 11, 0.2) 0%, transparent 70%);
    }

    .stat-info .stat-glow {
        background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, transparent 70%);
    }

    .stat-content {
        flex: 1;
        z-index: 2;
        position: relative;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark-color);
        margin: 0;
        line-height: 1;
    }

    .stat-label {
        color: #64748b;
        font-size: 0.85rem;
        margin: 0.25rem 0;
        font-weight: 600;
    }

    .stat-trend {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.25rem;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        backdrop-filter: blur(10px);
        width: fit-content;
    }

    .stat-primary .stat-trend {
        color: rgba(139, 92, 246, 0.9);
        background: rgba(139, 92, 246, 0.1);
    }

    .stat-success .stat-trend {
        color: rgba(16, 185, 129, 0.9);
        background: rgba(16, 185, 129, 0.1);
    }

    .stat-warning .stat-trend {
        color: rgba(245, 158, 11, 0.9);
        background: rgba(245, 158, 11, 0.1);
    }

    .stat-info .stat-trend {
        color: rgba(59, 130, 246, 0.9);
        background: rgba(59, 130, 246, 0.1);
    }

    /* Filtros */
    .filters-section {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.3);
        margin-bottom: 1.5rem;
        padding: 1.5rem;
    }

    .filters-header {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.9) 0%, rgba(59, 130, 246, 0.9) 100%);
        color: white;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 16px;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
    }

    .filters-header:hover {
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }

    .filters-title {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-weight: 600;
        font-size: 1.25rem;
    }

    .filters-icon {
        font-size: 1.5rem;
    }

    .filters-text h3 {
        font-size: 1.25rem;
        margin: 0;
    }

    .filters-text p {
        font-size: 0.9rem;
        color: #f7f8f8;
        margin: 0.5rem 0 0 0;
    }

    .filters-toggle {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        padding: 0.5rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1.25rem;
    }

    .filters-toggle:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.05);
    }

    .filters-content {
        padding: 0;
        display: none;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .filters-content.show {
        display: block;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .filter-group {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        padding: 1.5rem;
        border-radius: 16px;
        border: 1px solid #bae6fd;
    }

    .filter-label {
        color: #0369a1;
        font-weight: 600;
        margin-bottom: 1rem;
        display: block;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .filter-label i {
        color: var(--primary-color);
        font-size: 1rem;
        background: rgba(102, 126, 234, 0.1);
        padding: 0.5rem;
        border-radius: 8px;
    }

    .filter-input-wrapper {
        position: relative;
    }

    .filter-input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 0.9rem;
    }

    .filter-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: white;
        transition: all 0.3s;
        font-size: 0.9rem;
        color: var(--dark-color);
    }

    .filter-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .filter-input-border {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--primary-color);
        border-radius: 2px;
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .filter-input:focus + .filter-input-border {
        transform: scaleX(1);
    }

    .filters-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1.5rem;
        border-top: 2px solid #e2e8f0;
    }

    .filters-status {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .status-text {
        color: #64748b;
        font-weight: 600;
    }

    .filter-badge {
        background: var(--gradient-primary);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .filters-buttons {
        display: flex;
        gap: 1rem;
    }

    .btn-modern {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border: 1px solid transparent;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: var(--shadow-medium);
        text-decoration: none;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-heavy);
    }

    .btn-apply {
        background: var(--gradient-primary);
        border-color: transparent;
    }

    .btn-apply:hover {
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }

    .btn-clear {
        background: white;
        border-color: #e2e8f0;
        color: #64748b;
    }

    .btn-clear:hover {
        background: #f8fafc;
        border-color: #cbd5e0;
        color: #4a5568;
    }

    .btn-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        border-radius: 12px;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }

    .btn-modern:hover .btn-bg {
        opacity: 1;
    }

    /* Tarjeta Moderna */
    .content-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .content-header {
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.8) 0%, rgba(226, 232, 240, 0.6) 100%);
        padding: 1.5rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.3);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        backdrop-filter: blur(10px);
    }

    .content-title {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .title-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.9) 0%, rgba(59, 130, 246, 0.9) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.4rem;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
    }

    .title-text h3 {
        color: var(--dark-color);
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }

    .title-text p {
        color: #64748b;
        margin: 0.25rem 0 0 0;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .content-actions {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .search-container {
        flex: 1;
        max-width: 400px;
    }

    .search-wrapper {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 0.9rem;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: white;
        transition: all 0.3s;
        font-size: 0.9rem;
        color: var(--dark-color);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .search-border {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--primary-color);
        border-radius: 2px;
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .search-input:focus + .search-border {
        transform: scaleX(1);
    }

    .view-toggles {
        display: flex;
        gap: 0.5rem;
    }

    .desktop-only {
        display: flex;
    }

    .view-toggle {
        background: white;
        color: #64748b;
        border: 2px solid #e2e8f0;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .view-toggle.active {
        background: var(--gradient-primary);
        color: white;
        border-color: var(--primary-color);
    }

    .view-toggle:hover:not(.active) {
        transform: translateY(-2px);
        box-shadow: var(--shadow-light);
    }

    .content-body {
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.5) 0%, rgba(248, 250, 252, 0.3) 100%);
        backdrop-filter: blur(10px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-container {
            padding: 1rem;
        }

        .floating-header {
            position: relative;
            top: auto;
            margin-bottom: 1rem;
            padding: 1rem;
        }

        .header-content {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .header-left {
            flex-direction: column;
            gap: 0.75rem;
        }

        .header-icon-wrapper {
            width: 40px;
            height: 40px;
        }

        .header-icon {
            font-size: 1.2rem;
        }

        .header-title {
            font-size: 1.5rem;
        }

        .header-subtitle {
            font-size: 0.85rem;
        }

        .header-actions {
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn-glass {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }

        .stats-dashboard {
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .stat-card {
            padding: 1rem;
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }

        .stat-value {
            font-size: 1.5rem;
        }

        .stat-label {
            font-size: 0.75rem;
        }

        .stat-trend {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }

        .filters-section {
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .filters-header {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }

        .filters-title {
            font-size: 1rem;
        }

        .filters-text h3 {
            font-size: 1rem;
        }

        .filters-text p {
            font-size: 0.8rem;
        }

        .filters-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .filter-group {
            padding: 1rem;
        }

        .filters-actions {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }

        .filters-buttons {
            justify-content: center;
        }

        .content-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
        }

        .content-title {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }

        .title-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }

        .title-text h3 {
            font-size: 1.25rem;
        }

        .title-text p {
            font-size: 0.8rem;
        }

        .content-actions {
            width: 100%;
            flex-direction: column;
            gap: 1rem;
        }

        .search-container {
            max-width: none;
            width: 100%;
        }

        .search-input {
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            font-size: 0.9rem;
        }

        .view-toggles {
            justify-content: center;
            width: 100%;
        }

        .desktop-only {
            display: none !important;
        }

        .view-toggle {
            flex: 1;
            justify-content: center;
            padding: 0.75rem;
            font-size: 0.85rem;
        }

        .content-body {
            padding: 1rem;
        }
    }

    @media (max-width: 480px) {
        .main-container {
            padding: 0.75rem;
        }

        .floating-header {
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .header-icon-wrapper {
            width: 36px;
            height: 36px;
        }

        .header-icon {
            font-size: 1rem;
        }

        .header-title {
            font-size: 1.25rem;
        }

        .header-subtitle {
            font-size: 0.8rem;
        }

        .btn-glass {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
        }

        .stats-dashboard {
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .stat-card {
            padding: 0.75rem;
            gap: 0.5rem;
        }

        .stat-icon {
            width: 36px;
            height: 36px;
            font-size: 1rem;
        }

        .stat-value {
            font-size: 1.25rem;
        }

        .stat-label {
            font-size: 0.7rem;
        }

        .stat-trend {
            font-size: 0.65rem;
            padding: 0.15rem 0.3rem;
        }

        .filters-section {
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .filters-header {
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.75rem;
        }

        .filters-title {
            font-size: 0.9rem;
        }

        .filters-text h3 {
            font-size: 0.9rem;
        }

        .filters-text p {
            font-size: 0.75rem;
        }

        .filter-group {
            padding: 0.75rem;
        }

        .filter-label {
            font-size: 0.8rem;
        }

        .filter-input {
            padding: 0.5rem 0.75rem 0.5rem 2rem;
            font-size: 0.8rem;
        }

        .filter-input-icon {
            font-size: 0.75rem;
        }

        .filter-input-border {
            height: 1px;
        }

        .filters-actions {
            padding-top: 0.75rem;
        }

        .filters-status {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .status-text {
            font-size: 0.8rem;
        }

        .filter-badge {
            font-size: 0.75rem;
        }

        .filters-buttons {
            width: 100%;
            justify-content: center;
        }

        .btn-modern {
            width: 100%;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }

        .content-card {
            border-radius: 16px;
        }

        .content-header {
            padding: 0.75rem;
        }

        .content-title {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }

        .title-icon {
            width: 36px;
            height: 36px;
            font-size: 1rem;
        }

        .title-text h3 {
            font-size: 1.1rem;
        }

        .title-text p {
            font-size: 0.75rem;
        }

        .content-actions {
            gap: 0.75rem;
        }

        .search-input {
            padding: 0.5rem 0.75rem 0.5rem 2.5rem;
            font-size: 0.8rem;
        }

        .view-toggle {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
        }

        .content-body {
            padding: 0.75rem;
        }
    }

    /* Tabla Moderna */
    .table-container {
        overflow-x: auto;
        border-radius: 16px;
        box-shadow: var(--shadow-light);
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }

    .modern-table thead {
        background: var(--gradient-primary);
    }

    .modern-table th {
        padding: 1rem;
        text-align: left;
        border: none;
        position: relative;
    }

    .th-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: white;
        font-weight: 600;
        font-size: 1rem;
    }

    .modern-table td {
        padding: 1.25rem;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .table-row {
        transition: background-color 0.2s ease;
    }

    .table-row:hover {
        background: var(--light-color);
        transform: scale(1.01);
        box-shadow: var(--shadow-light);
    }

    .row-number {
        width: 45px;
        height: 45px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1rem;
        box-shadow: var(--shadow-light);
    }

    .product-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .product-avatar {
        width: 45px;
        height: 45px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
        transition: transform 0.3s ease;
    }

    .product-image:hover {
        transform: scale(1.1);
    }

    .product-details {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .product-name {
        font-weight: 700;
        color: var(--dark-color);
        font-size: 1rem;
    }

    .product-code {
        color: #718096;
        font-size: 0.85rem;
        font-weight: 500;
        font-family: 'Courier New', monospace;
    }

    .category-info {
        max-width: 200px;
    }

    .category-text {
        color: #4a5568;
        font-size: 0.95rem;
        line-height: 1.5;
        font-weight: 500;
    }

    .stock-info {
        text-align: center;
    }

    .stock-badge {
        padding: 0.375rem 0.625rem;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
        border-radius: 12px;
    }

    .price-info {
        text-align: right;
    }

    .price-text {
        font-weight: 600;
        font-size: 0.9rem;
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
        gap: 0.75rem;
        justify-content: center;
    }

    .btn-action {
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
        text-decoration: none;
        box-shadow: var(--shadow-light);
    }

    .btn-view {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
    }

    .btn-edit {
        background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
        color: white;
    }

    .btn-delete {
        background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
        color: white;
    }

    .btn-action:hover {
        transform: scale(1.15);
        box-shadow: var(--shadow-medium);
    }

    /* Tarjetas de Productos */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .product-card {
        background: white;
        border-radius: 16px;
        box-shadow: var(--shadow-light);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-medium);
    }

    .product-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--gradient-primary);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .product-card:hover::before {
        opacity: 1;
    }

    .card-header {
        background: var(--gradient-primary);
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .card-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }

    .card-avatar {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        z-index: 1;
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255,255,255,0.2);
    }

    .card-avatar .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .card-badge {
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        position: relative;
        z-index: 1;
        backdrop-filter: blur(10px);
        font-family: 'Courier New', monospace;
    }

    .card-body {
        padding: 1.5rem;
        background: white;
    }

    .card-title {
        color: var(--dark-color);
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0 0 0.75rem 0;
        line-height: 1.3;
    }

    .card-description {
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.5;
        margin: 0 0 1rem 0;
        font-weight: 500;
    }

    .card-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        margin-bottom: 1rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .meta-item i {
        color: var(--primary-color);
        font-size: 0.9rem;
        background: rgba(102, 126, 234, 0.1);
        padding: 0.4rem;
        border-radius: 6px;
    }

    .card-prices {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding: 0.75rem;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-radius: 10px;
        border: 1px solid #bae6fd;
        margin-bottom: 1rem;
    }

    .price-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .price-label {
        color: #0369a1;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .price-value {
        font-weight: 700;
        font-size: 0.9rem;
    }

    .purchase-price {
        color: var(--info-color);
    }

    .sale-price {
        color: var(--success-color);
    }

    .card-actions {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        border-top: 1px solid #e2e8f0;
    }

    .card-btn {
        flex: 1;
        min-width: 80px;
        padding: 0.75rem 1rem;
        border: none;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .card-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .card-btn:hover::before {
        left: 100%;
    }

    .card-btn-view {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
    }

    .card-btn-edit {
        background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
        color: white;
    }

    .card-btn-delete {
        background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
        color: white;
    }

    .card-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }

    /* Vista Móvil */
    .mobile-view {
        display: none;
    }

    @media (max-width: 768px) {
        .desktop-view {
            display: none !important;
        }

        .mobile-view {
            display: block;
        }

        .mobile-cards {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .mobile-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-light);
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .mobile-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .mobile-card-header {
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--gradient-primary);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .mobile-card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 8s ease-in-out infinite;
        }

        .mobile-avatar {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.2);
        }

        .mobile-avatar .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .mobile-info {
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .mobile-title {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.3;
        }

        .mobile-code {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-top: 0.2rem;
            display: block;
            font-family: 'Courier New', monospace;
        }

        .mobile-actions {
            display: flex;
            gap: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .mobile-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            text-decoration: none;
            backdrop-filter: blur(10px);
        }

        .mobile-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }

        .mobile-card-body {
            padding: 1rem;
            background: white;
        }

        .mobile-description {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.4;
            margin: 0 0 0.75rem 0;
            font-weight: 500;
        }

        .mobile-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            margin-bottom: 0.75rem;
        }

        .mobile-category, .mobile-stock {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .mobile-category i, .mobile-stock i {
            color: var(--primary-color);
            font-size: 0.8rem;
        }

        .mobile-prices {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 0.75rem;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 10px;
            border: 1px solid #bae6fd;
        }

        .mobile-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-label {
            color: #0369a1;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .price-value {
            font-weight: 700;
            font-size: 0.8rem;
        }
    }

    /* Paginación */
    .custom-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 2rem;
        background: white;
        border-top: 1px solid var(--border-color);
        border-radius: 0 0 24px 24px;
    }

    .pagination-info {
        color: #64748b;
        font-size: 1rem;
        font-weight: 600;
    }

    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .pagination-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.25rem;
        border: 2px solid var(--border-color);
        background: white;
        color: #64748b;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
        font-weight: 600;
    }

    .pagination-btn:hover:not(:disabled) {
        background: var(--light-color);
        border-color: var(--primary-color);
        color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: var(--shadow-light);
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    .page-numbers {
        display: flex;
        gap: 0.5rem;
    }

    .page-number {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--border-color);
        background: white;
        color: #64748b;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
        font-weight: 600;
    }

    .page-number:hover {
        background: var(--light-color);
        border-color: var(--primary-color);
        color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: var(--shadow-light);
    }

    .page-number.active {
        background: var(--gradient-primary);
        color: white;
        border-color: var(--primary-color);
        box-shadow: var(--shadow-medium);
    }

    /* Modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        padding: 1rem;
    }

    .modal-container {
        background: white;
        border-radius: 24px;
        box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
        width: 90%;
        max-width: 800px;
        max-height: 90%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-header {
        background: var(--gradient-primary);
        color: white;
        padding: 2rem 2.5rem;
        position: relative;
        overflow: hidden;
    }

    .modal-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
    }

    .modal-title {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 1.5rem;
        font-weight: 700;
        position: relative;
        z-index: 1;
    }

    .modal-title i {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.75rem;
        border-radius: 12px;
        font-size: 1.2rem;
    }

    .modal-close {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 0.75rem;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        backdrop-filter: blur(10px);
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .modal-body {
        padding: 2.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        overflow-y: auto;
        flex-grow: 1;
    }

    .modal-content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2.5rem;
    }

    .modal-content-column {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .modal-detail-item {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(102, 126, 234, 0.1);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .modal-detail-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--gradient-primary);
        border-radius: 0 2px 2px 0;
    }

    .modal-detail-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .detail-label {
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-label i {
        color: var(--primary-color);
        font-size: 1rem;
        background: rgba(102, 126, 234, 0.1);
        padding: 0.5rem;
        border-radius: 8px;
    }

    .detail-value {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--dark-color);
        line-height: 1.4;
        word-break: break-word;
    }

    .modal-footer {
        padding: 2rem 2.5rem;
        background: white;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }

    .btn-secondary {
        background: white;
        color: #64748b;
        border: 2px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e0;
        color: #4a5568;
    }

    /* Responsive adicional */
    @media (max-width: 768px) {
        .custom-pagination {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
            padding: 1rem;
        }

        .pagination-info {
            font-size: 0.9rem;
        }

        .pagination-controls {
            gap: 0.5rem;
        }

        .pagination-btn {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .page-number {
            width: 36px;
            height: 36px;
            font-size: 0.85rem;
        }

        .cards-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .product-card {
            border-radius: 12px;
        }

        .card-header {
            padding: 0.75rem;
        }

        .card-avatar {
            width: 40px;
            height: 40px;
        }

        .card-badge {
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
        }

        .card-body {
            padding: 0.75rem;
        }

        .card-title {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .card-description {
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
        }

        .card-meta {
            padding: 0.5rem;
            gap: 0.75rem;
        }

        .meta-item {
            font-size: 0.75rem;
            gap: 0.4rem;
        }

        .meta-item i {
            font-size: 0.8rem;
            padding: 0.3rem;
        }

        .card-prices {
            padding: 0.5rem;
        }

        .price-label {
            font-size: 0.75rem;
        }

        .price-value {
            font-size: 0.8rem;
        }

        .card-actions {
            flex-direction: column;
            gap: 0.4rem;
            padding: 0.75rem;
        }

        .card-btn {
            min-width: auto;
            padding: 0.6rem 0.8rem;
            font-size: 0.8rem;
            gap: 0.3rem;
        }

        .modal-container {
            max-width: 95%;
            border-radius: 16px;
        }

        .modal-header {
            padding: 1rem 1.5rem;
        }

        .modal-title {
            font-size: 1.1rem;
        }

        .modal-body {
            padding: 1rem;
        }

        .modal-content-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .modal-detail-item {
            padding: 1rem;
        }

        .detail-label {
            font-size: 0.8rem;
        }

        .detail-value {
            font-size: 1rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .custom-pagination {
            padding: 0.75rem;
        }

        .pagination-info {
            font-size: 0.8rem;
        }

        .pagination-btn {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }

        .page-number {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }

        .product-card {
            border-radius: 10px;
        }

        .card-header {
            padding: 0.5rem;
        }

        .card-avatar {
            width: 32px;
            height: 32px;
        }

        .card-badge {
            padding: 0.3rem 0.6rem;
            font-size: 0.7rem;
        }

        .card-body {
            padding: 0.5rem;
        }

        .card-title {
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
        }

        .card-description {
            font-size: 0.75rem;
            margin-bottom: 0.6rem;
        }

        .card-meta {
            padding: 0.4rem;
            gap: 0.6rem;
        }

        .meta-item {
            font-size: 0.7rem;
            gap: 0.3rem;
        }

        .meta-item i {
            font-size: 0.75rem;
            padding: 0.25rem;
        }

        .card-prices {
            padding: 0.4rem;
        }

        .price-label {
            font-size: 0.7rem;
        }

        .price-value {
            font-size: 0.75rem;
        }

        .card-actions {
            padding: 0.5rem;
        }

        .card-btn {
            padding: 0.5rem 0.6rem;
            font-size: 0.75rem;
            gap: 0.25rem;
        }

        .modal-container {
            max-width: 98%;
            border-radius: 12px;
        }

        .modal-header {
            padding: 0.75rem 1rem;
        }

        .modal-title {
            font-size: 1rem;
        }

        .modal-body {
            padding: 0.75rem;
        }

        .modal-detail-item {
            padding: 0.75rem;
        }

        .detail-label {
            font-size: 0.75rem;
        }

        .detail-value {
            font-size: 0.9rem;
        }

        .modal-footer {
            padding: 0.75rem 1rem;
        }
    }

    @media (min-width: 1200px) {
        .modal-container {
            max-width: 900px;
        }

        .modal-content-grid {
            gap: 3rem;
        }

        .modal-detail-item {
            padding: 2rem;
        }

        .detail-value {
            font-size: 1.2rem;
        }
    }

    @media (min-width: 1400px) {
        .modal-container {
            max-width: 1000px;
        }
    }

    /* Animaciones */
    @keyframes float {
        0%, 100% {
            transform: translate(0, 0) rotate(0deg);
        }
        33% {
            transform: translate(10px, -10px) rotate(120deg);
        }
        66% {
            transform: translate(-5px, 5px) rotate(240deg);
        }
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 0.3;
            transform: translate(-50%, -50%) scale(1);
        }
        50% {
            opacity: 0.6;
            transform: translate(-50%, -50%) scale(1.05);
        }
    }
</style>
@endpush

@push('js')
<script>
    // Variables globales
    let currentViewMode = 'cards';
    let currentPage = 1;
    const itemsPerPage = 10;
    const cardsPerPage = 12;
    let allProducts = [];
    let filteredProducts = [];

    // Inicializar la página
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Productos page loaded');
        initializeProductsPage();
        initializeEventListeners();
    });

    // Inicializar la página de productos
    function initializeProductsPage() {
        console.log('Initializing products page...');
        
        // Cargar modo de vista guardado
        const savedViewMode = localStorage.getItem('productsViewMode');
        if (savedViewMode && (savedViewMode === 'table' || savedViewMode === 'cards')) {
            currentViewMode = savedViewMode;
            changeViewMode(savedViewMode);
        } else {
            // Modo por defecto: tarjetas
            changeViewMode('cards');
        }

        // Obtener todos los productos
        getAllProducts();
        
        // Mostrar primera página
        showPage(1);
    }

    // Obtener todas las categorías
    function getAllProducts() {
        const tableRows = document.querySelectorAll('#productsTableBody tr');
        const productCards = document.querySelectorAll('.product-card');
        const mobileCards = document.querySelectorAll('.mobile-card');
        
        allProducts = [];
        
        // Procesar filas de tabla
        tableRows.forEach((row, index) => {
            const productName = row.querySelector('.product-name').textContent.trim();
            const productCode = row.querySelector('.product-code').textContent.trim();
            const categoryText = row.querySelector('.category-text').textContent.trim();
            
            allProducts.push({
                element: row,
                cardElement: productCards[index],
                mobileElement: mobileCards[index],
                data: {
                    id: row.dataset.productId,
                    name: productName,
                    code: productCode,
                    category: categoryText
                }
            });
        });
        
        filteredProducts = [...allProducts];
        console.log('Products loaded:', allProducts.length);
    }

    // Cambiar modo de vista
    function changeViewMode(mode) {
        console.log('Changing view mode to:', mode);
        currentViewMode = mode;
        localStorage.setItem('productsViewMode', mode);
        
        // Actualizar botones de vista
        document.querySelectorAll('.view-toggle').forEach(btn => {
            btn.classList.remove('active');
        });
        const activeButton = document.querySelector(`[data-view="${mode}"]`);
        if (activeButton) {
            activeButton.classList.add('active');
        }
        
        // Mostrar/ocultar vistas
        const tableView = document.getElementById('desktopTableView');
        const cardsView = document.getElementById('desktopCardsView');
        
        if (mode === 'table') {
            tableView.style.display = 'block';
            cardsView.style.display = 'none';
        } else {
            tableView.style.display = 'none';
            cardsView.style.display = 'block';
        }
        
        // Reiniciar paginación
        currentPage = 1;
        showPage(1);
    }

    // Mostrar página específica
    function showPage(page) {
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        
        // Ocultar todas las filas/tarjetas
        document.querySelectorAll('#productsTableBody tr').forEach(row => row.style.display = 'none');
        document.querySelectorAll('.product-card').forEach(card => card.style.display = 'none');
        document.querySelectorAll('.mobile-card').forEach(card => card.style.display = 'none');
        
        // Mostrar solo los elementos de la página actual
        filteredProducts.slice(startIndex, endIndex).forEach((product, index) => {
            if (product.element) product.element.style.display = 'table-row';
            if (product.cardElement) product.cardElement.style.display = 'block';
            if (product.mobileElement) product.mobileElement.style.display = 'block';
            
            // Actualizar números de fila
            if (product.element) {
                product.element.querySelector('.row-number').textContent = startIndex + index + 1;
            }
        });
        
        // Actualizar información de paginación
        updatePaginationInfo(page, filteredProducts.length);
        updatePaginationControls(page, Math.ceil(filteredProducts.length / itemsPerPage));
    }

    // Actualizar información de paginación
    function updatePaginationInfo(currentPage, totalItems) {
        const startItem = (currentPage - 1) * itemsPerPage + 1;
        const endItem = Math.min(currentPage * itemsPerPage, totalItems);
        document.getElementById('paginationInfo').textContent = `Mostrando ${startItem}-${endItem} de ${totalItems} registros`;
    }

    // Actualizar controles de paginación
    function updatePaginationControls(currentPage, totalPages) {
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        const pageNumbers = document.getElementById('pageNumbers');
        
        // Habilitar/deshabilitar botones
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
        
        // Generar números de página
        let pageNumbersHTML = '';
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            pageNumbersHTML += `
                <button class="page-number ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">
                    ${i}
                </button>
            `;
        }
        
        pageNumbers.innerHTML = pageNumbersHTML;
    }

    // Ir a página específica
    function goToPage(page) {
        currentPage = page;
        showPage(page);
    }

    // Función de búsqueda
    function filterProducts(searchTerm) {
        const searchLower = searchTerm.toLowerCase().trim();
        
        if (!searchLower) {
            filteredProducts = [...allProducts];
        } else {
            filteredProducts = allProducts.filter(product => {
                const nameMatch = product.data.name.toLowerCase().includes(searchLower);
                const codeMatch = product.data.code.toLowerCase().includes(searchLower);
                const categoryMatch = product.data.category.toLowerCase().includes(searchLower);
                return nameMatch || codeMatch || categoryMatch;
            });
        }
        
        currentPage = 1;
        showPage(1);
    }

    // Mostrar detalles de producto
    async function showProductDetails(productId) {
        console.log('Showing product details for ID:', productId);
        
        try {
            const response = await fetch(`/products/${productId}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                // Llenar datos en el modal
                document.getElementById('modalProductName').textContent = data.product.name;
                document.getElementById('modalProductCode').textContent = data.product.code;
                document.getElementById('modalProductCategory').textContent = data.product.category;
                document.getElementById('modalProductDescription').textContent = data.product.description || 'Sin descripción';
                document.getElementById('modalProductStock').textContent = data.product.stock;
                document.getElementById('modalProductPurchasePrice').textContent = data.product.purchase_price;
                document.getElementById('modalProductSalePrice').textContent = data.product.sale_price;
                document.getElementById('modalProductCreated').textContent = data.product.created_at;
                
                // Mostrar modal
                document.getElementById('showProductModal').style.display = 'flex';
            } else {
                showAlert('Error', 'No se pudieron obtener los datos del producto', 'error');
            }
        } catch (error) {
            console.error('Error fetching product details:', error);
            showAlert('Error', 'No se pudieron obtener los datos del producto', 'error');
        }
    }

    // Cerrar modal de producto
    function closeProductModal() {
        document.getElementById('showProductModal').style.display = 'none';
    }

    // Eliminar producto
    function deleteProduct(productId, productName) {
        console.log('Deleting product:', productId, productName);
        
        showConfirmDialog(
            '¿Estás seguro?',
            `¿Deseas eliminar el producto <strong>${productName}</strong>?<br><small class="text-muted">Esta acción no se puede revertir</small>`,
            'warning',
            () => performDeleteProduct(productId)
        );
    }

    // Realizar eliminación de producto
    async function performDeleteProduct(productId) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch(`/products/delete/${productId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                showAlert('¡Eliminado!', data.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert('Error', data.message, 'error');
            }
        } catch (error) {
            console.error('Error deleting product:', error);
            showAlert('Error', 'No se pudo eliminar el producto', 'error');
        }
    }

    // Mostrar diálogo de confirmación
    function showConfirmDialog(title, html, icon, onConfirm) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                html: html,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: '<i class="fas fa-trash mr-2"></i>Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    onConfirm();
                }
            });
        } else {
            if (confirm(title)) {
                onConfirm();
            }
        }
    }

    // Mostrar alerta
    function showAlert(title, text, icon) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                confirmButtonText: 'Entendido'
            });
        } else {
            alert(`${title}: ${text}`);
        }
    }

    // Inicializar event listeners
    function initializeEventListeners() {
        console.log('Initializing event listeners...');
        
        // Toggle de filtros
        const filtersToggle = document.getElementById('filtersToggle');
        const filtersContent = document.getElementById('filtersContent');
        
        if (filtersToggle && filtersContent) {
            filtersToggle.addEventListener('click', function() {
                filtersContent.classList.toggle('show');
                const icon = this.querySelector('i');
                if (filtersContent.classList.contains('show')) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            });
        }
        
        // Búsqueda en tiempo real
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value;
                filterProducts(searchTerm);
            });
        }
        
        // Búsqueda en filtros
        const productSearch = document.getElementById('productSearch');
        if (productSearch) {
            productSearch.addEventListener('keyup', function() {
                const searchTerm = this.value;
                filterProducts(searchTerm);
            });
        }
        
        // Botones de vista
        document.querySelectorAll('.view-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const viewMode = this.dataset.view;
                changeViewMode(viewMode);
            });
        });
        
        // Paginación
        document.getElementById('prevPage').addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        });
        
        document.getElementById('nextPage').addEventListener('click', function() {
            const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });
        
        // Aplicar filtros
        document.getElementById('applyFilters').addEventListener('click', function() {
            const searchTerm = document.getElementById('productSearch').value;
            filterProducts(searchTerm);
        });
        
        // Limpiar filtros
        document.getElementById('clearFilters').addEventListener('click', function() {
            document.getElementById('productSearch').value = '';
            filterProducts('');
        });
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('showProductModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeProductModal();
            }
        });
        
        console.log('Event listeners initialized');
    }
</script>
@endpush

@endsection
