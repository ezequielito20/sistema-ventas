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

// ===== FUNCIONES GLOBALES =====
if (typeof window.productsIndex === 'undefined') {
    window.productsIndex = {
        // Variables de estado
        currentViewMode: 'cards',
        currentPage: 1,
        allProducts: [],
        filteredProducts: [],

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

            // Obtener todos los productos
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
                const productName = row.querySelector('.product-name').textContent.trim();
                const productCode = row.querySelector('.product-code').textContent.trim();
                const categoryText = row.querySelector('.category-text').textContent.trim();
                
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

        // Mostrar página específica
        showPage: function(page) {
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
                    product.element.querySelector('.row-number').textContent = startIndex + index + 1;
                }
            });
            
            // Actualizar información de paginación
            this.updatePaginationInfo(page, this.filteredProducts.length);
            this.updatePaginationControls(page, Math.ceil(this.filteredProducts.length / PRODUCTS_CONFIG.pagination.itemsPerPage));
        },

        // Actualizar información de paginación
        updatePaginationInfo: function(currentPage, totalItems) {
            const startItem = (currentPage - 1) * PRODUCTS_CONFIG.pagination.itemsPerPage + 1;
            const endItem = Math.min(currentPage * PRODUCTS_CONFIG.pagination.itemsPerPage, totalItems);
            document.getElementById('paginationInfo').textContent = `Mostrando ${startItem}-${endItem} de ${totalItems} registros`;
        },

        // Actualizar controles de paginación
        updatePaginationControls: function(currentPage, totalPages) {
            const prevBtn = document.getElementById('prevPage');
            const nextBtn = document.getElementById('nextPage');
            const pageNumbers = document.getElementById('pageNumbers');
            
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
            this.currentPage = page;
            this.showPage(page);
        },

        // Función de búsqueda
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

        // Aplicar filtros avanzados
        applyAdvancedFilters: function(categoryFilter, stockFilter) {
            this.filteredProducts = this.allProducts.filter(product => {
                let matches = true;
                
                // Filtro por categoría
                if (categoryFilter && categoryFilter !== '') {
                    const productCategoryId = product.element.querySelector('.category-text').getAttribute('data-category-id');
                    if (productCategoryId !== categoryFilter) {
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
            this.filteredProducts = [...this.allProducts];
            this.currentPage = 1;
            this.showPage(1);
            this.updateActiveFilters('', '');
        },

        // Obtener estado de stock del elemento
        getStockStatus: function(stockElement) {
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
            const filters = [];
            
            if (categoryFilter && categoryFilter !== '') {
                const categorySelect = document.getElementById('categoryFilter');
                const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                filters.push(selectedOption.text);
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
            try {
                const response = await fetch(`${PRODUCTS_CONFIG.routes.show}/${productId}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    // Llenar datos en el modal
                    document.getElementById('modalProductName').textContent = data.product.name;
                    document.getElementById('modalProductCode').textContent = data.product.code;
                    document.getElementById('modalProductCategory').textContent = data.product.category;
                    document.getElementById('modalProductDescription').textContent = data.product.description || 'Sin descripción';
                    document.getElementById('modalProductStock').textContent = data.product.stock;
                    document.getElementById('modalProductPurchasePrice').textContent = data.product.purchase_price;
                    document.getElementById('modalProductSalePrice').textContent = data.product.sale_price;
                    document.getElementById('modalProductCreated').textContent = data.product.created_at;
                    
                    // Mostrar modal
                    document.getElementById('showProductModal').style.display = 'flex';
                } else {
                    this.showAlert('Error', 'No se pudieron obtener los datos del producto', 'error');
                }
            } catch (error) {
                this.showAlert('Error', 'No se pudieron obtener los datos del producto', 'error');
            }
        },

        // Cerrar modal de producto
        closeProductModal: function() {
            document.getElementById('showProductModal').style.display = 'none';
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
                
                if (data.status === 'success') {
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
            
            // Búsqueda en tiempo real
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
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
            
            // Paginación
            document.getElementById('prevPage').addEventListener('click', function() {
                if (productsIndex.currentPage > 1) {
                    productsIndex.currentPage--;
                    productsIndex.showPage(productsIndex.currentPage);
                }
            });
            
            document.getElementById('nextPage').addEventListener('click', function() {
                const totalPages = Math.ceil(productsIndex.filteredProducts.length / PRODUCTS_CONFIG.pagination.itemsPerPage);
                if (productsIndex.currentPage < totalPages) {
                    productsIndex.currentPage++;
                    productsIndex.showPage(productsIndex.currentPage);
                }
            });
            
            // Filtros en tiempo real
            const categoryFilter = document.getElementById('categoryFilter');
            if (categoryFilter) {
                categoryFilter.addEventListener('change', function() {
                    const categoryValue = this.value;
                    const stockValue = document.getElementById('stockFilter').value;
                    productsIndex.applyAdvancedFilters(categoryValue, stockValue);
                });
            }
            
            const stockFilter = document.getElementById('stockFilter');
            if (stockFilter) {
                stockFilter.addEventListener('change', function() {
                    const stockValue = this.value;
                    const categoryValue = document.getElementById('categoryFilter').value;
                    productsIndex.applyAdvancedFilters(categoryValue, stockValue);
                });
            }
            
            // Limpiar filtros
            document.getElementById('clearFilters').addEventListener('click', function() {
                document.getElementById('categoryFilter').value = '';
                document.getElementById('stockFilter').value = '';
                productsIndex.clearAdvancedFilters();
            });
            
            // Cerrar modal al hacer clic fuera
            document.getElementById('showProductModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    productsIndex.closeProductModal();
                }
            });
        }
    };
}

// ===== FUNCIONES GLOBALES PARA COMPATIBILIDAD =====

// Función para mostrar detalles de producto (compatibilidad con onclick)
if (typeof window.showProductDetails === 'undefined') {
    window.showProductDetails = function(productId) {
        window.productsIndex.showProductDetails(productId);
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
