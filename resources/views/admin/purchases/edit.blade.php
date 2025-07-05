@extends('adminlte::page')

@section('title', 'Editar Compra')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Editar Compra #{{ $purchase->id }}</h1>
        <button onclick="window.history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </button>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Información de la Compra
                    </h3>
                </div>

                <form action="{{ route('admin.purchases.update', $purchase->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
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
                                            placeholder="Ingrese el código del producto">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" id="addProduct" data-toggle="modal"
                                                data-target="#searchProductModal">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        @error('product_code')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fecha de compra -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="purchase_date" class="required">Fecha de Compra</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar"></i>
                                            </span>
                                        </div>
                                        <input type="date" name="purchase_date" id="purchase_date"
                                            class="form-control @error('purchase_date') is-invalid @enderror"
                                            value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                                        @error('purchase_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de productos -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title">
                                            <i class="fas fa-shopping-cart mr-2"></i>
                                            Productos en la Compra
                                        </h3>
                                    </div>
                                    <div class="card-body table-responsive p-1">
                                        <table class="table table-hover text-nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Producto</th>
                                                    <th>Imagen</th>
                                                    <th>stock</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio Unitario</th>
                                                    <th>Subtotal</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="purchaseItems">
                                                @foreach ($purchase->details as $detail)
                                                    <tr data-product-code="{{ $detail->product->code }}">
                                                        <td>{{ $detail->product->code }}</td>
                                                        <td>{{ $detail->product->name }}</td>
                                                        <td>
                                                            <img src="{{ $detail->product->image ? asset($detail->product->image) : asset('img/no-image.png') }}"
                                                                alt="{{ $detail->product->name }}" class="img-thumbnail"
                                                                style="max-height: 50px;">
                                                        </td>
                                                        <td class="text-center">
                                                            <span
                                                                class="badge badge-{{ $detail->product->stock > 10 ? 'success' : ($detail->product->stock > 0 ? 'warning' : 'danger') }}">
                                                                {{ $detail->product->stock }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                class="form-control form-control-sm quantity-input"
                                                                name="items[{{ $detail->product->id }}][quantity]"
                                                                value="{{ $detail->quantity }}" min="1"
                                                                max="{{ $detail->product->stock + $detail->quantity }}">
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-sm">
                                                                <div class="input-group-prepend">
                                                                    <span
                                                                        class="input-group-text">{{ $currency->symbol }}</span>
                                                                </div>
                                                                <input type="number"
                                                                    class="form-control form-control-sm price-input"
                                                                    name="items[{{ $detail->product->id }}][price]"
                                                                    value="{{ $detail->product->purchase_price }}"
                                                                    step="0.01">
                                                            </div>
                                                        </td>
                                                        <td class="text-right">
                                                            <span class="subtotal-value"
                                                                style="display:none;">{{ $detail->quantity * $detail->product->purchase_price }}</span>
                                                            <span class="subtotal-display">{{ $currency->symbol }}
                                                                {{ number_format($detail->quantity * $detail->product->purchase_price, 2) }}</span>
                                                        </td>
                                                        <td>
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm remove-item">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="6" class="text-right">
                                                        <strong>Total:</strong>
                                                    </td>
                                                    <td>
                                                        {{ $currency->symbol }}<span id="totalAmount">0.00</span>
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
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" id="updatePurchase">
                                <i class="fas fa-save mr-2"></i>
                                Actualizar Compra
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Búsqueda de Productos -->
    <div class="modal fade" id="searchProductModal" tabindex="-1" role="dialog"
        aria-labelledby="searchProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="searchProductModalLabel">
                        <i class="fas fa-search mr-2"></i>
                        Búsqueda de Productos
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table id="productsTable" class="table table-striped table-hover w-100">
                            <thead>
                                <tr>
                                    <th style="min-width: 120px">Código</th>
                                    <th style="min-width: 80px">Acción</th>
                                    <th style="min-width: 300px">Nombre</th>
                                    <th style="min-width: 150px">Categoría</th>
                                    <th style="min-width: 100px">Stock</th>
                                    <th style="min-width: 120px">Precio Compra</th>
                                    <th style="min-width: 100px">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="align-middle">{{ $product->code }}</td>
                                        <td class="align-middle text-center">
                                            <button type="button" class="btn btn-primary btn-sm select-product"
                                                data-code="{{ $product->code }}" data-dismiss="modal">
                                                <i class="fas fa-plus-circle"></i>
                                            </button>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $product->image ? asset($product->image) : asset('img/no-image.png') }}"
                                                    alt="{{ $product->name }}" class="img-thumbnail mr-2"
                                                    style="width: 40px; height: 40px; object-fit: cover;">
                                                <span>{{ $product->name }}</span>
                                            </div>
                                        </td>
                                        <td class="align-middle">{{ $product->category->name }}</td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-{{ $product->stock_status_class }}">
                                                {{ $product->stock }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-right">
                                            {{$currency->symbol}} {{ number_format($product->purchase_price, 2) }}
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <style>
        .modal-xl {
            max-width: 95% !important;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            min-height: 300px;
            max-height: calc(100vh - 200px);
        }

        #productsTable {
            width: 100% !important;
            margin: 0 !important;
        }

        #productsTable th {
            white-space: nowrap;
            padding: 12px 8px;
            background-color: #f4f6f9;
        }

        #productsTable td {
            white-space: normal;
            vertical-align: middle;
        }

        .select-product {
            padding: 0.25rem 0.5rem;
        }

        .img-thumbnail {
            max-width: 40px;
            height: auto;
        }

        @media (max-width: 768px) {
            .modal-xl {
                max-width: 95%;
                margin: 0.5rem;
            }

            #productsTable thead th {
                padding: 8px 4px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .d-flex.align-items-center {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .img-thumbnail {
                margin-bottom: 0.5rem;
            }
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
            // Inicializar los subtotales y el total al cargar la página
            $('#purchaseItems tr').each(function() {
                const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
                const price = parseFloat($(this).find('.price-input').val()) || 0;
                const subtotal = quantity * price;
                $(this).find('.subtotal').text(subtotal.toFixed(2));
            });
            updateTotal();

            // Cargar los detalles existentes de la compra
            const purchaseDetails = @json($purchaseDetails);

            // purchaseDetails.forEach(product => {
            //     addProductToTable(product, true);
            // });

            // Inicializar DataTable para el modal de búsqueda
            $('#productsTable').DataTable({
                responsive: true,
                scrollX: true,
                autoWidth: false,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                columnDefs: [{
                        responsivePriority: 1,
                        targets: [0, 1, 2]
                    }, // Código, Acción y Nombre siempre visibles
                    {
                        responsivePriority: 2,
                        targets: [4, 5]
                    }, // Stock y Precio siguiente prioridad
                    {
                        responsivePriority: 3,
                        targets: '_all'
                    } // El resto menos prioritario
                ]
            });

            // Manejar la adición de productos
            $('#addProduct').click(function() {
                const productCode = $('#product_code').val();
                if (productCode) {
                    // Aquí irá la lógica para buscar el producto por código
                    // y agregarlo a la tabla
                    $.get(`/purchases/product-by-code/${productCode}`, function(response) {
                        if (response.success) {
                            addProductToTable(response.product);
                            $('#product_code').val('');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    });
                }
            });

            // Manejar la entrada de código de producto
            $('#product_code').keypress(function(e) {
                if (e.which == 13) { // Si presiona Enter
                    e.preventDefault();
                    const productCode = $(this).val();

                    if (productCode) {
                        // Verificar si el producto ya está en la tabla
                        if ($(`tr[data-product-code="${productCode}"]`).length > 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Producto ya agregado',
                                text: 'Este producto ya está en la lista de compra'
                            });
                            return;
                        }

                        // Buscar el producto por código
                        $.ajax({
                            url: `/purchases/product-by-code/${productCode}`,
                            method: 'GET',
                            success: function(response) {
                                if (response.success) {
                                    addProductToTable(response.product);
                                    $('#product_code').val(''); // Limpiar el input
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'No se encontró el producto', 'error');
                            }
                        });
                    }
                }
            });

            // Evento para el botón de seleccionar producto
            $(document).on('click', '.select-product', function() {
                const productCode = $(this).data('code');
                console.log('Código del producto:', productCode); // Debug

                // Verificar si el producto ya está en la tabla
                if ($(`tr[data-product-code="${productCode}"]`).length > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Producto ya agregado',
                        text: 'Este producto ya está en la lista de compra'
                    });
                    return;
                }

                // Obtener detalles del producto y agregarlo a la tabla
                $.ajax({
                    url: `/purchases/product-details/${productCode}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            addProductToTable(response.product);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la petición:', error); // Debug
                        Swal.fire('Error', 'No se pudo obtener la información del producto',
                            'error');
                    }
                });
            });

            // Función para agregar producto a la tabla
            function addProductToTable(product) {

                const row = `
                    <tr data-product-code="${product.code}">
                        <td>${product.code}</td>
                        <td>${product.name}</td>
                        <td>
                            <img src="/${product.image ? product.image : '/img/no-image.png'}"
                                alt="${product.name}"
                                class="img-thumbnail"
                                style="max-height: 50px;">
                        </td>
                        <td class="text-center">
                            <span class="badge badge-${product.stock > 10 ? 'success' : (product.stock > 0 ? 'warning' : 'danger')}">
                                ${product.stock}
                            </span>
                        </td>
                        
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" 
                                       class="form-control quantity-input" 
                                       name="items[${product.id}][quantity]" 
                                       value="1" 
                                       min="1">
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" 
                                       class="form-control price-input" 
                                       name="items[${product.id}][price]" 
                                       value="${product.purchase_price || product.price}" 
                                       step="0.01">
                            </div>
                        </td>
                        <td class="text-right">
                            $<span class="subtotal">${product.purchase_price || product.price}</span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#purchaseItems').append(row);
                updateTotal();

                // Notificación de éxito
                Swal.fire({
                    icon: 'success',
                    title: 'Producto agregado',
                    text: 'El producto se agregó a la lista de compra',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }

            // Actualizar subtotal cuando cambie cantidad o precio
            $(document).on('input', '.quantity-input, .price-input', function() {
                const row = $(this).closest('tr');
                const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
                const price = parseFloat(row.find('.price-input').val()) || 0;
                const subtotal = quantity * price;
                row.find('.subtotal').text(subtotal.toFixed(2));
                updateTotal();
            });

            // Eliminar producto de la tabla
            $(document).on('click', '.remove-item', function() {
                const row = $(this).closest('tr');
                row.remove();
                updateTotal();
            });

            // Actualizar total general
            function updateTotal() {
                let total = 0;
                $('#purchaseItems tr').each(function() {
                    const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
                    const price = parseFloat($(this).find('.price-input').val()) || 0;
                    const subtotal = quantity * price;
                    $(this).find('.subtotal').text(subtotal.toFixed(2));
                    total += subtotal;
                });

                $('#totalAmount').text(total.toFixed(2));
                $('#totalAmountInput').val(total.toFixed(2));
            }

            // Cuando se hace clic en "Registrar Compra"
            $('#registrarCompra').click(function(e) {
                e.preventDefault();

                // Recolectar todos los items de la tabla
                let items = [];
                $('#purchaseTable tbody tr').each(function() {
                    items.push({
                        code: $(this).find('td:eq(0)').text(),
                        quantity: parseInt($(this).find('input.quantity').val()),
                        price: parseFloat($(this).find('input.price').val()),
                        subtotal: parseFloat($(this).find('td:eq(4)').text().replace('$',
                            ''))
                    });
                });

                // Crear el formulario
                let formData = new FormData();
                formData.append('purchase_date', $('#purchase_date').val());
                formData.append('total', $('#total').text());

                // Agregar los items
                items.forEach((item, index) => {
                    formData.append(`items[${index}][code]`, item.code);
                    formData.append(`items[${index}][quantity]`, item.quantity);
                    formData.append(`items[${index}][price]`, item.price);
                });

                // Enviar el formulario
                $.ajax({
                    url: '{{ route('admin.purchases.store') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'Compra registrada correctamente',
                            icon: 'success'
                        }).then((result) => {
                            window.location.href =
                                '{{ route('admin.purchases.index') }}';
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Hubo un error al registrar la compra', 'error');
                    }
                });
            });
        });
    </script>
@stop
