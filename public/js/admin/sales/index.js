/**
 * SPA de Gestión de Ventas con Alpine.js
 * Archivo: public/js/admin/sales/index.js
 * Versión: 2.0.0 - SPA Edition con Paginación del Servidor
 */


// Esperar a que Alpine.js esté disponible
document.addEventListener('alpine:init', () => {
    
    Alpine.data('salesSPA', () => ({
        // ===== ESTADO DEL COMPONENTE =====
        loading: false,
        currentView: 'table',
        searchTerm: '',
        searchSuggestions: [],
        filtersOpen: false,
        modalOpen: false,
        
        // Datos
        allSales: [],
        filteredSales: [],
        selectedSale: null,
        
        // Filtros
        filters: {
            dateFrom: '',
            dateTo: '',
            amountMin: '',
            amountMax: ''
        },
        
        // Paginación del lado del servidor
        currentPage: 1,
        itemsPerPage: 15,
        
        // ===== COMPUTED PROPERTIES =====
        get paginatedSales() {
            // Para la paginación del lado del servidor, usamos directamente los datos
            return this.filteredSales;
        },
        
        get totalPages() {
            return Math.ceil(this.filteredSales.length / this.itemsPerPage);
        },
        
        get visiblePages() {
            const pages = [];
            const total = this.totalPages;
            const current = this.currentPage;
            
            if (total <= 7) {
                for (let i = 1; i <= total; i++) {
                    pages.push(i);
                }
            } else {
                if (current <= 4) {
                    for (let i = 1; i <= 5; i++) pages.push(i);
                    pages.push('...');
                    pages.push(total);
                } else if (current >= total - 3) {
                    pages.push(1);
                    pages.push('...');
                    for (let i = total - 4; i <= total; i++) pages.push(i);
                } else {
                    pages.push(1);
                    pages.push('...');
                    for (let i = current - 1; i <= current + 1; i++) pages.push(i);
                    pages.push('...');
                    pages.push(total);
                }
            }
            
            return pages;
        },
        
        get activeFiltersCount() {
            let count = 0;
            if (this.filters.dateFrom) count++;
            if (this.filters.dateTo) count++;
            if (this.filters.amountMin) count++;
            if (this.filters.amountMax) count++;
            if (this.searchTerm.trim()) count++;
            return count;
        },
        
        // ===== INICIALIZACIÓN =====
        async init() {
            try {
                this.loading = true;
                
                // Hacer el componente disponible globalmente para los onclick handlers
                window.salesSPAInstance = this;
                
                // Cargar datos iniciales desde la ventana global
                if (window.salesData) {
                    this.allSales = window.salesData;
                    this.filteredSales = [...this.allSales];
                } else {
                    // Fallback: cargar desde API
                    await this.loadSales();
                }
                
                // Renderizar contenido inicial
                this.renderTableFromData();
                this.renderCardsFromData();
                this.updatePagination();
                this.updateResultsCount();
                
                // Restaurar vista guardada
                const savedView = localStorage.getItem('salesViewPreference') || 'table';
                // En pantallas muy pequeñas, forzar vista de tarjetas
                this.currentView = this.isMobileView() ? 'cards' : (this.isMobile() ? 'cards' : savedView);
                
                // Configurar responsive
                this.setupResponsive();
                
                // Inicializar búsqueda desde URL si existe
                this.initializeSearchFromURL();
                
                // Inicializar manejadores de paginación
                this.initializePaginationHandlers();
                
                // Configurar watchers para búsqueda en tiempo real
                this.$watch('searchTerm', (value) => {
                    this.updateSearchSuggestions();
                });
                
                // Configurar watchers para filtros en tiempo real
                this.$watch('filters.dateFrom', () => {
                    this.filterSales();
                });
                
                this.$watch('filters.dateTo', () => {
                    this.filterSales();
                });
                
                this.$watch('filters.amountMin', () => {
                    this.filterSales();
                });
                
                this.$watch('filters.amountMax', () => {
                    this.filterSales();
                });
                
                this.loading = false;
            } catch (error) {
                this.showAlert('Error al cargar los datos', 'error');
                this.loading = false;
            }
        },
        
        async loadSales() {
            try {
                const response = await fetch('/api/sales');
                if (!response.ok) throw new Error('Error al cargar ventas');
                
                const data = await response.json();
                this.allSales = data.sales || [];
                this.filteredSales = [...this.allSales];
            } catch (error) {
                console.error('Error cargando ventas:', error);
                throw error;
            }
        },
        
        // ===== FUNCIONES DE FILTRADO Y BÚSQUEDA =====
        filterSales() {
            // Búsqueda en tiempo real con debounce
            clearTimeout(this._searchTimeout);
            this._searchTimeout = setTimeout(() => {
                this.executeServerSearch();
            }, 300);
        },
        
        executeServerSearch() {
            const searchTerm = this.searchTerm.trim();
            
            // Construir URL con parámetros de búsqueda
            const url = new URL(window.location.href);
            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            } else {
                url.searchParams.delete('search');
            }
            
            // Agregar filtros de fecha
            if (this.filters.dateFrom) {
                url.searchParams.set('dateFrom', this.filters.dateFrom);
            } else {
                url.searchParams.delete('dateFrom');
            }
            
            if (this.filters.dateTo) {
                url.searchParams.set('dateTo', this.filters.dateTo);
            } else {
                url.searchParams.delete('dateTo');
            }
            
            // Agregar filtros de monto
            if (this.filters.amountMin) {
                url.searchParams.set('amountMin', this.filters.amountMin);
            } else {
                url.searchParams.delete('amountMin');
            }
            
            if (this.filters.amountMax) {
                url.searchParams.set('amountMax', this.filters.amountMax);
            } else {
                url.searchParams.delete('amountMax');
            }
            
            // Realizar petición AJAX
            fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json, text/html, application/xhtml+xml'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la búsqueda');
                }
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response.text();
                }
            })
            .then(data => {
                if (typeof data === 'object' && data.success) {
                    // Respuesta JSON del servidor
                    this.updateTableWithJsonData(data, searchTerm);
                } else {
                    // Respuesta HTML (fallback)
                    this.updateTableWithSearchResults(data, searchTerm);
                }
            })
            .catch(error => {
                console.error('Error en búsqueda:', error);
                this.showSearchError('Error al realizar la búsqueda');
            });
        },

        updateTableWithSearchResults(html, searchTerm) {
            // Crear un elemento temporal para parsear el HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            // Extraer la nueva tabla
            const newTableBody = tempDiv.querySelector('tbody');
            const newCardsContainer = tempDiv.querySelector('.modern-cards-grid');
            const newPagination = tempDiv.querySelector('.pagination-container');
            
            // Actualizar la tabla
            const currentTableBody = document.querySelector('.modern-table tbody');
            if (newTableBody && currentTableBody) {
                currentTableBody.innerHTML = newTableBody.innerHTML;
            }
            
            // Actualizar las tarjetas
            const currentCardsContainer = document.querySelector('.modern-cards-grid');
            if (newCardsContainer && currentCardsContainer) {
                currentCardsContainer.innerHTML = newCardsContainer.innerHTML;
            }
            
            // Actualizar paginación
            const currentPagination = document.querySelector('.pagination-container');
            if (newPagination && currentPagination) {
                currentPagination.innerHTML = newPagination.innerHTML;
            }
            
            // Actualizar datos locales si están disponibles
            const scriptTag = tempDiv.querySelector('script');
            if (scriptTag) {
                try {
                    const scriptContent = scriptTag.textContent;
                    const match = scriptContent.match(/window\.salesData\s*=\s*(\[.*?\]);/s);
                    if (match) {
                        const newSalesData = JSON.parse(match[1]);
                        this.allSales = newSalesData;
                        this.filteredSales = [...newSalesData];
                    }
                } catch (e) {
                    console.error('Error parsing sales data:', e);
                }
            }
            
            // Actualizar contador de resultados
            this.updateResultsCount();
            
            // Actualizar URL sin recargar la página
            if (searchTerm || this.activeFiltersCount > 0) {
                const url = new URL(window.location.href);
                if (searchTerm) {
                    url.searchParams.set('search', searchTerm);
                }
                window.history.pushState({}, '', url.toString());
            } else {
                window.history.pushState({}, '', window.location.pathname);
            }
        },

       

        showSearchError(message) {
            // Mostrar error de búsqueda
            Swal.fire({
                icon: 'error',
                title: 'Error en la búsqueda',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        },
        
        updateSearchSuggestions() {
            if (!this.searchTerm.trim() || this.searchTerm.length < 2) {
                this.searchSuggestions = [];
                return;
            }
            
            const term = this.searchTerm.toLowerCase();
            const suggestions = [];
            
            // Sugerencias de clientes
            const customers = [...new Set(this.allSales.map(sale => sale.customer?.name).filter(Boolean))];
            customers.forEach(customer => {
                if (customer.toLowerCase().includes(term) && suggestions.length < 5) {
                    suggestions.push({
                        id: `customer-${customer}`,
                        text: `Cliente: ${customer}`,
                        type: 'customer',
                        value: customer
                    });
                }
            });
            
            // Sugerencias de IDs de venta
            const saleIds = this.allSales.map(sale => sale.id.toString());
            saleIds.forEach(id => {
                if (id.includes(term) && suggestions.length < 5) {
                    suggestions.push({
                        id: `sale-${id}`,
                        text: `Venta #${id}`,
                        type: 'sale',
                        value: id
                    });
                }
            });
            
            // Sugerencias de fechas (evitar duplicados)
            const dateMap = new Map();
            this.allSales.forEach(sale => {
                const date = new Date(sale.sale_date);
                const dateKey = date.toISOString().split('T')[0]; // YYYY-MM-DD
                
                if (!dateMap.has(dateKey)) {
                    const day = date.getDate().toString().padStart(2, '0');
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const year = date.getFullYear().toString();
                    const yearShort = year.slice(-2);
                    
                    dateMap.set(dateKey, {
                        full: date.toLocaleDateString('es-ES'),
                        day: day,
                        month: month,
                        year: year,
                        yearShort: yearShort,
                        monthName: date.toLocaleDateString('es-ES', { month: 'long' }),
                        monthShort: date.toLocaleDateString('es-ES', { month: 'short' }),
                        // Formatos adicionales
                        dd_mm_yy: `${day}/${month}/${yearShort}`,
                        dd_mm_yyyy: `${day}/${month}/${year}`,
                        dd_mm: `${day}/${month}`
                    });
                }
            });
            
            // Sugerencias de productos
            const productMap = new Map();
            this.allSales.forEach(sale => {
                sale.sale_details?.forEach(detail => {
                    if (detail.product) {
                        const productKey = detail.product.id;
                        if (!productMap.has(productKey)) {
                            productMap.set(productKey, {
                                code: detail.product.code,
                                name: detail.product.name,
                                category: detail.product.category?.name || 'Sin categoría'
                            });
                        }
                    }
                });
            });
            
            const products = Array.from(productMap.values());
            products.forEach(product => {
                // Buscar por código de producto
                if (product.code?.toLowerCase().includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `product-${product.code}`,
                        text: `Producto: ${product.code} - ${product.name}`,
                        type: 'product',
                        value: product.code
                    });
                }
                // Buscar por nombre de producto
                else if (product.name?.toLowerCase().includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `product-${product.name}`,
                        text: `Producto: ${product.name}`,
                        type: 'product',
                        value: product.name
                    });
                }
                // Buscar por categoría
                else if (product.category?.toLowerCase().includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `category-${product.category}`,
                        text: `Categoría: ${product.category}`,
                        type: 'category',
                        value: product.category
                    });
                }
            });
            
            // Sugerencias de montos
            const amounts = [...new Set(this.allSales.map(sale => sale.total_price).filter(Boolean))];
            amounts.forEach(amount => {
                const amountStr = amount.toString();
                if (amountStr.includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `amount-${amount}`,
                        text: `Monto: $${amount}`,
                        type: 'amount',
                        value: amountStr
                    });
                }
            });
            
            // Sugerencias de teléfonos de clientes
            const phones = [...new Set(this.allSales.map(sale => sale.customer?.phone).filter(Boolean))];
            phones.forEach(phone => {
                if (phone.toLowerCase().includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `phone-${phone}`,
                        text: `Teléfono: ${phone}`,
                        type: 'phone',
                        value: phone
                    });
                }
            });
            
            const dates = Array.from(dateMap.values());
            
            dates.forEach(date => {
                // Buscar por fecha completa
                if (date.full.toLowerCase().includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `date-${date.full}`,
                        text: `Fecha: ${date.full}`,
                        type: 'date',
                        value: date.full
                    });
                }
                // Buscar por formato dd/mm/aa
                else if (date.dd_mm_yy.includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `date-dd-mm-yy-${date.dd_mm_yy}`,
                        text: `Fecha: ${date.dd_mm_yy}`,
                        type: 'date',
                        value: date.dd_mm_yy
                    });
                }
                // Buscar por formato dd/mm/aaaa
                else if (date.dd_mm_yyyy.includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `date-dd-mm-yyyy-${date.dd_mm_yyyy}`,
                        text: `Fecha: ${date.dd_mm_yyyy}`,
                        type: 'date',
                        value: date.dd_mm_yyyy
                    });
                }
                // Buscar por formato dd/mm
                else if (date.dd_mm.includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `date-dd-mm-${date.dd_mm}`,
                        text: `Fecha: ${date.dd_mm}`,
                        type: 'date',
                        value: date.dd_mm
                    });
                }
                // Buscar por día
                else if (date.day.includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `day-${date.day}`,
                        text: `Día: ${date.day}`,
                        type: 'date',
                        value: date.day
                    });
                }
                // Buscar por mes (número)
                else if (date.month.toString().includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `month-${date.month}`,
                        text: `Mes: ${date.month}`,
                        type: 'date',
                        value: date.month.toString()
                    });
                }
                // Buscar por año
                else if (date.year.includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `year-${date.year}`,
                        text: `Año: ${date.year}`,
                        type: 'date',
                        value: date.year
                    });
                }
                // Buscar por año corto
                else if (date.yearShort.includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `year-short-${date.yearShort}`,
                        text: `Año: ${date.yearShort}`,
                        type: 'date',
                        value: date.yearShort
                    });
                }
                // Buscar por nombre de mes
                else if (date.monthName.toLowerCase().includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `monthname-${date.monthName}`,
                        text: `Mes: ${date.monthName}`,
                        type: 'date',
                        value: date.monthName
                    });
                }
                // Buscar por nombre corto de mes
                else if (date.monthShort.toLowerCase().includes(term) && suggestions.length < 8) {
                    suggestions.push({
                        id: `monthshort-${date.monthShort}`,
                        text: `Mes: ${date.monthShort}`,
                        type: 'date',
                        value: date.monthShort
                    });
                }
            });
            
            this.searchSuggestions = suggestions;
        },
        
        selectSuggestion(suggestion) {
            this.searchTerm = suggestion.value;
            this.searchSuggestions = [];
            // Ejecutar búsqueda inmediatamente al seleccionar una sugerencia
            this.executeServerSearch();
        },

        clearSearch() {
            this.searchTerm = '';
            this.searchSuggestions = [];
            // Ejecutar búsqueda inmediatamente al limpiar
            this.executeServerSearch();
        },
        
        clearFilters() {
            this.searchTerm = '';
            this.filters = {
                dateFrom: '',
                dateTo: '',
                amountMin: '',
                amountMax: ''
            };
            this.searchSuggestions = [];
            // Ejecutar búsqueda inmediatamente al limpiar filtros
            this.executeServerSearch();
        },
        
        updateResultsCount() {
            // Actualizar el contador de resultados mostrados
            const tableRows = document.querySelectorAll('.modern-table tbody tr');
            const cardItems = document.querySelectorAll('.modern-cards-grid .sale-card');
            const count = Math.max(tableRows.length, cardItems.length);
            
            // Actualizar algún elemento que muestre el contador si existe
            const counterElement = document.querySelector('.results-counter');
            if (counterElement) {
                counterElement.textContent = `${count} ventas encontradas`;
            }
        },
        
        updateTableWithJsonData(data, searchTerm) {
            // Actualizar datos locales
            this.allSales = data.sales || [];
            this.filteredSales = [...this.allSales];
            
            // Actualizar página actual desde la paginación
            if (data.pagination) {
                this.currentPage = data.pagination.current_page || 1;
            }
            
            // Actualizar la tabla con los nuevos datos
            this.renderTableFromData();
            
            // Actualizar las tarjetas
            this.renderCardsFromData();
            
            // Actualizar paginación
            this.updatePagination(data.pagination);
            
            // Actualizar contador de resultados
            this.updateResultsCount();
            
            // Actualizar URL sin recargar la página
            if (searchTerm || this.activeFiltersCount > 0) {
                const url = new URL(window.location.href);
                if (searchTerm) {
                    url.searchParams.set('search', searchTerm);
                }
                window.history.pushState({}, '', url.toString());
            } else {
                window.history.pushState({}, '', window.location.pathname);
            }
        },
        
        renderTableFromData() {
            const tableBody = document.getElementById('salesTableBody');
            if (!tableBody) return;
            
            tableBody.innerHTML = this.filteredSales.map((sale, index) => {
                const rowNumber = (this.currentPage - 1) * this.itemsPerPage + index + 1;
                const formattedDate = this.formatDate(sale.sale_date);
                const formattedTime = this.formatTime(sale.sale_date);
                const totalQuantity = this.getTotalQuantity(sale.sale_details);
                const formattedCurrency = this.formatCurrency(sale.total_price);
                
                return `
                    <tr class="table-row">
                        <td>
                            <div class="row-number">${rowNumber}</div>
                        </td>
                        <td>
                            <div class="customer-info">
                                <div class="customer-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="customer-details">
                                    <span class="customer-name">${sale.customer?.name || 'Cliente no especificado'}</span>
                                    <span class="customer-email">${sale.customer?.email || 'Sin email'}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="date-info">
                                <span class="date-main">${formattedDate}</span>
                                <span class="date-time">${formattedTime}</span>
                            </div>
                        </td>
                        <td>
                            <div class="products-info">
                                <div class="product-badge unique">
                                    <i class="fas fa-boxes"></i>
                                    <span>${sale.sale_details?.length || 0} únicos</span>
                                </div>
                                <div class="product-badge total">
                                    <i class="fas fa-cubes"></i>
                                    <span>${totalQuantity} totales</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="price-info">
                                <span class="price-amount">${formattedCurrency}</span>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; justify-content: center; align-items: center;">
                                <button type="button" 
                                        class="btn-modern btn-primary view-details"
                                        onclick="window.salesSPAInstance.showSaleDetails(${sale.id})">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button type="button" 
                                        class="btn-action btn-edit"
                                        onclick="window.salesSPAInstance.editSale(${sale.id})"
                                        title="Editar venta">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" 
                                        class="btn-action btn-delete"
                                        onclick="window.salesSPAInstance.deleteSale(${sale.id})"
                                        title="Eliminar venta">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        },
        
        renderCardsFromData() {
            const cardsContainer = document.getElementById('salesCardsGrid');
            if (!cardsContainer) return;
            
            cardsContainer.innerHTML = this.filteredSales.map((sale, index) => {
                const rowNumber = (this.currentPage - 1) * this.itemsPerPage + index + 1;
                const formattedDate = this.formatDate(sale.sale_date);
                const formattedTime = this.formatTime(sale.sale_date);
                const totalQuantity = this.getTotalQuantity(sale.sale_details);
                const formattedCurrency = this.formatCurrency(sale.total_price);
                
                return `
                    <div class="modern-sale-card">
                        <div class="sale-card-header">
                            <div class="sale-number">#${String(rowNumber).padStart(3, '0')}</div>
                            <div class="sale-status">
                                <span class="status-dot active"></span>
                                <span class="status-text">Completada</span>
                            </div>
                        </div>

                        <div class="sale-card-body">
                            <div class="customer-section">
                                <div class="customer-avatar-large">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="customer-info-card">
                                    <h4 class="customer-name">${sale.customer?.name || 'Cliente no especificado'}</h4>
                                    <p class="customer-phone">${sale.customer?.phone || 'Sin teléfono'}</p>
                                </div>
                            </div>

                            <div class="sale-details">
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Fecha</span>
                                    </div>
                                    <div class="detail-value">${formattedDate} ${formattedTime}</div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="fas fa-boxes"></i>
                                        <span>Productos</span>
                                    </div>
                                    <div class="detail-value">
                                        <div class="product-badges">
                                            <span class="mini-badge unique">${sale.sale_details?.length || 0} únicos</span>
                                            <span class="mini-badge total">${totalQuantity} totales</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="detail-row total-row">
                                    <div class="detail-label">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>Total</span>
                                    </div>
                                    <div class="detail-value total-amount">${formattedCurrency}</div>
                                </div>
                            </div>
                        </div>

                        <div class="sale-card-footer">
                            <button type="button" 
                                    class="btn-card-primary"
                                    onclick="window.salesSPAInstance.showSaleDetails(${sale.id})">
                                <i class="fas fa-list"></i>
                            </button>

                            <div class="card-actions">
                                <button type="button" 
                                        class="btn-card-action btn-edit"
                                        onclick="window.salesSPAInstance.editSale(${sale.id})"
                                        title="Editar venta">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" 
                                        class="btn-card-action btn-delete"
                                        onclick="window.salesSPAInstance.deleteSale(${sale.id})"
                                        title="Eliminar venta">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button type="button" 
                                        class="btn-card-action print"
                                        onclick="window.salesSPAInstance.printSale(${sale.id})"
                                        title="Imprimir venta">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        },
        
        updatePagination(pagination) {
            const paginationContainer = document.getElementById('salesPagination');
            if (!paginationContainer) return;
            
            // Si no hay paginación, mostrar información básica
            if (!pagination) {
                paginationContainer.innerHTML = `
                    <div class="pagination-info">
                        <span>Mostrando ${this.filteredSales.length} ventas</span>
                    </div>
                `;
                return;
            }
            
            // Generar enlaces de paginación
            const links = [];
            
            // Información de paginación
            const startItem = (pagination.current_page - 1) * pagination.per_page + 1;
            const endItem = Math.min(pagination.current_page * pagination.per_page, pagination.total);
            
            links.push(`
                <div class="pagination-info">
                    <span>Mostrando ${startItem}-${endItem} de ${pagination.total} ventas</span>
                </div>
            `);
            
            links.push('<div class="pagination-controls">');
            
            if (pagination.current_page > 1) {
                links.push(`<button onclick="window.salesSPAInstance.loadPage(${pagination.current_page - 1})" class="pagination-btn">
                    <i class="fas fa-chevron-left"></i>
                    <span>Anterior</span>
                </button>`);
            } else {
                links.push(`<button class="pagination-btn" disabled>
                    <i class="fas fa-chevron-left"></i>
                    <span>Anterior</span>
                </button>`);
            }
            
            // Números de página inteligentes
            links.push('<div class="page-numbers">');
            if (pagination.smart_links) {
                pagination.smart_links.forEach(link => {
                    if (link.isSeparator) {
                        links.push(`<span class="page-separator">${link.label}</span>`);
                    } else {
                        const activeClass = link.active ? 'active' : '';
                        links.push(`<button onclick="window.salesSPAInstance.loadPage(${link.page})" class="page-number ${activeClass}">${link.label}</button>`);
                    }
                });
            }
            links.push('</div>');
            
            if (pagination.current_page < pagination.last_page) {
                links.push(`<button onclick="window.salesSPAInstance.loadPage(${pagination.current_page + 1})" class="pagination-btn">
                    <span>Siguiente</span>
                    <i class="fas fa-chevron-right"></i>
                </button>`);
            } else {
                links.push(`<button class="pagination-btn" disabled>
                    <span>Siguiente</span>
                    <i class="fas fa-chevron-right"></i>
                </button>`);
            }
            
            links.push('</div>'); // Cerrar pagination-controls
            
            paginationContainer.innerHTML = links.join('');
        },
        
        // ===== FUNCIONES DE VISTA =====
        changeView(viewType) {
            this.currentView = viewType;
            localStorage.setItem('salesViewPreference', viewType);
        },
        
        toggleFilters() {
            this.filtersOpen = !this.filtersOpen;
        },
        
        // ===== FUNCIONES DE PAGINACIÓN =====
        initializeSearchFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            const searchParam = urlParams.get('search');
            const dateFromParam = urlParams.get('dateFrom');
            const dateToParam = urlParams.get('dateTo');
            const amountMinParam = urlParams.get('amountMin');
            const amountMaxParam = urlParams.get('amountMax');
            
            if (searchParam) {
                this.searchTerm = searchParam;
            }
            
            if (dateFromParam) {
                this.filters.dateFrom = dateFromParam;
            }
            
            if (dateToParam) {
                this.filters.dateTo = dateToParam;
            }
            
            if (amountMinParam) {
                this.filters.amountMin = amountMinParam;
            }
            
            if (amountMaxParam) {
                this.filters.amountMax = amountMaxParam;
            }
        },
        
        initializePaginationHandlers() {
            // Delegar eventos de clic para enlaces de paginación
            document.addEventListener('click', (e) => {
                if (e.target.closest('.pagination-btn') || e.target.closest('.page-number')) {
                    e.preventDefault();
                    const link = e.target.closest('a');
                    if (link) {
                        this.loadPage(link.href);
                    }
                }
            });
        },
        
        loadPage(pageNumber) {
            // Actualizar la página actual
            this.currentPage = pageNumber;
            
            // Construir la URL con los parámetros actuales
            const url = new URL(window.location.href);
            url.searchParams.set('page', pageNumber);
            
            // Mantener los filtros y búsqueda actuales
            if (this.searchTerm) {
                url.searchParams.set('search', this.searchTerm);
            }
            if (this.filters.dateFrom) {
                url.searchParams.set('dateFrom', this.filters.dateFrom);
            }
            if (this.filters.dateTo) {
                url.searchParams.set('dateTo', this.filters.dateTo);
            }
            if (this.filters.amountMin) {
                url.searchParams.set('amountMin', this.filters.amountMin);
            }
            if (this.filters.amountMax) {
                url.searchParams.set('amountMax', this.filters.amountMax);
            }
            
            // Realizar petición AJAX para la nueva página
            fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json, text/html, application/xhtml+xml'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al cargar la página');
                }
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response.text();
                }
            })
            .then(data => {
                if (typeof data === 'object' && data.success) {
                    // Respuesta JSON del servidor
                    this.updateTableWithJsonData(data, this.searchTerm);
                } else {
                    // Respuesta HTML (fallback)
                    this.updateTableWithSearchResults(data, this.searchTerm);
                }
                
                // Actualizar URL sin recargar la página
                window.history.pushState({}, '', url.toString());
            })
            .catch(error => {
                console.error('Error al cargar página:', error);
                this.showSearchError('Error al cargar la página');
            });
        },
        
        // ===== FUNCIONES DE MODAL =====
        async showSaleDetails(saleId) {
            try {
                this.loading = true;
                
                // Buscar la venta en los datos locales
                const sale = this.allSales.find(s => s.id == saleId);
                
                if (!sale) {
                    throw new Error('Venta no encontrada');
                }
                
                // Usar los datos locales en lugar de hacer llamada a API
                this.selectedSale = {
                    ...sale,
                    sale_details: sale.sale_details || [],
                    sale_date: sale.sale_date,
                    sale_time: this.formatTime(sale.sale_date)
                };
                
                this.modalOpen = true;
                
                // Mover modal al body para asegurar cobertura completa
                this.$nextTick(() => {
                    const overlay = this.$refs?.salesModal;
                    if (overlay && overlay.parentElement !== document.body) {
                        document.body.appendChild(overlay);
                    }
                });
                
                // Verificar que el modal esté en el DOM después de un pequeño delay
                setTimeout(() => {
                    const modal = document.querySelector('.modal-overlay');
                }, 100);
                
                // Bloquear scroll del body
                document.body.style.overflow = 'hidden';
                document.body.style.paddingRight = '0px';
                
            } catch (error) {
                this.showAlert('Error al cargar los detalles de la venta: ' + error.message, 'error');
            } finally {
                this.loading = false;
            }
        },
        
        closeModal() {
            this.modalOpen = false;
            this.selectedSale = null;
            
            // Restaurar scroll del body
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            
            // Devolver modal a su contenedor original si es necesario
            const overlay = this.$refs?.salesModal;
            const root = document.getElementById('salesRoot');
            if (overlay && root && overlay.parentElement === document.body) {
                root.appendChild(overlay);
            }
        },
        
        // ===== ACCIONES DE VENTA =====
        editSale(saleId) {
                            window.location.href = `/sales/edit/${saleId}`;
        },

        async deleteSale(saleId) {
            const confirmed = await this.showConfirmDialog(
                '¿Estás seguro de que quieres eliminar esta venta?',
                'Esta acción no se puede deshacer.'
            );

            if (!confirmed) return;

            try {
                const response = await fetch(`/sales/delete/${saleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    this.showAlert(data.message || 'Error al eliminar la venta', data.icons || 'warning');
                    return;
                }

                if (data.error) {
                    this.showAlert(data.message, 'error');
                } else {
                    this.showAlert(data.message || 'Venta eliminada correctamente', 'success');
                    
                    // Remover la venta de los datos locales
                    this.allSales = this.allSales.filter(sale => sale.id !== saleId);
                    this.filteredSales = this.filteredSales.filter(sale => sale.id !== saleId);
                }
            } catch (error) {
                console.error('Error:', error);
                this.showAlert('Error al eliminar la venta', 'error');
            }
        },
        
        printSale(saleId) {
            if (saleId) {
                window.open(`/sales/print/${saleId}`, '_blank');
            }
        },
        
        // ===== FUNCIONES AUXILIARES =====
        formatCurrency(amount) {
            const symbol = window.currencySymbol || '$';
            return `${symbol} ${parseFloat(amount || 0).toFixed(2)}`;
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        },
        
        formatTime(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        
        // Función helper para detectar si el término de búsqueda es una fecha
        isDateSearch(term) {
            // Patrones de fecha comunes
            const datePatterns = [
                /^\d{1,2}\/\d{1,2}\/\d{2,4}$/, // dd/mm/yyyy o dd/mm/yy
                /^\d{1,2}-\d{1,2}-\d{2,4}$/,   // dd-mm-yyyy o dd-mm-yy
                /^\d{4}-\d{1,2}-\d{1,2}$/,     // yyyy-mm-dd
                /^\d{1,2}$/,                    // solo día
                /^\d{1,2}$/,                    // solo mes
                /^\d{4}$/,                      // solo año
                /^(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)$/i,
                /^(ene|feb|mar|abr|may|jun|jul|ago|sep|oct|nov|dic)$/i
            ];
            
            return datePatterns.some(pattern => pattern.test(term));
        },
        
        getTotalQuantity(saleDetails) {
            if (!saleDetails || !Array.isArray(saleDetails)) return 0;
            return saleDetails.reduce((total, detail) => total + (detail.quantity || 0), 0);
        },
        
        isMobile() {
            return window.innerWidth <= 768;
        },
        
        isMobileView() {
            return window.innerWidth <= 450;
        },
        
        setupResponsive() {
            const handleResize = () => {
                const isMobile = this.isMobile();
                const isMobileView = this.isMobileView();
                
                // Forzar vista de tarjetas en pantallas muy pequeñas
                if (isMobileView && this.currentView === 'table') {
                    this.changeView('cards');
                } else if (isMobile && this.currentView === 'table') {
                    this.changeView('cards');
                }
            };
            
            window.addEventListener('resize', handleResize);
            handleResize(); // Ejecutar inmediatamente
        },
        
        // ===== FUNCIONES DE UI =====
        showAlert(message, type = 'info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? '¡Éxito!' : type === 'error' ? 'Error' : 'Información',
                    text: message,
                    icon: type,
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#667eea'
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
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#667eea',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        resolve(result.isConfirmed);
                    });
                } else {
                    resolve(confirm(`${title}\n${text}`));
                }
            });
        },


    }));
});

// Funciones globales para compatibilidad (mantener las existentes si es necesario)
window.showSaleDetails = function(saleId) {
    // Buscar la instancia de Alpine.js y ejecutar la función
    const salesComponent = Alpine.$data(document.getElementById('salesRoot'));
    if (salesComponent && salesComponent.showSaleDetails) {
        salesComponent.showSaleDetails(saleId);
    }
};

window.editSale = function(saleId) {
    window.location.href = `/sales/edit/${saleId}`;
};

window.deleteSale = function(saleId) {
    const salesComponent = Alpine.$data(document.getElementById('salesRoot'));
    if (salesComponent && salesComponent.deleteSale) {
        salesComponent.deleteSale(saleId);
    }
};

window.printSale = function(saleId) {
    window.open(`/sales/print/${saleId}`, '_blank');
};