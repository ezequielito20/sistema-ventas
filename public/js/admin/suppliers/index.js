/**
 * JavaScript optimizado para la vista de proveedores
 * Archivo: public/js/admin/suppliers/index.js
 * Versión: 2.0.0
 * Descripción: Funciones específicas para la gestión de proveedores con búsqueda del servidor
 */

// Verificar si ya se ha cargado para evitar redeclaraciones
if (typeof window.suppliersIndexLoaded !== 'undefined') {
    console.warn('suppliers/index.js ya ha sido cargado anteriormente');
} else {
    window.suppliersIndexLoaded = true;
}

// ===== VARIABLES GLOBALES =====
let currentViewMode = 'cards';

// ===== FUNCIONES DE DETECCIÓN Y CARGA DEL SERVIDOR =====

/**
 * Inicializar event listeners
 */
function initializeEventListeners() {
    // Reinicializar event listeners para elementos dinámicos
    // Esto se llama después de cargar contenido via AJAX
    
    // Botones de vista
    document.querySelectorAll('.view-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const viewMode = this.dataset.view;
            changeViewMode(viewMode);
        });
    });
    
    // Cerrar modal al hacer clic fuera
    const modal = document.getElementById('showSupplierModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeSupplierModal();
            }
        });
    }

    // Cerrar modal con la tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('showSupplierModal');
            if (modal && modal.classList.contains('show')) {
                closeSupplierModal();
            }
        }
    });
}

// Utilidad: detectar si la vista usa paginación del servidor
function isServerPaginationActive() {
    // Siempre activar la búsqueda del servidor para suppliers
    // ya que el controlador está configurado para paginación del servidor
    return true;
}

