/**
 * SPA de Gestión de Ventas con Alpine.js
 * Archivo: public/js/admin/sales/index.js
 * Versión: 2.0.0 - SPA Edition
 */


// Esperar a que Alpine.js esté disponible
document.addEventListener('alpine:init', () => {
    
    Alpine.data('salesSPA', () => ({
        // ===== ESTADO DEL COMPONENTE =====
        loading: true,
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
        
        // Paginación
        currentPage: 1,
        itemsPerPage: 15,
        
        // ===== COMPUTED PROPERTIES =====
        get paginatedSales() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredSales.slice(start, end);
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
                } else {
                    // Fallback: cargar desde API
                    await this.loadSales();
                }
                
                this.filteredSales = [...this.allSales];
                
                // Restaurar vista guardada
                const savedView = localStorage.getItem('salesViewPreference') || 'table';
                // En pantallas muy pequeñas, forzar vista de tarjetas
                this.currentView = this.isMobileView() ? 'cards' : (this.isMobile() ? 'cards' : savedView);
                
                // Configurar responsive
                this.setupResponsive();
                
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
            } catch (error) {
                console.error('Error cargando ventas:', error);
                throw error;
            }
        },
        
        // ===== FUNCIONES DE FILTRADO Y BÚSQUEDA =====
        filterSales() {
            let filtered = [...this.allSales];
            
            // Filtro por término de búsqueda
            if (this.searchTerm.trim()) {
                const term = this.searchTerm.toLowerCase().trim();
                filtered = filtered.filter(sale => {
                    return (
                        sale.customer?.name?.toLowerCase().includes(term) ||
                        sale.customer?.email?.toLowerCase().includes(term) ||
                        sale.id.toString().includes(term) ||
                        this.formatDate(sale.sale_date).includes(term)
                    );
                });
            }
            
            // Filtro por fecha desde
            if (this.filters.dateFrom) {
                filtered = filtered.filter(sale => {
                    const saleDate = new Date(sale.sale_date);
                    const fromDate = new Date(this.filters.dateFrom);
                    return saleDate >= fromDate;
                });
            }
            
            // Filtro por fecha hasta
            if (this.filters.dateTo) {
                filtered = filtered.filter(sale => {
                    const saleDate = new Date(sale.sale_date);
                    const toDate = new Date(this.filters.dateTo);
                    return saleDate <= toDate;
                });
            }
            
            // Filtro por monto mínimo
            if (this.filters.amountMin) {
                filtered = filtered.filter(sale => {
                    return parseFloat(sale.total_price) >= parseFloat(this.filters.amountMin);
                });
            }
            
            // Filtro por monto máximo
            if (this.filters.amountMax) {
                filtered = filtered.filter(sale => {
                    return parseFloat(sale.total_price) <= parseFloat(this.filters.amountMax);
                });
            }
            
            this.filteredSales = filtered;
            this.currentPage = 1; // Resetear paginación
            this.updateSearchSuggestions();
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
        
        clearFilters() {
            this.searchTerm = '';
            this.filters = {
                dateFrom: '',
                dateTo: '',
                amountMin: '',
                amountMax: ''
            };
            this.searchSuggestions = [];
            this.filteredSales = [...this.allSales];
            this.currentPage = 1;
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
        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
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
                    this.filterSales();
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
        }
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