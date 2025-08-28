// ===== CONFIGURACIÓN OPTIMIZADA =====
const CONFIG = {
    routes: { delete: '/customers/delete' },
    exchangeRate: { default: 134.0, key: 'exchangeRate' }
};

// ===== FUNCIONES GLOBALES =====
window.customersIndex = {
    // Actualizar valores Bs - Optimizado
    updateBsValues: function(rate) {
        requestAnimationFrame(() => {
            document.querySelectorAll('.bs-debt').forEach(el => {
                const debt = parseFloat(el.dataset.debt);
                if (!isNaN(debt)) {
                    el.textContent = `Bs. ${(debt * rate).toLocaleString('es-VE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}`;
                }
            });
        });
    },

    // Función para eliminar cliente
    deleteCustomer: function(customerId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Obtener el token CSRF
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch(`${CONFIG.routes.delete}/${customerId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: data.message,
                            icon: data.icons
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        // Mostrar mensaje de error
                        let showCancelButton = false;
                        let cancelButtonText = '';
                        let confirmButtonText = 'Entendido';
                        
                        if (data.has_sales) {
                            showCancelButton = true;
                            cancelButtonText = 'Ver Ventas';
                            confirmButtonText = 'Entendido';
                        }
                        
                        Swal.fire({
                            title: data.icons === 'warning' ? 'No se puede eliminar' : 'Error',
                            html: data.message.replace(/\n/g, '<br>'),
                            icon: data.icons,
                            showCancelButton: showCancelButton,
                            cancelButtonText: cancelButtonText,
                            confirmButtonText: confirmButtonText
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.cancel && data.has_sales) {
                                // Redirigir a las ventas del cliente
                                window.location.href = data.sales_url;
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Ocurrió un error al eliminar el cliente',
                        icon: 'error'
                    });
                });
            }
        });
    }
};

// ===== FUNCIONES DE ALPINE.JS =====

// Función para el hero section
function heroSection() {
    return {
            init() {
        // Inicialización del hero section
    }
    }
}

// Panel de filtros - Optimizado
function filtersPanel() {
    return {
        filtersOpen: false,
        currentFilter: 'all',
        searchTerm: '',
        searchResultsCount: 0,
        hasActiveFilters: false,
        
        init() {
            setTimeout(() => {
                const saved = localStorage.getItem('customerFilter');
                if (saved) this.currentFilter = saved;
                this.updateSearchResultsCount();
            }, 0);
        },
        
        toggleFilters() { this.filtersOpen = !this.filtersOpen; },
        
        setFilter(filter) {
            this.currentFilter = filter;
            localStorage.setItem('customerFilter', filter);
            this.executeServerFilter(filter);
        },
        
        performSearch() {
            clearTimeout(this._searchTimeout);
            this._searchTimeout = setTimeout(() => this.applyFilters(), 300);
        },
        
        clearSearch() {
            this.searchTerm = '';
            this.applyFilters();
        },
        
        applyFilters() {
            requestAnimationFrame(() => {
                const cards = document.querySelectorAll('[data-defaulter]');
                let visibleCount = 0;
                
                cards.forEach(card => {
                    const isDefaulter = card.dataset.defaulter === 'true';
                    let shouldShow = false;
                    
                    switch (this.currentFilter) {
                        case 'all': shouldShow = true; break;
                        case 'defaulters': shouldShow = isDefaulter; break;
                    }
                    
                    if (shouldShow && this.searchTerm) {
                        const text = card.textContent.toLowerCase();
                        shouldShow = text.includes(this.searchTerm.toLowerCase());
                    }
                    
                    card.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) visibleCount++;
                });
                
                this.searchResultsCount = visibleCount;
                this.hasActiveFilters = this.currentFilter !== 'all' || this.searchTerm.length > 0;
            });
        },
        
        updateSearchResultsCount() {
            const visible = document.querySelectorAll('[data-defaulter]:not([style*="display: none"])');
            this.searchResultsCount = visible.length;
        },
        
        executeServerFilter(filter) {
            
            // Construir URL con parámetros de filtro
            const url = new URL(window.location.href);
            if (filter && filter !== 'all') {
                url.searchParams.set('filter', filter);
            } else {
                url.searchParams.delete('filter');
            }
            
            // Mantener búsqueda si existe
            const searchTerm = document.querySelector('input[x-model="searchTerm"]')?.value;
            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            }
            
            // Realizar petición AJAX
            fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, application/xhtml+xml'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al aplicar filtro');
                }
                return response.text();
            })
            .then(html => {
                // Actualizar la tabla con los nuevos resultados
                this.updateTableWithFilterResults(html, filter);
            })
            .catch(error => {
                console.error('Error al aplicar filtro:', error);
            });
        },
        
        updateTableWithFilterResults(html, filter) {
            // Crear un elemento temporal para parsear el HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            // Extraer la nueva tabla
            const newTableBody = tempDiv.querySelector('#customersTableBody');
            const newCardsContainer = tempDiv.querySelector('#mobileCustomersContainer');
            const newMobileContainer = tempDiv.querySelector('#mobileOnlyContainer');
            const newPagination = tempDiv.querySelector('.custom-pagination');
            
            // Actualizar la tabla
            const currentTableBody = document.getElementById('customersTableBody');
            if (newTableBody && currentTableBody) {
                currentTableBody.innerHTML = newTableBody.innerHTML;
            }
            
            // Actualizar las tarjetas
            const currentCardsContainer = document.getElementById('mobileCustomersContainer');
            if (newCardsContainer && currentCardsContainer) {
                currentCardsContainer.innerHTML = newCardsContainer.innerHTML;
            }
            
            // Actualizar vista móvil
            const currentMobileContainer = document.getElementById('mobileOnlyContainer');
            if (newMobileContainer && currentMobileContainer) {
                currentMobileContainer.innerHTML = newMobileContainer.innerHTML;
            }
            
            // Actualizar paginación
            const currentPagination = document.querySelector('.custom-pagination');
            if (newPagination && currentPagination) {
                currentPagination.innerHTML = newPagination.innerHTML;
            }
            
            // Actualizar contador de resultados
            this.updateSearchResultsCount();
            
            // Actualizar URL sin recargar la página
            const url = new URL(window.location.href);
            if (filter && filter !== 'all') {
                url.searchParams.set('filter', filter);
            } else {
                url.searchParams.delete('filter');
            }
            window.history.pushState({}, '', url.toString());
        },
        

    };
}

// Función para sincronización de tipo de cambio en modal
function modalExchangeRateSync() {
    return {
        init() {
            // Sincronizar con el widget principal cuando se abre el modal
            this.$watch('debtReportModal', (value) => {
                if (value) {
                    this.syncFromWidget();
                }
            });
        },
        
        // Sincronizar desde el widget principal
        syncFromWidget() {
            const widgetElements = document.querySelectorAll('[x-data*="exchangeRateWidget"]');
            widgetElements.forEach(element => {
                if (element._x_dataStack && element._x_dataStack[0]) {
                    const widget = element._x_dataStack[0];
                    const modalInput = document.getElementById('modalExchangeRate');
                    if (modalInput) {
                        modalInput.value = widget.exchangeRate;
                    }
                }
            });
        },
        
        // Sincronizar hacia el widget principal
        syncFromModal(value) {
            const rate = parseFloat(value);
            if (!isNaN(rate) && rate > 0) {
                // Sincronizar con el widget principal
                const widgetElements = document.querySelectorAll('[x-data*="exchangeRateWidget"]');
                widgetElements.forEach(element => {
                    if (element._x_dataStack && element._x_dataStack[0]) {
                        const widget = element._x_dataStack[0];
                        widget.syncFromModal(rate);
                    }
                });
            }
        }
    };
}

// Función para el widget de tipo de cambio
function exchangeRateWidget() {
    return {
        exchangeRate: 134.0,
        updating: false,
        
        init() {
            // Cargar el tipo de cambio guardado
            const savedRate = localStorage.getItem('exchangeRate');
            if (savedRate) {
                this.exchangeRate = parseFloat(savedRate);
            } else {
                this.exchangeRate = window.exchangeRate || 134.0;
            }
            
            // Watcher para sincronizar automáticamente cuando cambie el valor
            this.$watch('exchangeRate', (value) => {
                if (value > 0) {
                    this.syncToModal();
                }
            });
        },
        
        updateRate() {
            if (this.exchangeRate <= 0) return;
            
            this.updating = true;
            
            // Simular actualización
            setTimeout(() => {
                window.currentExchangeRate = this.exchangeRate;
                localStorage.setItem('exchangeRate', this.exchangeRate);
                window.customersIndex.updateBsValues(this.exchangeRate);
                
                // Sincronizar con el modal
                this.syncToModal();
                
                this.updating = false;
                
                // Mostrar notificación
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tipo de cambio actualizado',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            }, 500);
        },
        
        // Sincronizar con el modal
        syncToModal() {
            const modalInput = document.getElementById('modalExchangeRate');
            if (modalInput) {
                modalInput.value = this.exchangeRate;
            }
            
            // También actualizar valores en Bs en el modal si está abierto
            if (typeof window.modalManager !== 'undefined' && window.modalManager().debtReportModal) {
                if (typeof window.customersIndex !== 'undefined' && window.customersIndex.updateBsValues) {
                    window.customersIndex.updateBsValues(this.exchangeRate);
                }
            }
        },
        
        // Sincronizar desde el modal
        syncFromModal(rate) {
            this.exchangeRate = rate;
        }
    }
}

// Función para la tabla de datos
function dataTable() {
    return {
        viewMode: window.innerWidth >= 768 ? 'table' : 'cards',
        searchTerm: '',
        searchResultsCount: 0,
        isSearching: false,
        isLoadingPage: false,
        _lastPageRequestId: 0,
        
        init() {
            // Detectar el modo de vista inicial basado en el tamaño de pantalla
            this.updateViewMode();
            
            // Escuchar cambios de tamaño de ventana
            window.addEventListener('resize', () => {
                this.updateViewMode();
            });
            
            // Watcher para búsqueda en tiempo real
            this.$watch('searchTerm', () => {
                this.performSearch();
            });
            
            // Watcher para cambio de modo de vista
            this.$watch('viewMode', () => {
                this.updateSearchResultsCount();
            });
            
            // Actualizar contador de resultados
            this.updateSearchResultsCount();
            
            // Inicializar búsqueda desde URL si existe
            this.initializeSearchFromURL();
            
            // Inicializar manejadores de paginación
            this.initializePaginationHandlers();
        },
        
        initializeSearchFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            const searchParam = urlParams.get('search');
            const filterParam = urlParams.get('filter');
            
            if (searchParam) {
                this.searchTerm = searchParam;
                // No ejecutar búsqueda automáticamente para evitar doble búsqueda
            }
            
            if (filterParam) {
                this.currentFilter = filterParam;
                localStorage.setItem('customerFilter', filterParam);
            }
        },
        
        initializePaginationHandlers() {
            // Delegar eventos de clic para enlaces de paginación
            document.addEventListener('click', (e) => {
                if (e.target.closest('.pagination-btn') || e.target.closest('.page-number')) {
                    e.preventDefault();
                    if (this.isLoadingPage) return;
                    const link = e.target.closest('a');
                    if (link) {
                        this.loadPage(link.href);
                    }
                }
            });
        },
        
        loadPage(url) {
            // Asignar ID de solicitud para invalidar respuestas antiguas
            const requestId = ++this._lastPageRequestId;
            this.isLoadingPage = true;

            this.setPaginationDisabled(true);
            
            // Realizar petición AJAX para la nueva página
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, application/xhtml+xml'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al cargar la página');
                }
                return response.text();
            })
            .then(html => {
                // Descartar respuesta si ya existe una solicitud más nueva
                if (requestId !== this._lastPageRequestId) return;
                // Actualizar la tabla con los nuevos resultados
                this.updateTableWithSearchResults(html, this.searchTerm);
                // Scroll al inicio del contenedor principal
                try {
                    const container = document.querySelector('.table-container') || document.body;
                    const topTarget = container.getBoundingClientRect ? (window.scrollY + container.getBoundingClientRect().top - 80) : 0;
                    window.scrollTo({ top: Math.max(0, topTarget), behavior: 'smooth' });
                } catch (_) {}
                
                // Actualizar URL sin recargar la página
                window.history.pushState({}, '', url);
                // Asegurar que la página activa quede visible centrada en el contenedor
                setTimeout(() => {
                    try {
                        const numbers = document.querySelector('.page-numbers');
                        const active = numbers ? numbers.querySelector('.page-number.active') : null;
                        if (numbers && active) {
                            const offsetLeft = active.offsetLeft - (numbers.clientWidth / 2) + (active.clientWidth / 2);
                            numbers.scrollTo({ left: Math.max(0, offsetLeft), behavior: 'smooth' });
                        }
                    } catch (_) {}
                }, 0);
            })
            .catch(error => {
                console.error('Error al cargar página:', error);
                this.showSearchError('Error al cargar la página. Intenta nuevamente.');
            })
            .finally(() => {
                this.setPaginationDisabled(false);
                this.isLoadingPage = false;
            });
        },
        
        updateViewMode() {
            this.viewMode = window.innerWidth >= 768 ? 'table' : 'cards';
        },
        
        setViewMode(mode) {
            this.viewMode = mode;
        },
        
        performSearch() {
            // Búsqueda del lado del servidor con debounce
            this.isSearching = true;
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.executeServerSearch();
                this.isSearching = false;
            }, 300); // 500ms de debounce para búsqueda del servidor
        },

        executeServerSearch() {
            const searchTerm = this.searchTerm.trim();
            
            // Construir URL con parámetros de búsqueda
            const url = new URL(window.location.href);
            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            } else {
                url.searchParams.delete('search');
            }
            
            // Mantener filtro activo si existe
            const currentFilter = document.querySelector('[x-data="filtersPanel()"]')?._x_dataStack?.[0]?.currentFilter;
            if (currentFilter && currentFilter !== 'all') {
                url.searchParams.set('filter', currentFilter);
            }
            
            // Realizar petición AJAX
            fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, application/xhtml+xml'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la búsqueda');
                }
                return response.text();
            })
            .then(html => {
                // Actualizar la tabla con los nuevos resultados
                this.updateTableWithSearchResults(html, searchTerm);
            })
            .catch(error => {
                console.error('Error en búsqueda:', error);
                this.showSearchError('Error al realizar la búsqueda');
            });
        },

        updateTableWithSearchResults(html, searchTerm) {
            // Crear un elemento temporal para parsear el HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            // Extraer la nueva tabla
            const newTableBody = tempDiv.querySelector('#customersTableBody');
            const newCardsContainer = tempDiv.querySelector('#mobileCustomersContainer');
            const newMobileContainer = tempDiv.querySelector('#mobileOnlyContainer');
            const newPagination = tempDiv.querySelector('.custom-pagination');
            
            // Actualizar la tabla
            const currentTableBody = document.getElementById('customersTableBody');
            if (newTableBody && currentTableBody) {
                currentTableBody.innerHTML = newTableBody.innerHTML;
            }
            
            // Actualizar las tarjetas
            const currentCardsContainer = document.getElementById('mobileCustomersContainer');
            if (newCardsContainer && currentCardsContainer) {
                currentCardsContainer.innerHTML = newCardsContainer.innerHTML;
            }
            
            // Actualizar vista móvil
            const currentMobileContainer = document.getElementById('mobileOnlyContainer');
            if (newMobileContainer && currentMobileContainer) {
                currentMobileContainer.innerHTML = newMobileContainer.innerHTML;
            }
            
            // Actualizar paginación
            const currentPagination = document.querySelector('.custom-pagination');
            if (newPagination && currentPagination) {
                currentPagination.innerHTML = newPagination.innerHTML;
            }
            
            // Actualizar contador de resultados
            this.updateSearchResultsCount();
            
            // Mostrar mensaje si no hay resultados
            const totalVisible = this.getVisibleCount();
            this.showNoResultsMessage(totalVisible === 0 && searchTerm !== '');
            
            // Actualizar URL sin recargar la página
            if (searchTerm) {
                window.history.pushState({}, '', `?search=${encodeURIComponent(searchTerm)}`);
            } else {
                window.history.pushState({}, '', window.location.pathname);
            }
        },



        // Deshabilitar/enhabilitar controles de paginado mientras hay carga
        setPaginationDisabled(disabled) {
            try {
                const pagination = document.querySelector('.custom-pagination');
                if (!pagination) return;
                const links = pagination.querySelectorAll('a.page-number, a.pagination-btn');
                links.forEach(a => {
                    if (disabled) {
                        a.setAttribute('data-href', a.getAttribute('href'));
                        a.removeAttribute('href');
                        a.classList.add('pointer-events-none', 'opacity-60');
                    } else {
                        const saved = a.getAttribute('data-href');
                        if (saved) a.setAttribute('href', saved);
                        a.removeAttribute('data-href');
                        a.classList.remove('pointer-events-none', 'opacity-60');
                    }
                });
            } catch (_) {}
        },

        showSearchError(message) {
            // Mostrar error de búsqueda
            Swal.fire({
                icon: 'error',
                title: 'Error en la búsqueda',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        },
        
        // Buscar en modo tabla
        searchInTable(searchTerm) {
            const table = document.getElementById('customersTable');
            if (!table) return;
            
            const rows = table.querySelectorAll('tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const matches = text.includes(searchTerm);
                
                if (searchTerm === '' || matches) {
                    row.style.display = '';
                    visibleCount++;
                    
                    if (searchTerm !== '') {
                        this.highlightSearchTerms(row, searchTerm);
                    } else {
                        this.removeHighlights(row);
                    }
                } else {
                    row.style.display = 'none';
                }
            });
        },
        
        // Buscar en modo tarjetas
        searchInCards(searchTerm) {
            const cardsContainer = document.getElementById('mobileCustomersContainer');
            if (!cardsContainer) return;
            
            const cards = cardsContainer.querySelectorAll('.bg-white.rounded-2xl');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                const matches = text.includes(searchTerm);
                
                if (searchTerm === '' || matches) {
                    card.style.display = '';
                    
                    if (searchTerm !== '') {
                        this.highlightSearchTermsInCard(card, searchTerm);
                    } else {
                        this.removeHighlightsFromCard(card);
                    }
                } else {
                    card.style.display = 'none';
                }
            });
        },
        
        // Resaltar términos de búsqueda
        highlightSearchTerms(row, searchTerm) {
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                // Solo resaltar en elementos de texto específicos, nunca en botones
                this.highlightSafeElements(cell, searchTerm);
            });
        },
        
        // Resaltar elementos de forma segura sin tocar botones
        highlightSafeElements(cell, searchTerm) {
            // Lista de elementos seguros para resaltar
            const safeSelectors = [
                '.customer-name',
                '.customer-email', 
                '.id-badge',
                '.sales-amount',
                '.sales-count',
                '.debt-amount-value',
                '.no-sales',
                '.no-debt-badge'
            ];
            
            safeSelectors.forEach(selector => {
                const elements = cell.querySelectorAll(selector);
                elements.forEach(element => {
                    if (element.children.length === 0) { // Solo elementos sin hijos
                        this.highlightElement(element, searchTerm);
                    }
                });
            });
        },
        
        // Resaltar un elemento específico
        highlightElement(element, searchTerm) {
            const originalText = element.getAttribute('data-original-text') || element.textContent;
            if (!element.hasAttribute('data-original-text')) {
                element.setAttribute('data-original-text', originalText);
            }
            
            // Escapar caracteres especiales en el término de búsqueda
            const escapedSearchTerm = searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`(${escapedSearchTerm})`, 'gi');
            const highlightedText = originalText.replace(regex, '<mark class="bg-yellow-200 text-yellow-800 px-1 rounded">$1</mark>');
            element.innerHTML = highlightedText;
        },
        
        // Resaltar términos de búsqueda en tarjetas
        highlightSearchTermsInCard(card, searchTerm) {
            // Lista de elementos seguros para resaltar en tarjetas
            const safeSelectors = [
                'h3', // nombre del cliente
                'span', // email, teléfono, etc.
                'p' // otros textos
            ];
            
            safeSelectors.forEach(selector => {
                const elements = card.querySelectorAll(selector);
                elements.forEach(element => {
                    // Solo resaltar elementos que no contengan botones y no sean badges de estado
                    const hasButtons = element.querySelector('button, a');
                    const isStatusBadge = element.closest('.inline-flex');
                    
                    if (!hasButtons && !isStatusBadge && element.children.length === 0) {
                        this.highlightElement(element, searchTerm);
                    }
                });
            });
        },
        
        // Remover resaltados de tarjetas
        removeHighlightsFromCard(card) {
            const highlightedElements = card.querySelectorAll('mark');
            highlightedElements.forEach(mark => {
                const parent = mark.parentElement;
                const originalText = parent.getAttribute('data-original-text');
                if (originalText) {
                    parent.innerHTML = originalText;
                }
            });
        },
        

        
        // Remover resaltados
        removeHighlights(row) {
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                // Remover resaltados de elementos específicos
                const highlightedElements = cell.querySelectorAll('mark');
                highlightedElements.forEach(mark => {
                    const parent = mark.parentElement;
                    const originalText = parent.getAttribute('data-original-text');
                    if (originalText) {
                        parent.innerHTML = originalText;
                    }
                });
            });
        },
        
        // Manejar teclas especiales
        handleKeydown(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.executeServerSearch();
            } else if (event.key === 'Escape') {
                this.clearSearch();
            }
        },
        
        // Mostrar mensaje de no resultados
        showNoResultsMessage(show) {
            // Mostrar mensaje en la tabla
            this.showNoResultsInTable(show);
            
            // Mostrar mensaje en las tarjetas
            this.showNoResultsInCards(show);
        },
        
        // Mostrar mensaje de no resultados en tabla
        showNoResultsInTable(show) {
            const table = document.getElementById('customersTable');
            if (!table) return;
            
            let noResultsRow = table.querySelector('.no-results-row');
            
            if (show) {
                if (!noResultsRow) {
                    const tbody = table.querySelector('tbody');
                    noResultsRow = document.createElement('tr');
                    noResultsRow.className = 'no-results-row';
                    noResultsRow.innerHTML = `
                        <td colspan="100%" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-search text-4xl mb-4"></i>
                                <p class="text-lg font-medium">No se encontraron resultados</p>
                                <p class="text-sm">Intenta con otros términos de búsqueda</p>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(noResultsRow);
                }
                noResultsRow.style.display = '';
            } else if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        },
        
        // Mostrar mensaje de no resultados en tarjetas
        showNoResultsInCards(show) {
            const cardsContainer = document.getElementById('mobileCustomersContainer');
            if (!cardsContainer) return;
            
            let noResultsCard = cardsContainer.querySelector('.no-results-card');
            
            if (show) {
                if (!noResultsCard) {
                    noResultsCard = document.createElement('div');
                    noResultsCard.className = 'no-results-card col-span-full';
                    noResultsCard.innerHTML = `
                        <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-search text-4xl mb-4"></i>
                                <p class="text-lg font-medium">No se encontraron resultados</p>
                                <p class="text-sm">Intenta con otros términos de búsqueda</p>
                            </div>
                        </div>
                    `;
                    cardsContainer.appendChild(noResultsCard);
                }
                noResultsCard.style.display = '';
            } else if (noResultsCard) {
                noResultsCard.style.display = 'none';
            }
        },
        
        clearSearch() {
            this.searchTerm = '';
            this.searchResultsCount = 0;
            
            // Realizar búsqueda del servidor para limpiar filtros pero mantener filtro activo
            this.executeServerSearch();
        },
        
        updateSearchResultsCount() {
            this.searchResultsCount = this.getVisibleCount();
        },
        
        // Obtener conteo total de elementos visibles
        getVisibleCount() {
            let totalVisible = 0;
            
            // Contar elementos visibles según el modo de vista activo
            if (this.viewMode === 'table') {
                const table = document.getElementById('customersTable');
                if (table) {
                    const visibleRows = table.querySelectorAll('tbody tr:not([style*="display: none"])');
                    totalVisible = visibleRows.length;
                }
            } else if (this.viewMode === 'cards') {
                const cardsContainer = document.getElementById('mobileCustomersContainer');
                if (cardsContainer) {
                    const visibleCards = cardsContainer.querySelectorAll('.bg-white.rounded-2xl:not([style*="display: none"])');
                    totalVisible = visibleCards.length;
                }
            }
            
            return totalVisible;
        },
        
        applyFilters(currentFilter, searchTerm) {
            // Esta función se puede usar para filtrar la tabla si es necesario
            this.updateSearchResultsCount();
        }
    }
}

