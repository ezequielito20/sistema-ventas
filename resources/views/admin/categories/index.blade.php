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
    <!-- Floating Header -->
    <div class="floating-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon-wrapper">
                    <div class="header-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="icon-glow"></div>
                </div>
                <div class="header-text">
                    <h1 class="header-title">Gestión de Categorías</h1>
                    <p class="header-subtitle">Organiza y gestiona las categorías de productos del sistema</p>
                </div>
            </div>
            <div class="header-actions">
                @if($permissions['categories.report'])
                    <a href="{{ route('admin.categories.report') }}" class="btn-glass btn-secondary-glass" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                        <span>Reporte</span>
                        <div class="btn-ripple"></div>
                    </a>
                @endif
                @if($permissions['categories.create'])
                    <a href="{{ route('admin.categories.create') }}" class="btn-glass btn-primary-glass">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nueva Categoría</span>
                        <div class="btn-ripple"></div>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Dashboard -->
    <div class="stats-dashboard">
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $categories->count() }}</div>
                    <div class="stat-label">Total de Categorías</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+{{ $categories->count() }}%</span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $categories->count() }}</div>
                    <div class="stat-label">Activas</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+100%</span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $categories->where('created_at', '>=', now()->subDays(7))->count() }}</div>
                    <div class="stat-label">Esta Semana</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+{{ $categories->where('created_at', '>=', now()->subDays(7))->count() }}%</span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $categories->where('products_count', '>', 0)->count() }}</div>
                    <div class="stat-label">Con Productos</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+{{ $categories->where('products_count', '>', 0)->count() }}%</span>
                    </div>
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
                            <input type="text" placeholder="Buscar categorías..." id="searchInput" class="search-input">
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
                            <div class="category-card" data-category-id="{{ $category->id }}" data-search="{{ strtolower($category->name . ' ' . ($category->description ?? '')) }}">
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
                                    <p class="card-description">{{ Str::limit($category->description, 150) ?? 'Sin descripción' }}</p>
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
                                    @if($permissions['categories.show'])
                                        <button type="button" class="card-btn card-btn-view" onclick="showCategoryDetails({{ $category->id }})" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                            <span>Ver</span>
                                        </button>
                                    @endif
                                    @if($permissions['categories.edit'])
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="card-btn card-btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                            <span>Editar</span>
                                        </a>
                                    @endif
                                    @if($permissions['categories.destroy'])
                                        <button type="button" class="card-btn card-btn-delete" onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                            <span>Eliminar</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Paginación para tarjetas --}}
                    <div class="custom-pagination">
                        <div class="pagination-info">
                            <span id="cardsPaginationInfo">Mostrando 1-{{ min(12, $categories->count()) }} de {{ $categories->count() }} registros</span>
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
                                                <span class="description-text">{{ Str::limit($category->description, 100) ?? 'Sin descripción' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="date-info">
                                                <span class="date-main">{{ \Carbon\Carbon::parse($category->created_at)->format('d/m/Y') }}</span>
                                                <span class="date-time">{{ \Carbon\Carbon::parse($category->created_at)->format('H:i') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                @if($permissions['categories.show'])
                                                    <button type="button" class="btn-action btn-view" onclick="showCategoryDetails({{ $category->id }})" data-toggle="tooltip" title="Ver Detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endif
                                                @if($permissions['categories.edit'])
                                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn-action btn-edit" data-toggle="tooltip" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                                @if($permissions['categories.destroy'])
                                                    <button type="button" class="btn-action btn-delete" onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')" data-toggle="tooltip" title="Eliminar">
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

                    {{-- Paginación personalizada --}}
                    <div class="custom-pagination">
                        <div class="pagination-info">
                            <span id="paginationInfo">Mostrando 1-{{ min(10, $categories->count()) }} de {{ $categories->count() }} registros</span>
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
                        @foreach ($categories as $category)
                            <div class="mobile-card" data-category-id="{{ $category->id }}" data-search="{{ strtolower($category->name . ' ' . ($category->description ?? '')) }}">
                                <div class="mobile-card-header">
                                    <div class="mobile-avatar">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <div class="mobile-info">
                                        <h4 class="mobile-title">{{ $category->name }}</h4>
                                        <span class="mobile-id">ID: {{ $category->id }}</span>
                                    </div>
                                    <div class="mobile-actions">
                                        @if($permissions['categories.show'])
                                            <button type="button" class="mobile-btn mobile-btn-view" onclick="showCategoryDetails({{ $category->id }})" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                        @if($permissions['categories.edit'])
                                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="mobile-btn mobile-btn-edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if($permissions['categories.destroy'])
                                            <button type="button" class="mobile-btn mobile-btn-delete" onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="mobile-card-body">
                                    <p class="mobile-description">{{ Str::limit($category->description, 100) ?? 'Sin descripción' }}</p>
                                    <div class="mobile-meta">
                                        <span class="mobile-date">{{ \Carbon\Carbon::parse($category->created_at)->format('d/m/Y H:i') }}</span>
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
            <button type="button" class="btn-modern btn-secondary" onclick="closeCategoryModal()">
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
