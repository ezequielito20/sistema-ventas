/**
 * JavaScript optimizado para cash-counts/edit
 * Archivo: public/js/admin/cash-counts/edit.js
 * Versión: 1.0.0
 * Descripción: Funciones específicas para el formulario de edición de caja
 */

// Script de prueba para verificar carga
console.log('✅ cash-counts/edit.js cargado correctamente');
console.log('SweetAlert2 disponible:', typeof Swal !== 'undefined');

// ===== CONFIGURACIÓN GLOBAL =====
const CASH_COUNT_EDIT_CONFIG = {
    currencySymbol: window.cashCountEditData?.currencySymbol || '$',
    defaultAmount: '0.00',
    cashCountId: window.cashCountEditData?.cashCountId || null
};

// ===== FUNCIÓN ALPINE.JS =====

/**
 * Función Alpine.js para el formulario de edición de caja
 */
window.editCashCountForm = function() {
    return {
        openingDate: window.cashCountEditData?.openingDate || new Date().toISOString().split('T')[0],
        openingTime: window.cashCountEditData?.openingTime || new Date().toTimeString().slice(0, 5),
        initialAmount: window.cashCountEditData?.initialAmount || CASH_COUNT_EDIT_CONFIG.defaultAmount,
        observations: window.cashCountEditData?.observations || '',
        errors: {},
        isSubmitting: false,
        originalData: {},

        /**
         * Inicializar datos originales para comparación
         */
        init() {
            this.originalData = {
                openingDate: this.openingDate,
                openingTime: this.openingTime,
                initialAmount: this.initialAmount,
                observations: this.observations
            };
        },

        /**
         * Formatear monto inicial
         */
        formatAmount() {
            const n = parseFloat(this.initialAmount);
            this.initialAmount = isNaN(n) ? CASH_COUNT_EDIT_CONFIG.defaultAmount : n.toFixed(2);
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
         * Verificar si hay cambios en el formulario
         */
        hasChanges() {
            return this.openingDate !== this.originalData.openingDate ||
                   this.openingTime !== this.originalData.openingTime ||
                   this.initialAmount !== this.originalData.initialAmount ||
                   this.observations !== this.originalData.observations;
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
            
            // Verificar si hay cambios
            if (!this.hasChanges()) {
                this.showNoChangesMessage();
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
         * Mostrar mensaje cuando no hay cambios
         */
        showNoChangesMessage() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin cambios',
                    text: 'No se han realizado cambios en el formulario.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#6b7280'
                });
            } else {
                alert('No se han realizado cambios en el formulario.');
            }
        },

        /**
         * Mostrar estado de carga
         */
        showLoadingState() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Guardando cambios...',
                    text: 'Por favor espera mientras se actualiza la información.',
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
         * Resetear formulario a datos originales
         */
        resetForm() {
            this.openingDate = this.originalData.openingDate;
            this.openingTime = this.originalData.openingTime;
            this.initialAmount = this.originalData.initialAmount;
            this.observations = this.originalData.observations;
            this.clearErrors();
        },

        /**
         * Confirmar reset del formulario
         */
        confirmReset() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'question',
                    title: '¿Restablecer formulario?',
                    text: 'Se perderán todos los cambios no guardados.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, restablecer',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.resetForm();
                        this.showSuccess('Formulario restablecido correctamente.');
                    }
                });
            } else {
                if (confirm('¿Restablecer formulario? Se perderán todos los cambios no guardados.')) {
                    this.resetForm();
                    alert('Formulario restablecido correctamente.');
                }
            }
        }
    }
}

// ===== FUNCIONES DE UTILIDAD =====

/**
 * Formatear moneda
 */
function formatCurrency(amount) {
    if (amount === null || amount === undefined || amount === '') {
        return CASH_COUNT_EDIT_CONFIG.currencySymbol + ' 0.00';
    }
    const num = parseFloat(amount);
    if (isNaN(num)) {
        return CASH_COUNT_EDIT_CONFIG.currencySymbol + ' 0.00';
    }
    return CASH_COUNT_EDIT_CONFIG.currencySymbol + ' ' + num.toFixed(2);
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

/**
 * Formatear número con separadores de miles
 */
function formatNumber(number) {
    return new Intl.NumberFormat('es-ES').format(number);
}

// ===== FUNCIONES DEL RESUMEN DE MOVIMIENTOS =====

/**
 * Actualizar resumen de movimientos
 */
function updateMovementsSummary() {
    // Esta función podría ser usada para actualizar dinámicamente
    // el resumen de movimientos si fuera necesario
    console.log('Actualizando resumen de movimientos...');
}

/**
 * Animar tarjetas de resumen
 */
function animateSummaryCards() {
    const cards = document.querySelectorAll('.summary-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('animate-pulse');
        setTimeout(() => {
            card.classList.remove('animate-pulse');
        }, 1000);
    });
}

// ===== INICIALIZACIÓN =====

/**
 * Inicializar la aplicación cuando el DOM esté listo
 */
function initializeApp() {
    console.log('🚀 Inicializando aplicación cash-counts/edit...');
    
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
    
    // Animar tarjetas de resumen
    setTimeout(() => {
        animateSummaryCards();
    }, 500);
    
    console.log('🎉 Aplicación cash-counts/edit inicializada correctamente');
}

// Hacer funciones disponibles globalmente
window.cashCountEdit = {
    initializeApp,
    formatCurrency,
    isValidDate,
    isValidTime,
    formatNumber,
    updateMovementsSummary,
    animateSummaryCards
};

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}
