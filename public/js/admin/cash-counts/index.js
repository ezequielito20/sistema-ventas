/**
 * JavaScript optimizado para cash-counts/index
 * Archivo: public/js/admin/cash-counts/index.js
 * Versión: 2.0.0
 * Descripción: Funciones específicas para la gestión de arqueos de caja
 */

// Verificar si ya se ha cargado para evitar redeclaraciones
if (typeof window.cashCountsIndexLoaded !== 'undefined') {
    console.warn('cash-counts/index.js ya ha sido cargado anteriormente');
} else {
    window.cashCountsIndexLoaded = true;
}



// ===== VARIABLES GLOBALES =====
let cashCountModalInstance = null;
let charts = {};

// Verificar si ya existe para evitar redeclaración
if (typeof window.cashCountModalInstance === 'undefined') {
    window.cashCountModalInstance = null;
}
if (typeof window.cashCountsCharts === 'undefined') {
    window.cashCountsCharts = {};
}

// ===== CONFIGURACIÓN GLOBAL =====
const CASH_COUNTS_CONFIG = {
    currencySymbol: window.cashCountsData?.currencySymbol || '$',
    chartColors: {
        primary: '#4facfe',
        secondary: '#fa709a',
        success: '#48bb78',
        warning: '#ed8936',
        danger: '#f56565'
    }
};

// Función para obtener el símbolo de moneda actual
function getCurrentCurrencySymbol() {
    return window.cashCountsData?.currencySymbol || '$';
}

// ===== FUNCIONES DE PAGINACIÓN INTELIGENTE =====

// Función para detectar si la paginación del servidor está activa
function isServerPaginationActive() {
    const paginator = document.querySelector('.pagination-container .page-numbers a');
    return !!paginator; // existen enlaces → servidor
}

