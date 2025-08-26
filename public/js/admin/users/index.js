function usersApp() {
    return {
        viewMode: 'table',
        searchTerm: '',
        showModal: false,
        selectedUser: {},
        isLoading: false,

        init() {
            // Cargar modo de vista guardado
            const savedViewMode = localStorage.getItem('usersViewMode');
            if (savedViewMode && (savedViewMode === 'table' || savedViewMode === 'cards')) {
                this.viewMode = savedViewMode;
            }
            
            // Inicializar interceptación de paginación
            this.initializePagination();
        },

        // Detectar si la vista usa paginación del servidor
        isServerPaginationActive() {
            const paginator = document.querySelector('.pagination-container .page-numbers a');
            return !!paginator; // existen enlaces → servidor
        },

        // Cargar una URL y reemplazar secciones sin recargar
        async loadUsersPage(url) {
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
                const newTableBody = temp.querySelector('#usersTable tbody');
                const tableBody = document.querySelector('#usersTable tbody');
                if (newTableBody && tableBody) {
                    tableBody.innerHTML = newTableBody.innerHTML;
                }

                // Reemplazar tarjetas de escritorio
                const newDesktopCards = temp.querySelector('.desktop-view .users-grid');
                const desktopCards = document.querySelector('.desktop-view .users-grid');
                if (newDesktopCards && desktopCards) {
                    desktopCards.innerHTML = newDesktopCards.innerHTML;
                }

                // Reemplazar tarjetas móviles
                const newMobileCards = temp.querySelector('.mobile-view .users-grid');
                const mobileCards = document.querySelector('.mobile-view .users-grid');
                if (newMobileCards && mobileCards) {
                    mobileCards.innerHTML = newMobileCards.innerHTML;
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
                    this.loadUsersPage(paginationLink.href);
                }
            });

            // Interceptar búsqueda para servidor
            const searchInput = document.querySelector('.search-input');
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
                            this.loadUsersPage(url.toString());
                        }
                    }, 300);
                });
            }
        },

        changeViewMode(mode) {
            this.viewMode = mode;
            localStorage.setItem('usersViewMode', mode);
        },

        isUserVisible(userData) {
            // Si hay paginación del servidor, no filtrar en el cliente
            if (this.isServerPaginationActive()) return true;
            
            if (!this.searchTerm) return true;
            return userData.toLowerCase().includes(this.searchTerm.toLowerCase());
        },



        async showUserDetails(userId) {
            this.isLoading = true;
            this.showModal = true;
            
            try {
                const response = await fetch(`/users/${userId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.selectedUser = {
                        name: data.user.name,
                        email: data.user.email,
                        company_name: data.user.company_name || 'N/A',
                        roles_html: data.user.roles.map(role => 
                            `<span class="badge badge-role">${role.display_name}</span>`
                        ).join('') || '<span class="text-muted">Sin rol asignado</span>',
                        verification_html: data.user.verified ? 
                            '<span class="badge badge-success">Verificado</span>' : 
                            '<span class="badge badge-warning">Pendiente de verificación</span>'
                    };
                } else {
                    this.showNotification('Error', 'No se pudieron obtener los datos del usuario', 'error');
                }
            } catch (error) {
                console.error('Error en showUserDetails:', error);
                this.showNotification('Error', 'No se pudieron obtener los datos del usuario', 'error');
            } finally {
                this.isLoading = false;
            }
        },

        async deleteUser(userId, userName) {
            const confirmed = await this.showConfirmDialog(
                '¿Estás seguro?',
                `¿Deseas eliminar al usuario <strong>${userName}</strong>?<br><small class="text-muted">Esta acción no se puede revertir</small>`,
                'warning'
            );

            if (!confirmed) return;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                const response = await fetch(`/users/delete/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.showNotification('¡Eliminado!', data.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showNotification('Error', data.message, 'error');
                }
            } catch (error) {
                console.error('Error en deleteUser:', error);
                this.showNotification('Error', 'No se pudo eliminar el usuario', 'error');
            }
        },

        closeModal() {
            this.showModal = false;
            this.selectedUser = {};
        },

        async showConfirmDialog(title, html, icon) {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: title,
                    html: html,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: '#e74c3c',
                    cancelButtonColor: '#95a5a6',
                    confirmButtonText: '<i class="fas fa-trash mr-2"></i>Sí, eliminar',
                    cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
                    reverseButtons: true
                });
                return result.isConfirmed;
            } else {
                return confirm(title);
            }
        },

        showNotification(title, text, icon) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    confirmButtonText: 'Entendido'
                });
            } else {
                alert(`${title}: ${text}`);
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
});
