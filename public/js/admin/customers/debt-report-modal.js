// ===== CONFIGURACIÓN GLOBAL =====
const DEBT_REPORT_MODAL_CONFIG = {
    routes: {
        pdf: '/admin/customers/debt-report/download',
        updateExchangeRate: '/admin/exchange-rate/update'
    },
    exchangeRate: {
        default: 134,
        localStorageKey: 'exchangeRate'
    },
    filters: {
        search: '',
        order: 'debt_desc',
        debt_type: '',
        date_from: '',
        date_to: ''
    }
};

// ===== FUNCIONES PRINCIPALES =====

// Función para inicializar el modal de reporte de deudas
function initializeDebtReportModal() {
    // Inicializar tipo de cambio
    initializeExchangeRate();

    // Inicializar filtros
    initializeFilters();

    // Inicializar eventos
    initializeEvents();

    // Cargar filtros guardados (ahora reinicia a valores por defecto)
    loadSavedFilters();

    // Reiniciar filtros cuando se recarga la página
    window.addEventListener('beforeunload', function () {
        localStorage.removeItem('debtReportFilters');
    });
}

// ===== FUNCIONES DE TIPO DE CAMBIO =====

// Función para inicializar el tipo de cambio
function initializeExchangeRate() {
    const updateButton = document.getElementById('updateModalExchangeRate');


    if (updateButton) {
        updateButton.addEventListener('click', updateExchangeRate);
    }
}

// Función para actualizar el tipo de cambio
async function updateExchangeRate() {
    const exchangeRateInput = document.getElementById('modalExchangeRate');
    const updateButton = document.getElementById('updateModalExchangeRate');

    if (!exchangeRateInput || !updateButton) return;

    const newRate = parseFloat(exchangeRateInput.value);
    if (isNaN(newRate) || newRate <= 0) {
        showNotification('Por favor, ingrese un tipo de cambio válido', 'error');
        return;
    }

    // Mostrar estado de carga
    updateButton.disabled = true;
    updateButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Actualizando...';

    try {
        const response = await fetch(DEBT_REPORT_MODAL_CONFIG.routes.updateExchangeRate, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ exchange_rate: newRate })
        });

        const data = await response.json();

        if (response.ok) {
            // Guardar en localStorage (opcional, ahora preferimos el widget como fuente)
            localStorage.setItem(DEBT_REPORT_MODAL_CONFIG.exchangeRate.localStorageKey, newRate.toString());

            // Actualizar precios en el modal
            updateBsValues(newRate);

            // Sincronizar con el widget de la página principal disparando un evento global
            window.dispatchEvent(new CustomEvent('sync-rate', {
                detail: {
                    rate: newRate,
                    updatedAt: data.updated_at
                }
            }));

            showNotification('Tipo de cambio actualizado exitosamente', 'success');
        } else {
            showNotification(data.message || 'Error al actualizar el tipo de cambio', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión. Intente nuevamente.', 'error');
    } finally {
        // Restaurar botón
        updateButton.disabled = false;
        updateButton.innerHTML = '<i class="fas fa-sync-alt mr-1"></i><span class="hidden md:inline">Actualizar</span>';
    }
}

// Función para actualizar valores en bolívares
function updateBsValues(exchangeRate) {
    // Actualizar total en Bs
    const totalDebtElements = document.querySelectorAll('.modal-bs-debt');
    totalDebtElements.forEach(element => {
        const debt = parseFloat(element.dataset.debt) || 0;
        const bsValue = debt * exchangeRate;
        element.textContent = `Bs. ${bsValue.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    });

    // Actualizar valores individuales en la tabla
    const bsDebtElements = document.querySelectorAll('.bs-debt');
    bsDebtElements.forEach(element => {
        const debt = parseFloat(element.dataset.debt) || 0;
        const bsValue = debt * exchangeRate;
        element.textContent = `Bs. ${bsValue.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    });
}

// ===== FUNCIONES DE FILTROS =====

// Función para inicializar filtros
function initializeFilters() {
    const searchFilter = document.getElementById('searchFilter');
    const orderFilter = document.getElementById('orderFilter');
    const debtTypeFilter = document.getElementById('debtTypeFilter');
    const dateFromFilter = document.getElementById('dateFromFilter');
    const dateToFilter = document.getElementById('dateToFilter');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');

    if (searchFilter) {
        searchFilter.addEventListener('input', debounce(applyFilters, 400));
    }

    if (orderFilter) {
        orderFilter.addEventListener('change', applyFilters);
    }

    if (debtTypeFilter) {
        debtTypeFilter.addEventListener('change', applyFilters);
    }

    if (dateFromFilter) {
        dateFromFilter.addEventListener('change', applyFilters);
    }

    if (dateToFilter) {
        dateToFilter.addEventListener('change', applyFilters);
    }

    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearAllFilters);
    }
}

