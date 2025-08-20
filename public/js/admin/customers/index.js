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
        isSearching: false,
        
        init() {
            // Detectar el modo de vista inicial basado en el tamaño de pantalla
            this.updateViewMode();
            
            // Escuchar cambios de tamaño de ventana
            window.addEventListener('resize', () => {
                this.updateViewMode();
            });
            
            // Watcher para búsqueda en tiempo real
            this.$watch('searchTerm', () => {
                this.performSearch();
            });
            
            // Watcher para cambio de modo de vista
            this.$watch('viewMode', () => {
                this.updateSearchResultsCount();
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
            // Búsqueda en tiempo real con debounce
            this.isSearching = true;
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.executeSearch();
                this.isSearching = false;
            }, 300); // 300ms de debounce
        },
        
        executeSearch() {
            const searchTerm = this.searchTerm.toLowerCase().trim();
            
            // Buscar en modo tabla
            this.searchInTable(searchTerm);
            
            // Buscar en modo tarjetas
            this.searchInCards(searchTerm);
            
            // Actualizar contador de resultados
            this.updateSearchResultsCount();
            
            // Mostrar mensaje si no hay resultados
            const totalVisible = this.getVisibleCount();
            this.showNoResultsMessage(totalVisible === 0 && searchTerm !== '');
        },
        
        // Buscar en modo tabla
        searchInTable(searchTerm) {
            const table = document.getElementById('customersTable');
            if (!table) return;
            
            const rows = table.querySelectorAll('tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const matches = text.includes(searchTerm);
                
                if (searchTerm === '' || matches) {
                    row.style.display = '';
                    visibleCount++;
                    
                    if (searchTerm !== '') {
                        this.highlightSearchTerms(row, searchTerm);
                    } else {
                        this.removeHighlights(row);
                    }
                } else {
                    row.style.display = 'none';
                }
            });
        },
        
        // Buscar en modo tarjetas
        searchInCards(searchTerm) {
            const cardsContainer = document.getElementById('mobileCustomersContainer');
            if (!cardsContainer) return;
            
            const cards = cardsContainer.querySelectorAll('.bg-white.rounded-2xl');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                const matches = text.includes(searchTerm);
                
                if (searchTerm === '' || matches) {
                    card.style.display = '';
                    
                    if (searchTerm !== '') {
                        this.highlightSearchTermsInCard(card, searchTerm);
                    } else {
                        this.removeHighlightsFromCard(card);
                    }
                } else {
                    card.style.display = 'none';
                }
            });
        },
        
        // Resaltar términos de búsqueda
        highlightSearchTerms(row, searchTerm) {
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                // Solo resaltar en elementos de texto específicos, nunca en botones
                this.highlightSafeElements(cell, searchTerm);
            });
        },
        
        // Resaltar elementos de forma segura sin tocar botones
        highlightSafeElements(cell, searchTerm) {
            // Lista de elementos seguros para resaltar
            const safeSelectors = [
                '.customer-name',
                '.customer-email', 
                '.id-badge',
                '.sales-amount',
                '.sales-count',
                '.debt-amount-value',
                '.status-badge',
                '.no-sales',
                '.no-debt-badge'
            ];
            
            safeSelectors.forEach(selector => {
                const elements = cell.querySelectorAll(selector);
                elements.forEach(element => {
                    if (element.children.length === 0) { // Solo elementos sin hijos
                        this.highlightElement(element, searchTerm);
                    }
                });
            });
        },
        
        // Resaltar un elemento específico
        highlightElement(element, searchTerm) {
            const originalText = element.getAttribute('data-original-text') || element.textContent;
            if (!element.hasAttribute('data-original-text')) {
                element.setAttribute('data-original-text', originalText);
            }
            
            // Escapar caracteres especiales en el término de búsqueda
            const escapedSearchTerm = searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`(${escapedSearchTerm})`, 'gi');
            const highlightedText = originalText.replace(regex, '<mark class="bg-yellow-200 text-yellow-800 px-1 rounded">$1</mark>');
            element.innerHTML = highlightedText;
        },
        
        // Resaltar términos de búsqueda en tarjetas
        highlightSearchTermsInCard(card, searchTerm) {
            // Lista de elementos seguros para resaltar en tarjetas
            const safeSelectors = [
                'h3', // nombre del cliente
                'span', // email, teléfono, etc.
                'p' // otros textos
            ];
            
            safeSelectors.forEach(selector => {
                const elements = card.querySelectorAll(selector);
                elements.forEach(element => {
                    // Solo resaltar elementos que no contengan botones y no sean badges de estado
                    const hasButtons = element.querySelector('button, a');
                    const isStatusBadge = element.closest('.inline-flex');
                    
                    if (!hasButtons && !isStatusBadge && element.children.length === 0) {
                        this.highlightElement(element, searchTerm);
                    }
                });
            });
        },
        
        // Remover resaltados de tarjetas
        removeHighlightsFromCard(card) {
            const highlightedElements = card.querySelectorAll('mark');
            highlightedElements.forEach(mark => {
                const parent = mark.parentElement;
                const originalText = parent.getAttribute('data-original-text');
                if (originalText) {
                    parent.innerHTML = originalText;
                }
            });
        },
        

        
        // Remover resaltados
        removeHighlights(row) {
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                // Remover resaltados de elementos específicos
                const highlightedElements = cell.querySelectorAll('mark');
                highlightedElements.forEach(mark => {
                    const parent = mark.parentElement;
                    const originalText = parent.getAttribute('data-original-text');
                    if (originalText) {
                        parent.innerHTML = originalText;
                    }
                });
            });
        },
        
        // Manejar teclas especiales
        handleKeydown(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.executeSearch();
            } else if (event.key === 'Escape') {
                this.clearSearch();
            }
        },
        
        // Mostrar mensaje de no resultados
        showNoResultsMessage(show) {
            // Mostrar mensaje en la tabla
            this.showNoResultsInTable(show);
            
            // Mostrar mensaje en las tarjetas
            this.showNoResultsInCards(show);
        },
        
        // Mostrar mensaje de no resultados en tabla
        showNoResultsInTable(show) {
            const table = document.getElementById('customersTable');
            if (!table) return;
            
            let noResultsRow = table.querySelector('.no-results-row');
            
            if (show) {
                if (!noResultsRow) {
                    const tbody = table.querySelector('tbody');
                    noResultsRow = document.createElement('tr');
                    noResultsRow.className = 'no-results-row';
                    noResultsRow.innerHTML = `
                        <td colspan="100%" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-search text-4xl mb-4"></i>
                                <p class="text-lg font-medium">No se encontraron resultados</p>
                                <p class="text-sm">Intenta con otros términos de búsqueda</p>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(noResultsRow);
                }
                noResultsRow.style.display = '';
            } else if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        },
        
        // Mostrar mensaje de no resultados en tarjetas
        showNoResultsInCards(show) {
            const cardsContainer = document.getElementById('mobileCustomersContainer');
            if (!cardsContainer) return;
            
            let noResultsCard = cardsContainer.querySelector('.no-results-card');
            
            if (show) {
                if (!noResultsCard) {
                    noResultsCard = document.createElement('div');
                    noResultsCard.className = 'no-results-card col-span-full';
                    noResultsCard.innerHTML = `
                        <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-search text-4xl mb-4"></i>
                                <p class="text-lg font-medium">No se encontraron resultados</p>
                                <p class="text-sm">Intenta con otros términos de búsqueda</p>
                            </div>
                        </div>
                    `;
                    cardsContainer.appendChild(noResultsCard);
                }
                noResultsCard.style.display = '';
            } else if (noResultsCard) {
                noResultsCard.style.display = 'none';
            }
        },
        
        clearSearch() {
            this.searchTerm = '';
            this.searchResultsCount = 0;
            
            // Restaurar todas las filas de la tabla a su estado original
            const table = document.getElementById('customersTable');
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    row.style.display = '';
                    this.removeHighlights(row);
                });
            }
            
            // Restaurar todas las tarjetas a su estado original
            const cardsContainer = document.getElementById('mobileCustomersContainer');
            if (cardsContainer) {
                const cards = cardsContainer.querySelectorAll('.bg-white.rounded-2xl');
                cards.forEach(card => {
                    card.style.display = '';
                    this.removeHighlightsFromCard(card);
                });
            }
            
            this.updateSearchResultsCount();
            this.showNoResultsMessage(false); // Ocultar mensaje de no resultados
        },
        
        updateSearchResultsCount() {
            this.searchResultsCount = this.getVisibleCount();
        },
        
        // Obtener conteo total de elementos visibles
        getVisibleCount() {
            let totalVisible = 0;
            
            // Contar elementos visibles según el modo de vista activo
            if (this.viewMode === 'table') {
                const table = document.getElementById('customersTable');
                if (table) {
                    const visibleRows = table.querySelectorAll('tbody tr:not([style*="display: none"])');
                    totalVisible = visibleRows.length;
                }
            } else if (this.viewMode === 'cards') {
                const cardsContainer = document.getElementById('mobileCustomersContainer');
                if (cardsContainer) {
                    const visibleCards = cardsContainer.querySelectorAll('.bg-white.rounded-2xl:not([style*="display: none"])');
                    totalVisible = visibleCards.length;
                }
            }
            
            return totalVisible;
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
