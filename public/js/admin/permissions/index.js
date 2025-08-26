// ===== CONFIGURACIÓN GLOBAL =====
if (typeof PERMISSIONS_CONFIG === 'undefined') {
    window.PERMISSIONS_CONFIG = {
        routes: {
            delete: '/permissions/delete',
            show: '/permissions',
            report: '/admin/permissions/report'
        },
        search: {
            debounceTime: 300
        },
        debug: {
            enabled: false
        }
    };
}

// ===== FUNCIONES GLOBALES =====
if (typeof window.permissionsIndex === 'undefined') {
    window.permissionsIndex = {
        // Función para eliminar permiso
        deletePermission: function(permissionId) {
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede revertir",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submitDeletePermission(permissionId);
                }
            });
        },

        // Función para enviar eliminación
        submitDeletePermission: function(permissionId) {
            // Mostrar modal de carga
            Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch(`${PERMISSIONS_CONFIG.routes.delete}/${permissionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Cerrar modal de carga
                Swal.close();
                
                if (data.icons === 'success') {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: data.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message,
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Cerrar modal de carga
                Swal.close();
                
                let errorMessage = 'No se pudo eliminar el permiso';
                
                if (error.status) {
                    errorMessage += ` (Código: ${error.status})`;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error al Eliminar',
                    text: errorMessage,
                    confirmButtonText: 'Entendido'
                });
            });
        },

        // Función para mostrar detalles del permiso
        showPermissionDetails: function(permissionId) {
            
            const button = document.querySelector(`[data-id="${permissionId}"]`);
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            }
            
            // Mostrar loading global
            Swal.fire({
                title: 'Cargando detalles...',
                text: 'Obteniendo información del permiso',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`${PERMISSIONS_CONFIG.routes.show}/${permissionId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                
                if (data.status === 'success' && data.permission) {
                    // Cerrar loading
                    Swal.close();
                    
                    // Usar la función global para abrir el modal
                    const modalData = {
                        name: data.permission.name,
                        guard: data.permission.guard_name,
                        roles: data.permission.roles_count + ' rol(es)',
                        users: data.permission.users_count + ' usuario(s)',
                        created_at: data.permission.created_at,
                        updated_at: data.permission.updated_at
                    };
                    
                    // Intentar abrir el modal
                    if (!window.openPermissionModal(modalData)) {
                        // Si falla, esperar un poco y reintentar
                        setTimeout(() => {
                            if (!window.openPermissionModal(modalData)) {
                                console.error('No se pudo abrir el modal después de reintentar');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo abrir el modal',
                                    confirmButtonText: 'Entendido'
                                });
                            }
                        }, 200);
                    }
                    
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al Obtener Datos',
                        text: data.message || 'No se pudieron obtener los datos del permiso',
                        confirmButtonText: 'Entendido'
                    });
                }
            })
            .catch(error => {
                console.error('Error AJAX:', error);
                
                let errorMessage = 'No se pudieron obtener los datos del permiso';
                
                if (error.status) {
                    errorMessage += ` (Código: ${error.status})`;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error al Obtener Datos',
                    text: errorMessage,
                    confirmButtonText: 'Entendido'
                });
            })
            .finally(() => {
                // Restaurar botón
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
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
        }
    };
}

// ===== FUNCIONES DE EFECTOS VISUALES =====

// Función para inicializar efectos hover en filas de tabla
function initializeTableRowEffects() {
    const tableRows = document.querySelectorAll('.table-row-hover');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.background = 'linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%)';
            this.style.transform = 'scale(1.01)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.05)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.background = 'transparent';
            this.style.transform = 'scale(1)';
            this.style.boxShadow = 'none';
        });
    });
}

// Función para inicializar efectos hover en botones de acción
function initializeActionButtonEffects() {
    const actionButtons = document.querySelectorAll('.btn-show-permission, .btn-delete-permission, a[href*="edit"]');
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.1)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.2)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = this.style.boxShadow.replace('0 4px 12px rgba(0, 0, 0, 0.2)', '0 2px 8px rgba(0, 0, 0, 0.1)');
        });
    });
}

