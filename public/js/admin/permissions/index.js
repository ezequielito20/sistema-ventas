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
                if (data.status === 'success') {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: data.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire(
                        'Error',
                        data.message,
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
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

// ===== FUNCIONES DE BÚSQUEDA =====

// Función para inicializar búsqueda
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            const searchTerm = this.value.toLowerCase();
            performSearch(searchTerm);
        }, PERMISSIONS_CONFIG.search.debounceTime);
    });
}

// Función para realizar búsqueda
function performSearch(searchTerm) {
    const tableRows = document.querySelectorAll('#permissionsTable tbody tr');
    const mobileCards = document.querySelectorAll('.mobile-permission-card');
    const desktopCards = document.querySelectorAll('.permission-card');
    
    let visibleCount = 0;
    
    // Buscar en tabla
    tableRows.forEach(row => {
        const permissionName = row.querySelector('.permission-name')?.textContent.toLowerCase() || '';
        const guardName = row.querySelector('.guard-badge')?.textContent.toLowerCase() || '';
        
        if (permissionName.includes(searchTerm) || guardName.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Buscar en tarjetas móviles
    mobileCards.forEach(card => {
        const permissionName = card.querySelector('h6')?.textContent.toLowerCase() || '';
        const guardName = card.querySelector('.info-row .info-value')?.textContent.toLowerCase() || '';
        
        if (permissionName.includes(searchTerm) || guardName.includes(searchTerm)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Buscar en tarjetas desktop
    desktopCards.forEach(card => {
        const permissionName = card.querySelector('h6')?.textContent.toLowerCase() || '';
        const guardName = card.querySelector('.badge-info')?.textContent.toLowerCase() || '';
        
        if (permissionName.includes(searchTerm) || guardName.includes(searchTerm)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Mostrar mensaje si no hay resultados
    showSearchResults(visibleCount);
}

// Función para mostrar resultados de búsqueda
function showSearchResults(count) {
    let messageContainer = document.getElementById('searchResultsMessage');
    
    if (count === 0) {
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.id = 'searchResultsMessage';
            messageContainer.className = 'alert alert-info text-center mt-3';
            messageContainer.innerHTML = '<i class="fas fa-search"></i> No se encontraron permisos que coincidan con la búsqueda';
            
            const tableContainer = document.querySelector('.table-responsive');
            if (tableContainer) {
                tableContainer.parentNode.insertBefore(messageContainer, tableContainer.nextSibling);
            }
        }
        messageContainer.style.display = 'block';
    } else {
        if (messageContainer) {
            messageContainer.style.display = 'none';
        }
    }
}

// ===== FUNCIONES DE PAGINACIÓN =====

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
    
    // Inicializar paginación
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
