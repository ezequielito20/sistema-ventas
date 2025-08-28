<div>
    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" id="orderTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button 
                class="nav-link {{ $activeTab === 'orders' ? 'active' : '' }}" 
                wire:click="switchTab('orders')"
                type="button" 
                role="tab">
                <i class="fas fa-shopping-cart"></i>
                Hacer Pedido
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button 
                class="nav-link {{ $activeTab === 'debt' ? 'active' : '' }}" 
                wire:click="switchTab('debt')"
                type="button" 
                role="tab">
                <i class="fas fa-credit-card"></i>
                Consultar Deuda
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="orderTabsContent">
        <!-- Orders Tab -->
        <div class="tab-pane fade {{ $activeTab === 'orders' ? 'show active' : '' }}" wire:ignore.self>
            @if($show_success_message)
                <div class="success-message animate__animated animate__fadeInDown">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="success-text">
                        <strong>¡Éxito!</strong><br>
                        {{ $success_message }}
                    </div>
                    <button type="button" class="close-btn" wire:click="closeSuccessMessage">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <!-- SPA Product Catalog -->
            <div class="product-catalog-spa">
                <!-- Categories Navigation -->
                <div class="categories-nav mb-8">
                    <div class="flex flex-wrap gap-3 justify-center">
                        <button data-category="all"
                                class="category-btn active px-6 py-3 rounded-full font-semibold transition-all duration-300 transform hover:scale-105 bg-blue-500 text-white">
                            <i class="fas fa-th-large mr-2"></i>
                            Todos los Productos
                        </button>
                        
                        @foreach($categories as $category)
                        <button data-category="{{ $category->id }}"
                                class="category-btn px-6 py-3 rounded-full font-semibold transition-all duration-300 transform hover:scale-105 bg-gray-100 text-gray-700 hover:bg-gray-200">
                            <i class="fas fa-tag mr-2"></i>
                            {{ $category->name }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="products-grid">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($products as $product)
                        <div data-product-category="{{ $product->category_id }}" class="product-card-wrapper">
                            @livewire('components.product-card', ['product' => $product], key('product-'.$product->id))
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Empty State -->
                <div class="text-center py-12" style="display: none;" id="empty-state">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay productos en esta categoría</h3>
                    <p class="text-gray-500">Selecciona otra categoría para ver más productos</p>
                </div>
            </div>

            <!-- Checkout Form (Hidden by default, shown when cart has items) -->
            <div class="checkout-section mt-8" x-data="{ showCheckout: false }" x-show="showCheckout">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Finalizar Pedido
                    </h3>
                    
                    <form wire:submit.prevent="submitOrder">
                <!-- Phone Number with Search Button -->
                <div class="form-group">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone"></i>
                        Número de Teléfono
                    </label>
                    <div class="input-group">
                        <input 
                            type="tel" 
                            id="phone"
                            class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" 
                            wire:model="phone"
                            placeholder="Ej: 04141234567"
                            maxlength="15"
                            autocomplete="off">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary" wire:click="searchCustomer">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Ingresa tu número de teléfono y haz clic en la lupa para buscar si ya eres cliente.
                    </small>
                </div>

                <!-- Customer Information -->
                @if($customer_exists && $existing_customer)
                    <div class="customer-info animate__animated animate__fadeInUp">
                        <h5><i class="fas fa-user-check"></i> Cliente Encontrado</h5>
                        <p><strong>Nombre:</strong> {{ $existing_customer->name }}</p>
                        <p><strong>Deuda Actual:</strong> <span class="{{ $existing_customer->total_debt > 0 ? "text-danger" : "text-success" }}">${{ number_format($existing_customer->total_debt, 2) }}</span></p>
                    </div>
                @elseif($phone && !$customer_exists && $existing_customer === null)
                    <div class="animate__animated animate__fadeInUp">
                        <!-- Customer Name -->
                        <div class="form-group">
                            <label for="customer_name" class="form-label">
                                <i class="fas fa-user"></i>
                                Nombre Completo
                            </label>
                            <input 
                                type="text" 
                                id="customer_name"
                                class="form-control {{ $errors->has('customer_name') ? 'is-invalid' : '' }}" 
                                wire:model="customer_name"
                                placeholder="Ej: Juan Pérez">
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Department -->
                        <div class="form-group">
                            <label for="department" class="form-label">
                                <i class="fas fa-building"></i>
                                Departamento
                            </label>
                            <input 
                                type="text" 
                                id="department"
                                class="form-control {{ $errors->has('department') ? 'is-invalid' : '' }}" 
                                wire:model="department"
                                placeholder="Ej: Ventas, Administración, etc.">
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endif

                @if($phone && ($customer_exists || (!$customer_exists && $existing_customer === null)))
                    <!-- Product Selection -->
                    <div class="form-group animate__animated animate__fadeInUp">
                        <label for="product_id" class="form-label">
                            <i class="fas fa-box"></i>
                            Producto
                        </label>
                        <select 
                            id="product_id"
                            class="form-select {{ $errors->has('product_id') ? 'is-invalid' : '' }}" 
                            wire:model.live="product_id">
                            <option value="">Selecciona un producto</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }} - ${{ number_format($product->sale_price, 2) }}
                                    (Stock: {{ $product->stock }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($selected_product)
                        <div class="animate__animated animate__fadeInUp">
                            <!-- Stock Information -->
                            <div class="stock-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Stock disponible:</strong> {{ $selected_product->stock }} unidades
                                <br>
                                <strong>Precio unitario:</strong> ${{ number_format($selected_product->sale_price, 2) }}
                            </div>

                            <!-- Quantity -->
                            <div class="form-group">
                                <label for="quantity" class="form-label">
                                    <i class="fas fa-sort-numeric-up"></i>
                                    Cantidad
                                </label>
                                <div class="quantity-controls">
                                    <button 
                                        type="button" 
                                        class="quantity-btn" 
                                        wire:click="$set('quantity', {{ max(1, $quantity - 1) }})"
                                        {{ $quantity <= 1 ? 'disabled' : '' }}>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input 
                                        type="number" 
                                        class="quantity-input {{ $errors->has('quantity') ? 'is-invalid' : '' }}" 
                                        wire:model.live="quantity"
                                        min="1" 
                                        max="{{ $selected_product->stock }}">
                                    <button 
                                        type="button" 
                                        class="quantity-btn" 
                                        wire:click="$set('quantity', {{ min($selected_product->stock, $quantity + 1) }})"
                                        {{ $quantity >= $selected_product->stock ? 'disabled' : '' }}>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="form-group">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note"></i>
                                    Notas (Opcional)
                                </label>
                                <textarea 
                                    id="notes"
                                    class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}" 
                                    wire:model="notes"
                                    rows="3"
                                    placeholder="Especifica qué chucherías deseas: Ping Pong, Bolero, Miramar, Gomitas, Granola, Maní Salado, Maní Japonés, Dandys, etc."></textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-lightbulb"></i>
                                    Si seleccionaste "Chucherías", especifica cuáles deseas en este campo.
                                </small>
                            </div>

                            <!-- Total Display -->
                            @if($total_price > 0)
                                <div class="total-display animate__animated animate__pulse">
                                    <div class="total-amount">${{ number_format($total_price, 2) }}</div>
                                    <div class="total-label">Total a Pagar</div>
                                </div>
                            @endif

                            <!-- Submit Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane"></i>
                                    Enviar Pedido
                                </button>
                            </div>
                        </div>
                    @endif
                @endif

                @error('general')
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </form>
        </div>

        <!-- Debt Tab -->
        <div class="tab-pane fade {{ $activeTab === 'debt' ? 'show active' : '' }}" wire:ignore.self>
            <div class="form-group">
                <label for="lookup_phone" class="form-label">
                    <i class="fas fa-search"></i>
                    Número de Teléfono
                </label>
                <div class="input-group">
                    <input 
                        type="tel" 
                        id="lookup_phone"
                        class="form-control {{ $errors->has('lookup_phone') ? 'is-invalid' : '' }}" 
                        wire:model="lookup_phone"
                        placeholder="Ej: 04141234567"
                        maxlength="15"
                        autocomplete="off">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-primary" wire:click="searchCustomerDebt">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                @error('lookup_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Ingresa tu número de teléfono y haz clic en la lupa para consultar tu deuda actual.
                </small>
            </div>

            @if($lookup_customer)
                <div class="debt-result animate__animated animate__fadeInUp">
                    <div class="customer-info">
                        <h5><i class="fas fa-user"></i> {{ $lookup_customer->name }}</h5>
                        <p><strong>Teléfono:</strong> {{ $lookup_customer->phone }}</p>
                        @if($lookup_customer->email)
                            <p><strong>Deuda Actual:</strong> {{ $lookup_customer->email }}</p>
                        @endif
                    </div>

                    <div class="debt-amount {{ $customer_debt > 0 ? 'has-debt' : 'no-debt' }}">
                        ${{ number_format($customer_debt, 2) }}
                    </div>
                    
                    @if($customer_debt > 0)
                        <h4 class="text-danger">Tienes una deuda pendiente</h4>
                        <div class="debt-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Importante:</strong> Esta deuda de ${{ number_format($customer_debt, 2) }} 
                            está calculada a tasa BCV (Banco Central de Venezuela). 
                            Contáctanos para coordinar el pago.
                        </div>
                    @else
                        <h4 class="text-success">¡No tienes deudas pendientes!</h4>
                        <p class="text-muted">Tu cuenta está al día. Puedes hacer nuevos pedidos sin problemas.</p>
                    @endif
                </div>
            @elseif($lookup_phone && $lookup_customer === null)
                <div class="debt-result animate__animated animate__fadeInUp">
                    <div class="text-center">
                        <i class="fas fa-user-slash" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h4>Cliente no encontrado</h4>
                        <p class="text-muted">
                            No encontramos ningún cliente registrado con este número de teléfono.
                            <br>
                            Si eres nuevo, puedes hacer tu primer pedido en la pestaña "Hacer Pedido".
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