// Función para inicializar efectos hover en tarjetas de estadísticas
function initializeStatsCardEffects() {
    const statCards = document.querySelectorAll('.stats-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
        });
    });
}

// Función para inicializar efectos en inputs
function initializeInputEffects() {
    const inputs = document.querySelectorAll('input[type="text"]');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = '#667eea';
            this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';
        });
        
        input.addEventListener('blur', function() {
            this.style.borderColor = '#e2e8f0';
            this.style.boxShadow = 'none';
        });
    });
}

// Función para inicializar efectos en tarjetas de permisos
function initializePermissionCardEffects() {
    const permissionCards = document.querySelectorAll('.permission-card');
    permissionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
        });
    });
}

// Función para inicializar efectos en tarjetas móviles
function initializeMobileCardEffects() {
    const mobilePermissionCards = document.querySelectorAll('.mobile-permission-card');
    mobilePermissionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
        });
    });
}

// Función para inicializar efectos en botones de tarjetas móviles
function initializeMobileCardButtonEffects() {
    const mobileCardButtons = document.querySelectorAll('.mobile-permission-card .card-actions button, .mobile-permission-card .card-actions a');
    mobileCardButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.2)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.1)';
        });
    });
}

// ===== FUNCIONES DE VISTA =====

// Función para manejar la responsividad de vistas
function handleResponsiveView() {
    const isMobile = window.innerWidth <= 768;
    const tableView = document.getElementById('tableView');
    const cardsView = document.getElementById('cardsView');
    const mobileCardsView = document.getElementById('mobileCardsView');
    const viewToggles = document.querySelectorAll('.view-toggle');
    
    if (isMobile) {
        // En móviles, siempre mostrar tarjetas móviles
        if (tableView) tableView.style.display = 'none';
        if (cardsView) cardsView.style.display = 'none';
        if (mobileCardsView) mobileCardsView.style.display = 'block';
        
        // Desactivar toggles en móviles
        viewToggles.forEach(toggle => {
            toggle.style.opacity = '0.5';
            toggle.style.pointerEvents = 'none';
        });
    } else {
        // En desktop, permitir cambio de vista
        if (mobileCardsView) mobileCardsView.style.display = 'none';
        viewToggles.forEach(toggle => {
            toggle.style.opacity = '1';
            toggle.style.pointerEvents = 'auto';
        });
        
        // Mantener la vista seleccionada
        const activeToggle = document.querySelector('.view-toggle.active');
        if (activeToggle) {
            const viewType = activeToggle.dataset.view;
            if (viewType === 'table') {
                if (tableView) tableView.style.display = 'block';
                if (cardsView) cardsView.style.display = 'none';
            } else {
                if (tableView) tableView.style.display = 'none';
                if (cardsView) cardsView.style.display = 'block';
            }
        }
    }
}

// Función para inicializar toggles de vista
function initializeViewToggles() {
    const viewToggles = document.querySelectorAll('.view-toggle');
    const tableView = document.getElementById('tableView');
    const cardsView = document.getElementById('cardsView');
    
    viewToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            // Solo permitir en desktop
            if (window.innerWidth <= 768) return;
            
            // Remover clase active de todos los toggles
            viewToggles.forEach(t => {
                t.classList.remove('active');
                t.style.background = 'white';
                t.style.color = '#64748b';
                t.style.border = '2px solid #e2e8f0';
            });
            
            // Agregar clase active al toggle clickeado
            this.classList.add('active');
            this.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            this.style.color = 'white';
            this.style.border = 'none';
            
            // Cambiar vista
            const viewType = this.dataset.view;
            if (viewType === 'table') {
                if (tableView) tableView.style.display = 'block';
                if (cardsView) cardsView.style.display = 'none';
            } else {
                if (tableView) tableView.style.display = 'none';
                if (cardsView) cardsView.style.display = 'block';
            }
        });
        
        // Efectos hover para toggles
        toggle.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.1)';
            }
        });
        
        toggle.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            }
        });
    });
}

// ===== FUNCIONES DE BÚSQUEDA SPA =====

// Función para inicializar búsqueda
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            const searchTerm = this.value.trim();
            performSPASearch(searchTerm);
        }, PERMISSIONS_CONFIG.search.debounceTime);
    });
}

