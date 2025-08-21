/**
 * Sistema de carga optimizada para archivos CSS y JS
 * Archivo: public/js/shared/loader.js
 * Versión: 1.0.0
 */

// ===== SISTEMA DE CARGA OPTIMIZADA =====

class ResourceLoader {
    constructor() {
        this.loadedCSS = new Set();
        this.loadedJS = new Set();
        this.loadingPromises = new Map();
    }

    /**
     * Cargar archivo CSS de forma asíncrona
     * @param {string} href - URL del archivo CSS
     * @param {Object} options - Opciones de carga
     * @returns {Promise} - Promise que se resuelve cuando se carga el CSS
     */
    async loadCSS(href, options = {}) {
        if (this.loadedCSS.has(href)) {
            return Promise.resolve();
        }

        if (this.loadingPromises.has(href)) {
            return this.loadingPromises.get(href);
        }

        const promise = new Promise((resolve, reject) => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            
            if (options.media) {
                link.media = options.media;
            }

            link.onload = () => {
                this.loadedCSS.add(href);
                this.loadingPromises.delete(href);
                resolve();
            };

            link.onerror = () => {
                this.loadingPromises.delete(href);
                reject(new Error(`Error cargando CSS: ${href}`));
            };

            document.head.appendChild(link);
        });

        this.loadingPromises.set(href, promise);
        return promise;
    }

    /**
     * Cargar archivo JavaScript de forma asíncrona
     * @param {string} src - URL del archivo JS
     * @param {Object} options - Opciones de carga
     * @returns {Promise} - Promise que se resuelve cuando se carga el JS
     */
    async loadJS(src, options = {}) {
        if (this.loadedJS.has(src)) {
            return Promise.resolve();
        }

        if (this.loadingPromises.has(src)) {
            return this.loadingPromises.get(src);
        }

        const promise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.async = options.async !== false;
            script.defer = options.defer || false;
            
            if (options.type) {
                script.type = options.type;
            }

            script.onload = () => {
                this.loadedJS.add(src);
                this.loadingPromises.delete(src);
                resolve();
            };

            script.onerror = () => {
                this.loadingPromises.delete(src);
                reject(new Error(`Error cargando JS: ${src}`));
            };

            document.head.appendChild(script);
        });

        this.loadingPromises.set(src, promise);
        return promise;
    }

    /**
     * Precargar recursos críticos
     * @param {Array} resources - Array de recursos a precargar
     */
    preloadResources(resources) {
        resources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = resource.href;
            link.as = resource.as || 'fetch';
            
            if (resource.crossorigin) {
                link.crossOrigin = resource.crossorigin;
            }

            document.head.appendChild(link);
        });
    }

    /**
     * Cargar recursos por vista
     * @param {string} view - Nombre de la vista
     * @param {Object} options - Opciones de carga
     */
    async loadViewResources(view, options = {}) {
        const cssPath = `/css/admin/${view}/index.css`;
        const jsPath = `/js/admin/${view}/index.js`;

        try {
            // Cargar CSS y JS en paralelo
            await Promise.all([
                this.loadCSS(cssPath, { media: 'all' }),
                this.loadJS(jsPath, { async: true })
            ]);

        } catch (error) {
        }
    }

    /**
     * Cargar recursos compartidos
     */
    async loadSharedResources() {
        const sharedResources = [
            { href: '/css/shared/components.css', as: 'style' },
            { href: '/js/shared/utils.js', as: 'script' }
        ];

        try {
            await Promise.all([
                this.loadCSS('/css/shared/components.css'),
                this.loadJS('/js/shared/utils.js')
            ]);

        } catch (error) {
            console.error('Error cargando recursos compartidos:', error);
        }
    }

    /**
     * Verificar si un recurso está cargado
     * @param {string} url - URL del recurso
     * @param {string} type - Tipo de recurso ('css' o 'js')
     * @returns {boolean} - True si está cargado
     */
    isLoaded(url, type) {
        if (type === 'css') {
            return this.loadedCSS.has(url);
        } else if (type === 'js') {
            return this.loadedJS.has(url);
        }
        return false;
    }

    /**
     * Limpiar recursos no utilizados
     */
    cleanup() {
        this.loadingPromises.clear();
    }
}

// ===== SISTEMA DE RUTAS INTELIGENTE =====

class RouteManager {
    constructor(loader) {
        this.loader = loader;
        this.currentView = null;
        this.viewCache = new Map();
    }

