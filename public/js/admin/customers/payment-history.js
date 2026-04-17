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

/**
 * Confirmación moderna (ui-rich-dialog / tema oscuro). Mismo patrón que deleteCustomer en index.js.
 */
async function confirmPaymentHistoryDelete(options = {}) {
    const title = options.title || '¿Confirmar?';
    const text = options.text || '';
    const subtitle = options.subtitle || 'Verifica la información antes de continuar.';

    try {
        if (window.uiNotifications && typeof window.uiNotifications.confirmDialog === 'function') {
            return await window.uiNotifications.confirmDialog({
                title,
                text,
                subtitle,
                type: options.type || 'warning',
                confirmText: options.confirmText || 'Sí, eliminar',
                cancelText: options.cancelText || 'Cancelar',
                highlight: options.highlight || '',
                metrics: Array.isArray(options.metrics) ? options.metrics : [],
                items: Array.isArray(options.items) ? options.items : [],
            });
        }
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title,
                text,
                icon: 'warning',
                iconColor: '#fbbf24',
                showCancelButton: true,
                confirmButtonText: options.confirmText || 'Sí, eliminar',
                cancelButtonText: options.cancelText || 'Cancelar',
                reverseButtons: true,
                focusCancel: true,
                color: '#e2e8f0',
                customClass: {
                    popup: 'ui-swal-popup ui-swal-popup--futuristic',
                    confirmButton: 'ui-swal-confirm',
                    cancelButton: 'ui-swal-cancel',
                    htmlContainer: 'ui-swal-html',
                },
            });
            return Boolean(result.isConfirmed);
        }
    } catch (e) {
        console.error(e);
        return false;
    }
    return window.confirm(`${title}\n\n${text}`);
}

function notifyPaymentHistory(message, type = 'success') {
    const titles = {
        success: 'Listo',
        error: 'Error',
        warning: 'Atención',
        info: 'Información',
    };
    const toastType = type === 'success' ? 'success' : type === 'error' ? 'error' : 'info';

    if (window.uiNotifications && typeof window.uiNotifications.showToast === 'function') {
        window.uiNotifications.showToast(message, {
            type: toastType,
            title: titles[type] || titles.info,
            timeout: type === 'success' ? 3800 : 5200,
            theme: 'futuristic',
        });
        return;
    }
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: titles[type],
            text: message,
            timer: type === 'success' ? 3000 : undefined,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
        });
        return;
    }
    alert(message);
}

