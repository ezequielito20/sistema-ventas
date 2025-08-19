// ===== CONFIGURACIÓN GLOBAL =====
const CUSTOMERS_CONFIG = {
    routes: {
        delete: '/customers/delete',
        debtReport: '/admin/customers/debt-report',
        paymentHistory: '/admin/customers/payment-history',
        export: '/customers/export'
    },
    exchangeRate: {
        default: 134.0,
        localStorageKey: 'exchangeRate'
    }
};

// ===== FUNCIONES GLOBALES =====
window.customersIndex = {
    // Función para actualizar valores en Bs
    updateBsValues: function(rate) {
        // Actualizar elementos con clase bs-debt
        const bsDebtElements = document.querySelectorAll('.bs-debt');
        bsDebtElements.forEach(function(element) {
            const debtUsd = parseFloat(element.dataset.debt);
            if (!isNaN(debtUsd)) {
                const debtBs = debtUsd * rate;
                element.innerHTML = 'Bs. ' + debtBs.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        });
        
        // Actualizar elementos con clase debt-bs-info (para la tabla)
        const debtBsInfoElements = document.querySelectorAll('.debt-bs-info .bs-debt');
        debtBsInfoElements.forEach(function(element) {
            const debtUsd = parseFloat(element.dataset.debt);
            if (!isNaN(debtUsd)) {
                const debtBs = debtUsd * rate;
                element.innerHTML = 'Bs. ' + debtBs.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        });
    },

    // Función para eliminar cliente
    deleteCustomer: function(customerId) {
        console.log('Función deleteCustomer llamada para ID:', customerId);
        
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
                console.log('Confirmación aceptada, enviando petición fetch...');
                
                // Obtener el token CSRF
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch(`${CUSTOMERS_CONFIG.routes.delete}/${customerId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Respuesta recibida:', data);
                    
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
            console.log('Hero section inicializado');
        }
    }
}

// Función para el panel de filtros
function filtersPanel() {
    return {
        filtersOpen: false,
        currentFilter: 'all',
        searchTerm: '',
        searchResultsCount: 0,
        hasActiveFilters: false,
        
        init() {
            // Cargar filtros guardados
            const savedFilter = localStorage.getItem('customerFilter');
            if (savedFilter) {
                this.currentFilter = savedFilter;
            }
            
            // Actualizar contador de resultados
            this.updateSearchResultsCount();
        },
        
        toggleFilters() {
            this.filtersOpen = !this.filtersOpen;
        },
        
        setFilter(filter) {
            this.currentFilter = filter;
            localStorage.setItem('customerFilter', filter);
            this.applyFilters();
        },
        
        performSearch() {
            this.applyFilters();
        },
        
        clearSearch() {
            this.searchTerm = '';
            this.applyFilters();
        },
        
        applyFilters() {
            // Filtrar tarjetas móviles
            const customerCards = document.querySelectorAll('.customer-card');
            let visibleCount = 0;
            
            customerCards.forEach(function(card) {
                const cardStatus = card.dataset.status;
                const dataDefaulter = card.dataset.defaulter;
                const isDefaulter = dataDefaulter === 'true';
                let shouldShow = false;
                
                // Aplicar filtro de estado
                if (this.currentFilter === 'all') {
                    shouldShow = true;
                } else if (this.currentFilter === 'active' && cardStatus === 'active') {
                    shouldShow = true;
                } else if (this.currentFilter === 'inactive' && cardStatus === 'inactive') {
                    shouldShow = true;
                } else if (this.currentFilter === 'defaulters' && isDefaulter) {
                    shouldShow = true;
                }
                
                // Aplicar búsqueda si hay término de búsqueda
                if (shouldShow && this.searchTerm) {
                    const customerName = card.querySelector('.customer-name')?.textContent?.toLowerCase() || '';
                    const customerEmail = card.querySelector('.customer-email')?.textContent?.toLowerCase() || '';
                    const customerPhone = card.querySelector('.info-value')?.textContent?.toLowerCase() || '';
                    
                    shouldShow = customerName.includes(this.searchTerm.toLowerCase()) || 
                               customerEmail.includes(this.searchTerm.toLowerCase()) || 
                               customerPhone.includes(this.searchTerm.toLowerCase());
                }
                
                // Mostrar/ocultar tarjeta
                card.style.display = shouldShow ? '' : 'none';
                if (shouldShow) visibleCount++;
            }.bind(this));
            
            this.searchResultsCount = visibleCount;
            this.hasActiveFilters = this.currentFilter !== 'all' || this.searchTerm.length > 0;
        },
        
        updateSearchResultsCount() {
            const visibleCards = document.querySelectorAll('.customer-card[style*="display: none"]');
            this.searchResultsCount = document.querySelectorAll('.customer-card').length - visibleCards.length;
        },
        
        clearAllFilters() {
            this.currentFilter = 'all';
            this.searchTerm = '';
            this.filtersOpen = false;
            localStorage.removeItem('customerFilter');
            this.applyFilters();
        }
    }
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
        },
        
        updateRate() {
            if (this.exchangeRate <= 0) return;
            
            this.updating = true;
            
            // Simular actualización
            setTimeout(() => {
                window.currentExchangeRate = this.exchangeRate;
                localStorage.setItem('exchangeRate', this.exchangeRate);
                window.customersIndex.updateBsValues(this.exchangeRate);
                
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
            console.log('Aplicando filtros:', currentFilter, searchTerm);
            this.updateSearchResultsCount();
        }
    }
}

// ===== FUNCIONES DE INICIALIZACIÓN =====

// Función para inicializar el tipo de cambio
function initializeExchangeRate() {
    const savedRate = localStorage.getItem('exchangeRate');
    const rate = savedRate ? parseFloat(savedRate) : (window.exchangeRate || CUSTOMERS_CONFIG.exchangeRate.default);
    
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
function saveExchangeRate(rate) {
    window.currentExchangeRate = rate;
    localStorage.setItem(CUSTOMERS_CONFIG.exchangeRate.localStorageKey, rate);
    window.customersIndex.updateBsValues(rate);
}

// Función para sincronizar todos los elementos de tipo de cambio
function syncAllExchangeRateElements() {
    const rate = window.currentExchangeRate || initializeExchangeRate();
    
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

// ===== INICIALIZACIÓN CUANDO EL DOM ESTÉ LISTO =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ customers/index.js cargado correctamente');
    
    // Inicializar el tipo de cambio
    initializeExchangeRate();
    
    // Configurar eventos para el tipo de cambio
    setupExchangeRateEvents();
    
    // Configurar eventos de búsqueda
    setupSearchEvents();
    
    // Configurar eventos de filtros
    setupFilterEvents();
    
    // Inicializar contadores animados
    initializeCounters();
});

// ===== FUNCIONES DE CONFIGURACIÓN DE EVENTOS =====

function setupExchangeRateEvents() {
    // Actualizar valores en Bs cuando se cambia el tipo de cambio
    document.addEventListener('click', function(e) {
        if (e.target.matches('.update-exchange-rate')) {
            const exchangeRateInput = document.getElementById('exchangeRate');
            if (exchangeRateInput) {
                const rate = parseFloat(exchangeRateInput.value);
                if (rate > 0) {
                    // Usar la función centralizada para guardar
                    if (typeof saveExchangeRate === 'function') {
                        saveExchangeRate(rate);
                    } else {
                        window.currentExchangeRate = rate;
                        localStorage.setItem(CUSTOMERS_CONFIG.exchangeRate.localStorageKey, rate);
                    }
                    
                    window.customersIndex.updateBsValues(rate);
                    
                    // Mostrar mensaje de éxito
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
                }
            }
        }
    });
}

function setupSearchEvents() {
    // Conectar el campo de búsqueda (vista desktop)
    const mobileSearch = document.getElementById('mobileSearch');
    if (mobileSearch) {
        mobileSearch.addEventListener('keyup', function() {
            applyFiltersAndSearch();
        });

        // Mostrar/ocultar botón de limpiar búsqueda
        mobileSearch.addEventListener('input', function() {
            const hasValue = this.value.length > 0;
            const clearSearch = document.getElementById('clearSearch');
            if (clearSearch) {
                clearSearch.style.display = hasValue ? 'block' : 'none';
            }
        });
    }
}

function setupFilterEvents() {
    // Configurar eventos para filtros
    const filterInputs = document.querySelectorAll('.filter-input');
    filterInputs.forEach(input => {
        input.addEventListener('change', applyFiltersAndSearch);
    });
    
    // Configurar botón de limpiar filtros
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearAllFilters);
    }
}

function initializeCounters() {
    // Animar contadores cuando sean visibles
    const counters = document.querySelectorAll('.counter-animation');
    if (counters.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        });
        
        counters.forEach(counter => observer.observe(counter));
    }
}

function animateCounter(element) {
    const target = parseInt(element.dataset.target) || 0;
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;
    
    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}

// Función para aplicar filtros y búsqueda
function applyFiltersAndSearch() {
    // Obtener el término de búsqueda
    const mobileSearch = document.getElementById('mobileSearch');
    const searchTerm = mobileSearch ? mobileSearch.value : '';
    
    // Obtener el filtro actual
    let currentFilter = 'all';
    
    // Verificar si hay un botón activo
    const activeButton = document.querySelector('.filter-btn.active');
    if (activeButton) {
        currentFilter = activeButton.dataset.filter || 'all';
    }
    
    // Si Alpine.js está disponible, usar su estado
    if (window.Alpine && window.Alpine.store && window.Alpine.store('filters')) {
        currentFilter = window.Alpine.store('filters').currentFilter || currentFilter;
    }
    
    // Filtrar tarjetas móviles
    const customerCards = document.querySelectorAll('.customer-card');
    customerCards.forEach(function(card) {
        const cardStatus = card.dataset.status;
        const dataDefaulter = card.dataset.defaulter;
        const isDefaulter = dataDefaulter === 'true';
        let shouldShow = false;
        
        // Aplicar filtro de estado
        if (currentFilter === 'all') {
            shouldShow = true;
        } else if (currentFilter === 'active' && cardStatus === 'active') {
            shouldShow = true;
        } else if (currentFilter === 'inactive' && cardStatus === 'inactive') {
            shouldShow = true;
        } else if (currentFilter === 'defaulters' && isDefaulter) {
            shouldShow = true;
        }
        
        // Aplicar búsqueda si hay término de búsqueda
        if (shouldShow && searchTerm) {
            const customerName = card.querySelector('.customer-name')?.textContent?.toLowerCase() || '';
            const customerEmail = card.querySelector('.customer-email')?.textContent?.toLowerCase() || '';
            const customerPhone = card.querySelector('.info-value')?.textContent?.toLowerCase() || '';
            
            shouldShow = customerName.includes(searchTerm.toLowerCase()) || 
                       customerEmail.includes(searchTerm.toLowerCase()) || 
                       customerPhone.includes(searchTerm.toLowerCase());
        }
        
        // Mostrar/ocultar tarjeta
        card.style.display = shouldShow ? '' : 'none';
    });
    
    // Filtrar tabla (vista desktop) - usar Alpine.js si está disponible
    if (window.Alpine && window.Alpine.store && window.Alpine.store('dataTable')) {
        const dataTableStore = window.Alpine.store('dataTable');
        if (dataTableStore && typeof dataTableStore.applyFilters === 'function') {
            dataTableStore.applyFilters(currentFilter, searchTerm);
        }
    }
}

// Limpiar búsqueda
function clearSearch() {
    const mobileSearch = document.getElementById('mobileSearch');
    if (mobileSearch) {
        mobileSearch.value = '';
        applyFiltersAndSearch();
    }
}

// Limpiar todos los filtros
function clearAllFilters() {
    // Limpiar búsqueda
    const mobileSearch = document.getElementById('mobileSearch');
    if (mobileSearch) {
        mobileSearch.value = '';
    }
    
    // Limpiar filtros de estado
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => btn.classList.remove('active'));
    
    // Activar filtro "todos"
    const allFilterBtn = document.querySelector('.filter-btn[data-filter="all"]');
    if (allFilterBtn) {
        allFilterBtn.classList.add('active');
    }
    
    // Aplicar filtros
    applyFiltersAndSearch();
}

// ===== EXPONER FUNCIONES GLOBALMENTE =====
window.initializeExchangeRate = initializeExchangeRate;
window.saveExchangeRate = saveExchangeRate;
window.syncAllExchangeRateElements = syncAllExchangeRateElements;
window.heroSection = heroSection;
window.filtersPanel = filtersPanel;
window.exchangeRateWidget = exchangeRateWidget;
window.dataTable = dataTable;
window.showNotification = showNotification;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.formatDateTime = formatDateTime;
window.clearSearch = clearSearch;
window.clearAllFilters = clearAllFilters;
window.applyFiltersAndSearch = applyFiltersAndSearch;
