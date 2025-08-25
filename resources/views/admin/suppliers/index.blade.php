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
                @if($permissions['suppliers.report'])
                    <a href="{{ route('admin.suppliers.report') }}" class="btn-glass btn-secondary-glass" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                        <span class="btn-text">Reporte</span>
                        <div class="btn-ripple"></div>
                    </a>
                @endif
                @if($permissions['suppliers.create'])
                    <a href="{{ route('admin.suppliers.create') }}" class="btn-glass btn-primary-glass">
                        <i class="fas fa-plus-circle"></i>
                        <span class="btn-text">Nuevo Proveedor</span>
                        <div class="btn-ripple"></div>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Dashboard -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6">
        <!-- Total de Proveedores -->
        <x-dashboard-widget 
            title="Total de Proveedores"
            value="{{ $totalSuppliers }}"
            valueType="number"
            icon="fas fa-truck"
            trend="Registrados"
            trendIcon="fas fa-plus-circle"
            trendColor="text-green-300"
            gradientFrom="from-blue-500"
            gradientTo="to-blue-600"
            progressWidth="100%"
            progressGradientFrom="from-blue-400"
            progressGradientTo="to-blue-500"
        />

        <!-- Proveedores Activos -->
        <x-dashboard-widget 
            title="Proveedores Activos"
            value="{{ $activeSuppliers }}"
            valueType="number"
            icon="fas fa-check-circle"
            trend="Activos"
            trendIcon="fas fa-check"
            trendColor="text-green-300"
            subtitle="{{ $totalSuppliers > 0 ? round(($activeSuppliers / $totalSuppliers) * 100, 1) . '% del total' : '0% del total' }}"
            subtitleIcon="fas fa-percentage"
            gradientFrom="from-green-500"
            gradientTo="to-emerald-600"
            progressWidth="{{ $totalSuppliers > 0 ? ($activeSuppliers / $totalSuppliers) * 100 : 0 }}%"
            progressGradientFrom="from-green-400"
            progressGradientTo="to-emerald-500"
        />

        <!-- Proveedores Este Mes -->
        <x-dashboard-widget 
            title="Este Mes"
            value="{{ $recentSuppliers }}"
            valueType="number"
            icon="fas fa-user-plus"
            trend="Nuevos"
            trendIcon="fas fa-calendar-month"
            trendColor="text-yellow-300"
            subtitle="{{ $totalSuppliers > 0 ? round(($recentSuppliers / $totalSuppliers) * 100, 1) . '% del total' : '0% del total' }}"
            subtitleIcon="fas fa-percentage"
            gradientFrom="from-yellow-500"
            gradientTo="to-orange-500"
            progressWidth="{{ $totalSuppliers > 0 ? ($recentSuppliers / $totalSuppliers) * 100 : 0 }}%"
            progressGradientFrom="from-yellow-400"
            progressGradientTo="to-orange-400"
        />

        <!-- Proveedores Inactivos -->
        <x-dashboard-widget 
            title="Inactivos"
            value="{{ $inactiveSuppliers }}"
            valueType="number"
            icon="fas fa-user-slash"
            trend="Inactivos"
            trendIcon="fas fa-pause-circle"
            trendColor="text-red-300"
            subtitle="{{ $totalSuppliers > 0 ? round(($inactiveSuppliers / $totalSuppliers) * 100, 1) . '% del total' : '0% del total' }}"
            subtitleIcon="fas fa-percentage"
            gradientFrom="from-red-500"
            gradientTo="to-pink-600"
            progressWidth="{{ $totalSuppliers > 0 ? ($inactiveSuppliers / $totalSuppliers) * 100 : 0 }}%"
            progressGradientFrom="from-red-400"
            progressGradientTo="to-pink-500"
        />
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
                            <input type="text" 
                                   placeholder="Buscar proveedores..." 
                                   id="searchInput" 
                                   value="{{ request('search') }}"
                                   class="search-input">
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
                                    @if($permissions['suppliers.show'])
                                        <button type="button" class="card-btn card-btn-view" onclick="showSupplierDetails({{ $supplier->id }})" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                    @if($permissions['suppliers.edit'])
                                        <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="card-btn card-btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if($permissions['suppliers.destroy'])
                                        <button type="button" class="card-btn card-btn-delete" onclick="deleteSupplier({{ $supplier->id }}, '{{ $supplier->company_name }}')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Paginación del servidor para tarjetas --}}
                    @if($suppliers->hasPages())
                    <div class="custom-pagination">
                        <div class="pagination-info">
                            <span>Mostrando {{ $suppliers->firstItem() ?? 0 }} a {{ $suppliers->lastItem() ?? 0 }} de {{ $suppliers->total() }} registros</span>
                        </div>
                        <div class="pagination-controls">
                            {{ $suppliers->appends(request()->query())->links() }}
                        </div>
                    </div>
                    @endif
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
                                                @if($permissions['suppliers.show'])
                                                    <button type="button" class="btn-action btn-view" onclick="showSupplierDetails({{ $supplier->id }})" data-toggle="tooltip" title="Ver Detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endif
                                                @if($permissions['suppliers.edit'])
                                                    <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn-action btn-edit" data-toggle="tooltip" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                                @if($permissions['suppliers.destroy'])
                                                    <button type="button" class="btn-action btn-delete" onclick="deleteSupplier({{ $supplier->id }}, '{{ $supplier->company_name }}')" data-toggle="tooltip" title="Eliminar">
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

                    {{-- Paginación del servidor para tabla --}}
                    @if($suppliers->hasPages())
                    <div class="custom-pagination">
                        <div class="pagination-info">
                            <span>Mostrando {{ $suppliers->firstItem() ?? 0 }} a {{ $suppliers->lastItem() ?? 0 }} de {{ $suppliers->total() }} registros</span>
                        </div>
                        <div class="pagination-controls">
                            {{ $suppliers->appends(request()->query())->links() }}
                        </div>
                    </div>
                    @endif
                </div>


            </div>
        </div>
    </div>
