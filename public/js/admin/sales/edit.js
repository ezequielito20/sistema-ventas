// ===== JAVASCRIPT PARA LA VISTA DE EDICIÓN DE VENTAS =====

// Variables globales
let currencySymbol = '$';
let saleId = null;
let hasUnsavedChanges = false;

// Función principal de Alpine.js
window.saleForm = function() {
    return {
        loading: false,
        productCount: 0,
        totalAmount: 0.00,
        searchModalOpen: false,

        init() {
            this.initializeForm();
            this.setupEventListeners();
            
            this.updateCounters();
            this.updateEmptyState();
            this.checkForUnsavedChanges();
        },

        initializeForm() {
            // Obtener el ID de la venta desde la URL
            const urlParts = window.location.pathname.split('/');
            saleId = urlParts[urlParts.length - 1];
            
            // Guardar URL de referencia
            if (!sessionStorage.getItem('sales_edit_referrer')) {
                const referrer = document.referrer;
                if (referrer && 
                    !referrer.includes('/sales/edit') && 
                    referrer !== window.location.href &&
                    referrer !== window.location.origin + '/') {
                    sessionStorage.setItem('sales_edit_referrer', referrer);
                }
            }
        },

        setupEventListeners() {
            // Botón volver
            const backButton = document.getElementById('backButton');
            if (backButton) {
                backButton.addEventListener('click', () => this.goBack());
            }

            // Botón cancelar
            const cancelButton = document.getElementById('cancelSale');
            if (cancelButton) {
                cancelButton.addEventListener('click', () => this.cancelSale());
            }

            // Formulario
            const form = document.getElementById('saleForm');
            if (form) {
                form.addEventListener('submit', (e) => this.handleFormSubmit(e));
            }

            // Búsqueda por código
            const productCodeInput = document.getElementById('product_code');
            if (productCodeInput) {
                productCodeInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.searchProductByCode();
                    }
                });
            }

            // Cambios en inputs para detectar modificaciones
            document.addEventListener('input', () => {
                hasUnsavedChanges = true;
            });

            // Advertencia antes de salir
            window.addEventListener('beforeunload', (e) => {
                if (hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        },

        createCustomSelect() {
            const select = document.getElementById('customer_id');
            if (!select) {
                return;
            }

            const container = select.parentElement;
            if (!container) {
                return;
            }
            
            // Verificar si ya existe un select personalizado
            if (container.querySelector('.custom-select')) {
                return;
            }
            
            // Crear el botón visible
            const selectButton = document.createElement('div');
            selectButton.className = 'custom-select';
            selectButton.innerHTML = `
                <span class="selected-text text-gray-500">Seleccione un cliente</span>
                <i class="fas fa-chevron-down custom-select-arrow"></i>
            `;

            // Crear el dropdown
            const dropdown = document.createElement('div');
            dropdown.className = 'custom-dropdown';
            dropdown.innerHTML = `
                <input type="text" placeholder="Buscar cliente..." class="search-input">
                <div class="options-container"></div>
            `;

                // Agregar elementos al DOM
    container.appendChild(selectButton);
    container.appendChild(dropdown);
    
    // Agregar event listener para abrir/cerrar el dropdown
    selectButton.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        toggleDropdownIndependent();
    });

            // Ocultar el select original
            select.style.display = 'none';

            // Llenar opciones
            this.populateDropdownOptions(select, dropdown);

            // Event listeners
            selectButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Verificar si hay un modal de búsqueda de productos abierto
                const searchModal = document.querySelector('[x-show="searchModalOpen"]');
                if (searchModal && searchModal.style.display !== 'none' && !searchModal.classList.contains('hidden')) {
                    return;
                }
                
                this.toggleDropdown();
            });

            // Cerrar al hacer clic fuera
            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) {
                    this.toggleDropdown(false);
                }
            });

            // Cerrar al hacer scroll
            document.addEventListener('scroll', () => {
                this.toggleDropdown(false);
            });

            // Cerrar al redimensionar
            window.addEventListener('resize', () => {
                this.toggleDropdown(false);
            });

            // Cerrar cuando se abra el modal de búsqueda
            setInterval(() => {
                if (dropdown.classList.contains('show')) {
                    const searchModal = document.querySelector('[x-show="searchModalOpen"]');
                    if (searchModal && searchModal.style.display !== 'none' && !searchModal.classList.contains('hidden')) {
                        this.toggleDropdown(false);
                    }
                }
            }, 100);

            // Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.toggleDropdown(false);
                }
            });
        },

        populateDropdownOptions(select, dropdown) {
            const optionsContainer = dropdown.querySelector('.options-container');
            const searchInput = dropdown.querySelector('.search-input');
            const selectButton = dropdown.previousElementSibling;
            
            // Verificar que todos los elementos existan
            if (!optionsContainer || !searchInput || !selectButton) {
                return;
            }
            
            const selectedText = selectButton.querySelector('.selected-text');
            
            if (!selectedText) {
                return;
            }

            // Obtener opciones del select original
            const options = Array.from(select.options);

            // Función para crear opciones
            const createOptions = (filteredOptions = options) => {
                optionsContainer.innerHTML = '';
                
                filteredOptions.forEach(option => {
                    if (option.value === '') return; // Saltar placeholder
                    
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'custom-dropdown-option';
                    
                    const parts = option.text.split(' - ');
                    const name = parts[0]?.trim() || '';
                    const debt = parts[1]?.trim() || '';
                    const hasDebt = debt && !debt.includes('0.00');
                    
                    const badgeClass = hasDebt ? 'has-debt' : 'no-debt';
                    
                    optionDiv.innerHTML = `
                        <div>
                            <strong>${name}</strong>
                        </div>
                        <div>
                            <span class="debt-badge ${badgeClass}">${debt}</span>
                        </div>
                    `;
                    
                    optionDiv.addEventListener('click', () => {
                        // Verificar que los elementos existan antes de usarlos
                        if (!selectedText) {
                            return;
                        }
                        
                        // Remover selección anterior
                        optionsContainer.querySelectorAll('.custom-dropdown-option').forEach(opt => {
                            opt.classList.remove('selected');
                        });
                        
                        // Seleccionar esta opción
                        optionDiv.classList.add('selected');
                        
                        // Actualizar select original
                        select.value = option.value;
                        select.dispatchEvent(new Event('change'));
                        
                        // Actualizar texto del botón
                        selectedText.textContent = name;
                        selectedText.className = 'text-gray-900';
                        
                        // Cerrar dropdown
                        this.toggleDropdown(false);
                    });
                    
                    // Marcar como seleccionado si es la opción actual
                    if (option.selected && selectedText) {
                        optionDiv.classList.add('selected');
                        selectedText.textContent = name;
                        selectedText.className = 'text-gray-900';
                    }
                    
                    optionsContainer.appendChild(optionDiv);
                });
            };

            // Búsqueda
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const filteredOptions = options.filter(option => 
                    option.text.toLowerCase().includes(searchTerm)
                );
                
                createOptions(filteredOptions);
                
                // Mostrar mensaje si no hay resultados
                if (filteredOptions.length === 0) {
                    optionsContainer.innerHTML = '<div class="p-4 text-center text-gray-500">No se encontraron clientes</div>';
                }
            });

            // Crear opciones iniciales
            createOptions();
        },

        toggleDropdown(show = null) {
            const dropdown = document.querySelector('.custom-dropdown');
            const selectButton = document.querySelector('.custom-select');
            
            // Verificar que los elementos existan
            if (!dropdown || !selectButton) {
                return;
            }
            
            const searchInput = dropdown.querySelector('.search-input');
            const addButton = document.querySelector('.add-customer-button');

            if (show === null) {
                show = !dropdown.classList.contains('show');
            }

            if (show) {
                dropdown.classList.add('show');
                selectButton.classList.add('open');
                
                // Posicionar dropdown
                const rect = selectButton.getBoundingClientRect();
                const top = rect.bottom + window.scrollY + 4;
                const left = rect.left + window.scrollX;
                const width = rect.width;
                
                dropdown.style.position = 'fixed';
                dropdown.style.top = `${top}px`;
                dropdown.style.left = `${left}px`;
                dropdown.style.width = `${width}px`;
                dropdown.style.zIndex = '100';
                
                // Asegurar que el botón de agregar cliente sea visible
                if (addButton) {
                    addButton.style.zIndex = '101';
                }
                
                // Enfocar input de búsqueda
                setTimeout(() => {
                    searchInput.focus();
                    searchInput.select();
                }, 10);
            } else {
                dropdown.classList.remove('show');
                selectButton.classList.remove('open');
                
                // Limpiar búsqueda
                searchInput.value = '';
                this.populateDropdownOptions(document.getElementById('customer_id'), dropdown);
                
                // Resetear z-index del botón
                if (addButton) {
                    addButton.style.zIndex = '';
                }
            }
        },

        searchProductByCode() {
            const code = document.getElementById('product_code').value.trim();
            if (!code) return;

            fetch(`/sales/product-by-code/${code}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.addProductToTable(data.product);
                        document.getElementById('product_code').value = '';
                        document.getElementById('product_code').focus();
                    } else {
                        this.showAlert('Error', 'Producto no encontrado', 'error');
                    }
                })
                .catch(error => {
                    this.showAlert('Error', 'Error al buscar el producto', 'error');
                });
        },

        addProductToTable(product, showAlert = true) {
            const saleItems = document.getElementById('saleItems');
            const existingRow = saleItems.querySelector(`tr[data-product-id="${product.id}"]`);
            
            if (existingRow) {
                // Incrementar cantidad si ya existe
                const quantityInput = existingRow.querySelector('.quantity-input');
                const currentQuantity = parseInt(quantityInput.value) || 0;
                const newQuantity = currentQuantity + 1;
                const maxStock = parseInt(quantityInput.getAttribute('max'));
                
                if (newQuantity > maxStock) {
                    if (showAlert) {
                        this.showAlert('Stock insuficiente', `Solo hay ${maxStock} unidades disponibles`, 'warning');
                    }
                    return false; // Indica que no se pudo agregar
                }
                
                quantityInput.value = newQuantity;
                quantityInput.dispatchEvent(new Event('input'));
                
                // Mostrar notificación de cantidad incrementada
                if (showAlert) {
                    this.showAlert('Cantidad actualizada', `Se incrementó la cantidad de ${product.name}`, 'info');
                }
            } else {
                // Agregar nueva fila
                const row = document.createElement('tr');
                row.setAttribute('data-product-id', product.id);
                row.setAttribute('data-product-code', product.code);
                row.className = 'hover:bg-gray-50 transition-colors duration-200';
                
                const stockValue = parseInt(product.stock) || 0;
                const priceValue = parseFloat(product.sale_price) || 0;
                const stockClass = stockValue > 10 ? 'bg-green-100 text-green-800' : 
                                 (stockValue > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${product.code}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">${product.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${stockClass}">
                            ${stockValue}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <input type="number" class="quantity-input" 
                               value="1" min="1" max="${stockValue}" step="1">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                        $ ${priceValue.toFixed(2)}
                        <input type="hidden" class="price-input" value="${priceValue}">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                        <span class="subtotal-display">$ ${priceValue.toFixed(2)}</span>
                        <span class="subtotal-value hidden">${priceValue}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <button type="button" class="btn-action-remove remove-item">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </td>
                `;
                
                saleItems.appendChild(row);
                
                // Agregar event listener para cambios en cantidad
                const quantityInput = row.querySelector('.quantity-input');
                quantityInput.addEventListener('input', (e) => {
                    this.updateQuantity(e.target);
                });
                
                // Agregar event listener para eliminar producto
                const removeButton = row.querySelector('.remove-item');
                removeButton.addEventListener('click', () => {
                    this.removeProduct(row);
                });
                
                // Actualizar interfaz
                this.updateTotal();
                this.updateEmptyState();
                this.updateCounters();
                
                // Mostrar notificación de producto agregado
                if (showAlert) {
                    this.showAlert('¡Producto agregado!', `${product.name} se agregó a la lista de venta`, 'success');
                }
            }
            
            return true; // Indica que se agregó correctamente
        },

        updateQuantity(input) {
            const row = input.closest('tr');
            const quantity = parseFloat(input.value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const stock = parseInt(input.getAttribute('max'));

            if (quantity > stock) {
                input.value = stock;
                this.showAlert('Stock insuficiente', `Solo hay ${stock} unidades disponibles`, 'warning');
                return;
            }

            const subtotal = quantity * price;
            row.querySelector('.subtotal-value').textContent = subtotal.toFixed(2);
            row.querySelector('.subtotal-display').textContent = `$ ${subtotal.toFixed(2)}`;
            this.updateTotal();
        },

        removeProduct(row) {
            if (confirm('¿Está seguro de eliminar este producto de la venta?')) {
                row.remove();
                this.updateTotal();
                this.updateEmptyState();
                this.updateCounters();
            }
        },

        updateTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal-value').forEach(element => {
                total += parseFloat(element.textContent) || 0;
            });
            
            const totalAmount = document.getElementById('totalAmount');
            const totalAmountInput = document.getElementById('totalAmountInput');
            const totalAmountDisplay = document.querySelector('.total-amount-display');
            
            if (totalAmount) totalAmount.textContent = `$ ${total.toFixed(2)}`;
            if (totalAmountInput) totalAmountInput.value = total.toFixed(2);
            if (totalAmountDisplay) totalAmountDisplay.textContent = `$ ${total.toFixed(2)}`;
            
            this.updateCounters();
        },

        showAlert(title, message, type = 'info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    title: title,
                    text: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                alert(`${title}: ${message}`);
            }
        },

        updateCounters() {
            const productCount = document.querySelectorAll('#saleItems tr').length;
            const productsCountElement = document.querySelector('.products-count');
            if (productsCountElement) {
                productsCountElement.textContent = `${productCount} producto${productCount !== 1 ? 's' : ''}`;
            }
        },

        updateEmptyState() {
            const hasProducts = document.querySelectorAll('#saleItems tr').length > 0;
            const emptyState = document.getElementById('emptyState');
            const table = document.querySelector('.modern-table');

            if (hasProducts) {
                if (emptyState) emptyState.classList.add('hidden');
                if (table) table.classList.remove('hidden');
            } else {
                if (emptyState) emptyState.classList.remove('hidden');
                if (table) table.classList.add('hidden');
            }
        },

        updateEmptyState() {
            const saleItems = document.querySelectorAll('#saleItems tr');
            const hasProducts = saleItems.length > 0;
            const emptyState = document.getElementById('emptyState');
            const modernTable = document.querySelector('.modern-table');
            
            if (hasProducts) {
                if (emptyState) {
                    emptyState.style.display = 'none';
                    emptyState.classList.add('hidden');
                }
                if (modernTable) {
                    modernTable.style.display = 'table';
                    modernTable.classList.remove('hidden');
                }
            } else {
                if (emptyState) {
                    emptyState.style.display = 'block';
                    emptyState.classList.remove('hidden');
                }
                if (modernTable) {
                    modernTable.style.display = 'none';
                    modernTable.classList.add('hidden');
                }
            }
        },

        handleFormSubmit(e) {
            e.preventDefault();
            
            // Marcar que no hay cambios pendientes para evitar la alerta
            hasUnsavedChanges = false;
            
            // Deshabilitar botón
            const submitButton = document.getElementById('submitSale');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('btn-loading');
            }
            
            // Validaciones
            if (document.querySelectorAll('#saleItems tr').length === 0) {
                this.showAlert('Error', 'Debe agregar al menos un producto a la venta', 'error');
                this.enableSubmitButton();
                return false;
            }
            
            if (!document.getElementById('customer_id').value) {
                this.showAlert('Error', 'Debe seleccionar un cliente', 'error');
                this.enableSubmitButton();
                return false;
            }
            
            // Preparar datos
            const items = [];
            document.querySelectorAll('#saleItems tr').forEach(row => {
                const productId = row.getAttribute('data-product-id');
                const quantityInput = row.querySelector('.quantity-input');
                const priceInput = row.querySelector('.price-input');
                const subtotalValue = row.querySelector('.subtotal-value');
                
                if (!productId || productId === '0') {
                    this.showAlert('Error en datos del producto', 'Se detectó un ID de producto inválido. Por favor, recargue la página e intente nuevamente.', 'error');
                    this.enableSubmitButton();
                    return false;
                }
                
                items.push({
                    product_id: productId,
                    quantity: parseFloat(quantityInput.value),
                    price: parseFloat(priceInput.value),
                    subtotal: parseFloat(subtotalValue.textContent)
                });
            });
            
            // Crear campos ocultos
            const container = document.getElementById('itemsContainer');
            if (container) container.remove();
            
            const newContainer = document.createElement('div');
            newContainer.id = 'itemsContainer';
            
            items.forEach(item => {
                if (!item.product_id || item.product_id <= 0) {
                    this.showAlert('Error en datos del producto', `Producto tiene un ID inválido: ${item.product_id}`, 'error');
                    this.enableSubmitButton();
                    return false;
                }
                
                newContainer.innerHTML += `
                    <input type="hidden" name="items[${item.product_id}][product_id]" value="${item.product_id}">
                    <input type="hidden" name="items[${item.product_id}][quantity]" value="${item.quantity}">
                    <input type="hidden" name="items[${item.product_id}][price]" value="${item.price}">
                    <input type="hidden" name="items[${item.product_id}][subtotal]" value="${item.subtotal}">
                `;
            });
            
            e.target.appendChild(newContainer);
            
            // Limpiar la URL guardada para evitar problemas de navegación
            sessionStorage.removeItem('sales_edit_referrer');
            
            // Enviar formulario
            e.target.submit();
        },

        enableSubmitButton() {
            const submitButton = document.getElementById('submitSale');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.classList.remove('btn-loading');
            }
        },

        goBack() {
            // Marcar que no hay cambios pendientes para evitar la alerta
            hasUnsavedChanges = false;
            
            const savedReferrer = sessionStorage.getItem('sales_edit_referrer');
            
            if (savedReferrer && savedReferrer !== window.location.href) {
                window.location.href = savedReferrer;
            } else {
                window.location.href = '/admin/sales';
            }
        },

        cancelSale() {
            // Marcar que no hay cambios pendientes para evitar la alerta
            hasUnsavedChanges = false;
            
            this.showConfirmDialog(
                '¿Está seguro?',
                'Se perderán todos los cambios realizados en esta venta',
                'warning',
                () => this.goBack()
            );
        },

        showAlert(title, text, icon) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: icon === 'success' ? '#e8f5e8' : 
                              icon === 'warning' ? '#fff3cd' : 
                              icon === 'error' ? '#f8d7da' : '#d1ecf1',
                    color: icon === 'success' ? '#155724' : 
                          icon === 'warning' ? '#856404' : 
                          icon === 'error' ? '#721c24' : '#0c5460'
                });
            } else {
                alert(`${title}: ${text}`);
            }
        },

        showConfirmDialog(title, text, icon, onConfirm) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed && onConfirm) {
                        onConfirm();
                    }
                });
            } else {
                if (confirm(`${title}: ${text}`)) {
                    onConfirm();
                }
            }
        },

        checkForUnsavedChanges() {
            // Event listeners para detectar cambios
            document.addEventListener('input', () => {
                hasUnsavedChanges = true;
            });
            
            document.addEventListener('change', () => {
                hasUnsavedChanges = true;
            });
        }
    };
};

    // Event listeners globales
    document.addEventListener('DOMContentLoaded', function() {
    // Verificar estado inicial de productos
    setTimeout(() => {
        const saleForm = document.querySelector('[x-data="saleForm()"]')._x_dataStack[0];
        if (saleForm) {
            saleForm.updateEmptyState();
            saleForm.updateCounters();
        }
    }, 100);
    // Actualizar subtotal cuando cambie cantidad
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            const row = e.target.closest('tr');
            const quantity = parseFloat(e.target.value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const stock = parseInt(e.target.getAttribute('max'));

            // Validar stock
            if (quantity > stock) {
                e.target.value = stock;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock insuficiente',
                        text: `Solo hay ${stock} unidades disponibles`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
                return;
            }

            const subtotal = quantity * price;
            row.querySelector('.subtotal-value').textContent = subtotal.toFixed(2);
            row.querySelector('.subtotal-display').textContent = `${currencySymbol} ${subtotal.toFixed(2)}`;
            
            // Actualizar total
            const saleForm = document.querySelector('[x-data="saleForm()"]')._x_dataStack[0];
            if (saleForm) {
                saleForm.updateTotal();
            }
        }
    });

    // Eliminar producto
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const row = e.target.closest('tr');
            
            if (typeof Swal !== 'undefined') {
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
                        const saleForm = document.querySelector('[x-data="saleForm()"]')._x_dataStack[0];
                        if (saleForm) {
                            saleForm.updateTotal();
                            saleForm.updateEmptyState();
                            saleForm.updateCounters();
                        }
                    }
                });
            } else {
                if (confirm('¿Está seguro de eliminar este producto?')) {
                    row.remove();
                    const saleForm = document.querySelector('[x-data="saleForm()"]')._x_dataStack[0];
                    if (saleForm) {
                        saleForm.updateTotal();
                        saleForm.updateEmptyState();
                        saleForm.updateCounters();
                    }
                }
            }
        }
    });

    // Seleccionar producto del modal - Versión simplificada
    
    // Event listener para botones de productos en el modal
    document.addEventListener('click', (e) => {
        if (e.target.closest('.select-product')) {
            
            const button = e.target.closest('.select-product');
            
            // Verificar si el botón está deshabilitado
            if (button.disabled || button.classList.contains('opacity-50')) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Producto no disponible',
                        text: 'Este producto no tiene stock disponible',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
                return;
            }
            
            const code = button.getAttribute('data-code');
            const productId = button.getAttribute('data-id');
            
            
            // Mostrar indicador de carga en el botón
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i>';
            button.disabled = true;
            
            // Obtener datos del producto directamente del DOM
            const row = button.closest('tr');
            const productName = row.querySelector('td:nth-child(4)').textContent.trim();
            const productStock = parseInt(row.querySelector('td:nth-child(6) span').textContent.trim());
            const productPrice = parseFloat(row.querySelector('td:nth-child(7)').textContent.replace(/[^\d.,]/g, '').replace(',', '.'));
            const productImage = row.querySelector('td:nth-child(3) img').src;
            const productCategory = row.querySelector('td:nth-child(5)').textContent.trim();
            
            // Crear objeto producto
            const product = {
                id: productId,
                code: code,
                name: productName,
                stock: productStock,
                sale_price: productPrice,
                image_url: productImage,
                category: { name: productCategory }
            };
            
            
            // Obtener la instancia de Alpine.js
            const alpineElement = document.querySelector('[x-data="saleForm()"]');
            if (alpineElement && alpineElement._x_dataStack && alpineElement._x_dataStack[0]) {
                const saleForm = alpineElement._x_dataStack[0];
                
                
                // Agregar producto a la tabla
                const success = saleForm.addProductToTable(product, true);
                
                if (success) {
                    // Cerrar modal
                    saleForm.searchModalOpen = false;
                    
                    // Mostrar notificación de éxito
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Producto agregado!',
                            text: `${product.name} se agregó a la venta`,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                    
                } else {
                }
            } else {
                console.error('❌ No se pudo acceder al formulario de venta');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo acceder al formulario de venta',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            }
            
            // Restaurar el botón
            button.innerHTML = originalContent;
            button.disabled = false;
        }
    });
});

