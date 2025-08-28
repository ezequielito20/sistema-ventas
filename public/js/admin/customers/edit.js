// ===== CONFIGURACIÓN GLOBAL =====
const CUSTOMER_EDIT_CONFIG = {
    routes: {
        update: '/customers/edit',
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
        },
        total_debt: {
            minValue: 0
        }
    }
};

// ===== FUNCIÓN PRINCIPAL DE ALPINE.JS =====
function customerEditForm() {
    return {
        form: {
            name: '',
            nit_number: '',
            phone: '',
            email: '',
            total_debt: ''
        },
        errors: {},
        isSubmitting: false,
        debtEditable: false,

        init() {
            // Inicializar valores desde los datos del cliente
            this.initializeFormData();

            // Formatear teléfono al cargar
            if (this.form.phone) {
                this.formatPhone();
            }

            // Capitalizar automáticamente el nombre
            this.setupNameCapitalization();

            // Validar campos iniciales
            this.validateAllFields();
        },

        // ===== MÉTODOS DE INICIALIZACIÓN =====

        initializeFormData() {
            // Obtener datos del cliente desde window.customerData
            const customerData = window.customerData || {};
            this.form = {
                name: customerData.name || '',
                nit_number: customerData.nit_number || '',
                phone: customerData.phone || '',
                email: customerData.email || '',
                total_debt: customerData.total_debt || '0.00'
            };
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

                case 'total_debt':
                    this.validateTotalDebt(value);
                    break;
            }
        },

        validateName(value) {
            if (!value) {
                this.errors.name = 'El nombre es requerido';
            } else if (value.length < CUSTOMER_EDIT_CONFIG.validation.name.minLength) {
                this.errors.name = `El nombre debe tener al menos ${CUSTOMER_EDIT_CONFIG.validation.name.minLength} caracteres`;
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
                if (cleanPhone.length < CUSTOMER_EDIT_CONFIG.validation.phone.minLength) {
                    this.errors.phone = `El teléfono debe tener al menos ${CUSTOMER_EDIT_CONFIG.validation.phone.minLength} dígitos`;
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
            if (value && !CUSTOMER_EDIT_CONFIG.validation.email.pattern.test(value)) {
                this.errors.email = 'Ingrese un email válido';
            } else {
                delete this.errors.email;
            }
        },

        validateTotalDebt(value) {
            if (value && (isNaN(value) || parseFloat(value) < CUSTOMER_EDIT_CONFIG.validation.total_debt.minValue)) {
                this.errors.total_debt = 'La deuda debe ser un número válido mayor o igual a 0';
            } else {
                delete this.errors.total_debt;
            }
        },

        validateAllFields() {
            ['name', 'nit_number', 'phone', 'email', 'total_debt'].forEach(field => {
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

        // Método para manejar la entrada de teléfono en tiempo real
        handlePhoneInput(event) {
            let value = event.target.value;
            const cursorPosition = event.target.selectionStart;
            const oldValue = this.form.phone;
            
            // Obtener solo los dígitos
            const digitsOnly = value.replace(/\D/g, '');
            
            // Limitar a 10 dígitos
            if (digitsOnly.length > 10) {
                return; // No permitir más de 10 dígitos
            }
            
            // Aplicar formato según la cantidad de dígitos
            let formattedValue = '';
            if (digitsOnly.length === 0) {
                formattedValue = '';
            } else if (digitsOnly.length >= 6) {
                formattedValue = digitsOnly.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            } else if (digitsOnly.length >= 3) {
                formattedValue = digitsOnly.replace(/(\d{3})(\d{0,3})/, '($1) $2');
            } else {
                formattedValue = digitsOnly;
            }
            
            // Actualizar el valor
            this.form.phone = formattedValue;
            
            // Restaurar la posición del cursor después del formato
            this.$nextTick(() => {
                let newCursorPosition;
                
                // Si se está borrando (el valor anterior es más largo), colocar al final
                if (oldValue.length > formattedValue.length) {
                    newCursorPosition = formattedValue.length;
                } else if (oldValue.length < formattedValue.length) {
                    // Si se está agregando (el valor nuevo es más largo), colocar al final
                    newCursorPosition = formattedValue.length;
                } else {
                    // Si no hay cambio en la longitud, mantener la posición actual
                    newCursorPosition = Math.min(cursorPosition, formattedValue.length);
                }
                
                event.target.setSelectionRange(newCursorPosition, newCursorPosition);
            });
        },

        // Calcular la nueva posición del cursor después del formato
        calculateCursorPosition(oldPosition, oldValue, newValue) {
            // Obtener solo los dígitos hasta la posición del cursor en el valor anterior
            const oldDigitsBeforeCursor = oldValue.substring(0, oldPosition).replace(/\D/g, '');
            const newDigits = newValue.replace(/\D/g, '');
            
            // Si se eliminaron dígitos (borrado), colocar el cursor al final
            if (oldDigitsBeforeCursor.length > newDigits.length) {
                return newValue.length;
            }
            
            // Si se agregaron dígitos, calcular la nueva posición
            if (oldDigitsBeforeCursor.length < newDigits.length) {
                // Contar caracteres no-dígitos antes del cursor en el valor anterior
                let nonDigitsBefore = 0;
                for (let i = 0; i < oldPosition; i++) {
                    if (/\D/.test(oldValue[i])) {
                        nonDigitsBefore++;
                    }
                }
                
                // Calcular la nueva posición basada en los dígitos
                let newPosition = oldDigitsBeforeCursor.length;
                let digitCount = 0;
                
                for (let i = 0; i < newValue.length; i++) {
                    if (/\d/.test(newValue[i])) {
                        if (digitCount === newPosition) {
                            return i + 1; // Posición después del dígito
                        }
                        digitCount++;
                    }
                }
                
                // Si no se encontró la posición exacta, ir al final
                return newValue.length;
            }
            
            // Si no hay cambios en la cantidad de dígitos, mantener la posición relativa
            return Math.min(oldPosition, newValue.length);
        },

        // ===== MÉTODOS DE GESTIÓN DE DEUDA =====

        async toggleDebtEdit() {
            if (!this.debtEditable) {
                // Mostrar confirmación para habilitar edición
                const result = await this.showConfirmAlert(
                    '¿Habilitar edición de deuda?',
                    'La deuda se calcula automáticamente según las ventas y pagos. ¿Está seguro de que desea editarla manualmente?',
                    'warning'
                );

                if (result.isConfirmed) {
                    this.debtEditable = true;
                    this.focusDebtField();
                    this.showAlert('Campo habilitado. Ahora puede editar la deuda manualmente.', 'success');
                }
            } else {
                // Deshabilitar edición
                this.debtEditable = false;
                this.blurDebtField();
                this.showAlert('Campo deshabilitado. La deuda volverá a calcularse automáticamente.', 'info');
            }
        },

        focusDebtField() {
            // Usar requestAnimationFrame para evitar conflictos de accesibilidad
            requestAnimationFrame(() => {
                const debtInput = document.getElementById('total_debt');
                if (debtInput) {
                    // Remover temporalmente cualquier aria-hidden del ancestro
                    const ancestors = this.getAncestorsWithAriaHidden(debtInput);
                    ancestors.forEach(el => {
                        el.setAttribute('data-aria-hidden-backup', el.getAttribute('aria-hidden'));
                        el.removeAttribute('aria-hidden');
                    });
                    
                    // Enfocar y seleccionar
                    debtInput.focus();
                    debtInput.select();
                    
                    // Restaurar aria-hidden después de un breve delay
                    setTimeout(() => {
                        ancestors.forEach(el => {
                            const backup = el.getAttribute('data-aria-hidden-backup');
                            if (backup) {
                                el.setAttribute('aria-hidden', backup);
                                el.removeAttribute('data-aria-hidden-backup');
                            }
                        });
                    }, 500);
                }
            });
        },

        blurDebtField() {
            // Remover el foco del campo al deshabilitarlo
            const debtInput = document.getElementById('total_debt');
            if (debtInput) {
                debtInput.blur();
            }
        },

        getAncestorsWithAriaHidden(element) {
            const ancestors = [];
            let current = element.parentElement;
            
            while (current) {
                if (current.hasAttribute('aria-hidden')) {
                    ancestors.push(current);
                }
                current = current.parentElement;
            }
            
            return ancestors;
        },

        get isFormValid() {
            return !this.errors.name && this.form.name;
        },

        // ===== MÉTODOS DE ENVÍO =====

        async submitForm() {
            // Validar todos los campos
            this.validateAllFields();
            
            if (!this.isFormValid) {
                this.showAlert('Por favor, complete todos los campos requeridos', 'error');
                return;
            }

            this.isSubmitting = true;

            try {
                const formData = this.prepareFormData();
                const response = await this.sendFormData(formData);
                await this.handleResponse(response);
            } catch (error) {
                console.error('Error:', error);
                this.showAlert('Error de conexión. Intente nuevamente.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        prepareFormData() {
            const formData = new FormData();
            const csrfToken = this.getCsrfToken();
            
            // Verificar que tenemos el token CSRF
            if (!csrfToken) {
                console.error('Token CSRF no encontrado');
                throw new Error('Token CSRF no encontrado');
            }
            
            formData.append('_token', csrfToken);
            formData.append('_method', 'PUT');
            formData.append('name', this.form.name);
            formData.append('nit_number', this.form.nit_number);
            formData.append('phone', this.form.phone.replace(/\D/g, '')); // Guardar solo números
            formData.append('email', this.form.email);
            formData.append('total_debt', this.form.total_debt);

            return formData;
        },

        async sendFormData(formData) {
            const customerId = window.customerData?.id;
            const updateUrl = `/customers/edit/${customerId}`;
            const csrfToken = this.getCsrfToken();
            
            const response = await fetch(updateUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            return await response.json();
        },

        async handleResponse(result) {
            if (result.success) {
                this.showAlert(result.message || 'Cliente actualizado exitosamente', 'success');
                
                // Redirigir a la lista de clientes después de un breve delay
                setTimeout(() => {
                    window.location.href = '/customers';
                }, 1500);
            } else {
                // Manejar errores de validación del servidor
                if (result.errors) {
                    this.errors = result.errors;
                    this.showAlert('Por favor, corrija los errores en el formulario', 'error');
                } else {
                    this.showAlert(result.message || 'Error al actualizar el cliente', 'error');
                }
            }
        },

        // ===== MÉTODOS DE UTILIDAD =====

        getCsrfToken() {
            // Intentar obtener el token del meta tag primero
            const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (metaToken) {
                return metaToken;
            }
            
            // Fallback: obtener del input hidden del formulario
            const inputToken = document.querySelector('input[name="_token"]')?.value;
            if (inputToken) {
                return inputToken;
            }
            
            // Fallback: buscar en cualquier input hidden con _token
            const anyToken = document.querySelector('input[name="_token"]')?.value;
            if (anyToken) {
                return anyToken;
            }
            
            console.error('No se pudo encontrar el token CSRF');
            return '';
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
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, continuar',
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

// Función para validar número de deuda
function validateDebtAmount(amount) {
    const num = parseFloat(amount);
    return !isNaN(num) && num >= 0;
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

// ===== EXPONER FUNCIONES GLOBALMENTE =====
window.customerEditForm = customerEditForm;
window.showNotification = showNotification;
window.validateEmail = validateEmail;
window.formatPhoneNumber = formatPhoneNumber;
window.capitalizeText = capitalizeText;
window.validateDebtAmount = validateDebtAmount;
window.formatCurrency = formatCurrency;
window.CUSTOMER_EDIT_CONFIG = CUSTOMER_EDIT_CONFIG;
