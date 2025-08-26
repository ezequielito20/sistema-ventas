// ===== JAVASCRIPT PARA GESTIÓN DE ROLES - DISEÑO MODERNO =====

// Función principal para gestionar roles
window.rolesManager = function() {
    return {
        viewMode: 'table', // 'table' o 'cards'
        searchTerm: '', // Término de búsqueda
        
        init() {
            // En pantallas pequeñas, forzar vista de tarjetas
            if (window.innerWidth < 768) {
                this.viewMode = 'cards';
            } else {
                // Inicializar el modo de vista desde localStorage si existe
                const savedViewMode = localStorage.getItem('rolesViewMode');
                if (savedViewMode) {
                    this.viewMode = savedViewMode;
                }
            }
            
            // Observar cambios en el modo de vista
            this.$watch('viewMode', (value) => {
                // Solo guardar en localStorage si no es pantalla pequeña
                if (window.innerWidth >= 768) {
                    localStorage.setItem('rolesViewMode', value);
                }
            });
            
            // Escuchar cambios de tamaño de ventana
            window.addEventListener('resize', () => {
                if (window.innerWidth < 768) {
                    this.viewMode = 'cards';
                }
            });

            // Inicializar interceptación de paginación
            this.initializePagination();
        },

        // Detectar si la vista usa paginación del servidor
        isServerPaginationActive() {
            const paginator = document.querySelector('.pagination-container .page-numbers a');
            return !!paginator; // existen enlaces → servidor
        },

        // Cargar una URL y reemplazar secciones sin recargar
        async loadRolesPage(url) {
            const container = document.querySelector('.min-h-screen');
            if (!container) return;

            // Indicador simple de carga
            container.style.opacity = '0.6';

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html, application/xhtml+xml'
                    }
                });

                if (!response.ok) throw new Error('Error al cargar');
                
                const html = await response.text();
                const temp = document.createElement('div');
                temp.innerHTML = html;

                // Reemplazar tabla
                const newTableBody = temp.querySelector('.modern-table tbody');
                const tableBody = document.querySelector('.modern-table tbody');
                if (newTableBody && tableBody) {
                    tableBody.innerHTML = newTableBody.innerHTML;
                }

                // Reemplazar tarjetas
                const newCardsGrid = temp.querySelector('.grid.grid-cols-1');
                const cardsGrid = document.querySelector('.grid.grid-cols-1');
                if (newCardsGrid && cardsGrid) {
                    cardsGrid.innerHTML = newCardsGrid.innerHTML;
                }

                // Reemplazar contenedores de paginación
                const newPaginationContainers = temp.querySelectorAll('.pagination-container');
                const paginationContainers = document.querySelectorAll('.pagination-container');
                if (newPaginationContainers.length > 0 && paginationContainers.length > 0) {
                    newPaginationContainers.forEach((newContainer, index) => {
                        if (paginationContainers[index]) {
                            paginationContainers[index].innerHTML = newContainer.innerHTML;
                        }
                    });
                }

                // Actualizar URL sin recargar
                window.history.pushState({}, '', url);

                // Reinicializar event listeners
                this.initializePagination();
            } catch (error) {
                console.error('Error al cargar página:', error);
            } finally {
                container.style.opacity = '';
            }
        },

        // Inicializar interceptación de paginación
        initializePagination() {
            // Interceptar clicks de paginación cuando servidor está activo
            document.addEventListener('click', (e) => {
                const paginationLink = e.target.closest('.pagination-btn, .page-number');
                if (paginationLink && paginationLink.href && this.isServerPaginationActive()) {
                    e.preventDefault();
                    this.loadRolesPage(paginationLink.href);
                }
            });

            // Interceptar búsqueda para servidor
            const searchInput = document.querySelector('input[x-model="searchTerm"]');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', () => {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        if (this.isServerPaginationActive()) {
                            const url = new URL(window.location.href);
                            if (this.searchTerm.trim()) {
                                url.searchParams.set('search', this.searchTerm.trim());
                            } else {
                                url.searchParams.delete('search');
                            }
                            this.loadRolesPage(url.toString());
                        }
                    }, 300);
                });
            }
        },
        
        // Cambiar modo de vista
        toggleViewMode(mode) {
            // Solo permitir cambio en pantallas medianas y grandes
            if (window.innerWidth >= 768) {
                this.viewMode = mode;
            }
        },
        
        // Función para verificar si un rol debe ser visible según el término de búsqueda
        isRoleVisible(searchText) {
            // Si hay paginación del servidor, no filtrar en el cliente
            if (this.isServerPaginationActive()) return true;
            
            if (!this.searchTerm) return true;
            
            const searchLower = this.searchTerm.toLowerCase();
            const textLower = searchText.toLowerCase();
            
            return textLower.includes(searchLower);
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
    fetch(`/roles/${roleId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                const role = data.role;
                
                                // Cerrar el modal de carga
                Swal.close();
                
                // Actualizar contenido del modal
                document.getElementById('modalRoleName').textContent = role.name;
                document.getElementById('modalRoleCreated').textContent = role.created_at;
                document.getElementById('modalRoleUpdated').textContent = role.updated_at;
                document.getElementById('modalRoleUsers').textContent = `${role.users_count} usuarios`;
                document.getElementById('modalRolePermissions').textContent = `${role.permissions_count} permisos`;
                
                // Actualizar tipo de rol
                const roleTypeElement = document.getElementById('modalRoleType');
                roleTypeElement.className = `role-type-badge ${role.is_system_role ? 'system-role' : 'custom-role'}`;
                roleTypeElement.innerHTML = `
                    <i class="fas fa-shield-alt"></i>
                    <span>${role.is_system_role ? 'Rol del Sistema' : 'Rol Personalizado'}</span>
                `;
                
                // Mostrar modal usando JavaScript nativo
                const modal = document.getElementById('showRoleModal');
                const bootstrapModal = new bootstrap.Modal(modal);
                bootstrapModal.show();
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
            
            // Cerrar el modal de carga
            Swal.close();
            
            let errorMessage = 'Ocurrió un error al obtener la información del rol';
            
            if (error.message.includes('401')) {
                errorMessage = 'No tienes permisos para ver esta información';
            } else if (error.message.includes('404')) {
                errorMessage = 'El rol no fue encontrado';
            }
            
            Swal.fire({
                title: 'Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        });
};

// Editar rol
window.editRole = function(roleId) {
    window.location.href = `/roles/edit/${roleId}`;
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
    fetch(`/roles/${roleId}/permissions`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Cerrar loading
                Swal.close();
                
                // Configurar el modal de permisos
                document.getElementById('roleId').value = roleId;
                document.getElementById('roleName').textContent = roleName;

                // Marcar permisos existentes
                const rolePermissions = data.permissions || [];
                rolePermissions.forEach(permission => {
                    const permissionId = permission.id || permission;
                    const checkbox = document.getElementById(`modal_permission_${permissionId}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });

                // Mostrar modal usando JavaScript nativo
                const modal = document.getElementById('permissionsModal');
                const bootstrapModal = new bootstrap.Modal(modal);
                bootstrapModal.show();
                
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
            
            // Cerrar el modal de carga
            Swal.close();
            
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
            fetch(`/roles/delete/${roleId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                // Cerrar modal de carga
                Swal.close();
                
                if (data.status === 'success') {
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
                
                // Cerrar modal de carga
                Swal.close();
                
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
    fetch(`/roles/${roleId}/permissions`, {
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
        // Cerrar modal de carga
        Swal.close();
        
        if (data.status === 'success') {
            Swal.fire({
                title: '¡Guardado!',
                text: 'Los permisos han sido actualizados exitosamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                // Cerrar modal usando JavaScript nativo
                const modal = document.getElementById('permissionsModal');
                const bootstrapModal = bootstrap.Modal.getInstance(modal);
                if (bootstrapModal) {
                    bootstrapModal.hide();
                }
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
        
        // Cerrar modal de carga
        Swal.close();
        
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
