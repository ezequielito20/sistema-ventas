// ===== CONFIGURACIÓN GLOBAL =====
if (typeof PERMISSIONS_EDIT_CONFIG === 'undefined') {
    window.PERMISSIONS_EDIT_CONFIG = {
        routes: {
            update: '/admin/permissions',
            index: '/admin/permissions'
        },
        validation: {
            nameFormat: /^[a-z]+\.[a-z]+$/,
            minLength: 3
        }
    };
}

// ===== FUNCIONES GLOBALES =====
if (typeof window.permissionsEdit === 'undefined') {
    window.permissionsEdit = {
        // Función para formatear el nombre del permiso
        formatPermissionName: function(input) {
            const value = input.value;
            const formatted = value.toLowerCase().replace(/\s+/g, '.');
            input.value = formatted;
            
            // Validar formato
            this.validatePermissionName(input);
        },

        // Función para validar el nombre del permiso
        validatePermissionName: function(input) {
            const value = input.value;
            const isValid = PERMISSIONS_EDIT_CONFIG.validation.nameFormat.test(value);
            
            if (value.length > 0) {
                if (isValid) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                }
            } else {
                input.classList.remove('is-valid', 'is-invalid');
            }
        },

        // Función para manejar el envío del formulario
        handleFormSubmit: function(form) {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Deshabilitar botón y mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner loading-spinner mr-2"></i>Actualizando...';
            
            // Validar antes de enviar
            if (!this.validateForm(form)) {
                this.resetSubmitButton(submitBtn, originalText);
                return false;
            }
            
            return true;
        },

        // Función para validar el formulario completo
        validateForm: function(form) {
            const nameInput = form.querySelector('#name');
            let isValid = true;
            
            // Validar nombre
            if (!nameInput.value.trim()) {
                this.showFieldError(nameInput, 'El nombre del permiso es requerido');
                isValid = false;
            } else if (!PERMISSIONS_EDIT_CONFIG.validation.nameFormat.test(nameInput.value)) {
                this.showFieldError(nameInput, 'El formato debe ser: modulo.accion (ej: usuarios.crear)');
                isValid = false;
            } else {
                this.clearFieldError(nameInput);
            }
            
            return isValid;
        },

        // Función para mostrar error en un campo
        showFieldError: function(field, message) {
            field.classList.add('is-invalid');
            
            // Remover mensaje de error existente
            const existingError = field.parentNode.parentNode.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            // Crear nuevo mensaje de error
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i>${message}`;
            
            field.parentNode.parentNode.appendChild(errorDiv);
        },

        // Función para limpiar error de un campo
        clearFieldError: function(field) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            
            const errorMessage = field.parentNode.parentNode.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
        },

        // Función para resetear el botón de submit
        resetSubmitButton: function(button, originalText) {
            button.disabled = false;
            button.innerHTML = originalText;
        },

        // Función para mostrar notificaciones
        showNotification: function(message, type = 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    title: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                alert(message);
            }
        },

        // Función para detectar cambios en el formulario
        detectFormChanges: function(form) {
            const originalData = this.getFormData(form);
            let hasChanges = false;
            
            // Escuchar cambios en inputs
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    const currentData = this.getFormData(form);
                    hasChanges = !this.compareFormData(originalData, currentData);
                    this.updateSubmitButton(form, hasChanges);
                });
            });
            
            return hasChanges;
        },

        // Función para obtener datos del formulario
        getFormData: function(form) {
            const formData = new FormData(form);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            return data;
        },

        // Función para comparar datos del formulario
        compareFormData: function(data1, data2) {
            const keys1 = Object.keys(data1);
            const keys2 = Object.keys(data2);
            
            if (keys1.length !== keys2.length) return false;
            
            for (let key of keys1) {
                if (data1[key] !== data2[key]) return false;
            }
            
            return true;
        },

        // Función para actualizar estado del botón de submit
        updateSubmitButton: function(form, hasChanges) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                if (hasChanges) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-disabled');
                } else {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('btn-disabled');
                }
            }
        }
    };
}

// ===== FUNCIONES DE EFECTOS VISUALES =====

// Función para inicializar efectos en inputs
function initializeInputEffects() {
    const inputs = document.querySelectorAll('.form-input');
    
    inputs.forEach(input => {
        // Efecto de focus
        input.addEventListener('focus', function() {
            this.parentNode.style.transform = 'scale(1.02)';
            this.parentNode.style.boxShadow = '0 8px 25px rgba(102, 126, 234, 0.15)';
        });
        
        // Efecto de blur
        input.addEventListener('blur', function() {
            this.parentNode.style.transform = 'scale(1)';
            this.parentNode.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.05)';
        });
        
        // Efecto de hover
        input.addEventListener('mouseenter', function() {
            if (document.activeElement !== this) {
                this.parentNode.style.borderColor = '#cbd5e1';
            }
        });
        
        input.addEventListener('mouseleave', function() {
            if (document.activeElement !== this) {
                this.parentNode.style.borderColor = '#e5e7eb';
            }
        });
    });
}

// Función para inicializar efectos en botones
function initializeButtonEffects() {
    const buttons = document.querySelectorAll('.btn-primary, .btn-secondary');
    
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            if (!this.disabled) {
                this.style.transform = 'translateY(-2px) scale(1.02)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            if (!this.disabled) {
                this.style.transform = 'translateY(0) scale(1)';
            }
        });
    });
}

// Función para inicializar efectos en el header
function initializeHeaderEffects() {
    const header = document.querySelector('.page-header');
    const headerIcon = document.querySelector('.header-icon');
    
    if (header && headerIcon) {
        // Efecto de parallax suave
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            headerIcon.style.transform = `translateY(${rate}px)`;
        });
    }
}

// ===== FUNCIONES DE INICIALIZACIÓN =====

// Función principal de inicialización
function initializePermissionsEdit() {
    console.log('✅ permissions/edit.js cargado correctamente');
    
    // Inicializar efectos visuales
    initializeInputEffects();
    initializeButtonEffects();
    initializeHeaderEffects();
    
    // Configurar eventos del formulario
    setupFormEvents();
    
    // Configurar eventos adicionales
    setupAdditionalEvents();
}

// Función para configurar eventos del formulario
function setupFormEvents() {
    const form = document.querySelector('form');
    const nameInput = document.querySelector('#name');
    
    if (form) {
        // Evento de envío del formulario
        form.addEventListener('submit', function(e) {
            if (!window.permissionsEdit.handleFormSubmit(this)) {
                e.preventDefault();
            }
        });
        
        // Detectar cambios en el formulario
        window.permissionsEdit.detectFormChanges(form);
    }
    
    if (nameInput) {
        // Evento de input para formatear nombre
        nameInput.addEventListener('input', function() {
            window.permissionsEdit.formatPermissionName(this);
        });
        
        // Evento de blur para validar
        nameInput.addEventListener('blur', function() {
            window.permissionsEdit.validatePermissionName(this);
        });
    }
}

// Función para configurar eventos adicionales
function setupAdditionalEvents() {
    // Efecto de hover en el botón de volver
    const backButton = document.querySelector('.btn-back');
    if (backButton) {
        backButton.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.05)';
        });
        
        backButton.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    }
    
    // Efecto de hover en el icono del header
    const headerIcon = document.querySelector('.header-icon-bg');
    if (headerIcon) {
        headerIcon.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(5deg)';
        });
        
        headerIcon.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });
    }
    
    // Advertencia antes de salir si hay cambios
    window.addEventListener('beforeunload', function(e) {
        const form = document.querySelector('form');
        if (form) {
            const hasChanges = window.permissionsEdit.detectFormChanges(form);
            if (hasChanges) {
                e.preventDefault();
                e.returnValue = 'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?';
                return e.returnValue;
            }
        }
    });
}

// ===== EXPONER FUNCIONES GLOBALMENTE =====
if (typeof window.initializePermissionsEdit === 'undefined') {
    window.initializePermissionsEdit = initializePermissionsEdit;
}

// ===== INICIALIZAR CUANDO EL DOM ESTÉ LISTO =====
if (!window.permissionsEditInitialized) {
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.permissionsEditInitialized) {
            initializePermissionsEdit();
            window.permissionsEditInitialized = true;
        }
    });
}
