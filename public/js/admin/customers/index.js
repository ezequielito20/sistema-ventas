// ===== CONFIGURACIÓN OPTIMIZADA =====
const CONFIG = {
    routes: { delete: '/customers/delete' },
    exchangeRate: { default: 134.0, key: 'exchangeRate' }
};

// ===== FUNCIONES GLOBALES =====
window.customersIndex = {
    // Actualizar valores Bs - Optimizado
    updateBsValues: function(rate) {
        requestAnimationFrame(() => {
            document.querySelectorAll('.bs-debt').forEach(el => {
                const debt = parseFloat(el.dataset.debt);
                if (!isNaN(debt)) {
                    el.textContent = `Bs. ${(debt * rate).toLocaleString('es-VE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}`;
                }
            });
        });
    },

    // Función para eliminar cliente
    deleteCustomer: function(customerId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Obtener el token CSRF
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch(`${CONFIG.routes.delete}/${customerId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: data.message,
                            icon: data.icons
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        // Mostrar mensaje de error
                        let showCancelButton = false;
                        let cancelButtonText = '';
                        let confirmButtonText = 'Entendido';
                        
                        if (data.has_sales) {
                            showCancelButton = true;
                            cancelButtonText = 'Ver Ventas';
                            confirmButtonText = 'Entendido';
                        }
                        
                        Swal.fire({
                            title: data.icons === 'warning' ? 'No se puede eliminar' : 'Error',
                            html: data.message.replace(/\n/g, '<br>'),
                            icon: data.icons,
                            showCancelButton: showCancelButton,
                            cancelButtonText: cancelButtonText,
                            confirmButtonText: confirmButtonText
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.cancel && data.has_sales) {
                                // Redirigir a las ventas del cliente
                                window.location.href = data.sales_url;
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Ocurrió un error al eliminar el cliente',
                        icon: 'error'
                    });
                });
            }
        });
    }
};

// ===== FUNCIONES DE ALPINE.JS =====

// Función para el hero section
function heroSection() {
    return {
            init() {
        // Inicialización del hero section
    }
    }
}

// Panel de filtros - Optimizado
function filtersPanel() {
    return {
        filtersOpen: false,
        currentFilter: 'all',
        searchTerm: '',
        searchResultsCount: 0,
        hasActiveFilters: false,
        
        init() {
            setTimeout(() => {
                const saved = localStorage.getItem('customerFilter');
                if (saved) this.currentFilter = saved;
                this.updateSearchResultsCount();
            }, 0);
        },
        
        toggleFilters() { this.filtersOpen = !this.filtersOpen; },
        
        setFilter(filter) {
            this.currentFilter = filter;
            localStorage.setItem('customerFilter', filter);
            this.applyFilters();
        },
        
        performSearch() {
            clearTimeout(this._searchTimeout);
            this._searchTimeout = setTimeout(() => this.applyFilters(), 300);
        },
        
        clearSearch() {
            this.searchTerm = '';
            this.applyFilters();
        },
        
        applyFilters() {
            requestAnimationFrame(() => {
                const cards = document.querySelectorAll('[data-status]');
                let visibleCount = 0;
                
                cards.forEach(card => {
                    const status = card.dataset.status;
                    const isDefaulter = card.dataset.defaulter === 'true';
                    let shouldShow = false;
                    
                    switch (this.currentFilter) {
                        case 'all': shouldShow = true; break;
                        case 'active': shouldShow = status === 'active'; break;
                        case 'inactive': shouldShow = status === 'inactive'; break;
                        case 'defaulters': shouldShow = isDefaulter; break;
                    }
                    
                    if (shouldShow && this.searchTerm) {
                        const text = card.textContent.toLowerCase();
                        shouldShow = text.includes(this.searchTerm.toLowerCase());
                    }
                    
                    card.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) visibleCount++;
                });
                
                this.searchResultsCount = visibleCount;
                this.hasActiveFilters = this.currentFilter !== 'all' || this.searchTerm.length > 0;
            });
        },
        
        updateSearchResultsCount() {
            const visible = document.querySelectorAll('[data-status]:not([style*="display: none"])');
            this.searchResultsCount = visible.length;
        }
    };
}

// Función para sincronización de tipo de cambio en modal
function modalExchangeRateSync() {
    return {
        init() {
            // Sincronizar con el widget principal cuando se abre el modal
            this.$watch('debtReportModal', (value) => {
                if (value) {
                    this.syncFromWidget();
                }
            });
        },
        
        // Sincronizar desde el widget principal
        syncFromWidget() {
            const widgetElements = document.querySelectorAll('[x-data*="exchangeRateWidget"]');
            widgetElements.forEach(element => {
                if (element._x_dataStack && element._x_dataStack[0]) {
                    const widget = element._x_dataStack[0];
                    const modalInput = document.getElementById('modalExchangeRate');
                    if (modalInput) {
                        modalInput.value = widget.exchangeRate;
                    }
                }
            });
        },
        
        // Sincronizar hacia el widget principal
        syncFromModal(value) {
            const rate = parseFloat(value);
            if (!isNaN(rate) && rate > 0) {
                // Sincronizar con el widget principal
                const widgetElements = document.querySelectorAll('[x-data*="exchangeRateWidget"]');
                widgetElements.forEach(element => {
                    if (element._x_dataStack && element._x_dataStack[0]) {
                        const widget = element._x_dataStack[0];
                        widget.syncFromModal(rate);
                    }
                });
            }
        }
    };
}

// Función para el widget de tipo de cambio
function exchangeRateWidget() {
    return {
        exchangeRate: 134.0,
        updating: false,
        
        init() {
            // Cargar el tipo de cambio guardado
            const savedRate = localStorage.getItem('exchangeRate');
            if (savedRate) {
                this.exchangeRate = parseFloat(savedRate);
            } else {
                this.exchangeRate = window.exchangeRate || 134.0;
            }
            
            // Watcher para sincronizar automáticamente cuando cambie el valor
            this.$watch('exchangeRate', (value) => {
                if (value > 0) {
                    this.syncToModal();
                }
            });
        },
        
        updateRate() {
            if (this.exchangeRate <= 0) return;
            
            this.updating = true;
            
            // Simular actualización
            setTimeout(() => {
                window.currentExchangeRate = this.exchangeRate;
                localStorage.setItem('exchangeRate', this.exchangeRate);
                window.customersIndex.updateBsValues(this.exchangeRate);
                
                // Sincronizar con el modal
                this.syncToModal();
                
                this.updating = false;
                
                // Mostrar notificación
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tipo de cambio actualizado',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            }, 500);
        },
        
        // Sincronizar con el modal
        syncToModal() {
            const modalInput = document.getElementById('modalExchangeRate');
            if (modalInput) {
                modalInput.value = this.exchangeRate;
            }
            
            // También actualizar valores en Bs en el modal si está abierto
            if (typeof window.modalManager !== 'undefined' && window.modalManager().debtReportModal) {
                if (typeof window.customersIndex !== 'undefined' && window.customersIndex.updateBsValues) {
                    window.customersIndex.updateBsValues(this.exchangeRate);
                }
            }
        },
        
        // Sincronizar desde el modal
        syncFromModal(rate) {
            this.exchangeRate = rate;
        }
    }
}

// Función para la tabla de datos
function dataTable() {
    return {
        viewMode: window.innerWidth >= 768 ? 'table' : 'cards',
        searchTerm: '',
        searchResultsCount: 0,
        
        init() {
            // Detectar el modo de vista inicial basado en el tamaño de pantalla
            this.updateViewMode();
            
            // Escuchar cambios de tamaño de ventana
            window.addEventListener('resize', () => {
                this.updateViewMode();
            });
            
            // Actualizar contador de resultados
            this.updateSearchResultsCount();
        },
        
        updateViewMode() {
            this.viewMode = window.innerWidth >= 768 ? 'table' : 'cards';
        },
        
        setViewMode(mode) {
            this.viewMode = mode;
        },
        
        performSearch() {
            // Aplicar búsqueda usando la función global
            if (typeof applyFiltersAndSearch === 'function') {
                applyFiltersAndSearch();
            }
            this.updateSearchResultsCount();
        },
        
        clearSearch() {
            this.searchTerm = '';
            this.searchResultsCount = 0;
            
            // Limpiar búsqueda usando la función global
            if (typeof clearSearch === 'function') {
                clearSearch();
            }
        },
        
        updateSearchResultsCount() {
            // Contar elementos visibles en la tabla
            const visibleRows = document.querySelectorAll('#customersTable tbody tr:not([style*="display: none"])');
            this.searchResultsCount = visibleRows.length;
        },
        
        applyFilters(currentFilter, searchTerm) {
            // Esta función se puede usar para filtrar la tabla si es necesario
            this.updateSearchResultsCount();
        }
    }
}

// ===== FUNCIONES DE INICIALIZACIÓN =====

// Función para inicializar el tipo de cambio
window.initializeExchangeRate = function() {
    const savedRate = localStorage.getItem('exchangeRate');
    const rate = savedRate ? parseFloat(savedRate) : (window.exchangeRate || CONFIG.exchangeRate.default);
    
    // Actualizar el input si existe
    const exchangeRateInput = document.getElementById('exchangeRate');
    if (exchangeRateInput) {
        exchangeRateInput.value = rate;
    }
    
    // Actualizar valores en Bs
    window.customersIndex.updateBsValues(rate);
    
    return rate;
}

// Función para guardar el tipo de cambio
window.saveExchangeRate = function(rate) {
    window.currentExchangeRate = rate;
    localStorage.setItem(CONFIG.exchangeRate.key, rate);
    window.customersIndex.updateBsValues(rate);
}

// Función para sincronizar todos los elementos de tipo de cambio
window.syncAllExchangeRateElements = function() {
    const rate = window.currentExchangeRate || window.initializeExchangeRate();
    
    // Actualizar todos los inputs de tipo de cambio
    const exchangeRateInputs = document.querySelectorAll('input[name="exchange_rate"], #exchangeRate');
    exchangeRateInputs.forEach(input => {
        input.value = rate;
    });
    
    // Actualizar valores en Bs
    window.customersIndex.updateBsValues(rate);
}

// ===== FUNCIONES DE UTILIDAD =====

// Función para mostrar notificaciones
function showNotification(message, type = 'success') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        alert(message);
    }
}

// Función para formatear moneda
function formatCurrency(amount, currency = 'USD') {
    if (currency === 'USD') {
        return '$ ' + parseFloat(amount).toFixed(2);
    } else if (currency === 'BS') {
        return 'Bs. ' + parseFloat(amount).toLocaleString('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    return parseFloat(amount).toFixed(2);
}

// Función para formatear fecha
function formatDate(date) {
    return new Date(date).toLocaleDateString('es-ES');
}

// Función para formatear fecha y hora
function formatDateTime(date) {
    return new Date(date).toLocaleString('es-ES');
}

// ===== INICIALIZACIÓN OPTIMIZADA =====
document.addEventListener('DOMContentLoaded', function() {
    window.initializeExchangeRate();
    
    // Inicialización no crítica diferida
    setTimeout(() => {
        setupExchangeRateEvents();
        setupOptimizedEvents();
    }, 100);
});

// ===== FUNCIONES DE CONFIGURACIÓN DE EVENTOS =====

function setupExchangeRateEvents() {
    document.addEventListener('click', function(e) {
        if (e.target.matches('.update-exchange-rate')) {
            const input = document.getElementById('exchangeRate');
            if (input) {
                const rate = parseFloat(input.value);
                if (rate > 0) {
                    window.currentExchangeRate = rate;
                    localStorage.setItem(CONFIG.exchangeRate.key, rate);
                    window.customersIndex.updateBsValues(rate);
                    
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Tipo de cambio actualizado',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                }
            }
        }
    });
}

function setupOptimizedEvents() {
    // Event delegation optimizado
    document.addEventListener('click', function(e) {
        // Manejar botones de eliminación
        if (e.target.closest('[onclick*="deleteCustomer"]')) {
            e.preventDefault();
            const customerId = e.target.closest('[onclick*="deleteCustomer"]')
                .getAttribute('onclick')
                .match(/\d+/)?.[0];
            if (customerId) {
                window.customersIndex.deleteCustomer(customerId);
            }
        }
    });
}

// Utilidades simplificadas
function showNotification(message, type = 'success') {
    if (window.Swal) {
        Swal.fire({
            icon: type,
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
        });
    }
}

// ===== FUNCIONES GLOBALES ESENCIALES =====
window.heroSection = heroSection;
window.filtersPanel = filtersPanel;
window.exchangeRateWidget = exchangeRateWidget;
window.modalExchangeRateSync = modalExchangeRateSync;
window.dataTable = dataTable;
window.initializeExchangeRate = initializeExchangeRate;
window.showNotification = showNotification;
