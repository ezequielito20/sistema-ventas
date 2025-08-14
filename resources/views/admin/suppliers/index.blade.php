@extends('layouts.app')

@section('title', 'Gestión de Proveedores')

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
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="icon-glow"></div>
                </div>
                <div class="header-text">
                    <h1 class="header-title">Gestión de Proveedores</h1>
                    <p class="header-subtitle">Administra y organiza tus proveedores de manera eficiente</p>
                </div>
            </div>
            <div class="header-actions">
                @can('suppliers.report')
                    <a href="{{ route('admin.suppliers.report') }}" class="btn-glass btn-secondary-glass" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                        <span>Reporte</span>
                        <div class="btn-ripple"></div>
                    </a>
                @endcan
                @can('suppliers.create')
                    <a href="{{ route('admin.suppliers.create') }}" class="btn-glass btn-primary-glass">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nuevo Proveedor</span>
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
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $totalSuppliers }}</div>
                    <div class="stat-label">Total de Proveedores</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+{{ $totalSuppliers }}%</span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $activeSuppliers }}</div>
                    <div class="stat-label">Activos</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+100%</span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $recentSuppliers }}</div>
                    <div class="stat-label">Este Mes</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+{{ $recentSuppliers }}%</span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $inactiveSuppliers }}</div>
                    <div class="stat-label">Inactivos</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+{{ $inactiveSuppliers }}%</span>
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
                    <p>Refina tu búsqueda de proveedores</p>
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
                        <span>Buscar Proveedor</span>
                    </label>
                    <div class="filter-input-wrapper">
                        <div class="filter-input-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <input type="text" class="filter-input" id="supplierSearch" placeholder="Buscar por empresa, contacto o dirección...">
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
                            <option value="company_name">Empresa</option>
                            <option value="supplier_name">Contacto</option>
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
                        <span class="filter-badge">Todos los proveedores</span>
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
                        <h3>Lista de Proveedores</h3>
                        <p>Gestiona y visualiza todos los proveedores del sistema</p>
                    </div>
                </div>
                <div class="content-actions">
                    <div class="search-container">
                        <div class="search-wrapper">
                            <div class="search-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <input type="text" placeholder="Buscar proveedores..." id="searchInput" class="search-input">
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
                        @foreach ($suppliers as $supplier)
                            <div class="supplier-card" data-supplier-id="{{ $supplier->id }}" data-search="{{ strtolower($supplier->company_name . ' ' . $supplier->supplier_name . ' ' . $supplier->company_address) }}">
                                <div class="card-header">
                                    <div class="card-avatar">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                    <div class="card-badge">
                                        <span>ID: {{ $supplier->id }}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h4 class="card-title">{{ $supplier->company_name }}</h4>
                                    <p class="card-description">
                                        <strong>Contacto:</strong> {{ $supplier->supplier_name }}<br>
                                        <strong>Teléfono:</strong> {{ $supplier->supplier_phone }}
                                    </p>
                                    <div class="card-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-envelope"></i>
                                            <span>{{ $supplier->company_email }}</span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>{{ Str::limit($supplier->company_address, 50) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    @can('suppliers.show')
                                        <button type="button" class="card-btn card-btn-view" onclick="showSupplierDetails({{ $supplier->id }})" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                            <span>Ver</span>
                                        </button>
                                    @endcan
                                    @can('suppliers.edit')
                                        <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="card-btn card-btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                            <span>Editar</span>
                                        </a>
                                    @endcan
                                    @can('suppliers.destroy')
                                        <button type="button" class="card-btn card-btn-delete" onclick="deleteSupplier({{ $supplier->id }}, '{{ $supplier->company_name }}')" title="Eliminar">
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
                            <span id="cardsPaginationInfo">Mostrando 1-{{ min(12, $suppliers->count()) }} de {{ $suppliers->count() }} registros</span>
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
                        <table id="suppliersTable" class="modern-table">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="th-content">
                                            <span>#</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="th-content">
                                            <i class="fas fa-building"></i>
                                            <span>Empresa</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="th-content">
                                            <i class="fas fa-user"></i>
                                            <span>Contacto</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="th-content">
                                            <i class="fas fa-phone"></i>
                                            <span>Teléfono</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="th-content">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>Ubicación</span>
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
                            <tbody id="suppliersTableBody">
                                @foreach ($suppliers as $supplier)
                                    <tr class="table-row" data-supplier-id="{{ $supplier->id }}">
                                        <td>
                                            <div class="row-number">
                                                {{ $loop->iteration }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="supplier-info">
                                                <div class="supplier-avatar">
                                                    <i class="fas fa-truck"></i>
                                                </div>
                                                <div class="supplier-details">
                                                    <span class="supplier-name">{{ $supplier->company_name }}</span>
                                                    <span class="supplier-id">ID: {{ $supplier->id }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <span class="contact-name">{{ $supplier->supplier_name }}</span>
                                                <span class="contact-email">{{ $supplier->company_email }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="phone-info">
                                                <span class="phone-main">{{ $supplier->supplier_phone }}</span>
                                                <span class="phone-secondary">{{ $supplier->company_phone }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="location-info">
                                                <span class="location-text">{{ Str::limit($supplier->company_address, 50) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                @can('suppliers.show')
                                                    <button type="button" class="btn-action btn-view" onclick="showSupplierDetails({{ $supplier->id }})" data-toggle="tooltip" title="Ver Detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endcan
                                                @can('suppliers.edit')
                                                    <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn-action btn-edit" data-toggle="tooltip" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('suppliers.destroy')
                                                    <button type="button" class="btn-action btn-delete" onclick="deleteSupplier({{ $supplier->id }}, '{{ $supplier->company_name }}')" data-toggle="tooltip" title="Eliminar">
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
                            <span id="paginationInfo">Mostrando 1-{{ min(10, $suppliers->count()) }} de {{ $suppliers->count() }} registros</span>
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
                        @foreach ($suppliers as $supplier)
                            <div class="mobile-card" data-supplier-id="{{ $supplier->id }}" data-search="{{ strtolower($supplier->company_name . ' ' . $supplier->supplier_name . ' ' . $supplier->company_address) }}">
                                <div class="mobile-card-header">
                                    <div class="mobile-avatar">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                    <div class="mobile-info">
                                        <h4 class="mobile-title">{{ $supplier->company_name }}</h4>
                                        <span class="mobile-id">ID: {{ $supplier->id }}</span>
                                    </div>
                                    <div class="mobile-actions">
                                        @can('suppliers.show')
                                            <button type="button" class="mobile-btn mobile-btn-view" onclick="showSupplierDetails({{ $supplier->id }})" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endcan
                                        @can('suppliers.edit')
                                            <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="mobile-btn mobile-btn-edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('suppliers.destroy')
                                            <button type="button" class="mobile-btn mobile-btn-delete" onclick="deleteSupplier({{ $supplier->id }}, '{{ $supplier->company_name }}')" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="mobile-card-body">
                                    <p class="mobile-description">
                                        <strong>Contacto:</strong> {{ $supplier->supplier_name }}<br>
                                        <strong>Teléfono:</strong> {{ $supplier->supplier_phone }}
                                    </p>
                                    <div class="mobile-meta">
                                        <span class="mobile-email">{{ $supplier->company_email }}</span>
                                        <span class="mobile-location">{{ Str::limit($supplier->company_address, 40) }}</span>
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

{{-- Modal para mostrar proveedor --}}
<div class="modal fade" id="showSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-truck mr-2"></i>
                    Detalles del Proveedor
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    {{-- Información de la empresa --}}
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-building mr-2"></i>
                                    Información de la Empresa
                                </h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <th>Nombre:</th>
                                        <td id="companyName"></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td id="companyEmail"></td>
                                    </tr>
                                    <tr>
                                        <th>Teléfono:</th>
                                        <td id="companyPhone"></td>
                                    </tr>
                                    <tr>
                                        <th>Dirección:</th>
                                        <td id="companyAddress"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Información del contacto --}}
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-user mr-2"></i>
                                    Información del Contacto
                                </h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <th>Nombre:</th>
                                        <td id="supplierName"></td>
                                    </tr>
                                    <tr>
                                        <th>Teléfono:</th>
                                        <td id="supplierPhone"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Estadísticas del Proveedor --}}
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-box mr-2"></i>
                                    Resumen de Productos Distribuidos
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th class="text-center">Cantidad</th>
                                                <th class="text-right">Precio Unitario</th>
                                                <th class="text-right">Sub Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="productDetails">
                                            <!-- Los detalles se cargarán dinámicamente -->
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-right"><strong>Total General:</strong>
                                                </td>
                                                <td class="text-right"><strong id="grandTotal">0.00</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    /* ===== VARIABLES CSS MODERNAS ===== */
    :root {
        /* Colores principales */
        --primary-50: #f0f9ff;
        --primary-100: #e0f2fe;
        --primary-200: #bae6fd;
        --primary-300: #7dd3fc;
        --primary-400: #38bdf8;
        --primary-500: #0ea5e9;
        --primary-600: #0284c7;
        --primary-700: #0369a1;
        --primary-800: #075985;
        --primary-900: #0c4a6e;
        
        /* Colores secundarios */
        --secondary-50: #f8fafc;
        --secondary-100: #f1f5f9;
        --secondary-200: #e2e8f0;
        --secondary-300: #cbd5e1;
        --secondary-400: #94a3b8;
        --secondary-500: #64748b;
        --secondary-600: #475569;
        --secondary-700: #334155;
        --secondary-800: #1e293b;
        --secondary-900: #0f172a;
        
        /* Colores de estado */
        --success-50: #f0fdf4;
        --success-100: #dcfce7;
        --success-500: #22c55e;
        --success-600: #16a34a;
        --success-700: #15803d;
        
        --warning-50: #fffbeb;
        --warning-100: #fef3c7;
        --warning-500: #f59e0b;
        --warning-600: #d97706;
        --warning-700: #b45309;
        
        --danger-50: #fef2f2;
        --danger-100: #fee2e2;
        --danger-500: #ef4444;
        --danger-600: #dc2626;
        --danger-700: #b91c1c;
        
        --info-50: #eff6ff;
        --info-100: #dbeafe;
        --info-500: #3b82f6;
        --info-600: #2563eb;
        --info-700: #1d4ed8;
        
        /* Gradientes */
        --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --gradient-secondary: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        --gradient-success: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --gradient-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --gradient-info: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        
        /* Sombras */
        --shadow-xs: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
        
        /* Bordes */
        --border-radius-sm: 0.375rem;
        --border-radius-md: 0.5rem;
        --border-radius-lg: 0.75rem;
        --border-radius-xl: 1rem;
        --border-radius-2xl: 1.5rem;
        
        /* Transiciones */
        --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-normal: 300ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: 500ms cubic-bezier(0.4, 0, 0.2, 1);
        
        /* Espaciado */
        --spacing-xs: 0.25rem;
        --spacing-sm: 0.5rem;
        --spacing-md: 1rem;
        --spacing-lg: 1.5rem;
        --spacing-xl: 2rem;
        --spacing-2xl: 3rem;
    }

    /* ===== RESET Y BASE ===== */
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--secondary-50);
        color: var(--secondary-900);
        line-height: 1.6;
        margin: 0;
        padding: 0;
    }

    /* ===== FONDO Y CONTENEDOR PRINCIPAL ===== */
    .page-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 30%, rgba(102, 126, 234, 0.08) 0%, transparent 50%),
            radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.06) 0%, transparent 50%),
            radial-gradient(circle at 40% 80%, rgba(34, 197, 94, 0.05) 0%, transparent 50%),
            linear-gradient(135deg, var(--secondary-50) 0%, var(--secondary-100) 50%, var(--secondary-200) 100%);
        z-index: -1;
    }

    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: var(--spacing-lg);
        position: relative;
        z-index: 1;
    }

    /* ===== HEADER FLOTANTE ===== */
    .floating-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--border-radius-2xl);
        box-shadow: var(--shadow-xl);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-xl);
        position: sticky;
        top: var(--spacing-md);
        z-index: 100;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: var(--spacing-lg);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
    }

    .header-icon-wrapper {
        position: relative;
        width: 64px;
        height: 64px;
    }

    .header-icon {
        width: 100%;
        height: 100%;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.75rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        z-index: 2;
    }

    .icon-glow {
        position: absolute;
        inset: -8px;
        background: var(--gradient-primary);
        border-radius: 50%;
        opacity: 0.3;
        filter: blur(12px);
        z-index: 1;
    }

    .header-text {
        flex: 1;
    }

    .header-title {
        font-size: 2rem;
        font-weight: 800;
        color: var(--secondary-900);
        margin: 0;
        line-height: 1.2;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .header-subtitle {
        font-size: 1rem;
        color: var(--secondary-600);
        margin-top: var(--spacing-xs);
        font-weight: 500;
    }

    .header-actions {
        display: flex;
        gap: var(--spacing-md);
    }

    /* ===== BOTONES MODERNOS ===== */
    .btn-glass {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-md) var(--spacing-lg);
        border: 1px solid transparent;
        border-radius: var(--border-radius-lg);
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all var(--transition-normal);
        overflow: hidden;
    }

    .btn-glass:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .btn-secondary-glass {
        background: rgba(255, 255, 255, 0.9);
        border-color: var(--secondary-200);
        color: var(--secondary-700);
        box-shadow: var(--shadow-sm);
    }

    .btn-secondary-glass:hover {
        background: rgba(255, 255, 255, 1);
        border-color: var(--secondary-300);
        color: var(--secondary-800);
    }

    .btn-primary-glass {
        background: var(--gradient-primary);
        color: white;
        box-shadow: var(--shadow-md);
    }

    .btn-primary-glass:hover {
        box-shadow: var(--shadow-xl);
    }

    /* ===== DASHBOARD DE ESTADÍSTICAS ===== */
    .stats-dashboard {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--border-radius-2xl);
        box-shadow: var(--shadow-lg);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-xl);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-lg);
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: var(--border-radius-xl);
        padding: var(--spacing-lg);
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
        transition: all var(--transition-normal);
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }

    .stat-primary {
        border-color: rgba(102, 126, 234, 0.2);
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.05) 100%);
    }

    .stat-success {
        border-color: rgba(34, 197, 94, 0.2);
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(22, 163, 74, 0.05) 100%);
    }

    .stat-warning {
        border-color: rgba(245, 158, 11, 0.2);
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.05) 100%);
    }

    .stat-info {
        border-color: rgba(59, 130, 246, 0.2);
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--border-radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-primary .stat-icon {
        background: var(--gradient-primary);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .stat-success .stat-icon {
        background: var(--gradient-success);
        box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
    }

    .stat-warning .stat-icon {
        background: var(--gradient-warning);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
    }

    .stat-info .stat-icon {
        background: var(--gradient-info);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    }

    .stat-content {
        flex: 1;
    }

    .stat-value {
        font-size: 2.25rem;
        font-weight: 800;
        color: var(--secondary-900);
        margin: 0;
        line-height: 1;
    }

    .stat-label {
        color: var(--secondary-600);
        font-size: 0.875rem;
        font-weight: 600;
        margin-top: var(--spacing-xs);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-trend {
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: var(--spacing-sm);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-md);
        width: fit-content;
    }

    .stat-primary .stat-trend {
        color: var(--primary-700);
        background: rgba(102, 126, 234, 0.1);
    }

    .stat-success .stat-trend {
        color: var(--success-700);
        background: rgba(34, 197, 94, 0.1);
    }

    .stat-warning .stat-trend {
        color: var(--warning-700);
        background: rgba(245, 158, 11, 0.1);
    }

    .stat-info .stat-trend {
        color: var(--info-700);
        background: rgba(59, 130, 246, 0.1);
    }

    /* ===== SECCIÓN DE FILTROS ===== */
    .filters-section {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--border-radius-2xl);
        box-shadow: var(--shadow-lg);
        margin-bottom: var(--spacing-xl);
        overflow: hidden;
    }

    .filters-header {
        padding: var(--spacing-lg) var(--spacing-xl);
        border-bottom: 1px solid var(--secondary-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: background-color var(--transition-fast);
    }

    .filters-header:hover {
        background: rgba(248, 250, 252, 0.5);
    }

    .filters-title {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }

    .filters-icon {
        width: 40px;
        height: 40px;
        background: var(--gradient-primary);
        border-radius: var(--border-radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.125rem;
    }

    .filters-text h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--secondary-900);
        margin: 0;
    }

    .filters-text p {
        color: var(--secondary-600);
        margin: var(--spacing-xs) 0 0 0;
        font-size: 0.875rem;
    }

    .filters-toggle {
        background: none;
        border: none;
        color: var(--secondary-500);
        font-size: 1.125rem;
        cursor: pointer;
        padding: var(--spacing-sm);
        border-radius: var(--border-radius-md);
        transition: all var(--transition-fast);
    }

    .filters-toggle:hover {
        background: var(--secondary-100);
        color: var(--secondary-700);
    }

    .filters-content {
        padding: var(--spacing-xl);
        display: none;
    }

    .filters-content.show {
        display: block;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .filter-label {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--secondary-700);
    }

    .filter-label i {
        color: var(--primary-500);
        width: 16px;
    }

    .filter-input-wrapper {
        position: relative;
    }

    .filter-input {
        width: 100%;
        padding: var(--spacing-md) var(--spacing-lg);
        padding-left: 3rem;
        border: 2px solid var(--secondary-200);
        border-radius: var(--border-radius-lg);
        font-size: 0.875rem;
        background: white;
        transition: all var(--transition-fast);
    }

    .filter-input:focus {
        outline: none;
        border-color: var(--primary-500);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .filter-input-icon {
        position: absolute;
        left: var(--spacing-md);
        top: 50%;
        transform: translateY(-50%);
        color: var(--secondary-400);
        font-size: 1rem;
    }

    .filters-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: var(--spacing-lg);
        border-top: 1px solid var(--secondary-200);
    }

    .filters-status {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }

    .status-text {
        font-size: 0.875rem;
        color: var(--secondary-600);
        font-weight: 500;
    }

    .active-filters {
        display: flex;
        gap: var(--spacing-sm);
    }

    .filter-badge {
        background: var(--primary-100);
        color: var(--primary-700);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-md);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .filters-buttons {
        display: flex;
        gap: var(--spacing-md);
    }

    /* ===== BOTONES DE FILTROS ===== */
    .btn-modern {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-md) var(--spacing-lg);
        border: none;
        border-radius: var(--border-radius-lg);
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-normal);
        overflow: hidden;
    }

    .btn-apply {
        background: var(--gradient-primary);
        color: white;
        box-shadow: var(--shadow-sm);
    }

    .btn-apply:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .btn-clear {
        background: var(--secondary-100);
        color: var(--secondary-700);
        border: 1px solid var(--secondary-200);
    }

    .btn-clear:hover {
        background: var(--secondary-200);
        color: var(--secondary-800);
    }

    /* ===== CONTENEDOR DE CONTENIDO ===== */
    .content-container {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--border-radius-2xl);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }

    .content-card {
        width: 100%;
    }

    .content-header {
        padding: var(--spacing-xl);
        border-bottom: 1px solid var(--secondary-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: var(--spacing-lg);
    }

    .content-title {
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
    }

    .title-icon {
        width: 48px;
        height: 48px;
        background: var(--gradient-primary);
        border-radius: var(--border-radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }

    .title-text h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--secondary-900);
        margin: 0;
    }

    .title-text p {
        color: var(--secondary-600);
        margin: var(--spacing-xs) 0 0 0;
        font-size: 0.875rem;
    }

    .content-actions {
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
    }

    /* ===== BÚSQUEDA ===== */
    .search-container {
        position: relative;
    }

    .search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-icon {
        position: absolute;
        left: var(--spacing-md);
        color: var(--secondary-400);
        font-size: 1rem;
        z-index: 2;
    }

    .search-input {
        padding: var(--spacing-md) var(--spacing-lg);
        padding-left: 3rem;
        border: 2px solid var(--secondary-200);
        border-radius: var(--border-radius-lg);
        font-size: 0.875rem;
        background: white;
        min-width: 300px;
        transition: all var(--transition-fast);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary-500);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* ===== TOGGLES DE VISTA ===== */
    .view-toggles {
        display: flex;
        background: var(--secondary-100);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-xs);
        gap: var(--spacing-xs);
    }

    .view-toggle {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-sm) var(--spacing-md);
        border: none;
        border-radius: var(--border-radius-md);
        background: transparent;
        color: var(--secondary-600);
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-fast);
    }

    .view-toggle.active {
        background: white;
        color: var(--primary-700);
        box-shadow: var(--shadow-sm);
    }

    .view-toggle:hover:not(.active) {
        background: rgba(255, 255, 255, 0.5);
        color: var(--secondary-700);
    }

    /* ===== CUERPO DEL CONTENIDO ===== */
    .content-body {
        padding: var(--spacing-xl);
    }

    .desktop-view {
        width: 100%;
    }

    /* ===== VISTA DE TARJETAS ===== */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }

    .supplier-card {
        background: white;
        border: 1px solid var(--secondary-200);
        border-radius: var(--border-radius-xl);
        padding: var(--spacing-lg);
        transition: all var(--transition-normal);
        position: relative;
        overflow: hidden;
    }

    .supplier-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
        border-color: var(--primary-200);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-md);
    }

    .card-avatar {
        width: 48px;
        height: 48px;
        background: var(--gradient-primary);
        border-radius: var(--border-radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }

    .card-badge {
        background: var(--secondary-100);
        color: var(--secondary-600);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-md);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .card-body {
        margin-bottom: var(--spacing-lg);
    }

    .card-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--secondary-900);
        margin: 0 0 var(--spacing-sm) 0;
    }

    .card-description {
        color: var(--secondary-600);
        font-size: 0.875rem;
        line-height: 1.5;
        margin: 0 0 var(--spacing-md) 0;
    }

    .card-meta {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        color: var(--secondary-600);
        font-size: 0.875rem;
    }

    .meta-item i {
        color: var(--primary-500);
        width: 16px;
    }

    .card-actions {
        display: flex;
        gap: var(--spacing-sm);
    }

    .card-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-xs);
        padding: var(--spacing-sm) var(--spacing-md);
        border: none;
        border-radius: var(--border-radius-md);
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-fast);
        text-decoration: none;
    }

    .card-btn-view {
        background: var(--info-50);
        color: var(--info-700);
        border: 1px solid var(--info-200);
    }

    .card-btn-view:hover {
        background: var(--info-100);
        color: var(--info-800);
    }

    .card-btn-edit {
        background: var(--warning-50);
        color: var(--warning-700);
        border: 1px solid var(--warning-200);
    }

    .card-btn-edit:hover {
        background: var(--warning-100);
        color: var(--warning-800);
    }

    .card-btn-delete {
        background: var(--danger-50);
        color: var(--danger-700);
        border: 1px solid var(--danger-200);
    }

    .card-btn-delete:hover {
        background: var(--danger-100);
        color: var(--danger-800);
    }

    /* ===== VISTA DE TABLA ===== */
    .table-container {
        overflow-x: auto;
        border-radius: var(--border-radius-lg);
        border: 1px solid var(--secondary-200);
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }

    .modern-table thead {
        background: var(--secondary-50);
    }

    .modern-table th {
        padding: var(--spacing-lg);
        text-align: left;
        font-weight: 700;
        color: var(--secondary-700);
        border-bottom: 2px solid var(--secondary-200);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .th-content {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .th-content i {
        color: var(--primary-500);
        font-size: 0.875rem;
    }

    .modern-table td {
        padding: var(--spacing-lg);
        border-bottom: 1px solid var(--secondary-100);
        vertical-align: middle;
    }

    .modern-table tbody tr {
        transition: background-color var(--transition-fast);
    }

    .modern-table tbody tr:hover {
        background: var(--secondary-50);
    }

    .row-number {
        font-weight: 600;
        color: var(--secondary-600);
        font-size: 0.875rem;
    }

    .supplier-info {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }

    .supplier-avatar {
        width: 40px;
        height: 40px;
        background: var(--gradient-primary);
        border-radius: var(--border-radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
    }

    .supplier-details {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .supplier-name {
        font-weight: 700;
        color: var(--secondary-900);
        font-size: 0.875rem;
    }

    .supplier-id {
        color: var(--secondary-500);
        font-size: 0.75rem;
    }

    .contact-info {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .contact-name {
        font-weight: 600;
        color: var(--secondary-900);
        font-size: 0.875rem;
    }

    .contact-email {
        color: var(--secondary-600);
        font-size: 0.75rem;
    }

    .phone-info {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .phone-main {
        font-weight: 600;
        color: var(--secondary-900);
        font-size: 0.875rem;
    }

    .phone-secondary {
        color: var(--secondary-600);
        font-size: 0.75rem;
    }

    .location-info {
        color: var(--secondary-700);
        font-size: 0.875rem;
    }

    .action-buttons {
        display: flex;
        gap: var(--spacing-sm);
        justify-content: center;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: var(--border-radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all var(--transition-fast);
        text-decoration: none;
    }

    .btn-view {
        background: var(--gradient-info);
    }

    .btn-edit {
        background: var(--gradient-warning);
    }

    .btn-delete {
        background: var(--gradient-danger);
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    /* ===== PAGINACIÓN ===== */
    .custom-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-lg) var(--spacing-xl);
        border-top: 1px solid var(--secondary-200);
        background: var(--secondary-50);
    }

    .pagination-info {
        color: var(--secondary-600);
        font-size: 0.875rem;
        font-weight: 500;
    }

    .pagination-controls {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .pagination-btn {
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--secondary-200);
        border-radius: var(--border-radius-md);
        background: white;
        color: var(--secondary-700);
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-fast);
    }

    .pagination-btn:hover:not(:disabled) {
        background: var(--secondary-100);
        border-color: var(--secondary-300);
        color: var(--secondary-800);
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .page-numbers {
        display: flex;
        gap: var(--spacing-xs);
    }

    .page-number {
        width: 36px;
        height: 36px;
        border: 1px solid var(--secondary-200);
        border-radius: var(--border-radius-md);
        background: white;
        color: var(--secondary-700);
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-fast);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-number:hover {
        background: var(--secondary-100);
        border-color: var(--secondary-300);
        color: var(--secondary-800);
    }

    .page-number.active {
        background: var(--gradient-primary);
        border-color: transparent;
        color: white;
    }

    /* ===== VISTA MÓVIL ===== */
    .mobile-view {
        display: none;
    }

    .mobile-cards {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-md);
    }

    .mobile-card {
        background: white;
        border: 1px solid var(--secondary-200);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-md);
        transition: all var(--transition-normal);
    }

    .mobile-card:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary-200);
    }

    .mobile-card-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-md);
    }

    .mobile-avatar {
        width: 40px;
        height: 40px;
        background: var(--gradient-primary);
        border-radius: var(--border-radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
    }

    .mobile-info {
        flex: 1;
    }

    .mobile-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--secondary-900);
        margin: 0;
    }

    .mobile-id {
        color: var(--secondary-500);
        font-size: 0.75rem;
    }

    .mobile-actions {
        display: flex;
        gap: var(--spacing-xs);
    }

    .mobile-btn {
        width: 32px;
        height: 32px;
        border: none;
        border-radius: var(--border-radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all var(--transition-fast);
        text-decoration: none;
    }

    .mobile-btn-view {
        background: var(--gradient-info);
    }

    .mobile-btn-edit {
        background: var(--gradient-warning);
    }

    .mobile-btn-delete {
        background: var(--gradient-danger);
    }

    .mobile-card-body {
        border-top: 1px solid var(--secondary-100);
        padding-top: var(--spacing-md);
    }

    .mobile-description {
        color: var(--secondary-600);
        font-size: 0.875rem;
        margin: 0 0 var(--spacing-sm) 0;
    }

    .mobile-meta {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .mobile-email,
    .mobile-location {
        color: var(--secondary-500);
        font-size: 0.75rem;
    }

    /* ===== RESPONSIVE DESIGN ===== */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .filters-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .main-container {
            padding: var(--spacing-md);
        }

        .floating-header {
            padding: var(--spacing-lg);
        }

        .header-content {
            flex-direction: column;
            text-align: center;
            gap: var(--spacing-lg);
        }

        .header-left {
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .header-actions {
            flex-wrap: wrap;
            justify-content: center;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .content-header {
            flex-direction: column;
            text-align: center;
            gap: var(--spacing-lg);
        }

        .content-title {
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .content-actions {
            flex-direction: column;
            gap: var(--spacing-md);
            width: 100%;
        }

        .search-input {
            min-width: 100%;
        }

        .view-toggles {
            width: 100%;
            justify-content: center;
        }

        .cards-grid {
            grid-template-columns: 1fr;
        }

        .desktop-view {
            display: none !important;
        }

        .mobile-view {
            display: block;
        }

        .custom-pagination {
            flex-direction: column;
            gap: var(--spacing-md);
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .header-icon {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }

        .header-title {
            font-size: 1.5rem;
        }

        .stat-card {
            padding: var(--spacing-md);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }

        .stat-value {
            font-size: 1.75rem;
        }

        .btn-glass {
            padding: var(--spacing-sm) var(--spacing-md);
            font-size: 0.875rem;
        }
    }

    /* ===== UTILIDADES ===== */
    .desktop-only {
        display: block;
    }

    @media (max-width: 768px) {
        .desktop-only {
            display: none;
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
    let allSuppliers = [];
    let filteredSuppliers = [];

    // Inicializar la página
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Proveedores page loaded');
        initializeSuppliersPage();
        initializeEventListeners();
    });

    // Inicializar la página de proveedores
    function initializeSuppliersPage() {
        console.log('Initializing suppliers page...');
        
        // Cargar modo de vista guardado
        const savedViewMode = localStorage.getItem('suppliersViewMode');
        if (savedViewMode && (savedViewMode === 'table' || savedViewMode === 'cards')) {
            currentViewMode = savedViewMode;
            changeViewMode(savedViewMode);
        } else {
            // Modo por defecto: tarjetas
            changeViewMode('cards');
        }

        // Obtener todos los proveedores
        getAllSuppliers();
        
        // Mostrar primera página
        showPage(1);
    }

    // Obtener todos los proveedores
    function getAllSuppliers() {
        const tableRows = document.querySelectorAll('#suppliersTableBody tr');
        const supplierCards = document.querySelectorAll('.supplier-card');
        const mobileCards = document.querySelectorAll('.mobile-card');
        
        allSuppliers = [];
        
        // Procesar filas de tabla
        tableRows.forEach((row, index) => {
            const companyName = row.querySelector('.supplier-name').textContent.trim();
            const supplierName = row.querySelector('.contact-name').textContent.trim();
            
            allSuppliers.push({
                element: row,
                cardElement: supplierCards[index],
                mobileElement: mobileCards[index],
                data: {
                    id: row.dataset.supplierId,
                    company_name: companyName,
                    supplier_name: supplierName
                }
            });
        });
        
        filteredSuppliers = [...allSuppliers];
        console.log('Suppliers loaded:', allSuppliers.length);
    }

    // Cambiar modo de vista
    function changeViewMode(mode) {
        console.log('Changing view mode to:', mode);
        currentViewMode = mode;
        localStorage.setItem('suppliersViewMode', mode);
        
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
        document.querySelectorAll('#suppliersTableBody tr').forEach(row => row.style.display = 'none');
        document.querySelectorAll('.supplier-card').forEach(card => card.style.display = 'none');
        document.querySelectorAll('.mobile-card').forEach(card => card.style.display = 'none');
        
        // Mostrar solo los elementos de la página actual
        filteredSuppliers.slice(startIndex, endIndex).forEach((supplier, index) => {
            if (supplier.element) supplier.element.style.display = 'table-row';
            if (supplier.cardElement) supplier.cardElement.style.display = 'block';
            if (supplier.mobileElement) supplier.mobileElement.style.display = 'block';
            
            // Actualizar números de fila
            if (supplier.element) {
                supplier.element.querySelector('.row-number').textContent = startIndex + index + 1;
            }
        });
        
        // Actualizar información de paginación
        updatePaginationInfo(page, filteredSuppliers.length);
        updatePaginationControls(page, Math.ceil(filteredSuppliers.length / itemsPerPage));
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
    function filterSuppliers(searchTerm) {
        const searchLower = searchTerm.toLowerCase().trim();
        
        if (!searchLower) {
            filteredSuppliers = [...allSuppliers];
        } else {
            filteredSuppliers = allSuppliers.filter(supplier => {
                const companyMatch = supplier.data.company_name.toLowerCase().includes(searchLower);
                const supplierMatch = supplier.data.supplier_name.toLowerCase().includes(searchLower);
                return companyMatch || supplierMatch;
            });
        }
        
        currentPage = 1;
        showPage(1);
    }

    // Mostrar detalles de proveedor
    async function showSupplierDetails(supplierId) {
        console.log('Showing supplier details for ID:', supplierId);
        
        try {
            const response = await fetch(`/suppliers/${supplierId}`);
            const data = await response.json();
            
            if (data.icons === 'success') {
                // Llenar datos en el modal
                document.getElementById('companyName').textContent = data.supplier.company_name;
                document.getElementById('companyEmail').textContent = data.supplier.company_email;
                document.getElementById('companyPhone').textContent = data.supplier.company_phone;
                document.getElementById('companyAddress').textContent = data.supplier.company_address;
                document.getElementById('supplierName').textContent = data.supplier.supplier_name;
                document.getElementById('supplierPhone').textContent = data.supplier.supplier_phone;
                
                // Actualizar estadísticas de productos si existen
                if (data.stats) {
                    updateProductStats(data.stats);
                }
                
                // Mostrar modal
                document.getElementById('showSupplierModal').style.display = 'flex';
            } else {
                showAlert('Error', 'No se pudieron obtener los datos del proveedor', 'error');
            }
        } catch (error) {
            console.error('Error fetching supplier details:', error);
            showAlert('Error', 'No se pudieron obtener los datos del proveedor', 'error');
        }
    }

    // Cerrar modal de proveedor
    function closeSupplierModal() {
        document.getElementById('showSupplierModal').style.display = 'none';
    }

    // Eliminar proveedor
    function deleteSupplier(supplierId, supplierName) {
        console.log('Deleting supplier:', supplierId, supplierName);
        
        showConfirmDialog(
            '¿Estás seguro?',
            `¿Deseas eliminar el proveedor <strong>${supplierName}</strong>?<br><small class="text-muted">Esta acción no se puede revertir</small>`,
            'warning',
            () => performDeleteSupplier(supplierId)
        );
    }

    // Realizar eliminación de proveedor
    async function performDeleteSupplier(supplierId) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch(`/suppliers/delete/${supplierId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('¡Eliminado!', data.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert('Error', data.message, 'error');
            }
        } catch (error) {
            console.error('Error deleting supplier:', error);
            showAlert('Error', 'No se pudo eliminar el proveedor', 'error');
        }
    }

    // Actualizar estadísticas de productos
    function updateProductStats(stats) {
        const detailsContainer = document.getElementById('productDetails');
        let detailsHTML = '';
        let grandTotal = 0;

        if (stats && stats.length > 0) {
            stats.forEach(product => {
                const subtotal = product.stock * product.purchase_price;
                grandTotal += subtotal;

                detailsHTML += `
                    <tr>
                        <td>${product.name}</td>
                        <td class="text-center">
                            <span class="badge badge-primary">${product.stock}</span>
                        </td>
                        <td class="text-right">{{ $currency->symbol }} ${number_format(product.purchase_price)}</td>
                        <td class="text-right">{{ $currency->symbol }} ${number_format(subtotal)}</td>
                    </tr>`;
            });
        } else {
            detailsHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        No hay productos registrados para este proveedor
                    </td>
                </tr>`;
        }

        detailsContainer.innerHTML = detailsHTML;
        document.getElementById('grandTotal').innerHTML = `{{ $currency->symbol }} ${number_format(grandTotal)}`;
    }

    // Función para formatear números
    function number_format(number, decimals = 2) {
        return number.toLocaleString('es-PE', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
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
                filterSuppliers(searchTerm);
            });
        }
        
        // Búsqueda en filtros
        const supplierSearch = document.getElementById('supplierSearch');
        if (supplierSearch) {
            supplierSearch.addEventListener('keyup', function() {
                const searchTerm = this.value;
                filterSuppliers(searchTerm);
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
            const totalPages = Math.ceil(filteredSuppliers.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });
        
        // Aplicar filtros
        document.getElementById('applyFilters').addEventListener('click', function() {
            const searchTerm = document.getElementById('supplierSearch').value;
            filterSuppliers(searchTerm);
        });
        
        // Limpiar filtros
        document.getElementById('clearFilters').addEventListener('click', function() {
            document.getElementById('supplierSearch').value = '';
            filterSuppliers('');
        });
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('showSupplierModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSupplierModal();
            }
        });
        
        console.log('Event listeners initialized');
    }
</script>
@endpush
