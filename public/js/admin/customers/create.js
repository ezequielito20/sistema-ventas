// ===== CONFIGURACIÓN GLOBAL =====
const CUSTOMER_CREATE_CONFIG = {
    routes: {
        store: '/customers/create',
        index: '/customers'
    },
    validation: {
        name: {
            minLength: 2,
            required: true
        },
        nit_number: {
            minLength: 7,
            maxLength: 11
        },
        phone: {
            minLength: 10,
            maxLength: 10
        },
        email: {
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        }
    }
};

// ===== FUNCIÓN PRINCIPAL DE ALPINE.JS =====
function customerForm() {
    return {
        form: {
            name: '',
            nit_number: '',
            phone: '',
            email: ''
        },
        errors: {},
        isSubmitting: false,
        action: 'save',

        init() {
            // Inicializar valores desde old() de Laravel si existen
            this.form.name = this.getOldValue('name');
            this.form.nit_number = this.getOldValue('nit_number');
            this.form.phone = this.getOldValue('phone');
            this.form.email = this.getOldValue('email');

            // Guardar la URL original cuando se carga la página por primera vez
            this.saveOriginalReferrer();

            // Capitalizar automáticamente el nombre
            this.setupNameCapitalization();

            // Validar campos iniciales
            this.validateAllFields();
        },

        // ===== MÉTODOS DE INICIALIZACIÓN =====

        getOldValue(field) {
            // Obtener valores de old() de Laravel
            const oldValues = window.oldValues || {};
            return oldValues[field] || '';
        },

        saveOriginalReferrer() {
            if (!sessionStorage.getItem('customers_original_referrer')) {
                const referrer = document.referrer;
                if (referrer && !referrer.includes('/customers/create')) {
                    sessionStorage.setItem('customers_original_referrer', referrer);
                }
            }
        },

        setupNameCapitalization() {
            this.$watch('form.name', (value) => {
                if (value) {
                    let words = value.split(' ');
                    words = words.map(word => {
                        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
                    });
                    this.form.name = words.join(' ');
                }
            });
        },

        // ===== MÉTODOS DE VALIDACIÓN =====

        validateField(fieldName) {
            const value = this.form[fieldName];
            
            switch(fieldName) {
                case 'name':
                    this.validateName(value);
                    break;

                case 'nit_number':
                    this.validateNitNumber(value);
                    break;

                case 'phone':
                    this.validatePhone(value);
                    break;

                case 'email':
                    this.validateEmail(value);
                    break;
            }
        },

        validateName(value) {
            if (!value) {
                this.errors.name = 'El nombre es requerido';
            } else if (value.length < CUSTOMER_CREATE_CONFIG.validation.name.minLength) {
                this.errors.name = `El nombre debe tener al menos ${CUSTOMER_CREATE_CONFIG.validation.name.minLength} caracteres`;
            } else {
                delete this.errors.name;
            }
        },

        validateNitNumber(value) {
            if (value && !/^\d{7,11}$/.test(value.replace(/\D/g, ''))) {
                this.errors.nit_number = 'La cédula debe tener entre 7 y 11 dígitos';
            } else {
                delete this.errors.nit_number;
            }
        },

        validatePhone(value) {
            if (value) {
                const cleanPhone = value.replace(/\D/g, '');
                if (cleanPhone.length < CUSTOMER_CREATE_CONFIG.validation.phone.minLength) {
                    this.errors.phone = `El teléfono debe tener al menos ${CUSTOMER_CREATE_CONFIG.validation.phone.minLength} dígitos`;
                } else if (cleanPhone.length > 10) {
                    this.errors.phone = 'El teléfono no puede tener más de 10 dígitos';
                } else {
                    delete this.errors.phone;
                }
            } else {
                delete this.errors.phone;
            }
        },

        validateEmail(value) {
            if (value && !CUSTOMER_CREATE_CONFIG.validation.email.pattern.test(value)) {
                this.errors.email = 'Ingrese un email válido';
            } else {
                delete this.errors.email;
            }
        },

        validateAllFields() {
            ['name', 'nit_number', 'phone', 'email'].forEach(field => {
                this.validateField(field);
            });
        },

        // ===== MÉTODOS DE UI =====

        getFieldClasses(fieldName) {
            const baseClasses = 'w-full px-4 py-3 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent';
            
            if (this.errors[fieldName]) {
                return baseClasses + ' border-red-300 bg-red-50';
            } else if (this.form[fieldName] && !this.errors[fieldName]) {
                return baseClasses + ' border-green-300 bg-green-50';
            } else {
                return baseClasses + ' border-gray-200';
            }
        },

        formatPhone() {
            let value = this.form.phone.replace(/\D/g, '');
            
            // Limitar a máximo 10 dígitos
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            
            // Solo formatear si hay suficientes dígitos para un formato completo
            if (value.length === 0) {
                this.form.phone = '';
            } else if (value.length >= 6) {
                this.form.phone = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                this.form.phone = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
            } else {
                // Para 1-2 dígitos, mantener sin formato para permitir edición libre
                this.form.phone = value;
            }
        },



        get isFormValid() {
            return !this.errors.name && this.form.name;
        },

        // ===== MÉTODOS DE ENVÍO =====

        async submitForm(action) {
            this.action = action;
            
            // Validar todos los campos
            this.validateAllFields();
            
            if (!this.isFormValid) {
                this.showAlert('Por favor, complete todos los campos requeridos', 'error');
                return;
            }

            this.isSubmitting = true;

            try {
                const formData = this.prepareFormData(action);
                const response = await this.sendFormData(formData);
                await this.handleResponse(response, action);
            } catch (error) {
                console.error('Error:', error);
                this.showAlert('Error de conexión. Intente nuevamente.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        prepareFormData(action) {
            const formData = new FormData();
            formData.append('_token', this.getCsrfToken());
            formData.append('name', this.form.name);
            formData.append('nit_number', this.form.nit_number);
            formData.append('phone', this.form.phone.replace(/\D/g, '')); // Guardar solo números
            formData.append('email', this.form.email);
            formData.append('action', action);

            // Agregar return_to si existe
            const returnTo = this.getReturnToValue();
            if (returnTo) {
                formData.append('return_to', returnTo);
            }

            return formData;
        },

        async sendFormData(formData) {
            const response = await fetch(CUSTOMER_CREATE_CONFIG.routes.store, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            return await response.json();
        },

        async handleResponse(result, action) {
            if (result.success) {
                this.showAlert(result.message || 'Cliente creado exitosamente', 'success');
                
                if (action === 'save_and_new') {
                    // Limpiar formulario y volver a crear
                    setTimeout(() => {
                        this.resetForm();
                    }, 1500);
                } else {
                    // Redirigir según el contexto
                    setTimeout(() => {
                        this.goBack();
                    }, 1500);
                }
            } else {
                // Manejar errores de validación del servidor
                if (result.errors) {
                    this.errors = result.errors;
                    this.showAlert('Por favor, corrija los errores en el formulario', 'error');
                } else {
                    this.showAlert(result.message || 'Error al crear el cliente', 'error');
                }
            }
        },

        // ===== MÉTODOS DE UTILIDAD =====

        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                   document.querySelector('input[name="_token"]')?.value || 
                   '';
        },

        getReturnToValue() {
            return document.querySelector('input[name="return_to"]')?.value || '';
        },

        resetForm() {
            this.form = {
                name: '',
                nit_number: '',
                phone: '',
                email: ''
            };
            this.errors = {};
            this.$nextTick(() => {
                document.getElementById('name').focus();
            });
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

        getAlertTitle(type) {
            switch(type) {
                case 'success': return '¡Éxito!';
                case 'error': return 'Error';
                case 'warning': return 'Advertencia';
                default: return 'Información';
            }
        },

        goBack() {
            // Verificar si hay una URL de referencia guardada en sessionStorage
            const originalReferrer = sessionStorage.getItem('customers_original_referrer');
            
            if (originalReferrer && originalReferrer !== window.location.href) {
                // Si tenemos una URL original guardada, ir allí
                window.location.href = originalReferrer;
            } else {
                // Comportamiento normal del botón volver
                window.history.back();
            }
        }
    }
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

// Función para validar email
function validateEmail(email) {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(email);
}

// Función para formatear teléfono
function formatPhoneNumber(phone) {
    let value = phone.replace(/\D/g, '');
    if (value.length >= 6) {
        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
    } else if (value.length >= 3) {
        value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
    }
    return value;
}

// Función para capitalizar texto
function capitalizeText(text) {
    if (!text) return '';
    let words = text.split(' ');
    words = words.map(word => {
        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
    });
    return words.join(' ');
}

// ===== EXPONER FUNCIONES GLOBALMENTE =====
window.customerForm = customerForm;
window.showNotification = showNotification;
window.validateEmail = validateEmail;
window.formatPhoneNumber = formatPhoneNumber;
window.capitalizeText = capitalizeText;
window.CUSTOMER_CREATE_CONFIG = CUSTOMER_CREATE_CONFIG;
