// ===== FUNCIÓN PRINCIPAL DE ALPINE.JS =====
function supplierForm() {
    return {
        isSubmitting: false,
        formData: {
            company_name: '',
            company_email: '',
            company_phone: '',
            company_address: '',
            supplier_name: '',
            supplier_phone: ''
        },
        errors: {},

        // ===== COMPUTED PROPERTIES =====
        get isFormValid() {
            return this.formData.company_name.trim() && 
                   this.formData.company_email.trim() && 
                   this.formData.company_phone.trim() && 
                   this.formData.company_address.trim() &&
                   this.formData.supplier_name.trim() && 
                   this.formData.supplier_phone.trim() &&
                   !this.errors.company_name && 
                   !this.errors.company_email && 
                   !this.errors.company_phone && 
                   !this.errors.company_address &&
                   !this.errors.supplier_name && 
                   !this.errors.supplier_phone;
        },

        // ===== MÉTODOS DE FORMULARIO =====
        resetForm() {
            this.formData = {
                company_name: '',
                company_email: '',
                company_phone: '',
                company_address: '',
                supplier_name: '',
                supplier_phone: ''
            };
            this.errors = {};
            this.showNotification('Formulario limpiado', 'info');
        },

        // ===== VALIDACIÓN DE CAMPOS =====
        validateField(fieldName) {
            this.errors[fieldName] = '';
            
            const value = this.formData[fieldName].trim();
            
            switch (fieldName) {
                case 'company_name':
                case 'supplier_name':
                    if (!value) {
                        this.errors[fieldName] = 'Este campo es requerido';
                    } else if (value.length < 2) {
                        this.errors[fieldName] = 'Debe tener al menos 2 caracteres';
                    } else if (value.length > 100) {
                        this.errors[fieldName] = 'No puede exceder los 100 caracteres';
                    }
                    break;
                    
                case 'company_email':
                    if (!value) {
                        this.errors[fieldName] = 'Este campo es requerido';
                    } else if (!this.isValidEmail(value)) {
                        this.errors[fieldName] = 'Ingrese un email válido';
                    } else if (value.length > 255) {
                        this.errors[fieldName] = 'El email es demasiado largo';
                    }
                    break;
                    
                case 'company_phone':
                case 'supplier_phone':
                    if (!value) {
                        this.errors[fieldName] = 'Este campo es requerido';
                    } else if (!this.isValidPhone(value)) {
                        this.errors[fieldName] = 'Formato: (123) 456-7890';
                    }
                    break;
                    
                case 'company_address':
                    if (!value) {
                        this.errors[fieldName] = 'Este campo es requerido';
                    } else if (value.length < 10) {
                        this.errors[fieldName] = 'Debe tener al menos 10 caracteres';
                    } else if (value.length > 500) {
                        this.errors[fieldName] = 'No puede exceder los 500 caracteres';
                    }
                    break;
            }
        },

        // ===== VALIDACIONES ESPECÍFICAS =====
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        isValidPhone(phone) {
            const phoneRegex = /^\(\d{3}\)\s\d{3}-\d{4}$/;
            return phoneRegex.test(phone);
        },

        // ===== FORMATEO DE TELÉFONO =====
        formatPhone(input, fieldName) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length >= 10) {
                value = value.substring(0, 10);
                const formatted = `(${value.substring(0, 3)}) ${value.substring(3, 6)}-${value.substring(6)}`;
                this.formData[fieldName] = formatted;
            } else {
                this.formData[fieldName] = value;
            }
            
            this.validateField(fieldName);
        },

        // ===== ENVÍO DEL FORMULARIO =====
        async submitForm() {
            // Validar todos los campos
            Object.keys(this.formData).forEach(field => {
                this.validateField(field);
            });

            // Verificar si hay errores
            if (Object.values(this.errors).some(error => error)) {
                this.showNotification('Por favor, corrija los errores en el formulario', 'error');
                return;
            }

            this.isSubmitting = true;

            try {
                const formData = new FormData(this.$refs.form);
                const response = await fetch(this.$refs.form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    this.showNotification('Proveedor creado exitosamente', 'success');
                    setTimeout(() => {
                        window.location.href = '/suppliers';
                    }, 1500);
                } else {
                    // Manejar errores de validación del servidor
                    if (result.errors) {
                        // Mostrar errores específicos del servidor
                        Object.keys(result.errors).forEach(field => {
                            this.errors[field] = result.errors[field][0];
                        });
                        this.showNotification('Por favor, corrija los errores en el formulario', 'error');
                    } else {
                        this.showNotification(result.message || 'Error al crear el proveedor', 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                this.showNotification('Error de conexión. Por favor, inténtelo de nuevo.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        // ===== NOTIFICACIONES =====
        showNotification(message, type = 'info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? '¡Éxito!' : type === 'error' ? 'Error' : 'Información',
                    text: message,
                    icon: type,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : '#3b82f6',
                    background: 'rgba(255, 255, 255, 0.95)',
                    backdrop: 'rgba(0, 0, 0, 0.5)',
                    customClass: {
                        popup: 'modern-swal-popup',
                        title: 'modern-swal-title',
                        content: 'modern-swal-content',
                        confirmButton: 'modern-swal-button'
                    }
                });
            } else {
                // Fallback para cuando SweetAlert no está disponible
                alert(message);
            }
        },

        // ===== INICIALIZACIÓN =====
        init() {
            // Configurar validación en tiempo real
            this.$watch('formData', (value) => {
                // Validar campos cuando cambian
                Object.keys(value).forEach(field => {
                    if (value[field]) {
                        this.validateField(field);
                    }
                });
            }, { deep: true });
        }
    };
}

// ===== INICIALIZACIÓN GLOBAL =====
document.addEventListener('DOMContentLoaded', function() {
    
    // Verificar que Alpine.js esté disponible
    if (typeof Alpine !== 'undefined') {
    }
});

// ===== UTILIDADES GLOBALES =====
window.supplierFormUtils = {
    // Función para limpiar el formulario
    clearForm() {
        const form = document.querySelector('form');
        if (form) {
            form.reset();
        }
    },

    // Función para validar email
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Función para formatear teléfono
    formatPhoneNumber(value) {
        const cleaned = value.replace(/\D/g, '');
        if (cleaned.length >= 10) {
            return `(${cleaned.substring(0, 3)}) ${cleaned.substring(3, 6)}-${cleaned.substring(6, 10)}`;
        }
        return cleaned;
    }
};
