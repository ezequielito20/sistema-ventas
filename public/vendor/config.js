// Configuración centralizada de archivos locales
window.VendorAssets = {
    // DataTables
    datatables: {
        css: {
            core: '/vendor/datatables/dataTables.bootstrap4.min.css',
            responsive: '/vendor/datatables/responsive.bootstrap4.min.css',
            buttons: '/vendor/datatables/buttons.bootstrap4.min.css'
        },
        js: {
            core: '/vendor/datatables/jquery.dataTables.min.js',
            bootstrap4: '/vendor/datatables/dataTables.bootstrap4.min.js',
            responsive: '/vendor/datatables/dataTables.responsive.min.js',
            responsiveBootstrap4: '/vendor/datatables/responsive.bootstrap4.min.js',
            buttons: '/vendor/datatables/dataTables.buttons.min.js',
            buttonsBootstrap4: '/vendor/datatables/buttons.bootstrap4.min.js',
            buttonsHtml5: '/vendor/datatables/buttons.html5.min.js',
            buttonsPrint: '/vendor/datatables/buttons.print.min.js',
            buttonsColVis: '/vendor/datatables/buttons.colVis.min.js'
        }
    },
    
    // SweetAlert2
    sweetalert2: {
        css: '/vendor/sweetalert2/sweetalert2.min.css',
        js: '/vendor/sweetalert2/sweetalert2.min.js'
    },
    
    // Select2
    select2: {
        css: {
            core: '/vendor/select2/select2.min.css',
            bootstrap4: '/vendor/select2/select2-bootstrap4.min.css'
        },
        js: '/vendor/select2/select2.min.js'
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

// Función para cargar DataTables completo
window.loadDataTables = function(callback) {
    loadVendorCSS(VendorAssets.datatables.css.core);
    loadVendorCSS(VendorAssets.datatables.css.responsive);
    loadVendorCSS(VendorAssets.datatables.css.buttons);
    
    loadVendorJS(VendorAssets.datatables.js.core, function() {
        loadVendorJS(VendorAssets.datatables.js.bootstrap4, function() {
            loadVendorJS(VendorAssets.datatables.js.responsive, function() {
                loadVendorJS(VendorAssets.datatables.js.responsiveBootstrap4, function() {
                    loadVendorJS(VendorAssets.datatables.js.buttons, function() {
                        loadVendorJS(VendorAssets.datatables.js.buttonsBootstrap4, function() {
                            loadVendorJS(VendorAssets.datatables.js.buttonsHtml5, function() {
                                loadVendorJS(VendorAssets.datatables.js.buttonsPrint, function() {
                                    loadVendorJS(VendorAssets.datatables.js.buttonsColVis, callback);
                                });
                            });
                        });
                    });
                });
            });
        });
    });
};

// Función para cargar SweetAlert2
window.loadSweetAlert2 = function(callback) {
    loadVendorCSS(VendorAssets.sweetalert2.css);
    loadVendorJS(VendorAssets.sweetalert2.js, callback);
};

// Función para cargar Select2
window.loadSelect2 = function(callback) {
    loadVendorCSS(VendorAssets.select2.css.core);
    loadVendorCSS(VendorAssets.select2.css.bootstrap4);
    loadVendorJS(VendorAssets.select2.js, callback);
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