// ===== FUNCIONES DE INICIALIZACIÓN =====

// Función para inicializar el tipo de cambio
window.initializeExchangeRate = function() {
    const savedRate = localStorage.getItem('exchangeRate');
    const rate = savedRate ? parseFloat(savedRate) : (window.exchangeRate || CONFIG.exchangeRate.default);
    
    // Actualizar el input si existe
    const exchangeRateInput = document.getElementById('exchangeRate');
    if (exchangeRateInput) {
        exchangeRateInput.value = rate;
    }
    
    // Actualizar valores en Bs
    window.customersIndex.updateBsValues(rate);
    
    return rate;
}

// Función para guardar el tipo de cambio
window.saveExchangeRate = function(rate) {
    window.currentExchangeRate = rate;
    localStorage.setItem(CONFIG.exchangeRate.key, rate);
    window.customersIndex.updateBsValues(rate);
}

// Función para sincronizar todos los elementos de tipo de cambio
window.syncAllExchangeRateElements = function() {
    const rate = window.currentExchangeRate || window.initializeExchangeRate();
    
    // Actualizar todos los inputs de tipo de cambio
    const exchangeRateInputs = document.querySelectorAll('input[name="exchange_rate"], #exchangeRate');
    exchangeRateInputs.forEach(input => {
        input.value = rate;
    });
    
    // Actualizar valores en Bs
    window.customersIndex.updateBsValues(rate);
}

