@extends('adminlte::page')

@section('title', 'Nueva Venta')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Nueva Venta</h1>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver al listado
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Información de la Venta
                    </h3>
                </div>

                <form action="{{ route('admin.sales.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Código de Producto -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="product_code" class="required">Código de Producto</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-barcode"></i>
                                            </span>
                                        </div>
                                        <input type="text" name="product_code" id="product_code"
                                            class="form-control @error('product_code') is-invalid @enderror"
                                            placeholder="Escanee o ingrese el código">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" id="searchProduct"
                                                data-toggle="modal" data-target="#searchProductModal">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <a href="/products/create" class="btn btn-success">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cliente -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer_id" class="required">Cliente</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                        </div>
                                        <select name="customer_id" id="customer_id"
                                            class="form-control select2 @error('customer_id') is-invalid @enderror"
                                            style="width: calc(100% - 90px);" required>
                                            <option value="">Seleccione un cliente</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}">
                                                    {{ $customer->name }} - {{ $customer->nit_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append" style="white-space: nowrap;">
                                            <a href="{{ route('admin.customers.create') }}" class="btn btn-success">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Fecha de venta -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sale_date" class="required">Fecha de Venta</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar"></i>
                                            </span>
                                        </div>
                                        <input type="date" name="sale_date" id="sale_date"
                                            class="form-control @error('sale_date') is-invalid @enderror"
                                            value="{{ old('sale_date', date('Y-m-d')) }}" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Tipo de pago -->
                            {{-- <div class="col-md-3">
                                <div class="form-group">
                                    <label for="payment_type" class="required">Tipo de Pago</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-money-bill"></i>
                                            </span>
                                        </div>
                                        <select name="payment_type" id="payment_type"
                                            class="form-control @error('payment_type') is-invalid @enderror" required>
                                            <option value="cash">Efectivo</option>
                                            <option value="card">Tarjeta</option>
                                            <option value="transfer">Transferencia</option>
                                        </select>
                                    </div>
                                </div>
                            </div> --}}
                        </div>

                        <!-- Tabla de productos -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title">
                                            <i class="fas fa-shopping-cart mr-2"></i>
                                            Productos en la Venta
                                        </h3>
                                    </div>
                                    <div class="card-body table-responsive p-0">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Producto</th>
                                                    <th>Stock</th>
                                                    <th width="120px">Cantidad</th>
                                                    <th>Precio Unit.</th>
                                                    <th>Subtotal</th>
                                                    <th width="50px">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="saleItems">
                                                <!-- Los items se agregarán dinámicamente aquí -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-right">
                                                        <strong>Total:</strong>
                                                    </td>
                                                    <td colspan="2">
                                                        <span id="totalAmount">{{ $currency->symbol }} 0.00</span>
                                                        <input type="hidden" name="total_price" id="totalAmountInput"
                                                            value="0">
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-danger" id="cancelSale">
                                <i class="fas fa-times-circle mr-2"></i>
                                Cancelar Venta
                            </button>
                            <button type="submit" class="btn btn-primary" id="processSale">
                                <i class="fas fa-save mr-2"></i>
                                Procesar Venta
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Búsqueda de Productos -->
    <div class="modal fade" id="searchProductModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-search mr-2"></i>
                        Búsqueda de Productos
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>

                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="productsTable" class="table table-striped table-hover w-100 nowrap">
                            <thead>
                                <tr>
                                    <th style="min-width: 100px">Código</th>
                                    <th style="min-width: 40px">Acción</th>
                                    <th style="width: 40px">Imagen</th>
                                    <th style="min-width: 250px">Nombre</th>
                                    <th style="min-width: 150px">Categoría</th>
                                    <th style="min-width: 100px">Stock</th>
                                    <th style="min-width: 120px">Precio</th>
                                    <th style="min-width: 100px">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="align-middle">{{ $product->code }}</td>
                                        <td class="align-middle text-center">
                                            <button type="button" class="btn btn-primary btn-sm select-product"
                                                data-code="{{ $product->code }}"
                                                {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-plus-circle"></i>
                                            </button>
                                        </td>
                                        <td class="align-middle">
                                            <img src="{{ asset($product->image) }}" alt="N/I" class="img-thumbnail"
                                                width="50">
                                        </td>
                                        <td class="align-middle">{{ $product->name }}</td>
                                        <td class="align-middle">{{ $product->category->name }}</td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-{{ $product->stock_status_class }}">
                                                {{ $product->stock }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-right">{{ $currency->symbol }}
                                            {{ number_format($product->sale_price, 2) }}
                                        </td>
                                        <td class="align-middle text-center">
                                            <span
                                                class="badge badge-{{ $product->stock_status_label === 'Bajo' ? 'danger' : ($product->stock_status_label === 'Normal' ? 'warning' : 'success') }}">
                                                {{ $product->stock_status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        .required::after {
            content: " *";
            color: red;
        }

        /* Estilos para hacer la tabla responsive */
        .modal-xl {
            max-width: 95% !important;
        }

        .table-responsive {
            margin: 0;
            padding: 0;
            width: 100%;
        }

        #productsTable {
            width: 100% !important;
        }

        .dataTables_wrapper {
            width: 100%;
        }

        /* Ajustes para pantallas pequeñas */
        @media screen and (max-width: 768px) {
            .modal-body {
                padding: 0.5rem;
            }

            #productsTable td,
            #productsTable th {
                white-space: nowrap;
            }
        }

        /* Asegurar que los botones no se envuelvan */
        .input-group-append {
            display: flex;
            flex-wrap: nowrap;
        }

        /* Ajustar el ancho del select2 para dejar espacio a los botones */
        .select2-container {
            flex: 1 1 auto;
            width: auto !important;
            max-width: calc(100% - 90px) !important;
        }

        /* Mantener los botones juntos */
        .input-group-append .btn {
            margin-left: -1px;
            flex-shrink: 0;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccione un cliente'
            });

            // Inicializar DataTable
            $('#productsTable').DataTable({
                responsive: true,
                autoWidth: false,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                columnDefs: [{
                        responsivePriority: 1,
                        targets: [0, 1, 3] // Código, Acción y Nombre siempre visibles
                    },
                    {
                        responsivePriority: 2,
                        targets: [5, 6] // Stock y Precio siguiente prioridad
                    },
                    {
                        responsivePriority: 3,
                        targets: '_all' // El resto menos prioritario
                    }
                ]
            });

            // Función para agregar producto a la tabla
            function addProductToTable(product) {
                // Verificar si el producto ya está en la tabla
                if ($(`tr[data-product-code="${product.code}"]`).length > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Producto ya agregado',
                        text: 'Este producto ya está en la lista de venta'
                    });
                    return;
                }

                // Verificar stock
                if (product.stock <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sin stock',
                        text: 'Este producto no tiene stock disponible'
                    });
                    return;
                }

                const row = `
                    <tr data-product-code="${product.code}">
                        <td>${product.code}</td>
                        <td>${product.name}</td>
                        <td class="text-center">
                            <span class="badge badge-${product.stock_status_class}">${product.stock}</span>
                        </td>
                        <td>
                            <input type="number" 
                                class="form-control form-control-sm quantity-input" 
                                name="items[${product.id}][quantity]" 
                                value="1" 
                                min="1" 
                                max="${product.stock}">
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" 
                                    class="form-control form-control-sm price-input" 
                                    name="items[${product.id}][price]" 
                                    value="${product.sale_price}" 
                                    step="0.01" 
                                    readonly>
                            </div>
                        </td>
                        <td class="text-right">
                            <span class="subtotal-value" style="display:none;">${product.sale_price}</span>
                            <span class="subtotal-display">{{ $currency->symbol }}${product.sale_price}</span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#saleItems').append(row);
                updateTotal();

                // Notificación de éxito
                Swal.fire({
                    icon: 'success',
                    title: 'Producto agregado',
                    text: 'El producto se agregó a la lista de venta',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }

            // Buscar producto por código
            $('#product_code').on('keypress', function(e) {
                if (e.which == 13) { // Enter key
                    e.preventDefault();
                    const code = $(this).val();
                    if (code) {
                        $.ajax({
                            url: `/sales/product-by-code/${code}`,
                            method: 'GET',
                            success: function(response) {
                                if (response.success) {
                                    addProductToTable(response.product);
                                    $('#product_code').val('').focus();
                                } else {
                                    Swal.fire('Error', 'Producto no encontrado', 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Error al buscar el producto', 'error');
                            }
                        });
                    }
                }
            });

            // Seleccionar producto desde el modal
            $('.select-product').click(function() {
                const code = $(this).data('code');
                $.ajax({
                    url: `/sales/product-details/${code}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            addProductToTable(response.product);
                            $('#searchProductModal').modal('hide');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error al obtener detalles del producto', 'error');
                    }
                });
            });

            // Actualizar subtotal cuando cambie cantidad
            $(document).on('input', '.quantity-input', function() {
                const row = $(this).closest('tr');
                const quantity = parseFloat($(this).val()) || 0;
                const price = parseFloat(row.find('.price-input').val()) || 0;
                const stock = parseInt($(this).attr('max'));

                // Validar que la cantidad no exceda el stock
                if (quantity > stock) {
                    $(this).val(stock);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock insuficiente',
                        text: `Solo hay ${stock} unidades disponibles`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }

                const subtotal = quantity * price;
                row.find('.subtotal-value').text(subtotal.toFixed(2));
                row.find('.subtotal-display').text('{{ $currency->symbol }} ' + subtotal.toFixed(2));
                updateTotal();
            });

            // Eliminar producto de la tabla
            $(document).on('click', '.remove-item', function() {
                const row = $(this).closest('tr');
                Swal.fire({
                    title: '¿Eliminar producto?',
                    text: "¿Está seguro de eliminar este producto de la venta?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        row.remove();
                        updateTotal();
                    }
                });
            });

            // Actualizar total general
            function updateTotal() {
                let total = 0;
                $('.subtotal-value').each(function() {
                    total += parseFloat($(this).text()) || 0;
                });
                $('#totalAmount').text('{{ $currency->symbol }} ' + total.toFixed(2));
                $('#totalAmountInput').val(total.toFixed(2));
            }


            // Limpiar formulario
            $('#clearForm').click(function() {
                Swal.fire({
                    title: '¿Limpiar formulario?',
                    text: "Se eliminarán todos los productos agregados",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, limpiar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#saleItems').empty();
                        $('#customer_id').val('').trigger('change');
                        $('#sale_date').val('{{ date('Y-m-d') }}');
                        $('#payment_type').val('cash');
                        updateTotal();
                    }
                });
            });
        });
    </script>
@stop
