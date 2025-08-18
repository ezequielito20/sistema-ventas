/**
 * JavaScript optimizado para cash-counts/create-movement
 * Archivo: public/js/admin/cash-counts/create-movement.js
 * Versión: 1.0.0
 * Descripción: Funciones específicas para el formulario de creación de movimientos de caja
 */

// Script de prueba para verificar carga
console.log('✅ cash-counts/create-movement.js cargado correctamente');
console.log('SweetAlert2 disponible:', typeof Swal !== 'undefined');

// ===== CONFIGURACIÓN GLOBAL =====
const MOVEMENT_CREATE_CONFIG = {
    currencySymbol: window.movementCreateData?.currencySymbol || '$',
    maxDescriptionLength: 255,
    maxAmount: 999999.99,
    minAmount: 0.01
};

// ===== FUNCIÓN ALPINE.JS =====

/**
 * Función Alpine.js para el formulario de creación de movimientos
 */
window.movementForm = function() {
    return {
        type: window.movementCreateData?.type || '',
        amount: window.movementCreateData?.amount || '',
        description: window.movementCreateData?.description || '',
        errors: {},
        isSubmitting: false,
        charCount: 0,

        /**
         * Inicializar el formulario
         */
        init() {
            this.updateCharCount();
            this.setupEventListeners();
        },

        /**
         * Configurar event listeners
         */
        setupEventListeners() {
            // Contador de caracteres para la descripción
            this.$watch('description', (value) => {
                this.updateCharCount();
            });

            // Formateo automático del monto
            this.$watch('amount', (value) => {
                this.formatAmount();
            });
        },

        /**
         * Actualizar contador de caracteres
         */
        updateCharCount() {
            this.charCount = this.description.length;
        },

        /**
         * Formatear monto
         */
        formatAmount() {
            if (!this.amount) return;

            // Remover caracteres no numéricos excepto punto decimal
            let value = this.amount.toString().replace(/[^\d.]/g, '');

            // Asegurar que solo haya un punto decimal
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            // Limitar a 2 decimales
            if (parts.length === 2 && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].substring(0, 2);
            }

            this.amount = value;
        },

        /**
         * Validar formulario
         */
        validate() {
            this.errors = {};

            // Validar tipo de movimiento
            if (!this.type) {
                this.errors.type = 'Debes seleccionar un tipo de movimiento';
            }

            // Validar monto
            if (!this.amount) {
                this.errors.amount = 'El monto es obligatorio';
            } else {
                const amount = parseFloat(this.amount);
                if (isNaN(amount)) {
                    this.errors.amount = 'Monto inválido';
                } else if (amount < MOVEMENT_CREATE_CONFIG.minAmount) {
                    this.errors.amount = `El monto mínimo es ${MOVEMENT_CREATE_CONFIG.currencySymbol} ${MOVEMENT_CREATE_CONFIG.minAmount}`;
                } else if (amount > MOVEMENT_CREATE_CONFIG.maxAmount) {
                    this.errors.amount = `El monto máximo es ${MOVEMENT_CREATE_CONFIG.currencySymbol} ${MOVEMENT_CREATE_CONFIG.maxAmount.toLocaleString()}`;
                }
            }

            // Validar descripción (opcional pero con límite)
            if (this.description && this.description.length > MOVEMENT_CREATE_CONFIG.maxDescriptionLength) {
                this.errors.description = `La descripción no puede exceder ${MOVEMENT_CREATE_CONFIG.maxDescriptionLength} caracteres`;
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
                    title: 'Registrando movimiento...',
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
            this.type = '';
            this.amount = '';
            this.description = '';
            this.clearErrors();
            this.updateCharCount();
        },

        /**
         * Confirmar reset del formulario
         */
        confirmReset() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'question',
                    title: '¿Limpiar formulario?',
                    text: 'Se perderán todos los datos ingresados.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, limpiar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.resetForm();
                        this.showSuccess('Formulario limpiado correctamente.');
                    }
                });
            } else {
                if (confirm('¿Limpiar formulario? Se perderán todos los datos ingresados.')) {
                    this.resetForm();
                    alert('Formulario limpiado correctamente.');
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
        return MOVEMENT_CREATE_CONFIG.currencySymbol + ' 0.00';
    }
    const num = parseFloat(amount);
    if (isNaN(num)) {
        return MOVEMENT_CREATE_CONFIG.currencySymbol + ' 0.00';
    }
    return MOVEMENT_CREATE_CONFIG.currencySymbol + ' ' + num.toFixed(2);
}