// Función para realizar búsqueda SPA
function performSPASearch(searchTerm) {
    // Mostrar loading
    showSearchLoading();
    
    // Construir URL con parámetros de búsqueda
    const url = new URL(window.location.href);
    url.searchParams.set('search', searchTerm);
    
    // Realizar petición AJAX
    fetch(url.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            updateViewsWithSearchResults(data.data, searchTerm);
            hideSearchLoading();
        } else {
            throw new Error(data.message || 'Error en la búsqueda');
        }
    })
    .catch(error => {
        console.error('Error en búsqueda:', error);
        hideSearchLoading();
        showSearchError(error.message);
    });
}

// Función para actualizar vistas con resultados de búsqueda
function updateViewsWithSearchResults(data, searchTerm) {
    const { permissions, permissions_config, total } = data;
    
    // Actualizar tabla
    updateTableView(permissions, permissions_config);
    
    // Actualizar tarjetas desktop
    updateCardsView(permissions, permissions_config);
    
    // Actualizar tarjetas móviles
    updateMobileCardsView(permissions, permissions_config);
    
    // Actualizar contador de resultados
    updateResultsCounter(total, searchTerm);
    
    // Actualizar paginación para resultados de búsqueda
    updatePaginationForSearch(total, searchTerm);
    
    // Reinicializar efectos y eventos
    reinitializeEffects();
    setupButtonEvents();
}