// Cargar una URL y reemplazar secciones sin recargar
function loadCashCountsPage(url) {
    const container = document.querySelector('.space-y-6');
    if (!container) return;

    // Indicador simple de carga
    container.style.opacity = '0.6';

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html, application/xhtml+xml'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('Error al cargar');
        return r.text();
    })
    .then(html => {
        const temp = document.createElement('div');
        temp.innerHTML = html;

        // Reemplazar tabla
        const newTableBody = temp.querySelector('.modern-table tbody');
        const tableBody = document.querySelector('.modern-table tbody');
        if (newTableBody && tableBody) {
            tableBody.innerHTML = newTableBody.innerHTML;
        }

        // Reemplazar tarjetas
        const newCardsGrid = temp.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3');
        const cardsGrid = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3');
        if (newCardsGrid && cardsGrid) {
            cardsGrid.innerHTML = newCardsGrid.innerHTML;
        }

        // Reemplazar contenedores de paginación
        const newPaginationContainers = temp.querySelectorAll('.pagination-container');
        const paginationContainers = document.querySelectorAll('.pagination-container');
        if (newPaginationContainers.length > 0 && paginationContainers.length > 0) {
            newPaginationContainers.forEach((newContainer, index) => {
                if (paginationContainers[index]) {
                    paginationContainers[index].innerHTML = newContainer.innerHTML;
                }
            });
        }

        // Actualizar URL sin recargar
        window.history.pushState({}, '', url);

        // Reinicializar event listeners
        initializeEventListeners();
    })
    .catch(err => {
        console.error('Error al cargar página:', err);
        // Mostrar error al usuario
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: 'Error al cargar los resultados de búsqueda',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .finally(() => {
        container.style.opacity = '';
    });
}

// Interceptar clicks de paginación cuando servidor está activo
document.addEventListener('click', (e) => {
    const paginationLink = e.target.closest('.pagination-btn, .page-number');
    if (paginationLink && paginationLink.href && isServerPaginationActive()) {
        e.preventDefault();
        loadCashCountsPage(paginationLink.href);
    }
});

// ===== FUNCIONES DE DETECCIÓN Y CARGA DEL SERVIDOR =====

/**
 * Inicializar event listeners
 */
function initializeEventListeners() {
    // Reinicializar event listeners para elementos dinámicos
    // Esto se llama después de cargar contenido via AJAX
}

// Utilidad: detectar si la vista usa paginación del servidor
function isServerPaginationActive() {
    // Siempre activar la búsqueda del servidor para cash-counts
    // ya que el controlador está configurado para paginación del servidor
    return true;
}

// Cargar una URL y reemplazar secciones (tabla/tarjetas + paginación) sin recargar
function loadCashCountsPage(url) {
    // Mostrar indicador de carga en el campo de búsqueda
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.classList.add('search-loading');
        searchInput.disabled = true;
    }

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html, application/xhtml+xml'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('Error al cargar');
        return r.text();
    })
    .then(html => {
        const temp = document.createElement('div');
        temp.innerHTML = html;

        // Reemplazar tabla
        const newTableBody = temp.querySelector('.modern-table tbody');
        const tableBody = document.querySelector('.modern-table tbody');
        if (newTableBody && tableBody) {
            tableBody.innerHTML = newTableBody.innerHTML;
        }

        // Reemplazar tarjetas
        const newCardsGrid = temp.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3');
        const cardsGrid = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3');
        if (newCardsGrid && cardsGrid) {
            cardsGrid.innerHTML = newCardsGrid.innerHTML;
        }

        // Reemplazar información de paginación
        const newPaginationInfo = temp.querySelector('.text-sm.text-gray-700');
        const paginationInfo = document.querySelector('.text-sm.text-gray-700');
        if (newPaginationInfo && paginationInfo) {
            paginationInfo.innerHTML = newPaginationInfo.innerHTML;
        }

        // Reemplazar enlaces de paginación si existen
        const newPagination = temp.querySelector('.pagination');
        const pagination = document.querySelector('.pagination');
        if (newPagination && pagination) {
            pagination.innerHTML = newPagination.innerHTML;
        }

        // Actualizar URL sin recargar
        window.history.pushState({}, '', url);

        // Reinicializar event listeners para nuevos elementos
        initializeEventListeners();
    })
    .catch(err => {
        console.error('Error al cargar página:', err);
        // Mostrar error al usuario
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: 'Error al cargar los resultados de búsqueda',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .finally(() => {
        // Ocultar indicador de carga
        if (searchInput) {
            searchInput.classList.remove('search-loading');
            searchInput.disabled = false;
        }
    });
}

// Interceptar clicks de paginación cuando servidor está activo
document.addEventListener('click', (e) => {
    const link = e.target.closest('.pagination .page-link');
    if (link && isServerPaginationActive()) {
        e.preventDefault();
        loadCashCountsPage(link.href);
    }
});

// Interceptar búsqueda para servidor
function initializeSearchListener() {
    const search = document.getElementById('searchInput');
    if (search) {
        let t;
        search.addEventListener('input', function () {
            clearTimeout(t);
            t = setTimeout(() => {
                if (isServerPaginationActive()) {
                    const url = new URL(window.location.href);
                    if (this.value.trim()) url.searchParams.set('search', this.value.trim());
                    else url.searchParams.delete('search');
                    loadCashCountsPage(url.toString());
                }
            }, 300);
        });
    }
}

// Intentar inicializar inmediatamente y también después de que Alpine.js esté listo
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

// También intentar después de que Alpine.js esté listo
document.addEventListener('alpine:init', () => {
    setTimeout(initializeApp, 100);
});

// ===== FUNCIONES GLOBALES =====

/**
 * Cerrar caja con confirmación
 */
function closeCashCount(cashCountId) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Cerrar Caja?',
            text: '¿Estás seguro de que quieres cerrar la caja actual? Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cerrar caja',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                submitCloseCashCount(cashCountId);
            }
        });
    } else {
        if (confirm('¿Estás seguro de que quieres cerrar la caja actual?')) {
            submitCloseCashCount(cashCountId);
        }
    }
}

/**
 * Enviar formulario de cierre de caja
 */
function submitCloseCashCount(cashCountId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/cash-counts/close/${cashCountId}`;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'PUT';
    
    form.appendChild(csrfToken);
    form.appendChild(methodField);
    document.body.appendChild(form);
    form.submit();
}

/**
 * Eliminar arqueo de caja con confirmación
 */
function deleteCashCount(cashCountId) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar Arqueo?',
            text: '¿Estás seguro de que quieres eliminar este arqueo? Solo se pueden eliminar arqueos sin movimientos registrados.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                submitDeleteCashCount(cashCountId);
            }
        });
    } else {
        if (confirm('¿Estás seguro de que quieres eliminar este arqueo? Solo se pueden eliminar arqueos sin movimientos registrados.')) {
            submitDeleteCashCount(cashCountId);
        }
    }
}

/**
 * Enviar formulario de eliminación de arqueo
 */
async function submitDeleteCashCount(cashCountId) {
    try {
        const response = await fetch(`/cash-counts/delete/${cashCountId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.success) {
            // Mostrar notificación de éxito
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Eliminado!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    // Redirigir al index después de cerrar la alerta
                    window.location.href = '/cash-counts';
                });
            } else {
                alert(data.message);
                window.location.href = '/cash-counts';
            }
        } else {
            // Mostrar notificación de error
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else {
                alert('Error: ' + data.message);
            }
        }
    } catch (error) {
        // Mostrar notificación de error
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: 'Error al eliminar el arqueo. Inténtalo de nuevo.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert('Error al eliminar el arqueo. Inténtalo de nuevo.');
        }
    }
}

