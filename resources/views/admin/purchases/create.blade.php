@extends('adminlte::page')

@section('title', 'Nueva Compra')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Nueva Compra</h1>
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">
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
                        Información de la Compra
                    </h3>
                </div>

                <form action="{{ route('admin.purchases.store') }}" method="POST" enctype="multipart/form-data">
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
                                            placeholder="Ingrese el código del producto" value="{{ old('product_code') }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" id="addProduct"
                                                data-toggle="modal" data-target="#searchProductModal">
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
                        <!-- Tabla de productos agregados -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title">
                                            <i class="fas fa-shopping-cart mr-2"></i>
                                            Productos en la Compra
                                        </h3>
                                    </div>
                                    <div class="card-body table-responsive p-0">
                                        <table class="table table-hover text-nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Producto</th>
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
                                                    <td colspan="4" class="text-right"><strong>Total:</strong>
                                                    </td>
                                                    <td colspan="2">
                                                        <span id="totalAmount">0.00</span>
                                                        <input type="hidden" name="total_amount" id="totalAmountInput"
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
                            <button type="submit" class="btn btn-primary" id="submitPurchase">
                                <i class="fas fa-save mr-2"></i>
                                Registrar Compra
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
                <div class="modal-body">
                    <table id="productsTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Acción</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Stock</th>
                                <th>Precio Venta</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>{{ $product->code }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm select-product"
                                            data-code="{{ $product->code }}" data-dismiss="modal">
                                            <i class="fas fa-plus-circle mr-1"></i>
                                            Añadir
                                        </button>
                                    </td>
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
        .required::after {
            content: " *";
            color: red;
        }

        .card {
            box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
        }
    </style>
@stop

@section('js')
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

            // Permitir enviar el formulario presionando Enter en el código
            $('#product_code').keypress(function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    $('#addProduct').click();
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
                        console.log('Respuesta del servidor:', response); // Debug
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
                console.log('Agregando producto:', product); // Debug

                const row = `
                    <tr data-product-code="${product.code}">
                        <td>${product.code}</td>
                        <td>${product.name}</td>
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
                $('.subtotal').each(function() {
                    total += parseFloat($(this).text()) || 0;
                });
                $('#totalAmount').text(total.toFixed(2));
                $('#totalAmountInput').val(total.toFixed(2));
                console.log('Total actualizado:', total); // Debug
            }
        });
    </script>
@stop
