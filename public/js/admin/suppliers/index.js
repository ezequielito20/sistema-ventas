/**
 * JavaScript optimizado para la vista de proveedores
 * Archivo: public/js/admin/suppliers/index.js
 * Versión: 1.0.0
 */

// ===== VARIABLES GLOBALES =====
let currentViewMode = 'cards';
let currentPage = 1;
const itemsPerPage = 10;
const cardsPerPage = 12;
let allSuppliers = [];
let filteredSuppliers = [];

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('Proveedores page loaded');
    initializeSuppliersPage();
    initializeEventListeners();
});

// ===== FUNCIONES PRINCIPALES =====

// Inicializar la página de proveedores
function initializeSuppliersPage() {
    console.log('Initializing suppliers page...');
    
    // Cargar modo de vista guardado
    const savedViewMode = localStorage.getItem('suppliersViewMode');
    if (savedViewMode && (savedViewMode === 'table' || savedViewMode === 'cards')) {
        currentViewMode = savedViewMode;
        changeViewMode(savedViewMode);
    } else {
        // Modo por defecto: tarjetas
        changeViewMode('cards');
    }

    // Obtener todos los proveedores
    getAllSuppliers();
    
    // Mostrar primera página
    showPage(1);
}

// Obtener todos los proveedores
function getAllSuppliers() {
    const tableRows = document.querySelectorAll('#suppliersTableBody tr');
    const supplierCards = document.querySelectorAll('.supplier-card');
    const mobileCards = document.querySelectorAll('.mobile-card');
    
    allSuppliers = [];
    
    // Procesar filas de tabla
    tableRows.forEach((row, index) => {
        const companyName = row.querySelector('.supplier-name')?.textContent.trim() || '';
        const supplierName = row.querySelector('.contact-name')?.textContent.trim() || '';
        
        allSuppliers.push({
            element: row,
            cardElement: supplierCards[index],
            mobileElement: mobileCards[index],
            data: {
                id: row.dataset.supplierId,
                company_name: companyName,
                supplier_name: supplierName
            }
        });
    });
    
    filteredSuppliers = [...allSuppliers];
    console.log('Suppliers loaded:', allSuppliers.length);
}

// Cambiar modo de vista
function changeViewMode(mode) {
    console.log('Changing view mode to:', mode);
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
    
    // Reiniciar paginación
    currentPage = 1;
    showPage(1);
}

// Mostrar página específica
function showPage(page) {
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    
    // Ocultar todas las filas/tarjetas
    document.querySelectorAll('#suppliersTableBody tr').forEach(row => row.style.display = 'none');
    document.querySelectorAll('.supplier-card').forEach(card => card.style.display = 'none');
    document.querySelectorAll('.mobile-card').forEach(card => card.style.display = 'none');
    
    // Mostrar solo los elementos de la página actual
    filteredSuppliers.slice(startIndex, endIndex).forEach((supplier, index) => {
        if (supplier.element) supplier.element.style.display = 'table-row';
        if (supplier.cardElement) supplier.cardElement.style.display = 'block';
        if (supplier.mobileElement) supplier.mobileElement.style.display = 'block';
        
        // Actualizar números de fila
        if (supplier.element) {
            const rowNumber = supplier.element.querySelector('.row-number');
            if (rowNumber) rowNumber.textContent = startIndex + index + 1;
        }
    });
    
    // Actualizar información de paginación
    updatePaginationInfo(page, filteredSuppliers.length);
    updatePaginationControls(page, Math.ceil(filteredSuppliers.length / itemsPerPage));
}

// Actualizar información de paginación
function updatePaginationInfo(currentPage, totalItems) {
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, totalItems);
    const paginationInfo = document.getElementById('paginationInfo');
    if (paginationInfo) {
        paginationInfo.textContent = `Mostrando ${startItem}-${endItem} de ${totalItems} registros`;
    }
}

// Actualizar controles de paginación
function updatePaginationControls(currentPage, totalPages) {
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const pageNumbers = document.getElementById('pageNumbers');
    
    // Habilitar/deshabilitar botones
    if (prevBtn) prevBtn.disabled = currentPage === 1;
    if (nextBtn) nextBtn.disabled = currentPage === totalPages;
    
    // Generar números de página
    if (pageNumbers) {
        let pageNumbersHTML = '';
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            pageNumbersHTML += `
                <button class="page-number ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">
                    ${i}
                </button>
            `;
        }
        
        pageNumbers.innerHTML = pageNumbersHTML;
    }
}

