@extends('layouts.app')

@section('title', 'Gestión de Categorías')

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
            @can('categories.report')
                    <a href="{{ route('admin.categories.report') }}" class="btn-glass btn-secondary-glass" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                        <span>Reporte</span>
                        <div class="btn-ripple"></div>
                </a>
            @endcan
            @can('categories.create')
                    <a href="{{ route('admin.categories.create') }}" class="btn-glass btn-primary-glass">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nueva Categoría</span>
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

    <!-- Filters Section -->
    <div class="filters-section">
        <div class="filters-header" id="filtersHeader">
            <div class="filters-title">
                <div class="filters-icon">
                <i class="fas fa-filter"></i>
                </div>
                <div class="filters-text">
                    <h3>Filtros Avanzados</h3>
                    <p>Refina tu búsqueda de categorías</p>
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
                        <span>Buscar Categoría</span>
                    </label>
                    <div class="filter-input-wrapper">
                        <div class="filter-input-icon">
                            <i class="fas fa-search"></i>
                        </div>
                    <input type="text" class="filter-input" id="categorySearch" placeholder="Buscar por nombre o descripción...">
                        <div class="filter-input-border"></div>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-sort"></i>
                        <span>Ordenar por</span>
                    </label>
                    <div class="filter-input-wrapper">
                        <div class="filter-input-icon">
                            <i class="fas fa-sort"></i>
                        </div>
                    <select class="filter-input" id="sortBy">
                        <option value="name">Nombre</option>
                        <option value="created_at">Fecha de creación</option>
                        <option value="updated_at">Última actualización</option>
                    </select>
                        <div class="filter-input-border"></div>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-sort-amount-down"></i>
                        <span>Orden</span>
                    </label>
                    <div class="filter-input-wrapper">
                        <div class="filter-input-icon">
                            <i class="fas fa-sort-amount-down"></i>
                        </div>
                    <select class="filter-input" id="sortOrder">
                        <option value="asc">Ascendente</option>
                        <option value="desc">Descendente</option>
                    </select>
                        <div class="filter-input-border"></div>
                    </div>
                </div>
            </div>
            
            <div class="filters-actions">
                <div class="filters-status">
                    <span class="status-text">Filtros activos:</span>
                    <div class="active-filters" id="activeFilters">
                        <span class="filter-badge">Todas las categorías</span>
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
                                    @can('categories.show')
                                        <button type="button" class="card-btn card-btn-view" onclick="showCategoryDetails({{ $category->id }})" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                            <span>Ver</span>
                                        </button>
                                    @endcan
                                    @can('categories.edit')
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="card-btn card-btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                            <span>Editar</span>
                                        </a>
                                    @endcan
                                    @can('categories.destroy')
                                        <button type="button" class="card-btn card-btn-delete" onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')" title="Eliminar">
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
                                            @can('categories.show')
                                                <button type="button" class="btn-action btn-view" onclick="showCategoryDetails({{ $category->id }})" data-toggle="tooltip" title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endcan
                                            @can('categories.edit')
                                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn-action btn-edit" data-toggle="tooltip" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('categories.destroy')
                                                <button type="button" class="btn-action btn-delete" onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')" data-toggle="tooltip" title="Eliminar">
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
                                    @can('categories.show')
                                        <button type="button" class="mobile-btn mobile-btn-view" onclick="showCategoryDetails({{ $category->id }})" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    @can('categories.edit')
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="mobile-btn mobile-btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('categories.destroy')
                                        <button type="button" class="mobile-btn mobile-btn-delete" onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
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
        position: sticky;
        top: 1rem;
        width: 100%;
        padding: 1rem 1.5rem;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        z-index: 100;
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
        padding: 0; /* Removed padding as it's handled by filters-section */
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

    .title-content {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0; /* Removed margin-bottom */
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

    .title-content h3 {
        color: var(--dark-color);
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }

    .title-content p {
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

        .title-content {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }

        .title-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }

        .title-content h3 {
            font-size: 1.25rem;
        }

        .title-content p {
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

        .title-content {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }

        .title-icon {
            width: 36px;
            height: 36px;
            font-size: 1rem;
        }

        .title-content h3 {
            font-size: 1.1rem;
        }

        .title-content p {
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

    .category-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .category-avatar {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 1.3rem;
        box-shadow: var(--shadow-light);
    }

    .category-details {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .category-name {
        font-weight: 700;
        color: var(--dark-color);
        font-size: 1rem;
    }

    .category-id {
        color: #718096;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .description-info {
        max-width: 350px;
    }

    .description-text {
        color: #4a5568;
        font-size: 0.95rem;
        line-height: 1.5;
        font-weight: 500;
    }

    .date-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .date-main {
        font-weight: 700;
        color: var(--dark-color);
        font-size: 0.95rem;
    }

    .date-time {
        color: #718096;
        font-size: 0.85rem;
        font-weight: 500;
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

    /* Tarjetas de Categorías */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .category-card {
        background: white;
        border-radius: 16px;
        box-shadow: var(--shadow-light);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
        position: relative;
    }

    .category-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-medium);
    }

    .category-card::before {
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

    .category-card:hover::before {
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
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        position: relative;
        z-index: 1;
        backdrop-filter: blur(10px);
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
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
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

        .mobile-id {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-top: 0.2rem;
            display: block;
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
        }

        .mobile-date {
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 600;
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

        .category-card {
            border-radius: 12px;
        }

        .card-header {
            padding: 0.75rem;
        }

        .card-avatar {
            width: 36px;
            height: 36px;
            font-size: 1rem;
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

        .category-card {
            border-radius: 10px;
        }

        .card-header {
            padding: 0.5rem;
        }

        .card-avatar {
            width: 32px;
            height: 32px;
            font-size: 0.9rem;
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
</style>
@endpush

@push('js')
<script>
    // Variables globales
    let currentViewMode = 'cards';
    let currentPage = 1;
    const itemsPerPage = 10;
    const cardsPerPage = 12;
    let allCategories = [];
    let filteredCategories = [];

    // Inicializar la página
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Categorías page loaded');
        initializeCategoriesPage();
        initializeEventListeners();
    });

    // Inicializar la página de categorías
    function initializeCategoriesPage() {
        console.log('Initializing categories page...');
        
        // Cargar modo de vista guardado
        const savedViewMode = localStorage.getItem('categoriesViewMode');
        if (savedViewMode && (savedViewMode === 'table' || savedViewMode === 'cards')) {
            currentViewMode = savedViewMode;
            changeViewMode(savedViewMode);
        } else {
            // Modo por defecto: tarjetas
            changeViewMode('cards');
        }

        // Obtener todas las categorías
        getAllCategories();
        
        // Mostrar primera página
        showPage(1);
    }

    // Obtener todas las categorías
    function getAllCategories() {
        const tableRows = document.querySelectorAll('#categoriesTableBody tr');
        const categoryCards = document.querySelectorAll('.category-card');
        const mobileCards = document.querySelectorAll('.mobile-card');
        
        allCategories = [];
        
        // Procesar filas de tabla
        tableRows.forEach((row, index) => {
            const categoryName = row.querySelector('.category-name').textContent.trim();
            const categoryDescription = row.querySelector('.description-text').textContent.trim();
            
            allCategories.push({
                element: row,
                cardElement: categoryCards[index],
                mobileElement: mobileCards[index],
                data: {
                    id: row.dataset.categoryId,
                    name: categoryName,
                    description: categoryDescription
                }
            });
        });
        
        filteredCategories = [...allCategories];
        console.log('Categories loaded:', allCategories.length);
    }

    // Cambiar modo de vista
    function changeViewMode(mode) {
        console.log('Changing view mode to:', mode);
        currentViewMode = mode;
        localStorage.setItem('categoriesViewMode', mode);
        
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
        document.querySelectorAll('#categoriesTableBody tr').forEach(row => row.style.display = 'none');
        document.querySelectorAll('.category-card').forEach(card => card.style.display = 'none');
        document.querySelectorAll('.mobile-card').forEach(card => card.style.display = 'none');
        
        // Mostrar solo los elementos de la página actual
        filteredCategories.slice(startIndex, endIndex).forEach((category, index) => {
            if (category.element) category.element.style.display = 'table-row';
            if (category.cardElement) category.cardElement.style.display = 'block';
            if (category.mobileElement) category.mobileElement.style.display = 'block';
            
            // Actualizar números de fila
            if (category.element) {
                category.element.querySelector('.row-number').textContent = startIndex + index + 1;
            }
        });
        
        // Actualizar información de paginación
        updatePaginationInfo(page, filteredCategories.length);
        updatePaginationControls(page, Math.ceil(filteredCategories.length / itemsPerPage));
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
    function filterCategories(searchTerm) {
        const searchLower = searchTerm.toLowerCase().trim();
        
        if (!searchLower) {
            filteredCategories = [...allCategories];
        } else {
            filteredCategories = allCategories.filter(category => {
                const nameMatch = category.data.name.toLowerCase().includes(searchLower);
                const descriptionMatch = category.data.description.toLowerCase().includes(searchLower);
                return nameMatch || descriptionMatch;
            });
        }
        
        currentPage = 1;
        showPage(1);
    }

    // Mostrar detalles de categoría
    async function showCategoryDetails(categoryId) {
        console.log('Showing category details for ID:', categoryId);
        
        try {
            const response = await fetch(`/categories/${categoryId}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                // Llenar datos en el modal
                document.getElementById('modalCategoryName').textContent = data.category.name;
                document.getElementById('modalCategoryDescription').textContent = data.category.description || 'Sin descripción';
                document.getElementById('modalCategoryCreated').textContent = data.category.created_at;
                document.getElementById('modalCategoryUpdated').textContent = data.category.updated_at;
                
                // Mostrar modal
                document.getElementById('showCategoryModal').style.display = 'flex';
            } else {
                showAlert('Error', 'No se pudieron obtener los datos de la categoría', 'error');
            }
        } catch (error) {
            console.error('Error fetching category details:', error);
            showAlert('Error', 'No se pudieron obtener los datos de la categoría', 'error');
        }
    }

    // Cerrar modal de categoría
    function closeCategoryModal() {
        document.getElementById('showCategoryModal').style.display = 'none';
    }

    // Eliminar categoría
    function deleteCategory(categoryId, categoryName) {
        console.log('Deleting category:', categoryId, categoryName);
        
        showConfirmDialog(
            '¿Estás seguro?',
            `¿Deseas eliminar la categoría <strong>${categoryName}</strong>?<br><small class="text-muted">Esta acción no se puede revertir</small>`,
            'warning',
            () => performDeleteCategory(categoryId)
        );
    }

    // Realizar eliminación de categoría
    async function performDeleteCategory(categoryId) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch(`/categories/delete/${categoryId}`, {
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
            console.error('Error deleting category:', error);
            showAlert('Error', 'No se pudo eliminar la categoría', 'error');
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
                filterCategories(searchTerm);
            });
        }
        
        // Búsqueda en filtros
        const categorySearch = document.getElementById('categorySearch');
        if (categorySearch) {
            categorySearch.addEventListener('keyup', function() {
                const searchTerm = this.value;
                filterCategories(searchTerm);
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
            const totalPages = Math.ceil(filteredCategories.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });
        
        // Aplicar filtros
        document.getElementById('applyFilters').addEventListener('click', function() {
            const searchTerm = document.getElementById('categorySearch').value;
            filterCategories(searchTerm);
        });
        
        // Limpiar filtros
        document.getElementById('clearFilters').addEventListener('click', function() {
            document.getElementById('categorySearch').value = '';
            filterCategories('');
        });
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('showCategoryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCategoryModal();
            }
        });
        
        console.log('Event listeners initialized');
    }
</script>
@endpush
@endsection
