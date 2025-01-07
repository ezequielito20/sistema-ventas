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
                                            <button type="button" class="btn btn-success" id="addProduct">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-info" id="searchProduct">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        @error('product_code')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Cantidad -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="quantity" class="required">Cantidad</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-hashtag"></i>
                                            </span>
                                        </div>
                                        <input type="number" name="quantity" id="quantity"
                                            class="form-control @error('quantity') is-invalid @enderror"
                                            value="{{ old('quantity') }}" min="1" required>
                                        @error('quantity')
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
                                                    <th>Acciones</th>
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

            // Función para agregar producto a la tabla
            function addProductToTable(product) {
                const row = `
            <tr data-product-id="${product.id}">
                <td>${product.code}</td>
                <td>${product.name}</td>
                <td>
                    <input type="number" class="form-control quantity-input" 
                           name="items[${product.id}][quantity]" 
                           value="1" min="1" style="width: 100px">
                </td>
                <td>
                    <input type="number" class="form-control price-input" 
                           name="items[${product.id}][price]" 
                           value="${product.price}" step="0.01" style="width: 100px">
                </td>
                <td class="subtotal">${product.price}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
                $('#purchaseItems').append(row);
                updateTotal();
            }

            // Actualizar subtotales cuando cambie cantidad o precio
            $(document).on('input', '.quantity-input, .price-input', function() {
                const row = $(this).closest('tr');
                const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
                const price = parseFloat(row.find('.price-input').val()) || 0;
                const subtotal = quantity * price;
                row.find('.subtotal').text(subtotal.toFixed(2));
                updateTotal();
            });

            // Eliminar item
            $(document).on('click', '.remove-item', function() {
                $(this).closest('tr').remove();
                updateTotal();
            });

            // Actualizar total
            function updateTotal() {
                let total = 0;
                $('.subtotal').each(function() {
                    total += parseFloat($(this).text()) || 0;
                });
                $('#totalAmount').text(total.toFixed(2));
                $('#totalAmountInput').val(total.toFixed(2));
            }
        });
    </script>
@stop