// Ir a página específica
function goToPage(page) {
    currentPage = page;
    showPage(page);
}

// Función de búsqueda
function filterSuppliers(searchTerm) {
    const searchLower = searchTerm.toLowerCase().trim();
    
    if (!searchLower) {
        filteredSuppliers = [...allSuppliers];
    } else {
        filteredSuppliers = allSuppliers.filter(supplier => {
            const companyMatch = supplier.data.company_name.toLowerCase().includes(searchLower);
            const supplierMatch = supplier.data.supplier_name.toLowerCase().includes(searchLower);
            return companyMatch || supplierMatch;
        });
    }
    
    currentPage = 1;
    showPage(1);
}

// Mostrar detalles de proveedor
async function showSupplierDetails(supplierId) {
    console.log('🎯 showSupplierDetails called with ID:', supplierId);
    
    try {
        // Mostrar loading en el modal
        const modal = document.getElementById('showSupplierModal');
        console.log('🔍 Modal element found:', !!modal);
        
        if (modal) {
            modal.classList.add('show');
            console.log('✅ Modal shown');
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
        
        console.log('🌐 Making fetch request to:', `/suppliers/${supplierId}`);
        
        // Obtener el token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('🔐 CSRF Token found:', !!csrfToken);
        
        const response = await fetch(`/suppliers/${supplierId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        console.log('📡 Response status:', response.status);
        console.log('📡 Response ok:', response.ok);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📦 Supplier data received:', data);
        
        if (data.icons === 'success' && data.supplier) {
            console.log('✅ Success response, filling modal data');
            
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
                    console.log(`✅ Filled ${fieldId}:`, value);
                } else {
                    console.warn(`⚠️ Element ${fieldId} not found`);
                }
            });
            
            // Mostrar la sección de productos distribuidos si hay datos
            const productsSection = document.getElementById('productsDistributedSection');
            if (productsSection) {
                if (data.stats && data.stats.length > 0) {
                    productsSection.style.display = 'block';
                    updateProductStats(data.stats);
                    console.log('✅ Products section shown');
                } else {
                    productsSection.style.display = 'none';
                    console.log('ℹ️ No products to show');
                }
            }
            
            console.log('✅ Modal data filled successfully');
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
    console.log('Deleting supplier:', supplierId, supplierName);
    
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

// Inicializar event listeners
function initializeEventListeners() {
    console.log('Initializing event listeners...');
    
    // Toggle de filtros
    const filtersToggle = document.getElementById('filtersToggle');
    const filtersContent = document.getElementById('filtersContent');
    
    if (filtersToggle && filtersContent) {
        filtersToggle.addEventListener('click', function() {
            filtersContent.classList.toggle('show');
            const icon = this.querySelector('i');
            if (icon) {
                if (filtersContent.classList.contains('show')) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }
        });
    }
    
    // Búsqueda en tiempo real
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value;
            filterSuppliers(searchTerm);
        });
    }
    
    // Búsqueda en filtros
    const supplierSearch = document.getElementById('supplierSearch');
    if (supplierSearch) {
        supplierSearch.addEventListener('keyup', function() {
            const searchTerm = this.value;
            filterSuppliers(searchTerm);
        });
    }
    
    // Botones de vista
    document.querySelectorAll('.view-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const viewMode = this.dataset.view;
            changeViewMode(viewMode);
        });
    });
    
    // Paginación
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    
    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        });
    }
    
    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', function() {
            const totalPages = Math.ceil(filteredSuppliers.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });
    }
    
    // Aplicar filtros
    const applyFiltersBtn = document.getElementById('applyFilters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            const searchTerm = document.getElementById('supplierSearch')?.value || '';
            filterSuppliers(searchTerm);
        });
    }
    
    // Limpiar filtros
    const clearFiltersBtn = document.getElementById('clearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            const supplierSearchInput = document.getElementById('supplierSearch');
            if (supplierSearchInput) supplierSearchInput.value = '';
            filterSuppliers('');
        });
    }
    
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
    
    console.log('Event listeners initialized');
}
