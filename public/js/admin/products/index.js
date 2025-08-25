// ===== CONFIGURACIÓN GLOBAL =====
if (typeof PRODUCTS_CONFIG === 'undefined') {
    window.PRODUCTS_CONFIG = {
        routes: {
            show: '/products',
            delete: '/products/delete'
        },
        pagination: {
            itemsPerPage: 10,
            cardsPerPage: 12
        }
    };
}

// Utilidad: detectar si la vista usa paginación del servidor
function isServerPaginationActive() {
    const paginator = document.querySelector('.custom-pagination .page-numbers a');
    return !!paginator; // existen enlaces → servidor
}

// Cargar una URL y reemplazar secciones (tabla/tarjetas + paginación) sin recargar
function loadProductsPage(url) {
    const container = document.querySelector('.content-container');
    if (!container) return;

    // Indicador simple de carga
    container.style.opacity = '0.6';

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

        // Reemplazar grids/cards
        const newCards = temp.querySelector('#desktopCardsView .cards-grid');
        const cards = document.querySelector('#desktopCardsView .cards-grid');
        if (newCards && cards) cards.innerHTML = newCards.innerHTML;

        // Reemplazar tabla
        const newTableBody = temp.querySelector('#productsTableBody');
        const tableBody = document.getElementById('productsTableBody');
        if (newTableBody && tableBody) tableBody.innerHTML = newTableBody.innerHTML;

        // Reemplazar paginación
        const newPaginations = temp.querySelectorAll('.custom-pagination');
        const paginations = document.querySelectorAll('.custom-pagination');
        newPaginations.forEach((np, idx) => {
            if (paginations[idx]) paginations[idx].innerHTML = np.innerHTML;
        });

        // Actualizar URL sin recargar
        window.history.pushState({}, '', url);

        // Recalcular estructuras para fallback cliente
        if (!isServerPaginationActive() && window.productsIndex) {
            productsIndex.getAllProducts();
            productsIndex.showPage(1);
        }
    })
    .catch(err => console.error(err))
    .finally(() => {
        container.style.opacity = '';
    });
}

// Interceptar clicks de paginación cuando servidor está activo
document.addEventListener('click', (e) => {
    const link = e.target.closest('.custom-pagination a');
    if (link && isServerPaginationActive()) {
        e.preventDefault();
        loadProductsPage(link.href);
    }
});

// Interceptar búsqueda para servidor
document.addEventListener('DOMContentLoaded', () => {
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
                    loadProductsPage(url.toString());
                } else {
                    // Fallback: filtrado cliente existente
                    if (window.productsIndex) {
                        productsIndex.filterProducts(this.value);
                    }
                }
            }, 300);
        });
    }
});

