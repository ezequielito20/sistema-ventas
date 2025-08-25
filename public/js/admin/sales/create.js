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
        paymentOptions: [],
        
        // ===== COMPUTED PROPERTIES =====
        get totalAmount() {
            return this.saleItems.reduce((total, item) => total + item.subtotal, 0);
        },
        
        get canProcessSale() {
            // Verificar tanto la variable local como la global
            const customerSelected = this.selectedCustomerId || 
                                   (window.saleCreateData && window.saleCreateData.selectedCustomerId);
            
            return customerSelected && 
                   this.saleItems.length > 0 && 
                   this.saleDate && 
                   this.saleTime;
        },
        
        // Función para verificar si hay productos
        get hasProducts() {
            return this.saleItems.length > 0;
        },
        
        // Watcher para saleItems
        get saleItemsWatcher() {
            // Esta función se ejecuta cada vez que saleItems cambia
            return this.saleItems.length;
        },
        
        // Watcher para detectar cambios en el cliente seleccionado
        get customerWatcher() {
            // Esta función se ejecuta cada vez que cambia el cliente
            return window.saleCreateData ? window.saleCreateData.selectedCustomerId : null;
        },
        
        // ===== FUNCIONES DE FECHA Y HORA =====
        setCurrentDateTime() {
            // Usar fecha y hora local (que debería ser la fecha actual)
            const now = new Date();
            
            // Formatear fecha en formato YYYY-MM-DD
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            this.saleDate = `${year}-${month}-${day}`;
            
            // Formatear hora en formato HH:MM
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            this.saleTime = `${hours}:${minutes}`;
        },

        updateCurrentDateTime() {
            this.setCurrentDateTime();
            // Mostrar notificación
            this.showAlert('Fecha y hora actualizadas a la hora de Caracas, Venezuela', 'success');
        },

        // ===== INICIALIZACIÓN =====
        async init() {
            try {
                // Cargar datos iniciales
                if (window.saleCreateData) {
                    this.productsCache = window.saleCreateData.products || [];
                    this.filteredProducts = [...this.productsCache];
                    this.selectedCustomerId = window.saleCreateData.selectedCustomerId || '';
                    
                    // Establecer fecha y hora actual de Caracas, Venezuela
                    if (window.defaultSaleDate && window.defaultSaleTime) {
                        this.saleDate = window.defaultSaleDate;
                        this.saleTime = window.defaultSaleTime;
                    } else {
                        this.setCurrentDateTime();
                    }
                }
                
                // Configurar selects personalizados
                this.setupCustomSelects();
                
                // Cargar datos guardados localmente
                this.loadFromLocalStorage();
                
                // Procesar parámetro customer_id de la URL (después de cargar localStorage)
                this.processCustomerIdFromURL();
                
                // Auto-agregar producto si hay solo uno con stock > 0 y no hay productos en la venta
                this.autoAddSingleProduct();
                
                // Configurar persistencia automática
                this.setupAutoSave();
                
                // Sincronizar cliente seleccionado
                this.syncSelectedCustomer();
                
                // Activar watcher para cambios en cliente
                this.watchCustomerSelection();
                
                // Asegurar que la fecha se establezca después de la inicialización
                this.$nextTick(() => {
                    if (!this.saleDate) {
                        this.setCurrentDateTime();
                    }
                });
                
            } catch (error) {
                console.error('❌ Error inicializando SPA:', error);
                this.showAlert('Error al inicializar el sistema', 'error');
            }
        },

        setupCustomSelects() {
            // Configurar select de clientes
            this.customerOptions = window.saleCreateData.customers.map(customer => ({
                value: customer.id,
                text: customer.name,
                debt: customer.total_debt || 0
            }));

            // Configurar select de "Ya pagó"
            this.paymentOptions = [
                { value: '0', text: 'No' },
                { value: '1', text: 'Sí' }
            ];

            // Configurar función global para sincronizar cliente
            window.onCustomerChange = () => {
                // Verificar que estamos en la página correcta
                const saleCreateComponent = document.querySelector('[x-data="saleCreateSPA()"]');
                if (!saleCreateComponent) {
                    return;
                }
                
                // Obtener la instancia de Alpine.js del componente principal
                if (saleCreateComponent && saleCreateComponent.__x && saleCreateComponent.__x.$data) {
                    const component = saleCreateComponent.__x.$data;
                    component.syncSelectedCustomer();
                }
            };

        },
        
        // Función para sincronizar el cliente seleccionado
        syncSelectedCustomer() {
            if (window.saleCreateData && window.saleCreateData.selectedCustomerId) {
                this.selectedCustomerId = window.saleCreateData.selectedCustomerId;
                
                // Alpine.js es reactivo, no necesitamos forzar actualización
                // La propiedad canProcessSale se actualizará automáticamente
            }
        },
        
        // ===== WATCHERS =====
        // Watcher para detectar cambios en el cliente seleccionado
        watchCustomerSelection() {
            // Verificar cada 500ms si cambió el cliente seleccionado
            setInterval(() => {
                const currentGlobalCustomer = window.saleCreateData ? window.saleCreateData.selectedCustomerId : null;
                if (currentGlobalCustomer !== this.selectedCustomerId) {
                    this.syncSelectedCustomer();
                }
            }, 500);
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
            // Los productos ya agregados aparecerán pero estarán deshabilitados
            this.filteredProducts = filtered;
        },
        
        // Función para limpiar la búsqueda
        clearSearch() {
            this.productSearchTerm = '';
            this.filterProducts();
        },
        

        
        // ===== COMPONENTE SELECT PERSONALIZADO =====
        initCustomSelect(selectId, options, selectedValue = '', placeholder = 'Seleccionar...') {
            return {
                isOpen: false,
                selectedValue: selectedValue,
                selectedText: placeholder,
                options: options,
                searchTerm: '',
                filteredOptions: options,
                
                        init() {
            this.updateSelectedText();
            this.filteredOptions = this.options;
            
            // Agregar listeners para reposicionar el dropdown
            this.setupScrollListeners();
        },

        setupScrollListeners() {
            // Reposicionar dropdown en scroll y resize
            const repositionHandler = () => {
                if (this.isOpen) {
                    this.positionDropdown();
                }
            };
            
            window.addEventListener('scroll', repositionHandler, true);
            window.addEventListener('resize', repositionHandler);
            
            // Cleanup listeners cuando el componente se destruye
            this.$el.addEventListener('alpine:destroyed', () => {
                window.removeEventListener('scroll', repositionHandler, true);
                window.removeEventListener('resize', repositionHandler);
            });
        },
                
                        toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.searchTerm = '';
                this.filteredOptions = this.options;
                this.$nextTick(() => {
                    const input = this.$refs.searchInput;
                    if (input) input.focus();
                    
                    // Posicionar el dropdown correctamente
                    this.positionDropdown();
                });
            }
        },

        positionDropdown() {
            const trigger = this.$el;
            const dropdown = trigger.querySelector('.custom-select-dropdown');
            
            if (!dropdown) return;
            
            // Obtener la posición exacta del trigger en la ventana
            const triggerRect = trigger.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            const viewportWidth = window.innerWidth;
            
            // Calcular espacio disponible debajo del trigger
            const spaceBelow = viewportHeight - triggerRect.bottom;
            const dropdownHeight = Math.min(200, this.filteredOptions.length * 48); // 48px por opción
            
            // Remover clases anteriores
            dropdown.classList.remove('dropdown-above', 'dropdown-below');
            
            // Configurar ancho del dropdown
            dropdown.style.width = `${triggerRect.width}px`;
            dropdown.style.left = `${triggerRect.left}px`;
            
            // Si no hay suficiente espacio debajo, mostrar arriba
            if (spaceBelow < dropdownHeight && triggerRect.top > dropdownHeight) {
                dropdown.classList.add('dropdown-above');
                dropdown.style.top = `${triggerRect.top - dropdownHeight}px`;
            } else {
                dropdown.classList.add('dropdown-below');
                dropdown.style.top = `${triggerRect.bottom}px`;
            }
            
            // Asegurar que no se salga del viewport horizontalmente
            const dropdownRight = triggerRect.left + triggerRect.width;
            if (dropdownRight > viewportWidth) {
                dropdown.style.left = `${viewportWidth - triggerRect.width - 10}px`;
            }
            if (triggerRect.left < 0) {
                dropdown.style.left = '10px';
            }
        },
                
                select(value, text) {
                    this.selectedValue = value;
                    this.selectedText = text;
                    this.isOpen = false;
                    this.searchTerm = '';
                    
                    // Trigger change event
                    this.$dispatch('select-changed', { value, text, selectId });
                },
                
                filterOptions() {
                    if (!this.searchTerm.trim()) {
                        this.filteredOptions = this.options;
                        return;
                    }
                    
                    const term = this.searchTerm.toLowerCase();
                    this.filteredOptions = this.options.filter(option => 
                        option.text.toLowerCase().includes(term) ||
                        (option.value && option.value.toString().toLowerCase().includes(term))
                    );
                },
                
                updateSelectedText() {
                    if (!this.selectedValue) {
                        this.selectedText = placeholder;
                        return;
                    }
                    
                    const option = this.options.find(opt => opt.value == this.selectedValue);
                    this.selectedText = option ? option.text : placeholder;
                },
                
                closeOnClickOutside(event) {
                    if (!this.$el.contains(event.target)) {
                        this.isOpen = false;
                        this.searchTerm = '';
                    }
                }
            };
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
                
                const url = (window.saleCreateRoutes?.store || '/sales/create') + '?action=' + action;
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
                    throw new Error(data.message || 'Error al procesar la venta');
                }
                
                if (data.success) {
                    // Redirigir inmediatamente con parámetro de éxito
                    if (action === 'save_and_new') {
                        // Para "guardar y nueva", limpiar solo los productos pero mantener datos del cliente
                        this.saleItems = [];
                        this.saveToLocalStorage(); // Guardar el estado actualizado
                        // Redirigir al formulario de creación con parámetro de éxito
                        window.location.href = '/sales/create?sale_created_form=true';
                    } else {
                        // Para "guardar y salir", limpiar todo y redirigir al index
                        this.clearLocalStorage();
                        const redirectUrl = data.redirect_url || (window.saleCreateRoutes && window.saleCreateRoutes.index) || '/sales/create';
                        window.location.href = redirectUrl + '?sale_created=true';
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
            Swal.fire({
                title: '¿Cancelar venta?',
                text: 'Se perderán todos los datos no guardados',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, continuar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.clearLocalStorage();
                    
                    // Obtener la URL de referencia para evitar bucles
                    const referrer = document.referrer;
                    const currentUrl = window.location.href;
                    
                    // Verificar si la URL de referencia es válida y diferente a la actual
                    if (referrer && referrer !== currentUrl && !referrer.includes('/sales/create')) {
                        // Usar la URL de referencia si es válida
                        window.location.href = referrer;
                    } else {
                        // Fallback: ir a la lista de ventas
                        window.location.href = '/sales';
                    }
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
                        
                        // Forzar actualización de la vista
                        this.forceViewUpdate();
                        
                        // Mostrar notificación si se cargaron productos automáticamente
                        if (this.saleItems.length > 0) {
                            setTimeout(() => {
                                this.showToast('Venta Recuperada', `${this.saleItems.length} producto(s) cargado(s) automáticamente`, 'info', 2000);
                            }, 500); // Pequeño delay para que se vea después de la inicialización
                        }
                    } else {
                        this.clearLocalStorage();
                        // Si se limpió localStorage, verificar si hay un solo producto para auto-agregar
                        setTimeout(() => {
                            this.autoAddSingleProduct();
                        }, 100);
                    }
                } else {
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
                        confirmButtonText: 'Sí, confirmar',
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

        // Procesar parámetro customer_id de la URL
        processCustomerIdFromURL() {
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const customerId = urlParams.get('customer_id');
                
                if (customerId && window.saleCreateData && window.saleCreateData.customers) {
                    // Buscar el cliente en la lista (convertir a número para comparación)
                    const customerIdNum = parseInt(customerId);
                    const customer = window.saleCreateData.customers.find(c => c.id === customerIdNum);
                    
                    if (customer) {
                        // Auto-seleccionar el cliente en el componente principal
                        this.selectedCustomerId = customer.id;
                        
                        // Sincronizar con el componente Alpine
                        this.syncCustomerSelection(customer);
                        
                        
                        // Mostrar notificación al usuario
                        this.showToast('Cliente Seleccionado', `Cliente "${customer.name}" seleccionado automáticamente`, 'success', 3000);
                    } else {
                        console.warn(`⚠️ Cliente con ID ${customerId} no encontrado en la lista`);
                    }
                }
            } catch (error) {
                console.error('❌ Error procesando customer_id de la URL:', error);
            }
        },

        // Sincronizar selección de cliente con componente Alpine
        syncCustomerSelection(customer) {
            this.$nextTick(() => {
                const customerSelectContainer = this.$el.querySelector('[x-data*="selectedCustomerName"]');
                if (customerSelectContainer && customerSelectContainer.__x) {
                    const customerComponent = customerSelectContainer.__x;
                    
                    // Actualizar las propiedades del componente Alpine
                    customerComponent.selectedCustomerName = customer.name;
                    customerComponent.selectedCustomerDebt = parseFloat(customer.total_debt || 0);
                    customerComponent.isOpen = false;
                    
                    g(`✅ Componente Alpine sincronizado: ${customer.name}`);
                }
            });
        },

        autoAddSingleProduct() {
            // Verificar que tenemos productos en cache
            if (!this.productsCache || this.productsCache.length === 0) {
                return;
            }
            
            // Filtrar productos con stock > 0
            const availableProducts = this.productsCache.filter(product => product.stock > 0);
            
            // Si hay exactamente un producto disponible y no hay productos en la venta
            if (availableProducts.length === 1 && this.saleItems.length === 0) {
                const product = availableProducts[0];
                this.addProductToSale(product);
                this.showToast('Producto Agregado', `"${product.name}" agregado automáticamente`, 'success', 1500);
            }
        }
    }));
});