// ===== FUNCIÓN PRINCIPAL DE ALPINE.JS =====
function paymentHistory(initialData = {}) {
    return {
        showFilters: false,
        isDeleting: false,
        isSearching: false,
        selectionMode: false,
        selectedPaymentIds: [],
        searchTerm: initialData.searchTerm || '',
        _searchTimeout: null,

        init() {
            window.__paymentHistoryAlpineCtx = this;

            // Inicializar búsqueda del servidor
            this.initializeServerSearch();

            // Watcher para búsqueda fluida
            this.$watch('searchTerm', (value) => {
                this.performSearch();
            });
        },

        // ===== SELECCIÓN =====
        toggleSelectionMode() {
            this.selectionMode = !this.selectionMode;
            if (!this.selectionMode) {
                this.selectedPaymentIds = [];
            }
        },

        togglePaymentSelection(paymentId) {
            if (this.selectedPaymentIds.includes(paymentId)) {
                this.selectedPaymentIds = this.selectedPaymentIds.filter(id => id !== paymentId);
            } else {
                this.selectedPaymentIds.push(paymentId);
            }
        },

        currentPagePaymentIds() {
            return (window.paymentHistoryData?.payments || []).map(p => p.id);
        },

        allCurrentPageSelected() {
            const ids = this.currentPagePaymentIds();
            return ids.length > 0 && ids.every(id => this.selectedPaymentIds.includes(id));
        },

        toggleSelectAllOnPage() {
            const ids = this.currentPagePaymentIds();
            if (!ids.length) return;

            if (this.allCurrentPageSelected()) {
                this.selectedPaymentIds = this.selectedPaymentIds.filter(id => !ids.includes(id));
            } else {
                const merged = new Set([...this.selectedPaymentIds, ...ids]);
                this.selectedPaymentIds = [...merged];
            }
        },

        // ===== MÉTODOS DE BÚSQUEDA DEL SERVIDOR =====

        initializeServerSearch() {
            // Configurar filtros de fecha (estos permanecen como listeners tradicionales o Alpine)
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');

            if (dateFrom) {
                dateFrom.addEventListener('change', () => this.executeServerSearch());
            }
            if (dateTo) {
                dateTo.addEventListener('change', () => this.executeServerSearch());
            }
        },

        performSearch() {
            this.isSearching = true;
            clearTimeout(this._searchTimeout);
            this._searchTimeout = setTimeout(() => {
                this.executeServerSearch();
            }, 300);
        },

        clearSearch() {
            this.searchTerm = '';
        },

        executeServerSearch() {
            const url = new URL(window.location.href);

            // Obtener valores de los filtros
            const dateFrom = document.getElementById('date_from')?.value || '';
            const dateTo = document.getElementById('date_to')?.value || '';

            // Actualizar parámetros de URL
            if (this.searchTerm.trim()) {
                url.searchParams.set('customer_search', this.searchTerm.trim());
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

            // Siempre volver a la página 1 al buscar
            url.searchParams.delete('page');

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

                    // Actualizar tabla (tbody)
                    const newTableBody = doc.querySelector('.ui-table tbody');
                    const currentTableBody = document.querySelector('.ui-table tbody');
                    if (newTableBody && currentTableBody) {
                        currentTableBody.innerHTML = newTableBody.innerHTML;
                    }

                    // Actualizar tarjetas (contenedor de grid)
                    const newCardsGrid = doc.querySelector('#paymentHistoryCardsGrid');
                    const currentCardsGrid = document.querySelector('#paymentHistoryCardsGrid');
                    if (newCardsGrid && currentCardsGrid) {
                        currentCardsGrid.innerHTML = newCardsGrid.innerHTML;
                    }

                    const newMount = doc.querySelector('#paymentHistoryPaginationMount');
                    const currentMount = document.querySelector('#paymentHistoryPaginationMount');
                    if (newMount && currentMount) {
                        currentMount.innerHTML = newMount.innerHTML;
                    }

                    const newMeta = doc.querySelector('#paymentHistoryListMeta');
                    const currentMeta = document.querySelector('#paymentHistoryListMeta');
                    if (newMeta && currentMeta) {
                        currentMeta.textContent = newMeta.textContent;
                    }

                    const rowIds = Array.from(doc.querySelectorAll('.ui-table tbody tr[data-payment-id]'))
                        .map(row => parseInt(row.getAttribute('data-payment-id'), 10))
                        .filter(Number.isFinite);
                    if (window.paymentHistoryData) {
                        window.paymentHistoryData.payments = rowIds.map(id => ({ id }));
                    }
                    this.selectedPaymentIds = [];

                    // Actualizar URL sin recargar
                    window.history.pushState({}, '', url);

                    // Reinicializar event listeners si es necesario
                    this.initializeEventListeners();
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showAlert('Error al cargar los resultados', 'error');
                })
                .finally(() => {
                    this.hideLoadingIndicator();
                    this.isSearching = false;
                });
        },

        showLoadingIndicator() {
            // La UI de Alpine se encargará de esto mayormente a través de isSearching
        },

        hideLoadingIndicator() {
            // La UI de Alpine se encargará de esto mayormente a través de isSearching
        },

        initializeEventListeners() {
            // Delegación global en bindPaymentHistoryPaginationSpa; no hace falta re-vincular.
        },

        resetFilters() {
            // Limpiar campos de filtro
            this.searchTerm = '';
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');

            if (dateFrom) dateFrom.value = '';
            if (dateTo) dateTo.value = '';

            // Ejecutar búsqueda sin filtros
            this.executeServerSearch();
        },

        // ===== MÉTODOS DE ELIMINACIÓN =====

        async deletePayment(paymentId, customerName, paymentAmount) {
            if (this.isDeleting) return;

            const sym = this.getCurrencySymbol();
            const confirmed = await confirmPaymentHistoryDelete({
                title: '¿Eliminar este pago?',
                text: 'Se eliminará el abono y se restaurará la deuda del cliente por el mismo monto.',
                subtitle: 'Verifica cliente y monto antes de continuar.',
                type: 'warning',
                metrics: [
                    { label: 'Cliente', value: customerName },
                    { label: 'Monto del pago', value: `${sym} ${Number(paymentAmount).toFixed(2)}` },
                ],
            });

            if (confirmed) {
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

        async deleteSelectedPayments() {
            if (!this.selectedPaymentIds.length || this.isDeleting) return;

            const count = this.selectedPaymentIds.length;
            const confirmed = await confirmPaymentHistoryDelete({
                title: '¿Eliminar pagos seleccionados?',
                text: 'Se eliminarán los abonos y se restaurará la deuda de cada cliente según el monto de cada pago.',
                subtitle: 'Esta acción no se puede deshacer de forma sencilla.',
                type: 'warning',
                metrics: [{ label: 'Pagos seleccionados', value: String(count) }],
            });

            if (!confirmed) return;

            this.isDeleting = true;
            try {
                for (const paymentId of this.selectedPaymentIds) {
                    const response = await fetch(`${PAYMENT_HISTORY_CONFIG.routes.delete}/${paymentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                    });
                    if (!response.ok) {
                        const data = await response.json().catch(() => ({}));
                        throw new Error(data.message || `No se pudo eliminar el pago ${paymentId}`);
                    }
                }

                this.showAlert('Pagos eliminados correctamente.', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                this.showAlert(error.message || 'Error al eliminar pagos seleccionados.', 'error');
            } finally {
                this.isDeleting = false;
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
            notifyPaymentHistory(message, type);
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
                            label: function (context) {
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
                            callback: function (value) {
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
                            label: function (context) {
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
                            callback: function (value) {
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

(function bindPaymentHistoryPaginationSpa() {
    if (window.__paymentHistoryPaginationSpaBound) {
        return;
    }
    window.__paymentHistoryPaginationSpaBound = true;

    document.addEventListener('click', (e) => {
        const a = e.target.closest('#paymentHistoryPagination a[href]');
        if (!a || !a.getAttribute('href')) {
            return;
        }
        let targetUrl;
        try {
            targetUrl = new URL(a.href, window.location.href);
        } catch (_) {
            return;
        }
        if (targetUrl.origin !== window.location.origin) {
            return;
        }
        e.preventDefault();
        const ctx = window.__paymentHistoryAlpineCtx;
        if (ctx && typeof ctx.loadPaymentHistoryPage === 'function') {
            ctx.loadPaymentHistoryPage(targetUrl.toString());
        } else {
            window.location.assign(targetUrl.toString());
        }
    });

    document.addEventListener('change', (e) => {
        const sel = e.target.closest('#paymentHistoryPagination select[name="per_page"]');
        if (!sel) {
            return;
        }
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', sel.value);
        url.searchParams.set('page', '1');
        const ctx = window.__paymentHistoryAlpineCtx;
        if (ctx && typeof ctx.loadPaymentHistoryPage === 'function') {
            ctx.loadPaymentHistoryPage(url.toString());
        } else {
            window.location.assign(url.toString());
        }
    });
})();

// Limpiar gráficos cuando se navegue fuera de la página
window.addEventListener('beforeunload', cleanupCharts);

// Inicializar gráficos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    // Esperar un poco más para asegurar que Chart.js esté cargado
    setTimeout(initializeChartsOnce, 100);
});

// También intentar inicializar cuando la ventana esté completamente cargada
window.addEventListener('load', function () {
    if (typeof Chart === 'undefined') {
        setTimeout(initializeChartsOnce, 200);
    } else {
        initializeChartsOnce();
    }
});

// ===== FUNCIÓN GLOBAL PARA ELIMINAR PAGOS =====
async function deletePayment(paymentId, customerName, paymentAmount) {
    const sym = getCurrencySymbol();
    const confirmed = await confirmPaymentHistoryDelete({
        title: '¿Eliminar este pago?',
        text: 'Se eliminará el abono y se restaurará la deuda del cliente por el mismo monto.',
        subtitle: 'Verifica cliente y monto antes de continuar.',
        type: 'warning',
        metrics: [
            { label: 'Cliente', value: customerName },
            { label: 'Monto del pago', value: `${sym} ${Number(paymentAmount).toFixed(2)}` },
        ],
    });

    if (confirmed) {
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

/** Compatibilidad: código legacy que espera `{ isConfirmed }`. */
async function showConfirmAlert(title, text) {
    const ok = await confirmPaymentHistoryDelete({
        title,
        text,
        subtitle: 'Verifica la información antes de continuar.',
        type: 'warning',
    });
    return { isConfirmed: ok };
}

function showAlert(message, type = 'info') {
    notifyPaymentHistory(message, type);
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
