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
            
        },

        changeViewMode(mode) {
            this.viewMode = mode;
            localStorage.setItem('usersViewMode', mode);
        },

        isUserVisible(userData) {
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
