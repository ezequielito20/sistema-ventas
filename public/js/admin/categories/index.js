/**
 * JavaScript optimizado para categories/index
 * Archivo: public/js/admin/categories/index.js
 * Versión: 1.0.0
 * Descripción: Funciones específicas para la gestión de categorías
 */



// ===== CONFIGURACIÓN GLOBAL =====
const CATEGORIES_CONFIG = {
    itemsPerPage: 10,
    cardsPerPage: 12,
    maxVisiblePages: 5,
    defaultViewMode: 'cards',
    searchDelay: 300
};

// ===== VARIABLES GLOBALES =====
let currentViewMode = CATEGORIES_CONFIG.defaultViewMode;
let currentPage = 1;
let allCategories = [];
let filteredCategories = [];
let searchTimeout = null;

// ===== FUNCIONES PRINCIPALES =====

/**
 * Inicializar la página de categorías
 */
function initializeCategoriesPage() {
    // Cargar modo de vista guardado
    const savedViewMode = localStorage.getItem('categoriesViewMode');
    if (savedViewMode && (savedViewMode === 'table' || savedViewMode === 'cards')) {
        currentViewMode = savedViewMode;
        changeViewMode(savedViewMode);
    } else {
        // Modo por defecto: tarjetas
        changeViewMode(CATEGORIES_CONFIG.defaultViewMode);
    }

    // Obtener todas las categorías
    getAllCategories();
    
    // Mostrar primera página
    showPage(1);
}

/**
 * Obtener todas las categorías del DOM
 */
function getAllCategories() {
    const tableRows = document.querySelectorAll('#categoriesTableBody tr');
    const categoryCards = document.querySelectorAll('.category-card');
    const mobileCards = document.querySelectorAll('.mobile-card');
    
    allCategories = [];
    
    // Procesar filas de tabla
    tableRows.forEach((row, index) => {
        const categoryName = row.querySelector('.category-name')?.textContent?.trim() || '';
        const categoryDescription = row.querySelector('.description-text')?.textContent?.trim() || '';
        
        allCategories.push({
            element: row,
            cardElement: categoryCards[index],
            mobileElement: mobileCards[index],
            data: {
                id: row.dataset.categoryId,
                name: categoryName,
                description: categoryDescription
            }
        });
    });
    
    filteredCategories = [...allCategories];
}

/**
 * Cambiar modo de vista
 */
