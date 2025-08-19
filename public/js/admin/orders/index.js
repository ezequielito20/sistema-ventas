// ===== CONFIGURACIÓN GLOBAL =====
if (typeof ORDERS_CONFIG === 'undefined') {
    window.ORDERS_CONFIG = {
        routes: {
            delete: '/orders/delete',
            report: '/admin/orders/report',
            export: '/orders/export'
        },
        filters: {
            localStorageKey: 'orderFilters'
        }
    };
}

// ===== FUNCIONES GLOBALES =====
if (typeof window.ordersIndex === 'undefined') {
    window.ordersIndex = {
        // Función para eliminar orden
        deleteOrder: function(orderId) {
            console.log('Función deleteOrder llamada para ID:', orderId);
            
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
                    
                    fetch(`${ORDERS_CONFIG.routes.delete}/${orderId}`, {
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
                            Swal.fire({
                                title: 'Error',
                                text: data.message,
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Ocurrió un error al eliminar la orden',
                            icon: 'error'
                        });
                    });
                }
            });
        },

        // Función para mostrar notificaciones
        showNotification: function(message, type = 'success') {
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
    };
}

// ===== FUNCIONES DE FILTROS =====

// Función para manejar filtros avanzados
function initializeFilters() {
    const filtersToggle = document.getElementById('filtersToggle');
    const filtersContent = document.getElementById('filtersContent');
    
    if (filtersToggle && filtersContent) {
        filtersToggle.addEventListener('click', function() {
            filtersContent.classList.toggle('show');
            const icon = this.querySelector('i');
            if (filtersContent.classList.contains('show')) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
    }
}

// Función para manejar botones de filtro
function initializeFilterButtons() {
    const filterButtons = document.querySelectorAll('.btn-filter');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover clase active de todos los botones
            filterButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.style.background = 'white';
                btn.style.color = getFilterButtonColor(btn.dataset.filter);
            });
            
            // Agregar clase active al botón clickeado
            this.classList.add('active');
            this.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            this.style.color = 'white';
            
            // Aplicar filtros
            applyFilters();
        });
        
        // Efectos hover para botones de filtro
        button.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.1)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            }
        });
    });
}

// Función para obtener el color del botón de filtro
function getFilterButtonColor(filter) {
    const colors = {
        'pending': '#f59e0b',
        'processing': '#0ea5e9',
        'completed': '#22c55e',
        'cancelled': '#ef4444'
    };
    return colors[filter] || '#64748b';
}

// Función para aplicar filtros
function applyFilters() {
    const activeFilter = document.querySelector('.btn-filter.active');
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const amountMin = document.getElementById('amountMin').value;
    const amountMax = document.getElementById('amountMax').value;
    
    // Actualizar badges de filtros activos
    updateActiveFiltersBadges(activeFilter, dateFrom, dateTo, amountMin, amountMax);
    
    // Aquí se aplicaría la lógica de filtrado real
    console.log('Aplicando filtros:', {
        filter: activeFilter?.dataset.filter,
        dateFrom,
        dateTo,
        amountMin,
        amountMax
    });
    
    // Guardar filtros en localStorage
    saveFilters({
        filter: activeFilter?.dataset.filter || 'all',
        dateFrom,
        dateTo,
        amountMin,
        amountMax
    });
}

// Función para actualizar badges de filtros activos
function updateActiveFiltersBadges(activeFilter, dateFrom, dateTo, amountMin, amountMax) {
    const activeFiltersContainer = document.getElementById('activeFilters');
    if (!activeFiltersContainer) return;
    
    let badges = [];
    
    // Badge del filtro de estado
    if (activeFilter && activeFilter.dataset.filter !== 'all') {
        const filterNames = {
            'pending': 'Pendientes',
            'processing': 'En Proceso',
            'completed': 'Completados',
            'cancelled': 'Cancelados'
        };
        badges.push(`<span class="filter-badge">${filterNames[activeFilter.dataset.filter]}</span>`);
    }
    
    // Badge de rango de fechas
    if (dateFrom || dateTo) {
        const dateText = dateFrom && dateTo ? `${dateFrom} - ${dateTo}` : 
                        dateFrom ? `Desde ${dateFrom}` : `Hasta ${dateTo}`;
        badges.push(`<span class="filter-badge">${dateText}</span>`);
    }
    
    // Badge de rango de monto
    if (amountMin || amountMax) {
        const amountText = amountMin && amountMax ? `$${amountMin} - $${amountMax}` :
                          amountMin ? `Mínimo $${amountMin}` : `Máximo $${amountMax}`;
        badges.push(`<span class="filter-badge">${amountText}</span>`);
    }
    
    // Si no hay filtros activos, mostrar "Todos los pedidos"
    if (badges.length === 0) {
        badges.push('<span class="filter-badge">Todos los pedidos</span>');
    }
    
    activeFiltersContainer.innerHTML = badges.join('');
}

// Función para guardar filtros en localStorage
function saveFilters(filters) {
    localStorage.setItem(ORDERS_CONFIG.filters.localStorageKey, JSON.stringify(filters));
}

