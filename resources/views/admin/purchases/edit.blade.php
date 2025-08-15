@extends('layouts.app')

@section('title', 'Editar Compra')

@section('content')
    <!-- Background Pattern -->
    <div class="page-background"></div>

    <!-- Main Container -->
    <div class="main-container" x-data="purchaseEditor()">
        <!-- Floating Header -->
        <div class="floating-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon-wrapper">
                        <div class="header-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="icon-glow"></div>
                    </div>
                    <div class="header-text">
                        <h1 class="header-title">Editar Compra #{{ $purchase->id }}</h1>
                        <p class="header-subtitle">Modifica los productos y detalles de la compra</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button @click="goBack()" class="btn-glass btn-secondary-glass">
                        <i class="fas fa-arrow-left"></i>
                        <span>Volver</span>
                        <div class="btn-ripple"></div>
                    </button>
                    <button type="submit" form="purchaseForm" class="btn-glass btn-primary-glass">
                        <i class="fas fa-save"></i>
                        <span>Actualizar</span>
                        <div class="btn-ripple"></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Alertas Modernas -->
        <div class="alerts-container" x-show="alerts.length > 0" x-transition>
            <template x-for="alert in alerts" :key="alert.id">
                <div class="alert-modern" :class="alert.type" x-show="alert.visible" x-transition>
                    <div class="alert-content">
                        <div class="alert-icon">
                            <i :class="alert.icon"></i>
                        </div>
                        <div class="alert-text">
                            <span x-text="alert.message"></span>
                        </div>
                    </div>
                    <button @click="removeAlert(alert.id)" class="alert-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </template>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <form action="{{ route('admin.purchases.update', $purchase->id) }}" method="POST" enctype="multipart/form-data"
                id="purchaseForm" @submit="validateForm">
                @csrf
                @method('PUT')

                <!-- Main Form Card -->
                <div class="form-card">
                    <!-- Progress Indicator -->
                    <div class="progress-indicator">
                        <div class="progress-step active">
                            <div class="step-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <span>Información Básica</span>
                        </div>
                        <div class="progress-line"></div>
                        <div class="progress-step">
                            <div class="step-icon">
                                <i class="fas fa-shopping-basket"></i>
                            </div>
                            <span>Productos</span>
                        </div>
                        <div class="progress-line"></div>
                        <div class="progress-step">
                            <div class="step-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <span>Completar</span>
                        </div>
                    </div>

                    <!-- Form Content -->
                    <div class="form-content">
                        <!-- Basic Information Section -->
                        <div class="section-group">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="section-text">
                                    <h3 class="section-title">Información de la Compra</h3>
                                    <p class="section-subtitle">Complete los datos básicos de la compra</p>
                                </div>
                            </div>

                            <div class="fields-grid">
                                <!-- Product Code Field -->
                                <div class="field-group">
                                    <div class="field-wrapper">
                                        <label for="product_code" class="field-label">
                                            <div class="label-content">
                                                <div class="label-icon">
                                                    <i class="fas fa-barcode"></i>
                                                </div>
                                                <span>Código de Producto</span>
                                                <span class="required-indicator">*</span>
                                            </div>
                                        </label>

                                        <div class="input-container">
                                            <div class="input-wrapper">
                                                <div class="input-icon">
                                                    <i class="fas fa-barcode"></i>
                                                </div>
                                                <input type="text" id="product_code" name="product_code"
                                                    x-model="productCode" @keyup.enter="addProductByCode"
                                                    class="modern-input @error('product_code') error @enderror"
                                                    placeholder="Escanee o ingrese el código del producto"
                                                    autocomplete="off">
                                                <div class="input-border"></div>
                                                <div class="input-focus-effect"></div>
                                            </div>

                                            <div class="input-actions">
                                                <button type="button" @click="openProductModal"
                                                    class="btn-modern btn-search">
                                                    <div class="btn-content">
                                                        <i class="fas fa-search"></i>
                                                        <span>Buscar</span>
                                                    </div>
                                                    <div class="btn-bg"></div>
                                                </button>
                                                <a href="/products/create" class="btn-modern btn-new">
                                                    <div class="btn-content">
                                                        <i class="fas fa-plus"></i>
                                                        <span>Nuevo</span>
                                                    </div>
                                                    <div class="btn-bg"></div>
                                                </a>
                                            </div>

                                            @error('product_code')
                                                <div class="error-message">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <span>{{ $message }}</span>
                                                </div>
                                            @enderror

                                            <div class="field-help">
                                                <i class="fas fa-lightbulb"></i>
                                                <span>Presione Enter después de escanear o escribir el código</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Date and Time Fields -->
                                <div class="field-group">
                                    <div class="field-wrapper">
                                        <label for="purchase_date" class="field-label">
                                            <div class="label-content">
                                                <div class="label-icon">
                                                    <i class="fas fa-calendar"></i>
                                                </div>
                                                <span>Fecha de Compra</span>
                                                <span class="required-indicator">*</span>
                                            </div>
                                        </label>

                                        <div class="input-container">
                                            <div class="input-wrapper">
                                                <div class="input-icon">
                                                    <i class="fas fa-calendar"></i>
                                                </div>
                                                <input type="date" id="purchase_date" name="purchase_date"
                                                    value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}"
                                                    class="modern-input @error('purchase_date') error @enderror" required>
                                                <div class="input-border"></div>
                                                <div class="input-focus-effect"></div>
                                            </div>

                                            @error('purchase_date')
                                                <div class="error-message">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <span>{{ $message }}</span>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="field-group">
                                    <div class="field-wrapper">
                                        <label for="purchase_time" class="field-label">
                                            <div class="label-content">
                                                <div class="label-icon">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <span>Hora de Compra</span>
                                                <span class="required-indicator">*</span>
                                            </div>
                                        </label>

                                        <div class="input-container">
                                            <div class="input-wrapper">
                                                <div class="input-icon">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <input type="time" id="purchase_time" name="purchase_time"
                                                    value="{{ old('purchase_time', $purchase->purchase_date->format('H:i')) }}"
                                                    class="modern-input @error('purchase_time') error @enderror" required>
                                                <div class="input-border"></div>
                                                <div class="input-focus-effect"></div>
                                            </div>

                                            @error('purchase_time')
                                                <div class="error-message">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <span>{{ $message }}</span>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Products Section -->
                        <div class="section-group">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-shopping-basket"></i>
                                </div>
                                <div class="section-text">
                                    <h3 class="section-title">Productos en la Compra</h3>
                                    <p class="section-subtitle">Gestiona los productos de esta compra</p>
                                </div>
                                <div class="section-controls">
                                    <div class="product-counter">
                                        <span class="counter-badge" x-text="`${products.length} productos`"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Products Table -->
                            <div class="products-container" x-show="products.length > 0" x-transition>
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
                                        <tbody>
                                            <template x-for="(product, index) in products" :key="product.id">
                                                <tr class="product-row" x-transition>
                                                    <td>
                                                        <div class="product-item">
                                                            <img :src="product.image_url" :alt="product.name"
                                                                class="product-image">
                                                            <div class="product-info">
                                                                <div class="product-name" x-text="product.name"></div>
                                                                <div class="product-code" x-text="product.code"></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="stock-badge" :class="getStockClass(product.stock)"
                                                            x-text="product.stock"></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="quantity-control">
                                                            <input type="number" :name="`items[${product.id}][quantity]`"
                                                                x-model.number="product.quantity"
                                                                @input="updateProductSubtotal(index)"
                                                                class="modern-input quantity-input" min="1"
                                                                :max="product.stock + product.original_quantity"
                                                                style="text-align: center;">
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="price-control">
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span
                                                                        class="input-group-text">{{ $currency->symbol }}</span>
                                                                </div>
                                                                <input type="number" :name="`items[${product.id}][price]`"
                                                                    x-model.number="product.price"
                                                                    @input="updateProductSubtotal(index)"
                                                                    class="modern-input price-input" step="0.01">
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <span class="subtotal-text">{{ $currency->symbol }} <span
                                                                class="subtotal"
                                                                x-text="formatCurrency(product.subtotal)"></span></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" @click="removeProduct(index)"
                                                            class="btn-modern btn-danger">
                                                            <div class="btn-content">
                                                                <i class="fas fa-trash"></i>
                                                            </div>
                                                            <div class="btn-bg"></div>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div class="empty-state" x-show="products.length === 0" x-transition>
                                <div class="empty-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h5 class="empty-title">No hay productos agregados</h5>
                                <p class="empty-description">Escanee un producto o use el botón "Buscar" para agregar
                                    productos a la compra</p>
                            </div>
                        </div>
                    </div>
                </div>

        </div>

        <!-- Summary Card -->
        <div class="summary-card">
            <div class="summary-header">
                <div class="summary-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Resumen de Compra</h3>
            </div>

            <!-- Summary Stats -->
            <div class="summary-stats">
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-value" x-text="products.length"></div>
                        <div class="summary-label">Productos Únicos</div>
                    </div>
                </div>

                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-value" x-text="totalQuantity"></div>
                        <div class="summary-label">Cantidad Total</div>
                    </div>
                </div>

                <div class="summary-item total">
                    <div class="summary-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-value total-amount"
                            x-text="`{{ $currency->symbol }} ${formatCurrency(totalAmount)}`"></div>
                        <div class="summary-label">Total a Pagar</div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="total_price" x-model="totalAmount">

            <!-- Action Buttons -->
            <div class="form-actions">
                <div class="actions-left">
                    <button type="button" @click="cancelEdit" class="btn-modern btn-cancel">
                        <div class="btn-content">
                            <i class="fas fa-times"></i>
                            <span>Cancelar</span>
                        </div>
                        <div class="btn-bg"></div>
                    </button>
                </div>

                <div class="actions-right">
                    <button type="submit" class="btn-modern btn-submit">
                        <div class="btn-content">
                            <i class="fas fa-save"></i>
                            <span>Actualizar Compra</span>
                        </div>
                        <div class="btn-bg"></div>
                        <div class="btn-shine"></div>
                    </button>
                </div>
            </div>
        </div>
        </form>

        <!-- Product Search Modal -->
        <div class="modal-overlay" x-show="showProductModal" x-transition @click.away="showProductModal = false">
            <div class="modal-container" @click.stop>
                <div class="modal-header">
                    <div class="modal-title-section">
                        <div class="modal-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="modal-title-content">
                            <h5 class="modal-title">Búsqueda de Productos</h5>
                            <p class="modal-subtitle">Seleccione los productos para agregar a la compra</p>
                        </div>
                    </div>
                    <button @click="showProductModal = false" class="modal-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="search-container">
                        <div class="search-input-wrapper">
                            <div class="input-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <input type="text" x-model="searchTerm" @input="filterProducts"
                                class="modern-input search-input" placeholder="Buscar productos por nombre o código...">
                            <div class="input-border"></div>
                        </div>
                    </div>

                    <div class="products-grid" x-show="filteredProducts.length > 0">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div class="product-card" @click="addProduct(product)">
                                <div class="product-image">
                                    <img :src="product.image_url" :alt="product.name">
                                </div>
                                <div class="product-info">
                                    <h4 class="product-name" x-text="product.name"></h4>
                                    <p class="product-code" x-text="product.code"></p>
                                    <div class="product-meta">
                                        <span class="category-badge" x-text="product.category.name"></span>
                                        <span class="stock-badge" :class="getStockClass(product.stock)"
                                            x-text="product.stock"></span>
                                    </div>
                                    <div class="product-price">
                                        <span class="price-text">{{ $currency->symbol }} <span
                                                x-text="formatCurrency(product.purchase_price)"></span></span>
                                    </div>
                                </div>
                                <div class="product-action">
                                    <button class="btn-modern btn-add">
                                        <div class="btn-content">
                                            <i class="fas fa-plus"></i>
                                        </div>
                                        <div class="btn-bg"></div>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="empty-search" x-show="filteredProducts.length === 0 && searchTerm.length > 0">
                        <div class="empty-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5>No se encontraron productos</h5>
                        <p>Intenta con otros términos de búsqueda</p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button @click="showProductModal = false" class="btn-modern btn-cancel">
                        <div class="btn-content">
                            <i class="fas fa-times"></i>
                            <span>Cerrar</span>
                        </div>
                        <div class="btn-bg"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('css')
        <style>
            /* ===== VARIABLES GLOBALES ===== */
            :root {
                /* Colores principales */
                --primary-50: #f0f9ff;
                --primary-100: #e0f2fe;
                --primary-200: #bae6fd;
                --primary-300: #7dd3fc;
                --primary-400: #38bdf8;
                --primary-500: #0ea5e9;
                --primary-600: #0284c7;
                --primary-700: #0369a1;
                --primary-800: #075985;
                --primary-900: #0c4a6e;

                /* Colores secundarios */
                --secondary-50: #faf5ff;
                --secondary-100: #f3e8ff;
                --secondary-200: #e9d5ff;
                --secondary-300: #d8b4fe;
                --secondary-400: #c084fc;
                --secondary-500: #a855f7;
                --secondary-600: #9333ea;
                --secondary-700: #7c3aed;
                --secondary-800: #6b21a8;
                --secondary-900: #581c87;

                /* Gradientes */
                --gradient-primary: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 50%, #8b5cf6 100%);
                --gradient-secondary: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                --gradient-glass: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
                --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
                --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                --gradient-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);

                /* Colores neutros */
                --gray-50: #f8fafc;
                --gray-100: #f1f5f9;
                --gray-200: #e2e8f0;
                --gray-300: #cbd5e0;
                --gray-400: #94a3b8;
                --gray-500: #64748b;
                --gray-600: #475569;
                --gray-700: #334155;
                --gray-800: #1e293b;
                --gray-900: #0f172a;

                /* Sombras */
                --shadow-xs: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
                --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
                --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
                --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
                --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                --shadow-inner: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);

                /* Efectos especiales */
                --glow-primary: 0 0 20px rgba(14, 165, 233, 0.3);
                --glow-secondary: 0 0 20px rgba(168, 85, 247, 0.3);
                --blur-backdrop: blur(20px);
                --border-radius: 16px;
                --border-radius-lg: 24px;
                --border-radius-xl: 32px;

                /* Animaciones */
                --transition-fast: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
                --transition-normal: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                --transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
                --transition-bounce: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            }

            /* ===== RESET Y BASE ===== */
            * {
                box-sizing: border-box;
            }

            body {
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                min-height: 100vh;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                overflow-x: hidden;
            }

            /* ===== FONDO ANIMADO ===== */
            .page-background {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                overflow: hidden;
            }

            .page-background::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background:
                    radial-gradient(circle at 20% 80%, rgba(14, 165, 233, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(168, 85, 247, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 40% 40%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
                animation: float 20s ease-in-out infinite;
            }

            @keyframes float {

                0%,
                100% {
                    transform: translate(0, 0) rotate(0deg);
                }

                33% {
                    transform: translate(30px, -30px) rotate(120deg);
                }

                66% {
                    transform: translate(-20px, 20px) rotate(240deg);
                }
            }

            /* ===== CONTENEDOR PRINCIPAL ===== */
            .main-container {
                position: relative;
                max-width: 1400px;
                margin: 0 auto;
                padding: 2rem;
                min-height: 100vh;
                z-index: 1;
            }

            /* ===== HEADER FLOTANTE ===== */
            .floating-header {
                position: relative;
                margin-bottom: 2rem;
                animation: slideDown 0.6s ease-out;
                z-index: 10;
            }

            .header-content {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: var(--blur-backdrop);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: var(--border-radius-lg);
                padding: 1.5rem 2rem;
                box-shadow: var(--shadow-xl);
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 2rem;
            }

            .header-left {
                display: flex;
                align-items: center;
                gap: 1.5rem;
            }

            .header-icon-wrapper {
                position: relative;
            }

            .header-icon {
                width: 64px;
                height: 64px;
                background: var(--gradient-primary);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.5rem;
                box-shadow: var(--shadow-lg);
                position: relative;
                z-index: 2;
            }

            .icon-glow {
                position: absolute;
                inset: -4px;
                background: var(--gradient-primary);
                border-radius: 50%;
                opacity: 0.3;
                filter: blur(8px);
                animation: pulse 2s ease-in-out infinite;
            }

            .header-text h1 {
                font-size: 1.75rem;
                font-weight: 800;
                background: var(--gradient-primary);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                margin: 0;
                line-height: 1.2;
            }

            .header-text p {
                color: var(--gray-600);
                margin: 0.25rem 0 0 0;
                font-weight: 500;
            }

            .header-actions {
                display: flex;
                gap: 1rem;
            }

            /* ===== BOTONES GLASS ===== */
            .btn-glass {
                position: relative;
                padding: 0.75rem 1.5rem;
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 12px;
                background: var(--gradient-glass);
                backdrop-filter: var(--blur-backdrop);
                color: var(--gray-700);
                font-weight: 600;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                transition: var(--transition-normal);
                overflow: hidden;
                cursor: pointer;
            }

            .btn-glass::before {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
                opacity: 0;
                transition: var(--transition-fast);
            }

            .btn-glass:hover::before {
                opacity: 1;
            }

            .btn-glass:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-lg);
            }

            .btn-primary-glass {
                background: var(--gradient-primary);
                color: white;
                box-shadow: var(--glow-primary);
            }

            .btn-secondary-glass {
                background: rgba(255, 255, 255, 0.8);
                color: var(--gray-700);
            }

            .btn-ripple {
                position: absolute;
                inset: 0;
                overflow: hidden;
            }

            /* ===== ALERTAS MODERNAS ===== */
            .alerts-container {
                position: fixed;
                top: 2rem;
                right: 2rem;
                z-index: 1000;
                display: flex;
                flex-direction: column;
                gap: 1rem;
                max-width: 400px;
            }

            .alert-modern {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: var(--blur-backdrop);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: var(--border-radius);
                padding: 1rem;
                box-shadow: var(--shadow-lg);
                display: flex;
                align-items: center;
                gap: 1rem;
                animation: slideInRight 0.3s ease-out;
            }

            .alert-modern.success {
                border-left: 4px solid var(--success-color);
            }

            .alert-modern.danger {
                border-left: 4px solid var(--danger-color);
            }

            .alert-modern.warning {
                border-left: 4px solid var(--warning-color);
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

            .alert-modern.success .alert-icon {
                background: rgba(16, 185, 129, 0.1);
                color: var(--success-color);
            }

            .alert-modern.danger .alert-icon {
                background: rgba(239, 68, 68, 0.1);
                color: var(--danger-color);
            }

            .alert-modern.warning .alert-icon {
                background: rgba(245, 158, 11, 0.1);
                color: var(--warning-color);
            }

            .alert-text {
                flex: 1;
                font-weight: 500;
            }

            .alert-close {
                background: none;
                border: none;
                color: var(--gray-400);
                font-size: 1.25rem;
                padding: 0.25rem;
                border-radius: 50%;
                transition: var(--transition-fast);
                cursor: pointer;
            }

            .alert-close:hover {
                background: rgba(0, 0, 0, 0.1);
                color: var(--gray-600);
            }

            /* ===== CONTENEDOR DEL FORMULARIO ===== */
            .form-container {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: 2rem;
                align-items: start;
                position: relative;
                z-index: 1;
            }

            .form-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: var(--blur-backdrop);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: var(--border-radius-xl);
                box-shadow: var(--shadow-2xl);
                overflow: hidden;
                animation: slideUp 0.8s ease-out 0.2s both;
                position: relative;
                z-index: 2;
            }

            /* ===== INDICADOR DE PROGRESO ===== */
            .progress-indicator {
                display: flex;
                align-items: center;
                padding: 2rem;
                background: var(--gradient-secondary);
                border-bottom: 1px solid var(--gray-200);
            }

            .progress-step {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                flex: 1;
                position: relative;
            }

            .step-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.875rem;
                font-weight: 600;
                transition: var(--transition-normal);
                background: var(--gray-200);
                color: var(--gray-500);
            }

            .progress-step.active .step-icon {
                background: var(--gradient-primary);
                color: white;
                box-shadow: var(--glow-primary);
            }

            .progress-step span {
                font-weight: 600;
                color: var(--gray-600);
                transition: var(--transition-normal);
            }

            .progress-step.active span {
                color: var(--primary-600);
            }

            .progress-line {
                flex: 1;
                height: 2px;
                background: var(--gray-200);
                margin: 0 1rem;
                position: relative;
                overflow: hidden;
            }

            .progress-line::after {
                content: '';
                position: absolute;
                inset: 0;
                background: var(--gradient-primary);
                transform: translateX(-100%);
                animation: progressFill 2s ease-out 1s both;
            }

            /* ===== CONTENIDO DEL FORMULARIO ===== */
            .form-content {
                padding: 3rem;
            }

            .section-group {
                margin-bottom: 3rem;
                animation: fadeInUp 0.6s ease-out both;
            }

            .section-group:nth-child(1) {
                animation-delay: 0.3s;
            }

            .section-group:nth-child(2) {
                animation-delay: 0.4s;
            }

            .section-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid var(--gray-200);
            }

            .section-icon {
                width: 48px;
                height: 48px;
                background: var(--gradient-primary);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.25rem;
                margin-right: 1rem;
            }

            .section-text {
                flex: 1;
            }

            .section-title {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--gray-800);
                margin: 0 0 0.5rem 0;
            }

            .section-subtitle {
                color: var(--gray-600);
                margin: 0;
                font-weight: 500;
            }

            .section-controls {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .product-counter {
                background: var(--gradient-primary);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-weight: 600;
                font-size: 0.875rem;
            }

            /* ===== GRID DE CAMPOS ===== */
            .fields-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 2rem;
            }

            .field-group {
                margin-bottom: 2rem;
            }

            .field-wrapper {
                position: relative;
            }

            .field-label {
                margin-bottom: 1rem;
            }

            .label-content {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .label-icon {
                width: 32px;
                height: 32px;
                background: var(--gradient-primary);
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 0.875rem;
            }

            .label-content span {
                font-size: 1rem;
                font-weight: 700;
                color: var(--gray-800);
            }

            .required-indicator {
                color: #ef4444;
                font-weight: 800;
                font-size: 1.1rem;
                margin-left: 0.25rem;
            }

            /* ===== INPUTS MODERNOS ===== */
            .input-container {
                position: relative;
            }

            .input-wrapper {
                position: relative;
                display: flex;
                align-items: stretch;
                background: white;
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-sm);
                transition: var(--transition-normal);
                overflow: hidden;
            }

            .input-wrapper:hover {
                box-shadow: var(--shadow-md);
            }

            .input-wrapper:focus-within {
                box-shadow: var(--glow-primary);
                transform: translateY(-1px);
            }

            .input-icon {
                width: 48px;
                background: var(--gray-50);
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--primary-500);
                font-size: 1rem;
                border-right: 1px solid var(--gray-200);
            }

            .modern-input {
                flex: 1;
                padding: 1rem 1.25rem;
                border: none;
                background: transparent;
                font-size: 1rem;
                font-weight: 500;
                color: var(--gray-800);
                transition: var(--transition-fast);
                outline: none;
            }

            .modern-input::placeholder {
                color: var(--gray-400);
                font-weight: 400;
            }

            .input-border {
                position: absolute;
                inset: 0;
                border: 2px solid transparent;
                border-radius: var(--border-radius);
                pointer-events: none;
                transition: var(--transition-fast);
            }

            .input-wrapper:focus-within .input-border {
                border-color: var(--primary-500);
            }

            .input-wrapper.error .input-border {
                border-color: #ef4444;
            }

            .input-focus-effect {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 2px;
                background: var(--gradient-primary);
                transform: scaleX(0);
                transition: var(--transition-normal);
            }

            .input-wrapper:focus-within .input-focus-effect {
                transform: scaleX(1);
            }

            .input-actions {
                display: flex;
                gap: 0.75rem;
                margin-top: 0.75rem;
            }

            /* ===== BOTONES MODERNOS ===== */
            .btn-modern {
                position: relative;
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 12px;
                font-weight: 600;
                font-size: 0.875rem;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                transition: var(--transition-bounce);
                overflow: hidden;
                text-decoration: none;
            }

            .btn-content {
                position: relative;
                z-index: 2;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .btn-bg {
                position: absolute;
                inset: 0;
                transition: var(--transition-normal);
            }

            .btn-search {
                color: white;
            }

            .btn-search .btn-bg {
                background: var(--gradient-primary);
            }

            .btn-search:hover {
                transform: translateY(-2px);
                box-shadow: var(--glow-primary);
            }

            .btn-new {
                color: white;
            }

            .btn-new .btn-bg {
                background: var(--gradient-success);
            }

            .btn-new:hover {
                transform: translateY(-2px);
                box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
            }

            .btn-danger {
                color: white;
            }

            .btn-danger .btn-bg {
                background: var(--gradient-danger);
            }

            .btn-danger:hover {
                transform: translateY(-2px);
                box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
            }

            /* ===== MENSAJES DE ERROR Y AYUDA ===== */
            .error-message {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: #ef4444;
                font-weight: 600;
                font-size: 0.875rem;
                margin-top: 0.75rem;
                padding: 0.5rem 0.75rem;
                background: rgba(239, 68, 68, 0.05);
                border-radius: 8px;
                border-left: 3px solid #ef4444;
            }

            .field-help {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: var(--gray-500);
                font-size: 0.875rem;
                margin-top: 0.75rem;
                font-weight: 500;
            }

            .field-help i {
                color: var(--primary-500);
            }

            /* ===== TABLA DE PRODUCTOS ===== */
            .products-container {
                margin-top: 1rem;
            }

            .table-container {
                overflow-x: auto;
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-sm);
                background: white;
            }

            .modern-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                background: white;
            }

            .modern-table thead {
                background: var(--gradient-primary);
            }

            .modern-table th {
                padding: 1rem;
                color: white;
                font-weight: 600;
                text-align: left;
                border: none;
                font-size: 0.875rem;
                white-space: nowrap;
            }

            .modern-table th:first-child {
                border-top-left-radius: var(--border-radius);
            }

            .modern-table th:last-child {
                border-top-right-radius: var(--border-radius);
            }

            .modern-table tbody tr {
                transition: var(--transition-normal);
                border-bottom: 1px solid var(--gray-100);
            }

            .modern-table tbody tr:hover {
                background: var(--gray-50);
                transform: scale(1.01);
            }

            .modern-table td {
                padding: 1rem;
                vertical-align: middle;
                border: none;
            }

            /* Ancho de columnas específicas */
            .th-product {
                min-width: 250px;
                width: 30%;
            }

            .th-stock {
                min-width: 100px;
                width: 10%;
            }

            .th-quantity {
                min-width: 120px;
                width: 15%;
            }

            .th-price {
                min-width: 150px;
                width: 20%;
            }

            .th-subtotal {
                min-width: 120px;
                width: 15%;
            }

            .th-actions {
                min-width: 100px;
                width: 10%;
            }

            /* ===== PRODUCTO EN TABLA ===== */
            .product-item {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .product-image {
                width: 60px;
                height: 60px;
                border-radius: 12px;
                object-fit: cover;
                border: 2px solid var(--gray-200);
            }

            .product-info {
                flex: 1;
            }

            .product-name {
                font-weight: 600;
                color: var(--gray-800);
                margin-bottom: 0.25rem;
            }

            .product-code {
                font-size: 0.875rem;
                color: var(--gray-500);
                font-family: 'Courier New', monospace;
            }

            /* ===== CONTROLES DE CANTIDAD Y PRECIO ===== */
            .quantity-control,
            .price-control {
                max-width: 120px;
            }

            .quantity-control .modern-input,
            .price-control .modern-input {
                text-align: center;
                font-weight: 600;
                padding: 0.75rem;
                border: 2px solid var(--gray-200);
                border-radius: var(--border-radius);
                background: white;
            }

            .price-control .input-group {
                display: flex;
                align-items: stretch;
            }

            .price-control .input-group-prepend {
                display: flex;
                align-items: center;
            }

            .price-control .input-group-text {
                background: var(--gradient-success);
                color: white;
                border: 2px solid var(--success-color);
                border-right: none;
                font-weight: 600;
                padding: 0.75rem 0.5rem;
                border-radius: var(--border-radius) 0 0 var(--border-radius);
            }

            .price-control .modern-input {
                border-left: none;
                border-radius: 0 var(--border-radius) var(--border-radius) 0;
            }

            /* ===== BADGES ===== */
            .stock-badge {
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
                border-radius: 20px;
                font-weight: 600;
            }

            .badge-success {
                background: var(--gradient-success);
                color: white;
            }

            .badge-warning {
                background: var(--gradient-warning);
                color: white;
            }

            .badge-danger {
                background: var(--gradient-danger);
                color: white;
            }

            .subtotal-text {
                font-weight: 600;
                color: var(--success-color);
                font-size: 1rem;
            }

            /* ===== ESTADO VACÍO ===== */
            .empty-state {
                text-align: center;
                padding: 3rem 1rem;
                background: var(--gray-50);
                border-radius: var(--border-radius);
                border: 2px dashed var(--gray-300);
            }

            .empty-icon {
                font-size: 4rem;
                color: var(--gray-400);
                margin-bottom: 1.5rem;
            }

            .empty-title {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--gray-600);
                margin-bottom: 0.5rem;
            }

            .empty-description {
                color: var(--gray-500);
                max-width: 400px;
                margin: 0 auto;
                font-size: 0.875rem;
                line-height: 1.5;
            }

            /* ===== TARJETA DE RESUMEN ===== */
            .summary-card {
                width: 350px;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: var(--blur-backdrop);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: var(--border-radius-lg);
                box-shadow: var(--shadow-xl);
                overflow: hidden;
                position: relative;
                animation: slideLeft 0.8s ease-out 0.4s both;
                z-index: 2;
            }

            .summary-header {
                background: var(--gradient-secondary);
                padding: 1.5rem;
                display: flex;
                align-items: center;
                gap: 1rem;
                border-bottom: 1px solid var(--gray-200);
            }

            .summary-icon {
                width: 40px;
                height: 40px;
                background: var(--gradient-primary);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1rem;
            }

            .summary-header h3 {
                font-size: 1.125rem;
                font-weight: 700;
                color: var(--gray-800);
                margin: 0;
            }

            .summary-stats {
                padding: 1.5rem;
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .summary-item {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1rem;
                background: var(--gray-50);
                border-radius: var(--border-radius);
                border: 1px solid var(--gray-200);
                transition: var(--transition-normal);
            }

            .summary-item:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
            }

            .summary-item.total {
                background: var(--gradient-success);
                color: white;
                border: none;
            }

            .summary-item .summary-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: var(--primary-500);
                color: white;
                font-size: 1rem;
                flex-shrink: 0;
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
                margin-bottom: 0.25rem;
                line-height: 1.2;
            }

            .summary-label {
                font-size: 0.875rem;
                font-weight: 500;
                opacity: 0.8;
                line-height: 1.2;
            }

            .total-amount {
                font-size: 1.5rem;
            }

            /* ===== ACCIONES DEL FORMULARIO ===== */
            .form-actions {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1.5rem;
                padding: 1.5rem;
                border-top: 1px solid var(--gray-200);
                background: var(--gray-50);
            }

            .actions-left,
            .actions-right {
                display: flex;
                gap: 1rem;
            }

            .btn-cancel {
                color: var(--gray-700);
            }

            .btn-cancel .btn-bg {
                background: white;
                border: 2px solid var(--gray-200);
            }

            .btn-cancel:hover {
                color: var(--gray-800);
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
            }

            .btn-cancel:hover .btn-bg {
                background: var(--gray-50);
                border-color: var(--gray-300);
            }

            .btn-submit {
                color: white;
                position: relative;
            }

            .btn-submit .btn-bg {
                background: var(--gradient-primary);
            }

            .btn-submit:hover {
                transform: translateY(-2px);
                box-shadow: var(--glow-primary);
            }

            .btn-shine {
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.3) 50%, transparent 70%);
                transform: translateX(-100%);
                transition: var(--transition-slow);
            }

            .btn-submit:hover .btn-shine {
                transform: translateX(100%);
            }

            /* ===== MODAL DE BÚSQUEDA ===== */
            .modal-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(8px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                padding: 2rem;
            }

            .modal-container {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: var(--blur-backdrop);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: var(--border-radius-xl);
                box-shadow: var(--shadow-2xl);
                overflow: hidden;
                max-width: 90vw;
                max-height: 90vh;
                width: 1000px;
                display: flex;
                flex-direction: column;
            }

            .modal-header {
                background: var(--gradient-primary);
                color: white;
                padding: 1.5rem 2rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: none;
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
                transition: var(--transition-normal);
                cursor: pointer;
            }

            .modal-close:hover {
                background: rgba(255, 255, 255, 0.2);
                transform: rotate(90deg);
            }

            .modal-body {
                padding: 2rem;
                flex: 1;
                overflow-y: auto;
            }

            .search-container {
                margin-bottom: 2rem;
            }

            .search-input-wrapper {
                position: relative;
                display: flex;
                align-items: stretch;
                background: white;
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-sm);
                transition: var(--transition-normal);
                overflow: hidden;
            }

            .search-input-wrapper:focus-within {
                box-shadow: var(--glow-primary);
                transform: translateY(-1px);
            }

            .search-input {
                flex: 1;
                padding: 1rem 1.25rem;
                border: none;
                background: transparent;
                font-size: 1rem;
                font-weight: 500;
                color: var(--gray-800);
                outline: none;
            }

            .search-input::placeholder {
                color: var(--gray-400);
            }

            .products-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 1.5rem;
            }

            .product-card {
                background: white;
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-sm);
                overflow: hidden;
                transition: var(--transition-normal);
                cursor: pointer;
                position: relative;
            }

            .product-card:hover {
                transform: translateY(-4px);
                box-shadow: var(--shadow-lg);
            }

            .product-card .product-image {
                width: 100%;
                height: 200px;
                object-fit: cover;
                border: none;
                border-radius: 0;
            }

            .product-card .product-info {
                padding: 1.5rem;
            }

            .product-card .product-name {
                font-size: 1.125rem;
                font-weight: 700;
                color: var(--gray-800);
                margin-bottom: 0.5rem;
            }

            .product-card .product-code {
                font-size: 0.875rem;
                color: var(--gray-500);
                margin-bottom: 1rem;
            }

            .product-meta {
                display: flex;
                gap: 0.5rem;
                margin-bottom: 1rem;
            }

            .category-badge {
                background: var(--gray-100);
                color: var(--gray-700);
                padding: 0.25rem 0.75rem;
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 500;
            }

            .product-price {
                font-size: 1.25rem;
                font-weight: 700;
                color: var(--success-color);
            }

            .product-action {
                position: absolute;
                top: 1rem;
                right: 1rem;
            }

            .btn-add {
                color: white;
            }

            .btn-add .btn-bg {
                background: var(--gradient-success);
            }

            .btn-add:hover {
                transform: scale(1.1);
                box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
            }

            .empty-search {
                text-align: center;
                padding: 3rem 1rem;
            }

            .empty-search .empty-icon {
                font-size: 3rem;
                color: var(--gray-400);
                margin-bottom: 1rem;
            }

            .empty-search h5 {
                font-size: 1.125rem;
                font-weight: 600;
                color: var(--gray-600);
                margin-bottom: 0.5rem;
            }

            .empty-search p {
                color: var(--gray-500);
                font-size: 0.875rem;
            }

            .modal-footer {
                background: var(--gray-50);
                border-top: 1px solid var(--gray-200);
                padding: 1.5rem 2rem;
                display: flex;
                justify-content: flex-end;
            }

            /* ===== ANIMACIONES ===== */
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes slideLeft {
                from {
                    opacity: 0;
                    transform: translateX(20px);
                }

                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }

                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes pulse {

                0%,
                100% {
                    opacity: 0.3;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.5;
                    transform: scale(1.05);
                }
            }

            @keyframes progressFill {
                from {
                    transform: translateX(-100%);
                }

                to {
                    transform: translateX(0);
                }
            }

            /* ===== RESPONSIVE DESIGN ===== */
            @media (max-width: 1200px) {
                .form-container {
                    grid-template-columns: 1fr;
                }

                .summary-card {
                    width: 100%;
                    position: relative;
                }
            }

            @media (max-width: 768px) {
                .main-container {
                    padding: 1rem;
                }

                .header-content {
                    flex-direction: column;
                    text-align: center;
                    gap: 1.5rem;
                }

                .header-left {
                    flex-direction: column;
                    gap: 1rem;
                }

                .header-actions {
                    flex-wrap: wrap;
                    justify-content: center;
                }

                .form-content {
                    padding: 2rem;
                }

                .fields-grid {
                    grid-template-columns: 1fr;
                    gap: 1.5rem;
                }

                .section-header {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 1rem;
                }

                .section-controls {
                    align-self: stretch;
                    justify-content: center;
                }

                .form-actions {
                    flex-direction: column;
                    gap: 1rem;
                }

                .actions-left,
                .actions-right {
                    width: 100%;
                    justify-content: center;
                }

                .btn-modern {
                    width: 100%;
                    justify-content: center;
                }

                .progress-indicator {
                    padding: 1.5rem;
                }

                .progress-step {
                    flex-direction: column;
                    text-align: center;
                    gap: 0.5rem;
                }

                .progress-step span {
                    font-size: 0.875rem;
                }

                .modal-container {
                    margin: 1rem;
                    max-width: calc(100vw - 2rem);
                }

                .products-grid {
                    grid-template-columns: 1fr;
                }

                .modern-table {
                    font-size: 0.875rem;
                }

                .modern-table th,
                .modern-table td {
                    padding: 0.75rem 0.5rem;
                }

                .product-item {
                    flex-direction: column;
                    text-align: center;
                    gap: 0.5rem;
                }

                .product-image {
                    width: 50px;
                    height: 50px;
                }

                .quantity-control,
                .price-control {
                    max-width: 100px;
                }
            }

            @media (max-width: 480px) {
                .header-icon {
                    width: 56px;
                    height: 56px;
                    font-size: 1.25rem;
                }

                .header-text h1 {
                    font-size: 1.5rem;
                }

                .form-content {
                    padding: 1.5rem;
                }

                .field-group {
                    margin-bottom: 1.5rem;
                }

                .btn-glass {
                    padding: 0.625rem 1.25rem;
                    font-size: 0.875rem;
                }

                .modal-body {
                    padding: 1rem;
                }

                .product-card .product-info {
                    padding: 1rem;
                }
            }
        </style>
    @endpush

    @push('js')
        <script>
            function purchaseEditor() {
                return {
                    // Data
                    productCode: '',
                    products: {!! json_encode(
                        $purchase->details->map(function ($detail) {
                            return [
                                'id' => $detail->product->id,
                                'code' => $detail->product->code,
                                'name' => $detail->product->name,
                                'image_url' => $detail->product->image_url,
                                'stock' => $detail->product->stock,
                                'quantity' => $detail->quantity,
                                'original_quantity' => $detail->quantity,
                                'price' => $detail->product->purchase_price,
                                'subtotal' => $detail->quantity * $detail->product->purchase_price,
                                'category' => ['name' => $detail->product->category->name],
                            ];
                        }),
                    ) !!},
                    allProducts: {!! json_encode(
                        $products->map(function ($product) {
                            return [
                                'id' => $product->id,
                                'code' => $product->code,
                                'name' => $product->name,
                                'image_url' => $product->image_url,
                                'stock' => $product->stock,
                                'purchase_price' => $product->purchase_price,
                                'category' => ['name' => $product->category->name],
                            ];
                        }),
                    ) !!},
                    filteredProducts: [],
                    searchTerm: '',
                    showProductModal: false,
                    alerts: [],
                    formChanged: false,

                    // Computed
                    get totalAmount() {
                        return this.products.reduce((sum, product) => sum + product.subtotal, 0);
                    },

                    get totalQuantity() {
                        return this.products.reduce((sum, product) => sum + product.quantity, 0);
                    },

                    // Methods
                    init() {
                        this.filteredProducts = [...this.allProducts];
                        this.initializeAlerts();
                    },

                    initializeAlerts() {
                        @if (session('message'))
                            this.addAlert('{{ session('message') }}',
                                '{{ session('icons') == 'success' ? 'success' : 'danger' }}');
                        @endif

                        @if ($errors->any())
                            @foreach ($errors->all() as $error)
                                this.addAlert('{{ $error }}', 'danger');
                            @endforeach
                        @endif
                    },

                    addAlert(message, type = 'info') {
                        const alert = {
                            id: Date.now() + Math.random(),
                            message,
                            type,
                            icon: type === 'success' ? 'fas fa-check-circle' : type === 'danger' ?
                                'fas fa-exclamation-triangle' : 'fas fa-info-circle',
                            visible: true
                        };
                        this.alerts.push(alert);

                        setTimeout(() => {
                            this.removeAlert(alert.id);
                        }, 5000);
                    },

                    removeAlert(id) {
                        const alert = this.alerts.find(a => a.id === id);
                        if (alert) {
                            alert.visible = false;
                            setTimeout(() => {
                                this.alerts = this.alerts.filter(a => a.id !== id);
                            }, 300);
                        }
                    },

                    addProductByCode() {
                        if (!this.productCode.trim()) return;

                        const product = this.allProducts.find(p => p.code === this.productCode.trim());
                        if (!product) {
                            this.addAlert('Producto no encontrado', 'danger');
                            return;
                        }

                        if (this.products.some(p => p.code === product.code)) {
                            this.addAlert('Este producto ya está en la lista', 'warning');
                            return;
                        }

                        this.addProduct(product);
                        this.productCode = '';
                        this.addAlert('Producto agregado correctamente', 'success');
                    },

                    addProduct(product) {
                        const newProduct = {
                            ...product,
                            quantity: 1,
                            original_quantity: 0,
                            price: product.purchase_price,
                            subtotal: product.purchase_price
                        };

                        this.products.push(newProduct);
                        this.showProductModal = false;
                        this.formChanged = true;
                        this.addAlert('Producto agregado correctamente', 'success');
                    },

                    removeProduct(index) {
                        const product = this.products[index];
                        if (confirm(`¿Está seguro de que desea eliminar "${product.name}" de la compra?`)) {
                            this.products.splice(index, 1);
                            this.formChanged = true;
                            this.addAlert('Producto eliminado correctamente', 'success');
                        }
                    },

                    updateProductSubtotal(index) {
                        const product = this.products[index];
                        product.subtotal = product.quantity * product.price;
                        this.formChanged = true;
                    },

                    openProductModal() {
                        this.showProductModal = true;
                        this.searchTerm = '';
                        this.filteredProducts = [...this.allProducts];
                    },

                    filterProducts() {
                        if (!this.searchTerm.trim()) {
                            this.filteredProducts = [...this.allProducts];
                            return;
                        }

                        const term = this.searchTerm.toLowerCase();
                        this.filteredProducts = this.allProducts.filter(product =>
                            product.name.toLowerCase().includes(term) ||
                            product.code.toLowerCase().includes(term)
                        );
                    },

                    getStockClass(stock) {
                        if (stock > 10) return 'badge-success';
                        if (stock > 0) return 'badge-warning';
                        return 'badge-danger';
                    },

                    formatCurrency(amount) {
                        return parseFloat(amount).toFixed(2);
                    },

                    validateForm() {
                        if (this.products.length === 0) {
                            this.addAlert('Debe agregar al menos un producto a la compra', 'warning');
                            return false;
                        }
                        return true;
                    },

                    goBack() {
                        if (this.formChanged) {
                            if (confirm('¿Está seguro de que desea salir? Los cambios no guardados se perderán.')) {
                                window.history.back();
                            }
                        } else {
                            window.history.back();
                        }
                    },

                    cancelEdit() {
                        if (this.formChanged) {
                            if (confirm(
                                    '¿Está seguro de que desea cancelar la edición? Los cambios no guardados se perderán.')) {
                                window.history.back();
                            }
                        } else {
                            window.history.back();
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
