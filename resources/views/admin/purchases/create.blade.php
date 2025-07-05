@extends('adminlte::page')

@section('title', 'Nueva Compra')

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
        <h1 class="text-dark font-weight-bold mb-2 mb-md-0">Nueva Compra</h1>
        <button onclick="window.history.back()" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver
        </button>
    </div>
@stop

@section('content')
    <!-- Mostrar mensajes de error -->
    @if(session('message'))
        <div class="alert alert-{{ session('icons') == 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>¡Errores encontrados!</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Información de la Compra
                    </h3>
                </div>

                <form action="{{ route('admin.purchases.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">

                            <!-- Código de Producto -->
                            <div class="col-12 col-md-6 mb-3">
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
                                            placeholder="Escanee o ingrese el código" value="{{ old('product_code') }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" id="addProduct" data-toggle="modal"
                                                data-target="#searchProductModal">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <a href="/products/create" class="btn btn-success">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </div>
                                        @error('product_code')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fecha de compra -->
                            <div class="col-12 col-md-6 mb-3">
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
                        <!-- Tabla de productos agregados -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title">
                                            <i class="fas fa-shopping-cart mr-2"></i>
                                            Productos en la Compra
                                        </h3>
                                    </div>
                                    <div class="card-body table-responsive p-0">
                                        <table class="table table-hover ">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Producto</th>
                                                    <th>Imagen</th>
                                                    <th>stock</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio Unitario</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody id="purchaseItems">
                                                <!-- Los items se agregarán dinámicamente aquí -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="6" class="text-right"><strong>Total:</strong>
                                                    </td>
                                                    <td colspan="2">
                                                            {{$currency->symbol}}<span id="totalAmount">0.00</span>
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
                        <div class="btn-group-purchases w-100">
                            <button type="button" class="btn btn-danger" id="cancelPurchase">
                                <i class="fas fa-times-circle mr-1 d-lg-inline d-none"></i>
                                <i class="fas fa-times-circle d-lg-none"></i>
                                <span class="d-lg-inline d-none">Cancelar Compra</span>
                                <span class="d-lg-none d-md-inline d-none">Cancelar</span>
                                <span class="d-md-none d-sm-inline d-none">Cancel</span>
                                <span class="d-sm-none">X</span>
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitPurchase">
                                <i class="fas fa-save mr-1 d-lg-inline d-none"></i>
                                <i class="fas fa-save d-lg-none"></i>
                                <span class="d-lg-inline d-none">Registrar Compra</span>
                                <span class="d-lg-none d-md-inline d-none">Registrar</span>
                                <span class="d-md-none d-sm-inline d-none">Reg</span>
                                <span class="d-sm-none">✓</span>
                            </button>
                            <button type="submit" class="btn btn-success" name="action" value="save_and_new">
                                <i class="fas fa-plus-circle mr-1 d-lg-inline d-none"></i>
                                <i class="fas fa-plus-circle d-lg-none"></i>
                                <span class="d-lg-inline d-none">Registrar y Nueva Compra</span>
                                <span class="d-lg-none d-md-inline d-none">Reg y Nuevo</span>
                                <span class="d-md-none d-sm-inline d-none">Reg+</span>
                                <span class="d-sm-none">✓+</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Búsqueda de Productos -->
    <div class="modal fade" id="searchProductModal" tabindex="-1" role="dialog" aria-labelledby="searchProductModalLabel"
        aria-hidden="true">
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
                                    <th style="min-width: 120px">Precio Compra </th>
                                    <th style="min-width: 100px">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="align-middle">{{ $product->code }}</td>
                                        <td class="align-middle">
                                            <button type="button" class="btn btn-primary btn-sm select-product"
                                                data-code="{{ $product->code }}" 
                                                data-id="{{ $product->id }}" 
                                                data-dismiss="modal">
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .modal-xl {
            max-width: 95% !important;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        #productsTable {
            margin: 0 !important;
        }

        #productsTable th {
            white-space: nowrap;
            padding: 12px 8px;
        }

        .modal-xl {
            max-width: 90%;
        }

        @media (max-width: 768px) {
            .modal-xl {
                max-width: 95%;
                margin: 0.5rem;
            }
        }

        .table-responsive {
            max-height: calc(100vh - 200px);
        }

        #productsTable {
            width: 100% !important;
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

        @media (max-width: 576px) {
            .d-flex.align-items-center {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .img-thumbnail {
                margin-bottom: 0.5rem;
            }
        }

        .required::after {
            content: " *";
            color: red;
        }

        .card {
            box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
        }

        /* Estilos para el grupo de botones de compras */
        .btn-group-purchases {
            display: flex;
            gap: 0.25rem;
            align-items: center;
            justify-content: space-between;
        }

        .btn-group-purchases .btn {
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Separar el botón cancelar de los otros dos */
        .btn-group-purchases .btn:first-child {
            margin-right: auto;
        }

        .btn-group-purchases .btn:not(:first-child) {
            margin-left: 0.25rem;
        }

        /* Distribución responsive de botones - SIEMPRE EN LÍNEA */
        @media screen and (max-width: 768px) {
            .btn-group-purchases {
                gap: 0.25rem;
            }
            
            .btn-group-purchases .btn {
                font-size: 0.8rem;
                padding: 0.75rem 0.75rem;
            }
        }

        @media screen and (max-width: 576px) {
            .btn-group-purchases .btn {
                font-size: 0.75rem;
                padding: 0.6rem 0.6rem;
                min-width: 70px;
            }
        }

        @media screen and (max-width: 480px) {
            .btn-group-purchases .btn {
                font-size: 0.7rem;
                padding: 0.5rem 0.5rem;
                min-width: 60px;
            }
        }

        /* Estilos para pantallas grandes - botones menos anchos */
        @media screen and (min-width: 992px) {
            .btn-group-purchases {
                justify-content: flex-end;
            }
            
            .btn-group-purchases .btn {
                flex: none;
                padding: 0.5rem 1.5rem;
            }
            
            .btn-group-purchases .btn:not(:first-child) {
                min-width: 160px;
                max-width: 200px;
            }
            
            .btn-group-purchases .btn:first-child {
                min-width: 140px;
                max-width: 180px;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable primero
            $('#productsTable').DataTable({
                responsive: true,
                scrollX: true,
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
            
            // Verificar si solo hay un producto disponible y agregarlo automáticamente
            // Hacerlo después de que DataTable esté inicializado
            setTimeout(function() {
                checkAndAddSingleProduct();
            }, 100);
            
            // Función para verificar si solo hay un producto disponible
            function checkAndAddSingleProduct() {
                // Contar productos disponibles desde la tabla del modal
                const availableProducts = $('#productsTable tbody tr').length;
                console.log('Productos disponibles:', availableProducts);
                
                if (availableProducts === 1) {
                    // Solo hay un producto, obtener sus datos directamente de la fila
                    const productRow = $('#productsTable tbody tr:first');
                    const productCode = productRow.find('td:eq(0)').text().trim();
                    const productId = productRow.find('button.select-product').data('id');
                    const productName = productRow.find('td:eq(2) span').text().trim();
                    const productImage = productRow.find('td:eq(2) img').attr('src');
                    const productStock = productRow.find('td:eq(4) .badge').text().trim();
                    const productPriceText = productRow.find('td:eq(5)').text().trim();
                    const productPrice = parseFloat(productPriceText.replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
                    
                                    console.log('Datos extraídos:', {
                    id: productId,
                    code: productCode,
                    name: productName,
                    image: productImage,
                    stock: productStock,
                    priceText: productPriceText,
                    price: productPrice
                });
                
                // Verificar que tenemos un ID válido
                if (!productId || productId === '' || productId === null) {
                    console.error('ID de producto no válido:', productId);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo obtener el ID del producto'
                    });
                    return;
                }
                
                // Crear objeto producto con los datos disponibles
                const product = {
                    id: productId,
                    code: productCode,
                    name: productName,
                    image: productImage,
                    stock: productStock,
                    purchase_price: productPrice
                };
                    
                    console.log('Producto extraído:', product);
                    
                    setTimeout(function() {
                        // Agregar el producto a la tabla silenciosamente (sin alerta porque es automático)
                        addProductToTable(product, false);
                        
                        // Enfocar el campo de fecha para continuar con la compra
                        $('#purchase_date').focus();
                    }, 500); // Pequeño delay para asegurar que la página esté completamente cargada
                } else if (availableProducts === 0) {
                    // No hay productos en inventario
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin productos disponibles',
                        text: 'No hay productos disponibles en el inventario para realizar compras',
                        confirmButtonText: 'Entendido'
                    });
                }
                // Si hay más de un producto, no hacer nada (comportamiento normal)
            }

            // Manejar la adición de productos
            $('#addProduct').click(function() {
                const productCode = $('#product_code').val();
                if (productCode) {
                    // Aquí irá la lógica para buscar el producto por código
                    // y agregarlo a la tabla
                    $.get(`/admin/purchases/product-by-code/${productCode}`, function(response) {
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
                                    console.log('Producto encontrado por código:', response.product);
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
                const productId = $(this).data('id');
                console.log('Código del producto:', productCode, 'ID:', productId); // Debug

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
                        console.log('Respuesta del servidor:', response); // Debug
                        if (response.success) {
                            // Asegurar que el producto tenga el ID correcto
                            response.product.id = productId;
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
            // showAlert = true: muestra alerta cuando el usuario agrega manualmente
            // showAlert = false: no muestra alerta cuando se agrega automáticamente
            function addProductToTable(product, showAlert = true) {
                console.log('Agregando producto a la tabla:', product);
                
                // Verificar que el producto tenga un ID válido
                if (!product.id || product.id === '' || product.id === null) {
                    console.error('Producto sin ID válido:', product);
                    if (showAlert) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'El producto no tiene un ID válido'
                        });
                    }
                    return;
                }
                
                // Verificar si el producto ya está en la tabla
                if ($(`tr[data-product-code="${product.code}"]`).length > 0) {
                    if (showAlert) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Producto ya agregado',
                            text: 'Este producto ya está en la lista de compra'
                        });
                    }
                    return;
                }

                // Asegurar que tenemos una imagen válida
                let imageUrl = product.image;
                if (!imageUrl || imageUrl === '') {
                    imageUrl = '/img/no-image.png';
                } else if (!imageUrl.startsWith('http') && !imageUrl.startsWith('/')) {
                    imageUrl = '/' + imageUrl;
                }
                
                console.log('Imagen procesada:', imageUrl);

                // Asegurar que tenemos un precio válido
                const price = product.purchase_price || product.price || 0;

                const row = `
                    <tr data-product-code="${product.code}" data-product-id="${product.id}">
                        <td>${product.code}</td>
                        <td>${product.name}</td>
                        <td>
                            <img src="${imageUrl}" alt="${product.name}" class="img-thumbnail" style="max-height: 50px;">
                        </td>
                        <td>${product.stock}</td>
                        
                        <td style="width: 100px">
                            <input type="number" 
                                class="form-control form-control-sm quantity-input" 
                                name="items[${product.id}][quantity]" 
                                value="1" 
                                min="1">
                        </td>
                        <td style="width: 150px">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{$currency->symbol}}</span>
                                </div>
                                <input type="number" 
                                    class="form-control price-input" 
                                    name="items[${product.id}][price]" 
                                    value="${price}" 
                                    step="0.01">
                            </div>
                        </td>
                        <td class="text-right">
                            {{$currency->symbol}} <span class="subtotal">${price}</span>
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

                // Mostrar notificación solo si showAlert es true
                if (showAlert) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Producto agregado!',
                        text: `${product.name} se agregó a la lista de compra`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        background: '#e8f5e8',
                        color: '#2e7d32'
                    });
                }
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
                $('.subtotal').each(function() {
                    total += parseFloat($(this).text()) || 0;
                });
                $('#totalAmount').text(total.toFixed(2));
                $('#totalAmountInput').val(total.toFixed(2));
                console.log('Total actualizado:', total); // Debug
            }

            // Manejar el botón de cancelar compra
            $('#cancelPurchase').click(function() {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "Se perderán todos los datos ingresados en esta compra",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cancelar compra',
                    cancelButtonText: 'No, continuar editando'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Retroceder a la página anterior
                        window.history.back();
                    }
                });
            });

            // Manejar el envío del formulario
            $('form').on('submit', function(e) {
                e.preventDefault();
                
                // Verificar que hay productos en la tabla
                if ($('#purchaseItems tr').length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin productos',
                        text: 'Debe agregar al menos un producto a la compra'
                    });
                    return;
                }

                // Verificar que la fecha esté completa
                if (!$('#purchase_date').val()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fecha requerida',
                        text: 'Debe seleccionar una fecha de compra'
                    });
                    return;
                }

                // Deshabilitar botones para evitar doble envío
                $('#submitPurchase, button[name="action"]').prop('disabled', true);

                // Enviar el formulario normalmente
                this.submit();
            });
        });
    </script>
@stop