// ===== FUNCIONES GLOBALES PARA FILTER-SELECT =====

// Solo definir funciones globales si estamos en la página de crear venta
if (document.querySelector('[x-data*="saleCreateSPA"]')) {
    // Función para manejar la selección de cliente
window.saleCreateData = window.saleCreateData || {};
window.saleCreateData.onCustomerSelect = function(selectedValue, selectedItem) {
    // Verificar que estamos en la página correcta
    if (!document.querySelector('[x-data*="saleCreateSPA"]')) {
        return;
    }
    
    // Actualizar el selectedCustomerId en el componente principal
    if (window.Alpine && window.Alpine.store) {
        const saleCreateComponent = document.querySelector('[x-data*="saleCreateSPA"]');
        if (saleCreateComponent && saleCreateComponent.__x) {
            const component = saleCreateComponent.__x;
            component.selectedCustomerId = selectedValue;
            component.saveToLocalStorage();
        }
    }
};

// Función para manejar la selección de pago
window.saleCreateData.onPaymentSelect = function(selectedValue, selectedItem) {
    // Verificar que estamos en la página correcta
    if (!document.querySelector('[x-data*="saleCreateSPA"]')) {
        return;
    }
    
    // Si selecciona "Sí" (pago automático), mostrar confirmación
    if (selectedValue === '1') {
        Swal.fire({
            title: '¿Confirmar pago automático?',
            text: 'Al seleccionar Sí, se registrará automáticamente el pago de esta venta. ¿Está seguro?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Actualizar el alreadyPaid en el componente principal
                if (window.Alpine && window.Alpine.store) {
                    const saleCreateComponent = document.querySelector('[x-data*="saleCreateSPA"]');
                    if (saleCreateComponent && saleCreateComponent.__x) {
                        const component = saleCreateComponent.__x;
                        component.alreadyPaid = selectedValue;
                        component.saveToLocalStorage();
                        
                        Swal.fire({
                            title: '¡Pago automático activado!',
                            text: 'El pago se registrará automáticamente al crear la venta.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }
            } else {
                // Si cancela, revertir la selección
                const paymentSelect = document.querySelector('[name="payment-select"]');
                if (paymentSelect) {
                    paymentSelect.value = '0';
                    // Disparar evento para actualizar el componente
                    paymentSelect.dispatchEvent(new Event('change'));
                }
            }
        });
    } else {
        // Si selecciona "No", actualizar directamente
        if (window.Alpine && window.Alpine.store) {
            const saleCreateComponent = document.querySelector('[x-data*="saleCreateSPA"]');
            if (saleCreateComponent && saleCreateComponent.__x) {
                const component = saleCreateComponent.__x;
                component.alreadyPaid = selectedValue;
                component.saveToLocalStorage();
                

            }
        }
    }
};
}
