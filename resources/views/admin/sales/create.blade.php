@extends('adminlte::page')

@section('title', 'Nueva Venta')

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
        <h1 class="text-dark font-weight-bold mb-2 mb-md-0">Nueva Venta</h1>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-2 d-md-inline d-none"></i>
            <i class="fas fa-arrow-left d-md-none"></i>
            <span class="d-md-inline d-none">Volver al listado</span>
            <span class="d-md-none">Volver</span>
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
                            <div class="col-12 col-md-4 mb-3">
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
                            <div class="col-12 col-md-4 mb-3">
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
                                            style="width: 100%;" required>
                                            <option value="">Seleccione un cliente</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ isset($selectedCustomerId) && $selectedCustomerId == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }} - {{ $currency->symbol }} {{ number_format($customer->total_debt, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <a href="{{ route('admin.customers.create') }}" class="btn btn-success">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                    @error('customer_id')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Fecha de venta -->
                            <div class="col-12 col-md-4 mb-3">
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
                        <div class="btn-group-sales w-100">
                            <button type="button" class="btn btn-danger" id="cancelSale">
                                <i class="fas fa-times-circle mr-1 d-lg-inline d-none"></i>
                                <i class="fas fa-times-circle d-lg-none"></i>
                                <span class="d-lg-inline d-none">Cancelar Venta</span>
                                <span class="d-lg-none d-md-inline d-none">Cancelar</span>
                                <span class="d-md-none d-sm-inline d-none">Cancel</span>
                                <span class="d-sm-none">X</span>
                            </button>
                            <button type="submit" class="btn btn-primary" name="action" value="save">
                                <i class="fas fa-save mr-1 d-lg-inline d-none"></i>
                                <i class="fas fa-save d-lg-none"></i>
                                <span class="d-lg-inline d-none">Procesar Venta</span>
                                <span class="d-lg-none d-md-inline d-none">Procesar</span>
                                <span class="d-md-none d-sm-inline d-none">Proc</span>
                                <span class="d-sm-none">✓</span>
                            </button>
                            <button type="submit" class="btn btn-success" name="action" value="save_and_new">
                                <i class="fas fa-plus-circle mr-1 d-lg-inline d-none"></i>
                                <i class="fas fa-plus-circle d-lg-none"></i>
                                <span class="d-lg-inline d-none">Procesar y Nueva Venta</span>
                                <span class="d-lg-none d-md-inline d-none">Proc y Nuevo</span>
                                <span class="d-md-none d-sm-inline d-none">Proc+</span>
                                <span class="d-sm-none">✓+</span>
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
                                                data-id="{{ $product->id }}"
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
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Mejorar el estilo del Select2 */
        .select2-container--bootstrap4 .select2-selection--single {
            height: 38px !important;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
            padding-left: 0;
            padding-right: 0;
            color: #495057;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff;
        }

        .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .select2-container--bootstrap4 .select2-results__option {
            padding: 0.375rem 0.75rem;
        }

        .select2-container--bootstrap4 .select2-dropdown {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Ajustar el ancho del select2 para dejar espacio al botón */
        .input-group .select2-container {
            flex: 1 1 auto;
            width: auto !important;
        }

        /* Mantener los botones juntos */
        .input-group-append .btn {
            margin-left: -1px;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
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
            
            /* Hacer los botones más grandes en móviles */
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            /* Mejorar el espaciado en los formularios */
            .form-group {
                margin-bottom: 1rem;
            }
            
            /* Ajustar el tamaño de los inputs en móviles */
            .form-control {
                font-size: 16px; /* Previene zoom en iOS */
            }
        }

        /* Estilos adicionales para responsividad */
        @media screen and (max-width: 576px) {
            /* Hacer que los inputs del formulario sean más grandes */
            .input-group-text {
                min-width: 45px;
                justify-content: center;
            }
            
            /* Mejorar la tabla de productos en móviles */
            .table-responsive {
                border: none;
            }
            
            .table td, .table th {
                padding: 0.5rem 0.25rem;
                font-size: 0.875rem;
            }
            
            /* Botones de acción en la tabla más pequeños */
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
            
            /* Ajustar botones del footer para que se mantengan en línea */
            .card-footer .btn {
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
            }
        }

        /* Estilos para el grupo de botones de ventas */
        .btn-group-sales {
            display: flex;
            gap: 0.25rem;
            align-items: center;
            justify-content: space-between;
        }

        .btn-group-sales .btn {
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Separar el botón cancelar de los otros dos */
        .btn-group-sales .btn:first-child {
            margin-right: auto;
        }

        .btn-group-sales .btn:not(:first-child) {
            margin-left: 0.25rem;
        }

        /* Distribución responsive de botones - SIEMPRE EN LÍNEA */
        @media screen and (max-width: 768px) {
            .btn-group-sales {
                gap: 0.25rem;
            }
            
            .btn-group-sales .btn {
                font-size: 0.8rem;
                padding: 0.5rem 0.5rem;
            }
        }

        @media screen and (max-width: 576px) {
            .btn-group-sales .btn {
                font-size: 0.75rem;
                padding: 0.4rem 0.4rem;
            }
        }

        @media screen and (max-width: 480px) {
            .btn-group-sales .btn {
                font-size: 0.7rem;
                padding: 0.375rem 0.25rem;
            }
        }

        /* Mejoras para tablets */
        @media screen and (min-width: 769px) and (max-width: 1024px) {
            .col-md-4 {
                margin-bottom: 1rem;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 con opciones mejoradas
            $('#customer_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccione un cliente',
                allowClear: true,
                width: '100%',
                dropdownAutoWidth: true,
                dropdownParent: $('#customer_id').parent(),
                escapeMarkup: function(markup) {
                    return markup;
                },
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                },
                templateResult: formatCustomer,
                templateSelection: formatCustomerSelection
            });
            
            // Función para verificar si solo hay un producto disponible
            function checkAndAddSingleProduct() {
                // Contar productos disponibles desde la tabla del modal
                const availableProducts = $('#productsTable tbody tr').length;
                
                if (availableProducts === 1) {
                    // Solo hay un producto, obtener sus datos directamente de la fila
                    const productRow = $('#productsTable tbody tr:first');
                    const productCode = productRow.find('td:eq(0)').text().trim();
                    const productName = productRow.find('td:eq(3)').text().trim();
                    const productImage = productRow.find('td:eq(2) img').attr('src');
                    const productStock = productRow.find('td:eq(5) .badge').text().trim();
                    const productPriceText = productRow.find('td:eq(6)').text().trim();
                    const productPrice = parseFloat(productPriceText.replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
                    
                    // Obtener el ID real del producto desde el botón
                    const productId = productRow.find('button.select-product').data('id');
                    
                    console.log('ID extraído del botón:', productId, 'tipo:', typeof productId);
                    
                    // Crear objeto producto con los datos disponibles
                    const product = {
                        id: productId, // ID real del producto
                        code: productCode,
                        name: productName,
                        image: productImage,
                        stock: parseInt(productStock),
                        sale_price: productPrice
                    };
                    
                    console.log('Objeto producto creado:', product);
                    
                    setTimeout(function() {
                        // Agregar el producto a la tabla silenciosamente (sin alerta porque es automático)
                        addProductToTable(product, false);
                        
                        // No abrir automáticamente el select de clientes
                        // El usuario puede seleccionar el cliente cuando lo desee
                    }, 500); // Pequeño delay para asegurar que la página esté completamente cargada
                } else if (availableProducts === 0) {
                    // No hay productos en inventario
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin productos disponibles',
                        text: 'No hay productos disponibles en el inventario para realizar ventas',
                        confirmButtonText: 'Entendido'
                    });
                }
                // Si hay más de un producto, no hacer nada (comportamiento normal)
            }
            
            // Si hay un cliente preseleccionado, asegurarse de que Select2 lo muestre correctamente
            @if(isset($selectedCustomerId))
                $('#customer_id').trigger('change');
            @endif

            // Función para formatear las opciones en el dropdown
            function formatCustomer(customer) {
                if (!customer.id) {
                    return customer.text;
                }
                
                // Extraer nombre y deuda del texto
                const parts = customer.text.split(' - ');
                const name = parts[0];
                const debt = parts[1];
                
                // Crear un elemento HTML con formato mejorado
                const $container = $(
                    `<div class="d-flex justify-content-between align-items-center py-1">
                        <div>
                            <strong>${name}</strong>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-${debt.includes('0.00') ? 'success' : 'danger'}">${debt}</span>
                        </div>
                    </div>`
                );
                
                return $container;
            }
            
            // Función para formatear la opción seleccionada
            function formatCustomerSelection(customer) {
                if (!customer.id) {
                    return customer.text;
                }
                return customer.text;
            }

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
            
            // Verificar si solo hay un producto disponible y agregarlo automáticamente
            // Hacerlo después de que DataTable esté inicializado
            setTimeout(function() {
                checkAndAddSingleProduct();
            }, 100);

            // Función para agregar producto a la tabla
            // showAlert = true: muestra alerta cuando el usuario agrega manualmente
            // showAlert = false: no muestra alerta cuando se agrega automáticamente
            function addProductToTable(product, showAlert = true) {
                // Verificar si el producto ya está en la tabla
                const existingRow = $(`#saleItems tr[data-product-id="${product.id}"]`);
                
                if (existingRow.length > 0) {
                    // Si el producto ya existe, incrementar la cantidad
                    const quantityInput = existingRow.find('.quantity-input');
                    const currentQuantity = parseInt(quantityInput.val()) || 0;
                    const newQuantity = currentQuantity + 1;
                    
                    // Verificar stock
                    const maxStock = parseInt(quantityInput.attr('max'));
                    if (newQuantity > maxStock) {
                        if (showAlert) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Stock insuficiente',
                                text: `Solo hay ${maxStock} unidades disponibles`,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                        return;
                    }
                    
                    quantityInput.val(newQuantity).trigger('input');
                } else {
                    // Si es un producto nuevo, agregar una nueva fila
                    // Asegurar que tenemos una imagen válida
                    let imageUrl = product.image;
                    if (!imageUrl || imageUrl === '') {
                        imageUrl = '/img/no-image.png';
                    } else if (!imageUrl.startsWith('http') && !imageUrl.startsWith('/')) {
                        imageUrl = '/' + imageUrl;
                    }
                    

                    
                    const row = `
                        <tr data-product-id="${product.id}" data-product-code="${product.code}">
                            <td>${product.code}</td>
                            <td>${product.name}</td>
                            <td>
                                <span class="badge badge-${product.stock > 10 ? 'success' : (product.stock > 0 ? 'warning' : 'danger')}">
                                    ${product.stock}
                                </span>
                            </td>
                            <td>
                                <input type="number" class="form-control quantity-input" 
                                       value="1" min="1" max="${product.stock}" step="1">
                            </td>
                            <td>
                                {{ $currency->symbol }} ${product.sale_price}
                                <input type="hidden" class="price-input" value="${product.sale_price}">
                            </td>
                            <td>
                                <span class="subtotal-display">{{ $currency->symbol }} ${product.sale_price}</span>
                                <span class="subtotal-value d-none">${product.sale_price}</span>
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
                }
                
                // Mostrar notificación solo si showAlert es true
                if (showAlert) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Producto agregado!',
                        text: `${product.name} se agregó a la lista de venta`,
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
                const productId = $(this).data('id');
                
                $.ajax({
                    url: `/sales/product-details/${code}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            // Asegurar que el producto tenga el ID correcto
                            response.product.id = productId;
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

            // Manejar envío del formulario
            $('form').on('submit', function(e) {
                e.preventDefault();
                
                // Verificar si hay productos en la tabla
                if ($('#saleItems tr').length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe agregar al menos un producto a la venta'
                    });
                    return false;
                }
                
                // Verificar si se seleccionó un cliente
                if (!$('#customer_id').val()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe seleccionar un cliente'
                    });
                    return false;
                }
                
                // Preparar los datos de los productos
                const items = [];
                $('#saleItems tr').each(function() {
                    const row = $(this);
                    const productId = row.data('product-id');
                    console.log('Fila del producto:', {
                        productId: productId,
                        typeof: typeof productId,
                        dataAttributes: row.data()
                    });
                    items.push({
                        product_id: productId,
                        quantity: parseFloat(row.find('.quantity-input').val()),
                        price: parseFloat(row.find('.price-input').val()),
                        subtotal: parseFloat(row.find('.subtotal-value').text())
                    });
                });
                
                console.log('Items finales que se enviarán:', items);
                
                // Crear campos ocultos para los items
                $('#itemsContainer').remove(); // Eliminar contenedor previo si existe
                const container = $('<div id="itemsContainer"></div>');
                
                // Agregar cada item como un campo oculto
                items.forEach((item, index) => {
                    container.append(`<input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">`);
                    container.append(`<input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">`);
                    container.append(`<input type="hidden" name="items[${index}][price]" value="${item.price}">`);
                    container.append(`<input type="hidden" name="items[${index}][subtotal]" value="${item.subtotal}">`);
                });
                
                // Agregar el contenedor al formulario
                $(this).append(container);
                
                // Remover el event listener temporalmente para evitar bucles
                $(this).off('submit');
                
                // Enviar el formulario
                $(this).submit();
            });
            
            // Manejar clics en botones específicos para capturar la acción
            $('button[type="submit"]').on('click', function(e) {
                // Remover cualquier input de action previo
                $('input[name="action"]').remove();
                
                // Agregar el valor de action correspondiente al botón presionado
                const actionValue = $(this).val();
                $(this).closest('form').append(`<input type="hidden" name="action" value="${actionValue}">`);
            });

            // Manejar el botón de cancelar venta - retroceder a la vista anterior
            $('#cancelSale').click(function() {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "Se perderán todos los datos ingresados en esta venta",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cancelar venta',
                    cancelButtonText: 'No, continuar editando'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Retroceder a la página anterior
                        window.history.back();
                    }
                });
            });
        });
    </script>
@stop
