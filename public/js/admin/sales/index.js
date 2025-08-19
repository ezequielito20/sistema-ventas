/**
 * JavaScript optimizado para la vista de ventas
 * Archivo: public/js/admin/sales/index.js
 * Versión: 1.0.0
 */

// ===== CONFIGURACIÓN INICIAL =====
document.addEventListener('DOMContentLoaded', function() {
    // Obtener el símbolo de moneda desde el atributo data
    const currencySymbol = document.getElementById('salesRoot').dataset.currencySymbol;
    
    // Variables globales
    let currentView = 'table';
    let filteredSales = [];
    let allSales = [];
    
    // ===== INICIALIZACIÓN =====
    initializeSalesView();
    
    // ===== FUNCIONES PRINCIPALES =====
    function initializeSalesView() {
        // Cargar datos iniciales
        loadSalesData();
        
        // Configurar event listeners
        setupEventListeners();
        
        // Configurar filtros
        setupFilters();
        
        // Configurar búsqueda
        setupSearch();
        
        // Configurar toggles de vista
        setupViewToggles();
        
        // Configurar modales
        setupModals();
        
        // Configurar acciones
        setupActions();
    }
    
    function loadSalesData() {
        // Obtener todas las filas de la tabla
        const tableRows = document.querySelectorAll('#salesTable tbody tr');
        const mobileCards = document.querySelectorAll('.mobile-sale-card');
        const cardItems = document.querySelectorAll('.modern-sale-card');
        
        // Convertir a array de objetos
        allSales = Array.from(tableRows).map((row, index) => {
            const saleId = row.querySelector('.view-details').dataset.id;
            const customerName = row.querySelector('.customer-name').textContent;
            const customerEmail = row.querySelector('.customer-email').textContent;
            const saleDate = row.querySelector('.date-main').textContent;
            const saleTime = row.querySelector('.date-time').textContent;
            const totalPrice = parseFloat(row.querySelector('.price-amount').textContent.replace(currencySymbol, '').replace(/,/g, ''));
            const uniqueProducts = parseInt(row.querySelector('.product-badge.unique span').textContent);
            const totalProducts = parseInt(row.querySelector('.product-badge.total span').textContent);
            
            return {
                id: saleId,
                customerName,
                customerEmail,
                saleDate,
                saleTime,
                totalPrice,
                uniqueProducts,
                totalProducts,
                element: row,
                mobileElement: mobileCards[index],
                cardElement: cardItems[index]
            };
        });
        
        filteredSales = [...allSales];
    }
    
    function setupEventListeners() {
        // Event listeners para filtros
        document.getElementById('filtersToggle').addEventListener('click', toggleFilters);
        document.getElementById('applyFilters').addEventListener('click', applyFilters);
        document.getElementById('clearFilters').addEventListener('click', clearFilters);
        
        // Event listeners para búsqueda
        document.getElementById('salesSearch').addEventListener('input', handleSearch);
        
        // Event listeners para toggles de vista
        document.querySelectorAll('.view-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const view = this.dataset.view;
                switchView(view);
            });
        });
        
        // Event listeners para acciones
        document.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', function() {
                const saleId = this.dataset.id;
                showSaleDetails(saleId);
            });
        });
        
        document.querySelectorAll('.delete-sale').forEach(button => {
            button.addEventListener('click', function() {
                const saleId = this.dataset.id;
                deleteSale(saleId);
            });
        });
        
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const saleId = this.dataset.id;
                editSale(saleId);
            });
        });
        
        // Event listeners para modales
        document.querySelectorAll('[data-dismiss="modal"]').forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    closeModal(modal);
                }
            });
        });
        
        // Cerrar modales con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('.modal.show');
                openModals.forEach(modal => closeModal(modal));
            }
        });
        
        // Cerrar modales al hacer clic en el backdrop
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal(this);
                }
            });
        });
    }
    
    // ===== FUNCIONES DE FILTROS =====
    function setupFilters() {
        // Configurar filtros de fecha
        const dateFrom = document.getElementById('dateFrom');
        const dateTo = document.getElementById('dateTo');
        
        // Configurar filtros de monto
        const amountMin = document.getElementById('amountMin');
        const amountMax = document.getElementById('amountMax');
        
        // Event listeners para filtros en tiempo real
        [dateFrom, dateTo, amountMin, amountMax].forEach(input => {
            input.addEventListener('input', updateFiltersStatus);
        });
    }
    
    function toggleFilters() {
        const filtersContent = document.getElementById('filtersContent');
        const filtersToggle = document.getElementById('filtersToggle');
        
        filtersContent.classList.toggle('show');
        
        // Rotar el ícono
        const icon = filtersToggle.querySelector('i');
        if (filtersContent.classList.contains('show')) {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        } else {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }
    
    function applyFilters() {
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const amountMin = parseFloat(document.getElementById('amountMin').value) || 0;
        const amountMax = parseFloat(document.getElementById('amountMax').value) || Infinity;
        
        filteredSales = allSales.filter(sale => {
            // Filtro de fecha
            if (dateFrom || dateTo) {
                const saleDate = new Date(sale.saleDate + ' ' + sale.saleTime);
                const fromDate = dateFrom ? new Date(dateFrom) : new Date(0);
                const toDate = dateTo ? new Date(dateTo + ' 23:59:59') : new Date(8640000000000000);
                
                if (saleDate < fromDate || saleDate > toDate) {
                    return false;
                }
            }
            
            // Filtro de monto
            if (sale.totalPrice < amountMin || sale.totalPrice > amountMax) {
                return false;
            }
            
            return true;
        });
        
        updateSalesDisplay();
        updateFiltersStatus();
        showFiltersStatus();
    }
    
    function clearFilters() {
        // Limpiar inputs
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        document.getElementById('amountMin').value = '';
        document.getElementById('amountMax').value = '';
        
        // Restaurar datos originales
        filteredSales = [...allSales];
        
        // Actualizar display
        updateSalesDisplay();
        updateFiltersStatus();
        hideFiltersStatus();
    }
    
    function updateFiltersStatus() {
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const amountMin = document.getElementById('amountMin').value;
        const amountMax = document.getElementById('amountMax').value;
        
        const activeFilters = [];
        
        if (dateFrom || dateTo) {
            activeFilters.push('Fecha');
        }
        
        if (amountMin || amountMax) {
            activeFilters.push('Monto');
        }
        
        const activeFiltersList = document.getElementById('activeFiltersList');
        activeFiltersList.textContent = activeFilters.join(', ');
        
        return activeFilters.length > 0;
    }
    
    function showFiltersStatus() {
        const filtersStatus = document.getElementById('filtersStatus');
        filtersStatus.style.display = 'flex';
    }
    
    function hideFiltersStatus() {
        const filtersStatus = document.getElementById('filtersStatus');
        filtersStatus.style.display = 'none';
    }
    
    // ===== FUNCIONES DE BÚSQUEDA =====
    function setupSearch() {
        const searchInput = document.getElementById('salesSearch');
        const searchSuggestions = document.getElementById('searchSuggestions');
        
        // Event listener para búsqueda en tiempo real
        searchInput.addEventListener('input', handleSearch);
        
        // Event listener para cerrar sugerencias al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                searchSuggestions.style.display = 'none';
            }
        });
    }
    
    function handleSearch() {
        const searchTerm = document.getElementById('salesSearch').value.toLowerCase();
        
        if (searchTerm.length === 0) {
            // Si no hay término de búsqueda, usar filtros aplicados
            applyFilters();
            return;
        }
        
        // Filtrar por término de búsqueda
        filteredSales = allSales.filter(sale => {
            return sale.customerName.toLowerCase().includes(searchTerm) ||
                   sale.customerEmail.toLowerCase().includes(searchTerm) ||
                   sale.saleDate.includes(searchTerm) ||
                   sale.saleTime.includes(searchTerm);
        });
        
        updateSalesDisplay();
        showSearchSuggestions(searchTerm);
    }
    
    function showSearchSuggestions(searchTerm) {
        const searchSuggestions = document.getElementById('searchSuggestions');
        
        if (searchTerm.length === 0) {
            searchSuggestions.style.display = 'none';
            return;
        }
        
        // Generar sugerencias
        const suggestions = generateSuggestions(searchTerm);
        
        if (suggestions.length === 0) {
            searchSuggestions.style.display = 'none';
            return;
        }
        
        // Mostrar sugerencias
        searchSuggestions.innerHTML = suggestions.map(suggestion => 
            `<div class="suggestion-item">${suggestion}</div>`
        ).join('');
        
        searchSuggestions.style.display = 'block';
        
        // Event listeners para sugerencias
        searchSuggestions.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', function() {
                document.getElementById('salesSearch').value = this.textContent;
                searchSuggestions.style.display = 'none';
                handleSearch();
            });
        });
    }
    
    function generateSuggestions(searchTerm) {
        const suggestions = new Set();
        
        allSales.forEach(sale => {
            if (sale.customerName.toLowerCase().includes(searchTerm)) {
                suggestions.add(sale.customerName);
            }
            if (sale.customerEmail.toLowerCase().includes(searchTerm)) {
                suggestions.add(sale.customerEmail);
            }
            if (sale.saleDate.includes(searchTerm)) {
                suggestions.add(sale.saleDate);
            }
        });
        
        return Array.from(suggestions).slice(0, 5);
    }
    
    // ===== FUNCIONES DE VISTA =====
    function setupViewToggles() {
        // La configuración ya se hizo en setupEventListeners
    }
    
    function switchView(view) {
        // Actualizar toggles
        document.querySelectorAll('.view-toggle').forEach(toggle => {
            toggle.classList.remove('active');
        });
        document.querySelector(`[data-view="${view}"]`).classList.add('active');
        
        // Ocultar todas las vistas
        document.getElementById('tableView').style.display = 'none';
        document.getElementById('mobileView').style.display = 'none';
        document.getElementById('cardsView').style.display = 'none';
        
        // Mostrar vista seleccionada
        switch (view) {
            case 'table':
                document.getElementById('tableView').style.display = 'block';
                break;
            case 'cards':
                document.getElementById('cardsView').style.display = 'block';
                break;
        }
        
        currentView = view;
        updateSalesDisplay();
    }
    
    function updateSalesDisplay() {
        // Ocultar elementos que no están en filteredSales
        allSales.forEach((sale, index) => {
            const isVisible = filteredSales.includes(sale);
            
            // Actualizar tabla
            if (sale.element) {
                sale.element.style.display = isVisible ? 'table-row' : 'none';
            }
            
            // Actualizar vista móvil
            if (sale.mobileElement) {
                sale.mobileElement.style.display = isVisible ? 'block' : 'none';
            }
            
            // Actualizar vista de tarjetas
            if (sale.cardElement) {
                sale.cardElement.style.display = isVisible ? 'block' : 'none';
            }
        });
        
        // Actualizar números de fila
        updateRowNumbers();
    }
    
    function updateRowNumbers() {
        let visibleIndex = 1;
        
        filteredSales.forEach(sale => {
            // Actualizar número en tabla
            const rowNumber = sale.element?.querySelector('.row-number');
            if (rowNumber) {
                rowNumber.textContent = visibleIndex;
            }
            
            // Actualizar número en vista móvil
            const mobileNumber = sale.mobileElement?.querySelector('.number-badge');
            if (mobileNumber) {
                mobileNumber.textContent = `#${visibleIndex}`;
            }
            
            // Actualizar número en vista de tarjetas
            const cardNumber = sale.cardElement?.querySelector('.sale-number');
            if (cardNumber) {
                cardNumber.textContent = `#${String(visibleIndex).padStart(3, '0')}`;
            }
            
            visibleIndex++;
        });
    }
    
    // ===== FUNCIONES DE MODALES =====
    function setupModals() {
        // La configuración ya se hizo en setupEventListeners
    }
    
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            
            // Agregar backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = `${modalId}Backdrop`;
            document.body.appendChild(backdrop);
        }
    }
    
    function closeModal(modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        
        // Remover backdrop
        const backdropId = modal.id + 'Backdrop';
        const backdrop = document.getElementById(backdropId);
        if (backdrop) {
            backdrop.remove();
        }
    }
    
    // ===== FUNCIONES DE ACCIONES =====
    function setupActions() {
        // La configuración ya se hizo en setupEventListeners
    }
    
    async function showSaleDetails(saleId) {
        try {
            // Mostrar loading
            showLoadingAlert('Cargando detalles de la venta...');
            
            // Hacer petición al servidor
            const response = await fetch(`/admin/sales/${saleId}/details`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error('Error al cargar los detalles');
            }
            
            const data = await response.json();
            
            if (data.status === 'success') {
                // Llenar datos en el modal
                fillSaleDetailsModal(data.sale);
                
                // Cerrar loading y mostrar modal
                closeLoadingAlert();
                showModal('saleDetailsModal');
            } else {
                throw new Error(data.message || 'Error al cargar los detalles');
            }
        } catch (error) {
            closeLoadingAlert();
            showAlert({
                icon: 'error',
                title: 'Error',
                text: error.message
            });
        }
    }
    
    function fillSaleDetailsModal(sale) {
        // Información del cliente
        const customerInfo = document.getElementById('customerInfo');
        customerInfo.innerHTML = `
            <div class="customer-detail">
                <strong>Nombre:</strong> ${sale.customer.name}
            </div>
            <div class="customer-detail">
                <strong>Email:</strong> ${sale.customer.email}
            </div>
            <div class="customer-detail">
                <strong>Teléfono:</strong> ${sale.customer.phone || 'No especificado'}
            </div>
        `;
        
        // Fecha de venta
        const saleDate = document.getElementById('saleDate');
        saleDate.innerHTML = `
            <div class="date-detail">
                <strong>Fecha:</strong> ${formatDate(sale.sale_date)}
            </div>
            <div class="date-detail">
                <strong>Hora:</strong> ${formatTime(sale.sale_date)}
            </div>
        `;
        
        // Productos
        const tableBody = document.getElementById('saleDetailsTableBody');
        tableBody.innerHTML = sale.sale_details.map(detail => `
            <tr>
                <td>${detail.product.code}</td>
                <td>${detail.product.name}</td>
                <td>${detail.product.category?.name || 'Sin categoría'}</td>
                <td class="text-center">${detail.quantity}</td>
                <td class="text-right">${currencySymbol} ${formatNumber(detail.unit_price)}</td>
                <td class="text-right">${currencySymbol} ${formatNumber(detail.subtotal)}</td>
            </tr>
        `).join('');
        
        // Total
        document.getElementById('modalTotal').textContent = formatNumber(sale.total_price);
        
        // Nota (si existe)
        const noteCard = document.getElementById('noteCard');
        const noteText = document.getElementById('noteText');
        
        if (sale.note) {
            noteText.textContent = sale.note;
            noteCard.style.display = 'flex';
        } else {
            noteCard.style.display = 'none';
        }
    }
    
    async function deleteSale(saleId) {
        const result = await showAlert({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede revertir',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
            try {
                showLoadingAlert('Eliminando venta...');
                
                const response = await fetch(`/admin/sales/${saleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Error al eliminar la venta');
                }
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    closeLoadingAlert();
                    showAlert({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: data.message
                    });
                    
                    // Recargar la página para actualizar los datos
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Error al eliminar la venta');
                }
            } catch (error) {
                closeLoadingAlert();
                showAlert({
                    icon: 'error',
                    title: 'Error',
                    text: error.message
                });
            }
        }
    }
    
    function editSale(saleId) {
        // Redirigir a la página de edición
        window.location.href = `/admin/sales/${saleId}/edit`;
    }
    
    // ===== FUNCIONES DE UTILIDAD =====
    function formatNumber(number) {
        return new Intl.NumberFormat('es-ES', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(number);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES');
    }
    
    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    function showAlert(options) {
        if (typeof Swal !== 'undefined') {
            return Swal.fire(options);
        } else {
            // Fallback a alert nativo
            alert(options.text || options.title);
            return Promise.resolve({ isConfirmed: true });
        }
    }
    
    function showLoadingAlert(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    }
    
    function closeLoadingAlert() {
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
    }
    
    // ===== EVENT LISTENERS ADICIONALES =====
    
    // Imprimir detalles
    document.querySelectorAll('.print-details').forEach(button => {
        button.addEventListener('click', function() {
            window.print();
        });
    });
    
    // Event listeners para botones de acción en vista móvil
    document.querySelectorAll('.mobile-btn-action').forEach(button => {
        button.addEventListener('click', function() {
            const saleId = this.dataset.id;
            
            if (this.classList.contains('delete-sale')) {
                deleteSale(saleId);
            } else if (this.classList.contains('btn-edit')) {
                editSale(saleId);
            }
        });
    });
    
    // Event listeners para botones de acción en vista de tarjetas
    document.querySelectorAll('.btn-card-action').forEach(button => {
        button.addEventListener('click', function() {
            const saleId = this.dataset.id;
            
            if (this.classList.contains('delete')) {
                deleteSale(saleId);
            } else if (this.classList.contains('btn-edit')) {
                editSale(saleId);
            } else if (this.classList.contains('print')) {
                window.print();
            }
        });
    });
    
    // ===== INICIALIZACIÓN FINAL =====
    
    // Configurar vista inicial
    switchView('table');
    
    // Configurar responsive
    setupResponsive();
    
    function setupResponsive() {
        function handleResize() {
            const width = window.innerWidth;
            
            if (width <= 768) {
                // En móvil, forzar vista móvil
                if (currentView !== 'mobile') {
                    switchView('mobile');
                }
            } else if (width <= 1024) {
                // En tablet, mantener vista actual o cambiar a tabla
                if (currentView === 'mobile') {
                    switchView('table');
                }
            }
        }
        
        // Ejecutar al cargar y al cambiar tamaño
        handleResize();
        window.addEventListener('resize', handleResize);
    }
    
    console.log('✅ Vista de ventas inicializada correctamente');
});