/**
 * Validar formato de monto
 */
function isValidAmount(amount) {
    const num = parseFloat(amount);
    return !isNaN(num) && num >= MOVEMENT_CREATE_CONFIG.minAmount && num <= MOVEMENT_CREATE_CONFIG.maxAmount;
}

/**
 * Formatear número con separadores de miles
 */
function formatNumber(number) {
    return new Intl.NumberFormat('es-ES').format(number);
}

/**
 * Obtener clase CSS para el contador de caracteres
 */
function getCharCountClass(count, maxLength) {
    if (count >= maxLength) {
        return 'at-limit';
    } else if (count >= maxLength * 0.8) {
        return 'near-limit';
    }
    return '';
}

// ===== FUNCIONES PARA RADIO BUTTONS =====

/**
 * Manejar cambio en radio buttons
 */
function handleRadioChange(event) {
    const radioButtons = document.querySelectorAll('input[name="type"]');
    const containers = document.querySelectorAll('.radio-content');

    // Remover estilos activos de todos los contenedores
    containers.forEach(container => {
        container.classList.remove('income', 'expense', 'border-blue-500', 'bg-blue-50', 'border-red-500', 'bg-red-50');
        container.classList.add('border-gray-200');
    });

    // Aplicar estilos al seleccionado
    if (event.target.checked) {
        const container = event.target.closest('.radio-option').querySelector('.radio-content');
        container.classList.remove('border-gray-200');
        
        if (event.target.value === 'income') {
            container.classList.add('income', 'border-blue-500', 'bg-blue-50');
        } else if (event.target.value === 'expense') {
            container.classList.add('expense', 'border-red-500', 'bg-red-50');
        }
    }
}

/**
 * Inicializar radio buttons
 */
function initializeRadioButtons() {
    const radioButtons = document.querySelectorAll('input[name="type"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', handleRadioChange);
    });
}

// ===== FUNCIONES PARA EL CAMPO DE MONTO =====

/**
 * Manejar input del campo de monto
 */
function handleAmountInput(event) {
    const input = event.target;
    let value = input.value.replace(/[^\d.]/g, '');

    // Asegurar que solo haya un punto decimal
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }

    // Limitar a 2 decimales
    if (parts.length === 2 && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }

    input.value = value;

    // Agregar efecto visual cuando hay valor
    if (value && parseFloat(value) > 0) {
        input.classList.add('has-value');
    } else {
        input.classList.remove('has-value');
    }
}

/**
 * Inicializar campo de monto
 */
function initializeAmountField() {
    const amountInput = document.getElementById('amount');
    if (amountInput) {
        amountInput.addEventListener('input', handleAmountInput);
        
        // Efecto de focus mejorado
        amountInput.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        amountInput.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    }
}

// ===== INICIALIZACIÓN =====

/**
 * Inicializar la aplicación cuando el DOM esté listo
 */
function initializeApp() {
    console.log('🚀 Inicializando aplicación cash-counts/create-movement...');

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

    // Inicializar componentes
    initializeRadioButtons();
    initializeAmountField();

    console.log('🎉 Aplicación cash-counts/create-movement inicializada correctamente');
}

// Hacer funciones disponibles globalmente
window.movementCreate = {
    initializeApp,
    formatCurrency,
    isValidAmount,
    formatNumber,
    getCharCountClass,
    handleRadioChange,
    handleAmountInput
};

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}
