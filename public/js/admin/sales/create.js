// Función de Alpine.js para el formulario de ventas
window.saleForm = function() {
    return {
        loading: false,
        productCount: 0,
        totalAmount: 0.00,
        searchModalOpen: false,
        selectedCustomerId: null,
        customers: [],
        products: [],
        saleItems: [],
        currencySymbol: '$',

        init() {
            this.initializeForm();
            this.loadCustomers();
            this.loadProducts();
            this.setupEventListeners();
        },

        initializeForm() {
            // Establecer fecha y hora actual
            const today = new Date();
            const dateInput = document.getElementById('sale_date');
            const timeInput = document.getElementById('sale_time');
            
            if (dateInput) {
                dateInput.value = today.toISOString().split('T')[0];
            }
            if (timeInput) {
                timeInput.value = today.toTimeString().slice(0, 5);
            }

            // Inicializar contadores
            this.updateCounters();
        },

        loadCustomers() {
            // Cargar clientes desde el DOM
            const customerSelect = document.getElementById('customer_id');
            if (customerSelect) {
                this.customers = Array.from(customerSelect.options).map(option => ({
                    id: option.value,
                    name: option.text,
                    selected: option.selected
                }));
            }
        },

        loadProducts() {
            // Los productos se cargan desde el modal en el DOM
            const productRows = document.querySelectorAll('#productsTable tbody tr');
            this.products = Array.from(productRows).map(row => {
                const cells = row.querySelectorAll('td');
                const button = row.querySelector('.select-product');
                return {
                    id: button ? button.dataset.id : null,
                    code: cells[0] ? cells[0].textContent.trim() : '',
                    name: cells[3] ? cells[3].textContent.trim() : '',
                    image: cells[2] ? cells[2].querySelector('img')?.src : '',
                    stock: cells[5] ? parseInt(cells[5].textContent.trim()) : 0,
                    price: cells[6] ? parseFloat(cells[6].textContent.replace(/[^\d.,]/g, '').replace(',', '.')) : 0,
                    category: cells[4] ? cells[4].textContent.trim() : ''
                };
            });
        },

        setupEventListeners() {
            // Event listener para búsqueda por código
            const productCodeInput = document.getElementById('product_code');
            if (productCodeInput) {
                productCodeInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.searchProductByCode(productCodeInput.value);
                    }
                });
            }

            // Event listener para botón de búsqueda
            const searchButton = document.getElementById('searchProduct');
            if (searchButton) {
                searchButton.addEventListener('click', () => {
                    this.searchModalOpen = true;
                });
            }

            // Event listener para botón cancelar
            const cancelButton = document.getElementById('cancelSale');
            if (cancelButton) {
                cancelButton.addEventListener('click', () => {
                    this.cancelSale();
                });
            }

            // Event listener para formulario
            const form = document.getElementById('saleForm');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.submitForm(e);
                });
            }

            // Event listeners para botones de productos en el modal
            document.addEventListener('click', (e) => {
                if (e.target.closest('.select-product')) {
                    const button = e.target.closest('.select-product');
                    const productId = button.dataset.id;
                    const product = this.products.find(p => p.id === productId);
                    if (product) {
                        this.addProductToTable(product);
                        this.searchModalOpen = false;
                    }
                }
            });

            // Event listeners para botones de eliminar productos
            document.addEventListener('click', (e) => {
                if (e.target.closest('.remove-item')) {
                    const row = e.target.closest('tr');
                    this.removeProduct(row);
                }
            });

            // Event listeners para cambios en cantidad
            document.addEventListener('input', (e) => {
                if (e.target.classList.contains('quantity-input')) {
                    this.updateQuantity(e.target);
                }
            });
        },

        searchProductByCode(code) {
            if (!code) return;

            const product = this.products.find(p => p.code === code);
            if (product) {
                this.addProductToTable(product);
                document.getElementById('product_code').value = '';
                this.showNotification('Producto agregado correctamente', 'success');
            } else {
                this.showNotification('Producto no encontrado', 'error');
            }
        },

        addProductToTable(product) {
            // Verificar si el producto ya está en la tabla
            const existingRow = document.querySelector(`#saleItems tr[data-product-id="${product.id}"]`);
            
            if (existingRow) {
                // Incrementar cantidad
                const quantityInput = existingRow.querySelector('.quantity-input');
                const currentQuantity = parseInt(quantityInput.value) || 0;
                const newQuantity = currentQuantity + 1;
                
                if (newQuantity <= product.stock) {
                    quantityInput.value = newQuantity;
                    this.updateQuantity(quantityInput);
                } else {
                    this.showNotification(`Solo hay ${product.stock} unidades disponibles`, 'warning');
                }
            } else {
                // Agregar nueva fila
                const tbody = document.getElementById('saleItems');
                const row = this.createProductRow(product);
                tbody.appendChild(row);
                this.updateCounters();
                this.updateEmptyState();
            }
        },

        createProductRow(product) {
            const row = document.createElement('tr');
            row.dataset.productId = product.id;
            row.dataset.productCode = product.code;
            row.className = 'hover:bg-gray-50 transition-colors duration-200';

            const stockClass = product.stock > 10 ? 'bg-green-100 text-green-800' : 
                             product.stock > 0 ? 'bg-yellow-100 text-yellow-800' : 
                             'bg-red-100 text-red-800';

            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${product.code}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${product.name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${stockClass}">
                        ${product.stock}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <input type="number" class="quantity-input" value="1" min="1" max="${product.stock}" step="1">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                    ${this.currencySymbol} ${product.price.toFixed(2)}
                    <input type="hidden" class="price-input" value="${product.price}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                    <span class="subtotal-display">${this.currencySymbol} ${product.price.toFixed(2)}</span>
                    <span class="subtotal-value hidden">${product.price}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <button type="button" class="btn-action-remove remove-item">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </td>
            `;

            return row;
        },

        updateQuantity(input) {
            const row = input.closest('tr');
            const quantity = parseFloat(input.value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const stock = parseInt(input.getAttribute('max'));

            if (quantity > stock) {
                input.value = stock;
                this.showNotification(`Solo hay ${stock} unidades disponibles`, 'warning');
                return;
            }

            const subtotal = quantity * price;
            row.querySelector('.subtotal-value').textContent = subtotal.toFixed(2);
            row.querySelector('.subtotal-display').textContent = `${this.currencySymbol} ${subtotal.toFixed(2)}`;
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

            const totalElement = document.getElementById('totalAmount');
            const totalInput = document.getElementById('totalAmountInput');
            const totalDisplay = document.querySelector('.total-amount-display');

            if (totalElement) totalElement.textContent = `${this.currencySymbol} ${total.toFixed(2)}`;
            if (totalInput) totalInput.value = total.toFixed(2);
            if (totalDisplay) totalDisplay.textContent = `${this.currencySymbol} ${total.toFixed(2)}`;

            this.totalAmount = total;
            this.updateCounters();
        },

        updateCounters() {
            const productCount = document.querySelectorAll('#saleItems tr').length;
            const countElement = document.querySelector('.products-count');
            if (countElement) {
                countElement.textContent = `${productCount} producto${productCount !== 1 ? 's' : ''}`;
            }
            this.productCount = productCount;
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

        cancelSale() {
            if (confirm('¿Está seguro? Se perderán todos los datos ingresados en esta venta')) {
                window.history.back();
            }
        },

        submitForm(e) {
            // Validaciones
            if (this.productCount === 0) {
                this.showNotification('Debe agregar al menos un producto a la venta', 'error');
                return false;
            }

            const customerSelect = document.getElementById('customer_id');
            if (!customerSelect || !customerSelect.value) {
                this.showNotification('Debe seleccionar un cliente', 'error');
                return false;
            }

            const alreadyPaidSelect = document.getElementById('already_paid');
            if (!alreadyPaidSelect || !alreadyPaidSelect.value) {
                this.showNotification('Debe seleccionar si el cliente ya pagó o no', 'error');
                return false;
            }

            // Preparar datos
            const items = [];
            document.querySelectorAll('#saleItems tr').forEach(row => {
                const productId = row.dataset.productId;
                const quantity = parseFloat(row.querySelector('.quantity-input').value);
                const price = parseFloat(row.querySelector('.price-input').value);
                const subtotal = parseFloat(row.querySelector('.subtotal-value').textContent);

                items.push({
                    product_id: productId,
                    quantity: quantity,
                    price: price,
                    subtotal: subtotal
                });
            });

            // Crear campos ocultos
            const container = document.createElement('div');
            container.id = 'itemsContainer';

            items.forEach((item, index) => {
                container.innerHTML += `
                    <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                    <input type="hidden" name="items[${index}][price]" value="${item.price}">
                    <input type="hidden" name="items[${index}][subtotal]" value="${item.subtotal}">
                `;
            });

            // Agregar al formulario y enviar
            e.target.appendChild(container);
            e.target.submit();
        },

        showNotification(message, type = 'info') {
            // Usar SweetAlert2 si está disponible, sino usar alert nativo
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    title: type === 'success' ? '¡Éxito!' : type === 'error' ? 'Error' : 'Información',
                    text: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                alert(message);
            }
        }
    }
}

// Función para crear select personalizado con badges de deuda
function createCustomSelect() {
    const select = document.getElementById('customer_id');
    if (!select) return;

    // Crear contenedor del select personalizado
    const container = document.createElement('div');
    container.className = 'custom-select-with-badges';
    
    // Crear el select oculto que mantendrá el name para el formulario
    const hiddenSelect = select.cloneNode(true);
    hiddenSelect.style.display = 'none';
    hiddenSelect.id = 'customer_id_hidden';
    hiddenSelect.name = 'customer_id'; // Mantener el name original
    
    // Crear el botón del select
    const selectButton = document.createElement('button');
    selectButton.type = 'button';
    selectButton.className = 'w-full pl-3 pr-10 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-gray-500 focus:bg-white transition-all duration-300 text-gray-800 text-sm h-11 text-left flex items-center justify-between hover:border-gray-300 hover:bg-gray-100';
    selectButton.innerHTML = `
        <span id="selected-customer-text">Seleccione un cliente</span>
        <i class="fas fa-chevron-down text-gray-400 text-sm transition-transform duration-300"></i>
    `;
    
    // Crear el dropdown
    const dropdown = document.createElement('div');
    dropdown.className = 'custom-dropdown';
    
    // Crear el input de búsqueda
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Buscar cliente...';
    searchInput.className = 'w-full px-3 py-2 border-b border-gray-200 focus:outline-none focus:border-gray-400 text-sm';
    searchInput.id = 'customer-search-input';
    
    // Contenedor para las opciones
    const optionsContainer = document.createElement('div');
    optionsContainer.className = 'options-container';
    
    // Agregar opciones al dropdown
    const options = Array.from(select.options);
    options.forEach((option, index) => {
        // Saltar la opción vacía "Seleccione un cliente"
        if (option.value === '') return;
        
        const debt = parseFloat(option.dataset.debt || 0);
        const isSelected = option.selected;
        
        const optionElement = document.createElement('div');
        optionElement.className = `custom-dropdown-option ${isSelected ? 'selected' : ''}`;
        optionElement.dataset.value = option.value;
        
        // Opción con cliente
        const debtBadge = debt > 0 ? 
            `<span class="debt-badge has-debt">$${debt.toFixed(2)}</span>` : 
            `<span class="debt-badge no-debt">Sin deuda</span>`;
        
        optionElement.innerHTML = `
            <div class="flex flex-col flex-1 min-w-0">
                <span class="font-medium text-gray-900 truncate">${option.textContent}</span>
            </div>
            <div class="flex-shrink-0">
                ${debtBadge}
            </div>
        `;
        
        optionElement.addEventListener('click', () => {
            // Actualizar el select oculto
            hiddenSelect.value = option.value;
            hiddenSelect.dispatchEvent(new Event('change'));
            
            // Actualizar el texto del botón
            const selectedText = document.getElementById('selected-customer-text');
            selectedText.textContent = option.textContent;
            selectedText.className = 'text-gray-900'; // Cambiar a texto normal
            
            // Actualizar la clase selected
            optionsContainer.querySelectorAll('.custom-dropdown-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            optionElement.classList.add('selected');
            
            // Cerrar el dropdown
            toggleDropdown(false);
        });
        
        optionsContainer.appendChild(optionElement);
    });
    
    // Agregar elementos al dropdown
    dropdown.appendChild(searchInput);
    dropdown.appendChild(optionsContainer);
    
    // Funcionalidad de búsqueda
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const allOptions = optionsContainer.querySelectorAll('.custom-dropdown-option');
        
        allOptions.forEach(option => {
            const optionText = option.querySelector('span').textContent.toLowerCase();
            if (optionText.includes(searchTerm)) {
                option.style.display = 'flex';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Mostrar mensaje si no hay resultados
        const visibleOptions = optionsContainer.querySelectorAll('.custom-dropdown-option[style*="display: flex"]');
        let noResultsMessage = optionsContainer.querySelector('.no-results-message');
        
        if (visibleOptions.length === 0 && searchTerm !== '') {
            if (!noResultsMessage) {
                noResultsMessage = document.createElement('div');
                noResultsMessage.className = 'no-results-message text-center py-4 text-gray-500 text-sm';
                noResultsMessage.textContent = 'No se encontraron clientes';
                optionsContainer.appendChild(noResultsMessage);
            }
        } else if (noResultsMessage) {
            noResultsMessage.remove();
        }
    });
    
    // Navegación por teclado
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            toggleDropdown(false);
        }
    });
    
    // Evento para abrir/cerrar el dropdown
    selectButton.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        toggleDropdown();
    });
    
    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!container.contains(e.target)) {
            toggleDropdown(false);
        }
    });
    
    // Cerrar dropdown al hacer scroll
    document.addEventListener('scroll', () => {
        if (dropdown.classList.contains('show')) {
            toggleDropdown(false);
        }
    });
    
    // Cerrar dropdown al redimensionar la ventana
    window.addEventListener('resize', () => {
        if (dropdown.classList.contains('show')) {
            toggleDropdown(false);
        }
    });
    
    function toggleDropdown(show = null) {
        const isOpen = dropdown.classList.contains('show');
        const shouldShow = show !== null ? show : !isOpen;
        
        if (shouldShow) {
            dropdown.classList.add('show');
            selectButton.querySelector('i').style.transform = 'rotate(180deg)';
            selectButton.classList.add('border-gray-500', 'bg-white');
            
            // Enfocar el input de búsqueda
            setTimeout(() => {
                const searchInput = document.getElementById('customer-search-input');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }, 50);
            
            // Posicionar el dropdown correctamente
            setTimeout(() => {
                const buttonRect = selectButton.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                const viewportWidth = window.innerWidth;
                
                // Calcular posición
                let top = buttonRect.bottom + 4;
                let left = buttonRect.left;
                let width = buttonRect.width;
                
                // Ajustar si se corta por abajo
                if (top + 300 > viewportHeight - 20) {
                    top = buttonRect.top - 304; // Posicionar arriba del botón
                }
                
                // Ajustar si se corta por la derecha
                if (left + width > viewportWidth - 20) {
                    left = viewportWidth - width - 20;
                }
                
                // Ajustar si se corta por la izquierda
                if (left < 20) {
                    left = 20;
                }
                
                // Aplicar posición fija
                dropdown.style.position = 'fixed';
                dropdown.style.top = `${top}px`;
                dropdown.style.left = `${left}px`;
                dropdown.style.width = `${width}px`;
                dropdown.style.zIndex = '9999';
                
                // Asegurar que el botón de agregar cliente sea visible
                const addButton = document.querySelector('.add-customer-button');
                if (addButton) {
                    addButton.style.zIndex = '10000';
                }
            }, 10);
        } else {
            dropdown.classList.remove('show');
            selectButton.querySelector('i').style.transform = 'rotate(0deg)';
            selectButton.classList.remove('border-gray-500', 'bg-white');
            
            // Limpiar búsqueda
            const searchInput = document.getElementById('customer-search-input');
            if (searchInput) {
                searchInput.value = '';
                // Mostrar todas las opciones
                const allOptions = document.querySelectorAll('.custom-dropdown-option');
                allOptions.forEach(option => {
                    option.style.display = 'flex';
                });
                // Remover mensaje de no resultados
                const noResultsMessage = document.querySelector('.no-results-message');
                if (noResultsMessage) {
                    noResultsMessage.remove();
                }
            }
            
            // Resetear estilos
            dropdown.style.position = '';
            dropdown.style.top = '';
            dropdown.style.left = '';
            dropdown.style.width = '';
            dropdown.style.zIndex = '';
            
            // Resetear z-index del botón de agregar cliente
            const addButton = document.querySelector('.add-customer-button');
            if (addButton) {
                addButton.style.zIndex = '';
            }
        }
    }
    
    // Reemplazar el select original
    container.appendChild(hiddenSelect);
    container.appendChild(selectButton);
    container.appendChild(dropdown);
    select.parentNode.replaceChild(container, select);
    
    // Configurar el valor inicial si hay uno seleccionado
    const selectedOption = options.find(opt => opt.selected);
    if (selectedOption && selectedOption.value !== '') {
        const selectedText = document.getElementById('selected-customer-text');
        selectedText.textContent = selectedOption.textContent;
    } else {
        // Si no hay cliente seleccionado, mostrar texto por defecto
        const selectedText = document.getElementById('selected-customer-text');
        selectedText.textContent = 'Seleccione un cliente';
        selectedText.className = 'text-gray-500';
    }
}

// Inicializar el select personalizado cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    createCustomSelect();
});