// Función para actualizar vista de tabla
function updateTableView(permissions, permissions_config) {
    const tbody = document.querySelector('#permissionsTable tbody');
    if (!tbody) return;
    
    let html = '';
    permissions.forEach((permission, index) => {
        html += `
            <tr class="table-row-hover">
                <td>${index + 1}</td>
                <td>
                    <span class="permission-name">${permission.name}</span>
                </td>
                <td class="text-center">
                    <span class="guard-badge">${permission.guard_name}</span>
                </td>
                <td class="text-center">
                    <span class="roles-badge">
                        ${permission.roles.length}
                        <i class="fas fa-user-shield"></i>
                    </span>
                </td>
                <td class="text-center">
                    <span class="users-badge">
                        ${permission.users_count || 0}
                        <i class="fas fa-users"></i>
                    </span>
                </td>
                <td class="text-center">${formatDate(permission.created_at)}</td>
                <td class="text-center">
                    <div class="action-buttons">
                        ${permissions_config.can_show ? `
                            <button type="button" class="action-button show" data-id="${permission.id}">
                                <i class="fas fa-eye"></i>
                                <span class="action-text">Ver</span>
                            </button>
                        ` : ''}
                        ${permissions_config.can_edit ? `
                            <a href="/permissions/${permission.id}/edit" class="action-button edit">
                                <i class="fas fa-edit"></i>
                                <span class="action-text">Editar</span>
                            </a>
                        ` : ''}
                        ${permissions_config.can_destroy ? `
                            <button type="button" class="action-button delete" data-id="${permission.id}">
                                <i class="fas fa-trash"></i>
                                <span class="action-text">Eliminar</span>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Función para actualizar vista de tarjetas
function updateCardsView(permissions, permissions_config) {
    const cardsView = document.getElementById('cardsView');
    if (!cardsView) return;
    
    let html = '<div class="row">';
    permissions.forEach((permission, index) => {
        html += `
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="permission-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 font-weight-bold">${permission.name}</h6>
                            <span class="badge badge-light">#${index + 1}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="permission-info">
                            <div class="info-row">
                                <span>Guard:</span>
                                <span class="guard-badge">${permission.guard_name}</span>
                            </div>
                            <div class="info-row">
                                <span>Roles:</span>
                                <span class="roles-badge">
                                    ${permission.roles.length}
                                    <i class="fas fa-user-shield"></i>
                                </span>
                            </div>
                            <div class="info-row">
                                <span>Usuarios:</span>
                                <span class="users-badge">
                                    ${permission.users_count || 0}
                                    <i class="fas fa-users"></i>
                                </span>
                            </div>
                            <div class="info-row">
                                <span>Creado:</span>
                                <span>${formatDate(permission.created_at).split(' ')[0]}</span>
                            </div>
                        </div>
                        <div class="card-actions">
                            ${permissions_config.can_show ? `
                                <button type="button" class="action-button show" data-id="${permission.id}" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            ` : ''}
                            ${permissions_config.can_edit ? `
                                <a href="/permissions/${permission.id}/edit" class="action-button edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            ` : ''}
                            ${permissions_config.can_destroy ? `
                                <button type="button" class="action-button delete" data-id="${permission.id}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    cardsView.innerHTML = html;
}

// Función para actualizar vista de tarjetas móviles
function updateMobileCardsView(permissions, permissions_config) {
    const mobileCardsView = document.getElementById('mobileCardsView');
    if (!mobileCardsView) return;
    
    let html = '';
    permissions.forEach((permission, index) => {
        html += `
            <div class="mobile-permission-card">
                <div class="card-header">
                    <div>
                        <h6>${permission.name}</h6>
                        <small>ID: ${permission.id}</small>
                    </div>
                    <span>#${index + 1}</span>
                </div>
                <div class="card-body">
                    <div class="permission-info">
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-shield-alt"></i>
                                Guard:
                            </span>
                            <span class="guard-badge">${permission.guard_name}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-user-shield"></i>
                                Roles Asignados:
                            </span>
                            <span class="roles-badge">
                                ${permission.roles.length}
                                <i class="fas fa-user-shield"></i>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-users"></i>
                                Usuarios:
                            </span>
                            <span class="users-badge">
                                ${permission.users_count || 0}
                                <i class="fas fa-users"></i>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-calendar"></i>
                                Creado:
                            </span>
                            <span class="info-value">${formatDate(permission.created_at)}</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        ${permissions_config.can_show ? `
                            <button type="button" class="action-button show" data-id="${permission.id}">
                                <i class="fas fa-eye"></i>
                                <span class="action-text">Ver</span>
                            </button>
                        ` : ''}
                        ${permissions_config.can_edit ? `
                            <a href="/permissions/${permission.id}/edit" class="action-button edit">
                                <i class="fas fa-edit"></i>
                                <span class="action-text">Editar</span>
                            </a>
                        ` : ''}
                        ${permissions_config.can_destroy ? `
                            <button type="button" class="action-button delete" data-id="${permission.id}">
                                <i class="fas fa-trash"></i>
                                <span class="action-text">Eliminar</span>
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    mobileCardsView.innerHTML = html;
}

// Función para mostrar loading de búsqueda
function showSearchLoading() {
    let loadingContainer = document.getElementById('searchLoading');
    if (!loadingContainer) {
        loadingContainer = document.createElement('div');
        loadingContainer.id = 'searchLoading';
        loadingContainer.className = 'search-loading';
        loadingContainer.innerHTML = `
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Buscando permisos...</span>
            </div>
        `;
        
        const tableContainer = document.querySelector('.table-responsive');
        if (tableContainer) {
            tableContainer.parentNode.insertBefore(loadingContainer, tableContainer);
        }
    }
    loadingContainer.style.display = 'flex';
}

// Función para ocultar loading de búsqueda
function hideSearchLoading() {
    const loadingContainer = document.getElementById('searchLoading');
    if (loadingContainer) {
        loadingContainer.style.display = 'none';
    }
}

// Función para mostrar error de búsqueda
function showSearchError(message) {
    let errorContainer = document.getElementById('searchError');
    if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.id = 'searchError';
        errorContainer.className = 'search-error alert alert-danger';
        errorContainer.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <span>${message}</span>
        `;
        
        const tableContainer = document.querySelector('.table-responsive');
        if (tableContainer) {
            tableContainer.parentNode.insertBefore(errorContainer, tableContainer);
        }
    }
    errorContainer.style.display = 'block';
    
    // Ocultar error después de 5 segundos
    setTimeout(() => {
        errorContainer.style.display = 'none';
    }, 5000);
}

// Función para actualizar contador de resultados (deshabilitada)
function updateResultsCounter(total, searchTerm) {
    // Remover contador si existe
    const counterElement = document.getElementById('resultsCounter');
    if (counterElement) {
        counterElement.remove();
    }
}

// Función para actualizar paginación para resultados de búsqueda
function updatePaginationForSearch(total, searchTerm) {
    const paginationContainer = document.querySelector('.pagination-container');
    if (!paginationContainer) return;
    
    // Si hay búsqueda, mostrar paginación simplificada
    if (searchTerm && searchTerm.trim() !== '') {
        const paginationInfo = paginationContainer.querySelector('.pagination-info');
        const paginationControls = paginationContainer.querySelector('.pagination-controls');
        
        if (paginationInfo) {
            paginationInfo.innerHTML = `<span>Búsqueda: "${searchTerm}" - ${total} permisos encontrados</span>`;
        }
        
        if (paginationControls) {
            // Ocultar controles de paginación en búsqueda
            paginationControls.style.display = 'none';
        }
    } else {
        // Restaurar paginación normal
        const paginationControls = paginationContainer.querySelector('.pagination-controls');
        if (paginationControls) {
            paginationControls.style.display = 'flex';
        }
    }
}

// Función para reinicializar efectos después de actualización AJAX
function reinitializeEffects() {
    initializeTableRowEffects();
    initializeActionButtonEffects();
    initializePermissionCardEffects();
    initializeMobileCardEffects();
    initializeMobileCardButtonEffects();
}

// ===== FUNCIONES DE PAGINACIÓN INTELIGENTE =====

// Función para detectar si la paginación del servidor está activa
function isServerPaginationActive() {
    const paginator = document.querySelector('.pagination-container .page-numbers a');
    return !!paginator; // existen enlaces → servidor
}

// Función para cargar una página de permisos via AJAX
async function loadPermissionsPage(url) {
    const container = document.querySelector('.space-y-6');
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
        const newTableBody = temp.querySelector('#permissionsTable tbody');
        const tableBody = document.querySelector('#permissionsTable tbody');
        if (newTableBody && tableBody) {
            tableBody.innerHTML = newTableBody.innerHTML;
        }

        // Reemplazar tarjetas móviles
        const newMobileCards = temp.querySelector('#mobileCardsView');
        const mobileCards = document.querySelector('#mobileCardsView');
        if (newMobileCards && mobileCards) {
            mobileCards.innerHTML = newMobileCards.innerHTML;
        }

        // Reemplazar tarjetas desktop
        const newCardsView = temp.querySelector('#cardsView');
        const cardsView = document.querySelector('#cardsView');
        if (newCardsView && cardsView) {
            cardsView.innerHTML = newCardsView.innerHTML;
        }

        // Reemplazar contenedor de paginación
        const newPaginationContainer = temp.querySelector('.pagination-container');
        const paginationContainer = document.querySelector('.pagination-container');
        if (newPaginationContainer && paginationContainer) {
            paginationContainer.innerHTML = newPaginationContainer.innerHTML;
        }

        // Actualizar URL sin recargar
        window.history.pushState({}, '', url);

        // Reinicializar event listeners
        reinitializeEffects();
        setupButtonEvents();
    } catch (error) {
        console.error('Error al cargar página:', error);
    } finally {
        container.style.opacity = '';
    }
}

// Función para inicializar interceptación de paginación
function initializePaginationInterception() {
    // Interceptar clicks de paginación cuando servidor está activo
    document.addEventListener('click', (e) => {
        const paginationLink = e.target.closest('.pagination-btn, .page-number');
        if (paginationLink && paginationLink.href && isServerPaginationActive()) {
            e.preventDefault();
            loadPermissionsPage(paginationLink.href);
        }
    });

    // Interceptar búsqueda para servidor
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (isServerPaginationActive()) {
                    const url = new URL(window.location.href);
                    if (searchInput.value.trim()) {
                        url.searchParams.set('search', searchInput.value.trim());
                    } else {
                        url.searchParams.delete('search');
                    }
                    loadPermissionsPage(url.toString());
                }
            }, 300);
        });
    }
}