// Cargar una URL y reemplazar secciones (tabla/tarjetas + paginación) sin recargar
function loadSuppliersPage(url) {
    // Mostrar indicador de carga en el campo de búsqueda
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.classList.add('search-loading');
        searchInput.disabled = true;
    }

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html, application/xhtml+xml'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('Error al cargar');
        return r.text();
    })
    .then(html => {
        const temp = document.createElement('div');
        temp.innerHTML = html;

        // Reemplazar tabla
        const newTableBody = temp.querySelector('#suppliersTableBody');
        const tableBody = document.querySelector('#suppliersTableBody');
        if (newTableBody && tableBody) {
            tableBody.innerHTML = newTableBody.innerHTML;
        }

        // Reemplazar tarjetas
        const newCardsGrid = temp.querySelector('#cardsGrid');
        const cardsGrid = document.querySelector('#cardsGrid');
        if (newCardsGrid && cardsGrid) {
            cardsGrid.innerHTML = newCardsGrid.innerHTML;
        }

        // Reemplazar información de paginación
        const newPaginationInfo = temp.querySelector('.pagination-info span');
        const paginationInfo = document.querySelector('.pagination-info span');
        if (newPaginationInfo && paginationInfo) {
            paginationInfo.innerHTML = newPaginationInfo.innerHTML;
        }

        // Reemplazar enlaces de paginación si existen
        const newPagination = temp.querySelector('.pagination');
        const pagination = document.querySelector('.pagination');
        if (newPagination && pagination) {
            pagination.innerHTML = newPagination.innerHTML;
        }

        // Actualizar URL sin recargar
        window.history.pushState({}, '', url);

        // Reinicializar event listeners para nuevos elementos
        initializeEventListeners();
    })
    .catch(err => {
        console.error('Error al cargar página:', err);
        // Mostrar error al usuario
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: 'Error al cargar los resultados de búsqueda',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .finally(() => {
        // Ocultar indicador de carga
        if (searchInput) {
            searchInput.classList.remove('search-loading');
            searchInput.disabled = false;
        }
    });
}

// Interceptar clicks de paginación cuando servidor está activo
document.addEventListener('click', (e) => {
    const link = e.target.closest('.pagination .page-link');
    if (link && isServerPaginationActive()) {
        e.preventDefault();
        loadSuppliersPage(link.href);
    }
});

// Interceptar búsqueda para servidor
function initializeSearchListener() {
    const search = document.getElementById('searchInput');
    if (search) {
        let t;
        search.addEventListener('input', function () {
            clearTimeout(t);
            t = setTimeout(() => {
                if (isServerPaginationActive()) {
                    const url = new URL(window.location.href);
                    if (this.value.trim()) url.searchParams.set('search', this.value.trim());
                    else url.searchParams.delete('search');
                    loadSuppliersPage(url.toString());
                }
            }, 300);
        });
    }
}

// ===== FUNCIONES PRINCIPALES =====

// Inicializar la página de proveedores
function initializeSuppliersPage() {
    // Cargar modo de vista guardado
    const savedViewMode = localStorage.getItem('suppliersViewMode');
    if (savedViewMode && (savedViewMode === 'table' || savedViewMode === 'cards')) {
        currentViewMode = savedViewMode;
        changeViewMode(savedViewMode);
    } else {
        // Modo por defecto: tarjetas
        changeViewMode('cards');
    }
}

// Cambiar modo de vista
function changeViewMode(mode) {
    currentViewMode = mode;
    localStorage.setItem('suppliersViewMode', mode);
    
    // Actualizar botones de vista
    document.querySelectorAll('.view-toggle').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeButton = document.querySelector(`[data-view="${mode}"]`);
    if (activeButton) {
        activeButton.classList.add('active');
    }
    
    // Mostrar/ocultar vistas
    const tableView = document.getElementById('desktopTableView');
    const cardsView = document.getElementById('desktopCardsView');
    
    if (mode === 'table') {
        if (tableView) tableView.style.display = 'block';
        if (cardsView) cardsView.style.display = 'none';
    } else {
        if (tableView) tableView.style.display = 'none';
        if (cardsView) cardsView.style.display = 'block';
    }
}

// Mostrar detalles de proveedor
async function showSupplierDetails(supplierId) {
    try {
        // Mostrar loading en el modal
        const modal = document.getElementById('showSupplierModal');
        
        if (modal) {
            modal.classList.add('show');
        } else {
            console.error('❌ Modal not found!');
            showAlert('Error', 'Modal no encontrado', 'error');
            return;
        }
        
        // Limpiar datos anteriores
        const fields = ['companyName', 'companyEmail', 'companyPhone', 'companyAddress', 'supplierName', 'supplierPhone'];
        fields.forEach(field => {
            const element = document.getElementById(field);
            if (element) element.textContent = 'Cargando...';
        });
        
        // Obtener el token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch(`/suppliers/${supplierId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.icons === 'success' && data.supplier) {
            // Llenar datos en el modal
            const supplier = data.supplier;
            const fieldMappings = {
                'companyName': supplier.company_name || 'No disponible',
                'companyEmail': supplier.company_email || 'No disponible',
                'companyPhone': supplier.company_phone || 'No disponible',
                'companyAddress': supplier.company_address || 'No disponible',
                'supplierName': supplier.supplier_name || 'No disponible',
                'supplierPhone': supplier.supplier_phone || 'No disponible'
            };
            
            Object.entries(fieldMappings).forEach(([fieldId, value]) => {
                const element = document.getElementById(fieldId);
                if (element) {
                    element.textContent = value;
                }
            });
            
            // Mostrar la sección de productos distribuidos si hay datos
            const productsSection = document.getElementById('productsDistributedSection');
            if (productsSection) {
                if (data.stats && data.stats.length > 0) {
                    productsSection.style.display = 'block';
                    updateProductStats(data.stats);
                } else {
                    productsSection.style.display = 'none';
                }
            }
        } else {
            console.error('❌ Error response:', data);
            const errorMessage = data.message || 'No se pudieron obtener los datos del proveedor';
            showAlert('Error', errorMessage, 'error');
            closeSupplierModal();
        }
    } catch (error) {
        console.error('💥 Error in showSupplierDetails:', error);
        showAlert('Error', 'Error de conexión. Verifique su conexión a internet e inténtelo de nuevo.', 'error');
        closeSupplierModal();
    }
}

