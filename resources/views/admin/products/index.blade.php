@extends('layouts.app')

@section('title', 'Gestión de Productos')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/products/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/products/index-components.css') }}">
@endpush

@push('js')
    <script>
        // Pasar datos de categorías al frontend
        window.categoriesData = @js($categories);

        // Pasar información de moneda al frontend
        window.currencyData = {
            symbol: @js($currency->symbol),
            code: @js($currency->code),
            name: @js($currency->name)
        };
    </script>
    <script src="{{ asset('js/admin/products/index.js') }}" defer></script>
@endpush

@section('content')
    <!-- Background Pattern -->
    <div class="page-background"></div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Hero Section de Productos -->
        <div
            class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-xl shadow-lg mb-6 group">
            <!-- Patrón de Fondo Decorativo -->
            <div class="absolute inset-0 bg-black bg-opacity-10">
                <div class="absolute inset-0 bg-gradient-to-r from-white/5 to-transparent"></div>
                <div
                    class="absolute top-0 left-0 w-48 h-48 bg-white rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-blob">
                </div>
                <div
                    class="absolute top-0 right-0 w-48 h-48 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-blob animation-delay-2000">
                </div>
                <div
                    class="absolute -bottom-4 left-16 w-48 h-48 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-blob animation-delay-4000">
                </div>
            </div>

            <div class="relative px-4 py-3 sm:py-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex-1 lg:flex-shrink-0">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/30 shadow-inner"
                                    style="background-color: rgba(255, 255, 255, 0.2) !important;">
                                    <i class="fas fa-boxes text-lg sm:text-2xl text-white"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <h1 class="text-xl sm:text-3xl font-bold text-white leading-tight whitespace-nowrap">
                                    Gestión de Productos
                                </h1>
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-3 mt-4 lg:mt-0 lg:flex-shrink-0 lg:justify-end">
                        @if ($permissions['products.report'])
                            <a href="{{ route('admin.products.report') }}"
                                class="inline-flex items-center justify-center px-4 py-2.5 bg-white bg-opacity-10 hover:bg-opacity-20 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-20 backdrop-blur-sm group/btn text-center"
                                target="_blank">
                                <i
                                    class="fas fa-file-pdf text-base mr-2 group-hover/btn:scale-110 transition-transform"></i>
                                <span class="text-xs sm:text-sm">Reporte</span>
                            </a>
                        @endif

                        @if ($permissions['products.create'])
                            <a href="{{ route('admin.products.create') }}"
                                class="inline-flex items-center justify-center px-4 py-2.5 bg-white text-indigo-600 font-bold rounded-xl hover:bg-opacity-90 transition-all duration-200 shadow-lg transform hover:-translate-y-0.5 active:translate-y-0 text-center">
                                <i class="fas fa-plus text-base mr-2"></i>
                                <span class="text-xs sm:text-sm tracking-wide">Nuevo</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Dashboard -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6">
            <!-- Total de Productos -->
            <x-dashboard-widget title="Total de Productos" value="{{ $totalProducts }}" valueType="number"
                icon="fas fa-boxes" trend="Registrados" trendIcon="fas fa-plus-circle" trendColor="text-green-300"
                gradientFrom="from-blue-500" gradientTo="to-blue-600" progressWidth="100%"
                progressGradientFrom="from-blue-400" progressGradientTo="to-blue-500" />

            <!-- Valor de Compra -->
            <x-dashboard-widget title="Valor de Compra" value="{{ $totalPurchaseValue }}" valueType="currency"
                currencySymbol="{{ $currency->symbol }}" icon="fas fa-shopping-cart" trend="Total"
                trendIcon="fas fa-cart-plus" trendColor="text-green-300"
                subtitle="Promedio: {{ $totalProducts > 0 ? $currency->symbol . ' ' . number_format($totalPurchaseValue / $totalProducts, 2) : $currency->symbol . ' 0.00' }}"
                subtitleIcon="fas fa-calculator" gradientFrom="from-green-500" gradientTo="to-emerald-600"
                progressWidth="100%" progressGradientFrom="from-green-400" progressGradientTo="to-emerald-500" />

            <!-- Valor de Venta -->
            <x-dashboard-widget title="Valor de Venta" value="{{ $totalSaleValue }}" valueType="currency"
                currencySymbol="{{ $currency->symbol }}" icon="fas fa-cash-register" trend="Total"
                trendIcon="fas fa-cash-register" trendColor="text-green-300"
                subtitle="Promedio: {{ $totalProducts > 0 ? $currency->symbol . ' ' . number_format($totalSaleValue / $totalProducts, 2) : $currency->symbol . ' 0.00' }}"
                subtitleIcon="fas fa-calculator" gradientFrom="from-yellow-500" gradientTo="to-orange-500"
                progressWidth="100%" progressGradientFrom="from-yellow-400" progressGradientTo="to-orange-400" />

            <!-- Ganancia Potencial -->
            <x-dashboard-widget title="Ganancia Potencial" value="{{ $potentialProfit }}" valueType="currency"
                currencySymbol="{{ $currency->symbol }}" icon="fas fa-chart-line"
                trend="{{ number_format($profitPercentage, 1) }}%" trendIcon="fas fa-trending-up"
                trendColor="text-green-300" subtitle="Margen: {{ number_format($profitPercentage, 1) }}%"
                subtitleIcon="fas fa-percentage" gradientFrom="from-purple-500" gradientTo="to-indigo-600"
                progressWidth="100%" progressGradientFrom="from-purple-400" progressGradientTo="to-indigo-500" />
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
                            <i class="fas fa-tag"></i>
                            <span>Categoría</span>
                        </label>
                        <div class="relative" x-data="{
                            isOpen: false,
                            searchTerm: '',
                            filteredCategories: @js($categories),
                            selectedCategoryName: 'Todas las categorías',
                            selectedCategoryId: '',
                            filterCategories() {
                                if (!this.searchTerm) {
                                    this.filteredCategories = @js($categories);
                                    return;
                                }
                                const term = this.searchTerm.toLowerCase();
                                this.filteredCategories = @js($categories).filter(category =>
                                    category.name.toLowerCase().includes(term)
                                );
                            },
                            selectCategory(category) {
                                if (category) {
                                    this.selectedCategoryName = category.name;
                                    this.selectedCategoryId = category.id;
                                } else {
                                    this.selectedCategoryName = 'Todas las categorías';
                                    this.selectedCategoryId = '';
                                }
                                this.isOpen = false;
                                this.searchTerm = '';
                                this.filteredCategories = @js($categories);
                                // Trigger filter event
                                window.productsIndex.filterByCategory(this.selectedCategoryId);
                            }
                        }" @click.away="isOpen = false">

                            <div class="filter-input-wrapper">
                                <div class="filter-input-icon">
                                    <i class="fas fa-tag"></i>
                                </div>

                                <!-- Select Button -->
                                <button type="button"
                                    @click="isOpen = !isOpen; if (isOpen) { $nextTick(() => $refs.categorySearch.focus()) }"
                                    class="filter-input w-full text-left flex items-center justify-between">
                                    <span class="block truncate" x-text="selectedCategoryName"></span>
                                    <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 ml-2"
                                        :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div class="filter-input-border"></div>
                            </div>

                            <!-- Dropdown -->
                            <div x-show="isOpen" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute z-[9999] mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-auto"
                                style="z-index: 9999 !important;">

                                <!-- Search Input -->
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" x-ref="categorySearch" x-model="searchTerm"
                                        @input="filterCategories()"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Buscar categoría...">
                                </div>

                                <!-- Options -->
                                <div class="py-1">
                                    <!-- All categories option -->
                                    <button type="button" @click="selectCategory(null)"
                                        class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 flex items-center gap-3 transition-colors duration-150"
                                        :class="{ 'bg-blue-50 text-blue-700 font-medium': selectedCategoryId === '' }">
                                        <i class="fas fa-list text-gray-400"></i>
                                        <span>Todas las categorías</span>
                                    </button>

                                    <!-- Category options -->
                                    <template x-for="category in filteredCategories" :key="category.id">
                                        <button type="button" @click="selectCategory(category)"
                                            class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 flex items-center gap-3 transition-colors duration-150"
                                            :class="{
                                                'bg-blue-50 text-blue-700 font-medium': selectedCategoryId == category
                                                    .id
                                            }">
                                            <i class="fas fa-tag text-gray-400"></i>
                                            <span x-text="category.name"></span>
                                        </button>
                                    </template>

                                    <!-- No results -->
                                    <div x-show="filteredCategories.length === 0"
                                        class="px-4 py-3 text-sm text-gray-500 text-center">
                                        No se encontraron categorías
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-boxes"></i>
                            <span>Estado de Stock</span>
                        </label>
                        <div class="relative" x-data="{
                            isOpen: false,
                            searchTerm: '',
                            stockOptions: [
                                { id: 'low', name: 'Stock Bajo', icon: 'fa-exclamation-triangle', color: 'text-red-500' },
                                { id: 'normal', name: 'Stock Normal', icon: 'fa-check-circle', color: 'text-yellow-500' },
                                { id: 'high', name: 'Stock Alto', icon: 'fa-arrow-up', color: 'text-green-500' }
                            ],
                            filteredStockOptions: [
                                { id: 'low', name: 'Stock Bajo', icon: 'fa-exclamation-triangle', color: 'text-red-500' },
                                { id: 'normal', name: 'Stock Normal', icon: 'fa-check-circle', color: 'text-yellow-500' },
                                { id: 'high', name: 'Stock Alto', icon: 'fa-arrow-up', color: 'text-green-500' }
                            ],
                            selectedStockName: 'Todos los estados',
                            selectedStockId: '',
                            filterStockOptions() {
                                if (!this.searchTerm) {
                                    this.filteredStockOptions = this.stockOptions;
                                    return;
                                }
                                const term = this.searchTerm.toLowerCase();
                                this.filteredStockOptions = this.stockOptions.filter(option =>
                                    option.name.toLowerCase().includes(term)
                                );
                            },
                            selectStock(stock) {
                                if (stock) {
                                    this.selectedStockName = stock.name;
                                    this.selectedStockId = stock.id;
                                } else {
                                    this.selectedStockName = 'Todos los estados';
                                    this.selectedStockId = '';
                                }
                                this.isOpen = false;
                                this.searchTerm = '';
                                this.filteredStockOptions = this.stockOptions;
                                // Trigger filter event
                                window.productsIndex.filterByStock(this.selectedStockId);
                            }
                        }" @click.away="isOpen = false">

                            <div class="filter-input-wrapper">
                                <div class="filter-input-icon">
                                    <i class="fas fa-boxes"></i>
                                </div>

                                <!-- Select Button -->
                                <button type="button"
                                    @click="isOpen = !isOpen; if (isOpen) { $nextTick(() => $refs.stockSearch.focus()) }"
                                    class="filter-input w-full text-left flex items-center justify-between">
                                    <span class="block truncate" x-text="selectedStockName"></span>
                                    <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 ml-2"
                                        :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div class="filter-input-border"></div>
                            </div>

                            <!-- Dropdown -->
                            <div x-show="isOpen" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute z-[9999] mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-auto"
                                style="z-index: 9999 !important;">

                                <!-- Search Input -->
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" x-ref="stockSearch" x-model="searchTerm"
                                        @input="filterStockOptions()"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Buscar estado...">
                                </div>

                                <!-- Options -->
                                <div class="py-1">
                                    <!-- All states option -->
                                    <button type="button" @click="selectStock(null)"
                                        class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 flex items-center gap-3 transition-colors duration-150"
                                        :class="{ 'bg-blue-50 text-blue-700 font-medium': selectedStockId === '' }">
                                        <i class="fas fa-list text-gray-400"></i>
                                        <span>Todos los estados</span>
                                    </button>

                                    <!-- Stock options -->
                                    <template x-for="stock in filteredStockOptions" :key="stock.id">
                                        <button type="button" @click="selectStock(stock)"
                                            class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 flex items-center gap-3 transition-colors duration-150"
                                            :class="{ 'bg-blue-50 text-blue-700 font-medium': selectedStockId == stock.id }">
                                            <i class="fas" :class="[stock.icon, stock.color]"></i>
                                            <span x-text="stock.name"></span>
                                        </button>
                                    </template>

                                    <!-- No results -->
                                    <div x-show="filteredStockOptions.length === 0"
                                        class="px-4 py-3 text-sm text-gray-500 text-center">
                                        No se encontraron estados
                                    </div>
                                </div>
                            </div>
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
                        <button class="btn-modern btn-clear" id="clearFilters"
                            style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #9333ea 100%) !important;">
                            <div class="btn-content">
                                <i class="fas fa-times"></i>
                                <span>Limpiar</span>
                            </div>
                            <div class="btn-bg"
                                style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #9333ea 100%) !important;">
                            </div>
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
                                <input type="text" placeholder="Buscar productos..." id="searchInput"
                                    class="search-input" value="{{ request('search') }}">
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
                                <div class="product-card" data-product-id="{{ $product->id }}"
                                    data-search="{{ strtolower($product->name . ' ' . $product->code . ' ' . ($product->category->name ?? '')) }}">
                                    <div class="card-header">
                                        <div class="card-avatar">
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                class="product-image">
                                        </div>
                                        <div class="card-badge">
                                            <span>{{ $product->code }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h4 class="card-title">{{ $product->name }}</h4>
                                        <p class="card-description">
                                            {{ Str::limit($product->description, 100) ?? 'Sin descripción' }}</p>
                                        <div class="card-meta">
                                            <div class="meta-item">
                                                <i class="fas fa-tag"></i>
                                                <span
                                                    data-category-id="{{ $product->category->id ?? '' }}">{{ $product->category->name ?? 'Sin categoría' }}</span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="fas fa-boxes"></i>
                                                <span>Stock: {{ $product->stock }}</span>
                                            </div>
                                        </div>
                                        <div class="card-prices">
                                            <div class="price-item">
                                                <span class="price-label">Compra:</span>
                                                <span class="price-value purchase-price">{{ $currency->symbol }}
                                                    {{ number_format($product->purchase_price, 2) }}</span>
                                            </div>
                                            <div class="price-item">
                                                <span class="price-label">Venta:</span>
                                                <span class="price-value sale-price">{{ $currency->symbol }}
                                                    {{ number_format($product->sale_price, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-actions">
                                        @if ($permissions['products.show'])
                                            <button type="button" class="card-btn card-btn-view"
                                                onclick="showProductDetails({{ $product->id }})" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                                <span>Ver</span>
                                            </button>
                                        @endif

                                        @if ($permissions['products.edit'])
                                            <a href="{{ route('admin.products.edit', $product->id) }}"
                                                class="card-btn card-btn-edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                                <span>Editar</span>
                                            </a>
                                        @endif
                                        @if ($permissions['products.destroy'])
                                            <button type="button" class="card-btn card-btn-delete"
                                                onclick="deleteProduct({{ $product->id }}, '{{ $product->name }}')"
                                                title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                                <span>Eliminar</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Paginación Inteligente --}}
                        @include('partials.smart-pagination', [
                            'items' => $products,
                            'label' => 'productos',
                        ])
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
                                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                            class="product-image">
                                                    </div>
                                                    <div class="product-details">
                                                        <span class="product-name">{{ $product->name }}</span>
                                                        <span class="product-code">{{ $product->code }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="category-info">
                                                    <span class="category-text"
                                                        data-category-id="{{ $product->category->id ?? '' }}">{{ $product->category->name ?? 'Sin categoría' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="stock-info">
                                                    <span
                                                        class="stock-badge badge badge-{{ $product->stock_status_label === 'Bajo' ? 'danger' : ($product->stock_status_label === 'Normal' ? 'warning' : 'success') }}">
                                                        <i class="fas fa-boxes"></i>
                                                        {{ $product->stock }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="price-info">
                                                    <span class="price-text purchase-price">{{ $currency->symbol }}
                                                        {{ number_format($product->purchase_price, 2) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="price-info">
                                                    <span class="price-text sale-price">{{ $currency->symbol }}
                                                        {{ number_format($product->sale_price, 2) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    @if ($permissions['products.show'])
                                                        <button type="button" class="btn-action btn-view"
                                                            onclick="showProductDetails({{ $product->id }})"
                                                            data-toggle="tooltip" title="Ver Detalles">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    @endif
                                                    @if ($permissions['products.edit'])
                                                        <a href="{{ route('admin.products.edit', $product->id) }}"
                                                            class="btn-action btn-edit" data-toggle="tooltip"
                                                            title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    @if ($permissions['products.destroy'])
                                                        <button type="button" class="btn-action btn-delete"
                                                            onclick="deleteProduct({{ $product->id }}, '{{ $product->name }}')"
                                                            data-toggle="tooltip" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Paginación Inteligente --}}
                        @include('partials.smart-pagination', [
                            'items' => $products,
                            'label' => 'productos',
                        ])
                    </div>

                    {{-- Vista móvil (siempre tarjetas) --}}
                    <div class="mobile-view">
                        <div class="mobile-cards" id="mobileCards">
                            @foreach ($products as $product)
                                <div class="mobile-card" data-product-id="{{ $product->id }}"
                                    data-search="{{ strtolower($product->name . ' ' . $product->code . ' ' . ($product->category->name ?? '')) }}">
                                    <div class="mobile-card-header">
                                        <div class="mobile-avatar">
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                class="product-image">
                                        </div>
                                        <div class="mobile-info">
                                            <h4 class="mobile-title">{{ $product->name }}</h4>
                                            <span class="mobile-code">{{ $product->code }}</span>
                                        </div>
                                        <div class="mobile-actions">
                                            @if ($permissions['products.show'])
                                                <button type="button" class="mobile-btn mobile-btn-view"
                                                    onclick="showProductDetails({{ $product->id }})" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                            @if ($permissions['products.edit'])
                                                <a href="{{ route('admin.products.edit', $product->id) }}"
                                                    class="mobile-btn mobile-btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if ($permissions['products.destroy'])
                                                <button type="button" class="mobile-btn mobile-btn-delete"
                                                    onclick="deleteProduct({{ $product->id }}, '{{ $product->name }}')"
                                                    title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mobile-card-body">
                                        <p class="mobile-description">
                                            {{ Str::limit($product->description, 100) ?? 'Sin descripción' }}</p>
                                        <div class="mobile-meta">
                                            <div class="mobile-category">
                                                <i class="fas fa-tag"></i>
                                                <span
                                                    data-category-id="{{ $product->category->id ?? '' }}">{{ $product->category->name ?? 'Sin categoría' }}</span>
                                            </div>
                                            <div class="mobile-stock">
                                                <i class="fas fa-boxes"></i>
                                                <span>{{ $product->stock }}</span>
                                            </div>
                                        </div>
                                        <div class="mobile-prices">
                                            <div class="mobile-price">
                                                <span class="price-label">Compra:</span>
                                                <span class="price-value">{{ $currency->symbol }}
                                                    {{ number_format($product->purchase_price, 2) }}</span>
                                            </div>
                                            <div class="mobile-price">
                                                <span class="price-label">Venta:</span>
                                                <span class="price-value">{{ $currency->symbol }}
                                                    {{ number_format($product->sale_price, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Paginación Inteligente --}}
                        @include('partials.smart-pagination', [
                            'items' => $products,
                            'label' => 'productos',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para mostrar detalles de producto --}}
    <div class="modal-overlay" id="showProductModal" style="display: none;">
        <div class="modal-container">
            <!-- Header with gradient -->
            <div class="modal-header">
                <div class="modal-header-content">
                    <div class="modal-header-left">
                        <div class="modal-header-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="modal-header-text">
                            <h3 class="modal-title">Detalles del Producto</h3>
                            <p class="modal-subtitle">Información completa del producto</p>
                        </div>
                    </div>
                </div>
                <!-- Botón de cerrar posicionado absolutamente -->
                <button type="button" onclick="closeProductModal()" class="modal-close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <div class="modal-content">
                    <!-- Product Image and Basic Info -->
                    <div class="modal-product-info">
                        <div class="modal-product-image">
                            <img id="modalProductImage" src="" alt="Producto">
                        </div>
                        <div class="modal-product-details">
                            <h4 class="modal-product-name" id="modalProductName">-</h4>
                            <div class="modal-product-badges">
                                <span class="modal-badge modal-badge-code">
                                    <i class="fas fa-barcode"></i>
                                    <span id="modalProductCode">-</span>
                                </span>
                                <span class="modal-badge modal-badge-category">
                                    <i class="fas fa-tag"></i>
                                    <span id="modalProductCategory">-</span>
                                </span>
                            </div>
                            <p class="modal-product-description" id="modalProductDescription">-</p>
                            <div class="modal-stock-badge" id="modalStockBadge">
                                <i class="fas fa-boxes"></i>
                                <span>Stock: </span>
                                <span id="modalProductStock">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Information -->
                    <div class="modal-info-section">
                        <h5 class="modal-section-title">
                            <i class="fas fa-info-circle"></i>
                            Información Detallada
                        </h5>

                        <div class="modal-info-grid">
                            <!-- Precio de Compra -->
                            <div class="modal-info-card modal-info-card-green">
                                <div class="modal-info-card-header">
                                    <div class="modal-info-card-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <span class="modal-info-card-title">Precio de Compra</span>
                                </div>
                                <div class="modal-info-card-value" id="modalProductPurchasePrice">-</div>
                            </div>

                            <!-- Precio de Venta -->
                            <div class="modal-info-card modal-info-card-blue">
                                <div class="modal-info-card-header">
                                    <div class="modal-info-card-icon">
                                        <i class="fas fa-cash-register"></i>
                                    </div>
                                    <span class="modal-info-card-title">Precio de Venta</span>
                                </div>
                                <div class="modal-info-card-value" id="modalProductSalePrice">-</div>
                            </div>

                            <!-- Ganancia Potencial -->
                            <div class="modal-info-card modal-info-card-purple">
                                <div class="modal-info-card-header">
                                    <div class="modal-info-card-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <span class="modal-info-card-title">Ganancia Potencial</span>
                                </div>
                                <div class="modal-info-card-value" id="modalProductProfit">-</div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="modal-additional-info">
                            <!-- Stock Information -->
                            <div class="modal-info-panel">
                                <h6 class="modal-panel-title">
                                    <i class="fas fa-warehouse"></i>
                                    Información de Inventario
                                </h6>
                                <div class="modal-panel-content">
                                    <div class="modal-info-row">
                                        <span class="modal-info-label">Stock Actual:</span>
                                        <span class="modal-info-value" id="modalStockCurrent">-</span>
                                    </div>
                                    <div class="modal-info-row">
                                        <span class="modal-info-label">Estado del Stock:</span>
                                        <span class="modal-info-value" id="modalStockStatus">-</span>
                                    </div>
                                    <div class="modal-info-row">
                                        <span class="modal-info-label">Valor Total en Stock:</span>
                                        <span class="modal-info-value modal-info-value-green"
                                            id="modalStockValue">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Dates and Metadata -->
                            <div class="modal-info-panel">
                                <h6 class="modal-panel-title">
                                    <i class="fas fa-clock"></i>
                                    Información Temporal
                                </h6>
                                <div class="modal-panel-content">
                                    <div class="modal-info-row">
                                        <span class="modal-info-label">Fecha de Creación:</span>
                                        <span class="modal-info-value" id="modalProductCreated">-</span>
                                    </div>
                                    <div class="modal-info-row">
                                        <span class="modal-info-label">Última Actualización:</span>
                                        <span class="modal-info-value" id="modalProductUpdated">-</span>
                                    </div>
                                    <div class="modal-info-row">
                                        <span class="modal-info-label">ID del Producto:</span>
                                        <span class="modal-info-value modal-info-value-id" id="modalProductId">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Full Description Section -->
                        <div class="modal-description-section">
                            <h6 class="modal-panel-title">
                                <i class="fas fa-align-left"></i>
                                Descripción Completa
                            </h6>
                            <div class="modal-description-content">
                                <p id="modalProductFullDescription">Sin descripción disponible</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="modal-footer">
                <div class="modal-footer-actions">
                    <button type="button" onclick="closeProductModal()" class="modal-btn modal-btn-secondary">
                        <i class="fas fa-times"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