// Función para inicializar efectos de paginación
function initializePaginationEffects() {
    const paginationLinks = document.querySelectorAll('.pagination-links a');
    paginationLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(102, 126, 234, 0.3)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 4px rgba(102, 126, 234, 0.2)';
        });
    });
}



// ===== FUNCIONES DE DEBUGBAR =====

// Función para crear debugbar
function createDebugbar() {
    if (!PERMISSIONS_CONFIG.debug.enabled) return;
    
    const debugbar = document.createElement('div');
    debugbar.id = 'debugbar';
    debugbar.className = 'debugbar';
    debugbar.innerHTML = `
        <div class="debug-info">
            <div class="debug-metric">
                <i class="fas fa-clock"></i>
                <span id="loadTime">0ms</span>
            </div>
            <div class="debug-metric">
                <i class="fas fa-memory"></i>
                <span id="memoryUsage">0MB</span>
            </div>
            <div class="debug-metric">
                <i class="fas fa-database"></i>
                <span id="dbQueries">0 queries</span>
            </div>
            <div class="debug-metric">
                <i class="fas fa-code"></i>
                <span id="phpTime">0ms</span>
            </div>
            <div class="debug-metric">
                <i class="fas fa-eye"></i>
                <span id="permissionsCount">0 permisos</span>
            </div>
            <button onclick="toggleDebugbar()" class="debug-toggle">
                <i class="fas fa-cog"></i> Debug
            </button>
        </div>
    `;
    document.body.appendChild(debugbar);
}

