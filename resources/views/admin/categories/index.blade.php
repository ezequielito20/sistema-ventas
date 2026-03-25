@extends('layouts.app')

@section('title', 'Gestión de Categorías')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/categories/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/categories/index-components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/categories/index-tables-cards.css') }}">
@endpush

@push('js')
    <script>
        // Datos globales para JavaScript
        window.categoriesData = {
            totalCategories: {{ $categories->count() }},
            activeCategories: {{ $categories->count() }},
            weeklyCategories: {{ $categories->where('created_at', '>=', now()->subDays(7))->count() }},
            categoriesWithProducts: {{ $categories->where('products_count', '>', 0)->count() }},
            itemsPerPage: 10,
            cardsPerPage: 12
        };
    </script>
    <script src="{{ asset('js/admin/categories/index.js') }}" defer></script>
@endpush

@section('content')
    <!-- Background Pattern -->
    <div class="page-background"></div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Hero Section de Categorías -->
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
                                    <i class="fas fa-tags text-lg sm:text-2xl text-white"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <h1 class="text-xl sm:text-3xl font-bold text-white leading-tight whitespace-nowrap">
                                    Gestión de Categorías
                                </h1>
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-3 mt-4 lg:mt-0 lg:flex-shrink-0 lg:justify-end">
                        @if ($permissions['categories.report'])
                            <a href="{{ route('admin.categories.report') }}"
                                class="inline-flex items-center justify-center px-4 py-2.5 bg-white bg-opacity-10 hover:bg-opacity-20 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-20 backdrop-blur-sm group/btn text-center"
                                target="_blank">
                                <i
                                    class="fas fa-file-pdf text-base mr-2 group-hover/btn:scale-110 transition-transform"></i>
                                <span class="text-xs sm:text-sm">Reporte</span>
                            </a>
                        @endif

                        @if ($permissions['categories.create'])
                            <a href="{{ route('admin.categories.create') }}"
                                class="inline-flex items-center justify-center px-4 py-2.5 bg-white text-indigo-600 font-bold rounded-xl hover:bg-opacity-90 transition-all duration-200 shadow-lg transform hover:-translate-y-0.5 active:translate-y-0 text-center">
                                <i class="fas fa-plus text-base mr-2"></i>
                                <span class="text-xs sm:text-sm tracking-wide">Nueva</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Dashboard -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6">
            <!-- Total de Categorías -->
            <x-dashboard-widget title="Total de Categorías" value="{{ $categories->count() }}" valueType="number"
                icon="fas fa-tags" trend="Registradas" trendIcon="fas fa-plus-circle" trendColor="text-green-300"
                gradientFrom="from-blue-500" gradientTo="to-blue-600" progressWidth="100%"
                progressGradientFrom="from-blue-400" progressGradientTo="to-blue-500" />

            <!-- Categorías Activas -->
            <x-dashboard-widget title="Categorías Activas" value="{{ $categories->count() }}" valueType="number"
                icon="fas fa-check-circle" trend="Activas" trendIcon="fas fa-check" trendColor="text-green-300"
                subtitle="100% del total" subtitleIcon="fas fa-percentage" gradientFrom="from-green-500"
                gradientTo="to-emerald-600" progressWidth="100%" progressGradientFrom="from-green-400"
                progressGradientTo="to-emerald-500" />

            <!-- Categorías de Esta Semana -->
            <x-dashboard-widget title="Esta Semana"
                value="{{ $categories->where('created_at', '>=', now()->subDays(7))->count() }}" valueType="number"
                icon="fas fa-clock" trend="Nuevas" trendIcon="fas fa-calendar-week" trendColor="text-yellow-300"
                subtitle="{{ $categories->count() > 0 ? round(($categories->where('created_at', '>=', now()->subDays(7))->count() / $categories->count()) * 100, 1) . '% del total' : '0% del total' }}"
                subtitleIcon="fas fa-percentage" gradientFrom="from-yellow-500" gradientTo="to-orange-500"
                progressWidth="{{ $categories->count() > 0 ? ($categories->where('created_at', '>=', now()->subDays(7))->count() / $categories->count()) * 100 : 0 }}%"
                progressGradientFrom="from-yellow-400" progressGradientTo="to-orange-400" />

            <!-- Categorías con Productos -->
            <x-dashboard-widget title="Con Productos" value="{{ $categories->where('products_count', '>', 0)->count() }}"
                valueType="number" icon="fas fa-box" trend="Con Productos" trendIcon="fas fa-boxes"
                trendColor="text-green-300"
                subtitle="{{ $categories->count() > 0 ? round(($categories->where('products_count', '>', 0)->count() / $categories->count()) * 100, 1) . '% del total' : '0% del total' }}"
                subtitleIcon="fas fa-percentage" gradientFrom="from-purple-500" gradientTo="to-indigo-600"
                progressWidth="{{ $categories->count() > 0 ? ($categories->where('products_count', '>', 0)->count() / $categories->count()) * 100 : 0 }}%"
                progressGradientFrom="from-purple-400" progressGradientTo="to-indigo-500" />
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
                            <h3>Lista de Categorías</h3>
                            <p>Gestiona y visualiza todas las categorías del sistema</p>
                        </div>
                    </div>
                    <div class="content-actions">
                        <div class="search-container">
                            <div class="search-wrapper">
                                <div class="search-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <input type="text" placeholder="Buscar categorías..." id="searchInput"
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
                            @foreach ($categories as $category)
                                <div class="category-card" data-category-id="{{ $category->id }}"
                                    data-search="{{ strtolower($category->name . ' ' . ($category->description ?? '')) }}">
                                    <div class="card-header">
                                        <div class="card-avatar">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <div class="card-badge">
                                            <span>ID: {{ $category->id }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h4 class="card-title">{{ $category->name }}</h4>
                                        <p class="card-description">
                                            {{ Str::limit($category->description, 150) ?? 'Sin descripción' }}</p>
                                        <div class="card-meta">
                                            <div class="meta-item">
                                                <i class="fas fa-calendar"></i>
                                                <span>{{ \Carbon\Carbon::parse($category->created_at)->format('d/m/Y') }}</span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="fas fa-clock"></i>
                                                <span>{{ \Carbon\Carbon::parse($category->created_at)->format('H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-actions">
                                        @if ($permissions['categories.show'])
                                            <button type="button" class="card-btn card-btn-view"
                                                onclick="showCategoryDetails({{ $category->id }})" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                        @if ($permissions['categories.edit'])
                                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                                                class="card-btn card-btn-edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if ($permissions['categories.destroy'])
                                            <button type="button" class="card-btn card-btn-delete"
                                                onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')"
                                                title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Paginación inteligente --}}
                        @include('partials.smart-pagination', [
                            'items' => $categories,
                            'label' => 'categorías',
                        ])
                    </div>

                    {{-- Vista de tabla (solo desktop) --}}
                    <div class="desktop-view" id="desktopTableView" style="display: none;">
                        <div class="table-container">
                            <table id="categoriesTable" class="modern-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="th-content">
                                                <span>#</span>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <i class="fas fa-tag"></i>
                                                <span>Nombre</span>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <i class="fas fa-align-left"></i>
                                                <span>Descripción</span>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <i class="fas fa-calendar"></i>
                                                <span>Creada</span>
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
                                <tbody id="categoriesTableBody">
                                    @foreach ($categories as $category)
                                        <tr class="table-row" data-category-id="{{ $category->id }}">
                                            <td>
                                                <div class="row-number">
                                                    {{ $loop->iteration }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="category-info">
                                                    <div class="category-avatar">
                                                        <i class="fas fa-tag"></i>
                                                    </div>
                                                    <div class="category-details">
                                                        <span class="category-name">{{ $category->name }}</span>
                                                        <span class="category-id">ID: {{ $category->id }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="description-info">
                                                    <span
                                                        class="description-text">{{ Str::limit($category->description, 100) ?? 'Sin descripción' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="date-info">
                                                    <span
                                                        class="date-main">{{ \Carbon\Carbon::parse($category->created_at)->format('d/m/Y') }}</span>
                                                    <span
                                                        class="date-time">{{ \Carbon\Carbon::parse($category->created_at)->format('H:i') }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    @if ($permissions['categories.show'])
                                                        <button type="button" class="btn-action btn-view"
                                                            onclick="showCategoryDetails({{ $category->id }})"
                                                            data-toggle="tooltip" title="Ver Detalles">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    @endif
                                                    @if ($permissions['categories.edit'])
                                                        <a href="{{ route('admin.categories.edit', $category->id) }}"
                                                            class="btn-action btn-edit" data-toggle="tooltip"
                                                            title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    @if ($permissions['categories.destroy'])
                                                        <button type="button" class="btn-action btn-delete"
                                                            onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')"
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

                        {{-- Paginación inteligente --}}
                        @include('partials.smart-pagination', [
                            'items' => $categories,
                            'label' => 'categorías',
                        ])
                    </div>

                    {{-- Vista móvil (siempre tarjetas) --}}
                    <div class="mobile-view">
                        <div class="mobile-cards" id="mobileCards">
                            @foreach ($categories as $category)
                                <div class="mobile-card" data-category-id="{{ $category->id }}"
                                    data-search="{{ strtolower($category->name . ' ' . ($category->description ?? '')) }}">
                                    <div class="mobile-card-header">
                                        <div class="mobile-avatar">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <div class="mobile-info">
                                            <h4 class="mobile-title">{{ $category->name }}</h4>
                                            <span class="mobile-id">ID: {{ $category->id }}</span>
                                        </div>
                                    </div>
                                    <div class="mobile-card-body">
                                        <p class="mobile-description">
                                            {{ Str::limit($category->description, 100) ?? 'Sin descripción' }}</p>
                                        <div class="mobile-meta">
                                            <span
                                                class="mobile-date">{{ \Carbon\Carbon::parse($category->created_at)->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                    <div class="mobile-card-actions">
                                        @if ($permissions['categories.show'])
                                            <button type="button" class="mobile-btn mobile-btn-view"
                                                onclick="showCategoryDetails({{ $category->id }})" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                        @if ($permissions['categories.edit'])
                                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                                                class="mobile-btn mobile-btn-edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if ($permissions['categories.destroy'])
                                            <button type="button" class="mobile-btn mobile-btn-delete"
                                                onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')"
                                                title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para mostrar detalles de categoría --}}
    <div class="modal-overlay" id="showCategoryModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-tag mr-2"></i>
                    Detalles de la Categoría
                </h3>
                <button type="button" class="modal-close" onclick="closeCategoryModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-content-grid">
                    <div class="modal-content-column">
                        <div class="modal-detail-item">
                            <div class="detail-label">
                                <i class="fas fa-tag"></i>
                                Nombre de la Categoría
                            </div>
                            <div class="detail-value" id="modalCategoryName">-</div>
                        </div>
                        <div class="modal-detail-item">
                            <div class="detail-label">
                                <i class="fas fa-align-left"></i>
                                Descripción
                            </div>
                            <div class="detail-value" id="modalCategoryDescription">-</div>
                        </div>
                    </div>
                    <div class="modal-content-column">
                        <div class="modal-detail-item">
                            <div class="detail-label">
                                <i class="fas fa-calendar"></i>
                                Fecha de Creación
                            </div>
                            <div class="detail-value" id="modalCategoryCreated">-</div>
                        </div>
                        <div class="modal-detail-item">
                            <div class="detail-label">
                                <i class="fas fa-clock"></i>
                                Última Actualización
                            </div>
                            <div class="detail-value" id="modalCategoryUpdated">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 border border-gray-500/30 backdrop-blur-sm"
                    onclick="closeCategoryModal()">
                    <i class="fas fa-times mr-2 text-lg"></i>
                    <span>Cerrar</span>
                </button>
            </div>
        </div>
    </div>
@endsection
