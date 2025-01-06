@extends('adminlte::page')

@section('title', 'Gestión de Productos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Gestión de Productos</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-2"></i>Nuevo Producto
        </a>
    </div>
@stop

@section('content')
    {{-- Widgets de Estadísticas --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalProducts }}</h3>
                    <p>Total Productos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $lowStockProducts }}</h3>
                    <p>Stock Bajo</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($totalValue, 2) }}</h3>
                    <p>Valor del Inventario</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $categories->count() }}</h3>
                    <p>Categorías</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Productos --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-boxes mr-2"></i>
                Lista de Productos
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="productsTable" class="table table-striped table-hover">
                <thead >
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Precio Venta</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->code }}</td>
                            <td>
                                <img src="{{ $product->image ? asset($product->image) : asset('img/no-image.png') }}"
                                    alt="{{ $product->name }}" class="img-thumbnail mr-2"
                                    style="width: 50px; height: 50px; object-fit: cover;">
                                {{ $product->name }}
                            </td>
                            <td>{{ $product->category->name }}</td>
                            <td>
                                <span class="badge badge-{{ $product->stock_status_class }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td>${{ number_format($product->sale_price, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $product->stock_status_label === 'Bajo' ? 'danger' : ($product->stock_status_label === 'Normal' ? 'warning' : 'success') }}">
                                    {{ $product->stock_status_label }}
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
    </div>
    @if(isset($product) && $product->image)
    {{-- Modal para mostrar producto --}}
    <div class="modal fade" id="showProductModal" tabindex="-1" role="dialog" aria-labelledby="showProductModalLabel" aria-hidden="true">
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
                            <img src="{{ asset( $product->image) }}" 
                            alt="Imagen del producto"
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
                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
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
                                    <h4 class="text-primary mb-0">$<span id="productPurchasePrice"></span></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Precio de Venta</h6>
                                </div>
                                <div class="card-body">
                                    <h4 class="text-success mb-0">$<span id="productSalePrice"></span></h4>
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
    <style>
        .small-box {
            transition: transform .3s;
        }

        .small-box:hover {
            transform: translateY(-5px);
        }
    </style>
    <style>
        .small-box {
            transition: transform .3s;
        }

        .small-box:hover {
            transform: translateY(-5px);
        }

        .modal-header .close {
            padding: 1rem;
            margin: -1rem -1rem -1rem auto;
        }

        .table th {
            background-color: #f8f9fa;
        }

        #productImage {
            transition: transform .3s;
        }

        #productImage:hover {
            transform: scale(1.05);
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#productsTable').DataTable({
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
                order: [
                    [1, 'asc']
                ], // Ordenar por nombre por defecto
            });

            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();

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
                        Swal.fire('Error', 'No se pudo cargar la información del producto', 'error');
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
                                Swal.fire('Error', response.message || 'No se pudo eliminar el producto', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
