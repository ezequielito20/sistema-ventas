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
                
                // Cargar datos iniciales desde la ventana global
                if (window.salesData) {
                    this.allSales = window.salesData;
                    this.filteredSales = [...this.allSales];
                } else {
                    // Fallback: cargar desde API
                    await this.loadSales();
                }
                
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
            // Para la búsqueda del lado del servidor, redirigir con parámetros
            this.executeServerSearch();
        },
        
        executeServerSearch() {
            const searchTerm = this.searchTerm.trim();
            
            // Mostrar indicador de carga
            this.showSearchLoading();
            
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
                    'Accept': 'text/html, application/xhtml+xml'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la búsqueda');
                }
                return response.text();
            })
            .then(html => {
                // Actualizar la tabla con los nuevos resultados
                this.updateTableWithSearchResults(html, searchTerm);
                this.hideSearchLoading();
            })
            .catch(error => {
                console.error('Error en búsqueda:', error);
                this.hideSearchLoading();
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

        showSearchLoading() {
            // Mostrar indicador de carga en la barra de búsqueda
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15\'/%3E%3C/svg%3E")';
                searchInput.style.backgroundRepeat = 'no-repeat';
                searchInput.style.backgroundPosition = 'right 0.5rem center';
                searchInput.style.backgroundSize = '1.5rem';
            }
        },

        hideSearchLoading() {
            // Ocultar indicador de carga
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.style.backgroundImage = '';
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
            
            this.searchSuggestions = suggestions;
        },
        
        selectSuggestion(suggestion) {
            this.searchTerm = suggestion.value;
            this.searchSuggestions = [];
            this.filterSales();
        },

        clearSearch() {
            this.searchTerm = '';
            this.searchSuggestions = [];
            this.filterSales();
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
            this.filterSales();
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
        
        loadPage(url) {
            // Mostrar indicador de carga
            this.showSearchLoading();
            
            // Realizar petición AJAX para la nueva página
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, application/xhtml+xml'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al cargar la página');
                }
                return response.text();
            })
            .then(html => {
                // Actualizar la tabla con los nuevos resultados
                this.updateTableWithSearchResults(html, this.searchTerm);
                this.hideSearchLoading();
                
                // Actualizar URL sin recargar la página
                window.history.pushState({}, '', url);
            })
            .catch(error => {
                console.error('Error al cargar página:', error);
                this.hideSearchLoading();
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