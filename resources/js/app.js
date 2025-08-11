import './bootstrap';
import Alpine from 'alpinejs';

// Verificar si Alpine ya está inicializado para evitar conflictos
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}
