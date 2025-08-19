// ===== JAVASCRIPT PARA ROLES/EDIT =====

document.addEventListener('DOMContentLoaded', function() {
    // Elementos del formulario
    const form = document.getElementById('editRoleForm');
    const nameInput = document.getElementById('name');
    const submitButton = document.getElementById('submitRole');

    // Variables para el manejo de cambios
    let originalName = nameInput.value.trim();
    let hasChanges = false;

    // Validación del formulario
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar el formulario
            if (validateForm()) {
                // Si la validación pasa, enviar el formulario
                submitForm();
            }
        });
    }

    // Función para validar el formulario
    function validateForm() {
        let isValid = true;
        const name = nameInput.value.trim();

        // Limpiar mensajes de error previos
        clearErrors();

        // Validar nombre del rol
        if (name.length === 0) {
            showError(nameInput, 'El nombre del rol es obligatorio');
            isValid = false;
        } else if (name.length > 255) {
            showError(nameInput, 'El nombre no puede exceder los 255 caracteres');
            isValid = false;
        } else if (!/^[a-zA-Z0-9\s\-_]+$/.test(name)) {
            showError(nameInput, 'El nombre solo puede contener letras, números, espacios, guiones y guiones bajos');
            isValid = false;
        }

        return isValid;
    }

    // Función para mostrar errores
    function showError(input, message) {
        // Agregar clase de error al input
        input.classList.add('is-invalid');
        
        // Crear o actualizar mensaje de error
        let errorElement = input.parentNode.querySelector('.invalid-feedback');
        if (!errorElement) {
            errorElement = document.createElement('span');
            errorElement.className = 'invalid-feedback';
            input.parentNode.appendChild(errorElement);
        }
        errorElement.textContent = message;
    }

    // Función para limpiar errores
    function clearErrors() {
        // Remover clases de error
        const inputs = form.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.classList.remove('is-invalid');
        });

        // Remover mensajes de error
        const errorMessages = form.querySelectorAll('.invalid-feedback');
        errorMessages.forEach(error => {
            error.remove();
        });
    }

    // Función para enviar el formulario
    function submitForm() {
        // Deshabilitar botón para prevenir múltiples envíos
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // Mostrar indicador de carga
        showLoadingAlert();

        // Enviar formulario después de un pequeño delay para mostrar el loading
        setTimeout(() => {
            form.submit();
        }, 500);
    }

    // Función para mostrar alerta de carga
    function showLoadingAlert() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Actualizando rol...',
                text: 'Por favor espera mientras se procesa tu solicitud',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    }

    // Validación en tiempo real
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            const name = this.value.trim();
            
            // Verificar si hay cambios
            hasChanges = name !== originalName;
            
            // Remover clase de error si el usuario está escribiendo
            if (name.length > 0) {
                this.classList.remove('is-invalid');
                const errorElement = this.parentNode.querySelector('.invalid-feedback');
                if (errorElement) {
                    errorElement.remove();
                }
            }
        });

        // Validación al perder el foco
        nameInput.addEventListener('blur', function() {
            const name = this.value.trim();
            
            if (name.length === 0) {
                showError(this, 'El nombre del rol es obligatorio');
            } else if (name.length > 255) {
                showError(this, 'El nombre no puede exceder los 255 caracteres');
            } else if (!/^[a-zA-Z0-9\s\-_]+$/.test(name)) {
                showError(this, 'El nombre solo puede contener letras, números, espacios, guiones y guiones bajos');
            }
        });
    }

    // Prevenir envío múltiple con Enter
    if (nameInput) {
        nameInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (validateForm()) {
                    submitForm();
                }
            }
        });
    }

    // Confirmación antes de salir si hay cambios
    window.addEventListener('beforeunload', function(e) {
        if (hasChanges && !submitButton.disabled) {
            e.preventDefault();
            e.returnValue = 'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?';
            return e.returnValue;
        }
    });

    // Manejar navegación con botón de cancelar
    const cancelButton = document.querySelector('a[href*="roles.index"]');
    if (cancelButton) {
        cancelButton.addEventListener('click', function(e) {
            if (hasChanges) {
                e.preventDefault();
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: 'Tienes cambios sin guardar. ¿Quieres salir sin guardar?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, salir',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = this.href;
                        }
                    });
                } else {
                    if (confirm('Tienes cambios sin guardar. ¿Quieres salir sin guardar?')) {
                        window.location.href = this.href;
                    }
                }
            }
        });
    }

    // Desactivar la advertencia al enviar el formulario
    if (form) {
        form.addEventListener('submit', function() {
            hasChanges = false;
            window.onbeforeunload = null;
        });
    }

    // Mejorar UX: Auto-focus en el campo de nombre
    if (nameInput) {
        nameInput.focus();
        // Seleccionar todo el texto para facilitar la edición
        nameInput.select();
    }

    // Mejorar UX: Indicador visual de campos requeridos
    const requiredFields = form.querySelectorAll('.required');
    requiredFields.forEach(field => {
        const label = field.querySelector('label');
        if (label) {
            label.style.position = 'relative';
        }
    });

    // Mejorar UX: Feedback visual en botones
    const buttons = form.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mousedown', function() {
            this.style.transform = 'translateY(0px)';
        });
        
        button.addEventListener('mouseup', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-2px)';
        });
    });

    // Función para detectar cambios en el formulario
    function checkForChanges() {
        const currentName = nameInput.value.trim();
        hasChanges = currentName !== originalName;
    }

    // Escuchar cambios en todos los campos del formulario
    const formInputs = form.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        input.addEventListener('change', checkForChanges);
        input.addEventListener('input', checkForChanges);
    });

    // Mostrar indicador visual de cambios (opcional)
    function updateChangeIndicator() {
        const submitBtn = document.getElementById('submitRole');
        if (hasChanges) {
            submitBtn.style.background = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
            submitBtn.title = 'Actualizar Rol (cambios pendientes)';
        } else {
            submitBtn.style.background = 'var(--primary-gradient)';
            submitBtn.title = 'Actualizar Rol';
        }
    }

    // Actualizar indicador cuando cambie el estado
    nameInput.addEventListener('input', updateChangeIndicator);
});
