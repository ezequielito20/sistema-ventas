// ===== CONFIGURACIÓN GLOBAL =====
const PAYMENT_HISTORY_CONFIG = {
    routes: {
        delete: '/admin/customers/payment-history',
        index: '/admin/customers/payment-history'
    },
    pagination: {
        itemsPerPage: 15
    },
    charts: {
        colors: {
            primary: 'rgba(102, 126, 234, 0.6)',
            primaryBorder: 'rgba(102, 126, 234, 1)',
            success: 'rgba(16, 185, 129, 0.2)',
            successBorder: 'rgba(16, 185, 129, 1)',
            tooltip: 'rgba(31, 41, 55, 0.9)',
            tooltipBorder: 'rgba(255, 255, 255, 0.2)'
        }
    }
};

// ===== FUNCIÓN PRINCIPAL DE ALPINE.JS =====
function paymentHistory() {
    return {
        viewMode: window.innerWidth >= 1024 ? 'table' : 'cards',
        showFilters: false,
        isDeleting: false,

        init() {
            // Detectar el modo de vista inicial basado en el tamaño de pantalla
            this.updateViewMode();
            
            // Escuchar cambios de tamaño de ventana
            window.addEventListener('resize', () => {
                this.updateViewMode();
            });

            // Inicializar búsqueda del servidor
            this.initializeServerSearch();
            
            // Inicializar paginación inteligente
            this.initializeSmartPagination();
        },

        // ===== MÉTODOS DE INICIALIZACIÓN =====

        updateViewMode() {
            if (window.innerWidth >= 1024) {
                this.viewMode = 'table';
            } else {
                this.viewMode = 'cards';
            }
        },

        // ===== MÉTODOS DE PAGINACIÓN INTELIGENTE =====

        initializeSmartPagination() {
            // Interceptar clicks de paginación cuando servidor está activo
            document.addEventListener('click', (e) => {
                const paginationLink = e.target.closest('.pagination-btn, .page-number');
                if (paginationLink && paginationLink.href && this.isServerPaginationActive()) {
                    e.preventDefault();
                    this.loadPaymentHistoryPage(paginationLink.href);
                }
            });
        },

        // Detectar si la paginación del servidor está activa
        isServerPaginationActive() {
            const paginator = document.querySelector('.pagination-container .page-numbers a');
            return !!paginator; // existen enlaces → servidor
        },

        // ===== MÉTODOS DE BÚSQUEDA DEL SERVIDOR =====

        initializeServerSearch() {
            // Configurar búsqueda por cliente
            const customerSearch = document.getElementById('customer_search');
            if (customerSearch) {
                let timeout;
                customerSearch.addEventListener('input', (e) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        this.executeServerSearch();
                    }, 300);
                });
            }

            // Configurar filtros de fecha
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            
            if (dateFrom) {
                dateFrom.addEventListener('change', () => this.executeServerSearch());
            }
            if (dateTo) {
                dateTo.addEventListener('change', () => this.executeServerSearch());
            }
        },

        executeServerSearch() {
            const url = new URL(window.location.href);
            
            // Obtener valores de los filtros
            const customerSearch = document.getElementById('customer_search')?.value || '';
            const dateFrom = document.getElementById('date_from')?.value || '';
            const dateTo = document.getElementById('date_to')?.value || '';
            
            // Actualizar parámetros de URL
            if (customerSearch.trim()) {
                url.searchParams.set('customer_search', customerSearch.trim());
            } else {
                url.searchParams.delete('customer_search');
            }
            
            if (dateFrom) {
                url.searchParams.set('date_from', dateFrom);
            } else {
                url.searchParams.delete('date_from');
            }
            
            if (dateTo) {
                url.searchParams.set('date_to', dateTo);
            } else {
                url.searchParams.delete('date_to');
            }
            
            // Cargar página con filtros
            this.loadPaymentHistoryPage(url.toString());
        },

        loadPaymentHistoryPage(url) {
            // Mostrar indicador de carga
            this.showLoadingIndicator();
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, application/xhtml+xml'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Error al cargar');
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Actualizar tabla
                const newTableBody = doc.querySelector('tbody');
                const currentTableBody = document.querySelector('tbody');
                if (newTableBody && currentTableBody) {
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                }
                
                // Actualizar tarjetas
                const newCardsGrid = doc.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.xl\\:grid-cols-3');
                const currentCardsGrid = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.xl\\:grid-cols-3');
                if (newCardsGrid && currentCardsGrid) {
                    currentCardsGrid.innerHTML = newCardsGrid.innerHTML;
                }
                
                // Actualizar paginación inteligente
                const newPaginationContainer = doc.querySelector('.pagination-container');
                const currentPaginationContainer = document.querySelector('.pagination-container');
                if (newPaginationContainer && currentPaginationContainer) {
                    currentPaginationContainer.innerHTML = newPaginationContainer.innerHTML;
                }
                
                // Actualizar estadísticas
                this.updateStatistics(doc);
                
                // Actualizar URL sin recargar
                window.history.pushState({}, '', url);
                
                // Reinicializar event listeners
                this.initializeEventListeners();
            })
            .catch(error => {
                console.error('Error:', error);
                this.showAlert('Error al cargar los resultados', 'error');
            })
            .finally(() => {
                this.hideLoadingIndicator();
            });
        },

        showLoadingIndicator() {
            const searchInput = document.getElementById('customer_search');
            if (searchInput) {
                searchInput.classList.add('search-loading');
                searchInput.disabled = true;
            }
        },

        hideLoadingIndicator() {
            const searchInput = document.getElementById('customer_search');
            if (searchInput) {
                searchInput.classList.remove('search-loading');
                searchInput.disabled = false;
            }
        },

        updateStatistics(doc) {
            // Actualizar estadísticas si es necesario
            // Los gráficos se mantienen igual ya que son datos globales
        },

        initializeEventListeners() {
            // Reinicializar event listeners para elementos dinámicos
            // Esto se llama después de cargar contenido via AJAX
            
            // Reinicializar paginación inteligente
            this.initializeSmartPagination();
        },

        resetFilters() {
            // Limpiar campos de filtro
            const customerSearch = document.getElementById('customer_search');
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            
            if (customerSearch) customerSearch.value = '';
            if (dateFrom) dateFrom.value = '';
            if (dateTo) dateTo.value = '';
            
            // Ejecutar búsqueda sin filtros
            this.executeServerSearch();
        },

        // ===== MÉTODOS DE ELIMINACIÓN =====

        async deletePayment(paymentId, customerName, paymentAmount) {
            if (this.isDeleting) return;

            const result = await this.showConfirmAlert(
                '¿Estás seguro?',
                `Vas a eliminar el pago de ${paymentAmount} ${this.getCurrencySymbol()} del cliente ${customerName}. Esta acción restaurará la deuda al cliente.`,
                'warning'
            );

            if (result.isConfirmed) {
                this.isDeleting = true;

                try {
                    const response = await fetch(`${PAYMENT_HISTORY_CONFIG.routes.delete}/${paymentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.showAlert('¡Pago eliminado exitosamente!', 'success');
                        
                        // Recargar la página para actualizar datos
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showAlert(data.message || 'Error al eliminar el pago', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showAlert('Error de conexión. Intente nuevamente.', 'error');
                } finally {
                    this.isDeleting = false;
                }
            }
        },

        // ===== MÉTODOS DE UTILIDAD =====

        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                   document.querySelector('input[name="_token"]')?.value || 
                   '';
        },

        getCurrencySymbol() {
            return window.paymentHistoryData?.currency?.symbol || '$';
        },

        showAlert(message, type = 'info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: this.getAlertTitle(type),
                    text: message,
                    icon: type,
                    confirmButtonText: 'Entendido',
                    timer: type === 'success' ? 3000 : undefined,
                    timerProgressBar: type === 'success'
                });
            } else {
                alert(message);
            }
        },

        async showConfirmAlert(title, text, icon = 'warning') {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                });
                return result;
            } else {
                return { isConfirmed: confirm(text) };
            }
        },

        getAlertTitle(type) {
            switch(type) {
                case 'success': return '¡Éxito!';
                case 'error': return 'Error';
                case 'warning': return 'Advertencia';
                default: return 'Información';
            }
        }
    }
}

// ===== VARIABLES GLOBALES PARA LOS GRÁFICOS =====
let weekdayChartInstance = null;
let monthlyChartInstance = null;

// ===== FUNCIONES PARA LOS GRÁFICOS =====

// Función para inicializar gráficos de forma segura
function initializeCharts() {
    // Verificar si Chart.js está disponible
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js no está disponible. Los gráficos no se mostrarán.');
        return;
    }

    // Verificar si los elementos del canvas existen
    const weekdayChart = document.getElementById('weekdayChart');
    const monthlyChart = document.getElementById('monthlyChart');

    if (!weekdayChart && !monthlyChart) {
        console.warn('No se encontraron elementos de gráficos en la página.');
        return;
    }

    // Destruir gráficos existentes antes de crear nuevos
    if (weekdayChartInstance) {
        weekdayChartInstance.destroy();
        weekdayChartInstance = null;
    }
    if (monthlyChartInstance) {
        monthlyChartInstance.destroy();
        monthlyChartInstance = null;
    }

    // Obtener datos de los gráficos desde window
    const chartData = window.paymentHistoryData?.charts || {};
    const currencySymbol = window.paymentHistoryData?.currency?.symbol || '$';

    // Gráfico por día de la semana
    if (document.getElementById('weekdayChart')) {
        const weekdayCtx = document.getElementById('weekdayChart').getContext('2d');
        weekdayChartInstance = new Chart(weekdayCtx, {
            type: 'bar',
            data: {
                labels: chartData.weekdayLabels || [],
                datasets: [{
                    label: 'Pagos por día de la semana',
                    data: chartData.weekdayData || [],
                    backgroundColor: PAYMENT_HISTORY_CONFIG.charts.colors.primary,
                    borderColor: PAYMENT_HISTORY_CONFIG.charts.colors.primaryBorder,
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: PAYMENT_HISTORY_CONFIG.charts.colors.tooltip,
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: PAYMENT_HISTORY_CONFIG.charts.colors.tooltipBorder,
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return currencySymbol + ' ' + context.raw.toFixed(2);
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
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return currencySymbol + ' ' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico por mes
    if (document.getElementById('monthlyChart')) {
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        monthlyChartInstance = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: chartData.monthlyLabels || [],
                datasets: [{
                    label: 'Pagos por mes',
                    data: chartData.monthlyData || [],
                    backgroundColor: PAYMENT_HISTORY_CONFIG.charts.colors.success,
                    borderColor: PAYMENT_HISTORY_CONFIG.charts.colors.successBorder,
                    borderWidth: 3,
                    tension: 0.4,
                    pointBackgroundColor: PAYMENT_HISTORY_CONFIG.charts.colors.successBorder,
                    pointBorderColor: 'white',
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
                        display: false
                    },
                    tooltip: {
                        backgroundColor: PAYMENT_HISTORY_CONFIG.charts.colors.tooltip,
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: PAYMENT_HISTORY_CONFIG.charts.colors.tooltipBorder,
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return currencySymbol + ' ' + context.raw.toFixed(2);
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
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return currencySymbol + ' ' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }
}

// Variable para controlar si ya se inicializaron los gráficos
let chartsInitialized = false;

// Función para inicializar gráficos una sola vez
function initializeChartsOnce() {
    if (chartsInitialized) {
        return;
    }
    
    if (typeof Chart !== 'undefined') {
        initializeCharts();
        chartsInitialized = true;
    }
}

// Función para limpiar gráficos al salir de la página
function cleanupCharts() {
    if (weekdayChartInstance) {
        weekdayChartInstance.destroy();
        weekdayChartInstance = null;
    }
    if (monthlyChartInstance) {
        monthlyChartInstance.destroy();
        monthlyChartInstance = null;
    }
    chartsInitialized = false;
}

// ===== FUNCIONES GLOBALES =====

// Función para mostrar notificaciones
function showNotification(message, type = 'success') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: type === 'success' ? '¡Éxito!' : type === 'error' ? 'Error' : 'Información',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: type === 'success' ? 3000 : undefined,
            timerProgressBar: type === 'success'
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

// Función para calcular total de pagos
function calculateTotalPayments(payments) {
    return payments.reduce((sum, payment) => sum + parseFloat(payment.payment_amount), 0);
}

// Función para calcular promedio de pagos
function calculateAveragePayment(payments) {
    if (payments.length === 0) return 0;
    const total = calculateTotalPayments(payments);
    return total / payments.length;
}

// ===== EVENT LISTENERS =====

// Interceptar clicks de paginación para navegación sin recargar
document.addEventListener('click', (e) => {
    const link = e.target.closest('.pagination .page-link, .pagination-btn, .page-number');
    if (link && link.href) {
        e.preventDefault();
        const url = link.href;
        if (window.paymentHistory && window.paymentHistory.loadPaymentHistoryPage) {
            window.paymentHistory.loadPaymentHistoryPage(url);
        } else {
            window.location.href = url;
        }
    }
});

// Limpiar gráficos cuando se navegue fuera de la página
window.addEventListener('beforeunload', cleanupCharts);

// Inicializar gráficos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un poco más para asegurar que Chart.js esté cargado
    setTimeout(initializeChartsOnce, 100);
});

// También intentar inicializar cuando la ventana esté completamente cargada
window.addEventListener('load', function() {
    if (typeof Chart === 'undefined') {
        setTimeout(initializeChartsOnce, 200);
    } else {
        initializeChartsOnce();
    }
});

// ===== FUNCIÓN GLOBAL PARA ELIMINAR PAGOS =====
async function deletePayment(paymentId, customerName, paymentAmount) {
    const result = await showConfirmAlert(
        '¿Estás seguro?',
        `Vas a eliminar el pago de ${paymentAmount} ${getCurrencySymbol()} del cliente ${customerName}. Esta acción restaurará la deuda al cliente.`,
        'warning'
    );

    if (result.isConfirmed) {
        try {
            const response = await fetch(`${PAYMENT_HISTORY_CONFIG.routes.delete}/${paymentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                showAlert('¡Pago eliminado exitosamente!', 'success');
                
                // Recargar la página para actualizar datos
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert(data.message || 'Error al eliminar el pago', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error de conexión. Intente nuevamente.', 'error');
        }
    }
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
           document.querySelector('input[name="_token"]')?.value || 
           '';
}

function getCurrencySymbol() {
    return window.paymentHistoryData?.currency?.symbol || '$';
}

async function showConfirmAlert(title, text, icon = 'warning') {
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        });
        return result;
    } else {
        return { isConfirmed: confirm(text) };
    }
}

function showAlert(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: getAlertTitle(type),
            text: message,
            icon: type,
            confirmButtonText: 'Entendido',
            timer: type === 'success' ? 3000 : undefined,
            timerProgressBar: type === 'success'
        });
    } else {
        alert(message);
    }
}

function getAlertTitle(type) {
    switch(type) {
        case 'success': return '¡Éxito!';
        case 'error': return 'Error';
        case 'warning': return 'Advertencia';
        default: return 'Información';
    }
}

// ===== EXPONER FUNCIONES GLOBALMENTE =====
window.paymentHistory = paymentHistory;
window.showNotification = showNotification;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.formatDateTime = formatDateTime;
window.calculateTotalPayments = calculateTotalPayments;
window.calculateAveragePayment = calculateAveragePayment;
window.initializeCharts = initializeCharts;
window.cleanupCharts = cleanupCharts;
window.PAYMENT_HISTORY_CONFIG = PAYMENT_HISTORY_CONFIG;
window.deletePayment = deletePayment;
window.showAlert = showAlert;
window.showConfirmAlert = showConfirmAlert;
