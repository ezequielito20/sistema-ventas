function supplierEditForm() {
    const root = document.getElementById('supplierEditRoot');
    return {
        currentStep: 1,
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

        get canProceedToStep2() {
            return this.formData.company_name.trim() &&
                   this.formData.company_email.trim() &&
                   this.formData.company_phone.trim() &&
                   this.formData.company_address.trim() &&
                   !this.errors.company_name &&
                   !this.errors.company_email &&
                   !this.errors.company_phone &&
                   !this.errors.company_address;
        },

        get canProceedToStep3() {
            return this.formData.supplier_name.trim() &&
                   this.formData.supplier_phone.trim() &&
                   !this.errors.supplier_name &&
                   !this.errors.supplier_phone;
        },

        nextStep() { if (this.currentStep < 3) this.currentStep++; },
        prevStep() { if (this.currentStep > 1) this.currentStep--; },

        validateField(fieldName) {
            this.errors[fieldName] = '';
            const value = (this.formData[fieldName] || '').trim();
            switch (fieldName) {
                case 'company_name':
                case 'supplier_name':
                    if (!value) this.errors[fieldName] = 'Este campo es requerido';
                    else if (value.length < 2) this.errors[fieldName] = 'Debe tener al menos 2 caracteres';
                    break;
                case 'company_email':
                    if (!value) this.errors[fieldName] = 'Este campo es requerido';
                    else if (!this.isValidEmail(value)) this.errors[fieldName] = 'Ingrese un email válido';
                    break;
                case 'company_phone':
                case 'supplier_phone':
                    if (!value) this.errors[fieldName] = 'Este campo es requerido';
                    else if (!this.isValidPhone(value)) this.errors[fieldName] = 'Formato: (123) 456-7890';
                    break;
                case 'company_address':
                    if (!value) this.errors[fieldName] = 'Este campo es requerido';
                    else if (value.length < 10) this.errors[fieldName] = 'Debe tener al menos 10 caracteres';
                    break;
            }
        },

        isValidEmail(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); },
        isValidPhone(phone) { return /^\(\d{3}\)\s\d{3}-\d{4}$/.test(phone); },

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

        async submitForm() {
            Object.keys(this.formData).forEach(f => this.validateField(f));
            if (Object.values(this.errors).some(Boolean)) {
                this.showNotification('Por favor, corrija los errores en el formulario', 'error');
                return;
            }
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
                    setTimeout(() => { window.location.href = '/suppliers'; }, 1500);
                } else {
                    if (result.errors) {
                        Object.keys(result.errors).forEach(f => { this.errors[f] = result.errors[f][0]; });
                        this.showNotification('Por favor, corrija los errores en el formulario', 'error');
                    } else {
                        this.showNotification(result.message || 'Error al actualizar el proveedor', 'error');
                    }
                }
            } catch (e) {
                console.error(e);
                this.showNotification('Error de conexión. Por favor, inténtelo de nuevo.', 'error');
            } finally { this.isSubmitting = false; }
        },

        async showConfirmDialog(title, message) {
            if (typeof Swal !== 'undefined') {
                const r = await Swal.fire({ title, text: message, icon: 'question', showCancelButton: true, confirmButtonColor: '#667eea', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, actualizar', cancelButtonText: 'Cancelar' });
                return r.isConfirmed;
            }
            return confirm(`${title}\n${message}`);
        },

        showNotification(message, type='info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: type==='success' ? '¡Éxito!' : 'Error', text: message, icon: type, confirmButtonText: 'Entendido', confirmButtonColor: type==='success' ? '#22c55e' : '#ef4444' });
            } else { alert(message); }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
});
