@extends('adminlte::page')

@section('title', 'Nueva Venta')

@section('content_header')
    <div class="modern-header">
        <div class="header-gradient"></div>
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Nueva Venta</h1>
                            <p class="header-subtitle">Registre una nueva transacción de venta</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="header-actions">
                        <button onclick="window.history.back()" class="btn btn-modern btn-secondary-modern">
                            {{-- <i class="fas fa-arrow-left"></i> --}}
                            <span>Volver</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="modern-sale-form">
        <form action="{{ route('admin.sales.store') }}" method="POST" enctype="multipart/form-data" id="saleForm">
            @csrf
            
            <!-- Sección de Información Básica -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3 class="section-title">Información de la Venta</h3>
                </div>
                
                <div class="row">
                    <!-- Código de Producto -->
                    <div class="col-xl-4 col-lg-3 col-md-6 col-12">
                    <div class="form-group-modern">
                        <label for="product_code" class="form-label required">
                            <i class="fas fa-barcode"></i>
                            Código de Producto
                        </label>
                        <div class="input-group-modern">
                            <input type="text" name="product_code" id="product_code"
                                class="form-control-modern @error('product_code') is-invalid @enderror"
                                placeholder="Escanee o ingrese el código del producto">
                            <div class="input-actions">
                                <button type="button" class="btn-action btn-search" id="searchProduct"
                                    data-toggle="modal" data-target="#searchProductModal">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="/products/create" class="btn-action btn-add">
                                    <i class="fas fa-plus"></i>
                                </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cliente -->
                    <div class="col-xl-4 col-lg-3 col-md-6 col-12">
                    <div class="form-group-modern">
                        <label for="customer_id" class="form-label required">
                            <i class="fas fa-user"></i>
                            Cliente
                        </label>
                        <div class="input-group-modern">
                            <select name="customer_id" id="customer_id"
                                class="form-control-modern select2 @error('customer_id') is-invalid @enderror" required>
                                <option value="">Seleccione un cliente</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ isset($selectedCustomerId) && $selectedCustomerId == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} - {{ $currency->symbol }} {{ number_format($customer->total_debt, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="input-actions">
                                <a href="{{ route('admin.customers.create') }}?return_to=sales.create" class="btn-action btn-add">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                        </div>
                        @error('customer_id')
                            <div class="error-message">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        </div>
                    </div>

                    <!-- Fecha de Venta -->
                    <div class="col-xl-2 col-lg-3 col-md-6 col-12">
                    <div class="form-group-modern">
                        <label for="sale_date" class="form-label required">
                            <i class="fas fa-calendar"></i>
                            Fecha de Venta
                        </label>
                        <div class="input-group-modern">
                            <input type="date" name="sale_date" id="sale_date"
                                class="form-control-modern @error('sale_date') is-invalid @enderror"
                                value="{{ old('sale_date', date('Y-m-d')) }}" required>
                        </div>
                            @error('sale_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Hora de Venta -->
                    <div class="col-xl-2 col-lg-3 col-md-6 col-12">
                        <div class="form-group-modern">
                            <label for="sale_time" class="form-label required">
                                <i class="fas fa-clock"></i>
                                Hora de Venta
                            </label>
                            <div class="input-group-modern">
                                <input type="time" name="sale_time" id="sale_time"
                                    class="form-control-modern @error('sale_time') is-invalid @enderror"
                                    value="{{ old('sale_time', date('H:i')) }}" required>
                            </div>
                            @error('sale_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- ¿Ya pagó? -->
                    <div class="col-xl-2 col-lg-3 col-md-6 col-12">
                        <div class="form-group-modern">
                            <label for="already_paid" class="form-label">
                                <i class="fas fa-credit-card"></i>
                                ¿Ya pagó?
                            </label>
                            <div class="input-group-modern">
                                <select name="already_paid" id="already_paid" class="form-control-modern">
                                    <option value="0" {{ old('already_paid', '0') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('already_paid', '0') == '1' ? 'selected' : '' }}>Sí</option>
                                </select>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i>
                                Si selecciona "Sí", se registrará automáticamente el pago
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Productos -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3 class="section-title">Productos en la Venta</h3>
                </div>
                
                <div class="products-table-container">
                    <div class="table-header">
                        <div class="table-info">
                            <div class="info-item">
                                <i class="fas fa-boxes"></i>
                                <span class="products-count">0 productos</span>
                            </div>
                            <div class="info-item total-info">
                                <i class="fas fa-calculator"></i>
                                <span class="total-amount-display">{{ $currency->symbol }} 0.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-barcode"></i> Código</th>
                                    <th><i class="fas fa-box"></i> Producto</th>
                                    <th><i class="fas fa-warehouse"></i> Stock</th>
                                    <th><i class="fas fa-sort-numeric-up"></i> Cantidad</th>
                                    <th><i class="fas fa-dollar-sign"></i> Precio Unit.</th>
                                    <th><i class="fas fa-calculator"></i> Subtotal</th>
                                    <th><i class="fas fa-cogs"></i> Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="saleItems">
                                <!-- Los productos se agregarán dinámicamente aquí -->
                            </tbody>
                        </table>
                        
                        <!-- Estado vacío -->
                        <div class="empty-state" id="emptyState">
                            <div class="empty-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h4>No hay productos agregados</h4>
                            <p>Agregue productos escaneando códigos o usando el buscador</p>
                        </div>
                    </div>
                    
                    <!-- Total de la venta y Nota -->
                    <div class="sale-total">
                        <!-- Campo de Nota -->
                        <div class="note-card">
                            <div class="note-icon">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <div class="note-content">
                                <label for="note" class="note-label">Nota de la Venta</label>
                                <textarea name="note" id="note" class="note-textarea" 
                                    placeholder="Agregue una nota adicional para esta venta (opcional)">{{ old('note') }}</textarea>
                            </div>
                        </div>
                        
                        <!-- Total de la venta -->
                        <div class="total-card">
                            <div class="total-icon">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="total-content">
                                <span class="total-label">Total de la Venta</span>
                                <span class="total-amount" id="totalAmount">{{ $currency->symbol }} 0.00</span>
                                <input type="hidden" name="total_price" id="totalAmountInput" value="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones del Formulario -->
            <div class="form-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-modern btn-danger-modern" id="cancelSale" title="Cancelar Venta">
                        <i class="fas fa-times-circle"></i>
                    </button>
                    
                    <button type="submit" class="btn-modern btn-primary-modern" id="submitSale" name="action" value="save" title="Procesar Venta">
                        <i class="fas fa-save"></i>
                    </button>
                    
                    <button type="submit" class="btn-modern btn-success-modern" id="submitSaleAndNew" name="action" value="save_and_new" title="Procesar y Nueva Venta">
                        <i class="fas fa-plus-circle"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal de Búsqueda de Productos -->
    <div class="modal fade" id="searchProductModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl">
            <div class="modal-content modern-modal">
                <div class="modal-header-modern">
                    <div class="modal-header-background">
                        <div class="modal-header-gradient"></div>
                    </div>
                    <div class="modal-header-content">
                        <div class="modal-title-section">
                            <div class="modal-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="modal-title-text">
                                <h4 class="modal-title-main">Búsqueda de Productos</h4>
                                <p class="modal-subtitle">Seleccione productos para agregar a la venta</p>
                            </div>
                        </div>
                        <button type="button" class="modal-close-btn" data-dismiss="modal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="modal-body-modern">
                    <div class="table-responsive">
                        <table id="productsTable" class="modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-barcode"></i> Código</th>
                                    <th><i class="fas fa-plus-circle"></i> Acción</th>
                                    <th><i class="fas fa-image"></i> Imagen</th>
                                    <th><i class="fas fa-box"></i> Nombre</th>
                                    <th><i class="fas fa-tags"></i> Categoría</th>
                                    <th><i class="fas fa-warehouse"></i> Stock</th>
                                    <th><i class="fas fa-dollar-sign"></i> Precio</th>
                                    <th><i class="fas fa-info-circle"></i> Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="align-middle">{{ $product->code }}</td>
                                        <td class="align-middle text-center">
                                            <button type="button" class="btn-action btn-primary select-product"
                                                data-code="{{ $product->code }}"
                                                data-id="{{ $product->id }}"
                                                {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-plus-circle"></i>
                                            </button>
                                        </td>
                                        <td class="align-middle">
                                            <img src="{{ $product->image_url }}" alt="N/I" class="product-thumbnail">
                                        </td>
                                        <td class="align-middle">{{ $product->name }}</td>
                                        <td class="align-middle">{{ $product->category->name }}</td>
                                        <td class="align-middle text-center">
                                            <span class="stock-badge badge-{{ $product->stock_status_class }}">
                                                {{ $product->stock }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-right">{{ $currency->symbol }}
                                            {{ number_format($product->sale_price, 2) }}
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="status-badge badge-{{ $product->stock_status_label === 'Bajo' ? 'danger' : ($product->stock_status_label === 'Normal' ? 'warning' : 'success') }}">
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
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/responsive.bootstrap4.min.css') }}">
    <link href="{{ asset('vendor/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/select2/select2-bootstrap4.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    
    <style>

        :root {
            --primary-color: #667eea;
            --success-color: #48bb78;
            --danger-color: #f56565;
            --warning-color: #ed8936;
            --info-color: #4299e1;
            --dark-color: #2d3748;
            --light-color: #f7fafc;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            --gradient-danger: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            --gradient-warning: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            --gradient-info: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
        }

        /* Estilos para campos en una línea */
        .form-group-modern {
            margin-bottom: 1rem;
        }
        
        .form-group-modern .form-label {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        /* Contenedor principal más ancho */
        .modern-sale-form {
            max-width: 100%;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        /* Responsive para pantallas extra anchas */
        @media (min-width: 1400px) {
            .modern-sale-form {
                max-width: 95%;
                padding: 0 2rem;
            }
            
            .form-section {
                padding: 2rem 3rem;
            }
            
            .products-table-container {
                margin: 0 -1rem;
            }
        }
        
        /* Responsive para pantallas anchas */
        @media (min-width: 1200px) and (max-width: 1399px) {
            .modern-sale-form {
                max-width: 98%;
                padding: 0 1.5rem;
            }
        }
        
        /* Responsive para pantallas pequeñas */
        @media (max-width: 768px) {
            .col-lg-3, .col-xl-2, .col-xl-4 {
                margin-bottom: 1rem;
            }
        }
        
        /* Ajustes para campos de fecha y hora */
        input[type="date"], input[type="time"] {
            min-width: 120px;
        }
        
        /* Select2 responsive */
        .select2-container {
            width: 100% !important;
        }
        
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        /* Header Moderno */
        .modern-header {
            position: relative;
            margin: -15px -15px 20px -15px;
            padding: 2rem 0;
            overflow: hidden;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }

        .header-gradient {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-primary);
        }

        .header-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .header-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            backdrop-filter: blur(10px);
        }

        .header-title {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            font-size: 1rem;
        }

        .header-actions {
            display: flex;
            justify-content: flex-end;
        }

        /* Formulario Moderno */
        .modern-sale-form {
            max-width: 1200px;
            margin: 0 auto;
        }

        .form-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .section-header {
            background: var(--gradient-primary);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .section-title {
            color: white;
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
        }

        .form-grid {
            padding: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group-modern {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--dark-color);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-label.required::after {
            content: " *";
            color: var(--danger-color);
        }

        .input-group-modern {
            position: relative;
            display: flex;
            align-items: center;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }

        .input-group-modern:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-control-modern {
            flex: 1;
            border: none;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            background: transparent;
            color: var(--dark-color);
        }

        .form-control-modern:focus {
            outline: none;
        }

        .form-control-modern::placeholder {
            color: #a0aec0;
        }

        .input-actions {
            display: flex;
            gap: 0.25rem;
            padding: 0.25rem;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-action.btn-search {
            background: var(--gradient-info);
            color: white;
        }

        .btn-action.btn-add {
            background: var(--gradient-success);
            color: white;
        }

        .btn-action.btn-primary {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-action:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .error-message {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Tabla de Productos */
        .products-table-container {
            padding: 2rem;
            width: 100%;
        }
        
        /* Tabla más ancha en pantallas grandes */
        @media (min-width: 1200px) {
            .products-table-container {
                padding: 2rem 1rem;
                overflow-x: visible;
            }
            
            .table-responsive {
                overflow-x: visible;
            }
            
            .products-table {
                min-width: 100%;
            }
        }

        .table-header {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: var(--border-radius);
            border: 2px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .table-info {
            background: white;
            display: flex;
            align-items: center;
            gap: 3rem;
            width: 100%;
            justify-content: center;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            background: rgb(73, 214, 224);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            min-width: 140px;
            justify-content: center;
        }

        .info-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .info-item i {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .products-count {
            color: var(--dark-color);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .total-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .total-info i {
            color: white;
        }

        .total-amount-display {
            color: white;
            font-weight: 700;
            font-size: 1rem;
        }

        .table-wrapper {
            position: relative;
            min-height: 120px;
            overflow-x: auto;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .table-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 20px;
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.8) 100%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .table-wrapper.has-scroll::after {
            opacity: 1;
        }

        .modern-table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            table-layout: fixed;
        }

        .modern-table thead {
            background: var(--gradient-primary);
        }

        .modern-table thead th {
            padding: 0.75rem 1rem;
            text-align: left;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
            min-width: 120px;
            height: auto;
        }

        .modern-table thead th:first-child {
            min-width: 100px;
        }

        .modern-table thead th:nth-child(2) {
            min-width: 200px;
        }

        .modern-table thead th:nth-child(3) {
            min-width: 80px;
            text-align: center;
        }

        .modern-table thead th:nth-child(4) {
            min-width: 120px;
            text-align: center;
        }

        .modern-table thead th:nth-child(5) {
            min-width: 120px;
            text-align: right;
        }

        .modern-table thead th:nth-child(6) {
            min-width: 120px;
            text-align: right;
        }

        .modern-table thead th:last-child {
            min-width: 80px;
            text-align: center;
        }

        .modern-table thead th i {
            margin-right: 0.5rem;
        }

        .modern-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.3s ease;
        }

        .modern-table tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }

        .modern-table tbody tr:last-child {
            border-bottom: none;
        }

        .modern-table tbody {
            min-height: auto;
        }

        /* Cuando no hay productos, ocultar la tabla */
        .table-wrapper:has(#saleItems:empty) .modern-table {
            display: none;
        }

        /* Cuando hay productos, mostrar la tabla y ocultar el estado vacío */
        .table-wrapper:has(#saleItems:not(:empty)) #emptyState {
            display: none;
        }

        .modern-table tbody td {
            padding: 0.75rem 1rem;
            color: var(--dark-color);
            font-size: 0.9rem;
            vertical-align: middle;
            height: auto;
        }

        .modern-table tbody td:nth-child(3) {
            text-align: center;
        }

        .modern-table tbody td:nth-child(4) {
            text-align: center;
        }

        .modern-table tbody td:nth-child(5) {
            text-align: right;
        }

        .modern-table tbody td:nth-child(6) {
            text-align: right;
        }

        .modern-table tbody td:last-child {
            text-align: center;
        }

        .product-thumbnail {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
        }

        .stock-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stock-badge.badge-success {
            background: var(--gradient-success);
            color: white;
        }

        .stock-badge.badge-warning {
            background: var(--gradient-warning);
            color: white;
        }

        .stock-badge.badge-danger {
            background: var(--gradient-danger);
            color: white;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .quantity-input {
            width: 80px;
            padding: 0.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
        }

        .quantity-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Scrollbar personalizado para la tabla */
        .table-wrapper::-webkit-scrollbar {
            height: 8px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #5a67d8;
        }

        /* Estado Vacío */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #a0aec0;
            padding: 2rem;
            min-height: 120px;
            background: white;
            border-radius: var(--border-radius);
        }

        .empty-state .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h4 {
            margin: 0 0 0.5rem 0;
            color: var(--dark-color);
        }

        .empty-state p {
            margin: 0;
            font-size: 0.9rem;
        }

        /* Total de la Venta y Nota */
        .sale-total {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
        }

        /* Campo de Nota */
        .note-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            min-width: 300px;
            flex: 1;
            max-width: 400px;
        }

        .note-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-info);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: white;
            flex-shrink: 0;
        }

        .note-content {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex: 1;
        }

        .note-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }

        .note-textarea {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.9rem;
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .note-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .note-textarea::placeholder {
            color: #a0aec0;
            font-style: italic;
        }

        .total-card {
            background: var(--gradient-success);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 8px 25px rgba(72, 187, 120, 0.3);
            min-width: 300px;
            flex-shrink: 0;
        }

        .total-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .total-content {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .total-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .total-amount {
            font-size: 1.5rem;
            font-weight: 800;
        }

        /* Acciones del Formulario */
        .form-actions {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
        }

        .btn-modern {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border: none;
            border-radius: 50%;
            font-weight: 600;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            position: relative;
        }

        .btn-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
            text-decoration: none;
        }

        .btn-modern:active {
            transform: translateY(-1px);
        }

        .btn-modern.btn-primary-modern {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-modern.btn-success-modern {
            background: var(--gradient-success);
            color: white;
        }

        .btn-modern.btn-danger-modern {
            background: var(--gradient-danger);
            color: white;
        }

        .btn-modern.btn-secondary-modern {
            background: #e2e8f0;
            color: var(--dark-color);
        }

        /* Estilo para botones deshabilitados */
        .btn-modern:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn-modern:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        /* Tooltip para los botones */
        .btn-modern::before {
            content: attr(title);
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .btn-modern::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-bottom-color: rgba(0, 0, 0, 0.8);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .btn-modern:hover::before,
        .btn-modern:hover::after {
            opacity: 1;
            visibility: visible;
        }

        /* Modal Moderno */
        .modern-modal {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header-modern {
            position: relative;
            padding: 0;
            border: none;
            overflow: hidden;
            height: 120px;
        }

        .modal-header-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-primary);
        }

        .modal-header-gradient {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
        }

        .modal-header-content {
            position: relative;
            z-index: 2;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }

        .modal-title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .modal-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            backdrop-filter: blur(10px);
        }

        .modal-title-main {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .modal-subtitle {
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            font-size: 0.9rem;
        }

        .modal-close-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .modal-close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .modal-body-modern {
            padding: 2rem;
            background: #f8fafc;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .table-info {
                gap: 2rem;
            }

            .info-item {
                min-width: 120px;
                padding: 0.6rem 1.2rem;
            }
        }

        @media (max-width: 768px) {
            .modern-header {
                margin: -15px -15px 15px -15px;
                padding: 1.5rem 0;
            }

            .header-title {
                font-size: 1.5rem;
            }

            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .header-actions {
                justify-content: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }

            .section-header {
                padding: 1rem;
            }

            .products-table-container {
                padding: 1rem;
            }

            .table-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1rem;
            }

            .table-info {
                flex-direction: column;
                gap: 1rem;
                width: 100%;
            }

            .info-item {
                min-width: auto;
                width: 100%;
                max-width: 250px;
            }

            .table-wrapper {
                margin: 0 -1rem;
                border-radius: 0;
            }

            .modern-table {
                min-width: 700px;
            }

            .modern-table thead th {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }

            .modern-table tbody td {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }

            .table-wrapper {
                min-height: 100px;
            }

            .empty-state {
                min-height: 100px;
                padding: 1.5rem;
            }

            .action-buttons {
                gap: 0.75rem;
            }

            .btn-modern {
                width: 55px;
                height: 55px;
                font-size: 1.1rem;
            }

            .sale-total {
                flex-direction: column;
                gap: 1rem;
            }

            .note-card {
                min-width: auto;
                width: 100%;
                max-width: none;
            }

            .total-card {
                min-width: auto;
                width: 100%;
            }

            .modal-header-content {
                padding: 1rem;
            }

            .modal-title-main {
                font-size: 1.2rem;
            }

            .modal-body-modern {
                padding: 1rem;
            }
        }

        @media (max-width: 576px) {
            .btn-modern {
                width: 50px;
                height: 50px;
                font-size: 1rem;
            }

            .action-buttons {
                gap: 0.5rem;
            }

            .form-control-modern {
                font-size: 16px;
            }

            .modern-table {
                min-width: 600px;
            }

            .modern-table thead th {
                padding: 0.4rem 0.5rem;
                font-size: 0.75rem;
            }

            .modern-table tbody td {
                padding: 0.4rem 0.5rem;
                font-size: 0.75rem;
            }

            .table-wrapper {
                min-height: 80px;
            }

            .empty-state {
                min-height: 80px;
                padding: 1rem;
            }

            .quantity-input {
                width: 60px;
                padding: 0.375rem;
                font-size: 0.8rem;
            }
        }

        /* Select2 Moderno */
        .select2-container--bootstrap4 .select2-selection--single {
            height: auto !important;
            padding: 0.75rem 1rem;
            border: none !important;
            background: transparent !important;
            min-height: 48px;
            display: flex;
            align-items: center;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
            padding-left: 0 !important;
            padding-right: 0 !important;
            color: var(--dark-color) !important;
            font-size: 1rem;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
            color: #a0aec0 !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: auto;
            top: 50%;
            transform: translateY(-50%);
            right: 1rem;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
            border-color: var(--primary-color) transparent transparent transparent;
        }

        /* Dropdown styles */
        .select2-container--bootstrap4 .select2-dropdown {
            border: 2px solid var(--primary-color) !important;
            border-radius: var(--border-radius) !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
            z-index: 9999 !important;
            background: white !important;
        }

        .select2-container--bootstrap4 .select2-results__options {
            max-height: 300px;
        }

        .select2-container--bootstrap4 .select2-results__option {
            padding: 0.75rem 1rem !important;
            color: var(--dark-color) !important;
            background: white !important;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.95rem;
        }

        .select2-container--bootstrap4 .select2-results__option:last-child {
            border-bottom: none;
        }

        .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary-color) !important;
            color: white !important;
        }

        .select2-container--bootstrap4 .select2-results__option[aria-selected="true"] {
            background-color: rgba(102, 126, 234, 0.1) !important;
            color: var(--primary-color) !important;
            font-weight: 600;
        }

        .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
            border: 2px solid #e2e8f0 !important;
            border-radius: 8px !important;
            padding: 0.5rem !important;
            font-size: 0.95rem;
        }

        .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--primary-color) !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
        }

        /* Estilos para las opciones formateadas */
        .select2-results__option .d-flex {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            width: 100%;
        }

        .select2-results__option .badge {
            font-size: 0.75rem !important;
            padding: 0.25rem 0.5rem !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
        }

        .select2-results__option .badge-success {
            background: var(--gradient-success) !important;
            color: white !important;
        }

        .select2-results__option .badge-danger {
            background: var(--gradient-danger) !important;
            color: white !important;
        }

        /* Asegurar que el container tenga el ancho correcto */
        .select2-container {
            width: 100% !important;
        }

        /* Fix para el z-index del dropdown */
        .select2-dropdown {
            z-index: 9999 !important;
        }

        /* Estilos para cuando está dentro del input-group-modern */
        .input-group-modern .select2-container {
            flex: 1;
        }

        .input-group-modern .select2-container .select2-selection--single {
            border-radius: 0 !important;
        }

        /* Estilos adicionales para mejorar la visibilidad */
        .select2-container--bootstrap4.select2-container--open .select2-selection--single {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
        }

        .select2-container--bootstrap4 .select2-results__message {
            color: #6c757d !important;
            padding: 1rem !important;
            text-align: center;
            font-style: italic;
        }

        /* Asegurar que el texto sea legible */
        .select2-results__option strong {
            color: #2d3748 !important;
            font-weight: 600 !important;
        }

        /* Mejorar el contraste del placeholder */
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
            color: #9ca3af !important;
            font-style: italic;
        }

        /* Estilos para el estado focus */
        .input-group-modern:focus-within .select2-container .select2-selection--single {
            border-color: var(--primary-color) !important;
        }

        /* Asegurar que el dropdown tenga suficiente espacio */
        .select2-container--bootstrap4 .select2-dropdown .select2-results {
            padding: 0;
        }

        /* Mejorar la apariencia del campo de búsqueda */
        .select2-container--bootstrap4 .select2-search--dropdown {
            padding: 0.75rem !important;
            background: #f8f9fa !important;
            border-bottom: 1px solid #e9ecef !important;
        }

        /* Animaciones */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-section {
            animation: fadeIn 0.5s ease-out;
        }

        .form-section:nth-child(2) {
            animation-delay: 0.1s;
        }

        .form-section:nth-child(3) {
            animation-delay: 0.2s;
        }

        /* Estilos para el campo de pago */
        .form-text {
            color: #a0aec0;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        .form-text i {
            margin-right: 0.25rem;
        }

        #already_paid {
            font-weight: 600;
        }

        #already_paid option[value="1"] {
            color: var(--success-color);
        }

        #already_paid option[value="0"] {
            color: var(--warning-color);
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Cargar Select2, DataTables y SweetAlert2
            loadSelect2(function() {
                loadDataTables(function() {
                    loadSweetAlert2(function() {
                        // Guardar la URL original cuando se carga la página por primera vez
                        if (!sessionStorage.getItem('sales_original_referrer')) {
                            const referrer = document.referrer;
                            if (referrer && !referrer.includes('/sales/create')) {
                                sessionStorage.setItem('sales_original_referrer', referrer);
                            }
                        }
                        
                        // Inicializar Select2 con opciones mejoradas
            $('#customer_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccione un cliente',
                allowClear: true,
                width: '100%',
                dropdownAutoWidth: false,
                dropdownParent: $('body'), // Cambiar a body para evitar problemas de z-index
                escapeMarkup: function(markup) {
                    return markup;
                },
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    },
                    loadingMore: function() {
                        return "Cargando más resultados...";
                    }
                },
                templateResult: formatCustomer,
                templateSelection: formatCustomerSelection,
                matcher: function(params, data) {
                    // Si no hay término de búsqueda, mostrar todas las opciones
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // Si no hay datos, no mostrar nada
                    if (typeof data.text === 'undefined') {
                        return null;
                    }

                    // Buscar en el texto completo (nombre y deuda)
                    const searchTerm = params.term.toLowerCase();
                    const fullText = data.text.toLowerCase();
                    
                    if (fullText.indexOf(searchTerm) > -1) {
                        return data;
                    }

                    // No hay coincidencia
                    return null;
                }
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
                    const productStock = productRow.find('td:eq(5) .stock-badge').text().trim();
                    const productPriceText = productRow.find('td:eq(6)').text().trim();
                    const productPrice = parseFloat(productPriceText.replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
                    
                    // Obtener el ID real del producto desde el botón
                    const productId = productRow.find('button.select-product').data('id');
                    
                    
                    // Crear objeto producto con los datos disponibles
                    const product = {
                        id: productId, // ID real del producto
                        code: productCode,
                        name: productName,
                        image: productImage,
                        stock: parseInt(productStock),
                        sale_price: productPrice
                    };
                    
                    
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
                $('#customer_id').val('{{ $selectedCustomerId }}').trigger('change');
            @endif

            // Asegurar que Select2 se renderice correctamente después de la inicialización
            setTimeout(function() {
                $('#customer_id').trigger('change.select2');
            }, 100);

            // Manejar eventos de apertura y cierre del dropdown
            $('#customer_id').on('select2:open', function() {
                // Asegurar que el dropdown tenga el z-index correcto
                $('.select2-dropdown').css('z-index', 9999);
                
                // Enfocar el campo de búsqueda si existe
                setTimeout(function() {
                    $('.select2-search__field').focus();
                }, 100);
            });

            $('#customer_id').on('select2:close', function() {
                // Remover cualquier estilo temporal
                $('.select2-dropdown').css('z-index', '');
            });

            // Función para formatear las opciones en el dropdown
            function formatCustomer(customer) {
                if (!customer.id) {
                    return customer.text;
                }
                
                // Extraer nombre y deuda del texto
                const parts = customer.text.split(' - ');
                if (parts.length < 2) {
                    return customer.text;
                }
                
                const name = parts[0].trim();
                const debt = parts[1].trim();
                
                // Determinar el tipo de badge basado en la deuda
                const badgeClass = debt.includes('0.00') ? 'success' : 'danger';
                
                // Crear un elemento HTML con formato mejorado
                const $container = $(
                    `<div class="d-flex justify-content-between align-items-center" style="width: 100%; padding: 2px 0;">
                        <div style="flex: 1;">
                            <strong style="color: #2d3748; font-size: 0.95rem;">${name}</strong>
                        </div>
                        <div style="flex-shrink: 0; margin-left: 1rem;">
                            <span class="badge badge-${badgeClass}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 12px; font-weight: 600;">${debt}</span>
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
                language: window.DataTablesSpanishConfig,
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
                ],
                initComplete: function() {
                    // Reagregar event listeners después de que DataTable esté listo
                    $('.select-product').off('click').on('click', function() {
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
                }
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
                                <span class="stock-badge badge-${product.stock > 10 ? 'success' : (product.stock > 0 ? 'warning' : 'danger')}">
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
                                <button type="button" class="btn-action btn-danger remove-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    
                    $('#saleItems').append(row);
                    updateTotal();
                    updateEmptyState();
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

            // Función para actualizar el estado vacío
            function updateEmptyState() {
                const hasProducts = $('#saleItems tr').length > 0;
                const tableWrapper = $('.table-wrapper');
                
                if (hasProducts) {
                    $('#emptyState').hide();
                    $('.modern-table').show();
                    tableWrapper.css('min-height', 'auto');
                } else {
                    $('#emptyState').show();
                    $('.modern-table').hide();
                    tableWrapper.css('min-height', '120px');
                }
            }

            // Función para actualizar contadores
            function updateCounters() {
                const productCount = $('#saleItems tr').length;
                $('.products-count').text(`${productCount} producto${productCount !== 1 ? 's' : ''}`);
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
                        updateEmptyState();
                        updateCounters();
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
                $('.total-amount-display').text('{{ $currency->symbol }} ' + total.toFixed(2));
                updateCounters();
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
                        // Resetear fecha y hora a valores actuales
                        $('#sale_date').val('{{ date('Y-m-d') }}');
                        $('#sale_time').val('{{ date('H:i') }}');
                        updateTotal();
                        updateEmptyState();
                    }
                });
            });

            // Manejar envío del formulario
            $('form').on('submit', function(e) {
                e.preventDefault();
                
                // Deshabilitar ambos botones para prevenir múltiples envíos
                $('#submitSale, #submitSaleAndNew').prop('disabled', true);
                
                // Verificar si hay productos en la tabla
                if ($('#saleItems tr').length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe agregar al menos un producto a la venta'
                    });
                    // Rehabilitar botones si hay error
                    $('#submitSale, #submitSaleAndNew').prop('disabled', false);
                    return false;
                }
                
                // Verificar si se seleccionó un cliente
                if (!$('#customer_id').val()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe seleccionar un cliente'
                    });
                    // Rehabilitar botones si hay error
                    $('#submitSale, #submitSaleAndNew').prop('disabled', false);
                    return false;
                }

                // Verificar si se seleccionó el estado de pago
                if (!$('#already_paid').val()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe seleccionar si el cliente ya pagó o no'
                    });
                    // Rehabilitar botones si hay error
                    $('#submitSale, #submitSaleAndNew').prop('disabled', false);
                    return false;
                }
                
                // Preparar los datos de los productos
                const items = [];
                $('#saleItems tr').each(function() {
                    const row = $(this);
                    const productId = row.data('product-id');
                    
                    items.push({
                        product_id: productId,
                        quantity: parseFloat(row.find('.quantity-input').val()),
                        price: parseFloat(row.find('.price-input').val()),
                        subtotal: parseFloat(row.find('.subtotal-value').text())
                    });
                });
                
                
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
                        goBack();
                    }
                });
            });

            // Función para navegar de vuelta a la vista original
            function goBack() {
                // Verificar si hay una URL de referencia guardada en sessionStorage
                const originalReferrer = sessionStorage.getItem('sales_original_referrer');
                
                if (originalReferrer && originalReferrer !== window.location.href) {
                    // Si tenemos una URL original guardada, ir allí
                    window.location.href = originalReferrer;
                } else {
                    // Comportamiento normal del botón volver
                    window.history.back();
                }
            }

            // Event listener para el botón volver
            $('button[onclick="window.history.back()"]').removeAttr('onclick').click(function() {
                goBack();
            });

            // Inicializar estado vacío
            updateEmptyState();
            updateCounters();

            // Manejar cambio en el campo "¿Ya pagó?"
            $('#already_paid').on('change', function() {
                const alreadyPaid = $(this).val() === '1';
                const customerId = $('#customer_id').val();
                
                if (customerId && alreadyPaid) {
                    // Mostrar información sobre el pago automático
                    Swal.fire({
                        icon: 'info',
                        title: 'Pago Automático',
                        text: 'Al seleccionar "Sí", se registrará automáticamente el pago de esta venta y no se incrementará la deuda del cliente.',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#667eea'
                    });
                }
            });



            // Detectar scroll horizontal en la tabla
            function checkTableScroll() {
                const tableWrapper = $('.table-wrapper');
                const table = tableWrapper.find('.modern-table');
                
                if (table.width() > tableWrapper.width()) {
                    tableWrapper.addClass('has-scroll');
                } else {
                    tableWrapper.removeClass('has-scroll');
                }
            }

            // Verificar scroll al cargar y al cambiar el tamaño de la ventana
            $(window).on('load resize', function() {
                setTimeout(checkTableScroll, 100);
            });

            // Verificar scroll cuando se agregan productos (usando MutationObserver en lugar de DOMNodeInserted)
            const saleItemsContainer = document.getElementById('saleItems');
            if (saleItemsContainer) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList') {
                            setTimeout(checkTableScroll, 100);
                        }
                    });
                });
                
                observer.observe(saleItemsContainer, {
                    childList: true,
                    subtree: true
                });
            }
            
                    });
                });
            });
        });
    </script>
@stop