// Función para crear el select personalizado de forma independiente
function createCustomSelectIndependent() {
    
    const select = document.getElementById('customer_id');
    if (!select) {
        return;
    }

    const container = select.parentElement;
    if (!container) {
        console.error('❌ Contenedor del select no encontrado');
        return;
    }
    
    // Verificar si ya existe un select personalizado
    if (container.querySelector('.custom-select')) {
        return;
    }
    
    // Crear el botón visible
    const selectButton = document.createElement('div');
    selectButton.className = 'custom-select';
    selectButton.innerHTML = `
        <span class="selected-text text-gray-500">Seleccione un cliente</span>
        <i class="fas fa-chevron-down custom-select-arrow"></i>
    `;

    // Crear el dropdown
    const dropdown = document.createElement('div');
    dropdown.className = 'custom-dropdown';
    dropdown.innerHTML = `
        <input type="text" placeholder="Buscar cliente..." class="search-input">
        <div class="options-container"></div>
    `;

    // Agregar elementos al DOM
    container.appendChild(selectButton);
    container.appendChild(dropdown);

    // Ocultar el select original
    select.style.display = 'none';

    // Llenar opciones
    populateDropdownOptionsIndependent(select, dropdown);
}

// Función para llenar las opciones del dropdown de forma independiente
function populateDropdownOptionsIndependent(select, dropdown) {
    
    const optionsContainer = dropdown.querySelector('.options-container');
    const searchInput = dropdown.querySelector('.search-input');
    const selectButton = dropdown.previousElementSibling;
    
    
    
    // Verificar que todos los elementos existan
    if (!optionsContainer || !searchInput || !selectButton) {
        console.error('❌ Elementos del dropdown no encontrados:', {
            optionsContainer: !!optionsContainer,
            searchInput: !!searchInput,
            selectButton: !!selectButton
        });
        return;
    }
    
    const selectedText = selectButton.querySelector('.selected-text');
    
    if (!selectedText) {
        console.error('❌ Elemento selected-text no encontrado');
        return;
    }

    // Obtener opciones del select original
    const options = Array.from(select.options);

    // Función para crear opciones
    const createOptions = (filteredOptions = options) => {
        optionsContainer.innerHTML = '';
        
        filteredOptions.forEach(option => {
            if (option.value === '') return; // Saltar placeholder
            
            const optionDiv = document.createElement('div');
            optionDiv.className = 'custom-dropdown-option';
            
            const parts = option.text.split(' - ');
            const name = parts[0]?.trim() || '';
            const debt = parts[1]?.trim() || '';
            const hasDebt = debt && !debt.includes('0.00');
            
            const badgeClass = hasDebt ? 'has-debt' : 'no-debt';
            
            optionDiv.innerHTML = `
                <div>
                    <strong>${name}</strong>
                </div>
                <div>
                    <span class="debt-badge ${badgeClass}">${debt}</span>
                </div>
            `;
            
            optionDiv.addEventListener('click', () => {
                // Verificar que los elementos existan antes de usarlos
                if (!selectedText) {
                    console.error('selectedText no encontrado en click');
                    return;
                }
                
                // Remover selección anterior
                optionsContainer.querySelectorAll('.custom-dropdown-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Seleccionar esta opción
                optionDiv.classList.add('selected');
                
                // Actualizar select original
                select.value = option.value;
                select.dispatchEvent(new Event('change'));
                
                // Actualizar texto del botón
                selectedText.textContent = name;
                selectedText.className = 'text-gray-900';
                
                // Cerrar dropdown
                toggleDropdownIndependent(false);
            });
            
            // Marcar como seleccionado si es la opción actual
            if (option.selected && selectedText) {
                optionDiv.classList.add('selected');
                selectedText.textContent = name;
                selectedText.className = 'text-gray-900';
            }
            
            optionsContainer.appendChild(optionDiv);
        });
    };

    // Búsqueda
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const filteredOptions = options.filter(option => 
            option.text.toLowerCase().includes(searchTerm)
        );
        
        createOptions(filteredOptions);
        
        // Mostrar mensaje si no hay resultados
        if (filteredOptions.length === 0) {
            optionsContainer.innerHTML = '<div class="p-4 text-center text-gray-500">No se encontraron clientes</div>';
        }
    });

    // Crear opciones iniciales
    createOptions();
}

