@extends('adminlte::page')

@section('title', 'Editar Compra')

@section('meta')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
@stop

@section('content_header')
    <div class="modern-header">
        <div class="header-content">
            <div class="header-left">
                <div class="title-section">
                    <div class="icon-wrapper">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="title-text">
                        <h1 class="main-title">Editar Compra #{{ $purchase->id }}</h1>
                        <p class="subtitle">Modifique los datos de la compra seleccionada</p>
                    </div>
                </div>
            </div>
            <div class="header-actions">
                <button onclick="window.history.back()" class="action-btn back-btn">
                    <div class="btn-content">
                        <i class="fas fa-arrow-left"></i>
                        <span class="btn-text">Volver</span>
                    </div>
                    <div class="btn-glow"></div>
                </button>
            </div>
        </div>
        <div class="header-decoration">
            <div class="decoration-line"></div>
            <div class="decoration-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Alertas Modernas -->
    @if(session('message'))
        <div class="alert alert-{{ session('icons') == 'success' ? 'success' : 'danger' }} alert-dismissible fade show modern-alert" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-{{ session('icons') == 'success' ? 'check-circle' : 'exclamation-triangle' }}"></i>
                </div>
                <div class="alert-text">
                    <span>{{ session('message') }}</span>
                </div>
            </div>
            <button type="button" class="alert-close" data-dismiss="alert" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show modern-alert" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-text">
                    <strong>¡Errores encontrados!</strong>
                    <ul class="error-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="alert-close" data-dismiss="alert" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <form action="{{ route('admin.purchases.update', $purchase->id) }}" method="POST" enctype="multipart/form-data" id="purchaseForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Panel Principal -->
            <div class="col-lg-8">
                <!-- Panel de Información -->
                <div class="main-panel">
                    <div class="panel-header">
                        <div class="header-content">
                            <div class="title-section">
                                <div class="title-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="title-text">
                                    <h3 class="panel-title">Información de la Compra</h3>
                                    <p class="panel-subtitle">Complete los datos básicos de la compra</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <!-- Código de Producto -->
                            <div class="col-xl-5 col-lg-6 col-md-12 col-12">
                                <div class="form-group-modern">
                                    <label for="product_code" class="form-label required">
                                        <i class="fas fa-barcode"></i>
                                        Código de Producto
                                    </label>
                                    <div class="input-group-modern">
                                        <input type="text" name="product_code" id="product_code"
                                            class="form-control-modern @error('product_code') is-invalid @enderror"
                                            placeholder="Escanee o ingrese el código del producto" 
                                            value="{{ old('product_code') }}"
                                            autocomplete="off">
                                        <div class="input-actions">
                                            <button type="button" class="action-btn search-btn" id="addProduct" data-toggle="modal"
                                                data-target="#searchProductModal">
                                                <i class="fas fa-search"></i>
                                                <span>Buscar</span>
                                            </button>
                                            <a href="/products/create" class="action-btn new-btn">
                                                <i class="fas fa-plus"></i>
                                                <span>Nuevo</span>
                                            </a>
                                        </div>
                                        @error('product_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-help">
                                        <i class="fas fa-lightbulb"></i>
                                        Presione Enter después de escanear o escribir el código
                                    </div>
                                </div>
                            </div>

                            <!-- Fecha y Hora de Compra -->
                            <div class="col-xl-7 col-lg-6 col-md-12 col-12">
                                <div class="row">
                                    <!-- Fecha de Compra -->
                                    <div class="col-md-6 col-12">
                                        <div class="form-group-modern">
                                            <label for="purchase_date" class="form-label required">
                                                <i class="fas fa-calendar"></i>
                                                Fecha de Compra
                                            </label>
                                            <div class="input-group-modern">
                                                <input type="date" name="purchase_date" id="purchase_date"
                                                    class="form-control-modern @error('purchase_date') is-invalid @enderror"
                                                    value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" required>
                                            </div>
                                            @error('purchase_date')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <!-- Hora de Compra -->
                                    <div class="col-md-6 col-12">
                                        <div class="form-group-modern">
                                            <label for="purchase_time" class="form-label required">
                                                <i class="fas fa-clock"></i>
                                                Hora de Compra
                                            </label>
                                            <div class="input-group-modern">
                                                <input type="time" name="purchase_time" id="purchase_time"
                                                    class="form-control-modern @error('purchase_time') is-invalid @enderror"
                                                    value="{{ old('purchase_time', $purchase->purchase_date->format('H:i')) }}" required>
                                            </div>
                                            @error('purchase_time')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de Productos -->
                <div class="main-panel">
                    <div class="panel-header">
                        <div class="header-content">
                            <div class="title-section">
                                <div class="title-icon">
                                    <i class="fas fa-shopping-basket"></i>
                                </div>
                                <div class="title-text">
                                    <h3 class="panel-title">Productos en la Compra</h3>
                                    <p class="panel-subtitle">Gestiona los productos de esta compra</p>
                                </div>
                            </div>
                            <div class="header-controls">
                                <div class="product-counter">
                                    <span class="counter-badge" id="productCount">{{ $purchase->details->count() }} productos</span>
                                </div>
                                <button type="button" class="panel-toggle" data-card-widget="collapse">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-container">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th class="th-product">Producto</th>
                                        <th class="th-stock">Stock</th>
                                        <th class="th-quantity">Cant</th>
                                        <th class="th-price">Precio Unit.</th>
                                        <th class="th-subtotal">Subtotal</th>
                                        <th class="th-actions">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="purchaseItems">
                                    @foreach ($purchase->details as $detail)
                                        <tr data-product-code="{{ $detail->product->code }}" data-product-id="{{ $detail->product->id }}" class="fade-in-up">
                                            <td>
                                                <div class="product-item">
                                                    <img src="{{ $detail->product->image_url }}"
                                                        alt="{{ $detail->product->name }}" class="product-image">
                                                    <div class="product-info">
                                                        <div class="product-name">{{ $detail->product->name }}</div>
                                                        <div class="product-code">{{ $detail->product->code }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="stock-badge badge badge-{{ $detail->product->stock > 10 ? 'success' : ($detail->product->stock > 0 ? 'warning' : 'danger') }}">
                                                    {{ $detail->product->stock }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <input type="number" 
                                                    class="form-control quantity-control quantity-input" 
                                                    name="items[{{ $detail->product->id }}][quantity]" 
                                                    value="{{ $detail->quantity }}" 
                                                    min="1"
                                                    max="{{ $detail->product->stock + $detail->quantity }}"
                                                    style="text-align: center;">
                                            </td>
                                            <td class="text-center">
                                                <div class="input-group price-control">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">{{ $currency->symbol }}</span>
                                                    </div>
                                                    <input type="number" 
                                                        class="form-control price-input" 
                                                        name="items[{{ $detail->product->id }}][price]" 
                                                        value="{{ $detail->product->purchase_price }}" 
                                                        step="0.01">
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <span class="subtotal-text">{{ $currency->symbol }} <span class="subtotal">{{ number_format($detail->quantity * $detail->product->purchase_price, 2) }}</span></span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Estado vacío -->
                        <div id="emptyState" class="empty-state" style="display: none;">
                            <div class="empty-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h5 class="empty-title">No hay productos agregados</h5>
                            <p class="empty-description">Escanee un producto o use el botón "Buscar" para agregar productos a la compra</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="col-lg-4">
                <!-- Panel Unificado de Resumen y Acciones -->
                <div class="sidebar-panel sticky-top">
                    <div class="panel-header">
                        <div class="title-section">
                            <div class="title-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="title-text">
                                <h3 class="panel-title">Resumen y Acciones</h3>
                                <p class="panel-subtitle">Información y opciones de la compra</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección de Resumen -->
                    <div class="panel-section">
                        <div class="section-header">
                            <h4 class="section-title">
                                <i class="fas fa-calculator"></i>
                                Resumen de Compra
                            </h4>
                        </div>
                        <div class="summary-stats">
                            <div class="summary-item">
                                <div class="summary-icon">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-value" id="totalProducts">{{ $purchase->details->count() }}</div>
                                    <div class="summary-label">Productos Únicos</div>
                                </div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon">
                                    <i class="fas fa-cubes"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-value" id="totalQuantity">{{ $purchase->details->sum('quantity') }}</div>
                                    <div class="summary-label">Cantidad Total</div>
                                </div>
                            </div>
                            

                            
                            <div class="summary-item total">
                                <div class="summary-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-value total-amount" id="totalAmountDisplay">{{ $currency->symbol }} {{ number_format($purchase->details->sum(function($detail) { return $detail->quantity * $detail->product->purchase_price; }), 2) }}</div>
                                    <div class="summary-label">Total a Pagar</div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="total_price" id="totalAmountInput" value="{{ $purchase->details->sum(function($detail) { return $detail->quantity * $detail->product->purchase_price; }) }}">
                    </div>
                    
                    <!-- Sección de Acciones -->
                    <div class="panel-section">
                        <div class="section-header">
                            <h4 class="section-title">
                                <i class="fas fa-tasks"></i>
                                Acciones Disponibles
                            </h4>
                        </div>
                        <div class="action-buttons" style="display: flex !important; flex-direction: row !important; gap: 0.75rem; align-items: stretch;">
                            <button type="submit" class="action-btn primary-btn" id="updatePurchase" data-tooltip="Actualizar Compra" style="flex: 1 !important; width: auto !important;">
                                <div class="btn-content">
                                    <i class="fas fa-save"></i>
                                    <span class="btn-text">Actualizar</span>
                                </div>
                                <div class="btn-glow"></div>
                            </button>
                            
                            <button type="button" class="action-btn danger-btn" id="cancelEdit" data-tooltip="Cancelar Edición" style="flex: 1 !important; width: auto !important;">
                                <div class="btn-content">
                                    <i class="fas fa-times-circle"></i>
                                    <span class="btn-text">Cancelar</span>
                                </div>
                                <div class="btn-glow"></div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal de Búsqueda de Productos -->
    <div class="modal fade" id="searchProductModal" tabindex="-1" role="dialog" aria-labelledby="searchProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <div class="modal-title-section">
                        <div class="modal-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="modal-title-content">
                            <h5 class="modal-title" id="searchProductModalLabel">Búsqueda de Productos</h5>
                            <p class="modal-subtitle">Seleccione los productos para agregar a la compra</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-container">
                        <table id="productsTable" class="modern-table">
                            <thead>
                                <tr>
                                    <th class="th-code">Código</th>
                                    <th class="th-action">Acción</th>
                                    <th class="th-product">Producto</th>
                                    <th class="th-category">Categoría</th>
                                    <th class="th-stock">Stock</th>
                                    <th class="th-price">Precio Compra</th>
                                    <th class="th-status">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="td-code">
                                            <span class="product-code">{{ $product->code }}</span>
                                        </td>
                                        <td class="td-action">
                                            <button type="button" class="action-btn select-btn select-product"
                                                data-code="{{ $product->code }}" 
                                                data-id="{{ $product->id }}" 
                                                data-dismiss="modal">
                                                <div class="btn-content">
                                                <i class="fas fa-plus-circle"></i>
                                                    <span>Agregar</span>
                                                </div>
                                                <div class="btn-glow"></div>
                                            </button>
                                        </td>
                                        <td class="td-product">
                                            <div class="product-info">
                                                <img src="{{ $product->image_url }}"
                                                    alt="{{ $product->name }}" class="product-thumbnail">
                                                <div class="product-details">
                                                    <div class="product-name">{{ $product->name }}</div>
                                                    <div class="product-code">{{ $product->code }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="td-category">
                                            <span class="category-badge">{{ $product->category->name }}</span>
                                        </td>
                                        <td class="td-stock">
                                            <span class="stock-badge badge badge-{{ $product->stock_status_class }}">
                                                {{ $product->stock }}
                                            </span>
                                        </td>
                                        <td class="td-price">
                                            <span class="price-text">{{$currency->symbol}} {{ number_format($product->purchase_price, 2) }}</span>
                                        </td>
                                        <td class="td-status">
                                            <span class="status-badge badge badge-{{ $product->stock_status_label === 'Bajo' ? 'danger' : ($product->stock_status_label === 'Normal' ? 'warning' : 'success') }}">
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
                    <button type="button" class="modal-btn close-btn" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        <span>Cerrar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/responsive.bootstrap4.min.css') }}">
    <style>
        /* Variables CSS */
        :root {
            --primary-color: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #3730a3;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Header Moderno */
        .modern-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .modern-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .icon-wrapper {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .icon-wrapper i {
            font-size: 1.5rem;
            color: white;
        }

        .title-text {
            color: white;
        }

        .main-title {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0.25rem 0 0 0;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .action-btn {
            position: relative;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            text-decoration: none;
            color: white;
            font-weight: 600;
            transition: var(--transition);
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
            text-decoration: none;
        }

        .back-btn {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .back-btn:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.2) 100%);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }

        .btn-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            z-index: 1;
            justify-content: center;
            width: 100%;
        }

        .btn-glow {
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }

        .action-btn:hover .btn-glow {
            left: 100%;
        }

        .header-decoration {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        }

        .decoration-dots {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .decoration-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            animation: pulse 2s infinite;
        }

        .decoration-dots span:nth-child(2) {
            animation-delay: 0.5s;
        }

        .decoration-dots span:nth-child(3) {
            animation-delay: 1s;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        /* Alertas Modernas */
        .modern-alert {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            padding: 1rem;
        }

        .alert-content {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .alert-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .alert-success .alert-icon {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .alert-danger .alert-icon {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .alert-text {
            flex: 1;
        }

        .error-list {
            margin: 0.5rem 0 0 0;
            padding-left: 1.5rem;
        }

        .alert-close {
            background: none;
            border: none;
            color: inherit;
            font-size: 1.25rem;
            padding: 0.25rem;
            border-radius: 50%;
            transition: var(--transition);
            cursor: pointer;
        }

        .alert-close:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        /* Panel Principal */
        .main-panel {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }

        .panel-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .title-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .panel-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }

        .panel-subtitle {
            font-size: 0.8rem;
            color: #6b7280;
            margin: 0.125rem 0 0 0;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .counter-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .panel-toggle {
            background: none;
            border: none;
            color: #6b7280;
            padding: 0.5rem;
            border-radius: 50%;
            transition: var(--transition);
            cursor: pointer;
        }

        .panel-toggle:hover {
            background: #f3f4f6;
            color: var(--dark-color);
        }

        .panel-body {
            padding: 1rem;
        }

        /* Formulario Moderno */
        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        .form-section {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 1rem;
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
        .main-panel {
            max-width: 100%;
            margin: 0 auto;
        }
        
        /* Responsive para pantallas extra anchas */
        @media (min-width: 1400px) {
            .main-panel {
                max-width: 95%;
            }
            
            .panel-body {
                padding: 1.5rem 2rem;
            }
        }
        
        /* Responsive para pantallas anchas */
        @media (min-width: 1200px) and (max-width: 1399px) {
            .main-panel {
                max-width: 98%;
            }
        }
        
        /* Responsive para pantallas medianas */
        @media (min-width: 769px) and (max-width: 1199px) {
            .input-group-modern {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .input-actions {
                justify-content: stretch;
                gap: 0.5rem;
            }
            
            .search-btn, .new-btn {
                flex: 1;
                justify-content: center;
                padding: 0.625rem 0.75rem;
                font-size: 0.8rem;
            }
            
            .search-btn span, .new-btn span {
                font-size: 0.8rem;
            }
            
            .form-control-modern {
                min-height: 44px;
            }
            
            /* Mejorar espaciado en pantallas medianas */
            .form-group-modern {
                margin-bottom: 1.25rem;
            }
            
            .panel-body {
                padding: 1.25rem;
            }
            
            /* Ajustar tamaños de fuente para mejor legibilidad */
            .form-label {
                font-size: 0.875rem;
                margin-bottom: 0.625rem;
            }
            
            .form-control-modern {
                font-size: 0.875rem;
                padding: 0.75rem 0.875rem;
            }
            
            .form-help {
                font-size: 0.8rem;
                margin-top: 0.375rem;
            }
        }
        
        /* Responsive para pantallas pequeñas */
        @media (max-width: 768px) {
            .col-lg-3, .col-xl-2, .col-xl-4, .col-xl-5, .col-xl-7 {
                margin-bottom: 1rem;
            }
            
            .input-group-modern {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .input-actions {
                justify-content: stretch;
            }
            
            .search-btn, .new-btn {
                flex: 1;
                justify-content: center;
            }
        }
        
        /* Ajustes para campos de fecha y hora */
        input[type="date"], input[type="time"] {
            min-width: 120px;
        }
        
        /* Mejoras específicas para campos de fecha y hora en pantallas medianas */
        @media (min-width: 769px) and (max-width: 1199px) {
            input[type="date"], input[type="time"] {
                min-width: 140px;
                font-size: 0.875rem;
            }
            
            /* Asegurar que los campos de fecha y hora tengan el mismo tamaño */
            .col-md-6 .form-control-modern {
                width: 100%;
                min-width: 140px;
            }
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.75rem;
        }

        .form-label.required::after {
            content: " *";
            color: var(--danger-color);
        }

        .form-label i {
            color: var(--primary-color);
        }

        .input-group-modern {
            position: relative;
            display: flex;
            gap: 0.5rem;
        }

        .form-control-modern {
            flex: 1;
            padding: 0.75rem 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            transition: var(--transition);
            background: white;
        }

        .form-control-modern:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .input-actions {
            display: flex;
            gap: 0.5rem;
        }

        .search-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
            font-size: 0.85rem;
        }

        .new-btn {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
        }

        .search-btn:hover, .new-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
            text-decoration: none;
        }

        .form-help {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }

        .form-help i {
            color: var(--warning-color);
        }

        /* Tabla Moderna */
        .table-container {
            overflow-x: auto;
            border-radius: var(--border-radius);
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) #f1f5f9;
            position: relative;
        }



        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        .modern-table {
            width: 100%;
            min-width: 800px;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .modern-table thead {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        }

        .modern-table th {
            padding: 0.75rem;
            color: white;
            font-weight: 600;
            text-align: left;
            border: none;
            position: relative;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .modern-table th:first-child {
            border-top-left-radius: var(--border-radius);
        }

        .modern-table th:last-child {
            border-top-right-radius: var(--border-radius);
        }

        .modern-table tbody tr {
            transition: var(--transition);
            border-bottom: 1px solid #f3f4f6;
        }

        .modern-table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }

        .modern-table td {
            padding: 0.75rem;
            vertical-align: middle;
            border: none;
            white-space: nowrap;
        }

        /* Ancho de columnas específicas */
        .th-product {
            min-width: 200px;
            width: 25%;
        }

        .th-stock {
            min-width: 80px;
            width: 10%;
        }

        .th-quantity {
            min-width: 100px;
            width: 12%;
        }

        .th-price {
            min-width: 120px;
            width: 15%;
        }

        .th-subtotal {
            min-width: 100px;
            width: 12%;
        }

        .th-actions {
            min-width: 80px;
            width: 8%;
        }

        /* Estado Vacío */
        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
        }

        .empty-icon {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 0.375rem;
        }

        .empty-description {
            color: #9ca3af;
            max-width: 350px;
            margin: 0 auto;
            font-size: 0.875rem;
        }

        /* Panel Lateral Unificado */
        .sidebar-panel {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid #e5e7eb;
            height: fit-content;
            max-height: calc(100vh - 250px);
        }

        /* Secciones del Panel */
        .panel-section {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .panel-section:last-child {
            border-bottom: none;
        }

        .section-header {
            margin-bottom: 0.75rem;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }

        .section-title i {
            color: var(--primary-color);
        }

        .summary-stats {
            display: flex;
            flex-direction: row;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .summary-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: var(--border-radius);
            border: 1px solid #e5e7eb;
            flex: 1;
            min-width: 120px;
        }

        .summary-item.total {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
            border: none;
            flex: 2;
            min-width: 200px;
        }

        .summary-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: white;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .summary-item.total .summary-icon {
            background: rgba(255, 255, 255, 0.2);
        }

        .summary-content {
            flex: 1;
        }

        .summary-value {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 0.125rem;
            line-height: 1.2;
        }

        .summary-label {
            font-size: 0.75rem;
            font-weight: 500;
            opacity: 0.8;
            line-height: 1.2;
        }

        .summary-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
            margin: 0.75rem 0;
        }

        .total-amount {
            font-size: 1.375rem;
        }

        /* Botones de Acción */
        .action-buttons {
            display: flex !important;
            flex-direction: row !important;
            gap: 0.75rem;
            align-items: stretch;
            flex-wrap: nowrap;
        }

        .action-btn {
            position: relative;
            padding: 0.625rem 0.875rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
            overflow: hidden;
            cursor: pointer;
            text-decoration: none;
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            flex: 1 !important;
            min-width: 0;
            height: auto;
            width: auto !important;
        }

        .primary-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
        }

        .danger-btn {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
            text-decoration: none;
        }

        /* Producto en tabla */
        .product-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .product-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }

        .product-code {
            font-size: 0.875rem;
            color: #6b7280;
            font-family: 'Courier New', monospace;
        }

        /* Controles de cantidad y precio */
        .quantity-control {
            max-width: 80px;
        }
        
        .price-control {
            max-width: 120px;
        }

        .quantity-control .form-control, .price-control .form-control {
            text-align: center;
            font-weight: 600;
            padding: 0.5rem;
            border: 2px solid #e5e7eb;
            border-radius: var(--border-radius);
        }

        .price-control .input-group-text {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
            border: 2px solid var(--success-color);
            font-weight: 600;
        }

        /* Badges */
        .stock-badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
        }

        .subtotal-text {
            font-weight: 600;
            color: var(--success-color);
        }

        /* Modal Moderno */
        .modern-modal {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .modern-modal .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }

        .modal-title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .modal-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .modal-icon i {
            font-size: 1.25rem;
            color: white;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .modal-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            margin: 0.25rem 0 0 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.25rem;
            padding: 0.5rem;
            border-radius: 50%;
            transition: var(--transition);
            cursor: pointer;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .modern-modal .modal-body {
            padding: 1.5rem;
        }

        .product-thumbnail {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            object-fit: cover;
        }

        .category-badge {
            background: #f3f4f6;
            color: #374151;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .price-text {
            font-weight: 600;
            color: var(--success-color);
        }

        .select-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
        }

        .select-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            color: white;
        }

        .modern-modal .modal-footer {
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            padding: 1rem 1.5rem;
        }

        .modal-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
        }

        .close-btn {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
        }

        .close-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            color: white;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar-panel {
                margin-top: 1rem;
            }

            .summary-stats {
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            .summary-item {
                flex: 1;
                min-width: 100px;
            }

            .summary-item.total {
                flex: 1 1 100%;
                min-width: 100%;
            }

            .action-buttons {
                gap: 0.625rem;
                align-items: stretch;
                flex-direction: row !important;
                flex-wrap: nowrap;
            }
        }

        @media (max-width: 768px) {
            .modern-header {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .header-content {
                flex-direction: column;
                gap: 0.75rem;
                align-items: stretch;
            }

            .title-section {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .icon-wrapper {
                width: 50px;
                height: 50px;
            }

            .icon-wrapper i {
                font-size: 1.25rem;
            }

            .main-title {
                font-size: 1.5rem;
            }

            .subtitle {
                font-size: 0.875rem;
            }

            .header-actions {
                justify-content: center;
            }

            .action-btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .input-group-modern {
                flex-direction: column;
                gap: 0.5rem;
            }

            .input-actions {
                justify-content: stretch;
            }

            .search-btn, .new-btn {
                flex: 1;
                justify-content: center;
            }

            .sidebar-panel {
                position: static;
                margin-top: 1rem;
                max-height: none;
                overflow-y: visible;
                border-radius: var(--border-radius);
            }

            .panel-header {
                padding: 1rem;
            }

            .title-section {
                flex-direction: row;
                align-items: center;
                gap: 0.75rem;
            }

            .title-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .panel-title {
                font-size: 1.125rem;
                margin-bottom: 0.125rem;
            }

            .panel-subtitle {
                font-size: 0.8rem;
            }

            .action-buttons {
                flex-direction: row !important;
                gap: 0.5rem;
                align-items: stretch;
                flex-wrap: nowrap;
            }

            .action-btn {
                flex: 1;
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
                min-width: 0;
                position: relative;
                min-height: 44px;
            }

            .btn-text {
                display: none;
            }

            .action-btn i {
                font-size: 1rem;
            }

            .action-btn:hover::after {
                content: attr(data-tooltip);
                position: absolute;
                bottom: -2rem;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 0.25rem 0.5rem;
                border-radius: 0.25rem;
                font-size: 0.7rem;
                white-space: nowrap;
                z-index: 1000;
            }

            .btn-content {
                justify-content: center;
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .sidebar-panel {
                margin-top: 0.75rem;
            }

            .panel-section {
                padding: 0.75rem;
            }

            .summary-stats {
                gap: 0.25rem;
                flex-wrap: wrap;
            }

            .summary-item {
                flex: 1;
                min-width: 70px;
                padding: 0.375rem;
                gap: 0.5rem;
                justify-content: space-between;
            }

            .summary-item.total {
                flex: 1 1 100%;
                min-width: 100%;
            }

            .summary-icon {
                width: 25px;
                height: 25px;
                font-size: 0.625rem;
            }

            .summary-value {
                font-size: 0.9rem;
            }

            .summary-label {
                font-size: 0.65rem;
            }

            .total-amount {
                font-size: 1.125rem;
            }

            .summary-content {
                text-align: right;
            }

            .summary-item.total .summary-content {
                text-align: right;
            }

            .action-buttons {
                gap: 0.375rem;
                align-items: stretch;
                flex-direction: row !important;
                flex-wrap: nowrap;
            }

            .action-btn {
                padding: 0.625rem 0.375rem;
                font-size: 0.75rem;
                flex: 1;
                min-height: 40px;
            }

            .section-title {
                font-size: 0.9rem;
                gap: 0.375rem;
            }

            .section-title i {
                font-size: 0.875rem;
            }
            }

            .summary-item.total {
                flex-direction: row;
                align-items: center;
            }

            .summary-icon {
                width: 30px;
                height: 30px;
                font-size: 0.75rem;
            }

            .summary-value {
                font-size: 1.125rem;
            }

            .summary-label {
                font-size: 0.75rem;
            }

            .modal-title-section {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .modal-title {
                font-size: 1.25rem;
            }

            .modal-subtitle {
                font-size: 0.8rem;
            }

            /* Tabla responsiva mejorada */
            .table-container {
                margin: 0 -0.5rem;
                border-radius: 0;
            }



            .modern-table {
                font-size: 0.8rem;
                min-width: 600px;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.5rem 0.375rem;
            }

            .th-product {
                min-width: 150px;
            }

            .th-stock {
                min-width: 60px;
            }

            .th-quantity {
                min-width: 80px;
            }

            .th-price {
                min-width: 100px;
            }

            .th-subtotal {
                min-width: 80px;
            }

            .th-actions {
                min-width: 60px;
            }

            .product-item {
                flex-direction: column;
                gap: 0.25rem;
                text-align: center;
                min-width: 120px;
            }

            .product-image {
                width: 35px;
                height: 35px;
            }

            .product-name {
                font-size: 0.75rem;
                line-height: 1.2;
            }

            .product-code {
                font-size: 0.65rem;
            }

            .quantity-control {
                min-width: 70px;
                max-width: 90px;
            }

            .price-control {
                min-width: 90px;
                max-width: 110px;
            }

            .quantity-control .form-control,
            .price-control .form-control {
                padding: 0.25rem;
                font-size: 0.75rem;
            }

            .price-control .input-group-text {
                padding: 0.25rem 0.375rem;
                font-size: 0.7rem;
            }

            .stock-badge {
                font-size: 0.7rem;
                padding: 0.25rem 0.5rem;
            }

            .subtotal-text {
                font-size: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .modern-header {
                padding: 0.75rem;
            }

            .main-title {
                font-size: 1.25rem;
            }

            .subtitle {
                font-size: 0.8rem;
            }

            .icon-wrapper {
                width: 40px;
                height: 40px;
            }

            .icon-wrapper i {
                font-size: 1rem;
            }

            .panel-header {
                padding: 0.75rem;
            }

            .panel-body {
                padding: 0.75rem;
            }

            .title-icon {
                width: 35px;
                height: 35px;
                font-size: 0.875rem;
            }

            .panel-title {
                font-size: 1.125rem;
            }

            .panel-subtitle {
                font-size: 0.75rem;
            }

            .form-group {
                margin-bottom: 0.75rem;
            }

            .form-label {
                font-size: 0.875rem;
                margin-bottom: 0.5rem;
            }

            .form-control-modern {
                padding: 0.625rem 0.75rem;
                font-size: 0.8rem;
            }

            .search-btn, .new-btn {
                padding: 0.625rem 0.75rem;
                font-size: 0.8rem;
            }

            .form-help {
                font-size: 0.75rem;
            }

            .action-buttons {
                gap: 0.375rem;
            }

            .action-btn {
                padding: 0.625rem 0.875rem;
                font-size: 0.8rem;
            }

            .summary-item {
                padding: 0.375rem;
                gap: 0.5rem;
            }

            .summary-icon {
                width: 30px;
                height: 30px;
                font-size: 0.75rem;
            }

            .summary-value {
                font-size: 1rem;
            }

            .summary-label {
                font-size: 0.7rem;
            }

            .total-amount {
                font-size: 1.25rem;
            }

            .empty-state {
                padding: 1.5rem 0.75rem;
            }

            .empty-icon {
                font-size: 2.5rem;
                margin-bottom: 0.75rem;
            }

            .empty-title {
                font-size: 1rem;
            }

            .empty-description {
                font-size: 0.8rem;
                max-width: 280px;
            }

            /* Tabla responsiva para móviles */
            .table-container {
                margin: 0 -0.75rem;
                border-radius: 0;
            }

            .modern-table {
                min-width: 500px;
                font-size: 0.75rem;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.375rem 0.25rem;
            }

            .th-product {
                min-width: 120px;
            }

            .th-stock {
                min-width: 50px;
            }

            .th-quantity {
                min-width: 70px;
            }

            .th-price {
                min-width: 90px;
            }

            .th-subtotal {
                min-width: 70px;
            }

            .th-actions {
                min-width: 50px;
            }

            .product-item {
                flex-direction: column;
                gap: 0.125rem;
                text-align: center;
                min-width: 100px;
            }

            .product-image {
                width: 30px;
                height: 30px;
            }

            .product-name {
                font-size: 0.7rem;
                line-height: 1.1;
            }

            .product-code {
                font-size: 0.6rem;
            }

            .quantity-control {
                min-width: 60px;
                max-width: 80px;
            }

            .price-control {
                min-width: 80px;
                max-width: 100px;
            }

            .quantity-control .form-control,
            .price-control .form-control {
                padding: 0.125rem;
                font-size: 0.7rem;
            }

            .price-control .input-group-text {
                padding: 0.125rem 0.25rem;
                font-size: 0.65rem;
            }

            .stock-badge {
                font-size: 0.65rem;
                padding: 0.125rem 0.375rem;
            }

            .subtotal-text {
                font-size: 0.7rem;
            }
        }

        @media (max-width: 480px) {
            .modern-header {
                padding: 0.5rem;
            }

            .main-title {
                font-size: 1.125rem;
            }

            .subtitle {
                font-size: 0.75rem;
            }

            .icon-wrapper {
                width: 35px;
                height: 35px;
            }

            .icon-wrapper i {
                font-size: 0.875rem;
            }

            .action-btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.75rem;
            }

            .btn-text {
                font-size: 0.75rem;
            }

            .panel-section {
                padding: 0.5rem;
            }

            .summary-item {
                padding: 0.25rem;
            }

            .summary-icon {
                width: 25px;
                height: 25px;
                font-size: 0.625rem;
            }

            .summary-value {
                font-size: 0.875rem;
            }

            .summary-label {
                font-size: 0.65rem;
            }

            .total-amount {
                font-size: 1.125rem;
            }

            .modern-table {
                font-size: 0.7rem;
                min-width: 400px;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.25rem;
            }

            .product-image {
                width: 30px;
                height: 30px;
            }

            .quantity-control {
                min-width: 60px;
                max-width: 80px;
            }

            .price-control {
                min-width: 80px;
                max-width: 100px;
            }

            .quantity-control .form-control,
            .price-control .form-control {
                padding: 0.125rem;
                font-size: 0.7rem;
            }

            .price-control .input-group-text {
                padding: 0.125rem 0.25rem;
                font-size: 0.65rem;
            }
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .main-panel, .summary-panel, .actions-panel {
            animation: fadeInUp 0.6s ease-out;
        }

        .main-panel:nth-child(1) { animation-delay: 0.1s; }
        .main-panel:nth-child(2) { animation-delay: 0.2s; }

        /* DataTables personalización */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            color: #6b7280;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--primary-light) !important;
            border-color: var(--primary-light) !important;
            color: white !important;
        }

        /* Responsive para DataTables */
        @media (max-width: 768px) {
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                text-align: center;
                margin-bottom: 0.5rem;
            }

            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                text-align: center;
                margin-top: 0.5rem;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                font-size: 0.8rem;
            }

            .dataTables_wrapper .dataTables_info {
                font-size: 0.75rem;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.125rem 0.375rem;
                font-size: 0.75rem;
            }
        }

        /* Responsive para botones */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .action-btn {
                flex: none;
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }
            
            .btn-text {
                font-size: 0.875rem;
            }
        }

        @media (max-width: 480px) {
            .action-buttons {
                gap: 0.375rem;
            }
            
            .action-btn {
                padding: 0.625rem 0.875rem;
                font-size: 0.8rem;
            }
            
            .btn-text {
                font-size: 0.8rem;
            }
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Cargar todas las librerías necesarias
            loadDataTables(function() {

                // Inicializar los subtotales y el total al cargar la página
                updateAllTotals();

                // Inicializar DataTable para el modal de búsqueda
                $('#productsTable').DataTable({
                    responsive: true,
                    scrollX: true,
                    autoWidth: false,
                    language: {
                        "emptyTable": "No hay información",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ productos",
                        "infoEmpty": "Mostrando 0 a 0 de 0 productos",
                        "infoFiltered": "(filtrado de _MAX_ productos totales)",
                        "lengthMenu": "Mostrar _MENU_ productos",
                        "loadingRecords": "Cargando...",
                        "processing": "Procesando...",
                        "search": "Buscar:",
                        "zeroRecords": "No se encontraron coincidencias",
                        "paginate": {
                            "first": "Primero",
                            "last": "Último",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        }
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

                // Manejar la entrada de código de producto
                $('#product_code').keypress(function(e) {
                    if (e.which == 13) { // Si presiona Enter
                        e.preventDefault();
                        const productCode = $(this).val().trim();

                        if (productCode) {
                            // Verificar si el producto ya está en la tabla
                            if ($(`tr[data-product-code="${productCode}"]`).length > 0) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Producto ya agregado',
                                    text: 'Este producto ya está en la lista de compra',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
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
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: response.message,
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se encontró el producto',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        });
                    }
                }
            });

            // Evento para el botón de seleccionar producto del modal
            $(document).on('click', '.select-product', function() {
                const productCode = $(this).data('code');
                const productId = $(this).data('id');

                // Verificar si el producto ya está en la tabla
                if ($(`tr[data-product-code="${productCode}"]`).length > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Producto ya agregado',
                        text: 'Este producto ya está en la lista de compra',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }

                // Obtener detalles del producto y agregarlo a la tabla
                $.ajax({
                    url: `/purchases/product-details/${productCode}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            response.product.id = productId;
                            addProductToTable(response.product);
                            
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
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo obtener la información del producto',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            });

            // Función para agregar producto a la tabla
            function addProductToTable(product) {
                const imageUrl = product.image || '/img/no-image.png';
                const price = product.purchase_price || product.price || 0;
                const stockClass = product.stock > 10 ? 'success' : (product.stock > 0 ? 'warning' : 'danger');

                const row = `
                    <tr data-product-code="${product.code}" data-product-id="${product.id}" class="fade-in-up">
                        <td>
                            <div class="product-item">
                                <img src="${imageUrl}" alt="${product.name}" class="product-image">
                                <div class="product-info">
                                    <div class="product-name">${product.name}</div>
                                    <div class="product-code">${product.code}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="stock-badge badge badge-${stockClass}">${product.stock}</span>
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control quantity-control quantity-input" 
                                   name="items[${product.id}][quantity]" 
                                   value="1" 
                                   min="1"
                                   style="text-align: center;">
                        </td>
                        <td class="text-center">
                            <div class="input-group price-control">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" 
                                       class="form-control price-input" 
                                       name="items[${product.id}][price]" 
                                       value="${price}" 
                                       step="0.01">
                            </div>
                        </td>
                        <td class="text-right">
                            <span class="subtotal-text">{{ $currency->symbol }} <span class="subtotal">${price.toFixed(2)}</span></span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#purchaseItems').append(row);
                updateAllTotals();
                checkEmptyState();
            }

            // Actualizar subtotal cuando cambie cantidad o precio
            $(document).on('input', '.quantity-input, .price-input', function() {
                updateRowTotal($(this).closest('tr'));
                updateAllTotals();
            });

            // Eliminar producto de la tabla
            $(document).on('click', '.remove-item', function() {
                const row = $(this).closest('tr');
                const productName = row.find('.product-name').text();
                
                Swal.fire({
                    title: '¿Eliminar producto?',
                    text: `¿Está seguro de que desea eliminar "${productName}" de la compra?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        row.fadeOut(300, function() {
                            $(this).remove();
                            updateAllTotals();
                            checkEmptyState();
                        });
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Producto eliminado',
                            text: 'El producto se eliminó de la lista de compra',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            });

                // Función para actualizar el total de una fila
                function updateRowTotal(row) {
                    const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
                    const price = parseFloat(row.find('.price-input').val()) || 0;
                    const subtotal = quantity * price;
                    row.find('.subtotal').text(subtotal.toFixed(2));
                }

                // Función para actualizar todos los totales
                function updateAllTotals() {
                    let total = 0;
                    let totalProducts = 0;
                    let totalQuantity = 0;

                    $('#purchaseItems tr').each(function() {
                        const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
                        const price = parseFloat($(this).find('.price-input').val()) || 0;
                        const subtotal = quantity * price;
                        
                        $(this).find('.subtotal').text(subtotal.toFixed(2));
                        total += subtotal;
                        totalProducts++;
                        totalQuantity += quantity;
                    });

                    // Actualizar totales en el panel lateral
                    $('#totalAmountDisplay').text('{{ $currency->symbol }} ' + total.toFixed(2));
                    $('#totalAmountInput').val(total.toFixed(2));
                    $('#totalProducts').text(totalProducts);
                    $('#totalQuantity').text(totalQuantity);
                    $('#productCount').text(totalProducts + ' productos');
                }

                // Función para verificar estado vacío
                function checkEmptyState() {
                    const hasProducts = $('#purchaseItems tr').length > 0;
                    
                    if (hasProducts) {
                        $('.table-container').show();
                        $('#emptyState').hide();
                    } else {
                        $('.table-container').hide();
                        $('#emptyState').show();
                    }
                }

                // Botón cancelar edición
                $('#cancelEdit').click(function() {
                    Swal.fire({
                        title: '¿Cancelar edición?',
                        text: '¿Está seguro de que desea cancelar la edición? Los cambios no guardados se perderán.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, cancelar',
                        cancelButtonText: 'Continuar editando'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.history.back();
                        }
                    });
                });

                // Validación del formulario antes de enviar
                $('#purchaseForm').submit(function(e) {
                    const productCount = $('#purchaseItems tr').length;
                    
                    if (productCount === 0) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Compra vacía',
                            text: 'Debe agregar al menos un producto a la compra antes de actualizar.',
                            confirmButtonColor: '#4f46e5'
                        });
                        return false;
                    }

                    // Mostrar loading
                    Swal.fire({
                        title: 'Actualizando compra...',
                        text: 'Por favor espere mientras se procesa la información',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                });

                // Inicializar estado vacío
                checkEmptyState();
            });
            

        });
    </script>
@stop
