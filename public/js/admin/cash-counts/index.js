/**
 * JavaScript optimizado para cash-counts/index
 * Archivo: public/js/admin/cash-counts/index.js
 * Versión: 1.0.0
 */

// Script de prueba para verificar carga
console.log('✅ cash-counts/index.js cargado correctamente');

// Importar utilidades compartidas
// Las utilidades ya están disponibles globalmente desde utils.js

// ===== VARIABLES GLOBALES =====
let table;
let ajaxInProgress = false;

// ===== FUNCIÓN DE INICIALIZACIÓN SIMPLIFICADA =====

/**
 * Inicializar la aplicación cuando el DOM esté listo
 */
function initializeApp() {
    console.log('🚀 Inicializando aplicación cash-counts/index...');
    
    // Verificar que las utilidades estén disponibles
    if (typeof window.utils === 'undefined') {
        console.error('❌ utils.js no está cargado. Esperando...');
        setTimeout(initializeApp, 100);
        return;
    }
    
    console.log('✅ Utilidades cargadas correctamente');
    
    // Verificar que SweetAlert2 esté disponible
    if (typeof Swal === 'undefined') {
        console.warn('⚠️ SweetAlert2 no está cargado');
    } else {
        console.log('✅ SweetAlert2 cargado correctamente');
    }
    
    // Verificar que jQuery esté disponible (para DataTables)
    if (typeof $ === 'undefined') {
        console.warn('⚠️ jQuery no está cargado (necesario para DataTables)');
    } else {
        console.log('✅ jQuery cargado correctamente');
    }
    
    // Inicializar DataTable solo si existe la tabla y jQuery está disponible
    const tableElement = document.getElementById('cashCountsTable');
    if (tableElement && typeof $ !== 'undefined') {
        console.log('📊 Inicializando DataTable...');
        initializeDataTable();
    } else {
        console.log('⏭️ Saltando inicialización de DataTable');
    }

    // Event listeners básicos
    setupEventListeners();
    
    // Responsive
    adjustViewForScreenSize();
    window.addEventListener('resize', window.utils.throttle(adjustViewForScreenSize, 250));
    
    console.log('🎉 Aplicación cash-counts/index inicializada correctamente');
}

/**
 * Configurar event listeners básicos
 */
function setupEventListeners() {
    document.addEventListener('click', function(event) {
        // Cerrar caja
        if (event.target.closest('.close-cash-count')) {
            const cashCountId = event.target.closest('.close-cash-count').dataset.id;
            closeCashCount(cashCountId);
        }
        
        // Eliminar
        if (event.target.closest('.delete-cash-count')) {
            const cashCountId = event.target.closest('.delete-cash-count').dataset.id;
            deleteCashCount(cashCountId);
        }
    });
}

// ===== FUNCIONES BÁSICAS =====

/**
 * Cerrar caja con confirmación
 */
async function closeCashCount(cashCountId) {
    console.log('🔒 Cerrando caja:', cashCountId);
    
    if (typeof Swal === 'undefined') {
        if (confirm('¿Estás seguro de que deseas cerrar esta caja?')) {
            console.log('Caja cerrada (confirmación nativa)');
        }
        return;
    }
    
    const result = await Swal.fire({
        title: '¿Cerrar Caja?',
        text: "¿Estás seguro de que deseas cerrar esta caja?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#48bb78',
        cancelButtonColor: '#718096',
        confirmButtonText: 'Sí, cerrar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        console.log('Caja cerrada (SweetAlert2)');
        // Aquí iría la lógica de cierre
    }
}

/**
 * Eliminar cash count con confirmación
 */
async function deleteCashCount(cashCountId) {
    console.log('🗑️ Eliminando cash count:', cashCountId);
    
    if (typeof Swal === 'undefined') {
        if (confirm('¿Estás seguro de que deseas eliminar este arqueo?')) {
            console.log('Arqueo eliminado (confirmación nativa)');
        }
        return;
    }
    
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede revertir",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f56565',
        cancelButtonColor: '#718096',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        console.log('Arqueo eliminado (SweetAlert2)');
        // Aquí iría la lógica de eliminación
    }
}

/**
 * Función para ajustar vista según el tamaño de pantalla
 */
function adjustViewForScreenSize() {
    const windowWidth = window.innerWidth;
    console.log('📱 Ajustando vista para pantalla:', windowWidth + 'px');
    
    // Implementación básica de responsive
    const tableView = document.querySelector('.table-view');
    const cardsView = document.querySelector('.cards-view');
    const mobileView = document.querySelector('.mobile-view');
    const viewToggles = document.querySelector('.view-toggles');
    
    if (windowWidth <= 768) {
        if (tableView) tableView.style.display = 'none';
        if (cardsView) cardsView.style.display = 'none';
        if (mobileView) mobileView.style.display = 'block';
        if (viewToggles) viewToggles.style.display = 'none';
    } else {
        if (tableView) tableView.style.display = 'block';
        if (mobileView) mobileView.style.display = 'none';
        if (viewToggles) viewToggles.style.display = 'flex';
    }
}

// ===== FUNCIONES DE DATATABLE (SIMPLIFICADAS) =====

/**
 * Inicializar DataTable con configuración optimizada
 */
function initializeDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        console.warn('⚠️ DataTables no está disponible');
        return;
    }
    
    try {
        table = $('#cashCountsTable').DataTable({
            responsive: true,
            language: {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                },
                "buttons": {
                    "copy": "Copiar",
                    "colvis": "Visibilidad"
                }
            },
            pageLength: 10,
            dom: 'rt',
            deferRender: true,
            processing: false,
            serverSide: false
        });
        
        console.log('✅ DataTable inicializado correctamente');
    } catch (error) {
        console.error('❌ Error inicializando DataTable:', error);
    }
}

// ===== EXPORTAR FUNCIONES =====

// Hacer funciones disponibles globalmente
window.cashCountsIndex = {
    initializeApp,
    closeCashCount,
    deleteCashCount,
    adjustViewForScreenSize
};

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}