</div>

{{-- Modal para mostrar proveedor --}}
<div class="modal-overlay" id="showSupplierModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">
                <div class="title-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="title-text">
                    <h3>Detalles del Proveedor</h3>
                    <p>Información completa del proveedor seleccionado</p>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeSupplierModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="modal-content-grid">
                {{-- Información de la empresa --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h4>Información de la Empresa</h4>
                    </div>
                    <div class="info-card-body">
                        <div class="info-item">
                            <span class="info-label">Nombre:</span>
                            <span class="info-value" id="companyName"></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value" id="companyEmail"></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Teléfono:</span>
                            <span class="info-value" id="companyPhone"></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Dirección:</span>
                            <span class="info-value" id="companyAddress"></span>
                        </div>
                    </div>
                </div>

                {{-- Información del contacto --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4>Información del Contacto</h4>
                    </div>
                    <div class="info-card-body">
                        <div class="info-item">
                            <span class="info-label">Nombre:</span>
                            <span class="info-value" id="supplierName"></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Teléfono:</span>
                            <span class="info-value" id="supplierPhone"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estadísticas del Proveedor --}}
            <div class="stats-section" id="productsDistributedSection" style="display: none;">
                <div class="stats-card">
                    <div class="stats-card-header">
                        <div class="stats-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h4>Resumen de Productos Distribuidos</h4>
                    </div>
                    <div class="stats-card-body">
                        <div class="table-responsive">
                            <table class="stats-table">
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
                                        <td colspan="3" class="text-right"><strong>Total General:</strong></td>
                                        <td class="text-right"><strong id="grandTotal">0.00</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn-modern btn-secondary" onclick="closeSupplierModal()">
                <i class="fas fa-times"></i>
                <span>Cerrar</span>
            </button>
        </div>
    </div>
</div>
@stop

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/suppliers/index.css') }}">
@endpush
@push('js')
    <script src="{{ asset('js/admin/suppliers/index.js') }}"></script>
@endpush
