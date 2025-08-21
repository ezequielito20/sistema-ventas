/**
 * SPA de Creación de Ventas con Alpine.js
 * Archivo: public/js/admin/sales/create.js
 * Versión: 1.0.0 - SPA Edition
 */

// Esperar a que Alpine.js esté disponible
document.addEventListener('alpine:init', () => {
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
        
        // Productos en la venta
        saleItems: [],
        
        // Búsqueda y filtros
        codeSuggestions: [],
        productSearchTerm: '',
        stockFilter: 'all',
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
                
                // Configurar persistencia automática
                this.setupAutoSave();
                
                console.log('✅ SPA inicializado correctamente');
                
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
            let filtered = [...this.productsCache];
            
            // Filtro por término de búsqueda
            if (this.productSearchTerm.trim()) {
                const term = this.productSearchTerm.toLowerCase().trim();
                filtered = filtered.filter(product => 
                    product.code.toLowerCase().includes(term) ||
                    product.name.toLowerCase().includes(term) ||
                    (product.category?.name || '').toLowerCase().includes(term)
                );
            }
            
            // Filtro por stock
            switch (this.stockFilter) {
                case 'available':
                    filtered = filtered.filter(product => product.stock > 0);
                    break;
                case 'low':
                    filtered = filtered.filter(product => product.stock > 0 && product.stock <= 10);
                    break;
                case 'all':
                default:
                    break;
            }
            
            // Ocultar productos ya agregados
            filtered = filtered.filter(product => !this.isProductInSale(product.id));
            
            this.filteredProducts = filtered;
        },
        
        filterByStock(filter) {
            this.stockFilter = filter;
            this.filterProducts();
        },
        
        // ===== GESTIÓN DE PRODUCTOS EN LA VENTA =====
        addProductToSale(product) {
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
            this.updateTotal();
            this.saveToLocalStorage();
        },
        
        increaseQuantity(index) {
            const item = this.saleItems[index];
            if (item.quantity < item.stock) {
                item.quantity++;
                this.updateItemSubtotal(index);
            }
        },
        
        decreaseQuantity(index) {
            const item = this.saleItems[index];
            if (item.quantity > 1) {
                item.quantity--;
                this.updateItemSubtotal(index);
            }
        },
        
        updateItemSubtotal(index) {
            const item = this.saleItems[index];
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
                this.showAlert('Debe seleccionar un cliente', 'warning');
                return false;
            }
            
            if (this.saleItems.length === 0) {
                this.showAlert('Debe agregar al menos un producto', 'warning');
                return false;
            }
            
            if (!this.saleDate) {
                this.showAlert('Debe seleccionar una fecha', 'warning');
                return false;
            }
            
            if (!this.saleTime) {
                this.showAlert('Debe seleccionar una hora', 'warning');
                return false;
            }
            
            // Validar stock en tiempo real
            for (const item of this.saleItems) {
                const product = this.productsCache.find(p => p.id === item.id);
                if (!product) {
                    this.showAlert(`Producto "${item.name}" no encontrado en el inventario`, 'error');
                    return false;
                }
                
                if (item.quantity > product.stock) {
                    this.showToast(`Stock Insuficiente`, `Stock insuficiente para "${item.name}". Disponible: ${product.stock}`, 'error', 2000);
                    return false;
                }
            }
            
            return true;
        },
        
        // ===== PROCESAMIENTO DE VENTA =====
        async processSale(action = 'save') {
            if (!this.validateSale()) return;
            
            const confirmed = await this.showConfirmDialog(
                '¿Confirmar venta?',
                `Total: {{ $currency->symbol }} ${this.totalAmount.toFixed(2)}\nProductos: ${this.saleItems.length}`
            );
            
            if (!confirmed) return;
            
            try {
                this.loading = true;
                
                const formData = new FormData();
                formData.append('customer_id', this.selectedCustomerId);
                formData.append('sale_date', this.saleDate);
                formData.append('sale_time', this.saleTime);
                formData.append('already_paid', this.alreadyPaid);
                formData.append('note', this.saleNote);
                formData.append('total_price', this.totalAmount);
                formData.append('action', action);
                
                // Agregar productos
                this.saleItems.forEach((item, index) => {
                    formData.append(`sale_details[${index}][product_id]`, item.id);
                    formData.append(`sale_details[${index}][quantity]`, item.quantity);
                    formData.append(`sale_details[${index}][unit_price]`, item.price);
                    formData.append(`sale_details[${index}][subtotal]`, item.subtotal);
                });
                
                const response = await fetch(window.saleCreateRoutes?.store || '/sales', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Error al procesar la venta');
                }
                
                this.showAlert('Venta procesada correctamente', 'success');
                
                // Limpiar datos locales
                this.clearLocalStorage();
                
                // Redirigir según la acción
                if (action === 'save_and_new') {
                    window.location.reload();
                } else {
                    window.location.href = (window.saleCreateRoutes && window.saleCreateRoutes.index) ? window.saleCreateRoutes.index : '/sales';
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
                        this.saleItems = data.saleItems || [];
                        
                        // Mostrar notificación si se cargaron productos automáticamente
                        if (this.saleItems.length > 0) {
                            setTimeout(() => {
                                this.showToast('Venta Recuperada', `${this.saleItems.length} producto(s) cargado(s) automáticamente`, 'info', 2000);
                            }, 500); // Pequeño delay para que se vea después de la inicialización
                        }
                        
                        console.log('📦 Datos recuperados de localStorage');
                    } else {
                        this.clearLocalStorage();
                    }
                }
            } catch (error) {
                console.warn('Error cargando datos de localStorage:', error);
                this.clearLocalStorage();
            }
        },
        
        clearLocalStorage() {
            try {
                localStorage.removeItem('saleCreateData');
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
        
        // ===== EVENTOS =====
        onCustomerChange() {
            this.saveToLocalStorage();
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
        
        showConfirmDialog(title, text) {
            return new Promise((resolve) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: title,
                        text: text,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#667eea',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, confirmar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        resolve(result.isConfirmed);
                    });
                } else {
                    resolve(confirm(`${title}\n${text}`));
                }
            });
        }
    }));
});
