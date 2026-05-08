// ===== UTILIDADES PARA TABLAS SIMPLES CON ALPINE.JS =====

// Componente Alpine.js para tablas con funcionalidad de búsqueda, ordenamiento y paginación
window.simpleTable = function(config = {}) {
    return {
        // Configuración
        search: '',
        sortBy: config.sortBy || null,
        sortDirection: config.sortDirection || 'asc',
        currentPage: 1,
        itemsPerPage: config.itemsPerPage || 10,
        items: config.items || [],
        filteredItems: [],
        
        // Estados
        loading: false,
        selectedItems: [],
        
        init() {
            this.filterItems();
            this.$watch('search', () => {
                this.currentPage = 1;
                this.filterItems();
            });
        },
        
        // Filtrar elementos basado en búsqueda
        filterItems() {
            if (!this.search.trim()) {
                this.filteredItems = [...this.items];
            } else {
                const searchTerm = this.search.toLowerCase();
                this.filteredItems = this.items.filter(item => {
                    return Object.values(item).some(value => 
                        String(value).toLowerCase().includes(searchTerm)
                    );
                });
            }
            
            // Aplicar ordenamiento
            if (this.sortBy) {
                this.sortItems();
            }
        },
        
        // Ordenar elementos
        sortItems() {
            this.filteredItems.sort((a, b) => {
                let aVal = a[this.sortBy];
                let bVal = b[this.sortBy];
                
                // Convertir a números si es posible
                if (!isNaN(aVal) && !isNaN(bVal)) {
                    aVal = parseFloat(aVal);
                    bVal = parseFloat(bVal);
                } else {
                    aVal = String(aVal).toLowerCase();
                    bVal = String(bVal).toLowerCase();
                }
                
                if (this.sortDirection === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
        },
        
        // Cambiar ordenamiento
        sort(column) {
            if (this.sortBy === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = column;
                this.sortDirection = 'asc';
            }
            this.sortItems();
        },
        
        // Obtener elementos paginados
        get paginatedItems() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredItems.slice(start, end);
        },
        
        // Obtener total de páginas
        get totalPages() {
            return Math.ceil(this.filteredItems.length / this.itemsPerPage);
        },
        
        // Navegar a página
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        
        // Obtener rango de páginas para mostrar
        get pageRange() {
            const range = [];
            const maxPages = 5;
            let start = Math.max(1, this.currentPage - Math.floor(maxPages / 2));
            let end = Math.min(this.totalPages, start + maxPages - 1);
            
            if (end - start + 1 < maxPages) {
                start = Math.max(1, end - maxPages + 1);
            }
            
            for (let i = start; i <= end; i++) {
                range.push(i);
            }
            
            return range;
        },
        
        // Seleccionar/deseleccionar todos
        toggleSelectAll() {
            if (this.selectedItems.length === this.paginatedItems.length) {
                this.selectedItems = [];
            } else {
                this.selectedItems = this.paginatedItems.map(item => item.id);
            }
        },
        
        // Seleccionar/deseleccionar item
        toggleSelectItem(id) {
            const index = this.selectedItems.indexOf(id);
            if (index > -1) {
                this.selectedItems.splice(index, 1);
            } else {
                this.selectedItems.push(id);
            }
        },
        
        // Verificar si item está seleccionado
        isSelected(id) {
            return this.selectedItems.includes(id);
        },
        
        // Verificar si todos están seleccionados
        get allSelected() {
            return this.paginatedItems.length > 0 && 
                   this.selectedItems.length === this.paginatedItems.length;
        },
        
        // Verificar si algunos están seleccionados
        get someSelected() {
            return this.selectedItems.length > 0 && 
                   this.selectedItems.length < this.paginatedItems.length;
        },
        
        // Formatear fecha
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },
        
        // Formatear moneda
        formatCurrency(amount) {
            if (amount === null || amount === undefined) return '';
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        },
        
        // Formatear número
        formatNumber(number) {
            if (number === null || number === undefined) return '';
            return new Intl.NumberFormat('es-ES').format(number);
        },
        
        // Obtener badge de estado
        getStatusBadge(status) {
            const badges = {
                'active': 'bg-green-100 text-green-800',
                'inactive': 'bg-red-100 text-red-800',
                'pending': 'bg-yellow-100 text-yellow-800',
                'completed': 'bg-blue-100 text-blue-800',
                'cancelled': 'bg-gray-100 text-gray-800'
            };
            
            return badges[status] || 'bg-gray-100 text-gray-800';
        }
    };
};

// Función para inicializar tablas desde JavaScript
window.initSimpleTable = function(elementId, config) {
    const element = document.getElementById(elementId);
    if (element && element._x_dataStack) {
        const component = element._x_dataStack[0];
        Object.assign(component, config);
        component.filterItems();
    }
};

// Función para actualizar datos de tabla
window.updateTableData = function(elementId, newData) {
    const element = document.getElementById(elementId);
    if (element && element._x_dataStack) {
        const component = element._x_dataStack[0];
        component.items = newData;
        component.filterItems();
    }
};