// Función para aplicar filtros (Ahora es Server-Side para actualizar estadísticas)
async function applyFilters() {
    const filters = getCurrentFilters();

    // Guardar filtros en memoria local
    saveFilters(filters);

    // Obtener contenedor de la tabla y stats
    const modalBody = document.querySelector('#debtReportModal .p-6.max-h-\\[80vh\\]');
    if (!modalBody) {
        console.error('No se encontró el cuerpo del modal con el selector #debtReportModal .p-6.max-h-\\[80vh\\]');
        return;
    }

    // Obtener tasa de cambio actual para no perderla
    const exchangeRateInput = document.getElementById('modalExchangeRate');
    const exchangeRate = exchangeRateInput ? exchangeRateInput.value : (window.debtReportModalData?.exchangeRate || 134);

    // Mostrar un sutil indicador de carga sobre la tabla
    const tableContainer = modalBody.querySelector('.bg-white.rounded-xl.shadow-sm');
    if (tableContainer) {
        tableContainer.style.opacity = '0.5';
        tableContainer.style.pointerEvents = 'none';
    }

    try {
        // Construir URL con filtros
        const url = new URL('/admin/customers/debt-report', window.location.origin);
        url.searchParams.set('ajax', '1');
        url.searchParams.set('exchange_rate', exchangeRate);

        if (filters.search) url.searchParams.set('search', filters.search);
        if (filters.order) url.searchParams.set('order', filters.order);
        if (filters.debt_type) url.searchParams.set('debt_type', filters.debt_type);
        if (filters.date_from) url.searchParams.set('date_from', filters.date_from);
        if (filters.date_to) url.searchParams.set('date_to', filters.date_to);

        const response = await fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        });

        if (response.ok) {
            const html = await response.text();

            // Extraer el contenido del body del modal
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newStats = doc.getElementById('debtReportStats');
            const newTable = doc.getElementById('debtReportTable');

            if (newStats && newTable) {
                const currentStats = document.getElementById('debtReportStats');
                const currentTable = document.getElementById('debtReportTable');

                if (currentStats) currentStats.innerHTML = newStats.innerHTML;
                if (currentTable) {
                    currentTable.innerHTML = newTable.innerHTML;
                    currentTable.style.opacity = '1';
                    currentTable.style.pointerEvents = 'auto';
                }

                // Re-inicializar valores Bs con la tasa actual
                updateBsValues(parseFloat(exchangeRate));
            }
        }
    } catch (error) {
        console.error('Error filtrando:', error);
        if (tableContainer) {
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';
        }
    }
}

// Las funciones filterTable y sortTable ahora se manejan en el servidor
function filterTable(filters) { }
function sortTable(orderBy) { }

// Función auxiliar para obtener el valor de deuda
function getDebtValue(row) {
    const debtCell = row.querySelector('td:nth-child(4)');
    if (!debtCell) return 0;

    const debtText = debtCell.textContent.replace(/[^0-9.-]/g, '');
    return parseFloat(debtText) || 0;
}

// Función auxiliar para obtener el nombre del cliente
function getCustomerName(row) {
    const nameCell = row.querySelector('td:nth-child(2)');
    if (!nameCell) return '';

    return nameCell.textContent.trim();
}

// Función para limpiar todos los filtros
function clearAllFilters() {
    // Usar la función de reinicio para limpiar filtros
    resetFiltersToDefault();

    showNotification('Filtros limpiados', 'success');
}

// ===== FUNCIONES DE PERSISTENCIA =====

// Función para guardar filtros
function saveFilters(filters) {
    localStorage.setItem('debtReportFilters', JSON.stringify(filters));
}

// Función para cargar filtros guardados
function loadSavedFilters() {
    // Reiniciar filtros a valores por defecto al cargar la página
    resetFiltersToDefault();
}

