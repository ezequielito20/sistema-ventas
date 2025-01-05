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
                <thead class="bg-primary text-white">
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
                                <span class="badge badge-{{ $product->stock_status_class }}">
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

    {{-- Modal de Detalles del Producto --}}
    <div class="modal fade" id="showProductModal" tabindex="-1" role="dialog" aria-labelledby="showProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="showProductModalLabel">
                        <i class="fas fa-box mr-2"></i>
                        Detalles del Producto
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center mb-3">
                                <img id="productImage" src="" alt="Imagen del producto"
                                    class="img-fluid rounded shadow-sm" style="max-height: 300px; object-fit: contain;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4 id="productName" class="font-weight-bold mb-3"></h4>
                            <div class="badge badge-primary mb-2">
                                <i class="fas fa-barcode mr-1"></i>
                                <span id="productCode"></span>
                            </div>

                            <div class="card card-outline card-primary mb-3">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">Descripción:</h6>
                                    <p id="productDescription" class="text-muted"></p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <tr>
                                        <th class="bg-light" width="40%">Categoría</th>
                                        <td id="productCategory"></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Stock Actual</th>
                                        <td>
                                            <span id="productStock" class="badge"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Límites de Stock</th>
                                        <td>
                                            <small class="text-danger">Mín: </small>
                                            <span id="productMinStock"></span>
                                            <small class="text-success ml-2">Máx: </small>
                                            <span id="productMaxStock"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Precio de Compra</th>
                                        <td id="productPurchasePrice" class="text-primary"></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Precio de Venta</th>
                                        <td id="productSalePrice" class="text-success"></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Margen de Beneficio</th>
                                        <td>
                                            <span id="productProfit" class="badge badge-info"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Fecha de Ingreso</th>
                                        <td>
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            <span id="productEntryDate"></span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <a href="#" id="editProductBtn" class="btn btn-warning">
                        <i class="fas fa-edit mr-1"></i>
                        Editar Producto
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
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
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
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
                    url: `/admin/products/${id}`,
                    type: 'GET',
                    success: function(response) {
                        const product = response.product;

                        // Actualizar contenido del modal
                        $('#productName').text(product.name);
                        $('#productCode').text(product.code);
                        $('#productDescription').text(product.description || 'Sin descripción');
                        $('#productCategory').text(product.category.name);

                        // Stock con clase dinámica
                        $('#productStock')
                            .text(product.stock)
                            .removeClass()
                            .addClass(`badge badge-${product.stock_status_class}`);

                        $('#productMinStock').text(product.min_stock);
                        $('#productMaxStock').text(product.max_stock);
                        $('#productPurchasePrice').text(product.formatted_purchase_price);
                        $('#productSalePrice').text(product.formatted_sale_price);
                        $('#productProfit').text(`${product.profit_margin.toFixed(2)}%`);
                        $('#productEntryDate').text(product.formatted_entry_date);

                        // Imagen con fallback
                        $('#productImage').attr('src', product.image || '/img/no-image.png');

                        // Actualizar enlace de edición
                        $('#editProductBtn').attr('href', `/admin/products/edit/${product.id}`);

                        $('#showProductModal').modal('show');
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo cargar la información del producto'
                        });
                    }
                });
            });

            // Eliminar producto
            $('.delete-product').click(function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/products/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: 'El producto ha sido eliminado correctamente.',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo eliminar el producto'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
