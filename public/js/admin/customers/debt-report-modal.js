// ===== CONFIGURACIÓN GLOBAL =====
const DEBT_REPORT_MODAL_CONFIG = {
    routes: {
        pdf: '/admin/customers/report',
        updateExchangeRate: '/admin/exchange-rate/update'
    },
    exchangeRate: {
        default: 134,
        localStorageKey: 'exchangeRate'
    },
    filters: {
        search: '',
        order: 'debt_desc',
        debtType: ''
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
    window.addEventListener('beforeunload', function() {
        localStorage.removeItem('debtReportFilters');
    });
}

// ===== FUNCIONES DE TIPO DE CAMBIO =====

// Función para inicializar el tipo de cambio
function initializeExchangeRate() {
    const exchangeRateInput = document.getElementById('modalExchangeRate');
    const updateButton = document.getElementById('updateModalExchangeRate');
    
    if (exchangeRateInput) {
        // Cargar valor guardado o usar el valor por defecto
        const savedRate = localStorage.getItem(DEBT_REPORT_MODAL_CONFIG.exchangeRate.localStorageKey);
        if (savedRate) {
            exchangeRateInput.value = savedRate;
        }
        
        // Actualizar valores en Bs cuando cambie el tipo de cambio
        exchangeRateInput.addEventListener('input', function() {
            updateBsValues(parseFloat(this.value) || DEBT_REPORT_MODAL_CONFIG.exchangeRate.default);
        });
    }
    
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
            // Guardar en localStorage
            localStorage.setItem(DEBT_REPORT_MODAL_CONFIG.exchangeRate.localStorageKey, newRate.toString());
            
            // Actualizar valores en Bs
            updateBsValues(newRate);
            
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
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    
    if (searchFilter) {
        searchFilter.addEventListener('input', debounce(applyFilters, 300));
    }
    
    if (orderFilter) {
        orderFilter.addEventListener('change', applyFilters);
    }
    
    if (debtTypeFilter) {
        debtTypeFilter.addEventListener('change', applyFilters);
    }
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearAllFilters);
    }
}

// Función para aplicar filtros
function applyFilters() {
    const searchFilter = document.getElementById('searchFilter');
    const orderFilter = document.getElementById('orderFilter');
    const debtTypeFilter = document.getElementById('debtTypeFilter');
    
    if (!searchFilter || !orderFilter || !debtTypeFilter) return;
    
    // Obtener valores de los filtros
    const filters = {
        search: searchFilter.value.toLowerCase(),
        order: orderFilter.value,
        debtType: debtTypeFilter.value
    };
    
    // Guardar filtros
    saveFilters(filters);
    
    // Aplicar filtros a la tabla
    filterTable(filters);
}

// Función para filtrar la tabla
function filterTable(filters) {
    const tableBody = document.querySelector('.table tbody');
    if (!tableBody) return;
    
    const rows = tableBody.querySelectorAll('tr');
    
    rows.forEach(row => {
        let showRow = true;
        
        // Filtro de búsqueda
        if (filters.search) {
            const customerName = row.querySelector('td:nth-child(2)')?.textContent?.toLowerCase() || '';
            const customerPhone = row.querySelector('td:nth-child(3)')?.textContent?.toLowerCase() || '';
            
            if (!customerName.includes(filters.search) && !customerPhone.includes(filters.search)) {
                showRow = false;
            }
        }
        
        // Filtro de tipo de deuda
        if (filters.debtType) {
            const badge = row.querySelector('.badge');
            if (badge) {
                const isDefaulter = badge.classList.contains('moroso');
                
                if (filters.debtType === 'defaulters' && !isDefaulter) {
                    showRow = false;
                } else if (filters.debtType === 'current' && isDefaulter) {
                    showRow = false;
                }
            }
        }
        
        // Mostrar/ocultar fila
        row.style.display = showRow ? '' : 'none';
    });
    
    // Aplicar ordenamiento
    sortTable(filters.order);
}

// Función para ordenar la tabla
function sortTable(orderBy) {
    const tableBody = document.querySelector('.table tbody');
    if (!tableBody) return;
    
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        switch (orderBy) {
            case 'debt_desc':
                return getDebtValue(b) - getDebtValue(a);
            case 'debt_asc':
                return getDebtValue(a) - getDebtValue(b);
            case 'name_asc':
                return getCustomerName(a).localeCompare(getCustomerName(b));
            case 'name_desc':
                return getCustomerName(b).localeCompare(getCustomerName(a));
            default:
                return 0;
        }
    });
    
    // Reordenar filas en la tabla
    rows.forEach(row => {
        tableBody.appendChild(row);
    });
}

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
    
    // Establecer valores por defecto
    if (searchFilter) searchFilter.value = '';
    if (orderFilter) orderFilter.value = 'debt_desc';
    if (debtTypeFilter) debtTypeFilter.value = '';
    
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
        button.addEventListener('click', function(e) {
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
    
    return {
        search: searchFilter?.value || '',
        order: orderFilter?.value || 'debt_desc',
        debtType: debtTypeFilter?.value || ''
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
