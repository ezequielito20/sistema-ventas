// ===== CONFIGURACIÓN GLOBAL =====
const CATEGORY_CREATE_CONFIG = {
    typingDelay: 300,
    maxNameLength: 255,
    maxDescriptionLength: 500,
    routes: {
        index: '/admin/categories',
        store: '/admin/categories'
    }
};

// ===== FUNCIONES GLOBALES =====
window.categoryCreate = {
    // Función para volver atrás con animación
    goBack: function() {
        const card = document.querySelector('.form-card');
        if (card) {
            card.style.transform = 'translateX(-100%)';
            card.style.opacity = '0';
            
            setTimeout(() => {
                if (document.referrer) {
                    window.history.back();
                } else {
                    window.location.href = CATEGORY_CREATE_CONFIG.routes.index;
                }
            }, 300);
        }
    },

    // Función para resetear formulario con efectos
    resetForm: function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Limpiar formulario?',
                text: 'Se borrarán todos los datos ingresados',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0ea5e9',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, limpiar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'swal-modern-popup',
                    confirmButton: 'swal-modern-confirm',
                    cancelButton: 'swal-modern-cancel'
                },
                backdrop: 'rgba(0,0,0,0.4)'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.resetFormAction();
                }
            });
        } else {
            // Fallback si SweetAlert2 no está disponible
            if (confirm('¿Limpiar formulario? Se borrarán todos los datos ingresados')) {
                this.resetFormAction();
            }
        }
    },

    resetFormAction: function() {
        const fieldWrappers = document.querySelectorAll('.field-wrapper');
        
        // Animación de limpieza
        fieldWrappers.forEach((wrapper, index) => {
            setTimeout(() => {
                wrapper.style.transform = 'scale(0.95)';
                wrapper.style.opacity = '0.5';
            }, index * 100);
        });

        setTimeout(() => {
            const form = document.getElementById('categoryForm');
            if (form) {
                form.reset();
            }
            
            // Actualizar vista previa
            this.updatePreview();
            
            // Restaurar campos
            fieldWrappers.forEach(wrapper => {
                wrapper.style.transform = 'scale(1)';
                wrapper.style.opacity = '1';
            });
            
            // Mostrar confirmación
            this.showToast('Formulario limpiado correctamente', 'success');
        }, 500);
    },

    // Actualizar vista previa
    updatePreview: function() {
        const nameInput = document.getElementById('name');
        const descriptionInput = document.getElementById('description');
        const previewName = document.getElementById('previewName');
        const previewDescription = document.getElementById('previewDescription');
        const previewCard = document.querySelector('.category-preview');

        if (nameInput && previewName) {
            const name = nameInput.value.trim() || 'Nombre de la categoría';
            previewName.textContent = name;
        }

        if (descriptionInput && previewDescription) {
            const description = descriptionInput.value.trim() || 'Descripción de la categoría';
            previewDescription.textContent = description;
        }

        // Animación de actualización
        if (previewCard) {
            previewCard.style.transform = 'scale(0.98)';
            setTimeout(() => {
                previewCard.style.transform = 'scale(1)';
            }, 150);
        }
    },

    // Mostrar mensaje de validación
    showValidationError: function(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: message,
                confirmButtonColor: '#0ea5e9',
                customClass: {
                    popup: 'swal-modern-popup',
                    confirmButton: 'swal-modern-confirm'
                },
                backdrop: 'rgba(0,0,0,0.4)'
            });
        } else {
            // Fallback si SweetAlert2 no está disponible
            alert('Error de validación: ' + message);
        }
    },

    // Mostrar estado de carga
    showLoadingState: function() {
        const submitBtn = document.getElementById('submitCategory');
        if (submitBtn) {
            const btnContent = submitBtn.querySelector('.btn-content');
            if (btnContent) {
                const originalContent = btnContent.innerHTML;
                
                submitBtn.disabled = true;
                btnContent.innerHTML = `
                    <div class="loading-spinner"></div>
                    <span>Creando categoría...</span>
                `;
                
                // Restaurar contenido original después de un tiempo
                setTimeout(() => {
                    btnContent.innerHTML = originalContent;
                    submitBtn.disabled = false;
                }, 5000);
            }
        }
    },

    // Mostrar toast
    showToast: function(message, type = 'success') {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'swal-toast-popup'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            Toast.fire({
                icon: type,
                title: message
            });
        } else {
            // Fallback si SweetAlert2 no está disponible
        }
    },

    // Crear efecto ripple
    createRippleEffect: function(button, event) {
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        const ripple = document.createElement('div');
        ripple.className = 'ripple-effect';
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        `;
        
        const rippleContainer = button.querySelector('.btn-ripple');
        if (rippleContainer) {
            rippleContainer.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        }
    }
};

// ===== FUNCIÓN PRINCIPAL DE INICIALIZACIÓN =====
function initializeCategoryCreate() {
    // Variables globales
    let formChanged = false;
    let typingTimer;

    // ===== INICIALIZACIÓN DE INTERACCIONES =====
    function initializeFormInteractions() {
        // Efectos de foco en inputs
        const inputs = document.querySelectorAll('.modern-input, .modern-textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                const wrapper = this.closest('.input-wrapper, .textarea-wrapper');
                const fieldWrapper = this.closest('.field-wrapper');
                const labelIcon = fieldWrapper?.querySelector('.label-icon');
                
                if (wrapper) wrapper.classList.add('focused');
                if (labelIcon) labelIcon.style.transform = 'scale(1.1)';
            });

            input.addEventListener('blur', function() {
                const wrapper = this.closest('.input-wrapper, .textarea-wrapper');
                const fieldWrapper = this.closest('.field-wrapper');
                const labelIcon = fieldWrapper?.querySelector('.label-icon');
                
                if (wrapper) wrapper.classList.remove('focused');
                if (labelIcon) labelIcon.style.transform = 'scale(1)';
            });

            // Detección de cambios en el formulario
            input.addEventListener('input', function() {
                formChanged = true;
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    window.categoryCreate.updatePreview();
                }, CATEGORY_CREATE_CONFIG.typingDelay);
                
                // Efecto visual de cambio
                const wrapper = this.closest('.input-wrapper, .textarea-wrapper');
                if (wrapper) {
                    wrapper.classList.add('changed');
                    setTimeout(() => {
                        wrapper.classList.remove('changed');
                    }, 1000);
                }
            });
        });

        // Efectos de hover en botones
        const buttons = document.querySelectorAll('.btn-modern');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                const btnContent = this.querySelector('.btn-content');
                if (btnContent) {
                    btnContent.style.transform = 'translateY(-1px)';
                }
            });

            button.addEventListener('mouseleave', function() {
                const btnContent = this.querySelector('.btn-content');
                if (btnContent) {
                    btnContent.style.transform = 'translateY(0)';
                }
            });
        });

        // Efectos de ripple en botones glass
        const glassButtons = document.querySelectorAll('.btn-glass');
        glassButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                window.categoryCreate.createRippleEffect(this, e);
            });
        });
    }

    // ===== SISTEMA DE VISTA PREVIA =====
    function initializePreviewSystem() {
        window.categoryCreate.updatePreview();
    }

    // ===== VALIDACIÓN AVANZADA =====
    function initializeFormValidation() {
        const nameInput = document.getElementById('name');
        const descriptionInput = document.getElementById('description');
        const form = document.getElementById('categoryForm');

        // Validación en tiempo real del nombre
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                const value = this.value.trim();
                const wrapper = this.closest('.input-wrapper');
                const container = this.closest('.input-container');
                
                // Limpiar mensajes previos
                const prevMessages = container.querySelectorAll('.validation-message');
                prevMessages.forEach(msg => msg.remove());
                
                if (value.length === 0) {
                    if (wrapper) wrapper.classList.remove('valid');
                    if (wrapper) wrapper.classList.add('invalid');
                    showFieldMessage(container, 'El nombre es obligatorio', 'error');
                } else if (value.length > CATEGORY_CREATE_CONFIG.maxNameLength) {
                    if (wrapper) wrapper.classList.remove('valid');
                    if (wrapper) wrapper.classList.add('invalid');
                    showFieldMessage(container, `Excede el límite por ${value.length - CATEGORY_CREATE_CONFIG.maxNameLength} caracteres`, 'error');
                } else {
                    if (wrapper) wrapper.classList.remove('invalid');
                    if (wrapper) wrapper.classList.add('valid');
                    showFieldMessage(container, '¡Perfecto!', 'success');
                }
            });
        }

        // Contador de caracteres para descripción
        if (descriptionInput) {
            descriptionInput.addEventListener('input', function() {
                const currentLength = this.value.length;
                const remaining = CATEGORY_CREATE_CONFIG.maxDescriptionLength - currentLength;
                const counter = document.getElementById('charCounter');
                
                if (counter) {
                    counter.textContent = `${currentLength}/${CATEGORY_CREATE_CONFIG.maxDescriptionLength}`;
                    
                    if (remaining < 50) {
                        counter.style.color = '#f59e0b';
                    } else if (remaining < 0) {
                        counter.style.color = '#ef4444';
                    } else {
                        counter.style.color = '#94a3b8';
                    }
                }
            });
        }

        // Validación al enviar
        if (form) {
            form.addEventListener('submit', function(e) {
                const name = nameInput?.value.trim() || '';
                const description = descriptionInput?.value.trim() || '';
                
                // Validar nombre
                if (name.length === 0) {
                    e.preventDefault();
                    window.categoryCreate.showValidationError('El nombre de la categoría es obligatorio');
                    if (nameInput) nameInput.focus();
                    return false;
                }
                
                if (name.length > CATEGORY_CREATE_CONFIG.maxNameLength) {
                    e.preventDefault();
                    window.categoryCreate.showValidationError('El nombre no puede exceder los 255 caracteres');
                    if (nameInput) nameInput.focus();
                    return false;
                }

                if (description.length > CATEGORY_CREATE_CONFIG.maxDescriptionLength) {
                    e.preventDefault();
                    window.categoryCreate.showValidationError('La descripción no puede exceder los 500 caracteres');
                    if (descriptionInput) descriptionInput.focus();
                    return false;
                }

                // Mostrar estado de carga
                window.categoryCreate.showLoadingState();
                window.onbeforeunload = null;
            });
        }
    }

    // ===== ANIMACIONES Y EFECTOS =====
    function initializeAnimations() {
        // Animación de progreso
        setTimeout(() => {
            const progressSteps = document.querySelectorAll('.progress-step');
            if (progressSteps.length > 1) {
                progressSteps[1].classList.add('active');
            }
        }, 2000);
    }

    // ===== FUNCIONES AUXILIARES =====
    function showFieldMessage(container, message, type) {
        const messageClass = type === 'error' ? 'error-message' : 'success-message';
        const iconClass = type === 'error' ? 'fas fa-exclamation-triangle' : 'fas fa-check-circle';
        
        const messageHtml = `
            <div class="validation-message ${messageClass}">
                <i class="${iconClass}"></i>
                <span>${message}</span>
            </div>
        `;
        
        const messageEl = document.createElement('div');
        messageEl.innerHTML = messageHtml;
        const messageDiv = messageEl.firstElementChild;
        
        container.appendChild(messageDiv);
        
        // Animar entrada
        messageDiv.style.opacity = '0';
        messageDiv.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            messageDiv.style.opacity = '1';
            messageDiv.style.transform = 'translateY(0)';
        }, 50);
    }

    // ===== INICIALIZAR TODO =====
    initializeFormInteractions();
    initializePreviewSystem();
    initializeFormValidation();
    initializeAnimations();

    // Confirmación antes de salir si hay cambios
    window.onbeforeunload = function() {
        if (formChanged) {
            return "¿Estás seguro de que quieres salir? Los cambios no guardados se perderán.";
        }
    };

    // Agregar estilos adicionales para animaciones
    if (!document.querySelector('.category-create-styles')) {
        const style = document.createElement('style');
        style.className = 'category-create-styles';
        style.textContent = `
            .swal-modern-popup {
                border-radius: 16px !important;
                backdrop-filter: blur(10px) !important;
            }
            
            .swal-modern-confirm {
                border-radius: 8px !important;
                padding: 0.75rem 1.5rem !important;
                font-weight: 600 !important;
            }
            
            .swal-modern-cancel {
                border-radius: 8px !important;
                padding: 0.75rem 1.5rem !important;
                font-weight: 600 !important;
            }
            
            .swal-toast-popup {
                border-radius: 12px !important;
                backdrop-filter: blur(10px) !important;
            }
            
            .validation-message {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.875rem;
                font-weight: 500;
                margin-top: 0.5rem;
                padding: 0.5rem 0.75rem;
                border-radius: 8px;
                transition: all 0.3s ease;
            }
            
            .success-message {
                color: #059669;
                background: rgba(16, 185, 129, 0.1);
                border-left: 3px solid #10b981;
            }
            
            .input-wrapper.valid .input-border,
            .textarea-wrapper.valid .input-border {
                border-color: #10b981 !important;
            }
            
            .input-wrapper.invalid .input-border,
            .textarea-wrapper.invalid .input-border {
                border-color: #ef4444 !important;
            }
            
            .input-wrapper.changed,
            .textarea-wrapper.changed {
                transform: scale(1.02);
                box-shadow: 0 0 20px rgba(14, 165, 233, 0.2);
            }
            
            .ripple-effect {
                pointer-events: none;
            }
            
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }

            .loading-spinner {
                width: 16px;
                height: 16px;
                border: 2px solid rgba(255,255,255,0.3);
                border-top: 2px solid white;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    }
}

// ===== INICIALIZAR CUANDO EL DOM ESTÉ LISTO =====
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCategoryCreate);
} else {
    initializeCategoryCreate();
}