/**
 * Abrir modal de detalles del arqueo de caja
 */
window.openCashCountModal = function(cashCountId) {
    const modalInstance = window.cashCountModalInstance || cashCountModalInstance;
    if (modalInstance) {
        modalInstance.isOpen = true;
        modalInstance.cashCountData = null;
        
        // Asegurar que la configuración de moneda esté actualizada
        if (window.cashCountsData && window.cashCountsData.currencySymbol) {
            CASH_COUNTS_CONFIG.currencySymbol = window.cashCountsData.currencySymbol;
            modalInstance.currencySymbol = window.cashCountsData.currencySymbol;
        }
        
        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
        
        // Cargar datos del arqueo de forma asíncrona
        modalInstance.loadCashCountData(cashCountId);
    } else {
        console.error('Modal instance not found');
        showNotification('Error: Modal no disponible', 'error');
    }
};

/**
 * Función de prueba para el modal
 */
window.testModal = function() {
    const modalInstance = window.cashCountModalInstance || cashCountModalInstance;
    if (modalInstance) {
        modalInstance.isOpen = true;

        modalInstance.cashCountData = {
            id: 999,
            initial_amount: 1000.00,
            final_amount: null,
            opening_date: '2024-01-01T00:00:00.000000Z',
            closing_date: null,
            observations: 'Arqueo de prueba',
            total_income: 500.00,
            total_expenses: 200.00,
            current_balance: 1300.00,
            movements_count: 3,
            movements: [
                {
                    id: 1,
                    type: 'income',
                    amount: 300.00,
                    description: 'Venta de productos',
                    created_at: '2024-01-01T10:00:00.000000Z'
                },
                {
                    id: 2,
                    type: 'income',
                    amount: 200.00,
                    description: 'Pago de deuda',
                    created_at: '2024-01-01T11:00:00.000000Z'
                },
                {
                    id: 3,
                    type: 'expense',
                    amount: 200.00,
                    description: 'Compra de suministros',
                    created_at: '2024-01-01T12:00:00.000000Z'
                }
            ]
        };
        
        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
        
        showNotification('Modal de prueba cargado exitosamente', 'success');
    } else {
        showNotification('Error: Modal no disponible', 'error');
    }
};

// ===== FUNCIONES DE UTILIDAD =====

/**
 * Formatear moneda
 */
function formatCurrency(amount) {
    const currencySymbol = getCurrentCurrencySymbol();
    if (amount === null || amount === undefined || amount === '') {
        return currencySymbol + ' 0.00';
    }
    const num = parseFloat(amount);
    if (isNaN(num)) {
        return currencySymbol + ' 0.00';
    }
    return currencySymbol + ' ' + num.toFixed(2);
}

/**
 * Formatear fecha
 */
function formatDate(dateString) {
    if (!dateString || dateString === 'null' || dateString === 'undefined') {
        return 'N/A';
    }
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return 'N/A';
        }
        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
            } catch (error) {
            return 'N/A';
        }
}

/**
 * Formatear fecha y hora
 */
function formatDateTime(dateString) {
    if (!dateString || dateString === 'null' || dateString === 'undefined') {
        return 'N/A';
    }
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return 'N/A';
        }
        return date.toLocaleString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
            } catch (error) {
            return 'N/A';
        }
}

/**
 * Mostrar notificación
 */
function showNotification(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: type === 'error' ? 'Error' : 'Información',
            text: message,
            icon: type,
            confirmButtonText: 'OK',
            timer: type === 'error' ? null : 3000,
            timerProgressBar: type !== 'error'
        });
    } else {
        const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';
        alert(`${icon} ${message}`);
    }
}

// ===== INICIALIZACIÓN DE GRÁFICOS =====

/**
 * Inicializar gráficos cuando el DOM esté listo
 */
