/**
 * SPA de Creación de Ventas con Alpine.js
 * Archivo: public/js/admin/sales/create.js
 * Versión: 1.0.0 - SPA Edition
 */

// Esperar a que Alpine.js esté disponible
document.addEventListener('alpine:init', () => {
    console.log('🎯 Alpine.js inicializado - Registrando componente saleCreateSPA');
    
    Alpine.data('saleCreateSPA', () => ({
        // ===== ESTADO DEL COMPONENTE =====
        loading: false,
        
        // Datos del formulario
        productCode: '',
        selectedCustomerId: '',
        saleDate: '',
        saleTime: '',
        alreadyPaid: '0',
        saleNote: '',
        
        // Productos en la venta - Usar reactive array
        saleItems: [],
        
        // Búsqueda y filtros
        codeSuggestions: [],
        productSearchTerm: '',
        searchModalOpen: false,
        
        // Cache de productos
        productsCache: [],
        filteredProducts: [],
        
        // Sistema de notificaciones
        notifications: [],
        
        // ===== COMPUTED PROPERTIES =====
        get totalAmount() {
            return this.saleItems.reduce((total, item) => total + item.subtotal, 0);
        },
        
        get canProcessSale() {
            return this.selectedCustomerId && 
                   this.saleItems.length > 0 && 
                   this.saleDate && 
                   this.saleTime;
        },
        
        // Función para verificar si hay productos
        get hasProducts() {
            const hasProducts = this.saleItems.length > 0;
            console.log('🔍 Verificando productos:', hasProducts, 'Cantidad:', this.saleItems.length);
            return hasProducts;
        },
        
        // Watcher para saleItems
        get saleItemsWatcher() {
            // Esta función se ejecuta cada vez que saleItems cambia
            console.log('👀 saleItems cambió - Nueva longitud:', this.saleItems.length);
            return this.saleItems.length;
        },
        
        // ===== INICIALIZACIÓN =====
        async init() {
            try {
                console.log('🚀 Inicializando SPA de Creación de Ventas...');
                
                // Cargar datos iniciales
                if (window.saleCreateData) {
                    this.productsCache = window.saleCreateData.products || [];
                    this.filteredProducts = [...this.productsCache];
                    this.selectedCustomerId = window.saleCreateData.selectedCustomerId || '';
                    this.saleDate = new Date().toISOString().split('T')[0];
                    this.saleTime = new Date().toTimeString().slice(0, 5);
                    
                    console.log('📦 Datos cargados:', this.productsCache.length, 'productos');
                }
                
                // Cargar datos guardados localmente
                this.loadFromLocalStorage();
                
                // Auto-agregar producto si hay solo uno con stock > 0 y no hay productos en la venta
                this.autoAddSingleProduct();
                
                // Configurar persistencia automática
                this.setupAutoSave();
                
                console.log('✅ SPA inicializado correctamente');
                console.log('📊 Estado final - saleItems:', this.saleItems.length, 'productos');
                
            } catch (error) {
                console.error('❌ Error inicializando SPA:', error);
                this.showAlert('Error al inicializar el sistema', 'error');
            }
        },
        
        // ===== BÚSQUEDA Y AUTocompletado =====
        searchProductByCode() {
            if (!this.productCode.trim() || this.productCode.length < 2) {
                this.codeSuggestions = [];
                return;
            }
            
            const term = this.productCode.toLowerCase().trim();
            const suggestions = this.productsCache
                .filter(product => 
                    product.code.toLowerCase().includes(term) ||
                    product.name.toLowerCase().includes(term)
                )
                .slice(0, 5)
                .map(product => ({
                    code: product.code,
                    name: product.name,
                    product: product
                }));
            
            this.codeSuggestions = suggestions;
        },
        
        selectCodeSuggestion(suggestion) {
            this.productCode = suggestion.code;
            this.codeSuggestions = [];
            this.addProductToSale(suggestion.product);
        },
        
        addProductByCode() {
            if (!this.productCode.trim()) return;
            
            const product = this.productsCache.find(p => 
                p.code.toLowerCase() === this.productCode.toLowerCase().trim()
            );
            
            if (product) {
                this.addProductToSale(product);
                this.productCode = '';
            } else {
                this.showToast('Producto No Encontrado', 'El código ingresado no corresponde a ningún producto', 'warning', 2000);
            }
        },
        
        // ===== FILTRADO DE PRODUCTOS =====
        filterProducts() {
            console.log('🔍 Filtrando productos...');
            console.log('🔍 Término de búsqueda:', this.productSearchTerm);
            console.log('🔍 Productos en caché:', this.productsCache.length);
            console.log('🔍 Productos en venta actual:', this.saleItems.length);
            
            let filtered = [...this.productsCache];
            
            // Filtro por término de búsqueda en tiempo real
            if (this.productSearchTerm && this.productSearchTerm.trim()) {
                const term = this.productSearchTerm.toLowerCase().trim();
                console.log('🔍 Aplicando filtro de búsqueda con término:', term);
                
                filtered = filtered.filter(product => 
                    product.code.toLowerCase().includes(term) ||
                    product.name.toLowerCase().includes(term) ||
                    (product.category?.name || '').toLowerCase().includes(term)
                );
                
                console.log('🔍 Productos después del filtro de búsqueda:', filtered.length);
            } else {
                console.log('🔍 No hay término de búsqueda, mostrando todos los productos');
            }
            
            // Mostrar todos los productos, pero marcar los que ya están en la venta
            // Los productos ya agregados aparecerán pero estarán deshabilitados
            this.filteredProducts = filtered;
            console.log('🔍 Productos filtrados finales:', this.filteredProducts.length);
            console.log('🔍 Productos disponibles para agregar:', this.filteredProducts.filter(p => !this.isProductInSale(p.id)).length);
        },
        
        // Función para limpiar la búsqueda
        clearSearch() {
            console.log('🧹 Limpiando búsqueda...');
            this.productSearchTerm = '';
            this.filterProducts();
        },
        

        
        // ===== GESTIÓN DE PRODUCTOS EN LA VENTA =====
        addProductToSale(product) {
            console.log('➕ Agregando producto a la venta:', product.name);
            console.log('➕ Estado actual de saleItems:', this.saleItems.length);
            
            // Validar stock
            if (product.stock <= 0) {
                this.showToast('Sin Stock', 'Este producto no tiene stock disponible', 'warning', 2000);
                return;
            }

            // Verificar si ya está en la venta
            if (this.isProductInSale(product.id)) {
                this.showToast('Producto Duplicado', 'Este producto ya está en la venta', 'info', 2000);
                return;
            }

            // Agregar producto
            const saleItem = {
                id: product.id,
                code: product.code,
                name: product.name,
                price: parseFloat(product.sale_price || product.price || 0),
                stock: Number(product.stock) || 0,
                quantity: 1,
                subtotal: parseFloat(product.sale_price || product.price || 0),
                category: product.category
            };
            
            this.saleItems.push(saleItem);
            console.log('➕ Producto agregado. Nuevo estado de saleItems:', this.saleItems.length);
            
            // Forzar actualización de la vista
            this.forceViewUpdate();
            
            this.updateTotal();
            this.saveToLocalStorage();
            
            // Cerrar modal si está abierto
            if (this.searchModalOpen) {
                this.searchModalOpen = false;
            }
            
            this.showToast('Producto Agregado', `"${product.name}" agregado correctamente`, 'success', 1500);
        },
        
        removeItem(index) {
            this.saleItems.splice(index, 1);
            console.log('🗑️ Producto removido. Nuevo estado de saleItems:', this.saleItems.length);
            
            // Forzar actualización de la vista
            this.forceViewUpdate();
            
            this.updateTotal();
            this.saveToLocalStorage();
        },
        
        increaseQuantity(index) {
            const item = this.saleItems[index];
            if (item.quantity < item.stock) {
                item.quantity++;
                item.subtotal = item.price * item.quantity;
                this.updateTotal();
                this.saveToLocalStorage();
            }
        },
        
        decreaseQuantity(index) {
            const item = this.saleItems[index];
            if (item.quantity > 1) {
                item.quantity--;
                item.subtotal = item.price * item.quantity;
                this.updateTotal();
                this.saveToLocalStorage();
            }
        },
        
        updateItemSubtotal(index) {
            const item = this.saleItems[index];
            // Solo actualizar subtotal sin validaciones
            item.subtotal = item.price * item.quantity;
            this.updateTotal();
            this.saveToLocalStorage();
        },
        
        updateTotal() {
            // El total se calcula automáticamente con la computed property
            this.saveToLocalStorage();
        },
        
        isProductInSale(productId) {
            return this.saleItems.some(item => item.id === productId);
        },
        
        // ===== VALIDACIONES =====
        validateSale() {
            if (!this.selectedCustomerId) {
                this.showToast('Cliente Requerido', 'Debe seleccionar un cliente', 'warning', 2000);
                return false;
            }

            if (this.saleItems.length === 0) {
                this.showToast('Productos Requeridos', 'Debe agregar al menos un producto', 'warning', 2000);
                return false;
            }

            if (!this.saleDate) {
                this.showToast('Fecha Requerida', 'Debe seleccionar una fecha', 'warning', 2000);
                return false;
            }

            if (!this.saleTime) {
                this.showToast('Hora Requerida', 'Debe seleccionar una hora', 'warning', 2000);
                return false;
            }
            
            // Validar stock solo al procesar la venta
            for (const item of this.saleItems) {
                const product = this.productsCache.find(p => p.id === item.id);
                if (!product) {
                    this.showToast('Producto No Encontrado', `Producto "${item.name}" no encontrado en el inventario`, 'error', 2000);
                    return false;
                }
                
                if (item.quantity > product.stock) {
                    this.showToast('Stock Insuficiente', `Stock insuficiente para "${item.name}". Disponible: ${product.stock}`, 'error', 2000);
                    return false;
                }
            }
            
            return true;
        },
        
        // ===== PROCESAMIENTO DE VENTA =====
        async processSale(action = 'save') {
            if (!this.validateSale()) return;
            
            // Crear HTML personalizado para la confirmación
            const saleDetailsHTML = `
                <div class="text-left">
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-gray-800 mb-2">📋 Resumen de la Venta</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Productos:</span>
                                <span class="font-medium">${this.saleItems.length}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total:</span>
                                <span class="font-bold text-lg text-green-600">${window.saleCreateData?.currency?.symbol || '$'} ${this.totalAmount.toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-3 mb-4">
                        <h5 class="font-medium text-blue-800 mb-2">📦 Productos en la Venta:</h5>
                        <div class="space-y-1 max-h-32 overflow-y-auto">
                            ${this.saleItems.map(item => `
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-700">${item.name}</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-500">x${item.quantity}</span>
                                        <span class="font-medium">${window.saleCreateData?.currency?.symbol || '$'} ${item.subtotal.toFixed(2)}</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    
                    ${this.saleNote ? `
                        <div class="bg-yellow-50 rounded-lg p-3 mb-4">
                            <h5 class="font-medium text-yellow-800 mb-1">📝 Nota:</h5>
                            <p class="text-sm text-yellow-700">${this.saleNote}</p>
                        </div>
                    ` : ''}
                    
                    <div class="bg-green-50 rounded-lg p-3">
                        <h5 class="font-medium text-green-800 mb-1">✅ Confirmación</h5>
                        <p class="text-sm text-green-700">¿Está seguro de que desea procesar esta venta?</p>
            </div>
            </div>
        `;
        
            const confirmed = await this.showConfirmDialog(
                '¿Confirmar Venta?',
                saleDetailsHTML,
                'html'
            );
            
            if (!confirmed) return;
            
            this.loading = true;
            
            try {
                const formData = new FormData();
                
                // Datos básicos de la venta
                formData.append('customer_id', this.selectedCustomerId);
                formData.append('sale_date', this.saleDate);
                formData.append('sale_time', this.saleTime);
                formData.append('already_paid', this.alreadyPaid);
                formData.append('total_price', this.totalAmount);
                formData.append('note', this.saleNote || '');
                formData.append('action', action);
                
                // Agregar productos
                this.saleItems.forEach((item, index) => {
                    formData.append(`sale_details[${index}][product_id]`, item.id);
                    formData.append(`sale_details[${index}][quantity]`, item.quantity);
                    formData.append(`sale_details[${index}][unit_price]`, item.price);
                    formData.append(`sale_details[${index}][subtotal]`, item.subtotal);
                });
                
                const response = await fetch(window.saleCreateRoutes?.store || '/sales/create', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Error al procesar la venta');
                }
                
                if (data.success) {
                    this.showAlert('Venta procesada correctamente', 'success');
                    
                    // Limpiar datos locales
                    this.clearLocalStorage();
                    
                    // Redirigir según la acción
                    if (action === 'save_and_new') {
                        window.location.reload();
                    } else {
                        window.location.href = data.redirect_url || (window.saleCreateRoutes && window.saleCreateRoutes.index) || '/sales';
                    }
            } else {
                    throw new Error(data.message || 'Error al procesar la venta');
                }
                
            } catch (error) {
                console.error('❌ Error procesando venta:', error);
                this.showAlert('Error al procesar la venta: ' + error.message, 'error');
            } finally {
                this.loading = false;
            }
        },
        
        cancelSale() {
            this.showConfirmDialog(
                '¿Cancelar venta?',
                'Se perderán todos los datos no guardados'
            ).then(confirmed => {
                if (confirmed) {
                    this.clearLocalStorage();
                    window.location.href = '{{ route("admin.sales.index") }}';
                }
            });
        },
        
        // ===== PERSISTENCIA LOCAL =====
        saveToLocalStorage() {
            try {
                const data = {
                    selectedCustomerId: this.selectedCustomerId,
                    saleDate: this.saleDate,
                    saleTime: this.saleTime,
                    alreadyPaid: this.alreadyPaid,
                    saleNote: this.saleNote,
                    saleItems: this.saleItems,
                    timestamp: Date.now()
                };
                
                localStorage.setItem('saleCreateData', JSON.stringify(data));
            } catch (error) {
                console.warn('No se pudo guardar en localStorage:', error);
            }
        },
        
        loadFromLocalStorage() {
            try {
                const saved = localStorage.getItem('saleCreateData');
                if (saved) {
                    const data = JSON.parse(saved);
                    
                    // Solo cargar si los datos tienen menos de 1 hora
                    const oneHour = 60 * 60 * 1000;
                    if (Date.now() - data.timestamp < oneHour) {
                        this.selectedCustomerId = data.selectedCustomerId || '';
                        this.saleDate = data.saleDate || this.saleDate;
                        this.saleTime = data.saleTime || this.saleTime;
                        this.alreadyPaid = data.alreadyPaid || '0';
                        this.saleNote = data.saleNote || '';
                        
                        // Actualizar saleItems de forma más directa
                        this.saleItems.length = 0; // Limpiar array
                        if (data.saleItems && data.saleItems.length > 0) {
                            data.saleItems.forEach(item => {
                                this.saleItems.push(item);
                            });
                        }
                        
                        console.log('📦 Datos cargados de localStorage - saleItems:', this.saleItems.length);
                        
                        // Forzar actualización de la vista
                        this.forceViewUpdate();
                        
                        // Mostrar notificación si se cargaron productos automáticamente
                        if (this.saleItems.length > 0) {
                            setTimeout(() => {
                                this.showToast('Venta Recuperada', `${this.saleItems.length} producto(s) cargado(s) automáticamente`, 'info', 2000);
                            }, 500); // Pequeño delay para que se vea después de la inicialización
                        }
                        
                        console.log('📦 Datos recuperados de localStorage');
                    } else {
                        this.clearLocalStorage();
                        // Si se limpió localStorage, verificar si hay un solo producto para auto-agregar
                        setTimeout(() => {
                            this.autoAddSingleProduct();
                        }, 100);
                    }
                } else {
                    console.log('📦 No hay datos en localStorage');
                    // Si no hay datos en localStorage, verificar si hay un solo producto para auto-agregar
                    setTimeout(() => {
                        this.autoAddSingleProduct();
                    }, 100);
                }
            } catch (error) {
                console.warn('Error cargando datos de localStorage:', error);
                this.clearLocalStorage();
                // Si hay error, verificar si hay un solo producto para auto-agregar
                setTimeout(() => {
                    this.autoAddSingleProduct();
                }, 100);
            }
        },
        
        clearLocalStorage() {
            try {
                localStorage.removeItem('saleCreateData');
                // Limpiar también saleItems
                this.saleItems.length = 0;
                this.forceViewUpdate();
            } catch (error) {
                console.warn('Error limpiando localStorage:', error);
            }
        },
        
        setupAutoSave() {
            // Auto-guardar cada 30 segundos
            setInterval(() => {
                if (this.saleItems.length > 0) {
                    this.saveToLocalStorage();
                }
            }, 30000);
        },

        // ===== FUNCIONES DE UI =====
        showToast(title, message, type = 'success', duration = 1500) {
            if (typeof Swal !== 'undefined') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: duration,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
                Toast.fire({
                    icon: type,
                    title: title,
                    text: message
                });
            } else {
                // Fallback
                console.warn('SweetAlert2 no disponible, usando alerta básica');
                this.notifications.push({ title, message, type, visible: true });
                setTimeout(() => { this.notifications.shift(); }, duration);
            }
        },
        
        // Función para obtener la URL de la imagen del producto
        getProductImageUrl(product) {
            if (!product) {
                console.log('🖼️ Producto no definido, usando imagen por defecto');
                return '/img/no-image.svg';
            }
            
            console.log('🖼️ Procesando imagen para producto:', product.name, 'image_url:', product.image_url, 'image:', product.image);
            
            // Si ya tiene image_url, usarla
            if (product.image_url && product.image_url !== 'null' && product.image_url !== '') {
                console.log('🖼️ Usando image_url:', product.image_url);
                return product.image_url;
            }
            
            // Si tiene image, construir la URL
            if (product.image && product.image !== 'null' && product.image !== '') {
                const imageUrl = `/storage/products/${product.image}`;
                console.log('🖼️ Construyendo URL desde image:', imageUrl);
                return imageUrl;
            }
            
            // Fallback a imagen por defecto
            console.log('🖼️ Usando imagen por defecto para:', product.name);
            return '/img/no-image.svg';
        },
        
        // Función para forzar actualización de la vista
        forceViewUpdate() {
            // Forzar re-evaluación de computed properties
            this.$nextTick(() => {
                console.log('🔄 Forzando actualización de vista - saleItems:', this.saleItems.length);
                // Trigger un cambio mínimo para forzar la reactividad
                this.saleItems = [...this.saleItems];
            });
        },
        
        removeNotification(index) {
            this.notifications.splice(index, 1);
        },
        
        removeNotificationById(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index > -1) {
                this.notifications.splice(index, 1);
            }
        },
        
        showAlert(message, type = 'info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? '¡Éxito!' : type === 'error' ? 'Error' : 'Información',
                    text: message,
                    icon: type,
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#667eea',
                    timer: type === 'success' ? 2000 : undefined,
                    timerProgressBar: type === 'success'
                });
            } else {
                alert(message);
            }
        },
        
        showConfirmDialog(title, text, html = false) {
            return new Promise((resolve) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: title,
                        html: html ? text : null,
                        text: html ? null : text,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981', // Verde
                        cancelButtonColor: '#6b7280', // Gris
                        confirmButtonText: 'Sí, Procesar Venta',
                        cancelButtonText: 'Cancelar',
                        width: '32rem',
                        customClass: {
                            popup: 'rounded-xl shadow-2xl',
                            title: 'text-xl font-bold text-gray-800',
                            htmlContainer: 'text-left',
                            confirmButton: 'rounded-lg px-6 py-3 font-medium',
                            cancelButton: 'rounded-lg px-6 py-3 font-medium'
                        },
                        buttonsStyling: true,
                        reverseButtons: true
                    }).then((result) => {
                        resolve(result.isConfirmed);
                    });
                } else {
                    resolve(confirm(`${title}\n${html ? text.replace(/<[^>]*>/g, '') : text}`));
                }
            });
        },

        // ===== EVENTOS =====
        onCustomerChange() {
            this.saveToLocalStorage();
        },

        autoAddSingleProduct() {
            // Verificar que tenemos productos en cache
            if (!this.productsCache || this.productsCache.length === 0) {
                console.log('🔄 No hay productos en cache');
                return;
            }
            
            // Filtrar productos con stock > 0
            const availableProducts = this.productsCache.filter(product => product.stock > 0);
            console.log('🔄 Productos disponibles:', availableProducts.length, 'de', this.productsCache.length);
            console.log('🔄 Productos en venta actual:', this.saleItems.length);
            
            // Si hay exactamente un producto disponible y no hay productos en la venta
            if (availableProducts.length === 1 && this.saleItems.length === 0) {
                const product = availableProducts[0];
                console.log('🔄 Auto-agregando producto único:', product.name);
                this.addProductToSale(product);
                this.showToast('Producto Agregado', `"${product.name}" agregado automáticamente`, 'success', 1500);
            } else {
                console.log('🔄 No se auto-agrega producto - Condiciones no cumplidas');
            }
        }
    }));
});
