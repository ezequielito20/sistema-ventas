@extends('adminlte::page')

@section('title', 'Gestión de Productos')

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
        <h1 class="text-dark font-weight-bold mb-2 mb-md-0">Gestión de Productos</h1>
        <div class="d-flex">
            <a href="{{ route('admin.products.report') }}" class="btn btn-info btn-sm mr-2" target="_blank">
                <i class="fas fa-file-pdf mr-1 d-md-inline d-none"></i>
                <span class="d-md-inline d-none">Reporte</span>
                <i class="fas fa-file-pdf d-md-none"></i>
            </a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle mr-1 d-md-inline d-none"></i>
                <span class="d-md-inline d-none">Nuevo Producto</span>
                <i class="fas fa-plus-circle d-md-none"></i>
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Widgets de Estadísticas --}}
    <div class="row">
        <div class="col-lg-3 col-md-6 col-6 mb-3">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3 class="mb-1">{{ $totalProducts }}</h3>
                    <p class="mb-0 small">Total Productos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6 mb-3">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3 class="mb-1 text-truncate">{{ $currency->symbol }} {{ number_format($totalPurchaseValue, 2) }}</h3>
                    <p class="mb-0 small">
                        <span class="d-md-inline d-none">Valor de Compra del Inventario</span>
                        <span class="d-md-none">Valor Compra</span>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6 mb-3">
            <div class="small-box bg-success shadow-sm">
                <div class="inner">
                    <h3 class="mb-1 text-truncate">{{ $currency->symbol }} {{ number_format($totalSaleValue, 2) }}</h3>
                    <p class="mb-0 small">
                        <span class="d-md-inline d-none">Valor de Venta del Inventario</span>
                        <span class="d-md-none">Valor Venta</span>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-cash-register"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6 mb-3">
            <div class="small-box bg-primary shadow-sm">
                <div class="inner">
                    <h3 class="mb-1 text-truncate">{{ $currency->symbol }} {{ number_format($potentialProfit, 2) }}</h3>
                    <p class="mb-0 small">
                        <span class="d-md-inline d-none">Ganancia Potencial ({{ number_format($profitPercentage, 2) }}%)</span>
                        <span class="d-md-none">Ganancia ({{ number_format($profitPercentage, 2) }}%)</span>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Productos --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <h3 class="card-title mb-2 mb-md-0">
                <i class="fas fa-boxes mr-2"></i>
                Lista de Productos
            </h3>
                
                <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center">
                    <div class="input-group input-group-sm mb-2 mb-md-0 mr-md-2" style="width: 100%; max-width: 250px;">
                        <input type="text" id="productSearch" class="form-control" placeholder="Buscar producto...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="btn-group-mobile mb-2 mb-md-0 mr-md-2">
                        <button type="button" class="btn btn-sm btn-outline-primary active filter-btn"
                            data-filter="all">Todos</button>
                        <button type="button" class="btn btn-sm btn-outline-danger filter-btn"
                            data-filter="low">
                            <span class="d-md-inline d-none">Stock Bajo</span>
                            <span class="d-md-none">Bajo</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success filter-btn"
                            data-filter="normal">
                            <span class="d-md-inline d-none">Stock Normal</span>
                            <span class="d-md-none">Normal</span>
                        </button>
                    </div>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Vista de tabla para pantallas grandes --}}
            <div class="d-none d-lg-block">
            <table id="productsTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th style="width: 120px">Categoría</th>
                        <th>Stock</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td><small><strong>{{ $product->code }}</strong></small></td>
                            <td>
                                <div class="d-flex align-items-center">
                                                                                    <img src="{{ $product->image_url }}"
                                        alt="{{ $product->name }}" class="product-thumbnail mr-2">
                                    {{ $product->name }}
                                </div>
                            </td>
                            <td>
                                <span class="">
                                    <i class=""></i>
                                    {{ $product->category->name }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="badge badge-{{ $product->stock_status_label === 'Bajo' ? 'danger' : ($product->stock_status_label === 'Normal' ? 'warning' : 'success') }}">
                                    <i class="fas fa-boxes mr-1"></i>
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td>
                                <span class="text-primary">
                                    <strong>{{ $currency->symbol }}
                                        {{ number_format($product->purchase_price, 2) }}</strong>
                                </span>
                            </td>
                            <td>
                                <span class="text-success">
                                    <strong>{{ $currency->symbol }} {{ number_format($product->sale_price, 2) }}</strong>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-info btn-sm show-product"
                                        data-id="{{ $product->id }}" data-toggle="tooltip" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.products.edit', $product->id) }}"
                                        class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm delete-product"
                                        data-id="{{ $product->id }}" data-toggle="tooltip" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            {{-- Vista de tarjetas para móviles --}}
            <div class="d-lg-none">
                {{-- Barra de búsqueda para móviles --}}
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="mobileSearch" placeholder="Buscar producto...">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="row" id="mobileProductsContainer">
                    @foreach ($products as $product)
                        <div class="col-12 mb-3 product-card" data-stock-status="{{ $product->stock_status_label }}">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="product-image mr-3">
                                                <img src="{{ $product->image_url }}"
                                                    alt="{{ $product->name }}" class="rounded"
                                                    style="width: 60px; height: 60px; object-fit: cover;">
                                            </div>
                                            <div>
                                                <h6 class="mb-1 font-weight-bold product-name">{{ $product->name }}</h6>
                                                <small class="text-muted product-code">
                                                    <i class="fas fa-barcode mr-1"></i>
                                                    {{ $product->code }}
                                                </small>
                                                <br>
                                                <small class="text-muted product-category">
                                                    <i class="fas fa-tag mr-1"></i>
                                                    {{ $product->category->name }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-{{ $product->stock_status_label === 'Bajo' ? 'danger' : ($product->stock_status_label === 'Normal' ? 'warning' : 'success') }}">
                                                <i class="fas fa-boxes mr-1"></i>
                                                {{ $product->stock }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Precio Compra:</small>
                                            <div class="font-weight-bold text-primary">
                                                {{ $currency->symbol }} {{ number_format($product->purchase_price, 2) }}
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Precio Venta:</small>
                                            <div class="font-weight-bold text-success">
                                                {{ $currency->symbol }} {{ number_format($product->sale_price, 2) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="btn-group-mobile">
                                        <button type="button" class="btn btn-info btn-sm show-product"
                                            data-id="{{ $product->id }}">
                                            <i class="fas fa-eye d-md-none"></i>
                                            <span class="d-none d-md-inline">Ver</span>
                                        </button>
                                        <a href="{{ route('admin.products.edit', $product->id) }}"
                                            class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit d-md-none"></i>
                                            <span class="d-none d-md-inline">Editar</span>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm delete-product"
                                            data-id="{{ $product->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <style>
        .small-box {
            transition: transform .3s;
        }

        .small-box:hover {
            transform: translateY(-5px);
        }

        /* Estilos de tabla mejorados */
        .table td {
            vertical-align: middle !important;
        }

        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        /* Estilos de imagen de producto */
        .product-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            transition: transform .2s;
        }

        .product-thumbnail:hover {
            transform: scale(1.1);
        }

        /* Estilos de badges */
        .badge {
            padding: 8px 12px;
            font-size: 0.9rem;
            transition: all .2s;
        }

        .badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Estilos de botones */
        .btn-group .btn {
            margin: 0 2px;
            transition: all .2s;
        }

        .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Estilos para precios */
        .text-primary strong,
        .text-success strong {
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {

            .table td,
            .table th {
                padding: 0.5rem;
                font-size: 0.9rem;
            }

            .product-thumbnail {
                width: 40px;
                height: 40px;
            }
        }

        /* Ancho fijo para columna de categoría */
        #productsTable th:nth-child(3),
        #productsTable td:nth-child(3) {
            width: 120px;
            max-width: 120px;
        }

        /* Estilos responsive para botones */
        .btn-group-mobile {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .btn-group-mobile .btn {
            flex: 1;
            min-width: auto;
        }

        /* Estilos para tarjetas de productos */
        .product-card {
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-2px);
        }

        .product-card .card {
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
        }

        .product-card .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .product-image img {
            border: 2px solid #e3e6f0;
            transition: all 0.3s ease;
        }

        .product-image img:hover {
            border-color: #007bff;
        }

        /* Responsive breakpoints */
        @media (max-width: 576px) {
            .btn-group-mobile {
                flex-direction: row;
                gap: 0.25rem;
                justify-content: center;
            }
            
            .btn-group-mobile .btn {
                flex: 1;
                min-width: auto;
            }

            .small-box .inner h3 {
                font-size: 1.2rem;
            }

            .small-box .inner p {
                font-size: 0.75rem;
            }

            .small-box .icon {
                font-size: 50px;
            }
        }

        @media (max-width: 768px) {
            .card-tools {
                width: 100%;
            }
            
            .card-tools .d-flex {
                width: 100%;
            }
            
            .input-group {
                max-width: 100%;
            }
        }

        @media (max-width: 992px) {
            .btn-group-mobile .btn {
                font-size: 0.8rem;
                padding: 0.25rem 0.5rem;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable con opciones mejoradas
            const table = $('#productsTable').DataTable({
                responsive: true,
                "language": {
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Productos",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Productos",
                    "infoFiltered": "(Filtrado de _MAX_ total Productos)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ Productos",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscador:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                dom: 'Bfrtip',  // Añadir botones de exportación
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });

            // Conectar el campo de búsqueda personalizado con la búsqueda del DataTable
            $('#productSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Filtros de producto por estado de stock para desktop
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');

                const filter = $(this).data('filter');
                
                // Limpiar filtros previos
                table.search('').columns().search('').draw();
                
                if (filter === 'low') {
                    // Filtrar productos con stock bajo
                    table.column(3).search('Bajo').draw();
                } else if (filter === 'normal') {
                    // Filtrar productos con stock normal
                    table.column(3).search('Normal|Óptimo', true).draw();
                } else {
                    // Mostrar todos
                    table.draw();
                }
            });

            // Búsqueda para vista móvil
            $('#mobileSearch').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                
                $('.product-card').each(function() {
                    const productName = $(this).find('.product-name').text().toLowerCase();
                    const productCode = $(this).find('.product-code').text().toLowerCase();
                    const productCategory = $(this).find('.product-category').text().toLowerCase();
                    
                    if (productName.includes(searchTerm) || 
                        productCode.includes(searchTerm) || 
                        productCategory.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Los filtros de la vista móvil ahora usan los mismos botones del header
            // Actualizar filtros para que también funcionen en móvil
            $('.filter-btn').click(function() {
                const filter = $(this).data('filter');
                
                // Filtrar tarjetas móviles también
                $('.product-card').each(function() {
                    const stockStatus = $(this).data('stock-status');
                    
                    if (filter === 'all') {
                        $(this).show();
                    } else if (filter === 'low') {
                        if (stockStatus === 'Bajo') {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    } else if (filter === 'normal') {
                        if (stockStatus === 'Normal' || stockStatus === 'Óptimo') {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    }
                });
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
                        Swal.fire('Error', 'No se pudo cargar la información del producto',
                            'error');
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
                                Swal.fire('Error', response.message ||
                                    'No se pudo eliminar el producto', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
