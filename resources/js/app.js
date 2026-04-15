import Alpine from 'alpinejs';
import { notifications } from './ui/notifications';

// Inicializar Alpine.js solo si no está ya inicializado
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}

// Configurar axios para CSRF
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Funciones globales útiles
window.appHelpers = {
    // Mostrar notificación toast
    showToast(message, type = 'success') {
        notifications.showToast(message, {
            type,
            title: type === 'success' ? 'Exito' : undefined,
        });
    },
    
    // Confirmar acción
    confirmAction(message, callback) {
        notifications.confirmDialog({
            title: 'Confirmar accion',
            text: message,
            type: 'warning',
            confirmText: 'Si, continuar',
            cancelText: 'Cancelar',
        }).then((confirmed) => {
            if (confirmed && typeof callback === 'function') {
                callback();
            }
        });
    },
    
    // Formatear moneda
    formatCurrency(amount) {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    },
    
    // Formatear fecha
    formatDate(date) {
        return new Intl.DateTimeFormat('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(date));
    }
};

// Exponer API moderna global para migrar modulos progresivamente.
window.uiNotifications = notifications;
