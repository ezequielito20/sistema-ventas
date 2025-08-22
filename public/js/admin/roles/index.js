// ===== JAVASCRIPT PARA GESTIÓN DE ROLES - DISEÑO MODERNO =====

// Función principal para gestionar roles
window.rolesManager = function() {
    return {
        viewMode: 'table', // 'table' o 'cards'
        
        init() {
            // Inicializar el modo de vista desde localStorage si existe
            const savedViewMode = localStorage.getItem('rolesViewMode');
            if (savedViewMode) {
                this.viewMode = savedViewMode;
            }
            
            // Observar cambios en el modo de vista
            this.$watch('viewMode', (value) => {
                localStorage.setItem('rolesViewMode', value);
            });
        },
        
        // Cambiar modo de vista
        toggleViewMode(mode) {
            this.viewMode = mode;
        }
    };
};

// Función para la sección hero
window.heroSection = function() {
    return {
        init() {
            // Animaciones de entrada
            this.$nextTick(() => {
                const elements = this.$el.querySelectorAll('[data-animate]');
                elements.forEach((el, index) => {
                    setTimeout(() => {
                        el.classList.add('animate-in');
                    }, index * 100);
                });
            });
        }
    };
};

// Función para gestionar modales
window.modalManager = function() {
    return {
        init() {
            // Inicializar tooltips si existen
            if (typeof $ !== 'undefined' && $.fn.tooltip) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        }
    };
};

// ===== FUNCIONES GLOBALES PARA ACCIONES =====

// Mostrar detalles del rol
window.showRole = function(roleId) {
    // Mostrar loading
    Swal.fire({
        title: 'Cargando...',
        text: 'Obteniendo información del rol',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Realizar petición AJAX
    fetch(`/admin/roles/${roleId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const role = data.role;
                
                // Mostrar modal con detalles
                Swal.fire({
                    title: `<i class="fas fa-user-shield text-primary"></i> ${role.name}`,
                    html: `
                        <div class="role-details text-left">
                            <div class="detail-item">
                                <strong><i class="fas fa-calendar"></i> Fecha de Creación:</strong>
                                <span>${role.created_at}</span>
                            </div>
                            <div class="detail-item">
                                <strong><i class="fas fa-clock"></i> Última Actualización:</strong>
                                <span>${role.updated_at}</span>
                            </div>
                            <div class="detail-item">
                                <strong><i class="fas fa-users"></i> Usuarios Asignados:</strong>
                                <span>${role.users_count} usuarios</span>
                            </div>
                            <div class="detail-item">
                                <strong><i class="fas fa-key"></i> Permisos Asignados:</strong>
                                <span>${role.permissions_count} permisos</span>
                            </div>
                            <div class="detail-item">
                                <strong><i class="fas fa-shield-alt"></i> Tipo de Rol:</strong>
                                <span>${role.is_system_role ? 'Rol del Sistema' : 'Rol Personalizado'}</span>
                            </div>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: 'Cerrar',
                    confirmButtonColor: '#667eea',
                    customClass: {
                        popup: 'role-details-modal'
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'No se pudo obtener la información del rol',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: 'Ocurrió un error al obtener la información del rol',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        });
};

// Editar rol
window.editRole = function(roleId) {
    window.location.href = `/admin/roles/edit/${roleId}`;
};

// Asignar permisos
window.assignPermissions = function(roleId, roleName) {
    // Mostrar loading
    Swal.fire({
        title: 'Cargando...',
        text: 'Cargando permisos del rol',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Realizar petición AJAX para obtener permisos del rol
    fetch(`/admin/roles/${roleId}/permissions`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cerrar loading
                Swal.close();
                
                // Configurar el modal de permisos
                document.getElementById('roleId').value = roleId;
                document.getElementById('roleName').textContent = roleName;
                
                                 // Marcar permisos existentes
                 const rolePermissions = data.permissions || [];
                 rolePermissions.forEach(permissionId => {
                     const checkbox = document.getElementById(`modal_permission_${permissionId}`);
                     if (checkbox) {
                         checkbox.checked = true;
                     }
                 });
                
                // Mostrar modal
                $('#permissionsModal').modal('show');
                
                // Inicializar funcionalidad del modal
                initializePermissionsModal();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'No se pudieron cargar los permisos',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: 'Ocurrió un error al cargar los permisos',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        });
};

// Eliminar rol
window.deleteRole = function(roleId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer. El rol será eliminado permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Realizar petición de eliminación
            fetch(`/admin/roles/${roleId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El rol ha sido eliminado exitosamente',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Recargar la página
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'No se pudo eliminar el rol',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al eliminar el rol',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
        }
    });
};

// ===== FUNCIONES PARA EL MODAL DE PERMISOS =====

function initializePermissionsModal() {
    // Búsqueda de permisos
    const searchInput = document.getElementById('searchPermission');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const permissionItems = document.querySelectorAll('.permission-item');
            
            permissionItems.forEach(item => {
                const label = item.querySelector('.custom-control-label');
                const text = label.textContent.toLowerCase();
                
                if (text.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Seleccionar todos los permisos
    const selectAllCheckbox = document.getElementById('selectAllPermissions');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
            permissionCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Selectores de grupo
    const groupSelectors = document.querySelectorAll('.group-selector');
    groupSelectors.forEach(selector => {
        selector.addEventListener('change', function() {
            const group = this.dataset.group;
            const groupCheckboxes = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
            groupCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    });

    // Guardar permisos
    const saveButton = document.getElementById('savePermissions');
    if (saveButton) {
        saveButton.addEventListener('click', savePermissions);
    }
}

function savePermissions() {
    const roleId = document.getElementById('roleId').value;
    const selectedPermissions = Array.from(document.querySelectorAll('.permission-checkbox:checked'))
        .map(checkbox => checkbox.value);

    // Mostrar loading
    Swal.fire({
        title: 'Guardando...',
        text: 'Actualizando permisos del rol',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Realizar petición AJAX
    fetch(`/admin/roles/${roleId}/permissions`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            permissions: selectedPermissions
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: '¡Guardado!',
                text: 'Los permisos han sido actualizados exitosamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                // Cerrar modal
                $('#permissionsModal').modal('hide');
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message || 'No se pudieron guardar los permisos',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'Ocurrió un error al guardar los permisos',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    });
}

// ===== INICIALIZACIÓN CUANDO EL DOM ESTÉ LISTO =====

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    if (typeof $ !== 'undefined' && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Inicializar modales de Bootstrap si están disponibles
    if (typeof $ !== 'undefined' && $.fn.modal) {
        // Configurar eventos del modal de permisos
        $('#permissionsModal').on('shown.bs.modal', function() {
            initializePermissionsModal();
        });
    }

    // Configurar eventos de teclado
    document.addEventListener('keydown', function(e) {
        // ESC para cerrar modales
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                if (typeof $ !== 'undefined' && $.fn.modal) {
                    $(modal).modal('hide');
                }
            });
        }
    });
});

// ===== UTILIDADES ADICIONALES =====

// Función para formatear fechas
window.formatDate = function(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Función para formatear números
window.formatNumber = function(number) {
    return new Intl.NumberFormat('es-ES').format(number);
};

// Función para mostrar notificaciones
window.showNotification = function(message, type = 'info') {
    Swal.fire({
        title: type === 'success' ? '¡Éxito!' : type === 'error' ? 'Error' : 'Información',
        text: message,
        icon: type,
        timer: type === 'success' ? 3000 : undefined,
        timerProgressBar: type === 'success',
        confirmButtonText: 'Aceptar'
    });
};