// Función para toggle del dropdown de forma independiente
function toggleDropdownIndependent(show = null) {
    const dropdown = document.querySelector('.custom-dropdown');
    const selectButton = document.querySelector('.custom-select');
    
    // Verificar que los elementos existan
    if (!dropdown || !selectButton) {
        return;
    }
    
    const searchInput = dropdown.querySelector('.search-input');
    const addButton = document.querySelector('.add-customer-button');

    if (show === null) {
        show = !dropdown.classList.contains('show');
    }

    if (show) {
        dropdown.classList.add('show');
        selectButton.classList.add('open');
        
        // Posicionar dropdown
        const rect = selectButton.getBoundingClientRect();
        const top = rect.bottom + window.scrollY + 4;
        const left = rect.left + window.scrollX;
        const width = rect.width;
        
        dropdown.style.position = 'fixed';
        dropdown.style.top = top + 'px';
        dropdown.style.left = left + 'px';
        dropdown.style.width = width + 'px';
        dropdown.style.zIndex = '100';
        
        // Verificar si hay espacio suficiente abajo
        const dropdownHeight = 350; // altura estimada del dropdown
        const spaceBelow = window.innerHeight - rect.bottom;
        
        if (spaceBelow < dropdownHeight) {
            // Posicionar arriba si no hay espacio abajo
            dropdown.style.top = (rect.top + window.scrollY - dropdownHeight - 4) + 'px';
        }
        
        // Enfocar el input de búsqueda
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
        
        // Ajustar z-index del botón de agregar
        if (addButton) {
            addButton.style.zIndex = '101';
        }
        
        // Event listeners para cerrar
        document.addEventListener('click', closeDropdownOnClickOutside);
        document.addEventListener('scroll', closeDropdownOnScroll);
        window.addEventListener('resize', closeDropdownOnResize);
        
    } else {
        dropdown.classList.remove('show');
        selectButton.classList.remove('open');
        
        // Limpiar estilos
        dropdown.style.position = '';
        dropdown.style.top = '';
        dropdown.style.left = '';
        dropdown.style.width = '';
        dropdown.style.zIndex = '';
        
        // Limpiar input de búsqueda
        if (searchInput) {
            searchInput.value = '';
        }
        
        // Resetear z-index del botón de agregar
        if (addButton) {
            addButton.style.zIndex = '';
        }
        
        // Remover event listeners
        document.removeEventListener('click', closeDropdownOnClickOutside);
        document.removeEventListener('scroll', closeDropdownOnScroll);
        window.removeEventListener('resize', closeDropdownOnResize);
    }
}

