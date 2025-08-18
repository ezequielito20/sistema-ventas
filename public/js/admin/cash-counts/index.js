/**
 * JavaScript optimizado para cash-counts/index
 * Archivo: public/js/admin/cash-counts/index.js
 * Versión: 2.0.0
 * Descripción: Funciones específicas para la gestión de arqueos de caja
 */

// Script de prueba para verificar carga
console.log('✅ cash-counts/index.js cargado correctamente');

// ===== VARIABLES GLOBALES =====
let cashCountModalInstance = null;
let charts = {};

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
            text: '¿Estás seguro de que quieres eliminar este arqueo? Esta acción no se puede deshacer.',
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
        if (confirm('¿Estás seguro de que quieres eliminar este arqueo?')) {
            submitDeleteCashCount(cashCountId);
        }
    }
}

/**
 * Enviar formulario de eliminación de arqueo
 */
function submitDeleteCashCount(cashCountId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/cash-counts/${cashCountId}`;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    
    form.appendChild(csrfToken);
    form.appendChild(methodField);
    document.body.appendChild(form);
    form.submit();
}

/**
 * Abrir modal de detalles del arqueo de caja
 */
window.openCashCountModal = function(cashCountId) {
    if (cashCountModalInstance) {
        cashCountModalInstance.isOpen = true;
        cashCountModalInstance.cashCountData = null;
        
        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
        
        // Cargar datos del arqueo de forma asíncrona
        cashCountModalInstance.loadCashCountData(cashCountId);
    } else {
        console.error('Modal instance not found');
        showNotification('Error: Modal no disponible', 'error');
    }
};

/**
 * Función de prueba para el modal
 */
window.testModal = function() {
    if (cashCountModalInstance) {
        console.log('Probando modal...');
        cashCountModalInstance.isOpen = true;

        cashCountModalInstance.cashCountData = {
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
        console.error('Modal instance not found');
        showNotification('Error: Modal no disponible', 'error');
    }
};

// ===== FUNCIONES DE UTILIDAD =====

/**
 * Formatear moneda
 */
function formatCurrency(amount) {
    if (amount === null || amount === undefined || amount === '') {
        return CASH_COUNTS_CONFIG.currencySymbol + ' 0.00';
    }
    const num = parseFloat(amount);
    if (isNaN(num)) {
        return CASH_COUNTS_CONFIG.currencySymbol + ' 0.00';
    }
    return CASH_COUNTS_CONFIG.currencySymbol + ' ' + num.toFixed(2);
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
        console.error('Error formateando fecha:', error);
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
        console.error('Error formateando fecha/hora:', error);
        return 'N/A';
    }
}

/**
 * Mostrar notificación
 */
function showNotification(message, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${message}`);
    
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
        console.warn('Chart.js no está disponible');
        return;
    }

    console.log('Inicializando gráficos...');

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
    
    console.log('Gráficos inicializados correctamente');
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
            cashCountModalInstance = this;
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
                } else {
                    throw new Error(data.message || 'Error al cargar los datos');
                }
            } catch (error) {
                console.error('Error cargando datos:', error);
                this.cashCountData = null;
                this.showNotification(`Error al cargar los datos del arqueo: ${error.message}`, 'error');
            }
        },

        formatCurrency(amount) {
            return formatCurrency(amount);
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
    console.log('🚀 Inicializando aplicación cash-counts/index...');
    
    // Inicializar gráficos
    initializeCharts();
    
    console.log('🎉 Aplicación cash-counts/index inicializada correctamente');
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
    showNotification
};


