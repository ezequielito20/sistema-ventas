@extends('adminlte::page')

@section('title', 'Crear Producto')

@section('content_header')
    <div class="modern-header">
        <div class="header-content">
            <div class="title-section">
                <div class="icon-wrapper">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="title-text">
                    <h1 class="main-title">Crear Nuevo Producto</h1>
                    <p class="subtitle">Agrega un nuevo producto al inventario con toda la información necesaria</p>
                </div>
            </div>
            <div class="header-actions">
                <button onclick="window.history.back()" class="action-btn secondary-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver</span>
                </button>
            </div>
        </div>
        <div class="header-decoration"></div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf
        
        <div class="form-container">
            <!-- Información Básica -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="section-title">
                        <h3>Información Básica</h3>
                        <p>Datos principales del producto</p>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="code">Código del Producto</label>
                        <div class="input-wrapper">
                            <i class="fas fa-barcode input-icon"></i>
                            <input type="text" class="form-input @error('code') is-invalid @enderror"
                                id="code" name="code" value="{{ old('code') }}" placeholder="Ej: PROD001">
                        </div>
                        @error('code')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="name">Nombre del Producto</label>
                        <div class="input-wrapper">
                            <i class="fas fa-box input-icon"></i>
                            <input type="text" class="form-input @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name') }}" placeholder="Nombre del producto">
                        </div>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="category_id">Categoría</label>
                        <div class="input-wrapper">
                            <i class="fas fa-tag input-icon"></i>
                            <select class="form-input select2 @error('category_id') is-invalid @enderror"
                                id="category_id" name="category_id">
                                <option value="">Seleccionar categoría</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('category_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="entry_date">Fecha de Ingreso</label>
                        <div class="input-wrapper">
                            <i class="fas fa-calendar input-icon"></i>
                            <input type="date" class="form-input @error('entry_date') is-invalid @enderror"
                                id="entry_date" name="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}"
                                max="{{ date('Y-m-d') }}">
                        </div>
                        @error('entry_date')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="description">Descripción</label>
                    <div class="input-wrapper">
                        <i class="fas fa-align-left input-icon"></i>
                        <textarea class="form-input @error('description') is-invalid @enderror" 
                            id="description" name="description" rows="4" 
                            placeholder="Describe las características del producto...">{{ old('description') }}</textarea>
                    </div>
                    @error('description')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Imagen del Producto -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <div class="section-title">
                        <h3>Imagen del Producto</h3>
                        <p>Sube una imagen representativa</p>
                    </div>
                </div>
                
                <div class="image-upload-container">
                    <div class="image-preview" id="imagePreview">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Haz clic para seleccionar una imagen</p>
                            <span>JPG, PNG, GIF hasta 2MB</span>
                        </div>
                    </div>
                    
                    <div class="file-input-wrapper">
                        <input type="file" class="file-input @error('image') is-invalid @enderror"
                            id="image" name="image" accept="image/*">
                        <label for="image" class="file-label">
                            <i class="fas fa-camera"></i>
                            Seleccionar Imagen
                        </label>
                    </div>
                    
                    @error('image')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Gestión de Stock -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="section-title">
                        <h3>Gestión de Stock</h3>
                        <p>Controla el inventario del producto</p>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="stock">Stock Actual</label>
                        <div class="input-wrapper">
                            <i class="fas fa-cubes input-icon"></i>
                            <input type="number" class="form-input @error('stock') is-invalid @enderror"
                                id="stock" name="stock" value="{{ old('stock', 0) }}" min="0">
                        </div>
                        @error('stock')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="min_stock">Stock Mínimo</label>
                        <div class="input-wrapper">
                            <i class="fas fa-exclamation-triangle input-icon"></i>
                            <input type="number" class="form-input @error('min_stock') is-invalid @enderror"
                                id="min_stock" name="min_stock" value="{{ old('min_stock', 0) }}" min="0">
                        </div>
                        @error('min_stock')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="max_stock">Stock Máximo</label>
                        <div class="input-wrapper">
                            <i class="fas fa-warehouse input-icon"></i>
                            <input type="number" class="form-input @error('max_stock') is-invalid @enderror"
                                id="max_stock" name="max_stock" value="{{ old('max_stock', 0) }}" min="0">
                        </div>
                        @error('max_stock')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Precios -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="section-title">
                        <h3>Información de Precios</h3>
                        <p>Establece los precios de compra y venta</p>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="purchase_price">Precio de Compra</label>
                        <div class="input-wrapper">
                            <span class="currency-symbol">{{ $currency->symbol }}</span>
                            <input type="number" class="form-input @error('purchase_price') is-invalid @enderror"
                                id="purchase_price" name="purchase_price" value="{{ old('purchase_price', 0) }}" 
                                step="0.01" min="0">
                        </div>
                        @error('purchase_price')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sale_price">Precio de Venta</label>
                        <div class="input-wrapper">
                            <span class="currency-symbol">{{ $currency->symbol }}</span>
                            <input type="number" class="form-input @error('sale_price') is-invalid @enderror"
                                id="sale_price" name="sale_price" value="{{ old('sale_price', 0) }}" 
                                step="0.01" min="0">
                        </div>
                        @error('sale_price')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="profit-indicator" id="profitIndicator" style="display: none;">
                    <div class="profit-content">
                        <i class="fas fa-chart-line"></i>
                        <div class="profit-info">
                            <span class="profit-label">Margen de Beneficio:</span>
                            <span class="profit-value" id="profitValue">0%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="form-actions">
                <button type="submit" class="action-btn primary-btn">
                    <i class="fas fa-save"></i>
                    <span>Guardar Producto</span>
                </button>
                <a href="{{ route('admin.products.index') }}" class="action-btn secondary-btn">
                    <i class="fas fa-times"></i>
                    <span>Cancelar</span>
                </a>
            </div>
        </div>
    </form>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-light: #6366f1;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Header Moderno */
        .modern-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .icon-wrapper {
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

        .icon-wrapper i {
            font-size: 1.25rem;
            color: white;
        }

        .title-text {
            color: white;
        }

        .main-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            margin-bottom: 0.25rem;
        }

        .subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            min-width: 120px;
            justify-content: center;
        }

        .primary-btn {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            color: var(--primary-color);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .secondary-btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            color: inherit;
            text-decoration: none;
        }

        .header-decoration {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        }

        /* Contenedor del Formulario */
        .form-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Secciones del Formulario */
        .form-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid #e5e7eb;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .section-icon {
            width: 45px;
            height: 45px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
        }

        .section-title h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            margin-bottom: 0.25rem;
            color: var(--dark-color);
        }

        .section-title p {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        /* Grid del Formulario */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Grupos de Formulario */
        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        /* Inputs */
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            color: #9ca3af;
            z-index: 2;
            font-size: 0.875rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.5rem;
            border: 2px solid #e5e7eb;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            transition: var(--transition);
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-input.is-invalid {
            border-color: var(--danger-color);
        }

        .currency-symbol {
            position: absolute;
            left: 1rem;
            color: #6b7280;
            font-weight: 600;
            z-index: 2;
        }

        .form-input[type="number"] {
            padding-left: 2.5rem;
        }

        /* Select2 Personalizado */
        .select2-container--bootstrap4 .select2-selection--single {
            height: auto;
            padding: 0.875rem 1rem 0.875rem 2.5rem;
            border: 2px solid #e5e7eb;
            border-radius: var(--border-radius);
            background: white;
        }

        .select2-container--bootstrap4 .select2-selection--single:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Upload de Imagen */
        .image-upload-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .image-preview {
            width: 100%;
            height: 250px;
            border: 3px dashed #d1d5db;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
        }

        .image-preview:hover {
            border-color: var(--primary-color);
            background-color: #f8fafc;
        }

        .upload-placeholder {
            text-align: center;
            color: #6b7280;
        }

        .upload-placeholder i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .upload-placeholder p {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .upload-placeholder span {
            font-size: 0.875rem;
            opacity: 0.8;
        }

        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            padding: 1rem;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }

        .image-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
        }

        .image-info i {
            color: var(--success-color);
            font-size: 1.1rem;
        }

        .image-info span {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .file-input-wrapper {
            display: flex;
            justify-content: center;
        }

        .file-input {
            display: none;
        }

        .file-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
        }

        .file-label:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
        }

        /* Indicador de Beneficio */
        .profit-indicator {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: var(--border-radius);
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
        }

        .profit-indicator.alert-danger {
            background: #fef2f2;
            border-color: var(--danger-color);
        }

        .profit-indicator.alert-warning {
            background: #fffbeb;
            border-color: var(--warning-color);
        }

        .profit-indicator.alert-success {
            background: #f0fdf4;
            border-color: var(--success-color);
        }

        .profit-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .profit-content i {
            font-size: 1.25rem;
            color: var(--info-color);
        }

        .profit-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .profit-label {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .profit-value {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark-color);
        }

        /* Mensajes de Error */
        .error-message {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .error-message::before {
            content: '⚠';
            font-size: 0.75rem;
        }

        /* Botones de Acción */
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #f3f4f6;
        }

        .form-actions .action-btn {
            min-width: 150px;
            padding: 1rem 2rem;
            font-size: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .modern-header {
                padding: 1rem;
            }

            .main-title {
                font-size: 1.5rem;
            }

            .subtitle {
                font-size: 0.8rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .form-section {
                padding: 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .section-header {
                flex-direction: column;
                text-align: center;
                gap: 0.75rem;
            }

            .image-preview {
                height: 200px;
            }

            .form-actions {
                flex-direction: column;
                gap: 0.75rem;
            }

            .form-actions .action-btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .modern-header {
                padding: 0.75rem;
            }

            .main-title {
                font-size: 1.25rem;
            }

            .form-section {
                padding: 1rem;
            }

            .image-preview {
                height: 180px;
            }

            .upload-placeholder i {
                font-size: 2.5rem;
            }

            .upload-placeholder p {
                font-size: 0.9rem;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                dropdownParent: $('body')
            });

            // Preview de imagen mejorado
            $('#image').change(function() {
                const file = this.files[0];
                const preview = $('#imagePreview');
                const placeholder = preview.find('.upload-placeholder');
                
                if (file) {
                    // Validar tipo de archivo
                    if (!file.type.match('image.*')) {
                        alert('Por favor selecciona un archivo de imagen válido.');
                        return;
                    }

                    // Validar tamaño (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('La imagen no puede ser mayor a 2MB.');
                        return;
                    }

                    let reader = new FileReader();
                    reader.onload = function(event) {
                        preview.css({
                            'background-image': 'url(' + event.target.result + ')',
                            'border': '3px solid var(--primary-color)'
                        });
                        placeholder.hide();
                        
                        // Agregar overlay con información del archivo
                        preview.append(`
                            <div class="image-overlay">
                                <div class="image-info">
                                    <i class="fas fa-check-circle"></i>
                                    <span>${file.name}</span>
                                </div>
                            </div>
                        `);
                    }
                    reader.readAsDataURL(file);
                } else {
                    preview.css({
                        'background-image': 'none',
                        'border': '3px dashed #d1d5db'
                    });
                    placeholder.show();
                    preview.find('.image-overlay').remove();
                }
            });

            // Click en preview para seleccionar archivo
            $('#imagePreview').click(function() {
                $('#image').click();
            });

            // Calcular margen de beneficio
            function calculateProfit() {
                const purchasePrice = parseFloat($('#purchase_price').val()) || 0;
                const salePrice = parseFloat($('#sale_price').val()) || 0;

                if (purchasePrice > 0) {
                    const profit = ((salePrice - purchasePrice) / purchasePrice) * 100;
                    $('#profitValue').text(profit.toFixed(2) + '%');
                    $('#profitIndicator').show();

                    // Cambiar color según el margen
                    const indicator = $('#profitIndicator');
                    indicator.removeClass('alert-danger alert-warning alert-success');
                    
                    if (profit < 0) {
                        indicator.addClass('alert-danger');
                        indicator.find('i').attr('class', 'fas fa-arrow-down');
                    } else if (profit < 20) {
                        indicator.addClass('alert-warning');
                        indicator.find('i').attr('class', 'fas fa-exclamation-triangle');
                    } else {
                        indicator.addClass('alert-success');
                        indicator.find('i').attr('class', 'fas fa-arrow-up');
                    }
                } else {
                    $('#profitIndicator').hide();
                }
            }

            $('#purchase_price, #sale_price').on('input', calculateProfit);

            // Validación de stock
            function validateStock() {
                const stock = parseInt($('#stock').val()) || 0;
                const minStock = parseInt($('#min_stock').val()) || 0;
                const maxStock = parseInt($('#max_stock').val()) || 0;

                // Validar stock máximo vs mínimo
                if (minStock >= maxStock && maxStock > 0) {
                    $('#max_stock')[0].setCustomValidity('El stock máximo debe ser mayor que el stock mínimo');
                    $('#max_stock').addClass('is-invalid');
                } else {
                    $('#max_stock')[0].setCustomValidity('');
                    $('#max_stock').removeClass('is-invalid');
                }

                // Validar stock actual
                if (stock < 0) {
                    $('#stock')[0].setCustomValidity('El stock no puede ser negativo');
                    $('#stock').addClass('is-invalid');
                } else {
                    $('#stock')[0].setCustomValidity('');
                    $('#stock').removeClass('is-invalid');
                }
            }

            $('#stock, #min_stock, #max_stock').on('input', validateStock);

            // Validación del formulario
            $('#productForm').on('submit', function(e) {
                validateStock();
                
                const stock = parseInt($('#stock').val()) || 0;
                const minStock = parseInt($('#min_stock').val()) || 0;
                const maxStock = parseInt($('#max_stock').val()) || 0;

                if (minStock >= maxStock && maxStock > 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Validación',
                        text: 'El stock máximo debe ser mayor que el stock mínimo'
                    });
                    return false;
                }

                if (stock < 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Validación',
                        text: 'El stock no puede ser negativo'
                    });
                    return false;
                }

                // Mostrar indicador de carga
                $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            });

            // Auto-generar código si está vacío
            $('#name').on('blur', function() {
                const code = $('#code').val();
                const name = $(this).val();
                
                if (!code && name) {
                    const generatedCode = 'PROD' + Date.now().toString().slice(-6);
                    $('#code').val(generatedCode);
                }
            });

            // Efectos visuales
            $('.form-input').on('focus', function() {
                $(this).parent().addClass('focused');
            }).on('blur', function() {
                $(this).parent().removeClass('focused');
            });
        });
    </script>
@stop