// Funciones auxiliares para cerrar el dropdown
function closeDropdownOnClickOutside(e) {
    const dropdown = document.querySelector('.custom-dropdown');
    const selectButton = document.querySelector('.custom-select');
    
    if (dropdown && selectButton && !dropdown.contains(e.target) && !selectButton.contains(e.target)) {
        toggleDropdownIndependent(false);
    }
}

function closeDropdownOnScroll() {
    toggleDropdownIndependent(false);
}

function closeDropdownOnResize() {
    toggleDropdownIndependent(false);
}

// Función global para agregar producto desde el modal
window.addProductFromModal = function(code, id, name, imageUrl, stock, price, categoryName) {
    
    // Crear objeto producto
    const product = {
        id: id,
        code: code,
        name: name,
        stock: parseInt(stock),
        sale_price: parseFloat(price),
        image_url: imageUrl,
        category: { name: categoryName }
    };
    
    
    // Obtener la instancia de Alpine.js
    const alpineElement = document.querySelector('[x-data="saleForm()"]');
    if (alpineElement && alpineElement._x_dataStack && alpineElement._x_dataStack[0]) {
        const saleForm = alpineElement._x_dataStack[0];
        
        
        // Agregar producto a la tabla
        const success = saleForm.addProductToTable(product, true);
        
        if (success) {
            // Cerrar modal
            saleForm.searchModalOpen = false;
            
            // Mostrar notificación de éxito
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Producto agregado!',
                    text: `${product.name} se agregó a la venta`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }
            
        } else {
        }
    } else {
        console.error('❌ No se pudo acceder al formulario de venta');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo acceder al formulario de venta',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    }
};

// Inicializar el select personalizado cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        createCustomSelectIndependent();
    }, 1000);
});
