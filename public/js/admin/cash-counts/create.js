/**
 * JavaScript optimizado para cash-counts/create
 * Archivo: public/js/admin/cash-counts/create.js
 * Versión: 1.0.0
 * Descripción: Funciones específicas para el formulario de apertura de caja
 */

// Script de prueba para verificar carga
console.log('✅ cash-counts/create.js cargado correctamente');
console.log('SweetAlert2 disponible:', typeof Swal !== 'undefined');

// ===== CONFIGURACIÓN GLOBAL =====
const CASH_COUNT_CREATE_CONFIG = {
    currencySymbol: window.cashCountCreateData?.currencySymbol || '$',
    defaultAmount: '0.00'
};

// ===== FUNCIÓN ALPINE.JS =====

/**
 * Función Alpine.js para el formulario de apertura de caja
 */
window.cashCountForm = function() {
    return {
        openingDate: window.cashCountCreateData?.openingDate || new Date().toISOString().split('T')[0],
        openingTime: window.cashCountCreateData?.openingTime || new Date().toTimeString().slice(0, 5),
        initialAmount: window.cashCountCreateData?.initialAmount || CASH_COUNT_CREATE_CONFIG.defaultAmount,
        observations: window.cashCountCreateData?.observations || '',
        errors: {},
        isSubmitting: false,

        /**
         * Formatear monto inicial
         */
        formatAmount() {
            const n = parseFloat(this.initialAmount);
            this.initialAmount = isNaN(n) ? CASH_COUNT_CREATE_CONFIG.defaultAmount : n.toFixed(2);
        },

        /**
         * Validar formulario
         */
        validate() {
            this.errors = {};
            
            // Validar fecha
            if (!this.openingDate) {
                this.errors.opening_date = 'La fecha es obligatoria';
            } else {
                const selectedDate = new Date(this.openingDate);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate > today) {
                    this.errors.opening_date = 'La fecha no puede ser futura';
                }
            }
            
            // Validar hora
            if (!this.openingTime) {
                this.errors.opening_time = 'La hora es obligatoria';
            }
            
            // Validar monto inicial
            const amount = parseFloat(this.initialAmount);
            if (isNaN(amount)) {
                this.errors.initial_amount = 'Monto inválido';
            } else if (amount < 0) {
                this.errors.initial_amount = 'El monto no puede ser negativo';
            } else if (amount > 999999.99) {
                this.errors.initial_amount = 'El monto es demasiado alto';
            }
            
            return Object.keys(this.errors).length === 0;
        },

        /**
         * Manejar envío del formulario
         */
        async handleSubmit() {
            if (this.isSubmitting) return;
            
            if (!this.validate()) {
                this.showValidationError();
                return;
            }
            
            this.isSubmitting = true;
            
            try {
                // Mostrar indicador de carga
                this.showLoadingState();
                
                // Enviar formulario
                this.$refs.form.submit();
                
            } catch (error) {
                console.error('Error enviando formulario:', error);
                this.showError('Error al enviar el formulario. Inténtalo de nuevo.');
                this.isSubmitting = false;
            }
        },

        /**
         * Mostrar error de validación
         */
        showValidationError() {
            const errorMessages = Object.values(this.errors).join('\n');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Verifica los datos',
                    text: 'Corrige los campos marcados antes de continuar.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#6b7280'
                });
            } else {
                alert('Corrige los campos marcados:\n' + errorMessages);
            }
        },

        /**
         * Mostrar estado de carga
         */
        showLoadingState() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Abriendo caja...',
                    text: 'Por favor espera mientras se procesa la solicitud.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
        },

        /**
         * Mostrar error
         */
        showError(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444'
                });
            } else {
                alert('Error: ' + message);
            }
        },

        /**
         * Mostrar éxito
         */
        showSuccess(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#10b981',
                    timer: 3000,
                    timerProgressBar: true
                });
            } else {
                alert('Éxito: ' + message);
            }
        },

        /**
         * Limpiar errores
         */
        clearErrors() {
            this.errors = {};
        },

        /**
         * Resetear formulario
         */
        resetForm() {
            this.openingDate = new Date().toISOString().split('T')[0];
            this.openingTime = new Date().toTimeString().slice(0, 5);
            this.initialAmount = CASH_COUNT_CREATE_CONFIG.defaultAmount;
            this.observations = '';
            this.clearErrors();
        }
    }
}

// ===== FUNCIONES DE UTILIDAD =====

/**
 * Formatear moneda
 */
function formatCurrency(amount) {
    if (amount === null || amount === undefined || amount === '') {
        return CASH_COUNT_CREATE_CONFIG.currencySymbol + ' 0.00';
    }
    const num = parseFloat(amount);
    if (isNaN(num)) {
        return CASH_COUNT_CREATE_CONFIG.currencySymbol + ' 0.00';
    }
    return CASH_COUNT_CREATE_CONFIG.currencySymbol + ' ' + num.toFixed(2);
}

/**
 * Validar formato de fecha
 */
function isValidDate(dateString) {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date);
}

/**
 * Validar formato de hora
 */
function isValidTime(timeString) {
    const timeRegex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/;
    return timeRegex.test(timeString);
}

// ===== INICIALIZACIÓN =====

/**
 * Inicializar la aplicación cuando el DOM esté listo
 */
function initializeApp() {
    console.log('🚀 Inicializando aplicación cash-counts/create...');
    
    // Verificar que Alpine.js esté disponible
    if (typeof Alpine === 'undefined') {
        console.warn('⚠️ Alpine.js no está cargado');
    } else {
        console.log('✅ Alpine.js cargado correctamente');
    }
    
    // Verificar que SweetAlert2 esté disponible
    if (typeof Swal === 'undefined') {
        console.warn('⚠️ SweetAlert2 no está cargado');
    } else {
        console.log('✅ SweetAlert2 cargado correctamente');
    }
    
    console.log('🎉 Aplicación cash-counts/create inicializada correctamente');
}

// Hacer funciones disponibles globalmente
window.cashCountCreate = {
    initializeApp,
    formatCurrency,
    isValidDate,
    isValidTime
};

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}
