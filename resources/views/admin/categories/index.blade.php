@extends('adminlte::page')

@section('title', 'Gestión de Categorías')

@section('content_header')
    <div class="hero-section mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            <i class="fas fa-tags-gradient"></i>
                            Gestión de Categorías
                        </h1>
                        <p class="hero-subtitle">Organiza y gestiona las categorías de productos del sistema</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <div class="hero-action-buttons d-flex justify-content-lg-end justify-content-center align-items-center gap-3 flex-wrap">
            @can('categories.report')
                            <a href="{{ route('admin.categories.report') }}" class="hero-btn hero-btn-secondary" data-toggle="tooltip" title="Generar Reporte" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                                <span class="d-none d-md-inline">Reporte</span>
                </a>
            @endcan
            @can('categories.create')
                            <a href="{{ route('admin.categories.create') }}" class="hero-btn hero-btn-primary" data-toggle="tooltip" title="Crear Categoría">
                                <i class="fas fa-plus"></i>
                                <span class="d-none d-md-inline">Nueva Categoría</span>
                </a>
            @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .hero-action-buttons {
        gap: 1rem !important;
    }
    .hero-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.85);
        color: var(--primary-color);
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 0.7rem 1.2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        transition: all 0.2s;
        cursor: pointer;
        min-width: 44px;
        min-height: 44px;
        position: relative;
        text-decoration: none;
        outline: none;
    }
    .hero-btn i {
        font-size: 1.3rem;
        color: var(--primary-color);
        margin-right: 0.2rem;
    }
    .hero-btn-secondary { color: #f5576c; }
    .hero-btn-secondary i { color: #f5576c; }
    .hero-btn-primary { color: #667eea; }
    .hero-btn-primary i { color: #667eea; }
    .hero-btn:hover, .hero-btn:focus {
        background: #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        transform: translateY(-2px) scale(1.04);
        color: var(--primary-color);
        text-decoration: none;
    }
    .hero-btn:active {
        transform: scale(0.97);
    }
    .hero-btn span {
        font-size: 1rem;
        font-weight: 600;
        color: inherit;
        white-space: nowrap;
    }
    @media (max-width: 991px) {
        .hero-action-buttons {
            justify-content: center !important;
        }
    }
    @media (max-width: 767px) {
        .hero-btn span {
            display: none !important;
        }
        .hero-btn {
            padding: 0.7rem !important;
            min-width: 44px;
        }
    }
    </style>
@stop

@section('content')
    {{-- Filtros avanzados --}}
    <div class="exchange-filters-card mb-4">
        <div class="filters-header" id="filtersToggle">
            <div class="filters-title">
                <i class="fas fa-filter"></i>
                <span>Filtros Avanzados</span>
            </div>
            <i class="fas fa-chevron-down toggle-icon"></i>
        </div>
        <div class="filters-content" id="filtersContent">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="categorySearch">Buscar por nombre o descripción</label>
                        <input type="text" class="form-control filter-input" id="categorySearch" placeholder="Buscar categoría...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="sortBy">Ordenar por</label>
                        <select class="form-control filter-input" id="sortBy">
                            <option value="name">Nombre</option>
                            <option value="created_at">Fecha de creación</option>
                            <option value="updated_at">Última actualización</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="sortOrder">Orden</label>
                        <select class="form-control filter-input" id="sortOrder">
                            <option value="asc">Ascendente</option>
                            <option value="desc">Descendente</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="filters-actions">
                <button type="button" class="btn btn-primary" id="applyFilters">
                    <i class="fas fa-search"></i> Aplicar Filtros
                </button>
                <button type="button" class="btn btn-secondary" id="clearFilters">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>
        </div>
    </div>

    {{-- Contenedor principal --}}
    <div class="categories-container">
        {{-- Vista de tabla moderna --}}
        <div class="table-view" id="tableView">
            <div class="modern-table-container">
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
                                            <button type="button" class="btn-action btn-view show-category"
                                            data-id="{{ $category->id }}" data-toggle="tooltip" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    @can('categories.edit')
                                        <a href="{{ route('admin.categories.edit', $category->id) }}"
                                                class="btn-action btn-edit" data-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('categories.destroy')
                                            <button type="button" class="btn-action btn-delete delete-category"
                                            data-id="{{ $category->id }}" data-toggle="tooltip" title="Eliminar">
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
    </div>

    {{-- Modal para mostrar categoría --}}
    <div class="modal fade" id="showCategoryModal" tabindex="-1" role="dialog" aria-labelledby="showCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showCategoryModalLabel">
                        <i class="fas fa-tag mr-2"></i>
                        Detalles de la Categoría
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Nombre:</label>
                                <p id="categoryName" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Descripción:</label>
                                <p id="categoryDescription" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Fecha de Creación:</label>
                                <p id="categoryCreated" class="form-control-static"></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Última Actualización:</label>
                                <p id="categoryUpdated" class="form-control-static"></p>
                            </div>
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
@stop

@section('css')
    <style>
        :root {
            --primary-color: #667eea;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-color: #2d3748;
            --light-color: #f7fafc;
            --border-color: #e2e8f0;
            --shadow-light: 0 2px 8px rgba(0,0,0,0.07);
            --shadow-medium: 0 4px 16px rgba(0,0,0,0.12);
        }

        /* Hero Section */
        .hero-section {
            background: var(--gradient-primary);
            border-radius: 20px;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-medium);
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .hero-title i {
            font-size: 2rem;
            background: rgba(255,255,255,0.2);
            padding: 1rem;
            border-radius: 50%;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        .hero-stats {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 12px;
            min-width: 120px;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            text-align: center;
        }

        /* Filtros avanzados */
        .exchange-filters-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-light);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .filters-header {
            background: var(--gradient-primary);
            color: white;
            padding: 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .filters-header:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }

        .filters-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .toggle-icon {
            transition: transform 0.3s ease;
        }

        .filters-header.rotated .toggle-icon {
            transform: rotate(180deg);
        }

        .filters-content {
            padding: 2rem;
            display: none;
        }

        .filters-content.show {
            display: block;
        }

        .filters-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            justify-content: flex-end;
        }

        /* Contenedor principal */
        .categories-container {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-light);
            overflow: hidden;
        }

        /* Tabla moderna */
        .modern-table-container {
            overflow-x: auto;
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
            gap: 0.5rem;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .modern-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .table-row {
            transition: background-color 0.2s ease;
        }

        .table-row:hover {
            background: var(--light-color);
        }

        .row-number {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .category-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .category-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 1.2rem;
        }

        .category-details {
            display: flex;
            flex-direction: column;
        }

        .category-name {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .category-id {
            color: #718096;
            font-size: 0.8rem;
        }

        .description-info {
            max-width: 300px;
        }

        .description-text {
            color: #4a5568;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .date-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .date-main {
            font-weight: 600;
            color: var(--dark-color);
        }

        .date-time {
            color: #718096;
            font-size: 0.8rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn-action {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .btn-view {
            background: #48bb78;
            color: white;
        }

        .btn-edit {
            background: #ed8936;
            color: white;
        }

        .btn-delete {
            background: #f56565;
            color: white;
        }

        .btn-action:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-medium);
        }

        /* Paginación personalizada */
        .custom-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            background: white;
            border-top: 1px solid var(--border-color);
        }

        .pagination-info {
            color: #64748b;
            font-size: 0.9rem;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pagination-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            background: white;
            color: #64748b;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .pagination-btn:hover:not(:disabled) {
            background: var(--light-color);
            border-color: #cbd5e0;
            color: #4a5568;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-numbers {
            display: flex;
            gap: 0.25rem;
        }

        .page-number {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: white;
            color: #64748b;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .page-number:hover {
            background: var(--light-color);
            border-color: #cbd5e0;
        }

        .page-number.active {
            background: var(--gradient-primary);
            color: white;
            border-color: var(--primary-color);
        }

        /* Modal moderno */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: var(--shadow-medium);
        }

        .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: 16px 16px 0 0;
        }

        .modal-title {
            color: white;
        }

        .close {
            color: white;
            opacity: 0.8;
        }

        .close:hover {
            color: white;
            opacity: 1;
        }

        .form-control-static {
            padding: 0.75rem;
            background: var(--light-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-stats {
                gap: 1rem;
            }

            .stat-item {
                min-width: 100px;
                padding: 0.75rem;
            }

            .filters-content {
                padding: 1rem;
            }

            .filters-actions {
                flex-direction: column;
            }

            .custom-pagination {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }

            .btn-action {
                width: 30px;
                height: 30px;
                font-size: 0.8rem;
            }
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('vendor/config.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Cargar SweetAlert2
            loadSweetAlert2(function() {
                // Variables para paginación
                let currentPage = 1;
                const itemsPerPage = 10;
                let allCategories = [];
                let filteredCategories = [];

                // Obtener todas las filas de la tabla
                function getAllCategories() {
                    const rows = $('#categoriesTableBody tr');
                    allCategories = [];
                    rows.each(function(index) {
                        const $row = $(this);
                        const $categoryName = $row.find('.category-name');
                        const $descriptionText = $row.find('.description-text');
                        
                        allCategories.push({
                            element: $row,
                            data: {
                                id: $row.data('category-id'),
                                name: $categoryName.text().trim(),
                                description: $descriptionText.text().trim()
                            }
                        });
                    });
                    filteredCategories = [...allCategories];
                }

                // Función para mostrar página específica
                function showPage(page) {
                    const startIndex = (page - 1) * itemsPerPage;
                    const endIndex = startIndex + itemsPerPage;
                    
                    // Ocultar todas las filas
                    $('#categoriesTableBody tr').hide();
                    
                    // Mostrar solo las filas de la página actual
                    filteredCategories.slice(startIndex, endIndex).forEach((category, index) => {
                        category.element.show();
                        // Actualizar números de fila
                        category.element.find('.row-number').text(startIndex + index + 1);
                    });
                    
                    // Actualizar información de paginación
                    updatePaginationInfo(page, filteredCategories.length);
                    updatePaginationControls(page, Math.ceil(filteredCategories.length / itemsPerPage));
                }

                // Actualizar información de paginación
                function updatePaginationInfo(currentPage, totalItems) {
                    const startItem = (currentPage - 1) * itemsPerPage + 1;
                    const endItem = Math.min(currentPage * itemsPerPage, totalItems);
                    $('#paginationInfo').text(`Mostrando ${startItem}-${endItem} de ${totalItems} registros`);
                }

                // Actualizar controles de paginación
                function updatePaginationControls(currentPage, totalPages) {
                    const $prevBtn = $('#prevPage');
                    const $nextBtn = $('#nextPage');
                    const $pageNumbers = $('#pageNumbers');
                    
                    // Habilitar/deshabilitar botones
                    $prevBtn.prop('disabled', currentPage === 1);
                    $nextBtn.prop('disabled', currentPage === totalPages);
                    
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
                            <button class="page-number ${i === currentPage ? 'active' : ''}" data-page="${i}">
                                ${i}
                            </button>
                        `;
                    }
                    
                    $pageNumbers.html(pageNumbersHTML);
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
                    showPage(currentPage);
                }

                // Inicializar
                getAllCategories();
                showPage(1);

                // Event listeners para paginación
                $(document).on('click', '#prevPage', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        showPage(currentPage);
                    }
                });

                $(document).on('click', '#nextPage', function() {
                    const totalPages = Math.ceil(filteredCategories.length / itemsPerPage);
                    if (currentPage < totalPages) {
                        currentPage++;
                        showPage(currentPage);
                    }
                });

                $(document).on('click', '.page-number', function() {
                    const page = parseInt($(this).data('page'));
                    currentPage = page;
                    showPage(currentPage);
                });

                // Búsqueda en tiempo real
                $('#categorySearch').on('keyup', function() {
                    const searchTerm = $(this).val();
                    filterCategories(searchTerm);
                });

                // Toggle de filtros
                $('#filtersToggle').click(function() {
                    const content = $('#filtersContent');
                    const toggle = $(this);
                    
                    content.toggleClass('show');
                    toggle.toggleClass('rotated');
                });

                // Aplicar filtros
                $('#applyFilters').click(function() {
                    const searchTerm = $('#categorySearch').val();
                    filterCategories(searchTerm);
                });

                // Limpiar filtros
                $('#clearFilters').click(function() {
                    $('#categorySearch').val('');
                    filterCategories('');
            });

            // Manejo de visualización de categoría
            $('.show-category').click(function() {
                const categoryId = $(this).data('id');

                // Mostrar loading
                Swal.fire({
                    title: 'Cargando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Obtener datos de la categoría
                $.ajax({
                    url: `/categories/${categoryId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Llenar datos en el modal
                            $('#categoryName').text(response.category.name);
                            $('#categoryDescription').text(response.category.description);
                            $('#categoryCreated').text(response.category.created_at);
                            $('#categoryUpdated').text(response.category.updated_at);

                            // Cerrar loading y mostrar modal
                            Swal.close();
                            $('#showCategoryModal').modal('show');
                        } else {
                            Swal.fire('Error',
                                'No se pudieron obtener los datos de la categoría', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron obtener los datos de la categoría',
                            'error');
                    }
                });
            });

            // Manejo de eliminación de categorías
            $('.delete-category').click(function() {
                const categoryId = $(this).data('id');

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
                            url: `/categories/delete/${categoryId}`,
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
                                Swal.fire('Error', response.message ||
                                    'No se pudo eliminar la categoría', 'error');
                            }
                        });
                    }
                });
                });

                // Inicializar tooltips
                $('[data-toggle="tooltip"]').tooltip();
                
            });
        });
    </script>
@stop