// Función para toggle debugbar
function toggleDebugbar() {
    const debugbar = document.getElementById('debugbar');
    if (debugbar) {
        debugbar.classList.toggle('show');
        if (debugbar.classList.contains('show')) {
            updateDebugbar();
        }
    }
}

// Función para actualizar debugbar
function updateDebugbar() {
    const loadTime = performance.now() - window.performanceStart;
    const loadTimeElement = document.getElementById('loadTime');
    if (loadTimeElement) {
        loadTimeElement.textContent = Math.round(loadTime) + 'ms';
    }
    
    // Simular métricas
    const memoryUsage = Math.round(Math.random() * 50 + 20);
    const memoryElement = document.getElementById('memoryUsage');
    if (memoryElement) {
        memoryElement.textContent = memoryUsage + 'MB';
    }
    
    const dbQueries = Math.round(Math.random() * 10 + 5);
    const dbElement = document.getElementById('dbQueries');
    if (dbElement) {
        dbElement.textContent = dbQueries + ' queries';
    }
    
    const phpTime = Math.round(Math.random() * 100 + 50);
    const phpElement = document.getElementById('phpTime');
    if (phpElement) {
        phpElement.textContent = phpTime + 'ms';
    }
    
    const permissionsCount = document.querySelectorAll('#permissionsTable tbody tr, .mobile-permission-card, .permission-card').length;
    const countElement = document.getElementById('permissionsCount');
    if (countElement) {
        countElement.textContent = permissionsCount + ' permisos';
    }
}

// ===== FUNCIONES DE EVENTOS =====

// Función para configurar eventos de botones
function setupButtonEvents() {
    // Eventos para botones de eliminar
    document.addEventListener('click', function(e) {
        if (e.target.closest('.action-button.delete')) {
            e.preventDefault();
            const permissionId = e.target.closest('.action-button.delete').dataset.id;
            permissionsIndex.deletePermission(permissionId);
        }
    });
    
    // Eventos para botones de mostrar detalles
    document.addEventListener('click', function(e) {
        if (e.target.closest('.action-button.show')) {
            e.preventDefault();
            const permissionId = e.target.closest('.action-button.show').dataset.id;
            window.lastModalTrigger = e.target.closest('.action-button.show');
            permissionsIndex.showPermissionDetails(permissionId);
        }
    });
}

// ===== FUNCIONES DE UTILIDAD =====

// Función para ajustar ancho de tabla
function adjustTableWidth() {
    const container = document.querySelector('.modern-card-body');
    const table = document.getElementById('tableView');
    if (container && table) {
        const containerWidth = container.offsetWidth;
        table.style.width = containerWidth + 'px';
        table.style.maxWidth = '100%';
    }
}

// Función para formatear números
function formatNumber(num) {
    return new Intl.NumberFormat('es-ES').format(num);
}

