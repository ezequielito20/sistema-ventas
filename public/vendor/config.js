// Configuración centralizada de archivos locales
window.VendorAssets = {
    // SweetAlert2
    sweetalert2: {
        css: '/vendor/sweetalert2/sweetalert2.min.css',
        js: '/vendor/sweetalert2/sweetalert2.min.js'
    },
    
    // Chart.js
    chartjs: {
        js: '/vendor/chartjs/chart.min.js'
    },
    
    // Animate.css
    animate: {
        css: '/vendor/animate-css/animate.min.css'
    },
    
    // jQuery Inputmask
    inputmask: {
        js: '/vendor/inputmask/jquery.inputmask.min.js'
    },
    
    // PDFMake
    pdfmake: {
        js: '/vendor/pdfmake/pdfmake.min.js',
        vfs: '/vendor/pdfmake/vfs_fonts.js'
    },
    
    // JSZip
    jszip: {
        js: '/vendor/jszip/jszip.min.js'
    },
    
    // Alpine.js
    alpinejs: {
        js: '/vendor/alpinejs/alpine.min.js'
    },
    
    // FontAwesome
    fontawesome: {
        css: {
            all: '/vendor/fontawesome-free/css/all.min.css',
            solid: '/vendor/fontawesome-free/css/solid.min.css',
            regular: '/vendor/fontawesome-free/css/regular.min.css',
            brands: '/vendor/fontawesome-free/css/brands.min.css'
        }
    }
};

// Función para cargar CSS dinámicamente
window.loadVendorCSS = function(url) {
    if (!document.querySelector(`link[href="${url}"]`)) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = url;
        document.head.appendChild(link);
    }
};

// Función para cargar JS dinámicamente
window.loadVendorJS = function(url, callback) {
    if (!document.querySelector(`script[src="${url}"]`)) {
        const script = document.createElement('script');
        script.src = url;
        if (callback) {
            script.onload = callback;
        }
        document.head.appendChild(script);
    } else if (callback) {
        callback();
    }
};

// Función para cargar SweetAlert2
window.loadSweetAlert2 = function(callback) {
    loadVendorCSS(VendorAssets.sweetalert2.css);
    loadVendorJS(VendorAssets.sweetalert2.js, callback);
};

// Función para cargar Chart.js
window.loadChartJS = function(callback) {
    loadVendorJS(VendorAssets.chartjs.js, callback);
};

// Función para cargar Animate.css
window.loadAnimateCSS = function() {
    loadVendorCSS(VendorAssets.animate.css);
};

// Función para cargar Inputmask
window.loadInputmask = function(callback) {
    loadVendorJS(VendorAssets.inputmask.js, callback);
};

// Función para cargar PDFMake
window.loadPDFMake = function(callback) {
    loadVendorJS(VendorAssets.pdfmake.js, function() {
        loadVendorJS(VendorAssets.pdfmake.vfs, callback);
    });
};

// Función para cargar JSZip
window.loadJSZip = function(callback) {
    loadVendorJS(VendorAssets.jszip.js, callback);
};

// Función para cargar Alpine.js
window.loadAlpineJS = function(callback) {
    loadVendorJS(VendorAssets.alpinejs.js, callback);
};

// Función para cargar FontAwesome
window.loadFontAwesome = function(type = 'all') {
    const cssFile = VendorAssets.fontawesome.css[type] || VendorAssets.fontawesome.css.all;
    loadVendorCSS(cssFile);
};