// ===== FUNCIONES DE UTILIDAD =====

// Función para mostrar notificaciones
function showNotification(message, type = 'success') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        alert(message);
    }
}

// Función para formatear moneda
function formatCurrency(amount, currency = 'USD') {
    if (currency === 'USD') {
        return '$ ' + parseFloat(amount).toFixed(2);
    } else if (currency === 'BS') {
        return 'Bs. ' + parseFloat(amount).toLocaleString('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    return parseFloat(amount).toFixed(2);
}

// Función para formatear fecha
function formatDate(date) {
    return new Date(date).toLocaleDateString('es-ES');
}

// Función para formatear fecha y hora
function formatDateTime(date) {
    return new Date(date).toLocaleString('es-ES');
}

// ===== INICIALIZACIÓN OPTIMIZADA =====
document.addEventListener('DOMContentLoaded', function() {
    window.initializeExchangeRate();
    

    
    // Inicialización no crítica diferida
    setTimeout(() => {
        setupExchangeRateEvents();
        setupOptimizedEvents();
    }, 100);
});

// ===== FUNCIONES DE CONFIGURACIÓN DE EVENTOS =====

function setupExchangeRateEvents() {
    document.addEventListener('click', function(e) {
        if (e.target.matches('.update-exchange-rate')) {
            const input = document.getElementById('exchangeRate');
            if (input) {
                const rate = parseFloat(input.value);
                if (rate > 0) {
                    window.currentExchangeRate = rate;
                    localStorage.setItem(CONFIG.exchangeRate.key, rate);
                    window.customersIndex.updateBsValues(rate);
                    
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Tipo de cambio actualizado',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                }
            }
        }
    });
}

function setupOptimizedEvents() {
    // Event delegation optimizado
    document.addEventListener('click', function(e) {
        // Manejar botones de eliminación
        if (e.target.closest('[onclick*="deleteCustomer"]')) {
            e.preventDefault();
            const customerId = e.target.closest('[onclick*="deleteCustomer"]')
                .getAttribute('onclick')
                .match(/\d+/)?.[0];
            if (customerId) {
                window.customersIndex.deleteCustomer(customerId);
            }
        }
    });
}

// Utilidades simplificadas
function showNotification(message, type = 'success') {
    if (window.Swal) {
        Swal.fire({
            icon: type,
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
        });
    }
}

// ===== FUNCIONES GLOBALES ESENCIALES =====
window.heroSection = heroSection;
window.filtersPanel = filtersPanel;
window.exchangeRateWidget = exchangeRateWidget;
window.modalExchangeRateSync = modalExchangeRateSync;
window.dataTable = dataTable;
window.initializeExchangeRate = initializeExchangeRate;
window.showNotification = showNotification;

// Hacer la función deleteCustomer disponible globalmente
window.deleteCustomer = function(customerId) {
    if (window.customersIndex && window.customersIndex.deleteCustomer) {
        window.customersIndex.deleteCustomer(customerId);
    } else {
        console.error('deleteCustomer function not available');
    }
};

// Función de inicialización inmediata
(function() {
    // Función para asegurar que deleteCustomer esté disponible
    function ensureDeleteCustomerAvailable() {
        if (typeof window.deleteCustomer === 'undefined') {
            window.deleteCustomer = function(customerId) {
                if (window.customersIndex && window.customersIndex.deleteCustomer) {
                    window.customersIndex.deleteCustomer(customerId);
                } else {
                    console.error('deleteCustomer function not available');
                }
            };
        }
    }
    
    // Ejecutar inmediatamente
    ensureDeleteCustomerAvailable();
    
    // Ejecutar cuando Alpine.js esté disponible
    if (typeof Alpine !== 'undefined') {
        Alpine.nextTick(() => {
            ensureDeleteCustomerAvailable();
        });
    }
    
    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureDeleteCustomerAvailable);
    } else {
        ensureDeleteCustomerAvailable();
    }
    
    // Ejecutar cuando la ventana esté completamente cargada
    window.addEventListener('load', ensureDeleteCustomerAvailable);
})();

// ===== SPA PAYMENT HANDLER =====
/**
 * SPA Payment Handler para Customers
 * Maneja pagos de deuda sin recargar la página
 */

class SPAPaymentHandler {
    constructor() {
        this.isProcessing = false;
        this.currentCustomerId = null;
        this.exchangeRate = window.exchangeRate || 134;
        this.currencySymbol = '$';
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupFormValidation();
        this.setupRealTimeValidation();
    }

    bindEvents() {
        // Los eventos se vincularán cuando se abra el modal
    }

    bindModalEvents() {
        // Interceptar envío del formulario
        const form = document.getElementById('debtPaymentForm');
        if (form) {
            // Crear función bound para poder removerla después
            if (!this.boundHandleFormSubmit) {
                this.boundHandleFormSubmit = (e) => this.handleFormSubmit(e);
            }
            // Remover eventos anteriores para evitar duplicados
            form.removeEventListener('submit', this.boundHandleFormSubmit);
            form.addEventListener('submit', this.boundHandleFormSubmit);
        }

        // Botón de pago máximo
        const maxPaymentBtn = document.getElementById('max_payment_btn');
        if (maxPaymentBtn) {
            // Crear función bound para poder removerla después
            if (!this.boundSetMaxPayment) {
                this.boundSetMaxPayment = () => this.setMaxPayment();
            }
            // Remover eventos anteriores para evitar duplicados
            maxPaymentBtn.removeEventListener('click', this.boundSetMaxPayment);
            maxPaymentBtn.addEventListener('click', this.boundSetMaxPayment);
        }

        // Validación en tiempo real del monto y actualización de deuda restante
        const paymentAmountInput = document.getElementById('payment_amount');
        if (paymentAmountInput) {
            // Crear función bound para poder removerla después
            if (!this.boundHandlePaymentAmountInput) {
                this.boundHandlePaymentAmountInput = () => {
                    this.validatePaymentAmount();
                    this.updateRemainingDebt();
                };
            }
            if (!this.boundFormatPaymentAmount) {
                this.boundFormatPaymentAmount = () => this.formatPaymentAmount();
            }
            
            // Remover eventos anteriores para evitar duplicados
            paymentAmountInput.removeEventListener('input', this.boundHandlePaymentAmountInput);
            paymentAmountInput.addEventListener('input', this.boundHandlePaymentAmountInput);
            paymentAmountInput.removeEventListener('blur', this.boundFormatPaymentAmount);
            paymentAmountInput.addEventListener('blur', this.boundFormatPaymentAmount);
        }

        // Validación de fecha
        const paymentDateInput = document.getElementById('payment_date');
        if (paymentDateInput) {
            if (!this.boundValidatePaymentDate) {
                this.boundValidatePaymentDate = () => this.validatePaymentDate();
            }
            paymentDateInput.removeEventListener('change', this.boundValidatePaymentDate);
            paymentDateInput.addEventListener('change', this.boundValidatePaymentDate);
        }

        // Validación de hora
        const paymentTimeInput = document.getElementById('payment_time');
        if (paymentTimeInput) {
            if (!this.boundValidatePaymentTime) {
                this.boundValidatePaymentTime = () => this.validatePaymentTime();
            }
            paymentTimeInput.removeEventListener('change', this.boundValidatePaymentTime);
            paymentTimeInput.addEventListener('change', this.boundValidatePaymentTime);
        }
    }

    setupFormValidation() {
        // Configurar validaciones del formulario
        this.validationRules = {
            payment_amount: {
                required: true,
                min: 0.01,
                max: 0 // Se actualizará dinámicamente
            },
            payment_date: {
                required: true,
                maxDate: new Date().toISOString().split('T')[0]
            },
            payment_time: {
                required: true,
                pattern: /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/
            }
        };
    }

    setupRealTimeValidation() {
        // Configurar validaciones en tiempo real
        this.realTimeValidation = {
            payment_amount: () => this.validatePaymentAmount(),
            payment_date: () => this.validatePaymentDate(),
            payment_time: () => this.validatePaymentTime()
        };
    }

    async handleFormSubmit(e) {
        e.preventDefault();

        if (this.isProcessing) {
            return;
        }

        // Validar formulario
        if (!this.validateForm()) {
            return;
        }

        // Mostrar confirmación antes de enviar
        const formData = this.getFormData();
        const customerName = document.getElementById('customer_name').value;
        const paymentAmount = formData.payment_amount;
        
        const result = await Swal.fire({
            title: '<div class="flex items-center justify-center space-x-3 mb-4">' +
                   '<div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full flex items-center justify-center">' +
                   '<i class="fas fa-money-bill-wave text-white text-xl"></i>' +
                   '</div>' +
                   '<h2 class="text-2xl font-bold text-gray-800">¿Confirmar Pago?</h2>' +
                   '</div>',
            html: `
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                    <div class="space-y-4">
                        <!-- Información del Cliente -->
                        <div class="flex items-center space-x-3 p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">CLIENTE</p>
                                <p class="text-sm font-semibold text-gray-800">${customerName}</p>
                            </div>
                        </div>
                        
                        <!-- Monto del Pago -->
                        <div class="flex items-center space-x-3 p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">MONTO A PAGAR</p>
                                <p class="text-lg font-bold text-green-600">${this.currencySymbol}${paymentAmount.toFixed(2)}</p>
                            </div>
                        </div>
                        
                        <!-- Fecha y Hora -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex items-center space-x-3 p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                                <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">FECHA</p>
                                    <p class="text-sm font-semibold text-gray-800">${formData.payment_date}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3 p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                                <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-clock text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">HORA</p>
                                    <p class="text-sm font-semibold text-gray-800">${formData.payment_time}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mensaje informativo -->
                    <div class="mt-4 p-3 bg-blue-100 rounded-lg border border-blue-200">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-info-circle text-blue-600"></i>
                            <p class="text-xs text-blue-700 font-medium">Este pago se registrará inmediatamente y actualizará la deuda del cliente</p>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-check mr-2"></i>Sí, Registrar Pago',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-lg px-6 py-3 font-semibold',
                cancelButton: 'rounded-lg px-6 py-3 font-semibold'
            },
            width: '500px',
            padding: '2rem'
        });

        if (!result.isConfirmed) {
            return;
        }

        // Mostrar loading
        this.showLoadingState();

        try {
            const response = await this.sendPaymentRequest(formData);

            if (response.success) {
                await this.handlePaymentSuccess(response);
            } else {
                this.handlePaymentError(response);
            }
        } catch (error) {
            this.handleNetworkError(error);
        } finally {
            this.hideLoadingState();
        }
    }

    validateForm() {
        let isValid = true;
        const errors = {};

        // Validar monto
        if (!this.validatePaymentAmount()) {
            isValid = false;
            errors.payment_amount = 'Monto inválido';
        }

        // Validar fecha
        if (!this.validatePaymentDate()) {
            isValid = false;
            errors.payment_date = 'Fecha inválida';
        }

        // Validar hora
        if (!this.validatePaymentTime()) {
            isValid = false;
            errors.payment_time = 'Hora inválida';
        }

        if (!isValid) {
            this.showValidationErrors(errors);
        }

        return isValid;
    }

    validatePaymentAmount() {
        const input = document.getElementById('payment_amount');
        const amount = parseFloat(input.value);
        const maxDebt = parseFloat(input.getAttribute('data-max-debt') || 0);

        if (isNaN(amount) || amount <= 0) {
            this.showFieldError('payment_amount', 'El monto debe ser mayor a 0');
            return false;
        }

        if (amount > maxDebt) {
            this.showFieldError('payment_amount', `El monto no puede ser mayor a ${this.currencySymbol}${maxDebt.toFixed(2)}`);
            return false;
        }

        this.clearFieldError('payment_amount');
        return true;
    }

    validatePaymentDate() {
        const input = document.getElementById('payment_date');
        const selectedDateStr = input.value;
        
        if (!selectedDateStr) {
            this.showFieldError('payment_date', 'La fecha es requerida');
            return false;
        }

        // Usar formato de fecha simple para evitar problemas de zona horaria
        const today = new Date();
        const todayStr = today.getFullYear() + '-' + 
                         String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                         String(today.getDate()).padStart(2, '0');

        if (selectedDateStr > todayStr) {
            this.showFieldError('payment_date', 'La fecha no puede ser mayor a hoy');
            return false;
        }

        this.clearFieldError('payment_date');
        return true;
    }

    validatePaymentTime() {
        const input = document.getElementById('payment_time');
        const timePattern = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;

        if (!timePattern.test(input.value)) {
            this.showFieldError('payment_time', 'Formato de hora inválido (HH:MM)');
            return false;
        }

        this.clearFieldError('payment_time');
        return true;
    }

    formatPaymentAmount() {
        const input = document.getElementById('payment_amount');
        const value = parseFloat(input.value);
        
        if (!isNaN(value)) {
            input.value = value.toFixed(2);
        }
    }

    setMaxPayment() {
        const input = document.getElementById('payment_amount');
        if (!input) {
            return;
        }
        
        const maxDebt = parseFloat(input.getAttribute('data-max-debt') || 0);
        
        input.value = maxDebt.toFixed(2);
        this.validatePaymentAmount();
        this.updateRemainingDebt();
    }

    updateRemainingDebt() {
        const paymentAmountInput = document.getElementById('payment_amount');
        const remainingDebtElement = document.getElementById('remaining_debt');
        
        if (!paymentAmountInput) {
            return;
        }
        
        if (!remainingDebtElement) {
            return;
        }
        
        const currentDebt = parseFloat(paymentAmountInput.getAttribute('data-max-debt') || 0);
        const paymentAmount = parseFloat(paymentAmountInput.value) || 0;
        const remainingDebt = Math.max(0, currentDebt - paymentAmount);
        
        // Actualizar el elemento de deuda restante
        const remainingDebtSpan = remainingDebtElement.querySelector('span');
        if (remainingDebtSpan) {
            remainingDebtSpan.textContent = `${this.currencySymbol}${remainingDebt.toFixed(2)}`;
        }
        
        // Cambiar color según si hay deuda restante
        if (remainingDebt > 0) {
            remainingDebtElement.className = 'w-full pl-12 pr-12 py-2.5 bg-orange-50 border border-orange-200 rounded-lg text-orange-700 font-semibold text-sm flex items-center';
        } else {
            remainingDebtElement.className = 'w-full pl-12 pr-12 py-2.5 bg-green-50 border border-green-200 rounded-lg text-green-700 font-semibold text-sm flex items-center';
        }
    }

    getFormData() {
        const form = document.getElementById('debtPaymentForm');
        const formData = new FormData(form);
        
        const data = {
            payment_amount: parseFloat(formData.get('payment_amount')),
            payment_date: formData.get('payment_date'),
            payment_time: formData.get('payment_time'),
            notes: formData.get('notes') || ''
        };
        
        return data;
    }

    async sendPaymentRequest(data) {
        const url = `/admin/customers/${this.currentCustomerId}/register-payment-ajax`;
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(data)
        });
        
        const responseData = await response.json();
        
        return responseData;
    }

    async handlePaymentSuccess(response) {
        // Mostrar notificación de éxito mejorada
        await Swal.fire({
            title: '<div class="flex items-center justify-center space-x-3 mb-4">' +
                   '<div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full flex items-center justify-center">' +
                   '<i class="fas fa-check text-white text-xl"></i>' +
                   '</div>' +
                   '<h2 class="text-2xl font-bold text-gray-800">¡Pago Registrado!</h2>' +
                   '</div>',
            html: `
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
                    <div class="text-center space-y-4">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                            <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                        </div>
                        
                        <div>
                            <p class="text-lg font-semibold text-gray-800 mb-2">${response.message}</p>
                            <p class="text-sm text-gray-600">El pago se ha registrado correctamente en el sistema</p>
                        </div>
                        
                        <div class="bg-white rounded-lg p-4 border border-green-200">
                            <div class="grid grid-cols-2 gap-4 text-center">
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">MONTO PAGADO</p>
                                    <p class="text-lg font-bold text-green-600">${this.currencySymbol}${response.payment.amount.toFixed(2)}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">DEUDA RESTANTE</p>
                                    <p class="text-lg font-bold text-orange-600">${this.currencySymbol}${response.payment.remaining_debt.toFixed(2)}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            timer: 1500,
            timerProgressBar: true,
            showConfirmButton: false,
            customClass: {
                popup: 'rounded-2xl shadow-2xl'
            },
            width: '500px',
            padding: '2rem'
        });

        // Actualizar interfaz
        this.updateCustomerRow(response.customer);
        this.updateDashboardStats(response.stats);
        this.updateCustomerCards(response.customer);
        
        // Cerrar modal
        this.closePaymentModal();
        
        // Actualizar historial si está abierto
        this.updateCustomerHistory(response.customer.id);
    }

    handlePaymentError(response) {
        if (response.errors) {
            // Mostrar errores de validación
            this.showValidationErrors(response.errors);
        } else {
            // Mostrar error general mejorado
            Swal.fire({
                title: '<div class="flex items-center justify-center space-x-3 mb-4">' +
                       '<div class="w-12 h-12 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center">' +
                       '<i class="fas fa-exclamation-triangle text-white text-xl"></i>' +
                       '</div>' +
                       '<h2 class="text-2xl font-bold text-gray-800">Error</h2>' +
                       '</div>',
                html: `
                    <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-xl p-6 border border-red-200">
                        <div class="text-center space-y-4">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto">
                                <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                            </div>
                            
                            <div>
                                <p class="text-lg font-semibold text-gray-800 mb-2">${response.message || 'Error al registrar el pago'}</p>
                                <p class="text-sm text-gray-600">Por favor, verifica los datos e intenta nuevamente</p>
                            </div>
                        </div>
                    </div>
                `,
                confirmButtonColor: '#ef4444',
                confirmButtonText: '<i class="fas fa-check mr-2"></i>Entendido',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl',
                    confirmButton: 'rounded-lg px-6 py-3 font-semibold'
                },
                width: '500px',
                padding: '2rem'
            });
        }
    }

    handleNetworkError(error) {
        Swal.fire({
            title: '<div class="flex items-center justify-center space-x-3 mb-4">' +
                   '<div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-red-600 rounded-full flex items-center justify-center">' +
                   '<i class="fas fa-wifi text-white text-xl"></i>' +
                   '</div>' +
                   '<h2 class="text-2xl font-bold text-gray-800">Error de Conexión</h2>' +
                   '</div>',
            html: `
                <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-xl p-6 border border-orange-200">
                    <div class="text-center space-y-4">
                        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto">
                            <i class="fas fa-exclamation-triangle text-orange-600 text-2xl"></i>
                        </div>
                        
                        <div>
                            <p class="text-lg font-semibold text-gray-800 mb-2">No se pudo conectar con el servidor</p>
                            <p class="text-sm text-gray-600">Verifica tu conexión a internet e intenta nuevamente</p>
                        </div>
                        
                        <div class="bg-white rounded-lg p-4 border border-orange-200">
                            <div class="flex items-center space-x-2 text-center">
                                <i class="fas fa-lightbulb text-orange-500"></i>
                                <p class="text-xs text-orange-700 font-medium">Sugerencia: Revisa tu conexión WiFi o datos móviles</p>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            confirmButtonColor: '#f97316',
            confirmButtonText: '<i class="fas fa-check mr-2"></i>Entendido',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-lg px-6 py-3 font-semibold'
            },
            width: '500px',
            padding: '2rem'
        });
    }

    updateCustomerRow(customer) {
        // Actualizar fila en la tabla
        const row = document.querySelector(`tr[data-customer-id="${customer.id}"]`);
        if (!row) return;

        // Actualizar deuda
        const debtCell = row.querySelector('.debt-amount-value');
        if (debtCell) {
            debtCell.textContent = customer.formatted_total_debt;
        }

        // Actualizar deuda en Bs
        const debtBsCell = row.querySelector('.bs-debt');
        if (debtBsCell) {
            const debtBs = (customer.total_debt * this.exchangeRate).toFixed(2);
            debtBsCell.textContent = `Bs. ${debtBs}`;
        }

        // Actualizar estado de deuda
        this.updateDebtStatus(row, customer);

        // Actualizar botones de acción
        this.updateActionButtons(row, customer);

        // Si el cliente ya no tiene deuda y hay filtros activos, ocultar la fila
        if (!customer.has_debt && this.hasActiveFilters()) {
            this.hideCustomerRow(row, customer);
        }
    }

    updateDebtStatus(row, customer) {
        const debtInfo = row.querySelector('.debt-info');
        if (!debtInfo) return;

        if (customer.has_debt) {
            debtInfo.innerHTML = `
                <div class="debt-amount debt-value flex items-center gap-2" data-customer-id="${customer.id}" data-original-value="${customer.total_debt}">
                    <span>${this.currencySymbol} <span class="debt-amount-value">${customer.formatted_total_debt}</span></span>
                    ${customer.is_defaulter ? '<span class="debt-warning-badge" title="Cliente con deudas de arqueos anteriores"><i class="fas fa-exclamation-triangle"></i></span>' : ''}
                    <button class="edit-debt-btn-small" onclick="spaPaymentHandler.openPaymentModal(${customer.id})">
                        <i class="fas fa-dollar-sign"></i>
                    </button>
                </div>
            `;
        } else {
            debtInfo.innerHTML = `
                <div class="debt-amount flex items-center gap-2">
                    <span class="no-debt-badge">Sin deuda</span>
                </div>
            `;
        }
    }

    updateActionButtons(row, customer) {
        const actionButtons = row.querySelector('.action-buttons');
        if (!actionButtons) return;

        // Actualizar botón de pago
        const paymentBtn = actionButtons.querySelector('.btn-payment');
        if (paymentBtn) {
            if (customer.has_debt) {
                paymentBtn.style.display = 'flex';
                paymentBtn.onclick = () => this.openPaymentModal(customer.id);
            } else {
                paymentBtn.style.display = 'none';
            }
        }
    }

    updateDashboardStats(stats) {
        // Actualizar contador de clientes totales
        const totalCustomersElement = document.querySelector('[data-stat="total-customers"]');
        if (totalCustomersElement) {
            totalCustomersElement.textContent = stats.total_customers;
        }

        // Actualizar contador de clientes activos
        const activeCustomersElement = document.querySelector('[data-stat="active-customers"]');
        if (activeCustomersElement) {
            activeCustomersElement.textContent = stats.active_customers;
        }

        // Actualizar contador de nuevos clientes
        const newCustomersElement = document.querySelector('[data-stat="new-customers"]');
        if (newCustomersElement) {
            newCustomersElement.textContent = stats.new_customers;
        }

        // Actualizar contador de clientes morosos
        const defaultersElement = document.querySelector('[data-stat="defaulters-count"]');
        if (defaultersElement) {
            defaultersElement.textContent = stats.defaulters_count;
        }

        // Actualizar ingresos totales
        const totalRevenueElement = document.querySelector('[data-stat="total-revenue"]');
        if (totalRevenueElement) {
            totalRevenueElement.textContent = `${this.currencySymbol} ${parseFloat(stats.total_revenue).toFixed(2)}`;
        }

        // Animar los cambios
        this.animateStatChanges();
    }

    updateCustomerCards(customer) {
        // Actualizar tarjetas de cliente
        const cards = document.querySelectorAll(`[data-customer-id="${customer.id}"]`);
        cards.forEach(card => {
            // Actualizar deuda en la tarjeta
            const debtElement = card.querySelector('.debt-amount-value');
            if (debtElement) {
                debtElement.textContent = customer.formatted_total_debt;
            }

            // Actualizar estado de deuda en la tarjeta
            const debtInfo = card.querySelector('.debt-info');
            if (debtInfo) {
                if (customer.has_debt) {
                    debtInfo.innerHTML = `
                        <div class="space-y-1">
                            <div class="debt-value flex items-center gap-2" data-customer-id="${customer.id}" data-original-value="${customer.total_debt}">
                                <p class="text-sm font-semibold text-red-600">
                                    ${this.currencySymbol} <span class="debt-amount-value">${customer.formatted_total_debt}</span>
                                </p>
                                ${customer.is_defaulter ? '<span class="debt-warning-badge" title="Cliente con deudas de arqueos anteriores"><i class="fas fa-exclamation-triangle"></i></span>' : ''}
                            </div>
                        </div>
                    `;
                } else {
                    debtInfo.innerHTML = `
                        <span class="no-debt-badge">Sin deuda</span>
                    `;
                }
            }

            // Actualizar botones de acción en la tarjeta
            const paymentBtn = card.querySelector('[onclick*="debtPaymentModal"]');
            if (paymentBtn) {
                if (customer.has_debt) {
                    paymentBtn.style.display = 'flex';
                    paymentBtn.onclick = () => this.openPaymentModal(customer.id);
                } else {
                    paymentBtn.style.display = 'none';
                }
            }

            // Si el cliente ya no tiene deuda y hay filtros activos, ocultar la tarjeta
            if (!customer.has_debt && this.hasActiveFilters()) {
                this.hideCustomerCard(card, customer);
            }
        });
    }

    hideCustomerRow(row, customer) {
        // Verificar si hay filtros activos que requieran ocultar el cliente
        const currentFilter = this.getCurrentFilter();
        
        if (currentFilter === 'defaulters' && !customer.is_defaulter) {
            row.style.display = 'none';
            this.showFilterNotification();
        }
    }

    hideCustomerCard(card, customer) {
        // Verificar si hay filtros activos que requieran ocultar el cliente
        const currentFilter = this.getCurrentFilter();
        
        if (currentFilter === 'defaulters' && !customer.is_defaulter) {
            card.style.display = 'none';
            this.showFilterNotification();
        }
    }

    getCurrentFilter() {
        // Obtener el filtro actual activo
        const activeFilter = document.querySelector('.filter-btn.active');
        return activeFilter ? activeFilter.getAttribute('data-filter') : 'all';
    }

    hasActiveFilters() {
        return this.getCurrentFilter() !== 'all';
    }

    showFilterNotification() {
        // Mostrar notificación de que el cliente fue removido por filtros
        Swal.fire({
            icon: 'info',
            title: 'Cliente Removido',
            text: 'El cliente ha sido removido de la vista debido a los filtros activos',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    }

    async updateCustomerHistory(customerId) {
        // Si el modal de historial está abierto, actualizarlo
        const historyModal = document.getElementById('showCustomerModal');
        if (historyModal && historyModal.style.display !== 'none') {
            // Recargar historial del cliente
            await this.loadCustomerDetails(customerId);
        }
    }

    async loadCustomerDetails(customerId) {
        try {
            const response = await fetch(`/admin/customers/${customerId}/sales-history`);
            const data = await response.json();
            
            if (data.success) {
                this.updateSalesHistoryTable(data.sales);
            }
        } catch (error) {
            // Error loading customer history
        }
    }

    updateSalesHistoryTable(sales) {
        const tbody = document.getElementById('salesHistoryTable');
        if (!tbody) return;

        if (sales.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center space-y-3">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-info-circle text-2xl text-gray-400"></i>
                            </div>
                            <p class="text-gray-500">No hay ventas registradas</p>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            tbody.innerHTML = sales.map(sale => {
                // Asegurar que el HTML se procese correctamente
                const productsHtml = sale.products || 'Sin productos';
                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">${sale.date}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 products-cell">${productsHtml}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-green-600">${this.currencySymbol} ${parseFloat(sale.total).toFixed(2)}</td>
                    </tr>
                `;
            }).join('');
        }

        // Actualizar contador
        const salesCount = document.getElementById('salesCount');
        if (salesCount) {
            salesCount.textContent = sales.length;
        }
    }

    animateStatChanges() {
        // Animar cambios en las estadísticas
        const statElements = document.querySelectorAll('[data-stat]');
        statElements.forEach(element => {
            element.classList.add('animate-pulse');
            setTimeout(() => {
                element.classList.remove('animate-pulse');
            }, 1000);
        });
    }

    showLoadingState() {
        this.isProcessing = true;
        
        // Deshabilitar botón de envío
        const submitBtn = document.querySelector('#debtPaymentForm button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span>Procesando...</span>
                </div>
            `;
        }

        // Mostrar overlay de carga
        this.showLoadingOverlay();
    }

    hideLoadingState() {
        this.isProcessing = false;
        
        // Habilitar botón de envío
        const submitBtn = document.querySelector('#debtPaymentForm button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
                <i class="fas fa-save text-sm"></i>
                <span class="text-sm font-medium">Registrar Pago</span>
            `;
        }

        // Ocultar overlay de carga
        this.hideLoadingOverlay();
    }

    showLoadingOverlay() {
        // Crear overlay de carga si no existe
        if (!document.getElementById('payment-loading-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'payment-loading-overlay';
            overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            overlay.innerHTML = `
                <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
                    <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-gray-700">Procesando pago...</span>
                </div>
            `;
            document.body.appendChild(overlay);
        }
    }

    hideLoadingOverlay() {
        const overlay = document.getElementById('payment-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    showFieldError(fieldName, message) {
        const field = document.getElementById(fieldName);
        if (!field) return;

        // Remover error anterior
        this.clearFieldError(fieldName);

        // Agregar clase de error
        field.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');

        // Crear mensaje de error
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-500 text-xs mt-1';
        errorDiv.textContent = message;
        errorDiv.id = `${fieldName}-error`;

        // Insertar después del campo
        field.parentNode.appendChild(errorDiv);
    }

    clearFieldError(fieldName) {
        const field = document.getElementById(fieldName);
        if (!field) return;

        // Remover clases de error
        field.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');

        // Remover mensaje de error
        const errorDiv = document.getElementById(`${fieldName}-error`);
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    showValidationErrors(errors) {
        // Limpiar errores anteriores
        Object.keys(errors).forEach(field => {
            this.clearFieldError(field);
        });

        // Mostrar nuevos errores
        Object.keys(errors).forEach(field => {
            const message = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            this.showFieldError(field, message);
        });
    }

    openPaymentModal(customerId) {
        this.currentCustomerId = customerId;
        
        // Cargar datos del cliente
        this.loadCustomerData(customerId);
        
        // Abrir modal directamente
        const modal = document.getElementById('debtPaymentModal');
        if (modal) {
            // Remover cualquier atributo x-show que pueda estar interfiriendo
            modal.removeAttribute('x-show');
            modal.removeAttribute('x-cloak');
            
            // Mostrar el modal con animación
            modal.style.display = 'block';
            modal.classList.remove('hidden', 'hide');
            modal.classList.add('show');
            
            // Agregar clase para backdrop
            document.body.classList.add('modal-open');
            
            // Vincular eventos después de que el modal esté visible
            setTimeout(() => {
                this.bindModalEvents();
            }, 100);
        }
    }

    closePaymentModal() {
        // Cerrar modal directamente
        const modal = document.getElementById('debtPaymentModal');
        if (modal) {
            // Ocultar el modal con animación
            modal.classList.remove('show');
            modal.classList.add('hide');
            
            // Después de la animación, ocultar completamente
            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.add('hidden');
                modal.classList.remove('hide');
            }, 300);
            
            // Remover clase de backdrop
            document.body.classList.remove('modal-open');
        }

        // Limpiar formulario
        this.resetForm();
        
        // Limpiar errores
        this.clearAllErrors();
    }

    async loadCustomerData(customerId) {
        try {
            const response = await fetch(`/admin/customers/${customerId}/payment-data`);
            const data = await response.json();
            
            if (data.success) {
                this.populateForm(data.customer);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar la información del cliente'
            });
        }
    }

    populateForm(customer) {
        // Llenar información del cliente
        document.getElementById('customer_name').value = customer.name;
        document.getElementById('customer_phone').value = customer.phone;
        
        // Actualizar estado del cliente
        const statusElement = document.getElementById('customer_status');
        if (statusElement) {
            statusElement.className = customer.has_sales ? 
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800' :
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
            statusElement.innerHTML = customer.has_sales ? 
                '<i class="fas fa-check-circle mr-1"></i>Activo' :
                '<i class="fas fa-times-circle mr-1"></i>Inactivo';
        }

        // Actualizar deuda actual
        const currentDebtElement = document.getElementById('current_debt');
        if (currentDebtElement) {
            currentDebtElement.innerHTML = `<span class="text-red-700 font-semibold">${this.currencySymbol}${customer.formatted_total_debt}</span>`;
        }

        // Configurar monto máximo
        const paymentAmountInput = document.getElementById('payment_amount');
        if (paymentAmountInput) {
            paymentAmountInput.setAttribute('data-max-debt', customer.total_debt);
            paymentAmountInput.max = customer.total_debt;
            paymentAmountInput.value = ''; // Limpiar el campo
        }

        // Inicializar deuda restante
        const remainingDebtElement = document.getElementById('remaining_debt');
        if (remainingDebtElement) {
            remainingDebtElement.innerHTML = `<span class="text-orange-700 font-semibold">${this.currencySymbol}${customer.formatted_total_debt}</span>`;
            remainingDebtElement.className = 'w-full pl-12 pr-12 py-2.5 bg-orange-50 border border-orange-200 rounded-lg text-orange-700 font-semibold text-sm flex items-center';
        }

        // Establecer fecha y hora por defecto (usar zona horaria local)
        const now = new Date();
        const today = now.getFullYear() + '-' + 
                     String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                     String(now.getDate()).padStart(2, '0');
        const currentTime = String(now.getHours()).padStart(2, '0') + ':' + 
                           String(now.getMinutes()).padStart(2, '0');
        
        document.getElementById('payment_date').value = today;
        document.getElementById('payment_time').value = currentTime;
    }

    resetForm() {
        const form = document.getElementById('debtPaymentForm');
        if (form) {
            form.reset();
        }
    }

    clearAllErrors() {
        const errorElements = document.querySelectorAll('[id$="-error"]');
        errorElements.forEach(element => {
            element.remove();
        });

        const errorFields = document.querySelectorAll('.border-red-500');
        errorFields.forEach(field => {
            field.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
        });
    }
}

// Inicializar el handler cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.spaPaymentHandler = new SPAPaymentHandler();
});

// Exportar para uso global
window.SPAPaymentHandler = SPAPaymentHandler;