    /**
     * Navegar a una vista específica
     * @param {string} view - Nombre de la vista
     * @param {Object} options - Opciones de navegación
     */
    async navigateToView(view, options = {}) {
        if (this.currentView === view) {
            return;
        }

        try {
            // Cargar recursos de la vista
            await this.loader.loadViewResources(view, options);
            
            // Actualizar vista actual
            this.currentView = view;
            
            // Ejecutar callback de inicialización si existe
            if (options.onLoad && typeof options.onLoad === 'function') {
                options.onLoad();
            }

        } catch (error) {
            console.error(`Error navegando a vista ${view}:`, error);
        }
    }

    /**
     * Precargar vista
     * @param {string} view - Nombre de la vista
     */
    async preloadView(view) {
        if (this.viewCache.has(view)) {
            return;
        }

        try {
            await this.loader.loadViewResources(view, { preload: true });
            this.viewCache.set(view, true);
        } catch (error) {
            console.error(`Error precargando vista ${view}:`, error);
        }
    }

    /**
     * Obtener vista actual
     * @returns {string|null} - Nombre de la vista actual
     */
    getCurrentView() {
        return this.currentView;
    }
}

// ===== SISTEMA DE CACHE INTELIGENTE =====

class CacheManager {
    constructor() {
        this.cache = new Map();
        this.maxSize = 50; // Máximo número de elementos en cache
    }

    /**
     * Guardar en cache
     * @param {string} key - Clave del cache
     * @param {any} value - Valor a guardar
     * @param {number} ttl - Tiempo de vida en segundos
     */
    set(key, value, ttl = 3600) {
        // Limpiar cache si está lleno
        if (this.cache.size >= this.maxSize) {
            this.cleanup();
        }

        const item = {
            value: value,
            timestamp: Date.now(),
            ttl: ttl * 1000
        };

        this.cache.set(key, item);
    }

    /**
     * Obtener del cache
     * @param {string} key - Clave del cache
     * @returns {any|null} - Valor o null si no existe o expiró
     */
    get(key) {
        const item = this.cache.get(key);
        if (!item) return null;

        const now = Date.now();
        if (now - item.timestamp > item.ttl) {
            this.cache.delete(key);
            return null;
        }

        return item.value;
    }

    /**
     * Limpiar cache
     */
    cleanup() {
        const now = Date.now();
        for (const [key, item] of this.cache.entries()) {
            if (now - item.timestamp > item.ttl) {
                this.cache.delete(key);
            }
        }
    }

    /**
     * Limpiar todo el cache
     */
    clear() {
        this.cache.clear();
    }
}

// ===== INICIALIZACIÓN GLOBAL =====

// Crear instancias globales
window.resourceLoader = new ResourceLoader();
window.routeManager = new RouteManager(window.resourceLoader);
window.cacheManager = new CacheManager();

// Cargar recursos compartidos al inicio
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await window.resourceLoader.loadSharedResources();
    } catch (error) {
    }
});

// ===== FUNCIONES DE CONVENIENCIA =====

/**
 * Cargar vista rápidamente
 * @param {string} view - Nombre de la vista
 * @param {Object} options - Opciones
 */
window.loadView = async (view, options = {}) => {
    return await window.routeManager.navigateToView(view, options);
};

/**
 * Precargar vista
 * @param {string} view - Nombre de la vista
 */
window.preloadView = async (view) => {
    return await window.routeManager.preloadView(view);
};

/**
 * Cargar CSS
 * @param {string} href - URL del CSS
 * @param {Object} options - Opciones
 */
window.loadCSS = async (href, options = {}) => {
    return await window.resourceLoader.loadCSS(href, options);
};

/**
 * Cargar JS
 * @param {string} src - URL del JS
 * @param {Object} options - Opciones
 */
window.loadJS = async (src, options = {}) => {
    return await window.resourceLoader.loadJS(src, options);
};

// ===== DETECCIÓN AUTOMÁTICA DE VISTA =====

// Detectar vista actual basada en la URL
function detectCurrentView() {
    const path = window.location.pathname;
    const matches = path.match(/\/admin\/([^\/]+)/);
    
    if (matches) {
        const view = matches[1];
        window.routeManager.currentView = view;
        
        // Cargar recursos de la vista actual
        window.resourceLoader.loadViewResources(view).catch(error => {
            console.error(`Error cargando recursos de vista actual ${view}:`, error);
        });
    }
}

// Ejecutar detección cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', detectCurrentView);
} else {
    detectCurrentView();
}
