function supplierEditForm() {
    const root = document.getElementById('supplierEditRoot');
    return {
        isSubmitting: false,
        formData: {
            company_name: root?.dataset.companyName || '',
            company_email: root?.dataset.companyEmail || '',
            company_phone: root?.dataset.companyPhone || '',
            company_address: root?.dataset.companyAddress || '',
            supplier_name: root?.dataset.supplierName || '',
            supplier_phone: root?.dataset.supplierPhone || ''
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
                company_name: root?.dataset.companyName || '',
                company_email: root?.dataset.companyEmail || '',
                company_phone: root?.dataset.companyPhone || '',
                company_address: root?.dataset.companyAddress || '',
                supplier_name: root?.dataset.supplierName || '',
                supplier_phone: root?.dataset.supplierPhone || ''
            };
            this.errors = {};
            this.showNotification('Formulario restaurado a los valores originales', 'info');
        },

        // ===== VALIDACIÓN DE CAMPOS =====
        validateField(fieldName) {
            this.errors[fieldName] = '';
            const value = (this.formData[fieldName] || '').trim();
            
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
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); 
        },
        
        isValidPhone(phone) { 
            return /^\(\d{3}\)\s\d{3}-\d{4}$/.test(phone); 
        },

        // ===== FORMATEO DE TELÉFONO =====
        formatPhone(input, fieldName) {
            let value = (input.value || '').replace(/\D/g, '');
            if (value.length >= 10) {
                value = value.substring(0, 10);
                this.formData[fieldName] = `(${value.substring(0,3)}) ${value.substring(3,6)}-${value.substring(6)}`;
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

            // Confirmar actualización
            const confirmed = await this.showConfirmDialog('¿Confirmar actualización?', '¿Está seguro de que desea actualizar la información del proveedor?');
            if (!confirmed) return;

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
                    this.showNotification('Proveedor actualizado exitosamente', 'success');
                    setTimeout(() => { 
                        window.location.href = '/suppliers'; 
                    }, 1500);
                } else {
                    // Manejar errores de validación del servidor
                    if (result.errors) {
                        Object.keys(result.errors).forEach(field => {
                            this.errors[field] = result.errors[field][0];
                        });
                        this.showNotification('Por favor, corrija los errores en el formulario', 'error');
                    } else {
                        this.showNotification(result.message || 'Error al actualizar el proveedor', 'error');
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
                alert(message);
            }
        },

        // ===== DIÁLOGO DE CONFIRMACIÓN =====
        async showConfirmDialog(title, text) {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: title,
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, actualizar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#6b7280',
                    background: 'rgba(255, 255, 255, 0.95)',
                    backdrop: 'rgba(0, 0, 0, 0.5)',
                    customClass: {
                        popup: 'modern-swal-popup',
                        title: 'modern-swal-title',
                        content: 'modern-swal-content',
                        confirmButton: 'modern-swal-button',
                        cancelButton: 'modern-swal-button'
                    }
                });
                return result.isConfirmed;
            } else {
                return confirm(text);
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

document.addEventListener('DOMContentLoaded', () => {
});