// Función para reiniciar filtros a valores por defecto
function resetFiltersToDefault() {
    const searchFilter = document.getElementById('searchFilter');
    const orderFilter = document.getElementById('orderFilter');
    const debtTypeFilter = document.getElementById('debtTypeFilter');
    const dateFromFilter = document.getElementById('dateFromFilter');
    const dateToFilter = document.getElementById('dateToFilter');

    // Establecer valores por defecto
    if (searchFilter) searchFilter.value = '';
    if (orderFilter) orderFilter.value = 'debt_desc';
    if (debtTypeFilter) debtTypeFilter.value = '';
    if (dateFromFilter) dateFromFilter.value = '';
    if (dateToFilter) dateToFilter.value = '';

    // Limpiar filtros guardados en localStorage
    localStorage.removeItem('debtReportFilters');

    // Aplicar filtros (mostrar todos los resultados)
    applyFilters();
}

// ===== FUNCIONES DE EVENTOS =====

// Función para inicializar eventos
function initializeEvents() {
    // Evento para descargar PDF
    const downloadPdfBtn = document.getElementById('downloadPdfBtn');
    if (downloadPdfBtn) {
        downloadPdfBtn.addEventListener('click', downloadPdf);
    }

    // Evento para cerrar modal
    const closeButtons = document.querySelectorAll('[onclick*="closeModal"]');
    closeButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            closeModal('debtReportModal');
        });
    });
}

// Función para descargar PDF
function downloadPdf() {
    const downloadPdfBtn = document.getElementById('downloadPdfBtn');
    if (!downloadPdfBtn) return;

    // Mostrar estado de carga
    downloadPdfBtn.disabled = true;
    downloadPdfBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Generando...';

    // Obtener filtros actuales
    const filters = getCurrentFilters();

    // Construir URL con filtros
    const url = new URL(DEBT_REPORT_MODAL_CONFIG.routes.pdf, window.location.origin);
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            url.searchParams.append(key, filters[key]);
        }
    });

    // Descargar PDF
    window.open(url.toString(), '_blank');

    // Restaurar botón después de un breve delay
    setTimeout(() => {
        downloadPdfBtn.disabled = false;
        downloadPdfBtn.innerHTML = '<i class="fas fa-file-pdf mr-1"></i><span class="hidden md:inline">PDF</span>';
    }, 2000);
}

// Función para obtener filtros actuales
function getCurrentFilters() {
    const searchFilter = document.getElementById('searchFilter');
    const orderFilter = document.getElementById('orderFilter');
    const debtTypeFilter = document.getElementById('debtTypeFilter');
    const dateFromFilter = document.getElementById('dateFromFilter');
    const dateToFilter = document.getElementById('dateToFilter');

    return {
        search: searchFilter?.value || '',
        order: orderFilter?.value || 'debt_desc',
        debt_type: debtTypeFilter?.value || '',
        date_from: dateFromFilter?.value || '',
        date_to: dateToFilter?.value || ''
    };
}

// ===== FUNCIONES DE UTILIDAD =====

// Función para obtener el token CSRF
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
        document.querySelector('input[name="_token"]')?.value ||
        '';
}

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

// Función debounce para optimizar búsqueda
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Función para cerrar modal
function closeModal(modalId) {
    // Si está usando Alpine.js
    if (typeof Alpine !== 'undefined' && Alpine.store) {
        Alpine.store('modal').close(modalId);
    } else {
        // Fallback para modales tradicionales
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
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

// ===== FUNCIONES DE INICIALIZACIÓN =====

// Función para inicializar cuando el DOM esté listo
function initializeWhenReady() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeDebtReportModal);
    } else {
        initializeDebtReportModal();
    }
}

// ===== EXPONER FUNCIONES GLOBALMENTE =====
window.initializeDebtReportModal = initializeDebtReportModal;
window.updateExchangeRate = updateExchangeRate;
window.updateBsValues = updateBsValues;
window.applyFilters = applyFilters;
window.clearAllFilters = clearAllFilters;
window.downloadPdf = downloadPdf;
window.showNotification = showNotification;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.formatDateTime = formatDateTime;
window.DEBT_REPORT_MODAL_CONFIG = DEBT_REPORT_MODAL_CONFIG;

// Inicializar cuando el script se cargue
initializeWhenReady();
