@extends('layouts.app')

@section('title', 'Gestión de Productos')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/products/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/products/index-components.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/admin/products/index.js') }}" defer></script>
@endpush

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
@endsection
