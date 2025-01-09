@extends('adminlte::page')

@section('title', 'Crear Producto')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Crear Nuevo Producto</h1>
        <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('admin.products.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>
            Volver
        </a>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf
        <div class="row">
            {{-- Información Básica --}}
            <div class="col-12 col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Información Básica</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">Código <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-barcode"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control @error('code') is-invalid @enderror"
                                            id="code" name="code" value="{{ old('code') }}" required>
                                    </div>
                                    @error('code')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Categoría <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('category_id') is-invalid @enderror"
                                        id="category_id" name="category_id" required>
                                        <option value="">Seleccionar categoría</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre del Producto <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                        name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="entry_date">Fecha de Ingreso <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar"></i>
                                            </span>
                                        </div>
                                        <input type="date" class="form-control @error('entry_date') is-invalid @enderror"
                                            id="entry_date" name="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}"
                                            max="{{ date('Y-m-d') }}" required>
                                    </div>
                                    @error('entry_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>


                    </div>
                </div>
            </div>

            {{-- Imagen del Producto --}}
            <div class="col-12 col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Imagen del Producto</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('image') is-invalid @enderror"
                                    id="image" name="image" accept="image/*">
                                <label class="custom-file-label" for="image">Elegir imagen</label>
                            </div>

                            <center><output id="list"></output></center>

                            @error('image')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror

                            <script>
                                function archivo(evt) {
                                    var files = evt.target.files;

                                    for (var i = 0, f; f = files[i]; i++) {
                                        if (!f.type.match('image.*')) {
                                            continue;
                                        }

                                        var reader = new FileReader();
                                        reader.onload = (function(theFile) {
                                            return function(e) {
                                                document.getElementById('list').innerHTML = [
                                                    '<img class="thumb thumbnail" src="',
                                                    e.target.result,
                                                    '" style="width: 100%; height: auto; border-radius: 8px; margin-top: 10px;" title="',
                                                    escape(theFile.name),
                                                    '"/>'
                                                ].join('');
                                            };
                                        })(f);
                                        reader.readAsDataURL(f);
                                    }
                                }

                                document.getElementById('image').addEventListener('change', archivo, false);
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            {{-- Información de Stock --}}
            <div class="col-12 col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Gestión de Stock</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="stock">Stock Actual <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                        id="stock" name="stock" value="{{ old('stock', 0) }}" required>
                                    @error('stock')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="min_stock">Stock Mínimo <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('min_stock') is-invalid @enderror"
                                        id="min_stock" name="min_stock" value="{{ old('min_stock', 0) }}" required>
                                    @error('min_stock')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_stock">Stock Máximo <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('max_stock') is-invalid @enderror"
                                        id="max_stock" name="max_stock" value="{{ old('max_stock', 0) }}" required>
                                    @error('max_stock')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Información de Precios --}}
            <div class="col-12 col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Precios</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="purchase_price">Precio de Compra <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{$currency->symbol}}</span>
                                        </div>
                                        <input type="number"
                                            class="form-control @error('purchase_price') is-invalid @enderror"
                                            id="purchase_price" name="purchase_price"
                                            value="{{ old('purchase_price', 0) }}" step="0.01" required>
                                    </div>
                                    @error('purchase_price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sale_price">Precio de Venta <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{$currency->symbol}}</span>
                                        </div>
                                        <input type="number"
                                            class="form-control @error('sale_price') is-invalid @enderror"
                                            id="sale_price" name="sale_price" value="{{ old('sale_price', 0) }}"
                                            step="0.01" required>
                                    </div>
                                    @error('sale_price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div id="profit-margin" class="alert alert-info mt-3" style="display: none;">
                            Margen de beneficio: <span id="profit-percentage">0</span>%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Producto
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
            </div>
        </div>
    </form>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap4',
            });

            // Preview de imagen
            $('#image').change(function() {
                const file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        $('#preview').attr('src', event.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Calcular margen de beneficio
            function calculateProfit() {
                const purchasePrice = parseFloat($('#purchase_price').val()) || 0;
                const salePrice = parseFloat($('#sale_price').val()) || 0;

                if (purchasePrice > 0) {
                    const profit = ((salePrice - purchasePrice) / purchasePrice) * 100;
                    $('#profit-percentage').text(profit.toFixed(2));
                    $('#profit-margin').show();

                    // Cambiar color según el margen
                    if (profit < 0) {
                        $('#profit-margin').removeClass('alert-info alert-success').addClass('alert-danger');
                    } else if (profit < 20) {
                        $('#profit-margin').removeClass('alert-danger alert-success').addClass('alert-warning');
                    } else {
                        $('#profit-margin').removeClass('alert-danger alert-warning').addClass('alert-success');
                    }
                } else {
                    $('#profit-margin').hide();
                }
            }

            $('#purchase_price, #sale_price').on('input', calculateProfit);

            // Validación de stock
            $('#stock, #min_stock, #max_stock').on('input', function() {
                const stock = parseInt($('#stock').val()) || 0;
                const minStock = parseInt($('#min_stock').val()) || 0;
                const maxStock = parseInt($('#max_stock').val()) || 0;

                if (minStock >= maxStock) {
                    $('#max_stock')[0].setCustomValidity(
                        'El stock máximo debe ser mayor que el stock mínimo');
                } else {
                    $('#max_stock')[0].setCustomValidity('');
                }

                if (stock < 0) {
                    $('#stock')[0].setCustomValidity('El stock no puede ser negativo');
                } else {
                    $('#stock')[0].setCustomValidity('');
                }
            });

            // Prevenir envío del formulario si hay errores
            $('#productForm').on('submit', function(e) {
                const stock = parseInt($('#stock').val()) || 0;
                const minStock = parseInt($('#min_stock').val()) || 0;
                const maxStock = parseInt($('#max_stock').val()) || 0;

                if (minStock >= maxStock) {
                    e.preventDefault();
                    alert('El stock máximo debe ser mayor que el stock mínimo');
                    return false;
                }

                if (stock < 0) {
                    e.preventDefault();
                    alert('El stock no puede ser negativo');
                    return false;
                }
            });
        });
    </script>
@stop