function initializeCharts() {
    if (typeof Chart === 'undefined') {
        return;
    }

    // Gráfico de Movimientos
    const movementsCtx = document.getElementById('cashMovementsChart');
    if (movementsCtx && window.cashCountsData?.chartData) {
        charts.movements = new Chart(movementsCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: window.cashCountsData.chartData.labels,
                datasets: [{
                    label: 'Ingresos',
                    data: window.cashCountsData.chartData.income,
                    borderColor: CASH_COUNTS_CONFIG.chartColors.primary,
                    backgroundColor: 'rgba(79, 172, 254, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: CASH_COUNTS_CONFIG.chartColors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }, {
                    label: 'Egresos',
                    data: window.cashCountsData.chartData.expenses,
                    borderColor: CASH_COUNTS_CONFIG.chartColors.secondary,
                    backgroundColor: 'rgba(250, 112, 154, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: CASH_COUNTS_CONFIG.chartColors.secondary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Distribución
    const distributionCtx = document.getElementById('movementsDistributionChart');
    if (distributionCtx && window.cashCountsData?.todayIncome !== undefined && window.cashCountsData?.todayExpenses !== undefined) {
        charts.distribution = new Chart(distributionCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Ingresos', 'Egresos'],
                datasets: [{
                    data: [window.cashCountsData.todayIncome, window.cashCountsData.todayExpenses],
                    backgroundColor: [CASH_COUNTS_CONFIG.chartColors.primary, CASH_COUNTS_CONFIG.chartColors.secondary],
                    borderWidth: 0,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }
}

// ===== FUNCIONES ALPINE.JS =====

/**
 * Función Alpine.js para el dataTable
 */
window.dataTable = function() {
    return {
        viewMode: window.innerWidth >= 768 ? 'table' : 'cards', // Default: table en desktop, cards en móvil

        init() {
            // Detectar cambios de tamaño de pantalla
            window.addEventListener('resize', () => {
                // En móvil siempre mostrar cards, en desktop permitir toggle
                if (window.innerWidth < 768) {
                    this.viewMode = 'cards';
                }
            });
        }
    }
}

/**
 * Función Alpine.js para el modal de arqueos de caja
 */
window.cashCountModal = function() {
    return {
        isOpen: false,
        
        cashCountData: null,
        activeTab: 'clientes', // Pestaña activa por defecto
        currencySymbol: CASH_COUNTS_CONFIG.currencySymbol,
        // Estado de paginación para productos
        productsPage: 1,
        productsPerPage: 10,
        // Estado de paginación para pedidos
        ordersPage: 1,
        ordersPerPage: 10,

        init() {
            // Guardar referencia global
            if (typeof window.cashCountModalInstance === 'undefined') {
                window.cashCountModalInstance = this;
            }
            cashCountModalInstance = this;
            
            // Asegurar que la configuración de moneda esté actualizada
            if (window.cashCountsData && window.cashCountsData.currencySymbol) {
                this.currencySymbol = window.cashCountsData.currencySymbol;
                CASH_COUNTS_CONFIG.currencySymbol = window.cashCountsData.currencySymbol;
            }
        },

        closeModal() {
            this.isOpen = false;
            this.cashCountData = null;
            // Reset paginación productos al cerrar
            this.productsPage = 1;
            this.ordersPage = 1;
            
            // Restaurar scroll del body
            document.body.style.overflow = 'auto';
        },

        async loadCashCountData(cashCountId) {
            try {
                const response = await fetch(`/cash-counts/${cashCountId}/details`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                
                if (data.success && data.data) {
                    this.cashCountData = data.data;
                    // Asegurar que la configuración de moneda esté actualizada
                    if (window.cashCountsData && window.cashCountsData.currencySymbol) {
                        CASH_COUNTS_CONFIG.currencySymbol = window.cashCountsData.currencySymbol;
                    }
                } else {
                    throw new Error(data.message || 'Error al cargar los datos');
                }
            } catch (error) {
                this.cashCountData = null;
                this.showNotification(`Error al cargar los datos del arqueo: ${error.message}`, 'error');
            }
        },

        formatCurrency(amount) {
            const currencySymbol = this.currencySymbol || getCurrentCurrencySymbol();
            if (amount === null || amount === undefined || amount === '') {
                return currencySymbol + ' 0.00';
            }
            const num = parseFloat(amount);
            if (isNaN(num)) {
                return currencySymbol + ' 0.00';
            }
            return currencySymbol + ' ' + num.toFixed(2);
        },

        formatDate(dateString) {
            return formatDate(dateString);
        },

        formatDateTime(dateString) {
            return formatDateTime(dateString);
        },

        showNotification(message, type = 'info') {
            showNotification(message, type);
        }
    }
}

// ===== INICIALIZACIÓN =====

/**
 * Inicializar la aplicación cuando el DOM esté listo
 */
function initializeApp() {
    // Inicializar gráficos
    initializeCharts();
    
    // Inicializar event listeners
    initializeEventListeners();
    
    // Inicializar búsqueda del servidor
    initializeSearchListener();
}

// Hacer funciones disponibles globalmente
window.cashCountsIndex = {
    initializeApp,
    closeCashCount,
    deleteCashCount,
    openCashCountModal: window.openCashCountModal,
    testModal: window.testModal,
    formatCurrency,
    formatDate,
    formatDateTime,
    showNotification,
    getCurrentCurrencySymbol,
    // Nuevas funciones de servidor
    isServerPaginationActive,
    loadCashCountsPage,
    initializeEventListeners,
    initializeSearchListener
};


