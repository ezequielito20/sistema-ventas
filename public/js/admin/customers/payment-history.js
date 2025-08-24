// ===== CONFIGURACIÓN GLOBAL =====
const PAYMENT_HISTORY_CONFIG = {
    routes: {
        delete: '/admin/customers/payment-history',
        index: '/admin/customers'
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
        filters: {
            customer_search: '',
            date_from: '',
            date_to: ''
        },
        filteredPayments: [],
        allPayments: [],
        currentPage: 1,
        itemsPerPage: PAYMENT_HISTORY_CONFIG.pagination.itemsPerPage,
        isDeleting: false,

        init() {
            // Inicializar datos desde window.paymentHistoryData
            this.initializeData();
            
            // Detectar el modo de vista inicial basado en el tamaño de pantalla
            this.updateViewMode();
            
            // Escuchar cambios de tamaño de ventana
            window.addEventListener('resize', () => {
                this.updateViewMode();
            });

            // Configurar watchers para filtros
            this.$watch('filters.customer_search', () => this.applyFilters());
            this.$watch('filters.date_from', () => this.applyFilters());
            this.$watch('filters.date_to', () => this.applyFilters());
        },

        // ===== MÉTODOS DE INICIALIZACIÓN =====

        initializeData() {
            const paymentData = window.paymentHistoryData || {};
            this.allPayments = paymentData.payments || [];
            this.filteredPayments = [...this.allPayments];
        },

        updateViewMode() {
            if (window.innerWidth >= 1024) {
                this.viewMode = 'table';
            } else {
                this.viewMode = 'cards';
            }
        },

        // ===== MÉTODOS DE FILTRADO =====

        applyFilters() {
            // Filtrar pagos en tiempo real
            this.filteredPayments = this.allPayments.filter(payment => {
                let matches = true;
                
                // Filtro por nombre de cliente
                if (this.filters.customer_search) {
                    const customerName = payment.customer.name.toLowerCase();
                    const searchTerm = this.filters.customer_search.toLowerCase();
                    matches = matches && customerName.includes(searchTerm);
                }
                
                // Filtro por fecha desde
                if (this.filters.date_from) {
                    const paymentDate = new Date(payment.created_at);
                    const fromDate = new Date(this.filters.date_from);
                    matches = matches && paymentDate >= fromDate;
                }
                
                // Filtro por fecha hasta
                if (this.filters.date_to) {
                    const paymentDate = new Date(payment.created_at);
                    const toDate = new Date(this.filters.date_to);
                    toDate.setHours(23, 59, 59); // Incluir todo el día
                    matches = matches && paymentDate <= toDate;
                }
                
                return matches;
            });
            
            // Resetear a la primera página
            this.currentPage = 1;
        },

        resetFilters() {
            this.filters = {
                customer_search: '',
                date_from: '',
                date_to: ''
            };
            this.filteredPayments = [...this.allPayments];
            this.currentPage = 1;
        },

        // ===== MÉTODOS DE PAGINACIÓN =====

        get paginatedPayments() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredPayments.slice(start, end);
        },

        get totalPages() {
            return Math.ceil(this.filteredPayments.length / this.itemsPerPage);
        },

        get hasNextPage() {
            return this.currentPage < this.totalPages;
        },

        get hasPrevPage() {
            return this.currentPage > 1;
        },

        nextPage() {
            if (this.hasNextPage) {
                this.currentPage++;
            }
        },

        prevPage() {
            if (this.hasPrevPage) {
                this.currentPage--;
            }
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
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
