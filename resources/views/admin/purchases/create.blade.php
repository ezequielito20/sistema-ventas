@extends('adminlte::page')

@section('title', 'Nueva Compra')

@section('content_header')
    <div class="modern-header">
        <div class="header-content">
            <div class="header-left">
                <div class="title-section">
                    <div class="icon-wrapper">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="title-text">
                        <h1 class="main-title">Nueva Compra</h1>
                        <p class="subtitle">Registre una nueva compra de productos al inventario</p>
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

    <form action="{{ route('admin.purchases.store') }}" method="POST" enctype="multipart/form-data" id="purchaseForm">
        @csrf
        
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
                        <div class="form-grid">
                            <!-- Código de Producto -->
                            <div class="form-section">
                                <div class="form-group">
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

                            <!-- Fecha de compra -->
                            <div class="form-section">
                                <div class="form-group">
                                    <label for="purchase_date" class="form-label required">
                                        <i class="fas fa-calendar"></i>
                                        Fecha de Compra
                                    </label>
                                    <input type="date" name="purchase_date" id="purchase_date"
                                        class="form-control-modern @error('purchase_date') is-invalid @enderror"
                                        value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                                    @error('purchase_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                    <span class="counter-badge" id="productCount">0 productos</span>
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
                                        <th class="th-quantity">Cantidad</th>
                                        <th class="th-price">Precio Unit.</th>
                                        <th class="th-subtotal">Subtotal</th>
                                        <th class="th-actions">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="purchaseItems">
                                    <!-- Los items se agregarán dinámicamente aquí -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Estado vacío -->
                        <div id="emptyState" class="empty-state">
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
                                    <div class="summary-value" id="totalProducts">0</div>
                                    <div class="summary-label">Productos Únicos</div>
                                </div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon">
                                    <i class="fas fa-cubes"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-value" id="totalQuantity">0</div>
                                    <div class="summary-label">Cantidad Total</div>
                                </div>
                            </div>
                            
                            <div class="summary-divider"></div>
                            
                            <div class="summary-item total">
                                <div class="summary-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-value total-amount" id="totalAmountDisplay">{{$currency->symbol}} 0.00</div>
                                    <div class="summary-label">Total a Pagar</div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="total_price" id="totalAmountInput" value="0">
                    </div>
                    
                    <!-- Sección de Acciones -->
                    <div class="panel-section">
                        <div class="section-header">
                            <h4 class="section-title">
                                <i class="fas fa-tasks"></i>
                                Acciones Disponibles
                            </h4>
                        </div>
                        <div class="action-buttons">
                            <button type="submit" class="action-btn primary-btn" id="submitPurchase">
                                <div class="btn-content">
                                    <i class="fas fa-save"></i>
                                    <span class="btn-text">Guardar</span>
                                </div>
                                <div class="btn-glow"></div>
                            </button>
                            
                            <button type="submit" class="action-btn success-btn" name="action" value="save_and_new">
                                <div class="btn-content">
                                    <i class="fas fa-plus-circle"></i>
                                    <span class="btn-text">+ Nueva</span>
                                </div>
                                <div class="btn-glow"></div>
                            </button>
                            
                            <button type="button" class="action-btn danger-btn" id="cancelPurchase">
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        }

        .btn-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            z-index: 1;
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
            overflow: hidden;
            border-radius: var(--border-radius);
        }

        .modern-table {
            width: 100%;
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

        .sidebar-panel::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-panel::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .sidebar-panel::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .sidebar-panel::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
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
            flex-direction: column;
            gap: 0.75rem;
        }

        .summary-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: var(--border-radius);
            border: 1px solid #e5e7eb;
        }

        .summary-item.total {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
            border: none;
        }

        .summary-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: white;
            font-size: 1rem;
        }

        .summary-item.total .summary-icon {
            background: rgba(255, 255, 255, 0.2);
        }

        .summary-content {
            flex: 1;
        }

        .summary-value {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.125rem;
        }

        .summary-label {
            font-size: 0.8rem;
            font-weight: 500;
            opacity: 0.8;
        }

        .summary-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
            margin: 0.75rem 0;
        }

        .total-amount {
            font-size: 1.5rem;
        }

        /* Botones de Acción */
        .action-buttons {
            display: flex;
            flex-direction: row;
            gap: 0.5rem;
            flex-wrap: wrap;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            flex: 1;
            min-width: 0;
        }

        .primary-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
        }

        .success-btn {
            background: linear-gradient(135deg, var(--success-color), #059669);
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

        /* Responsive para botones */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                gap: 0.375rem;
            }
            
            .action-btn {
                flex: none;
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }
            
            .btn-text {
                font-size: 0.8rem;
            }
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
        .quantity-control, .price-control {
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
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .header-actions {
                justify-content: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .sidebar-panel {
                position: static;
                margin-top: 1rem;
                max-height: none;
                overflow-y: visible;
            }

            .action-buttons {
                gap: 0.5rem;
            }

            .action-btn {
                padding: 0.625rem 0.875rem;
                font-size: 0.85rem;
            }

            .modal-title-section {
                flex-direction: column;
                text-align: center;
            }

            .modal-title {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 576px) {
            .modern-header {
                padding: 1.5rem;
            }

            .main-title {
                font-size: 1.5rem;
            }

            .title-section {
                flex-direction: column;
                text-align: center;
            }

            .action-buttons {
                gap: 0.375rem;
            }

            .action-btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }

            .summary-item {
                padding: 0.75rem;
            }

            .summary-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .summary-value {
                font-size: 1.25rem;
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
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#productsTable').DataTable({
                responsive: true,
                scrollX: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                columnDefs: [{
                        responsivePriority: 1,
                        targets: [0, 1, 2]
                    },
                    {
                        responsivePriority: 2,
                        targets: [4, 5]
                    },
                    {
                        responsivePriority: 3,
                        targets: '_all'
                    }
                ]
            });
            
            // Verificar producto único después de inicializar DataTable
            setTimeout(function() {
                checkAndAddSingleProduct();
            }, 100);
            
            function checkAndAddSingleProduct() {
                const availableProducts = $('#productsTable tbody tr').length;
                console.log('Productos disponibles:', availableProducts);
                
                if (availableProducts === 1) {
                    const productRow = $('#productsTable tbody tr:first');
                    const productCode = productRow.find('td:eq(0)').text().trim();
                    const productId = productRow.find('button.select-product').data('id');
                    const productName = productRow.find('td:eq(2) .product-name').text().trim();
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
                
                    if (!productId || productId === '' || productId === null) {
                        console.error('ID de producto no válido:', productId);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo obtener el ID del producto'
                        });
                        return;
                    }
                
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
                        addProductToTable(product, false);
                        $('#purchase_date').focus();
                    }, 500);
                } else if (availableProducts === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin productos disponibles',
                        text: 'No hay productos disponibles en el inventario para realizar compras',
                        confirmButtonText: 'Entendido'
                    });
                }
            }

            // Manejar adición de productos
            $('#addProduct').click(function() {
                const productCode = $('#product_code').val();
                if (productCode) {
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

            // Manejar entrada de código
            $('#product_code').keypress(function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    const productCode = $(this).val();

                    if (productCode) {
                        if ($(`tr[data-product-code="${productCode}"]`).length > 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Producto ya agregado',
                                text: 'Este producto ya está en la lista de compra'
                            });
                            return;
                        }

                        $.ajax({
                            url: `/purchases/product-by-code/${productCode}`,
                            method: 'GET',
                            success: function(response) {
                                if (response.success) {
                                    console.log('Producto encontrado por código:', response.product);
                                    addProductToTable(response.product);
                                    $('#product_code').val('');
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

            // Evento para seleccionar producto
            $(document).on('click', '.select-product', function() {
                const productCode = $(this).data('code');
                const productId = $(this).data('id');
                console.log('Código del producto:', productCode, 'ID:', productId);

                if ($(`tr[data-product-code="${productCode}"]`).length > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Producto ya agregado',
                        text: 'Este producto ya está en la lista de compra'
                    });
                    return;
                }

                $.ajax({
                    url: `/purchases/product-details/${productCode}`,
                    method: 'GET',
                    success: function(response) {
                        console.log('Respuesta del servidor:', response);
                        if (response.success) {
                            response.product.id = productId;
                            addProductToTable(response.product);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la petición:', error);
                        Swal.fire('Error', 'No se pudo obtener la información del producto', 'error');
                    }
                });
            });

            // Función para agregar producto a la tabla
            function addProductToTable(product, showAlert = true) {
                console.log('Agregando producto a la tabla:', product);
                
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

                let imageUrl = product.image;
                if (!imageUrl || imageUrl === '') {
                    imageUrl = '/img/no-image.png';
                } else if (!imageUrl.startsWith('http') && !imageUrl.startsWith('/')) {
                    imageUrl = '/' + imageUrl;
                }
                
                console.log('Imagen procesada:', imageUrl);

                const price = product.purchase_price || product.price || 0;

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
                            <span class="stock-badge badge badge-${product.stock < 10 ? 'danger' : (product.stock < 50 ? 'warning' : 'success')}">
                                ${product.stock}
                            </span>
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                class="form-control quantity-control quantity-input" 
                                name="items[${product.id}][quantity]" 
                                value="1" 
                                min="1">
                        </td>
                        <td class="text-center">
                            <div class="input-group price-control">
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
                            <span class="subtotal-text">{{$currency->symbol}} <span class="subtotal">${price}</span></span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#purchaseItems').append(row);
                updateTotal();
                updateEmptyState();

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

            // Actualizar subtotal
            $(document).on('input', '.quantity-input, .price-input', function() {
                const row = $(this).closest('tr');
                const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
                const price = parseFloat(row.find('.price-input').val()) || 0;
                const subtotal = quantity * price;
                row.find('.subtotal').text(subtotal.toFixed(2));
                updateTotal();
            });

            // Eliminar producto
            $(document).on('click', '.remove-item', function() {
                const row = $(this).closest('tr');
                row.fadeOut(300, function() {
                    $(this).remove();
                    updateTotal();
                    updateEmptyState();
                });
            });

            // Actualizar total general
            function updateTotal() {
                let total = 0;
                let totalProducts = 0;
                let totalQuantity = 0;
                
                $('.subtotal').each(function() {
                    total += parseFloat($(this).text()) || 0;
                });
                
                $('#purchaseItems tr').each(function() {
                    totalProducts++;
                    const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
                    totalQuantity += quantity;
                });
                
                $('#totalAmount').text(total.toFixed(2));
                $('#totalAmountInput').val(total.toFixed(2));
                $('#totalAmountDisplay').text('{{$currency->symbol}} ' + total.toFixed(2));
                $('#totalProducts').text(totalProducts);
                $('#totalQuantity').text(totalQuantity);
                $('#productCount').text(totalProducts + ' productos');
                
                console.log('Total actualizado:', total);
            }

            // Actualizar estado vacío
            function updateEmptyState() {
                const itemCount = $('#purchaseItems tr').length;
                if (itemCount === 0) {
                    $('#emptyState').show();
                } else {
                    $('#emptyState').hide();
                }
            }

            // Cancelar compra
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
                        window.history.back();
                    }
                });
            });

            // Envío del formulario
            $('form').on('submit', function(e) {
                e.preventDefault();
                
                if ($('#purchaseItems tr').length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin productos',
                        text: 'Debe agregar al menos un producto a la compra'
                    });
                    return;
                }

                if (!$('#purchase_date').val()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fecha requerida',
                        text: 'Debe seleccionar una fecha de compra'
                    });
                    return;
                }

                $('#submitPurchase, button[name="action"]').prop('disabled', true);

                this.submit();
            });

            // Inicializar estado vacío
            updateEmptyState();
        });
    </script>
@stop