function changeViewMode(mode) {
    currentViewMode = mode;
    localStorage.setItem('categoriesViewMode', mode);
    
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

/**
 * Mostrar página específica
 */
function showPage(page) {
    const startIndex = (page - 1) * CATEGORIES_CONFIG.itemsPerPage;
    const endIndex = startIndex + CATEGORIES_CONFIG.itemsPerPage;
    
    // Ocultar todas las filas/tarjetas
    document.querySelectorAll('#categoriesTableBody tr').forEach(row => row.style.display = 'none');
    document.querySelectorAll('.category-card').forEach(card => card.style.display = 'none');
    document.querySelectorAll('.mobile-card').forEach(card => card.style.display = 'none');
    
    // Mostrar solo los elementos de la página actual
    filteredCategories.slice(startIndex, endIndex).forEach((category, index) => {
        if (category.element) category.element.style.display = 'table-row';
        if (category.cardElement) category.cardElement.style.display = 'block';
        if (category.mobileElement) category.mobileElement.style.display = 'block';
        
        // Actualizar números de fila
        if (category.element) {
            const rowNumber = category.element.querySelector('.row-number');
            if (rowNumber) {
                rowNumber.textContent = startIndex + index + 1;
            }
        }
    });
    
    // Actualizar información de paginación
    updatePaginationInfo(page, filteredCategories.length);
    updatePaginationControls(page, Math.ceil(filteredCategories.length / CATEGORIES_CONFIG.itemsPerPage));
}

/**
 * Actualizar información de paginación
 */
function updatePaginationInfo(currentPage, totalItems) {
    const startItem = (currentPage - 1) * CATEGORIES_CONFIG.itemsPerPage + 1;
    const endItem = Math.min(currentPage * CATEGORIES_CONFIG.itemsPerPage, totalItems);
    
    const paginationInfo = document.getElementById('paginationInfo');
    if (paginationInfo) {
        paginationInfo.textContent = `Mostrando ${startItem}-${endItem} de ${totalItems} registros`;
    }
    
    const cardsPaginationInfo = document.getElementById('cardsPaginationInfo');
    if (cardsPaginationInfo) {
        cardsPaginationInfo.textContent = `Mostrando ${startItem}-${endItem} de ${totalItems} registros`;
    }
}

/**
 * Actualizar controles de paginación
 */
function updatePaginationControls(currentPage, totalPages) {
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const pageNumbers = document.getElementById('pageNumbers');
    
    const cardsPrevBtn = document.getElementById('cardsPrevPage');
    const cardsNextBtn = document.getElementById('cardsNextPage');
    const cardsPageNumbers = document.getElementById('cardsPageNumbers');
    
    // Habilitar/deshabilitar botones
    if (prevBtn) prevBtn.disabled = currentPage === 1;
    if (nextBtn) nextBtn.disabled = currentPage === totalPages;
    if (cardsPrevBtn) cardsPrevBtn.disabled = currentPage === 1;
    if (cardsNextBtn) cardsNextBtn.disabled = currentPage === totalPages;
    
    // Generar números de página
    const pageNumbersHTML = generatePageNumbersHTML(currentPage, totalPages);
    
    if (pageNumbers) pageNumbers.innerHTML = pageNumbersHTML;
    if (cardsPageNumbers) cardsPageNumbers.innerHTML = pageNumbersHTML;
}

/**
 * Generar HTML de números de página
 */
function generatePageNumbersHTML(currentPage, totalPages) {
    let pageNumbersHTML = '';
    const maxVisiblePages = CATEGORIES_CONFIG.maxVisiblePages;
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
    
    return pageNumbersHTML;
}

/**
 * Ir a página específica
 */
function goToPage(page) {
    currentPage = page;
    showPage(page);
}

/**
 * Función de búsqueda con debounce
 */
function filterCategories(searchTerm) {
    const searchLower = searchTerm.toLowerCase().trim();
    
    if (!searchLower) {
        filteredCategories = [...allCategories];
    } else {
        filteredCategories = allCategories.filter(category => {
            const nameMatch = category.data.name.toLowerCase().includes(searchLower);
            const descriptionMatch = category.data.description.toLowerCase().includes(searchLower);
            return nameMatch || descriptionMatch;
        });
    }
    
    currentPage = 1;
    showPage(1);
    updateActiveFilters(searchTerm);
}

/**
 * Actualizar filtros activos
 */
function updateActiveFilters(searchTerm) {
    const activeFilters = document.getElementById('activeFilters');
    if (!activeFilters) return;
    
    if (searchTerm) {
        activeFilters.innerHTML = `<span class="filter-badge">Búsqueda: "${searchTerm}"</span>`;
    } else {
        activeFilters.innerHTML = `<span class="filter-badge">Todas las categorías</span>`;
    }
}

/**
 * Mostrar detalles de categoría
 */
async function showCategoryDetails(categoryId) {
    try {
        const response = await fetch(`/categories/${categoryId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.status === 'success') {
            // Llenar datos en el modal
            const modalCategoryName = document.getElementById('modalCategoryName');
            const modalCategoryDescription = document.getElementById('modalCategoryDescription');
            const modalCategoryCreated = document.getElementById('modalCategoryCreated');
            const modalCategoryUpdated = document.getElementById('modalCategoryUpdated');
            
            if (modalCategoryName) modalCategoryName.textContent = data.category.name;
            if (modalCategoryDescription) modalCategoryDescription.textContent = data.category.description || 'Sin descripción';
            if (modalCategoryCreated) modalCategoryCreated.textContent = data.category.created_at;
            if (modalCategoryUpdated) modalCategoryUpdated.textContent = data.category.updated_at;
            
            // Mostrar modal
            const modal = document.getElementById('showCategoryModal');
            if (modal) {
                modal.style.display = 'flex';
                // Agregar animación de entrada
                modal.classList.add('modal-enter');
                setTimeout(() => modal.classList.remove('modal-enter'), 300);
            }
        } else {
            showAlert('Error', 'No se pudieron obtener los datos de la categoría', 'error');
        }
    } catch (error) {
        showAlert('Error', 'No se pudieron obtener los datos de la categoría', 'error');
    }
}

/**
 * Cerrar modal de categoría
 */
function closeCategoryModal() {
    const modal = document.getElementById('showCategoryModal');
    if (modal) {
        modal.classList.add('modal-exit');
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('modal-exit');
        }, 300);
    }
}

/**
 * Eliminar categoría
 */
function deleteCategory(categoryId, categoryName) {
    showConfirmDialog(
        '¿Estás seguro?',
        `¿Deseas eliminar la categoría <strong>${categoryName}</strong>?<br><small class="text-muted">Esta acción no se puede revertir</small>`,
        'warning',
        () => performDeleteCategory(categoryId)
    );
}

/**
 * Realizar eliminación de categoría
 */
async function performDeleteCategory(categoryId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            throw new Error('CSRF token no encontrado');
        }
        
        const response = await fetch(`/categories/delete/${categoryId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showAlert('¡Eliminado!', data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('Error', data.message, 'error');
        }
    } catch (error) {
        showAlert('Error', 'No se pudo eliminar la categoría', 'error');
    }
}

// ===== FUNCIONES DE UTILIDAD =====

/**
 * Mostrar diálogo de confirmación
 */
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
            reverseButtons: true,
            customClass: {
                popup: 'swal-custom-popup',
                confirmButton: 'swal-confirm-button',
                cancelButton: 'swal-cancel-button'
            }
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

/**
 * Mostrar alerta
 */
function showAlert(title, text, icon) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'swal-custom-popup'
            }
        });
    } else {
        alert(`${title}: ${text}`);
    }
}