// ===== FUNCIONES GLOBALES =====
if (typeof window.productsIndex === 'undefined') {
    window.productsIndex = {
        // Variables de estado
        currentViewMode: 'cards',
        currentPage: 1,
        allProducts: [],
        filteredProducts: [],
        currentCategoryFilter: '',
        currentStockFilter: '',

        // Inicializar la página
        init: function() {
            // Cargar modo de vista guardado
            const savedViewMode = localStorage.getItem('productsViewMode');
            if (savedViewMode && (savedViewMode === 'table' || savedViewMode === 'cards')) {
                this.currentViewMode = savedViewMode;
                this.changeViewMode(savedViewMode);
            } else {
                // Modo por defecto: tarjetas
                this.changeViewMode('cards');
            }

            // Obtener todos los productos (solo para fallback cliente)
            this.getAllProducts();
            
            // Mostrar primera página
            this.showPage(1);
            
            // Inicializar event listeners
            this.initializeEventListeners();
        },

        // Obtener todas las categorías
        getAllProducts: function() {
            const tableRows = document.querySelectorAll('#productsTableBody tr');
            const productCards = document.querySelectorAll('.product-card');
            const mobileCards = document.querySelectorAll('.mobile-card');
            
            this.allProducts = [];
            
            // Procesar filas de tabla
            tableRows.forEach((row, index) => {
                const productName = row.querySelector('.product-name')?.textContent.trim() || '';
                const productCode = row.querySelector('.product-code')?.textContent.trim() || '';
                const categoryText = row.querySelector('.category-text')?.textContent.trim() || '';
                
                this.allProducts.push({
                    element: row,
                    cardElement: productCards[index],
                    mobileElement: mobileCards[index],
                    data: {
                        id: row.dataset.productId,
                        name: productName,
                        code: productCode,
                        category: categoryText
                    }
                });
            });
            
            this.filteredProducts = [...this.allProducts];
        },

        // Cambiar modo de vista
        changeViewMode: function(mode) {
            this.currentViewMode = mode;
            localStorage.setItem('productsViewMode', mode);
            
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
                tableView.style.display = 'block';
                cardsView.style.display = 'none';
            } else {
                tableView.style.display = 'none';
                cardsView.style.display = 'block';
            }
            
            // Reiniciar paginación
            this.currentPage = 1;
            this.showPage(1);
        },

        // Mostrar página específica (solo fallback cliente)
        showPage: function(page) {
            if (isServerPaginationActive()) return; // servidor maneja

            const startIndex = (page - 1) * PRODUCTS_CONFIG.pagination.itemsPerPage;
            const endIndex = startIndex + PRODUCTS_CONFIG.pagination.itemsPerPage;
            
            // Ocultar todas las filas/tarjetas
            document.querySelectorAll('#productsTableBody tr').forEach(row => row.style.display = 'none');
            document.querySelectorAll('.product-card').forEach(card => card.style.display = 'none');
            document.querySelectorAll('.mobile-card').forEach(card => card.style.display = 'none');
            
            // Mostrar solo los elementos de la página actual
            this.filteredProducts.slice(startIndex, endIndex).forEach((product, index) => {
                if (product.element) product.element.style.display = 'table-row';
                if (product.cardElement) product.cardElement.style.display = 'block';
                if (product.mobileElement) product.mobileElement.style.display = 'block';
                
                // Actualizar números de fila
                if (product.element) {
                    const num = product.element.querySelector('.row-number');
                    if (num) num.textContent = startIndex + index + 1;
                }
            });
            
            // Actualizar información de paginación
            this.updatePaginationInfo(page, this.filteredProducts.length);
            this.updatePaginationControls(page, Math.ceil(this.filteredProducts.length / PRODUCTS_CONFIG.pagination.itemsPerPage));
        },

        // Actualizar información de paginación
        updatePaginationInfo: function(currentPage, totalItems) {
            const infoEl = document.getElementById('paginationInfo');
            if (!infoEl) return;
            const startItem = (currentPage - 1) * PRODUCTS_CONFIG.pagination.itemsPerPage + 1;
            const endItem = Math.min(currentPage * PRODUCTS_CONFIG.pagination.itemsPerPage, totalItems);
            infoEl.textContent = `Mostrando ${startItem}-${endItem} de ${totalItems} registros`;
        },

        // Actualizar controles de paginación
        updatePaginationControls: function(currentPage, totalPages) {
            const prevBtn = document.getElementById('prevPage');
            const nextBtn = document.getElementById('nextPage');
            const pageNumbers = document.getElementById('pageNumbers');
            
            if (!prevBtn || !nextBtn || !pageNumbers) return;

            // Habilitar/deshabilitar botones
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages;
            
            // Generar números de página
            let pageNumbersHTML = '';
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            
            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
            
            for (let i = startPage; i <= endPage; i++) {
                pageNumbersHTML += `
                    <button class="page-number ${i === currentPage ? 'active' : ''}" onclick="productsIndex.goToPage(${i})">
                        ${i}
                    </button>
                `;
            }
            
            pageNumbers.innerHTML = pageNumbersHTML;
        },

        // Ir a página específica
        goToPage: function(page) {
            if (isServerPaginationActive()) return; // servidor
            this.currentPage = page;
            this.showPage(page);
        },

        // Función de búsqueda (fallback cliente)
        filterProducts: function(searchTerm) {
            const searchLower = searchTerm.toLowerCase().trim();
            
            if (!searchLower) {
                this.filteredProducts = [...this.allProducts];
            } else {
                this.filteredProducts = this.allProducts.filter(product => {
                    const nameMatch = product.data.name.toLowerCase().includes(searchLower);
                    const codeMatch = product.data.code.toLowerCase().includes(searchLower);
                    const categoryMatch = product.data.category.toLowerCase().includes(searchLower);
                    return nameMatch || codeMatch || categoryMatch;
                });
            }
            
            this.currentPage = 1;
            this.showPage(1);
        },

        // Aplicar filtros por categoría (desde Alpine.js)
        filterByCategory: function(categoryId) {
            if (isServerPaginationActive()) {
                const url = new URL(window.location.href);
                if (categoryId) url.searchParams.set('category_id', categoryId);
                else url.searchParams.delete('category_id');
                loadProductsPage(url.toString());
                return;
            }
            this.currentCategoryFilter = categoryId;
            this.applyAdvancedFilters(categoryId, this.currentStockFilter || '');
        },

        // Aplicar filtros por stock (desde Alpine.js)
        filterByStock: function(stockId) {
            if (isServerPaginationActive()) {
                const url = new URL(window.location.href);
                if (stockId) url.searchParams.set('stock_status', stockId);
                else url.searchParams.delete('stock_status');
                loadProductsPage(url.toString());
                return;
            }
            this.currentStockFilter = stockId;
            this.applyAdvancedFilters(this.currentCategoryFilter || '', stockId);
        },

        // Aplicar filtros avanzados (fallback cliente)
        applyAdvancedFilters: function(categoryFilter, stockFilter) {
            if (isServerPaginationActive()) return;
            this.filteredProducts = this.allProducts.filter(product => {
                let matches = true;
                
                // Filtro por categoría
                if (categoryFilter && categoryFilter !== '') {
                    const productCategoryId = product.element.querySelector('.category-text')?.getAttribute('data-category-id');
                    if (productCategoryId != categoryFilter) {
                        matches = false;
                    }
                }
                
                // Filtro por estado de stock
                if (stockFilter && stockFilter !== '') {
                    const stockElement = product.element.querySelector('.stock-badge');
                    const stockStatus = this.getStockStatus(stockElement);
                    if (stockStatus !== stockFilter) {
                        matches = false;
                    }
                }
                
                return matches;
            });
            
            this.currentPage = 1;
            this.showPage(1);
            this.updateActiveFilters(categoryFilter, stockFilter);
        },

        // Limpiar filtros avanzados
        clearAdvancedFilters: function() {
            if (isServerPaginationActive()) {
                const url = new URL(window.location.href);
                url.searchParams.delete('category_id');
                url.searchParams.delete('stock_status');
                url.searchParams.delete('search');
                loadProductsPage(url.toString());
                return;
            }
            this.filteredProducts = [...this.allProducts];
            this.currentPage = 1;
            this.showPage(1);
            this.updateActiveFilters('', '');
        },

        // Obtener estado de stock del elemento
        getStockStatus: function(stockElement) {
            if (!stockElement) return 'normal';
            if (stockElement.classList.contains('badge-danger')) {
                return 'low';
            } else if (stockElement.classList.contains('badge-warning')) {
                return 'normal';
            } else if (stockElement.classList.contains('badge-success')) {
                return 'high';
            }
            return 'normal';
        },

        // Actualizar filtros activos
        updateActiveFilters: function(categoryFilter, stockFilter) {
            const activeFilters = document.getElementById('activeFilters');
            if (!activeFilters) return;
            const filters = [];
            
            if (categoryFilter && categoryFilter !== '') {
                // Buscar el nombre de la categoría en los datos globales
                if (window.categoriesData) {
                    const selectedCategory = window.categoriesData.find(cat => cat.id == categoryFilter);
                    if (selectedCategory) {
                        filters.push(selectedCategory.name);
                    }
                }
            }
            
            if (stockFilter && stockFilter !== '') {
                const stockLabels = {
                    'low': 'Stock Bajo',
                    'normal': 'Stock Normal',
                    'high': 'Stock Alto'
                };
                filters.push(stockLabels[stockFilter] || stockFilter);
            }
            
            if (filters.length > 0) {
                activeFilters.innerHTML = filters.map(filter => 
                    `<span class="filter-badge">${filter}</span>`
                ).join('');
            } else {
                activeFilters.innerHTML = '<span class="filter-badge">Todos los productos</span>';
            }
        },

        // Mostrar detalles de producto
        showProductDetails: async function(productId) {
            // Mostrar loading
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Cargando...',
                    text: 'Obteniendo detalles del producto',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            try {

                const response = await fetch(`${PRODUCTS_CONFIG.routes.show}/${productId}`);

                const data = await response.json();

                
                if (data.status === 'success' || data.success) {
                    if (typeof Swal !== 'undefined') Swal.close();
                    
                    const product = data.product;
                    
                    // Llenar datos básicos del modal
                    document.getElementById('modalProductName').textContent = product.name || '-';
                    document.getElementById('modalProductCode').textContent = product.code || '-';
                    document.getElementById('modalProductCategory').textContent = product.category || 'Sin categoría';
                    document.getElementById('modalProductDescription').textContent = product.description || 'Sin descripción';
                    document.getElementById('modalProductFullDescription').textContent = product.description || 'Sin descripción disponible';
                    
                    // Imagen del producto
                    const modalImage = document.getElementById('modalProductImage');
                    modalImage.src = product.image || '/img/no-image.svg';
                    modalImage.alt = product.name || 'Producto';
                    
                    // Stock y estado
                    const stock = parseInt(product.stock) || 0;
                    document.getElementById('modalProductStock').textContent = stock;
                    document.getElementById('modalStockCurrent').textContent = stock;
                    
                    // Estado del stock con colores
                    const stockBadge = document.getElementById('modalStockBadge');
                    const stockStatus = document.getElementById('modalStockStatus');
                    let stockStatusText = 'Normal';
                    let stockStatusColor = 'bg-yellow-100 text-yellow-800';
                    
                    if (stock <= 10) {
                        stockStatusText = 'Stock Bajo';
                        stockStatusColor = 'bg-red-100 text-red-800';
                    } else if (stock > 50) {
                        stockStatusText = 'Stock Alto';
                        stockStatusColor = 'bg-green-100 text-green-800';
                    }
                    
                    stockBadge.className = `inline-flex items-center px-4 py-2 rounded-xl font-semibold text-sm ${stockStatusColor}`;
                    stockStatus.textContent = stockStatusText;
                    stockStatus.className = `font-semibold ${stockStatusColor.split(' ')[1]}`;
                    
                    // Precios
                    document.getElementById('modalProductPurchasePrice').textContent = product.purchase_price_formatted || product.purchase_price || '-';
                    document.getElementById('modalProductSalePrice').textContent = product.sale_price_formatted || product.sale_price || '-';
                    
                    // Calcular ganancia potencial
                    const purchasePrice = parseFloat(product.purchase_price) || 0;
                    const salePrice = parseFloat(product.sale_price) || 0;
                    const profit = salePrice - purchasePrice;
                    
                    // Usar la moneda del sistema
                    const currencyCode = window.currencyData ? window.currencyData.code : 'USD';
                    const currencySymbol = window.currencyData ? window.currencyData.symbol : '$';
                    
                    const profitFormatted = new Intl.NumberFormat('es-ES', {
                        style: 'currency',
                        currency: currencyCode,
                        minimumFractionDigits: 0
                    }).format(profit);
                    document.getElementById('modalProductProfit').textContent = profitFormatted;
                    
                    // Valor total en stock
                    const stockValue = stock * salePrice;
                    const stockValueFormatted = new Intl.NumberFormat('es-ES', {
                        style: 'currency',
                        currency: currencyCode,
                        minimumFractionDigits: 0
                    }).format(stockValue);
                    document.getElementById('modalStockValue').textContent = stockValueFormatted;
                    
                    // Fechas
                    document.getElementById('modalProductCreated').textContent = product.created_at_formatted || product.created_at || '-';
                    document.getElementById('modalProductUpdated').textContent = product.updated_at_formatted || product.updated_at || '-';
                    document.getElementById('modalProductId').textContent = product.id || '-';
                    
                    // Guardar ID para edición
                    window.currentProductId = productId;
                    
                    // Mostrar modal
                    document.getElementById('showProductModal').style.display = 'flex';
                    document.body.style.overflow = 'hidden'; // Prevenir scroll del body
                } else {
                    if (typeof Swal !== 'undefined') Swal.close();
                    this.showAlert('Error', 'No se pudieron obtener los datos del producto', 'error');
                }
            } catch (error) {
                if (typeof Swal !== 'undefined') Swal.close();
                console.error('Error:', error);
                this.showAlert('Error', 'No se pudieron obtener los datos del producto', 'error');
            }
        },

        // Cerrar modal de producto
        closeProductModal: function() {
            document.getElementById('showProductModal').style.display = 'none';
            document.body.style.overflow = ''; // Restaurar scroll del body
        },

        // Eliminar producto
        deleteProduct: function(productId, productName) {
            this.showConfirmDialog(
                '¿Estás seguro?',
                `¿Deseas eliminar el producto <strong>${productName}</strong>?<br><small class="text-muted">Esta acción no se puede revertir</small>`,
                'warning',
                () => this.performDeleteProduct(productId)
            );
        },

        // Realizar eliminación de producto
        performDeleteProduct: async function(productId) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`${PRODUCTS_CONFIG.routes.delete}/${productId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.status === 'success' || data.success) {
                    this.showAlert('¡Eliminado!', data.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    // Mostrar mensaje específico para productos con ventas/compras asociadas
                    if (data.sales_count || data.purchases_count) {
                        this.showAlert(
                            'No se puede eliminar', 
                            data.message, 
                            'warning'
                        );
                    } else {
                        this.showAlert('Error', data.message || 'Error al eliminar el producto', 'error');
                    }
                }
            } catch (error) {
                console.error('Error al eliminar producto:', error);
                this.showAlert('Error', 'No se pudo eliminar el producto', 'error');
            }
        },

        // Mostrar diálogo de confirmación
        showConfirmDialog: function(title, html, icon, onConfirm) {
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
        },

        // Mostrar alerta
        showAlert: function(title, text, icon) {
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
        },

        // Inicializar event listeners
        initializeEventListeners: function() {
            // Toggle de filtros
            const filtersToggle = document.getElementById('filtersToggle');
            const filtersContent = document.getElementById('filtersContent');
            
            if (filtersToggle && filtersContent) {
                filtersToggle.addEventListener('click', function() {
                    filtersContent.classList.toggle('show');
                    const icon = this.querySelector('i');
                    if (filtersContent.classList.contains('show')) {
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                    } else {
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                    }
                });
            }
            
            // Búsqueda en tiempo real (fallback cliente si no hay servidor)
            const searchInput = document.getElementById('searchInput');
            if (searchInput && !isServerPaginationActive()) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value;
                    productsIndex.filterProducts(searchTerm);
                });
            }
            
            // Botones de vista
            document.querySelectorAll('.view-toggle').forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const viewMode = this.dataset.view;
                    productsIndex.changeViewMode(viewMode);
                });
            });
            
            // Paginación (fallback cliente)
            const prevBtn = document.getElementById('prevPage');
            const nextBtn = document.getElementById('nextPage');
            if (prevBtn && nextBtn && !isServerPaginationActive()) {
                prevBtn.addEventListener('click', function() {
                    if (productsIndex.currentPage > 1) {
                        productsIndex.currentPage--;
                        productsIndex.showPage(productsIndex.currentPage);
                    }
                });
                
                nextBtn.addEventListener('click', function() {
                    const totalPages = Math.ceil(productsIndex.filteredProducts.length / PRODUCTS_CONFIG.pagination.itemsPerPage);
                    if (productsIndex.currentPage < totalPages) {
                        productsIndex.currentPage++;
                        productsIndex.showPage(productsIndex.currentPage);
                    }
                });
            }
            
            // Filtros en tiempo real (fallback)
            const categoryFilter = document.getElementById('categoryFilter');
            if (categoryFilter && !isServerPaginationActive()) {
                categoryFilter.addEventListener('change', function() {
                    const categoryValue = this.value;
                    const stockValue = document.getElementById('stockFilter').value;
                    productsIndex.applyAdvancedFilters(categoryValue, stockValue);
                });
            }
            
            const stockFilter = document.getElementById('stockFilter');
            if (stockFilter && !isServerPaginationActive()) {
                stockFilter.addEventListener('change', function() {
                    const stockValue = this.value;
                    const categoryValue = document.getElementById('categoryFilter').value;
                    productsIndex.applyAdvancedFilters(categoryValue, stockValue);
                });
            }
            
            // Limpiar filtros
            const clearBtn = document.getElementById('clearFilters');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    if (isServerPaginationActive()) {
                        productsIndex.clearAdvancedFilters();
                    } else {
                        const cat = document.getElementById('categoryFilter');
                        const stock = document.getElementById('stockFilter');
                        if (cat) cat.value = '';
                        if (stock) stock.value = '';
                        productsIndex.clearAdvancedFilters();
                    }
                });
            }
            
            // Cerrar modal al hacer clic fuera
            const modal = document.getElementById('showProductModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        productsIndex.closeProductModal();
                    }
                });
            }
        }
    };
}

// ===== FUNCIONES GLOBALES PARA COMPATIBILIDAD =====

// Función para mostrar detalles de producto (compatibilidad con onclick)
if (typeof window.showProductDetails === 'undefined') {
    window.showProductDetails = function(productId) {

        if (window.productsIndex && window.productsIndex.showProductDetails) {
            window.productsIndex.showProductDetails(productId);
        } else {
            console.error('productsIndex not available');
        }
    };
}

// Función para cerrar modal de producto (compatibilidad con onclick)
if (typeof window.closeProductModal === 'undefined') {
    window.closeProductModal = function() {
        window.productsIndex.closeProductModal();
    };
}

// Función para eliminar producto (compatibilidad con onclick)
if (typeof window.deleteProduct === 'undefined') {
    window.deleteProduct = function(productId, productName) {
        window.productsIndex.deleteProduct(productId, productName);
    };
}




// ===== INICIALIZACIÓN =====
if (!window.productsIndexInitialized) {
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.productsIndexInitialized) {
            window.productsIndex.init();
            window.productsIndexInitialized = true;
        }
    });
}