// Cerrar modal de proveedor
function closeSupplierModal() {
    const modal = document.getElementById('showSupplierModal');
    if (modal) modal.classList.remove('show');
    
    // Limpiar datos del modal
    setTimeout(() => {
        const fields = ['companyName', 'companyEmail', 'companyPhone', 'companyAddress', 'supplierName', 'supplierPhone'];
        fields.forEach(field => {
            const element = document.getElementById(field);
            if (element) element.textContent = '';
        });
        
        // Ocultar la sección de productos distribuidos
        const productsSection = document.getElementById('productsDistributedSection');
        if (productsSection) {
            productsSection.style.display = 'none';
        }
    }, 300);
}

// Eliminar proveedor
function deleteSupplier(supplierId, supplierName) {
    showConfirmDialog(
        '¿Estás seguro?',
        `¿Deseas eliminar el proveedor <strong>${supplierName}</strong>?<br><small class="text-muted">Esta acción no se puede revertir</small>`,
        'warning',
        () => performDeleteSupplier(supplierId)
    );
}

// Realizar eliminación de proveedor
async function performDeleteSupplier(supplierId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/suppliers/delete/${supplierId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('¡Eliminado!', data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('Error', data.message, 'error');
        }
    } catch (error) {
        console.error('Error deleting supplier:', error);
        showAlert('Error', 'No se pudo eliminar el proveedor', 'error');
    }
}

// Actualizar estadísticas de productos
function updateProductStats(stats) {
    const detailsContainer = document.getElementById('productDetails');
    if (!detailsContainer) return;
    
    let detailsHTML = '';
    let grandTotal = 0;

    if (stats && stats.length > 0) {
        stats.forEach(product => {
            const subtotal = product.stock * product.purchase_price;
            grandTotal += subtotal;

            detailsHTML += `
                <tr>
                    <td>${product.name}</td>
                    <td class="text-center">
                        <span class="badge badge-primary">${product.stock}</span>
                    </td>
                    <td class="text-right">${formatCurrency(product.purchase_price)}</td>
                    <td class="text-right">${formatCurrency(subtotal)}</td>
                </tr>`;
        });
    } else {
        detailsHTML = `
            <tr>
                <td colspan="4" class="text-center">
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>No hay productos registrados para este proveedor</p>
                    </div>
                </td>
            </tr>`;
    }

    detailsContainer.innerHTML = detailsHTML;
    const grandTotalElement = document.getElementById('grandTotal');
    if (grandTotalElement) {
        grandTotalElement.innerHTML = formatCurrency(grandTotal);
    }
}

// Función para formatear moneda
function formatCurrency(amount) {
    const currencySymbol = '$';
    return `${currencySymbol} ${number_format(amount)}`;
}

// Función para formatear números
function number_format(number, decimals = 2) {
    return number.toLocaleString('es-PE', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

// Mostrar diálogo de confirmación
function showConfirmDialog(title, html, icon, onConfirm) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            html: html,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                onConfirm();
            }
        });
    } else {
        if (confirm(title)) {
            onConfirm();
        }
    }
}

// Mostrar alerta
function showAlert(title, text, icon) {
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

// ===== INICIALIZACIÓN =====

// Intentar inicializar inmediatamente y también después de que Alpine.js esté listo
document.addEventListener('DOMContentLoaded', () => {
    initializeSuppliersPage();
    initializeSearchListener();
    initializeEventListeners();
});

// También intentar después de que Alpine.js esté listo
document.addEventListener('alpine:init', () => {
    setTimeout(initializeSearchListener, 100);
});

// ===== FUNCIONES GLOBALES =====

// Hacer funciones disponibles globalmente
window.suppliersIndex = {
    initializeSuppliersPage,
    changeViewMode,
    showSupplierDetails,
    closeSupplierModal,
    deleteSupplier,
    performDeleteSupplier,
    updateProductStats,
    formatCurrency,
    number_format,
    showConfirmDialog,
    showAlert,
    // Nuevas funciones de servidor
    isServerPaginationActive,
    loadSuppliersPage,
    initializeEventListeners
};