// Función para cargar filtros guardados
function loadSavedFilters() {
    const savedFilters = localStorage.getItem(ORDERS_CONFIG.filters.localStorageKey);
    if (savedFilters) {
        const filters = JSON.parse(savedFilters);
        
        // Aplicar filtros guardados
        if (filters.filter && filters.filter !== 'all') {
            const filterButton = document.querySelector(`[data-filter="${filters.filter}"]`);
            if (filterButton) {
                filterButton.click();
            }
        }
        
        if (filters.dateFrom) {
            document.getElementById('dateFrom').value = filters.dateFrom;
        }
        
        if (filters.dateTo) {
            document.getElementById('dateTo').value = filters.dateTo;
        }
        
        if (filters.amountMin) {
            document.getElementById('amountMin').value = filters.amountMin;
        }
        
        if (filters.amountMax) {
            document.getElementById('amountMax').value = filters.amountMax;
        }
    }
}

// Función para limpiar filtros
function clearFilters() {
    // Limpiar inputs
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('amountMin').value = '';
    document.getElementById('amountMax').value = '';
    
    // Activar filtro "todos"
    const allFilterButton = document.querySelector('[data-filter="all"]');
    if (allFilterButton) {
        allFilterButton.click();
    }
    
    // Limpiar localStorage
    localStorage.removeItem(ORDERS_CONFIG.filters.localStorageKey);
    
    // Actualizar badges
    updateActiveFiltersBadges(null, '', '', '', '');
}

// ===== FUNCIONES DE VISTA =====

// Función para manejar toggle de vista
function initializeViewToggles() {
    const viewToggles = document.querySelectorAll('.view-toggle');
    viewToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            // Remover clase active de todos los toggles
            viewToggles.forEach(t => {
                t.classList.remove('active');
                t.style.background = 'white';
                t.style.color = '#64748b';
                t.style.border = '2px solid #e2e8f0';
            });
            
            // Agregar clase active al toggle clickeado
            this.classList.add('active');
            this.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            this.style.color = 'white';
            this.style.border = 'none';
            
            // Cambiar vista
            const viewMode = this.dataset.view;
            changeViewMode(viewMode);
        });
        
        // Efectos hover para toggles
        toggle.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.1)';
            }
        });
        
        toggle.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            }
        });
    });
}

// Función para cambiar modo de vista
function changeViewMode(viewMode) {
    console.log('Cambiando a vista:', viewMode);
    // Aquí se implementaría la lógica para cambiar entre tabla y tarjetas
}

// ===== FUNCIONES DE EFECTOS VISUALES =====

// Función para inicializar efectos en tarjetas de estadísticas
function initializeStatsCards() {
    const statCards = document.querySelectorAll('.stats-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
        });
    });
}

// Función para inicializar efectos en inputs
function initializeInputEffects() {
    const inputs = document.querySelectorAll('input[type="date"], input[type="number"], input[type="text"]');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = '#667eea';
            this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';
        });
        
        input.addEventListener('blur', function() {
            this.style.borderColor = '#e2e8f0';
            this.style.boxShadow = 'none';
        });
    });
}

// Función para inicializar efectos en botones de acción
function initializeActionButtons() {
    const actionButtons = document.querySelectorAll('.btn-apply, .btn-clear');
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.15)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
}

// ===== FUNCIONES DE BÚSQUEDA =====

// Función para inicializar búsqueda
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        // Debounce para la búsqueda
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });
    }
}

// Función para realizar búsqueda
function performSearch(searchTerm) {
    console.log('Realizando búsqueda:', searchTerm);
    // Aquí se implementaría la lógica de búsqueda real
}

// ===== FUNCIONES DE NOTIFICACIONES =====

// Función para mostrar notificación
function showNotification(message, type = 'success') {
    const event = new CustomEvent('showNotification', {
        detail: {
            type: type,
            message: message
        }
    });
    window.dispatchEvent(event);
}

// ===== INICIALIZACIÓN =====

// Función principal de inicialización
function initializeOrdersIndex() {
    console.log('✅ orders/index.js cargado correctamente');
    
    // Inicializar filtros
    initializeFilters();
    initializeFilterButtons();
    
    // Inicializar vista
    initializeViewToggles();
    
    // Inicializar efectos visuales
    initializeStatsCards();
    initializeInputEffects();
    initializeActionButtons();
    
    // Inicializar búsqueda
    initializeSearch();
    
    // Cargar filtros guardados
    loadSavedFilters();
    
    // Configurar eventos de botones
    setupButtonEvents();
}

// Función para configurar eventos de botones
function setupButtonEvents() {
    // Botón aplicar filtros
    const applyFiltersBtn = document.getElementById('applyFilters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', applyFilters);
    }
    
    // Botón limpiar filtros
    const clearFiltersBtn = document.getElementById('clearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearFilters);
    }
}

// ===== EXPONER FUNCIONES GLOBALMENTE =====
if (typeof window.initializeOrdersIndex === 'undefined') {
    window.initializeOrdersIndex = initializeOrdersIndex;
}
if (typeof window.applyFilters === 'undefined') {
    window.applyFilters = applyFilters;
}
if (typeof window.clearFilters === 'undefined') {
    window.clearFilters = clearFilters;
}
if (typeof window.performSearch === 'undefined') {
    window.performSearch = performSearch;
}
if (typeof window.showNotification === 'undefined') {
    window.showNotification = showNotification;
}

// ===== INICIALIZAR CUANDO EL DOM ESTÉ LISTO =====
if (!window.ordersInitialized) {
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.ordersInitialized) {
            initializeOrdersIndex();
            window.ordersInitialized = true;
        }
    });
}
