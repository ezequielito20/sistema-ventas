/**
 * SPA de Edición de Ventas con Alpine.js
 * Archivo: public/js/admin/sales/edit.js
 * Versión: 1.0.0 - SPA Edition
 */

// Esperar a que Alpine.js esté disponible
document.addEventListener('alpine:init', () => {
    
    Alpine.data('saleEditSPA', () => ({
        // ===== ESTADO DEL COMPONENTE =====
        loading: false,
        
        // Datos del formulario
        productCode: '',
        selectedCustomerId: '',
        saleDate: '',
        saleTime: '',
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
        
        // Selects personalizados
        customerOptions: [],
        
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
        
        get hasProducts() {
            return this.saleItems.length > 0;
        },
        
        // ===== INICIALIZACIÓN =====
        async init() {
            try {
                // Cargar datos iniciales
                if (window.saleEditData) {
                    this.productsCache = window.saleEditData.products || [];
                    this.filteredProducts = [...this.productsCache];
                    this.selectedCustomerId = window.saleEditData.selectedCustomerId || '';
                    this.saleDate = window.saleEditData.saleDate || '';
                    this.saleTime = window.saleEditData.saleTime || '';
                    this.saleNote = window.saleEditData.saleNote || '';
                    
                    // Transformar los saleItems del servidor al formato del SPA
                    if (window.saleEditData.saleItems && window.saleEditData.saleItems.length > 0) {
                        this.saleItems = window.saleEditData.saleItems.map(item => ({
                            id: item.product_id,
                            code: item.code,
                            name: item.name,
                            price: parseFloat(item.sale_price || 0),
                            stock: Number(item.stock) || 0,
                            quantity: Number(item.quantity) || 1,
                            subtotal: parseFloat(item.sale_price || 0) * Number(item.quantity || 1),
                            category: { name: item.category || 'Sin categoría' }
                        }));
                    }
                }
                
                // Configurar selects personalizados
                this.setupCustomSelects();
                
                // Inicializar el cliente seleccionado con un pequeño delay
                setTimeout(() => {
                    this.initializeSelectedCustomer();
                }, 100);
                
                // Configurar persistencia automática
                this.setupAutoSave();
                
            } catch (error) {
                console.error('❌ Error inicializando SPA:', error);
                this.showAlert('Error al inicializar el sistema', 'error');
            }
        },

        setupCustomSelects() {
            // Configurar select de clientes
            this.customerOptions = window.saleEditData.customers.map(customer => ({
                value: customer.id,
                text: customer.name,
                debt: customer.total_debt || 0
            }));
        },

        initializeSelectedCustomer() {
            if (!this.selectedCustomerId || !window.saleEditData.customers) {
                return;
            }

            const selectedCustomer = window.saleEditData.customers.find(c => c.id == this.selectedCustomerId);
            if (!selectedCustomer) {
                return;
            }

            // Función para intentar inicializar el cliente
            const tryInitialize = () => {
                // Buscar el contenedor del select de cliente
                const customerContainer = this.$el.querySelector('[x-data*="selectedCustomerName"]');
                if (!customerContainer) {
                    // Si no se encuentra, intentar de nuevo en 50ms
                    setTimeout(tryInitialize, 50);
                    return;
                }

                // Obtener los datos de Alpine.js del contenedor
                const customerData = Alpine.$data(customerContainer);
                if (!customerData) {
                    // Si no hay datos de Alpine, intentar de nuevo en 50ms
                    setTimeout(tryInitialize, 50);
                    return;
                }

                // Actualizar los valores del select
                customerData.selectedCustomerName = selectedCustomer.name;
                customerData.selectedCustomerDebt = parseFloat(selectedCustomer.total_debt || 0);

                // Forzar la actualización de la vista
                this.$nextTick(() => {
                    customerData.selectedCustomerName = selectedCustomer.name;
                    customerData.selectedCustomerDebt = parseFloat(selectedCustomer.total_debt || 0);
                });

            };

            // Iniciar el proceso de inicialización
            tryInitialize();
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
            
            // Filtro por término de búsqueda en tiempo real
            if (this.productSearchTerm && this.productSearchTerm.trim()) {
                const term = this.productSearchTerm.toLowerCase().trim();
                
                filtered = filtered.filter(product => 
                    product.code.toLowerCase().includes(term) ||
                    product.name.toLowerCase().includes(term) ||
                    (product.category?.name || '').toLowerCase().includes(term)
                );
            }
            
            // Mostrar todos los productos, pero marcar los que ya están en la venta
            this.filteredProducts = filtered;
        },
        
        clearSearch() {
            this.productSearchTerm = '';
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
        async processSale() {
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
                                <span class="font-bold text-lg text-green-600">${window.saleEditData?.currency?.symbol || '$'} ${this.totalAmount.toFixed(2)}</span>
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
                                        <span class="font-medium">${window.saleEditData?.currency?.symbol || '$'} ${item.subtotal.toFixed(2)}</span>
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
                        <p class="text-sm text-green-700">¿Está seguro de que desea actualizar esta venta?</p>
                    </div>
                </div>
            `;
        
            const confirmed = await this.showConfirmDialog(
                '¿Confirmar Actualización?',
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
                formData.append('total_price', this.totalAmount);
                formData.append('note', this.saleNote || '');
                formData.append('_method', 'PUT');
                
                // Agregar productos
                this.saleItems.forEach((item, index) => {
                    formData.append(`items[${item.id}][product_id]`, item.id);
                    formData.append(`items[${item.id}][quantity]`, item.quantity);
                });
                
                const url = window.saleEditRoutes?.update || `/sales/update/${window.saleEditData.saleId}`;
                const response = await fetch(url, {
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
                    throw new Error(data.message || 'Error al actualizar la venta');
                }
                
                if (data.success) {
                    // Limpiar localStorage
                    this.clearLocalStorage();
                    
                    // Redirigir al index con mensaje de éxito
                    window.location.href = '/sales?sale_updated=true';
                } else {
                    throw new Error(data.message || 'Error al actualizar la venta');
                }
                
            } catch (error) {
                console.error('❌ Error actualizando venta:', error);
                this.showAlert('Error al actualizar la venta: ' + error.message, 'error');
            } finally {
                this.loading = false;
            }
        },
        
        cancelSale() {
            this.showConfirmDialog(
                '¿Cancelar edición?',
                'Se perderán todos los cambios no guardados'
            ).then(confirmed => {
                if (confirmed) {
                    this.clearLocalStorage();
                    window.location.href = '/sales';
                }
            });
        },

        hasUnsavedChanges() {
            // Comparar el estado actual con los datos originales
            const originalData = window.saleEditData;
            
            // Verificar cambios en datos básicos
            if (this.selectedCustomerId != originalData.selectedCustomerId) return true;
            if (this.saleDate != originalData.saleDate) return true;
            if (this.saleTime != originalData.saleTime) return true;
            if (this.saleNote != originalData.saleNote) return true;
            
            // Verificar cambios en items
            const originalItems = originalData.saleItems || [];
            if (this.saleItems.length !== originalItems.length) return true;
            
            // Verificar cada item
            for (let i = 0; i < this.saleItems.length; i++) {
                const currentItem = this.saleItems[i];
                const originalItem = originalItems[i];
                
                if (!originalItem) return true;
                if (currentItem.product_id != originalItem.product_id) return true;
                if (currentItem.quantity != originalItem.quantity) return true;
                if (parseFloat(currentItem.price) != parseFloat(originalItem.sale_price || 0)) return true;
            }
            
            return false;
        },

        goBack() {
            // Verificar si hay cambios sin guardar
            const hasChanges = this.hasUnsavedChanges();
            
            if (hasChanges) {
                this.showConfirmDialog(
                    '¿Salir sin guardar?',
                    'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?'
                ).then(confirmed => {
                    if (confirmed) {
                        this.clearLocalStorage();
                        window.location.href = '/sales';
                    }
                });
            } else {
                // No hay cambios, redirigir directamente
                window.location.href = '/sales';
            }
        },
        
        // ===== PERSISTENCIA LOCAL =====
        saveToLocalStorage() {
            try {
                const data = {
                    selectedCustomerId: this.selectedCustomerId,
                    saleDate: this.saleDate,
                    saleTime: this.saleTime,
                    saleNote: this.saleNote,
                    saleItems: this.saleItems,
                    timestamp: Date.now()
                };
                
                localStorage.setItem('saleEditData', JSON.stringify(data));
            } catch (error) {
                console.warn('No se pudo guardar en localStorage:', error);
            }
        },
        
        loadFromLocalStorage() {
            try {
                const saved = localStorage.getItem('saleEditData');
                if (saved) {
                    const data = JSON.parse(saved);
                    
                    // Solo cargar si los datos tienen menos de 1 hora
                    const oneHour = 60 * 60 * 1000;
                    if (Date.now() - data.timestamp < oneHour) {
                        this.selectedCustomerId = data.selectedCustomerId || this.selectedCustomerId;
                        this.saleDate = data.saleDate || this.saleDate;
                        this.saleTime = data.saleTime || this.saleTime;
                        this.saleNote = data.saleNote || this.saleNote;
                        
                        // Actualizar saleItems de forma más directa
                        if (data.saleItems && data.saleItems.length > 0) {
                            this.saleItems = [...data.saleItems];
                        }
                        
                        // Forzar actualización de la vista
                        this.forceViewUpdate();
                        
                        // Mostrar notificación si se cargaron productos automáticamente
                        if (this.saleItems.length > 0) {
                            setTimeout(() => {
                                this.showToast('Cambios Recuperados', `${this.saleItems.length} producto(s) cargado(s) automáticamente`, 'info', 2000);
                            }, 500);
                        }
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
                localStorage.removeItem('saleEditData');
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
                return '/img/no-image.svg';
            }
            
            // Si ya tiene image_url, usarla
            if (product.image_url && product.image_url !== 'null' && product.image_url !== '') {
                return product.image_url;
            }
            
            // Si tiene image, construir la URL
            if (product.image && product.image !== 'null' && product.image !== '') {
                const imageUrl = `/storage/products/${product.image}`;
                return imageUrl;
            }
            
            // Fallback a imagen por defecto
            return '/img/no-image.svg';
        },
        
        // Función para forzar actualización de la vista
        forceViewUpdate() {
            // Forzar re-evaluación de computed properties
            this.$nextTick(() => {
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
                return Swal.fire({
                    title: type === 'success' ? '¡Éxito!' : type === 'error' ? 'Error' : 'Información',
                    text: message,
                    icon: type,
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: type === 'success' ? '#10b981' : '#667eea',
                    timer: type === 'success' ? 5000 : undefined,
                    timerProgressBar: type === 'success',
                    showConfirmButton: type !== 'success',
                    customClass: {
                        popup: 'rounded-xl shadow-2xl',
                        title: 'text-xl font-bold text-gray-800',
                        confirmButton: 'rounded-lg px-6 py-3 font-medium'
                    }
                });
            } else {
                alert(message);
                return Promise.resolve();
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
                        confirmButtonText: 'Sí, Actualizar Venta',
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
        }
    }));
});

// Función global para agregar productos desde el modal
window.addProductFromModal = function(code, id, name, image, stock, price, category) {
    const editComponent = Alpine.$data(document.getElementById('saleEditRoot'));
    if (editComponent) {
        const product = {
            id: parseInt(id),
            code: code,
            name: name,
            image_url: image,
            stock: parseInt(stock),
            sale_price: parseFloat(price),
            category: { name: category }
        };
        editComponent.addProductToSale(product);
    }
};
