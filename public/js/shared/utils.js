/**
 * Utilidades compartidas del sistema
 * Archivo: public/js/shared/utils.js
 * Versión: 1.0.0
 */

// Script de prueba para verificar carga
console.log('✅ utils.js cargado correctamente');

// ===== UTILIDADES DE FORMATO =====

/**
 * Formatear moneda
 * @param {number} amount - Cantidad a formatear
 * @param {string} currency - Símbolo de moneda (por defecto '$')
 * @returns {string} - Cantidad formateada
 */
function formatCurrency(amount, currency = '$') {
    if (amount === null || amount === undefined || amount === '') {
        return currency + ' 0.00';
    }
    const num = parseFloat(amount);
    if (isNaN(num)) {
        return currency + ' 0.00';
    }
    return currency + ' ' + num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Formatear fecha
 * @param {string} dateString - Fecha en formato string
 * @returns {string} - Fecha formateada
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
 * @param {string} dateString - Fecha en formato string
 * @returns {string} - Fecha y hora formateada
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

// ===== UTILIDADES DE NOTIFICACIONES =====

/**
 * Mostrar notificación con SweetAlert2
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo de notificación (success, error, warning, info)
 * @param {object} options - Opciones adicionales
 */
function showNotification(message, type = 'info', options = {}) {
    const defaultOptions = {
        icon: type,
        title: type.charAt(0).toUpperCase() + type.slice(1),
        text: message,
        confirmButtonColor: '#667eea',
        timer: type === 'error' ? null : 3000,
        timerProgressBar: type !== 'error'
    };

    const finalOptions = { ...defaultOptions, ...options };

    if (typeof Swal !== 'undefined') {
        Swal.fire(finalOptions);
    } else {
        // Fallback a alert nativo
        const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : type === 'warning' ? '⚠️' : 'ℹ️';
        alert(`${icon} ${message}`);
    }
}

/**
 * Mostrar notificación toast
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo de notificación
 */
function showToast(message, type = 'info') {
    showNotification(message, type, {
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

// ===== UTILIDADES DE VALIDACIÓN =====

/**
 * Validar email
 * @param {string} email - Email a validar
 * @returns {boolean} - True si es válido
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validar teléfono
 * @param {string} phone - Teléfono a validar
 * @returns {boolean} - True si es válido
 */
function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

/**
 * Validar número
 * @param {string|number} value - Valor a validar
 * @returns {boolean} - True si es un número válido
 */
function isValidNumber(value) {
    return !isNaN(parseFloat(value)) && isFinite(value);
}

// ===== UTILIDADES DE DOM =====

/**
 * Crear elemento con clases
 * @param {string} tag - Tag del elemento
 * @param {string} className - Clases CSS
 * @param {string} text - Texto del elemento
 * @returns {HTMLElement} - Elemento creado
 */
function createElement(tag, className = '', text = '') {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (text) element.textContent = text;
    return element;
}

/**
 * Obtener elemento por selector con timeout
 * @param {string} selector - Selector CSS
 * @param {number} timeout - Timeout en ms
 * @returns {Promise<HTMLElement>} - Elemento encontrado
 */
function waitForElement(selector, timeout = 5000) {
    return new Promise((resolve, reject) => {
        const element = document.querySelector(selector);
        if (element) {
            resolve(element);
            return;
        }

        const observer = new MutationObserver(() => {
            const element = document.querySelector(selector);
            if (element) {
                observer.disconnect();
                resolve(element);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        setTimeout(() => {
            observer.disconnect();
            reject(new Error(`Elemento ${selector} no encontrado en ${timeout}ms`));
        }, timeout);
    });
}

// ===== UTILIDADES DE ARRAYS Y OBJETOS =====

/**
 * Agrupar array por propiedad
 * @param {Array} array - Array a agrupar
 * @param {string} key - Propiedad para agrupar
 * @returns {Object} - Objeto agrupado
 */
function groupBy(array, key) {
    return array.reduce((groups, item) => {
        const group = item[key];
        groups[group] = groups[group] || [];
        groups[group].push(item);
        return groups;
    }, {});
}

/**
 * Ordenar array por múltiples propiedades
 * @param {Array} array - Array a ordenar
 * @param {Array} properties - Propiedades para ordenar
 * @returns {Array} - Array ordenado
 */
function sortByMultiple(array, properties) {
    return array.sort((a, b) => {
        for (let prop of properties) {
            const aVal = a[prop];
            const bVal = b[prop];
            
            if (aVal < bVal) return -1;
            if (aVal > bVal) return 1;
        }
        return 0;
    });
}

/**
 * Filtrar array por múltiples criterios
 * @param {Array} array - Array a filtrar
 * @param {Object} filters - Criterios de filtrado
 * @returns {Array} - Array filtrado
 */
function filterByMultiple(array, filters) {
    return array.filter(item => {
        return Object.keys(filters).every(key => {
            const filterValue = filters[key];
            const itemValue = item[key];
            
            if (typeof filterValue === 'string') {
                return itemValue.toLowerCase().includes(filterValue.toLowerCase());
            }
            if (typeof filterValue === 'number') {
                return itemValue === filterValue;
            }
            if (Array.isArray(filterValue)) {
                return filterValue.includes(itemValue);
            }
            return itemValue === filterValue;
        });
    });
}

// ===== UTILIDADES DE STORAGE =====

/**
 * Guardar en localStorage con expiración
 * @param {string} key - Clave
 * @param {any} value - Valor
 * @param {number} ttl - Tiempo de vida en segundos
 */
function setStorageWithTTL(key, value, ttl = 3600) {
    const item = {
        value: value,
        timestamp: Date.now(),
        ttl: ttl * 1000
    };
    localStorage.setItem(key, JSON.stringify(item));
}

/**
 * Obtener de localStorage con expiración
 * @param {string} key - Clave
 * @returns {any|null} - Valor o null si expiró
 */
function getStorageWithTTL(key) {
    const item = localStorage.getItem(key);
    if (!item) return null;
    
    try {
        const parsed = JSON.parse(item);
        const now = Date.now();
        
        if (now - parsed.timestamp > parsed.ttl) {
            localStorage.removeItem(key);
            return null;
        }
        
        return parsed.value;
    } catch (error) {
        console.error('Error parsing localStorage item:', error);
        return null;
    }
}

// ===== UTILIDADES DE DEBOUNCE Y THROTTLE =====

/**
 * Debounce function
 * @param {Function} func - Función a debounce
 * @param {number} wait - Tiempo de espera en ms
 * @returns {Function} - Función debounced
 */
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

/**
 * Throttle function
 * @param {Function} func - Función a throttle
 * @param {number} limit - Límite de tiempo en ms
 * @returns {Function} - Función throttled
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ===== UTILIDADES DE API =====

/**
 * Hacer petición AJAX con manejo de errores
 * @param {string} url - URL de la petición
 * @param {Object} options - Opciones de la petición
 * @returns {Promise} - Promise con la respuesta
 */
async function apiRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    };

    const finalOptions = { ...defaultOptions, ...options };

    try {
        const response = await fetch(url, finalOptions);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return await response.json();
        }

        return await response.text();
    } catch (error) {
        console.error('Error en petición API:', error);
        throw error;
    }
}

// ===== EXPORTAR FUNCIONES =====

// Hacer funciones disponibles globalmente
window.utils = {
    formatCurrency,
    formatDate,
    formatDateTime,
    showNotification,
    showToast,
    isValidEmail,
    isValidPhone,
    isValidNumber,
    createElement,
    waitForElement,
    groupBy,
    sortByMultiple,
    filterByMultiple,
    setStorageWithTTL,
    getStorageWithTTL,
    debounce,
    throttle,
    apiRequest
};

// También exportar individualmente para compatibilidad
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.formatDateTime = formatDateTime;
window.showNotification = showNotification;
window.showToast = showToast;
