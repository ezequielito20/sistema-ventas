// Logic moved from resources/views/admin/roles/index.blade.php
// Roles management functionality using Alpine.js and native JavaScript

// Función para esperar a que Alpine.js esté disponible
function waitForAlpine(callback) {
    if (typeof Alpine !== 'undefined') {
        callback();
    } else {
        setTimeout(function() {
            waitForAlpine(callback);
        }, 50);
    }
}

// Esperar a que Alpine.js esté disponible antes de ejecutar el código
waitForAlpine(function() {
    // Inicializar Alpine.js cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        
        // Tabla simple con Alpine.js - DataTables migrado
        /*
        if (typeof $.fn.DataTable !== 'undefined') {
            $('#rolesTable').DataTable({
                responsive: true,
                autoWidth: true,
                dom: 'lfrtip',
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
                processing: false,
                serverSide: false,
                ajax: null,
                deferRender: false,

                "language": {
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Roles",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Roles",
                    "infoFiltered": "(Filtrado de _MAX_ total Roles)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ Roles",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscador:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                }
            });
        }
        */

        // Función para obtener token CSRF
        function getCSRFToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        }

        // Función para hacer peticiones fetch
        async function makeRequest(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                }
            };
            
            const finalOptions = { ...defaultOptions, ...options };
            
            try {
                const response = await fetch(url, finalOptions);
                return await response.json();
            } catch (error) {
                console.error('Error en petición:', error);
                throw error;
            }
        }

        // Función para mostrar SweetAlert2
        function showAlert(options) {
            if (typeof Swal !== 'undefined') {
                return Swal.fire(options);
            } else {
                // Fallback a alert nativo
                alert(options.text || options.title);
            }
        }

        // Funciones para manejar acciones de la tabla simple
        window.showRole = function(roleId) {
            // Implementar lógica para mostrar detalles del rol
            console.log('Mostrar rol:', roleId);
        };

        window.assignPermissions = function(roleId, roleName) {
            // Implementar lógica para asignar permisos
            console.log('Asignar permisos a:', roleName, roleId);
        };

        window.deleteRole = async function(roleId) {
            const result = await showAlert({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await makeRequest(`/admin/roles/${roleId}`, {
                        method: 'DELETE'
                    });

                    if (response.success) {
                        showAlert({
                            title: '¡Eliminado!',
                            text: 'El rol ha sido eliminado correctamente',
                            icon: 'success'
                        });
                        
                        // Recargar la página para actualizar la tabla
                        window.location.reload();
                    } else {
                        showAlert({
                            title: 'Error',
                            text: response.message || 'Error al eliminar el rol',
                            icon: 'error'
                        });
                    }
                } catch (error) {
                    showAlert({
                        title: 'Error',
                        text: 'Error de conexión',
                        icon: 'error'
                    });
                }
            }
        };

        // Manejo de eliminación de roles (legacy - mantener por compatibilidad)
        document.querySelectorAll('.delete-role').forEach(button => {
            button.addEventListener('click', async function() {
                const roleId = this.dataset.id;

                const result = await showAlert({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede revertir",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                });

                if (result.isConfirmed) {
                    try {
                        const response = await makeRequest(`/roles/delete/${roleId}`, {
                            method: 'DELETE'
                        });

                        if (response.status === 'success') {
                            await showAlert({
                                title: '¡Eliminado!',
                                text: response.message,
                                icon: 'success'
                            });
                            window.location.reload();
                        } else {
                            showAlert({
                                title: 'Error',
                                text: response.message,
                                icon: 'error'
                            });
                        }
                    } catch (error) {
                        let errorMessage = 'No se pudo eliminar el rol';
                        
                        if (error.response) {
                            try {
                                const errorData = await error.response.json();
                                if (errorData.message) {
                                    errorMessage = errorData.message;
                                }
                            } catch (e) {
                                console.error('Error al procesar respuesta de error:', e);
                            }
                        }
                        
                        showAlert({
                            icon: 'error',
                            title: 'Error al Eliminar Rol',
                            text: errorMessage
                        });
                    }
                }
            });
        });

        // Manejo de visualización de rol
        document.querySelectorAll('.show-role').forEach(button => {
            button.addEventListener('click', async function() {
                const roleId = this.dataset.id;

                // Mostrar loading
                showAlert({
                    title: 'Cargando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        if (typeof Swal !== 'undefined') {
                            Swal.showLoading();
                        }
                    }
                });

                try {
                    const response = await makeRequest(`/roles/${roleId}`, {
                        method: 'GET'
                    });

                    if (response.status === 'success') {
                        // Cerrar loading
                        if (typeof Swal !== 'undefined') {
                            Swal.close();
                        }
                        
                        // Mostrar modal con SweetAlert2
                        showAlert({
                            title: `<i class="fas fa-user-shield"></i> Detalles del Rol`,
                            html: `
                                <div class="role-details">
                                    <div class="detail-item">
                                        <strong><i class="fas fa-tag"></i> Nombre:</strong>
                                        <span>${response.role.name}</span>
                                    </div>
                                    <div class="detail-item">
                                        <strong><i class="fas fa-calendar"></i> Fecha de Creación:</strong>
                                        <span>${response.role.created_at}</span>
                                    </div>
                                    <div class="detail-item">
                                        <strong><i class="fas fa-clock"></i> Última Actualización:</strong>
                                        <span>${response.role.updated_at}</span>
                                    </div>
                                    <div class="detail-item">
                                        <strong><i class="fas fa-users"></i> Usuarios Asignados:</strong>
                                        <span>${response.role.users_count} usuario(s)</span>
                                    </div>
                                    <div class="detail-item">
                                        <strong><i class="fas fa-key"></i> Permisos Asignados:</strong>
                                        <span>${response.role.permissions_count} permiso(s)</span>
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
                        showAlert({
                            icon: 'error',
                            title: 'Error al Obtener Datos',
                            text: 'No se pudieron obtener los datos del rol'
                        });
                    }
                } catch (error) {
                    let errorMessage = 'No se pudieron obtener los datos del rol';
                    
                    if (error.response) {
                        try {
                            const errorData = await error.response.json();
                            if (errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (e) {
                            console.error('Error al procesar respuesta:', e);
                        }
                    }
                    
                    showAlert({
                        icon: 'error',
                        title: 'Error al Obtener Datos',
                        text: errorMessage
                    });
                }
            });
        });

        // Scripts para el modal de permisos
        document.querySelectorAll('.assign-permissions').forEach(button => {
            button.addEventListener('click', async function() {
                const roleId = this.dataset.id;
                const roleName = this.dataset.name;

                document.getElementById('roleId').value = roleId;
                document.getElementById('roleName').textContent = roleName;

                // Mostrar loading
                showAlert({
                    title: 'Cargando permisos...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        if (typeof Swal !== 'undefined') {
                            Swal.showLoading();
                        }
                    }
                });

                // Limpiar checkboxes
                document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });

                try {
                    const data = await makeRequest(`/roles/${roleId}/permissions`, {
                        method: 'GET'
                    });

                    if (data.status === 'success') {
                        data.permissions.forEach(function(permission) {
                            const checkbox = document.getElementById(`permission_${permission.id}`);
                            if (checkbox) {
                                checkbox.checked = true;
                            }
                        });

                        // Actualizar estados de los selectores de grupo
                        updateGroupSelectors();
                        
                        // Mostrar información adicional del rol si está disponible
                        if (data.role_info && data.role_info.is_system_role) {
                            const roleNameElement = document.getElementById('roleName');
                            roleNameElement.innerHTML += ' <small class="badge badge-warning">Rol del Sistema</small>';
                        }

                        // Cerrar loading y mostrar modal
                        if (typeof Swal !== 'undefined') {
                            Swal.close();
                        }
                        
                        // Mostrar modal usando JavaScript nativo
                        const modal = document.getElementById('permissionsModal');
                        if (modal) {
                            modal.style.display = 'block';
                            modal.classList.add('show');
                            document.body.classList.add('modal-open');
                            
                            // Agregar backdrop
                            const backdrop = document.createElement('div');
                            backdrop.className = 'modal-backdrop fade show';
                            backdrop.id = 'permissionsModalBackdrop';
                            document.body.appendChild(backdrop);
                        }
                    } else {
                        showAlert({
                            icon: 'error',
                            title: 'Error al Cargar Permisos',
                            text: data.message || 'No se pudieron cargar los permisos del rol'
                        });
                    }
                } catch (error) {
                    let errorMessage = 'No se pudieron cargar los permisos del rol';
                    
                    if (error.response) {
                        try {
                            const errorData = await error.response.json();
                            if (errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (e) {
                            console.error('Error al procesar respuesta:', e);
                        }
                    }
                    
                    showAlert({
                        icon: 'error',
                        title: 'Error al Cargar Permisos',
                        text: errorMessage
                    });
                }
            });
        });

        // Selector para todos los permisos
        const selectAllPermissions = document.getElementById('selectAllPermissions');
        if (selectAllPermissions) {
            selectAllPermissions.addEventListener('change', function() {
                const isChecked = this.checked;
                const searchTerm = document.getElementById('searchPermission')?.value.toLowerCase() || '';

                if (searchTerm) {
                    // Si hay término de búsqueda, solo seleccionar los permisos visibles
                    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                        const permissionItem = checkbox.closest('.permission-item');
                        if (permissionItem && permissionItem.style.display !== 'none') {
                            checkbox.checked = isChecked;
                        }
                    });

                    // Actualizar los selectores de grupo
                    updateGroupSelectors();
                } else {
                    // Si no hay búsqueda, comportamiento normal
                    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                    document.querySelectorAll('.group-selector').forEach(selector => {
                        selector.checked = isChecked;
                    });
                }
            });
        }

        // Búsqueda de permisos
        const searchPermission = document.getElementById('searchPermission');
        if (searchPermission) {
            searchPermission.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                document.querySelectorAll('.permissions-container .permission-module-card').forEach(card => {
                    const permissionItems = card.querySelectorAll('.permission-item');
                    let hasVisiblePermissions = false;

                    permissionItems.forEach(item => {
                        const friendlyText = item.querySelector('label')?.textContent.toLowerCase() || '';
                        const technicalText = item.querySelector('input')?.dataset.name?.toLowerCase() || '';
                        const isVisible = friendlyText.includes(searchTerm) || technicalText.includes(searchTerm);
                        
                        item.style.display = isVisible ? 'block' : 'none';
                        if (isVisible) {
                            hasVisiblePermissions = true;
                        }
                    });

                    const column = card.closest('.col-xl-4, .col-lg-6, .col-12');
                    if (column) {
                        column.style.display = hasVisiblePermissions ? 'block' : 'none';
                    }
                });

                // Resetear el checkbox general
                if (selectAllPermissions) {
                    selectAllPermissions.checked = false;
                }
            });
        }

        // Selector de grupo
        document.querySelectorAll('.group-selector').forEach(selector => {
            selector.addEventListener('change', function() {
                const group = this.dataset.group;
                const checked = this.checked;

                document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`).forEach(checkbox => {
                    checkbox.checked = checked;
                });
            });
        });

        // Actualizar selector de grupo cuando cambian los permisos individuales
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateGroupSelectors);
        });

        // Guardar cambios
        const savePermissions = document.getElementById('savePermissions');
        if (savePermissions) {
            savePermissions.addEventListener('click', async function() {
                const roleId = document.getElementById('roleId').value;
                const permissions = Array.from(document.querySelectorAll('.permission-checkbox:checked')).map(checkbox => checkbox.value);

                try {
                    const response = await makeRequest(`/roles/${roleId}/permissions`, {
                        method: 'POST',
                        body: JSON.stringify({
                            permissions: permissions
                        })
                    });

                    // Cerrar modal
                    const modal = document.getElementById('permissionsModal');
                    if (modal) {
                        modal.style.display = 'none';
                        modal.classList.remove('show');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.getElementById('permissionsModalBackdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                    }

                    await showAlert({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Los permisos han sido actualizados correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    // Recargar la página para actualizar la tabla después de un pequeño delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 100);
                } catch (error) {
                    let errorMessage = 'Hubo un problema al actualizar los permisos';
                    
                    if (error.response) {
                        try {
                            const errorData = await error.response.json();
                            if (errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (e) {
                            console.error('Error al procesar respuesta de error:', e);
                        }
                    }
                    
                    showAlert({
                        icon: 'error',
                        title: 'Error al Actualizar Permisos',
                        text: errorMessage
                    });
                }
            });
        }

        function updateGroupSelectors() {
            document.querySelectorAll('.group-selector').forEach(selector => {
                const group = selector.dataset.group;
                const totalPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`).length;
                const checkedPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]:checked`).length;

                selector.checked = totalPermissions === checkedPermissions;
            });
        }

        // Event listeners para cerrar modales
        document.querySelectorAll('[data-dismiss="modal"]').forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.getElementById('permissionsModalBackdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            });
        });

        // Cerrar modales con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('.modal.show');
                openModals.forEach(modal => {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.getElementById('permissionsModalBackdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                });
            }
        });

        // Cerrar modales al hacer clic en el backdrop
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    this.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.getElementById('permissionsModalBackdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            });
        });

    });
});