/**
 * Aplicar filtros
 */
function applyFilters() {
    const searchTerm = document.getElementById('categorySearch')?.value || '';
    filterCategories(searchTerm);
}

/**
 * Limpiar filtros
 */
function clearFilters() {
    const categorySearch = document.getElementById('categorySearch');
    if (categorySearch) {
        categorySearch.value = '';
    }
    filterCategories('');
}

/**
 * Toggle de filtros
 */
function toggleFilters() {
    const filtersContent = document.getElementById('filtersContent');
    const filtersToggle = document.getElementById('filtersToggle');
    
    if (filtersContent && filtersToggle) {
        filtersContent.classList.toggle('show');
        const icon = filtersToggle.querySelector('i');
        if (icon) {
            if (filtersContent.classList.contains('show')) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }
    }
}

// ===== INICIALIZACIÓN DE EVENT LISTENERS =====

/**
 * Inicializar event listeners
 */
function initializeEventListeners() {
    // Toggle de filtros
    const filtersToggle = document.getElementById('filtersToggle');
    if (filtersToggle) {
        filtersToggle.addEventListener('click', toggleFilters);
    }
    
    // Búsqueda en tiempo real con debounce
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterCategories(this.value);
            }, CATEGORIES_CONFIG.searchDelay);
        });
    }
    
    // Búsqueda en filtros
    const categorySearch = document.getElementById('categorySearch');
    if (categorySearch) {
        categorySearch.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterCategories(this.value);
            }, CATEGORIES_CONFIG.searchDelay);
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
    const cardsPrevPageBtn = document.getElementById('cardsPrevPage');
    const cardsNextPageBtn = document.getElementById('cardsNextPage');
    
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
            const totalPages = Math.ceil(filteredCategories.length / CATEGORIES_CONFIG.itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });
    }
    
    if (cardsPrevPageBtn) {
        cardsPrevPageBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        });
    }
    
    if (cardsNextPageBtn) {
        cardsNextPageBtn.addEventListener('click', function() {
            const totalPages = Math.ceil(filteredCategories.length / CATEGORIES_CONFIG.itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });
    }
    
    // Aplicar filtros
    const applyFiltersBtn = document.getElementById('applyFilters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', applyFilters);
    }
    
    // Limpiar filtros
    const clearFiltersBtn = document.getElementById('clearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearFilters);
    }
    
    // Cerrar modal al hacer clic fuera
    const showCategoryModal = document.getElementById('showCategoryModal');
    if (showCategoryModal) {
        showCategoryModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeCategoryModal();
            }
        });
    }
    
    // Cerrar modal con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCategoryModal();
        }
    });
}

// ===== INICIALIZACIÓN =====

/**
 * Inicializar la aplicación cuando el DOM esté listo
 */
function initializeApp() {
    // Inicializar componentes
    initializeCategoriesPage();
    initializeEventListeners();
}

// Hacer funciones disponibles globalmente
window.categoriesIndex = {
    initializeApp,
    changeViewMode,
    showPage,
    goToPage,
    filterCategories,
    showCategoryDetails,
    closeCategoryModal,
    deleteCategory,
    applyFilters,
    clearFilters,
    toggleFilters,
    showConfirmDialog,
    showAlert
};

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}