// Función para formatear fechas
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// ===== FUNCIONES DE INICIALIZACIÓN =====

// Función principal de inicialización
function initializePermissionsIndex() {
    
    // Marcar tiempo de inicio para debugbar
    window.performanceStart = performance.now();
    
    // Inicializar componentes
    initializeTableRowEffects();
    initializeActionButtonEffects();
    initializeStatsCardEffects();
    initializeInputEffects();
    initializePermissionCardEffects();
    initializeMobileCardEffects();
    initializeMobileCardButtonEffects();
    
    // Inicializar vistas
    handleResponsiveView();
    initializeViewToggles();
    
    // Inicializar búsqueda
    initializeSearch();
    
    // Inicializar paginación inteligente
    initializePaginationInterception();
    initializePaginationEffects();
    
    // Configurar eventos
    setupButtonEvents();
    
    // Inicializar debugbar
    createDebugbar();
    
    // Configurar eventos adicionales
    setupAdditionalEvents();
    
    // Ajustar ancho de tabla
    adjustTableWidth();
    window.addEventListener('resize', adjustTableWidth);
    window.addEventListener('resize', handleResponsiveView);
}

// Función para configurar eventos adicionales
function setupAdditionalEvents() {
    // Mostrar debugbar con Ctrl+Shift+D
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.key === 'D') {
            e.preventDefault();
            toggleDebugbar();
        }
    });
    
    // Actualizar debugbar cada 5 segundos si está visible
    setInterval(function() {
        const debugbar = document.getElementById('debugbar');
        if (debugbar && debugbar.classList.contains('show')) {
            updateDebugbar();
        }
    }, 5000);
}

// ===== EXPONER FUNCIONES GLOBALMENTE =====
if (typeof window.initializePermissionsIndex === 'undefined') {
    window.initializePermissionsIndex = initializePermissionsIndex;
}
if (typeof window.toggleDebugbar === 'undefined') {
    window.toggleDebugbar = toggleDebugbar;
}
if (typeof window.formatNumber === 'undefined') {
    window.formatNumber = formatNumber;
}
if (typeof window.formatDate === 'undefined') {
    window.formatDate = formatDate;
}

// ===== INICIALIZAR CUANDO EL DOM ESTÉ LISTO =====
if (!window.permissionsIndexInitialized) {
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.permissionsIndexInitialized) {
            initializePermissionsIndex();
            window.permissionsIndexInitialized = true;
        }
    });
}

// ===== COMPONENTE ALPINE.JS PARA EL MODAL =====
function permissionModal() {
	return {
		isOpen: false,
		permissionData: {
			name: '',
			guard: '',
			roles: '0',
			users: '0',
			created_at: '',
			updated_at: ''
		},
		
		// Se ejecuta al inicializar el componente
		init() {
			// Escuchar evento global para abrir el modal desde JS externo
			window.addEventListener('open-permission-modal', (event) => {
				if (event && event.detail) {
					this.updateData(event.detail);
					this.openModal();
				}
			});
		},
		
		openModal() {
			this.isOpen = true;
			// Prevenir scroll del body
			document.body.style.overflow = 'hidden';
		},
		
		closeModal() {
			this.isOpen = false;
			// Restaurar scroll del body
			document.body.style.overflow = '';
		},
		
		// Método para actualizar datos desde fuera del componente
		updateData(data) {
			this.permissionData = {
				name: data.name || '',
				guard: data.guard || '',
				roles: data.roles || '0',
				users: data.users || '0',
				created_at: data.created_at || '',
				updated_at: data.updated_at || ''
			};
		}
	}
}

// ===== FUNCIÓN GLOBAL PARA MANEJAR EL MODAL =====
if (typeof window.openPermissionModal === 'undefined') {
	window.openPermissionModal = function(permissionData) {
		try {
			// Despachar evento global; el componente Alpine lo recibirá en su init()
			window.dispatchEvent(new CustomEvent('open-permission-modal', { detail: permissionData }));
			return true;
		} catch (err) {
			console.error('Error al despachar evento del modal', err);
			return false;
		}
	};
}
