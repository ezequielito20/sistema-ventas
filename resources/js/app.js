import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import { notifications } from './ui/notifications';

window.Chart = Chart;

/**
 * Componente Alpine para galeria del formulario de producto.
 * Registrado ANTES de Alpine.start() para que funcione siempre.
 */
Alpine.data('productGallery', () => ({
    dragging: false,
    handleDrop(e) {
        this.dragging = false;
        const files = e.dataTransfer.files;
        if (!files.length) return;
        const input = this.$refs.fileInput;
        const dt = new DataTransfer();
        for (const f of files) {
            dt.items.add(f);
        }
        input.files = dt.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    },
}));

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

/**
 * Vista previa local del formulario de producto (Livewire).
 * Delegación en document: Livewire puede reemplazar el input con wire:model y borrar x-on:change / Alpine.
 * IDs definidos en resources/views/livewire/product-form.blade.php
 */
(function () {
    const INPUT_ID = 'product-form-image-input';
    const ROOT_ID = 'product-form-preview-root';
    const IMG_ID = 'product-form-preview-img';
    const EMPTY_ID = 'product-form-preview-empty';

    let currentObjectUrl = null;

    function revokeBlob() {
        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = null;
        }
    }

    function isLikelyImage(file) {
        if (!file) {
            return false;
        }
        if (file.type && /^image\/(jpeg|png|gif|webp)/.test(file.type)) {
            return true;
        }
        const name = (file.name || '').toLowerCase();

        return /\.(jpe?g|png|gif|webp)$/i.test(name);
    }

    function applyPreview(file) {
        const root = document.getElementById(ROOT_ID);
        const img = document.getElementById(IMG_ID);
        const empty = document.getElementById(EMPTY_ID);
        if (!root || !img) {
            return;
        }

        const existing = root.getAttribute('data-existing-url') || '';

        if (!file) {
            revokeBlob();
            if (existing) {
                img.src = existing;
                img.classList.remove('hidden');
            } else {
                img.removeAttribute('src');
                img.classList.add('hidden');
            }
            if (empty) {
                empty.classList.toggle('hidden', !!existing);
            }

            return;
        }

        if (!isLikelyImage(file)) {
            return;
        }

        revokeBlob();
        currentObjectUrl = URL.createObjectURL(file);
        img.src = currentObjectUrl;
        img.classList.remove('hidden');
        if (empty) {
            empty.classList.add('hidden');
        }
    }

    document.addEventListener(
        'change',
        function (event) {
            const el = event.target;
            if (!el || el.id !== INPUT_ID || el.type !== 'file') {
                return;
            }
            const file = el.files && el.files[0];
            applyPreview(file || null);
        },
        true
    );
})();
