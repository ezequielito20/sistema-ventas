@extends('layouts.app')

@section('title', 'Gestión de Clientes')

@section('content')
    <!-- Definir funciones Alpine.js ANTES del HTML -->
    <script>
        // Definir funciones Alpine.js globalmente ANTES de que se evalúen
        window.heroSection = function() {
            return {
                showStats: true,

                init() {
                    setTimeout(() => {
                        this.showStats = true;
                    }, 500);
                },

                openDebtReport() {
                    // Abrir el modal y cargar el reporte
                    this.openModal('debtReportModal');
                    this.loadDebtReport();
                }
            }
        }

        // statsWidgets ya no es necesario - widgets optimizados sin animaciones

        window.dataTable = function() {
            return {
                viewMode: window.innerWidth >= 768 ? 'table' : 'cards', // Default: table en desktop, cards en móvil
                searchTerm: '',
                searchResultsCount: 0,
                currentPage: 1,
                itemsPerPage: 25,
                totalPages: {{ $customers->lastPage() }},
                totalItems: {{ $customers->total() }},

                init() {
                    // Detectar cambios de tamaño de pantalla
                    window.addEventListener('resize', () => {
                        // En móvil siempre mostrar cards, en desktop permitir toggle
                        if (window.innerWidth < 768) {
                            this.viewMode = 'cards';
                        }
                    });

                    console.log('DataTable inicializado con vista:', this.viewMode);
                },

                performSearch() {
                    const mobileSearch = document.getElementById('mobileSearch');
                    if (mobileSearch) {
                        mobileSearch.value = this.searchTerm;
                        mobileSearch.dispatchEvent(new Event('keyup'));
                    }
                },

                clearSearch() {
                    this.searchTerm = '';
                    this.searchResultsCount = 0;
                    const mobileSearch = document.getElementById('mobileSearch');
                    if (mobileSearch) {
                        mobileSearch.value = '';
                        mobileSearch.dispatchEvent(new Event('keyup'));
                    }
                },

                // Métodos de paginación
                goToPage(page) {
                    if (page >= 1 && page <= this.totalPages) {
                        this.currentPage = page;
                        this.loadPage(page);
                    }
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.goToPage(this.currentPage + 1);
                    }
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.goToPage(this.currentPage - 1);
                    }
                },

                loadPage(page) {
                    // Mostrar loading
                    this.showLoading();
                    
                    // Construir URL con parámetros
                    const url = new URL(window.location);
                    url.searchParams.set('page', page);
                    if (this.itemsPerPage !== 25) {
                        url.searchParams.set('per_page', this.itemsPerPage);
                    }
                    
                    // Hacer petición AJAX para cargar la página
                    fetch(url.toString())
                        .then(response => response.text())
                        .then(html => {
                            // Crear un DOM temporal para extraer solo la tabla/tarjetas
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            
                            // Actualizar tabla
                            const newTableBody = doc.querySelector('#customersTableBody');
                            if (newTableBody) {
                                document.querySelector('#customersTableBody').innerHTML = newTableBody.innerHTML;
                            }
                            
                            // Actualizar tarjetas
                            const newCardsContainer = doc.querySelector('#mobileCustomersContainer');
                            if (newCardsContainer) {
                                document.querySelector('#mobileCustomersContainer').innerHTML = newCardsContainer.innerHTML;
                            }
                            
                            // Actualizar tarjetas móviles
                            const newMobileContainer = doc.querySelector('#mobileOnlyContainer');
                            if (newMobileContainer) {
                                document.querySelector('#mobileOnlyContainer').innerHTML = newMobileContainer.innerHTML;
                            }
                            
                            // Actualizar información de paginación
                            const paginationInfo = doc.querySelector('[x-data*="dataTable"]');
                            if (paginationInfo) {
                                // Extraer valores de paginación del HTML
                                const totalItemsMatch = html.match(/totalItems:\s*(\d+)/);
                                const totalPagesMatch = html.match(/totalPages:\s*(\d+)/);
                                
                                if (totalItemsMatch) {
                                    this.totalItems = parseInt(totalItemsMatch[1]);
                                }
                                if (totalPagesMatch) {
                                    this.totalPages = parseInt(totalPagesMatch[1]);
                                }
                            }
                            
                            // Actualizar URL sin recargar la página
                            window.history.pushState({}, '', url.toString());
                            
                            // Ocultar loading
                            this.hideLoading();
                            
                            // Reinicializar eventos
                            this.initializeEvents();
                        })
                        .catch(error => {
                            console.error('Error cargando página:', error);
                            this.hideLoading();
                        });
                },

                // Cambiar elementos por página
                changeItemsPerPage(newItemsPerPage) {
                    this.itemsPerPage = newItemsPerPage;
                    this.currentPage = 1;
                    this.loadPage(1);
                },

                showLoading() {
                    // Crear overlay de loading si no existe
                    if (!document.getElementById('paginationLoading')) {
                        const loading = document.createElement('div');
                        loading.id = 'paginationLoading';
                        loading.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                        loading.innerHTML = `
                            <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
                                <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-gray-700">Cargando...</span>
                            </div>
                        `;
                        document.body.appendChild(loading);
                    }
                },

                hideLoading() {
                    const loading = document.getElementById('paginationLoading');
                    if (loading) {
                        loading.remove();
                    }
                },

                initializeEvents() {
                    // Reinicializar eventos de botones y funcionalidades
                    // Esto se ejecutará después de cargar nueva página
                    if (typeof initializeCustomerEvents === 'function') {
                        initializeCustomerEvents();
                    }
                },

                // Getters para la paginación
                get startItem() {
                    return (this.currentPage - 1) * this.itemsPerPage + 1;
                },

                get endItem() {
                    return Math.min(this.currentPage * this.itemsPerPage, this.totalItems);
                },

                get hasNextPage() {
                    return this.currentPage < this.totalPages;
                },

                get hasPrevPage() {
                    return this.currentPage > 1;
                },

                get pageNumbers() {
                    const pages = [];
                    const maxVisible = 5;
                    let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
                    let end = Math.min(this.totalPages, start + maxVisible - 1);
                    
                    if (end - start + 1 < maxVisible) {
                        start = Math.max(1, end - maxVisible + 1);
                    }
                    
                    for (let i = start; i <= end; i++) {
                        pages.push(i);
                    }
                    
                    return pages;
                }
            }
        }

        window.filtersPanel = function() {
            return {
                filtersOpen: false,
                hasActiveFilters: false,
                currentFilter: 'all',
                searchTerm: '',
                searchResultsCount: 0,
                totalResults: {{ $totalCustomers ?? 0 }},

                init() {
                    console.log('Panel de filtros inicializado');
                    this.updateActiveFiltersIndicator();
                },

                toggleFilters() {
                    this.filtersOpen = !this.filtersOpen;
                    console.log('Filtros toggled:', this.filtersOpen);
                },

                setFilter(filter) {
                    this.currentFilter = filter;
                    this.updateActiveFiltersIndicator();

                    // Aplicar filtro usando la función existente
                    const filterButtons = document.querySelectorAll('.filter-btn');
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    const activeBtn = document.querySelector(`.filter-btn[data-filter="${filter}"]`);
                    if (activeBtn) activeBtn.classList.add('active');

                    // Trigger existing filter functionality
                    if (typeof applyFiltersAndSearch === 'function') {
                        applyFiltersAndSearch();
                    }
                },

                performSearch() {
                    const mobileSearch = document.getElementById('mobileSearch');
                    if (mobileSearch) {
                        mobileSearch.value = this.searchTerm;
                        mobileSearch.dispatchEvent(new Event('keyup'));
                    }
                    this.updateActiveFiltersIndicator();
                },

                clearSearch() {
                    this.searchTerm = '';
                    const mobileSearch = document.getElementById('mobileSearch');
                    if (mobileSearch) {
                        mobileSearch.value = '';
                        mobileSearch.dispatchEvent(new Event('keyup'));
                    }
                    this.searchResultsCount = 0;
                    this.updateActiveFiltersIndicator();
                },

                clearAllFilters() {
                    this.currentFilter = 'all';
                    this.searchTerm = '';
                    this.searchResultsCount = 0;

                    // Limpiar filtros existentes
                    const filterButtons = document.querySelectorAll('.filter-btn');
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    const allBtn = document.querySelector('.filter-btn[data-filter="all"]');
                    if (allBtn) allBtn.classList.add('active');

                    const mobileSearch = document.getElementById('mobileSearch');
                    if (mobileSearch) mobileSearch.value = '';

                    if (typeof applyFiltersAndSearch === 'function') {
                        applyFiltersAndSearch();
                    }

                    this.updateActiveFiltersIndicator();
                },

                applyFilters() {
                    if (typeof applyFiltersAndSearch === 'function') {
                        applyFiltersAndSearch();
                    }
                    this.updateActiveFiltersIndicator();
                },

                updateActiveFiltersIndicator() {
                    this.hasActiveFilters = (this.currentFilter !== 'all' || this.searchTerm.length > 0);

                    // Actualizar badges de filtros activos
                    const container = document.getElementById('activeFiltersContainer');
                    if (container) {
                        let badges = '';

                        if (this.currentFilter !== 'all') {
                            const filterNames = {
                                'active': 'Activos',
                                'inactive': 'Inactivos',
                                'defaulters': 'Morosos'
                            };
                            badges += `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    <i class="fas fa-filter mr-1"></i>
                                    ${filterNames[this.currentFilter] || this.currentFilter}
                                  </span>`;
                        }

                        if (this.searchTerm.length > 0) {
                            badges += `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                    <i class="fas fa-search mr-1"></i>
                                    "${this.searchTerm}"
                                  </span>`;
                        }

                        container.innerHTML = badges;
                    }
                }
            }
        }

        window.exchangeRateWidget = function() {
            return {
                exchangeRate: 120.00,
                updating: false,

                init() {
                    // Cargar valor guardado del localStorage o del input existente
                    const savedRate = localStorage.getItem('exchangeRate');
                    const exchangeRateInput = document.getElementById('exchangeRate');

                    if (savedRate) {
                        this.exchangeRate = parseFloat(savedRate);
                    } else if (exchangeRateInput && exchangeRateInput.value) {
                        this.exchangeRate = parseFloat(exchangeRateInput.value);
                    }

                    // Sincronizar con el input original
                    if (exchangeRateInput) {
                        exchangeRateInput.value = this.exchangeRate;
                    }
                },

                updateRate() {
                    if (this.exchangeRate <= 0) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'El tipo de cambio debe ser mayor a 0'
                            });
                        } else {
                            alert('El tipo de cambio debe ser mayor a 0');
                        }
                        return;
                    }

                    this.updating = true;

                    // Actualizar el input original y trigger el evento
                    const exchangeRateInput = document.getElementById('exchangeRate');
                    if (exchangeRateInput) {
                        exchangeRateInput.value = this.exchangeRate;
                    }

                    const updateBtn = document.querySelector('.update-exchange-rate');
                    if (updateBtn) {
                        updateBtn.click();
                    }

                    setTimeout(() => {
                        this.updating = false;
                    }, 1000);
                }
            }
        }

        window.modalManager = function() {
            return {
                showCustomerModal: false,
                debtReportModal: false,
                debtPaymentModal: false,
                
                openModal(modalName) {
                    this[modalName] = true;
                    document.body.style.overflow = 'hidden';
                },
                
                closeModal(modalName) {
                    this[modalName] = false;
                    document.body.style.overflow = 'auto';
                },
                
                closeAllModals() {
                    this.showCustomerModal = false;
                    this.debtReportModal = false;
                    this.debtPaymentModal = false;
                    document.body.style.overflow = 'auto';
                },
                
                loadCustomerDetails(customerId) {
                    // Cargar detalles del cliente usando AJAX
                    fetch(`/admin/customers/${customerId}/show`)
                        .then(response => response.text())
                        .then(html => {
                            const modalBody = document.querySelector('#showCustomerModal .modal-body');
                            if (modalBody) {
                                modalBody.innerHTML = html;
                            }
                        })
                        .catch(error => {
                            console.error('Error cargando detalles del cliente:', error);
                        });
                },
                
                loadDebtPaymentData(customerId) {
                    // Cargar datos para el modal de pago de deuda
                    fetch(`/admin/customers/${customerId}/debt-payment-data`)
                        .then(response => response.json())
                        .then(data => {
                            // Llenar los campos del modal
                            document.getElementById('payment_customer_id').value = data.customer_id;
                            document.getElementById('customer_name').value = data.customer_name;
                            document.getElementById('current_debt').value = data.current_debt;
                            document.getElementById('remaining_debt').value = data.remaining_debt;
                        })
                        .catch(error => {
                            console.error('Error cargando datos de pago de deuda:', error);
                        });
                },
                
                loadDebtReport() {
                    console.log('loadDebtReport ejecutándose...');
                    // Cargar el reporte de deudas
                    const modalBody = document.querySelector('#debtReportModal .modal-body');
                    console.log('modalBody encontrado:', modalBody);
                    if (!modalBody) {
                        console.error('No se encontró modalBody');
                        return;
                    }
                    
                    // Mostrar loading
                    modalBody.innerHTML = `
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="w-16 h-16 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin mb-6"></div>
                            <div class="text-center">
                                <h5 class="text-xl font-semibold text-gray-900 mb-2">Cargando reporte de deudas</h5>
                                <p class="text-gray-600">Preparando información detallada...</p>
                            </div>
                        </div>
                    `;
                    
                    // Obtener el tipo de cambio actual
                    const exchangeRate = document.getElementById('exchangeRate')?.value || 134;
                    
                    // Cargar el reporte mediante fetch
                    const url = '{{ route('admin.customers.debt-report') }}';
                    console.log('Haciendo fetch a:', url);
                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                    .then(response => {
                        console.log('Respuesta recibida:', response);
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.text();
                    })
                    .then(html => {
                        console.log('HTML recibido:', html.substring(0, 200) + '...');
                        
                        // Crear un DOM temporal para extraer solo el contenido del modal
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        // Buscar el contenido del modal en el HTML
                        const modalContentFromResponse = doc.querySelector('.modal-content') || 
                                                       doc.querySelector('#debtReportModal') ||
                                                       doc.querySelector('.debt-modal-body') ||
                                                       doc.body;
                        
                        console.log('Contenido extraído:', modalContentFromResponse);
                        
                        // Actualizar el contenido del modal
                        const modalContent = document.querySelector('#debtReportModal .modal-content');
                        console.log('modalContent encontrado:', modalContent);
                        if (modalContent && modalContentFromResponse) {
                            modalContent.innerHTML = modalContentFromResponse.innerHTML;
                            console.log('Contenido actualizado');
                            
                            // Inicializar event listeners después de cargar el contenido
                            this.initializeDebtReportEvents();
                        } else {
                            console.error('No se encontró modalContent o contenido de respuesta');
                        }
                    })
                    .catch(error => {
                        console.error('Error cargando reporte de deudas:', error);
                        modalBody.innerHTML = `
                            <div class="flex flex-col items-center justify-center py-12">
                                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
                                    <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
                                </div>
                                <div class="text-center">
                                    <h5 class="text-xl font-semibold text-gray-900 mb-2">Error al cargar el reporte</h5>
                                    <p class="text-gray-600">No se pudo cargar el reporte de deudas. Inténtalo de nuevo.</p>
                                    <button @click="loadDebtReport()" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                        Reintentar
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                },
                
                initializeDebtReportEvents() {
                    console.log('Inicializando event listeners del reporte de deudas...');
                    
                    // Obtener el tipo de cambio actual
                    const currentRate = document.getElementById('exchangeRate')?.value || 134;
                    
                    // Establecer el valor inicial en el modal
                    const modalExchangeRateInput = document.getElementById('modalExchangeRate');
                    if (modalExchangeRateInput) {
                        modalExchangeRateInput.value = currentRate;
                        console.log('Valor inicial establecido:', currentRate);
                    }
                    
                    // Event listener para el input del tipo de cambio
                    const exchangeRateInput = document.getElementById('modalExchangeRate');
                    if (exchangeRateInput) {
                        exchangeRateInput.addEventListener('input', (e) => {
                            const rate = parseFloat(e.target.value);
                            console.log('Evento input detectado, valor:', rate);
                            
                            if (rate > 0) {
                                console.log('Valor válido, actualizando...');
                                this.updateModalBsValues(rate);
                            } else {
                                console.log('Valor inválido o 0, no se actualiza');
                            }
                        });
                        console.log('Event listener agregado al input del tipo de cambio');
                    }
                    
                    // Event listener para el botón de actualizar
                    const updateBtn = document.getElementById('updateModalExchangeRate');
                    if (updateBtn) {
                        updateBtn.addEventListener('click', () => {
                            const rate = parseFloat(document.getElementById('modalExchangeRate').value);
                            if (rate > 0) {
                                // Actualizar la variable global
                                window.currentExchangeRate = rate;
                                
                                // Actualizar el input en la tabla principal
                                const mainExchangeRateInput = document.getElementById('exchangeRate');
                                if (mainExchangeRateInput) {
                                    mainExchangeRateInput.value = rate;
                                }
                                
                                // Guardar en localStorage
                                localStorage.setItem('exchangeRate', rate);
                                
                                // Actualizar valores en Bs en el modal
                                this.updateModalBsValues(rate);
                                
                                // Actualizar valores en Bs en la tabla principal
                                if (typeof window.updateBsValues === 'function') {
                                    window.updateBsValues(rate);
                                }
                                
                                // Mostrar mensaje de éxito
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
                            }
                        });
                        console.log('Event listener agregado al botón de actualizar');
                    }
                    
                    // Actualizar valores iniciales
                    this.updateModalBsValues(currentRate);
                    console.log('Event listeners inicializados correctamente');
                },
                
                updateModalBsValues(rate) {
                    console.log('Actualizando valores en Bs con tasa:', rate);
                    
                    // Actualizar el resumen total (botones en la sección de estadísticas)
                    const modalBsDebtElements = document.querySelectorAll('.modal-bs-debt');
                    modalBsDebtElements.forEach(element => {
                        const debtUsd = parseFloat(element.dataset.debt);
                        if (!isNaN(debtUsd)) {
                            const debtBs = debtUsd * rate;
                            element.innerHTML = 'Bs. ' + debtBs.toLocaleString('es-VE', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            console.log('Actualizado modal-bs-debt:', debtUsd, '->', debtBs);
                        }
                    });
                    
                    // Actualizar cada fila de la tabla en el modal
                    const bsDebtElements = document.querySelectorAll('#debtReportModal .bs-debt');
                    bsDebtElements.forEach(element => {
                        const debtUsd = parseFloat(element.dataset.debt);
                        if (!isNaN(debtUsd)) {
                            const debtBs = debtUsd * rate;
                            element.innerHTML = 'Bs. ' + debtBs.toLocaleString('es-VE', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            console.log('Actualizado bs-debt en tabla:', debtUsd, '->', debtBs);
                        }
                    });
                    
                    // Buscar y actualizar cualquier elemento que contenga "Bs." en el modal
                    const modal = document.getElementById('debtReportModal');
                    if (modal) {
                        const allElements = modal.querySelectorAll('*');
                        allElements.forEach(element => {
                            const text = element.textContent;
                            
                            // Buscar elementos que contengan "Bs." y que tengan un atributo data-debt
                            if (text.includes('Bs.') && element.dataset.debt) {
                                const debtUsd = parseFloat(element.dataset.debt);
                                if (!isNaN(debtUsd)) {
                                    const debtBs = debtUsd * rate;
                                    element.innerHTML = 'Bs. ' + debtBs.toLocaleString('es-VE', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                    console.log('Actualizado elemento con Bs.:', debtUsd, '->', debtBs);
                                }
                            }
                        });
                    }
                    
                    console.log('Actualización completada');
                }
            }
        }
    </script>

    <!-- Contenedor Principal con Gradiente de Fondo -->
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-100" x-data="modalManager()">

        <!-- Hero Section con Tailwind y Alpine.js -->
        <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl mb-8"
            x-data="heroSection()">
            <!-- Background Pattern -->
            <div class="absolute inset-0 bg-black bg-opacity-10">
                <div class="absolute inset-0 bg-gradient-to-r from-white/5 to-transparent"></div>
                <!-- Decorative circles -->
                <div
                    class="absolute top-0 left-0 w-72 h-72 bg-white rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-blob">
                </div>
                <div
                    class="absolute top-0 right-0 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-blob animation-delay-2000">
                </div>
                <div
                    class="absolute -bottom-8 left-20 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-blob animation-delay-4000">
                </div>
            </div>

            <div class="relative px-6 py-8 sm:px-8 lg:px-12">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <!-- Hero Content -->
                    <div class="flex-1 lg:pr-8">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-users text-3xl text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h1 class="text-3xl sm:text-4xl font-bold text-white">
                            Gestión de Clientes
                        </h1>
                    </div>
                </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 lg:mt-0 lg:flex-shrink-0">
                        <div class="flex flex-wrap gap-3 justify-center lg:justify-end">
                        @can('customers.report')
                                <button @click="openDebtReport()"
                                    class="group relative inline-flex items-center px-4 py-2.5 bg-white/20 backdrop-blur-sm text-white font-medium rounded-xl hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200 transform hover:scale-105 hover:-translate-y-0.5"
                                    title="Reporte de Deudas">
                                    <i class="fas fa-file-invoice-dollar text-lg mr-2 text-blue-200"></i>
                                    <span class="hidden sm:inline">Deudas</span>
                                    <!-- Tooltip -->
                                    <div
                                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                        Reporte de Deudas
                                    </div>
                            </button>
                        @endcan

                        @can('customers.report')
                                <a href="{{ route('admin.customers.report') }}" target="_blank"
                                    class="group relative inline-flex items-center px-4 py-2.5 bg-white/20 backdrop-blur-sm text-white font-medium rounded-xl hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200 transform hover:scale-105 hover:-translate-y-0.5"
                                    title="Reporte PDF">
                                    <i class="fas fa-file-pdf text-lg mr-2 text-red-200"></i>
                                    <span class="hidden sm:inline">PDF</span>
                                    <!-- Tooltip -->
                                    <div
                                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                        Reporte PDF
                                    </div>
                            </a>
                        @endcan

                        @can('customers.report')
                                <a href="{{ route('admin.customers.payment-history') }}"
                                    class="group relative inline-flex items-center px-4 py-2.5 bg-white/20 backdrop-blur-sm text-white font-medium rounded-xl hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200 transform hover:scale-105 hover:-translate-y-0.5"
                                    title="Historial de Pagos">
                                    <i class="fas fa-history text-lg mr-2 text-yellow-200"></i>
                                    <span class="hidden sm:inline">Historial</span>
                                    <!-- Tooltip -->
                                    <div
                                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                        Historial de Pagos
                                    </div>
                            </a>
                        @endcan

                        @can('customers.create')
                                <a href="{{ route('admin.customers.create') }}"
                                    class="group relative inline-flex items-center px-6 py-2.5 bg-white text-blue-600 font-semibold rounded-xl hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 hover:-translate-y-0.5 shadow-lg"
                                    title="Nuevo Cliente">
                                    <i class="fas fa-plus text-lg mr-2"></i>
                                    <span class="hidden sm:inline">Nuevo Cliente</span>
                                    <!-- Tooltip -->
                                    <div
                                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                        Crear Nuevo Cliente
                                    </div>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Stats Widgets optimizados sin animaciones -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
            <!-- Total de Clientes -->
            <div
                class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <!-- Gradient Background -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 opacity-5 group-hover:opacity-10 transition-opacity duration-300">
                </div>

                <!-- Content -->
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-users text-white text-xl"></i>
            </div>
                    @if ($customerGrowth > 0)
                            <div
                                class="flex items-center space-x-1 bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-medium">
                                <i class="fas fa-arrow-up text-xs"></i>
                                <span>{{ $customerGrowth }}%</span>
                            </div>
                    @endif
                </div>

                    <div class="space-y-2">
                        <div class="text-3xl font-bold text-gray-900">
                            {{ $totalCustomers }}
                        </div>
                        <div class="text-sm font-medium text-gray-600">Total de Clientes</div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full" style="width: 100%">
                            </div>
                        </div>
                </div>
            </div>
        </div>

            <!-- Clientes Activos -->
            <div
                class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <!-- Gradient Background -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 opacity-5 group-hover:opacity-10 transition-opacity duration-300">
            </div>

                <!-- Content -->
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-user-check text-white text-xl"></i>
                </div>
                </div>

                    <div class="space-y-2">
                        <div class="text-3xl font-bold text-gray-900">
                            <span>{{ $activeCustomers }}</span>
                            <span class="text-lg text-gray-500">/{{ $totalCustomers }}</span>
                        </div>
                        <div class="text-sm font-medium text-gray-600">Clientes Activos</div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-2 rounded-full"
                                style="width: {{ $totalCustomers > 0 ? ($activeCustomers / $totalCustomers) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
            </div>
        </div>

            <!-- Nuevos este Mes -->
            <div
                class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <!-- Gradient Background -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-yellow-500 to-orange-500 opacity-5 group-hover:opacity-10 transition-opacity duration-300">
            </div>

                <!-- Content -->
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-user-plus text-white text-xl"></i>
                </div>
                </div>

                    <div class="space-y-2">
                        <div class="text-3xl font-bold text-gray-900">
                            {{ $newCustomers }}
                        </div>
                        <div class="text-sm font-medium text-gray-600">Nuevos este Mes</div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 h-2 rounded-full"
                                style="width: {{ $totalCustomers > 0 ? ($newCustomers / $totalCustomers) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
            </div>
        </div>

            <!-- Ingresos Totales -->
            <div
                class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <!-- Gradient Background -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-purple-500 to-indigo-600 opacity-5 group-hover:opacity-10 transition-opacity duration-300">
            </div>

                <!-- Content -->
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-money-bill-wave text-white text-xl"></i>
                </div>
                </div>

                    <div class="space-y-2">
                        <div class="text-2xl font-bold text-gray-900">
                            {{ $currency->symbol }} {{ number_format($totalRevenue, 2) }}
                        </div>
                        <div class="text-sm font-medium text-gray-600">Ingresos Totales</div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                            <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-2 rounded-full"
                                style="width: 100%"></div>
                        </div>
                    </div>
            </div>
        </div>

            <!-- Clientes Morosos -->
            <div
                class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <!-- Gradient Background -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-red-500 to-pink-600 opacity-5 group-hover:opacity-10 transition-opacity duration-300">
            </div>

                <!-- Content -->
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                </div>
                        @if ($defaultersCount > 0)
                            <div
                                class="flex items-center space-x-1 bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-medium">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                <span>Atención</span>
                </div>
                        @endif
            </div>

                    <div class="space-y-2">
                        <div class="text-3xl font-bold text-gray-900">
                            {{ $defaultersCount }}
                        </div>
                        <div class="text-sm font-medium text-gray-600">Clientes Morosos</div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                            <div class="bg-gradient-to-r from-red-500 to-pink-600 h-2 rounded-full"
                                style="width: {{ $totalCustomers > 0 ? ($defaultersCount / $totalCustomers) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

        <!-- Filtros Rediseñados y Compactos -->
        <div class="bg-white rounded-2xl shadow-lg mb-8 overflow-hidden" x-data="filtersPanel()">
            <!-- Header del Panel de Filtros -->
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-filter text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Filtros y Búsqueda</h3>
                            <p class="text-sm text-gray-500">Personaliza la vista de tus clientes</p>
                        </div>
                    </div>

                    <!-- Toggle Button -->
                    <button @click="toggleFilters()"
                        class="group flex items-center space-x-2 px-4 py-2 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <span class="text-sm font-medium text-gray-700"
                            x-text="filtersOpen ? 'Ocultar Filtros' : 'Mostrar Filtros'"></span>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform duration-200 group-hover:text-gray-700"
                            :class="{ 'rotate-180': filtersOpen }"></i>
                    </button>
                </div>

                <!-- Active Filters Indicator -->
                <div x-show="hasActiveFilters" x-transition class="mt-3 flex items-center space-x-2">
                    <span class="text-xs font-medium text-blue-600">Filtros activos:</span>
                    <div class="flex flex-wrap gap-2" id="activeFiltersContainer">
                        <!-- Los filtros activos se mostrarán aquí dinámicamente -->
                    </div>
                </div>
            </div>

            <!-- Panel de Filtros Colapsable -->
            <div x-show="filtersOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="p-6 bg-gray-50 border-t border-gray-100">

                <!-- Sección Unificada de Filtros -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        <!-- Tipo de Cambio -->
                        <div x-data="exchangeRateWidget()">
                            

                            <!-- Input y Botón en línea -->
                            <div class="flex items-center justify-start space-x-3">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-600">1 USD =</span>
                                    <input type="number" x-model="exchangeRate" step="0.01" min="0"
                                        @cannot('customers.edit') readonly @endcannot @keyup.enter="updateRate()"
                                        class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center font-semibold text-gray-900 text-sm"
                                        placeholder="0.00">
                                    <span class="text-sm font-medium text-gray-600">VES</span>
                                </div>

                    @can('customers.edit')
                                    <button @click="updateRate()" :disabled="updating"
                                        class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Actualizar tipo de cambio">
                                        <i class="fas fa-sync-alt text-sm" :class="{ 'animate-spin': updating }"></i>
                        </button>
                    @endcan
                </div>
            </div>

                        <!-- Filtros por Estado -->
                        <div>

                            <!-- Botones de Filtro por Estado -->
                            <div class="flex items-center justify-end space-x-3">
                                <!-- Botón Todos - Azul -->
                                <button type="button" @click="setFilter('all')" title="Todos los clientes"
                                    :class="currentFilter === 'all' ?
                                        'bg-blue-500 border-blue-600 text-white shadow-lg transform scale-105' :
                                        'bg-blue-100 border-blue-300 text-blue-600 hover:bg-blue-200 hover:border-blue-400'"
                                    class="flex items-center justify-center w-12 h-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <i class="fas fa-list text-lg"></i>
                        </button>

                                <!-- Botón Activos - Verde -->
                                <button type="button" @click="setFilter('active')" title="Clientes activos"
                                    :class="currentFilter === 'active' ?
                                        'bg-green-500 border-green-600 text-white shadow-lg transform scale-105' :
                                        'bg-green-100 border-green-300 text-green-600 hover:bg-green-200 hover:border-green-400'"
                                    class="flex items-center justify-center w-12 h-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    <i class="fas fa-check-circle text-lg"></i>
                        </button>

                                <!-- Botón Inactivos - Gris -->
                                <button type="button" @click="setFilter('inactive')" title="Clientes inactivos"
                                    :class="currentFilter === 'inactive' ?
                                        'bg-gray-500 border-gray-600 text-white shadow-lg transform scale-105' :
                                        'bg-gray-100 border-gray-300 text-gray-600 hover:bg-gray-200 hover:border-gray-400'"
                                    class="flex items-center justify-center w-12 h-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    <i class="fas fa-times-circle text-lg"></i>
                        </button>

                                <!-- Botón Morosos - Rojo -->
                                <button type="button" @click="setFilter('defaulters')" title="Clientes morosos"
                                    :class="currentFilter === 'defaulters' ?
                                        'bg-red-500 border-red-600 text-white shadow-lg transform scale-105' :
                                        'bg-red-100 border-red-300 text-red-600 hover:bg-red-200 hover:border-red-400'"
                                    class="flex items-center justify-center w-12 h-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    <i class="fas fa-exclamation-triangle text-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
    .exchange-filters-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 2rem 2rem 1.5rem 2rem;
        margin-bottom: 2rem;
    }

    .exchange-filters-content {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .exchange-block {
        flex: 1 1 340px;
        min-width: 260px;
        max-width: 420px;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .header-icon {
        width: 50px;
        height: 50px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
    }

    .header-text h4 {
        margin: 0;
        font-weight: 600;
        color: var(--dark-color);
    }

    .header-text p {
        margin: 0 0 0.5rem 0;
        color: #666;
        font-size: 0.95rem;
    }

    .rate-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        flex-wrap: wrap;
    }

    .rate-label {
        font-size: 0.95rem;
        color: #666;
        margin-right: 0.5rem;
    }

    .rate-input {
        border: 2px solid #e9ecef;
        border-radius: var(--border-radius-sm);
        padding: 0.75rem;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        transition: var(--transition);
        width: 120px;
    }

    .rate-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

            .currency-symbol,
            .currency-code {
        font-weight: 600;
        color: var(--dark-color);
    }

    .update-rate-btn {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: var(--border-radius-sm);
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: 0.5rem;
    }

    .update-rate-btn:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }

    .filters-block.redesigned-right {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        flex: 2 1 500px;
        min-width: 260px;
        max-width: 700px;
        padding-left: 2rem;
    }

    .filters-title {
        font-weight: 600;
        color: var(--dark-color);
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .filters-search-row {
        display: flex;
        align-items: center;
        gap: 1.1rem;
        width: 100%;
        justify-content: flex-start;
    }

    .filters-btns {
        display: flex;
        gap: 0.7rem;
        margin-bottom: 0;
        flex-wrap: wrap;
    }

    .redesigned-search-group {
        max-width: 260px;
        min-width: 120px;
        width: 100%;
        margin-left: 0.7rem;
        flex: 0 0 auto;
    }

    .search-container {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    @media (max-width: 991px) {
        .filters-search-row {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }

        .redesigned-search-group {
            margin-left: 0;
            max-width: 100%;
        }
    }

            /* ===== TABLA MODERNA ESTILO ESTÁNDAR ===== */
            .table-container {
                overflow-x: auto;
                border-radius: 16px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                background: white;
            }

            .modern-table {
                width: 100%;
                border-collapse: collapse;
                background: white;
            }

            .modern-table thead {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }

            .modern-table th {
                padding: 1rem;
                text-align: left;
                border: none;
                position: relative;
            }

            .th-content {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                color: white;
                font-weight: 600;
                font-size: 1rem;
            }

            .modern-table td {
                padding: 1.25rem;
                border-bottom: 1px solid #e2e8f0;
                vertical-align: middle;
            }

            .table-row {
                transition: all 0.2s ease;
            }

            .table-row:hover {
                background: #f8fafc;
                transform: scale(1.01);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            }

            /* Número de fila */
            .row-number {
                width: 45px;
                height: 45px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 700;
                font-size: 1rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            }

            /* Información del cliente */
            .customer-info {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .customer-avatar .avatar-circle {
                width: 45px;
                height: 45px;
                background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #64748b;
                font-size: 1.3rem;
                font-weight: bold;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            }

            .customer-details {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .customer-name {
                font-weight: 700;
                color: #1f2937;
                font-size: 1rem;
            }

            .customer-email {
                color: #718096;
                font-size: 0.85rem;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .customer-email i {
                color: #64748b;
            }

            /* Información de contacto */
            .contact-info {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: #4a5568;
                font-size: 0.95rem;
                font-weight: 500;
            }

            .contact-info i {
                color: #64748b;
            }

            /* Badge de ID */
            .id-info {
                display: flex;
                align-items: center;
            }

            .id-badge {
                background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
                color: #4a5568;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                border: 1px solid #e2e8f0;
            }

            /* Información de ventas */
            .sales-info {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .sales-amount {
                font-weight: 700;
                color: #1f2937;
                font-size: 0.95rem;
            }

            .sales-count {
                color: #718096;
                font-size: 0.85rem;
                font-weight: 500;
            }

            .no-sales {
                background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
                color: #718096;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                border: 1px solid #e2e8f0;
            }

            /* Información de deuda */
            .debt-info {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .debt-amount {
                font-weight: 700;
                color: #e53e3e;
                font-size: 0.95rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .debt-warning-badge {
                background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
                color: #c53030;
                padding: 0.25rem 0.5rem;
                border-radius: 12px;
                font-size: 0.75rem;
                font-weight: 600;
                border: 1px solid #fbb6ce;
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
            }

            .no-debt-badge {
                background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
                color: #22543d;
                padding: 0.4rem 0.8rem;
                border-radius: 16px;
                font-size: 0.7rem;
                font-weight: 600;
                border: 1px solid #9ae6b4;
                white-space: nowrap;
            }

            .edit-debt-btn {
                background: none;
                border: none;
                color: #667eea;
                cursor: pointer;
                padding: 0.5rem;
                border-radius: 50%;
                transition: all 0.3s ease;
                font-size: 0.9rem;
            }

            .edit-debt-btn:hover {
                background: rgba(102, 126, 234, 0.1);
                color: #5a67d8;
                transform: scale(1.1);
            }

            .edit-debt-btn-small {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                color: white;
                cursor: pointer;
                padding: 0.25rem;
                border-radius: 4px;
                transition: all 0.3s ease;
                font-size: 0.75rem;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .edit-debt-btn-small:hover {
                background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
                transform: scale(1.1);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }

            /* Información de deuda en Bs */
            .debt-bs-info {
                color: #4a5568;
                font-size: 0.95rem;
                font-weight: 500;
            }

            .bs-debt {
                color: #4a5568;
                font-weight: 600;
            }

            /* Estado */
            .status-info {
                display: flex;
                align-items: center;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                border: 1px solid;
            }

            .status-active {
                background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
                color: #22543d;
                border-color: #9ae6b4;
            }

            .status-inactive {
                background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
                color: #718096;
                border-color: #e2e8f0;
            }

            /* Botones de acción */
            .action-buttons {
                display: flex;
                gap: 0.75rem;
                justify-content: center;
            }

            .btn-action {
                width: 40px;
                height: 40px;
                border: none;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 1rem;
                text-decoration: none;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            }

            .btn-view {
                background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
                color: white;
            }

            .btn-edit {
                background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
                color: white;
            }

            .btn-delete {
                background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
                color: white;
            }

            .btn-sale {
                background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%);
                color: white;
            }

            .btn-action:hover {
                transform: scale(1.15);
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            }

            .btn-view:hover {
                background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            }

            .btn-edit:hover {
                background: linear-gradient(135deg, #dd6b20 0%, #c05621 100%);
            }

            .btn-delete:hover {
                background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            }

            .btn-sale:hover {
                background: linear-gradient(135deg, #805ad5 0%, #6b46c1 100%);
            }

            /* Responsive para tabla */
            @media (max-width: 1024px) {

                .modern-table th,
                .modern-table td {
                    padding: 0.75rem;
                }

                .th-content {
                    font-size: 0.9rem;
                    gap: 0.5rem;
                }

                .row-number {
                    width: 35px;
                    height: 35px;
                    font-size: 0.9rem;
                }

                .customer-avatar .avatar-circle {
                    width: 35px;
                    height: 35px;
                    font-size: 1.1rem;
                }

                .btn-action {
                    width: 35px;
                    height: 35px;
                    font-size: 0.9rem;
                }
            }

            @media (max-width: 768px) {
                .table-container {
                    border-radius: 12px;
                }

                .modern-table th,
                .modern-table td {
                    padding: 0.5rem;
                }

                .th-content {
                    font-size: 0.8rem;
                    gap: 0.4rem;
                }

                .row-number {
                    width: 30px;
                    height: 30px;
                    font-size: 0.8rem;
                }

                .customer-avatar .avatar-circle {
                    width: 30px;
                    height: 30px;
                    font-size: 1rem;
                }

                .btn-action {
                    width: 30px;
                    height: 30px;
                    font-size: 0.8rem;
                }

                .action-buttons {
                    gap: 0.5rem;
                }
            }
    </style>

        <!-- Tabla de Clientes Rediseñada con Tailwind y Alpine.js -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden" x-data="dataTable()">

            <!-- Header de la Tabla con Toggle de Vista -->
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Lista de Clientes</h3>
                        </div>
                    </div>

                    <!-- Barra de Búsqueda -->
                    <div class="flex-1 max-w-sm mx-auto lg:mx-0 lg:ml-8">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" x-model="searchTerm" @input="performSearch()"
                                class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-500 text-sm"
                                placeholder="Buscar por nombre, email o teléfono...">
                            <button x-show="searchTerm.length > 0" @click="clearSearch()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <!-- Search Results Counter -->
                        <div x-show="searchTerm.length > 0" x-transition class="mt-1">
                            <div class="flex items-center space-x-2 text-xs text-gray-600">
                                <i class="fas fa-info-circle"></i>
                                <span x-text="`${searchResultsCount} resultado(s) encontrado(s)`"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Toggle Vista - Solo visible en desktop/tablet -->
                    <div class="hidden md:flex items-center space-x-3">
                        <span class="text-sm font-medium text-gray-700">Vista:</span>
                        <div class="flex items-center bg-gray-100 rounded-lg p-1">
                            <button @click="viewMode = 'table'"
                                :class="viewMode === 'table' ? 'bg-white text-gray-900 shadow-sm' :
                                    'text-gray-600 hover:text-gray-900'"
                                class="flex items-center space-x-2 px-3 py-2 rounded-md transition-all duration-200 focus:outline-none">
                                <i class="fas fa-table text-sm"></i>
                                <span class="text-sm font-medium">Tabla</span>
                            </button>
                            <button @click="viewMode = 'cards'"
                                :class="viewMode === 'cards' ? 'bg-white text-gray-900 shadow-sm' :
                                    'text-gray-600 hover:text-gray-900'"
                                class="flex items-center space-x-2 px-3 py-2 rounded-md transition-all duration-200 focus:outline-none">
                                <i class="fas fa-th-large text-sm"></i>
                                <span class="text-sm font-medium">Tarjetas</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista de Tabla - Desktop/Tablet -->
            <div x-show="viewMode === 'table'" class="hidden md:block">
            <div class="table-container">
                    <table id="customersTable" class="modern-table">
                    <thead>
                        <tr>
                                <th>
                                    <div class="th-content">
                                        <span>#</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-user"></i>
                                        <span>Cliente</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-phone"></i>
                                        <span>Contacto</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-id-card"></i>
                                        <span>C.I</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-shopping-cart"></i>
                                        <span>Total Compras</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>Deuda Total</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-coins"></i>
                                        <span>Deuda Bs</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-toggle-on"></i>
                                        <span>Estado</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-cogs"></i>
                                        <span>Acciones</span>
                                    </div>
                                </th>
                        </tr>
                    </thead>
                        <tbody id="customersTableBody">
                        @foreach ($customers as $customer)
                                <tr class="table-row" data-customer-id="{{ $customer->id }}"
                                    data-status="{{ $customer->sales->count() > 0 ? 'active' : 'inactive' }}">
                                    <td>
                                        <div class="row-number">
                                            {{ $loop->iteration }}
                                        </div>
                                    </td>
                                    <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">
                                            <div class="avatar-circle">
                                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="customer-details">
                                                <span class="customer-name">{{ $customer->name }}</span>
                                            <div class="customer-email">
                                                <i class="fas fa-envelope"></i>
                                                {{ $customer->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                    <td>
                                    <div class="contact-info">
                                        <i class="fas fa-phone"></i>
                                        <span>{{ $customer->phone }}</span>
                                    </div>
                                </td>
                                    <td>
                                        <div class="id-info">
                                    <span class="id-badge">{{ $customer->nit_number }}</span>
                                        </div>
                                </td>
                                    <td>
                                        <div class="sales-info">
                                            @if ($customer->sales->count() > 0)
                                                <div class="sales-amount">{{ $currency->symbol }}
                                                    {{ number_format($customer->sales->sum('total_price'), 2) }}</div>
                                            <div class="sales-count">{{ $customer->sales->count() }} venta(s)</div>
                                    @else
                                        <span class="no-sales">Sin ventas</span>
                                    @endif
                                        </div>
                                </td>
                                    <td>
                                        <div class="debt-info">
                                            @if ($customer->total_debt > 0)
                                                <div class="debt-amount debt-value flex items-center gap-2"
                                                 data-customer-id="{{ $customer->id }}" 
                                                 data-original-value="{{ $customer->total_debt }}">
                                                    <span>{{ $currency->symbol }} <span
                                                            class="debt-amount-value">{{ number_format($customer->formatted_total_debt, 2) }}</span></span>
                                                @if ($customersData[$customer->id]['isDefaulter'])
                                                        <span class="debt-warning-badge"
                                                            title="Cliente con deudas de arqueos anteriores">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </span>
                                                @endif
                                            @if ($customer->total_debt > 0)
                                                <button class="edit-debt-btn-small" @click="openModal('debtPaymentModal'); loadDebtPaymentData({{ $customer->id }})">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </button>
                                            @else
                                                @can('customers.edit')
                                                    <button class="edit-debt-btn-small">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endcan
                                            @endif
                                        </div>
                                    @else
                                                <div class="debt-amount flex items-center gap-2">
                                            <span class="no-debt-badge">Sin deuda</span>
                                            @can('customers.edit')
                                                        <button class="edit-debt-btn-small">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    @endif
                                        </div>
                                </td>
                                    <td>
                                        <div class="debt-bs-info">
                                    @if ($customer->total_debt > 0)
                                        <span class="bs-debt" data-debt="{{ $customer->total_debt }}">
                                            Bs. {{ number_format($customer->total_debt, 2) }}
                                        </span>
                                    @else
                                        <span class="no-debt-badge">Sin deuda</span>
                                    @endif
                                        </div>
                                </td>
                                    <td>
                                        <div class="status-info">
                                    @if ($customer->sales->count() > 0)
                                        <span class="status-badge status-active">
                                            <i class="fas fa-check-circle"></i>
                                            Activo
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive">
                                            <i class="fas fa-times-circle"></i>
                                            Inactivo
                                        </span>
                                    @endif
                                        </div>
                                </td>
                                    <td>
                                    <div class="action-buttons">
                                        @can('customers.show')
                                                <button type="button" class="btn-action btn-view"
                                                    @click="openModal('showCustomerModal'); loadCustomerDetails({{ $customer->id }})" 
                                                    data-toggle="tooltip" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endcan
                                        @can('customers.edit')
                                            <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                                    class="btn-action btn-edit" data-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('customers.destroy')
                                                <button type="button" class="btn-action btn-delete delete-customer"
                                                data-id="{{ $customer->id }}" data-toggle="tooltip" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                        @can('sales.create')
                                            <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                                    class="btn-action btn-sale" data-toggle="tooltip" title="Nueva venta">
                                                <i class="fas fa-cart-plus"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

            <!-- Vista de Tarjetas - Móvil y Desktop (cuando se selecciona) -->
            <div x-show="viewMode === 'cards'" class="md:block" :class="{ 'block': true, 'hidden': false }">
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="mobileCustomersContainer">
                @foreach ($customers as $customer)
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-l-4 {{ $customer->sales->count() > 0 ? 'border-green-500' : 'border-gray-400' }}"
                        data-status="{{ $customer->sales->count() > 0 ? 'active' : 'inactive' }}" 
                            data-defaulter="{{ $customersData[$customer->id]['isDefaulter'] ? 'true' : 'false' }}">

                            <!-- Header de la Tarjeta -->
                            <div class="p-6 pb-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div
                                            class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $customer->name }}
                                            </h3>
                                            <div class="flex items-center space-x-1 text-sm text-gray-500 mt-1">
                                                <i class="fas fa-envelope text-xs"></i>
                                                <span class="truncate">{{ $customer->email }}</span>
                            </div>
                                </div>
                            </div>

                                    <!-- Estado -->
                                    <div class="flex-shrink-0">
                                @if ($customer->sales->count() > 0)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Activo
                                    </span>
                                @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                Inactivo
                                    </span>
                                @endif
                                    </div>
                            </div>
                        </div>
                        
                            <!-- Información Principal -->
                            <div class="px-6 pb-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Teléfono -->
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-phone"></i>
                                            <span>Teléfono</span>
                                    </div>
                                        <p class="text-sm font-medium text-gray-900">{{ $customer->phone }}</p>
                                </div>

                                    <!-- C.I -->
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-id-card"></i>
                                            <span>C.I</span>
                                    </div>
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $customer->nit_number }}
                                        </span>
                                    </div>

                                    <!-- Total Compras -->
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-shopping-cart"></i>
                                            <span>Total Compras</span>
                                    </div>
                                        @if ($customer->sales->count() > 0)
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $currency->symbol }}
                                                    {{ number_format($customer->sales->sum('total_price'), 2) }}</p>
                                                <p class="text-xs text-gray-500">({{ $customer->sales->count() }} ventas)
                                                </p>
                                            </div>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                                Sin ventas
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Deuda -->
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-money-bill-wave"></i>
                                            <span>Deuda</span>
                                    </div>
                                        @if ($customer->total_debt > 0)
                                            <div class="space-y-1">
                                                <div class="debt-value" data-customer-id="{{ $customer->id }}"
                                                     data-original-value="{{ $customer->total_debt }}">
                                                    <p class="text-sm font-semibold text-red-600">
                                                        {{ $currency->symbol }} <span
                                                            class="debt-amount-value">{{ number_format($customer->formatted_total_debt, 2) }}</span>
                                                    </p>
                                                    <p class="bs-debt text-xs text-gray-600"
                                                        data-debt="{{ $customer->total_debt }}">
                                                    Bs. {{ number_format($customer->total_debt, 2) }}
                                                    </p>
                                                </div>
                                                    @if ($customersData[$customer->id]['isDefaulter'])
                                                    <span
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                                        title="Cliente con deudas de arqueos anteriores">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                            Moroso
                                                        </span>
                                                    @endif
                                            </div>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-800">
                                                Sin deuda
                                            </span>
                                        @endif
                                </div>
                            </div>
                        </div>
                        
                            <!-- Acciones -->
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                                <div class="flex justify-center gap-3">
                                @can('customers.show')
                                        <button type="button"
                                            class="w-10 h-10 flex items-center justify-center rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            @click="openModal('showCustomerModal'); loadCustomerDetails({{ $customer->id }})" 
                                            title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endcan
                                @can('customers.edit')
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                            class="w-10 h-10 flex items-center justify-center rounded-lg bg-green-500 hover:bg-green-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @if ($customer->total_debt > 0)
                                        <button
                                            class="w-10 h-10 flex items-center justify-center rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            @click="openModal('debtPaymentModal'); loadDebtPaymentData({{ $customer->id }})" 
                                            title="Pagar deuda">
                                        <i class="fas fa-dollar-sign"></i>
                                    </button>
                                @endif
                                @can('sales.create')
                                    <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                            class="w-10 h-10 flex items-center justify-center rounded-lg bg-purple-500 hover:bg-purple-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            title="Nueva venta">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                @endcan
                                @can('customers.destroy')
                                        <button type="button"
                                            class="w-10 h-10 flex items-center justify-center rounded-lg bg-red-500 hover:bg-red-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 delete-customer"
                                            data-id="{{ $customer->id }}" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Vista Solo Móvil (automática) -->
            <div class="block md:hidden">
                <div class="p-4 space-y-4" id="mobileOnlyContainer">
                    @foreach ($customers as $customer)
                        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 {{ $customer->sales->count() > 0 ? 'border-green-500' : 'border-gray-400' }}"
                            data-status="{{ $customer->sales->count() > 0 ? 'active' : 'inactive' }}"
                            data-defaulter="{{ $customersData[$customer->id]['isDefaulter'] ? 'true' : 'false' }}">

                            <!-- Header Compacto -->
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $customer->name }}
                                            </h3>
                                            <p class="text-xs text-gray-500 truncate">{{ $customer->email }}</p>
                                        </div>
                                    </div>
                                    @if ($customer->sales->count() > 0)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                        </span>
                                    @endif
                                </div>

                                <!-- Info Compacta -->
                                <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <span class="text-gray-500">📞</span>
                                        <span class="ml-1 text-gray-900">{{ $customer->phone }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">🆔</span>
                                        <span class="ml-1 text-gray-900">{{ $customer->nit_number }}</span>
                                    </div>
                                    @if ($customer->total_debt > 0)
                                        <div class="col-span-2">
                                            <span class="text-red-600 font-medium debt-value"
                                                data-customer-id="{{ $customer->id }}"
                                                data-original-value="{{ $customer->total_debt }}">
                                                💰 {{ $currency->symbol }} <span
                                                    class="debt-amount-value">{{ number_format($customer->formatted_total_debt, 2) }}</span>
                                            </span>
                                            @if ($customersData[$customer->id]['isDefaulter'])
                                                <span
                                                    class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    ⚠️ Moroso
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Acciones Compactas -->
                                <div class="mt-3 flex justify-center gap-2">
                                    @can('customers.show')
                                        <button type="button"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            @click="openModal('showCustomerModal'); loadCustomerDetails({{ $customer->id }})" 
                                            title="Ver detalles">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>
                                    @endcan
                                    @can('customers.edit')
                                        <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-green-500 hover:bg-green-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            title="Editar">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                    @endcan
                                    @if ($customer->total_debt > 0)
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            @click="openModal('debtPaymentModal'); loadDebtPaymentData({{ $customer->id }})" 
                                            title="Pagar deuda">
                                            <i class="fas fa-dollar-sign text-xs"></i>
                                        </button>
                                    @endif
                                    @can('sales.create')
                                        <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-purple-500 hover:bg-purple-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                            title="Nueva venta">
                                            <i class="fas fa-cart-plus text-xs"></i>
                                        </a>
                                    @endcan
                                    @can('customers.destroy')
                                        <button type="button"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-500 hover:bg-red-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 delete-customer"
                                            data-id="{{ $customer->id }}" title="Eliminar">
                                            <i class="fas fa-trash text-xs"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

        {{-- Paginación (igual que categorías) --}}
    <div class="custom-pagination">
        <div class="pagination-info">
            <span id="paginationInfo">Mostrando 1-{{ min(25, $customers->count()) }} de {{ $customers->total() }} clientes</span>
        </div>
        <div class="pagination-controls">
            <button id="prevPage" class="pagination-btn" disabled>
                <i class="fas fa-chevron-left"></i>
                Anterior
            </button>
            <div id="pageNumbers" class="page-numbers"></div>
            <button id="nextPage" class="pagination-btn">
                Siguiente
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

     {{-- Paginación de Laravel (fallback para carga inicial) --}}
     @if($customers->hasPages())
         <div class="mt-4 flex justify-center">
             {{ $customers->appends(request()->query())->links() }}
         </div>
     @endif

    {{-- Modal de Detalles del Cliente Rediseñado con Alpine.js --}}
    <div x-show="showCustomerModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal('showCustomerModal')"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                
                <!-- Header del Modal -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-t-2xl">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-tie text-white text-lg"></i>
                        </div>
                        <div>
                            <h5 class="text-xl font-bold text-gray-900">Detalles del Cliente</h5>
                            <p class="text-sm text-gray-600">Información completa y historial de ventas</p>
                        </div>
                    </div>
                    <button type="button" @click="closeModal('showCustomerModal')" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 rounded-lg flex items-center justify-center transition-all duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Body del Modal -->
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <!-- Header de la Sección -->
                        <div class="flex items-center space-x-4 p-6 bg-gradient-to-r from-gray-50 to-blue-50 border-b border-gray-200">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-white"></i>
                            </div>
                            <div>
                                <h6 class="text-lg font-semibold text-gray-900">Historial de Ventas</h6>
                                <p class="text-sm text-gray-600">Cliente: <span id="customerName" class="font-semibold text-blue-600"></span></p>
                            </div>
                        </div>
                        
                        <!-- Filtros -->
                        <div class="p-6 border-b border-gray-100">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                                <!-- Rango de Fechas -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Rango de Fechas</label>
                                    <div class="flex items-center space-x-3">
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-calendar text-gray-400"></i>
                                            </div>
                                            <input type="date" id="dateFrom" placeholder="Desde" 
                                                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        </div>
                                        <span class="text-sm text-gray-500 font-medium">hasta</span>
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-calendar text-gray-400"></i>
                                            </div>
                                            <input type="date" id="dateTo" placeholder="Hasta" 
                                                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        </div>
                                    </div>
                                </div>

                                <!-- Rango de Monto -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Rango de Monto</label>
                                    <div class="flex items-center space-x-3">
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-medium">{{ $currency->symbol }}</span>
                                            </div>
                                            <input type="number" id="amountFrom" placeholder="Mínimo" step="0.01" min="0"
                                                class="w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        </div>
                                        <span class="text-sm text-gray-500 font-medium">-</span>
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-medium">{{ $currency->symbol }}</span>
                                            </div>
                                            <input type="number" id="amountTo" placeholder="Máximo" step="0.01" min="0"
                                                class="w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Filtro -->
                            <div class="flex justify-end space-x-3">
                                <button type="button" id="clearFilters" 
                                    class="flex items-center space-x-2 px-4 py-2.5 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    <i class="fas fa-times text-sm"></i>
                                    <span class="text-sm font-medium">Limpiar</span>
                                </button>
                                <button type="button" id="applyFilters" 
                                    class="flex items-center space-x-2 px-6 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md hover:shadow-lg">
                                    <i class="fas fa-filter text-sm"></i>
                                    <span class="text-sm font-medium">Aplicar Filtros</span>
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de Ventas -->
                        <div class="p-6">
                            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                                <table class="w-full">
                                    <thead class="bg-gradient-to-r from-gray-50 to-blue-50 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b border-gray-200">Fecha</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b border-gray-200">Productos</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b border-gray-200">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="salesHistoryTable">
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
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Footer de la Tabla -->
                            <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                                <div class="text-sm text-gray-600">
                                    <span id="salesCount" class="font-semibold">0</span> ventas mostradas
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para el reporte de deudas rediseñado con Alpine.js --}}
    <div id="debtReportModal" x-show="debtReportModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal('debtReportModal')"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-content relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                
                <!-- Header del Modal -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-red-50 to-pink-50 rounded-t-2xl">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-invoice-dollar text-white text-lg"></i>
                        </div>
                        <div>
                            <h5 class="text-xl font-bold text-gray-900">Reporte de Deudas</h5>
                            <p class="text-sm text-gray-600">Análisis detallado de deudas por cliente</p>
                        </div>
                    </div>
                    <button type="button" @click="closeModal('debtReportModal')" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 rounded-lg flex items-center justify-center transition-all duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Body del Modal -->
                <div class="modal-body p-8">
                    <div class="flex flex-col items-center justify-center py-12">
                        <!-- Spinner de Carga -->
                        <div class="w-16 h-16 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin mb-6"></div>
                        
                        <!-- Texto de Carga -->
                        <div class="text-center">
                            <h5 class="text-xl font-semibold text-gray-900 mb-2">Cargando reporte de deudas</h5>
                            <p class="text-gray-600">Preparando información detallada...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para registrar pagos de deuda rediseñado con Alpine.js --}}
    <div x-show="debtPaymentModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal('debtPaymentModal')"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                
                <!-- Header del Modal -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50 rounded-t-2xl">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-white text-lg"></i>
                        </div>
                        <div>
                            <h5 class="text-xl font-bold text-gray-900">Registrar Pago de Deuda</h5>
                            <p class="text-sm text-gray-600">Gestiona los pagos de tus clientes de forma eficiente</p>
                        </div>
                    </div>
                    <button type="button" @click="closeModal('debtPaymentModal')" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 rounded-lg flex items-center justify-center transition-all duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="debtPaymentForm">
                    <div class="p-6 max-h-[70vh] overflow-y-auto">
                        <input type="hidden" id="payment_customer_id" name="customer_id">
                        
                        <div class="space-y-6">
                            <!-- Información del Cliente -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                    <h6 class="text-lg font-semibold text-gray-900">Información del Cliente</h6>
                                </div>
                                <div class="space-y-3">
                                    <label for="customer_name" class="text-sm font-semibold text-gray-700">Cliente</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                        <input type="text" id="customer_name" readonly
                                            class="w-full pl-10 pr-3 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 text-sm">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Estado de Deuda -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-chart-line text-white text-sm"></i>
                                    </div>
                                    <h6 class="text-lg font-semibold text-gray-900">Estado de Deuda</h6>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-sm font-semibold text-gray-700">Deuda Actual</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-medium">{{ $currency->symbol }}</span>
                                            </div>
                                            <input type="text" id="current_debt" readonly
                                                class="w-full pl-8 pr-3 py-2.5 bg-red-50 border border-red-200 rounded-lg text-red-700 font-semibold text-sm">
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-semibold text-gray-700">Deuda Restante</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-medium">{{ $currency->symbol }}</span>
                                            </div>
                                            <input type="text" id="remaining_debt" readonly
                                                class="w-full pl-8 pr-3 py-2.5 bg-orange-50 border border-orange-200 rounded-lg text-orange-700 font-semibold text-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Detalles del Pago -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-credit-card text-white text-sm"></i>
                                    </div>
                                    <h6 class="text-lg font-semibold text-gray-900">Detalles del Pago</h6>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="space-y-2">
                                        <label for="payment_amount" class="text-sm font-semibold text-gray-700">Monto del Pago</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-dollar-sign text-gray-400"></i>
                                            </div>
                                            <input type="number" id="payment_amount" name="payment_amount" step="0.01" min="0.01" required
                                                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                                        </div>
                                        <small class="text-xs text-gray-500">El monto no puede ser mayor que la deuda actual</small>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <label for="payment_date" class="text-sm font-semibold text-gray-700">Fecha del Pago</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-calendar text-gray-400"></i>
                                            </div>
                                            <input type="date" id="payment_date" name="payment_date" required
                                                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                                        </div>
                                        <small class="text-xs text-gray-500">La fecha no puede ser mayor a hoy</small>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <label for="payment_time" class="text-sm font-semibold text-gray-700">Hora del Pago</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-clock text-gray-400"></i>
                                            </div>
                                            <input type="time" id="payment_time" name="payment_time" required
                                                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                                        </div>
                                        <small class="text-xs text-gray-500">Hora en que se realizó el pago</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Notas Adicionales -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-sticky-note text-white text-sm"></i>
                                    </div>
                                    <h6 class="text-lg font-semibold text-gray-900">Notas Adicionales</h6>
                                </div>
                                <div class="space-y-2">
                                    <label for="payment_notes" class="text-sm font-semibold text-gray-700">Notas</label>
                                    <textarea id="payment_notes" name="notes" rows="3" placeholder="Detalles adicionales sobre este pago..."
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm resize-vertical"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer del Modal -->
                    <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                        <button type="button" class="flex items-center space-x-2 px-6 py-2.5 text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500" data-dismiss="modal">
                            <i class="fas fa-times text-sm"></i>
                            <span class="text-sm font-medium">Cancelar</span>
                        </button>
                        <button type="submit" class="flex items-center space-x-2 px-6 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-md hover:shadow-lg">
                            <i class="fas fa-save text-sm"></i>
                            <span class="text-sm font-medium">Registrar Pago</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    
    <style>
        /* ===== VARIABLES Y CONFIGURACIÓN GLOBAL ===== */
        :root {
            --primary-color: #667eea;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-color: #f093fb;
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-color: #4facfe;
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-color: #43e97b;
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --danger-color: #fa709a;
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --purple-color: #a8edea;
            --purple-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --dark-color: #2c3e50;
            --light-color: #f8fafc;
            --border-color: #e2e8f0;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --shadow-light: 0 2px 8px rgba(0,0,0,0.07);
            --shadow-medium: 0 4px 16px rgba(0,0,0,0.12);
            --shadow-heavy: 0 20px 40px rgba(0,0,0,0.1);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Alpine.js x-cloak directive */
        [x-cloak] {
            display: none !important;
        }

        /* ===== MEJORAS CON TAILWIND ===== */
        /* Mejoras en la accesibilidad y focus states */
        .hero-btn:focus,
        .filter-btn:focus,
        .search-input:focus,
        .rate-input:focus {
            @apply ring-2 ring-blue-500 ring-offset-2 outline-none;
        }

        /* Mejoras en las transiciones */
        .stat-card,
        .hero-btn,
        .filter-btn,
        .action-btn {
            @apply transition-all duration-300 ease-in-out;
        }

        /* Mejoras en el hover de las tarjetas */
        .stat-card:hover {
            @apply transform -translate-y-1 shadow-lg;
        }

        /* Mejoras en los botones de acción */
        .action-btn:hover {
            @apply transform scale-105 shadow-md;
        }

        /* Mejoras en la tabla */
        .customers-table tbody tr {
            @apply transition-colors duration-200;
        }

        .customers-table tbody tr:hover {
            @apply bg-gray-50;
        }

        /* Mejoras en los badges */
        .status-badge,
        .growth-badge,
        .debt-warning-badge {
            @apply transition-all duration-200;
        }

        /* Mejoras en los inputs */
        .search-input,
        .rate-input {
            @apply focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
        }

        /* Mejoras en los modales */
        .modern-modal {
            @apply backdrop-blur-sm;
        }

        /* Mejoras en la responsividad */
        @media (max-width: 640px) {
            .hero-title {
                @apply text-2xl;
            }
            
            .stat-number {
                @apply text-xl;
            }
        }

        /* ===== ANIMACIONES Y ESTADOS DE CARGA ===== */
        /* Spinner personalizado */
        .spinner-custom {
            @apply animate-spin rounded-full border-2 border-gray-300 border-t-blue-600;
        }

        /* Animación de fade in para las tarjetas */
        .customer-card {
            @apply animate-fade-in;
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

        /* Mejoras en los estados de hover */
        .customer-card:hover {
            @apply transform -translate-y-1 shadow-lg;
        }

        /* Mejoras en los botones de acción */
        .action-btn {
            @apply transition-all duration-200 ease-in-out;
        }

        .action-btn:hover {
            @apply transform scale-110 shadow-lg;
        }

        /* Mejoras en los filtros */
        .filter-btn {
            @apply transition-all duration-200 ease-in-out;
        }

        .filter-btn:hover:not(.active) {
            @apply transform -translate-y-0.5 shadow-md;
        }

        /* Mejoras en el tipo de cambio */
        .update-rate-btn:disabled {
            @apply opacity-50 cursor-not-allowed;
        }

        /* Mejoras en la búsqueda */
        .search-clear {
            @apply transition-all duration-200 ease-in-out;
        }

        .search-clear:hover {
            @apply transform scale-110 bg-gray-200;
        }

        /* ===== HERO SECTION ===== */
        .hero-section {
            background: var(--primary-gradient);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .hero-title i {
            font-size: 3rem;
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        .hero-stats {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .action-btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .action-btn-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .action-btn-warning {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        /* ===== STATS GRID ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

            .stat-card-primary::before {
                background: var(--primary-gradient);
            }

            .stat-card-success::before {
                background: var(--success-gradient);
            }

            .stat-card-warning::before {
                background: var(--warning-gradient);
            }

            .stat-card-purple::before {
                background: var(--purple-gradient);
            }

            .stat-card-danger::before {
                background: var(--danger-gradient);
            }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

            .stat-card-primary .stat-icon {
                background: var(--primary-gradient);
            }

            .stat-card-success .stat-icon {
                background: var(--success-gradient);
            }

            .stat-card-warning .stat-icon {
                background: var(--warning-gradient);
            }

            .stat-card-purple .stat-icon {
                background: var(--purple-gradient);
            }

            .stat-card-danger .stat-icon {
                background: var(--danger-gradient);
            }

        .stat-content {
            position: relative;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }

        .growth-badge {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .growth-positive {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .stat-progress {
            height: 4px;
            background: #f0f0f0;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: var(--primary-gradient);
            border-radius: 2px;
            transition: width 1s ease-in-out;
        }

        /* ===== EXCHANGE RATE CARD ===== */
        .exchange-rate-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .header-text h4 {
            margin: 0;
            font-weight: 600;
            color: var(--dark-color);
        }

        .header-text p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .collapse-btn {
            background: none;
            border: none;
            color: #666;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .collapse-btn:hover {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .card-body {
            padding: 1.5rem;
        }

        .exchange-rate-content {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2rem;
            align-items: center;
        }

        .rate-input-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .rate-display {
            flex: 1;
        }

        .rate-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .rate-value {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .currency-symbol {
            font-weight: 600;
            color: var(--dark-color);
        }

        .rate-input {
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            padding: 0.75rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            transition: var(--transition);
            width: 120px;
        }

        .rate-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .currency-code {
            font-weight: 600;
            color: var(--dark-color);
        }

        .update-rate-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .update-rate-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .rate-info {
            max-width: 300px;
        }

        .info-card {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border-radius: var(--border-radius-sm);
            padding: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .info-card i {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-top: 0.1rem;
        }

        .info-content h6 {
            margin: 0 0 0.5rem 0;
            color: var(--dark-color);
            font-weight: 600;
        }

        .info-content p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        /* ===== FILTERS SECTION ===== */
        .filters-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .filters-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .filter-label {
            font-weight: 600;
            color: var(--dark-color);
            white-space: nowrap;
        }

        .filter-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .filter-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            background: white;
            color: #666;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-1px);
        }

        .filter-btn.active {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
            box-shadow: var(--shadow);
        }

            .filter-btn-all.active {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }

            .filter-btn-active.active {
                background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            }

            .filter-btn-inactive.active {
                background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            }

            .filter-btn-defaulters.active {
                background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            }

        .search-group {
            flex: 1;
            max-width: 400px;
        }

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            color: #666;
            z-index: 2;
        }

        .search-input {
            width: 100%;
            min-width: 0;
            max-width: 260px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-clear {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 50%;
            transition: var(--transition);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e0e0e0;
        }

        .search-clear:hover {
            background: #bdbdbd;
            color: #222;
        }

        /* ===== CUSTOMERS CONTAINER ===== */
        .customers-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* ===== TABLE VIEW ===== */
        .table-container {
            overflow-x: auto;
        }

        .customers-table {
            width: 100%;
            border-collapse: collapse;
        }

        .customers-table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: 2px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .customers-table td {
            padding: 1rem;
            border-bottom: 1px solid #f8f9fa;
            vertical-align: middle;
        }

        .customers-table tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .customer-row {
            transition: var(--transition);
        }

        .customer-row:hover {
            transform: scale(1.01);
            box-shadow: var(--shadow);
        }

        /* Customer Info */
        .customer-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .customer-avatar {
            flex-shrink: 0;
        }

        .avatar-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .avatar-circle:hover {
            transform: scale(1.1);
        }

        .customer-details {
            flex: 1;
        }

        .customer-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }

        .customer-email {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Contact Info */
        .contact-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
        }

        /* ID Badge */
        .id-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Sales Info */
        .sales-info {
            text-align: center;
        }

        .sales-amount {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }

        .sales-count {
            color: #666;
            font-size: 0.8rem;
        }

        .no-sales {
            color: #999;
            font-style: italic;
        }

        /* Debt Info */
        .debt-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .debt-amount {
            font-weight: 600;
            color: #dc3545;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .debt-amount-value {
            font-size: 1.1rem;
        }

        .debt-warning-badge {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            cursor: help;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .debt-warning-badge:hover {
            transform: scale(1.1);
                box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        }

        .debt-status {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .no-debt-badge {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .edit-debt-btn {
            background: none;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .edit-debt-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }

        /* Status Badge */
        .status-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .status-inactive {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        /* Debt Type Badge - Solo para móviles */
        .debt-type-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .debt-type-defaulters {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
        }

        .debt-type-current {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .debt-type-none {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        /* Debt Type Info for Mobile Cards */
        .debt-type-info {
            margin-top: 0.5rem;
        }

        .debt-type-info .debt-type-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* Tooltip styles for debt type explanation */
        .debt-type-badge {
            cursor: help;
            position: relative;
        }

        .debt-type-badge[title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            white-space: nowrap;
            z-index: 1000;
            margin-bottom: 0.5rem;
        }

        /* Ocultar badges de tipo de deuda en pantallas grandes */
        @media (min-width: 992px) {
            .debt-type-badge {
                display: none !important;
            }
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            font-size: 0.9rem;
        }

        .action-btn-view {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .action-btn-edit {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .action-btn-delete {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .action-btn-sale {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .action-btn:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-hover);
        }

        /* ===== CARDS VIEW (MOBILE) ===== */
        .cards-container {
            padding: 1.5rem;
            display: grid;
            gap: 1.5rem;
        }

        .customer-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }

        .customer-card[data-status="active"] {
            border-left-color: #4facfe;
        }

        .customer-card[data-status="inactive"] {
            border-left-color: #fa709a;
        }

        .customer-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .card-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid #f8f9fa;
        }

        .customer-avatar {
            flex-shrink: 0;
        }

        .avatar-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.5rem;
            transition: var(--transition);
        }

        .customer-info {
            flex: 1;
        }

        .customer-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }

        .customer-email {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-indicator {
            flex-shrink: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
            color: var(--dark-color);
        }

        .info-value small {
            font-weight: normal;
            color: #666;
        }

        .card-actions {
            padding: 1.5rem;
            border-top: 1px solid #f8f9fa;
            background: #f8f9fa;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 0.75rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border: none;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .action-btn-view {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .action-btn-edit {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .action-btn-payment {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .action-btn-sale {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: var(--dark-color);
        }

        .action-btn-delete {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        /* ===== MODALS ===== */
        /* Los modales ahora usan Tailwind CSS completamente */

        /* ===== PAGINACIÓN (igual que categorías) ===== */
        .custom-pagination {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 2rem !important;
            background: white !important;
            border-top: 1px solid var(--border-color) !important;
            border-radius: 0 0 24px 24px !important;
        }

        .pagination-info {
            color: #64748b !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
        }

        .pagination-controls {
            display: flex !important;
            align-items: center !important;
            gap: 1rem !important;
        }

        .pagination-btn {
            display: flex !important;
            align-items: center !important;
            gap: 0.75rem !important;
            padding: 0.75rem 1.25rem !important;
            border: 2px solid var(--border-color) !important;
            background: white !important;
            color: #64748b !important;
            border-radius: 12px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
        }

        .pagination-btn:hover:not(:disabled) {
            background: var(--light-color) !important;
            border-color: var(--primary-color) !important;
            color: var(--primary-color) !important;
            transform: translateY(-2px) !important;
            box-shadow: var(--shadow-light) !important;
        }

        .pagination-btn:disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            transform: none !important;
            box-shadow: none !important;
        }

        .page-numbers {
            display: flex !important;
            gap: 0.5rem !important;
        }

        .page-number {
            width: 40px !important;
            height: 40px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 2px solid var(--border-color) !important;
            background: white !important;
            color: #64748b !important;
            border-radius: 10px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
        }

        .page-number:hover {
            background: var(--light-color) !important;
            border-color: var(--primary-color) !important;
            color: var(--primary-color) !important;
            transform: translateY(-2px) !important;
            box-shadow: var(--shadow-light) !important;
        }

        .page-number.active {
            background: var(--gradient-primary) !important;
            color: white !important;
            border-color: var(--primary-color) !important;
            box-shadow: var(--shadow-medium) !important;
        }

        /* Paginación de Laravel (fallback) */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .pagination .page-item {
            list-style: none;
        }

        .pagination .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            color: #6b7280;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            background: white;
        }

        .pagination .page-link:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
            color: #374151;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            background: #f9fafb;
            border-color: #e5e7eb;
            color: #9ca3af;
            cursor: not-allowed;
        }

        .pagination .page-item.disabled .page-link:hover {
            background: #f9fafb;
            border-color: #e5e7eb;
            color: #9ca3af;
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 768px) {
            .custom-pagination {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1rem;
            }

            .pagination-info {
                font-size: 0.9rem;
            }

            .pagination-controls {
                gap: 0.5rem;
            }

            .pagination-btn {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }

            .page-number {
                width: 36px;
                height: 36px;
                font-size: 0.85rem;
            }
            .hero-section {
                padding: 1.5rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-stats {
                gap: 1rem;
            }

            .stat-number {
                font-size: 1.5rem;
            }

            .action-buttons {
                flex-direction: row;
                gap: 0.5rem;
            }

            .action-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .exchange-rate-content {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .rate-input-section {
                flex-direction: column;
                align-items: stretch;
            }

            .filters-container {
                flex-direction: column;
                gap: 1rem;
            }

            .search-group {
                max-width: none;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                grid-template-columns: repeat(2, 1fr);
            }

            .debt-status-card {
                grid-template-columns: 1fr;
            }

            .payment-details-grid {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .custom-pagination {
                padding: 0.75rem;
            }

            .pagination-info {
                font-size: 0.8rem;
            }

            .pagination-btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }

            .page-number {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }

            .hero-title {
                font-size: 1.5rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .stat-number {
                font-size: 1.5rem;
            }

            .action-btn {
                padding: 0.5rem;
                font-size: 0.8rem;
            }

            .customer-card .card-header {
                padding: 1rem;
            }

            .avatar-circle {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

            .customer-card,
            .stat-card,
            .exchange-rate-card {
            animation: fadeInUp 0.6s ease-out;
        }

            .customer-card:nth-child(1) {
                animation-delay: 0.1s;
            }

            .customer-card:nth-child(2) {
                animation-delay: 0.2s;
            }

            .customer-card:nth-child(3) {
                animation-delay: 0.3s;
            }

            .customer-card:nth-child(4) {
                animation-delay: 0.4s;
            }

            .customer-card:nth-child(5) {
                animation-delay: 0.5s;
            }

        /* ===== SCROLLBAR STYLING ===== */
        .table-wrapper::-webkit-scrollbar,
        .customer-details-container::-webkit-scrollbar {
            width: 8px;
        }

        .table-wrapper::-webkit-scrollbar-track,
        .customer-details-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-wrapper::-webkit-scrollbar-thumb,
        .customer-details-container::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover,
        .customer-details-container::-webkit-scrollbar-thumb:hover {
            background: #5a6fd8;
        }

        /* Firefox scrollbar */
        .table-wrapper,
        .customer-details-container {
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) #f1f1f1;
        }

        /* ===== ESTILOS ADICIONALES PARA LA TABLA DE VENTAS ===== */
            .sale-date,
            .sale-products,
            .sale-amount {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .sale-date {
            color: var(--dark-color);
        }

        .sale-products {
            color: #666;
        }

        .sale-amount {
            color: #28a745;
            font-weight: 600;
        }

            .sale-date i,
            .sale-products i,
            .sale-amount i {
            color: var(--primary-color);
            font-size: 0.9rem;
        }

        /* ===== MEJORAS EN LA EXPERIENCIA DE USUARIO ===== */
        .counter {
            animation: countUp 2s ease-out;
        }

        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Efecto de hover mejorado para las tarjetas */
        .customer-card:hover .avatar-circle {
            transform: scale(1.1) rotate(5deg);
        }

        /* Efecto de pulso para los botones de acción */
        .action-btn:active {
            transform: scale(0.95);
        }

        /* Mejora en la legibilidad de los textos */
            .customer-name,
            .stat-number,
            .debt-amount {
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Efecto de sombra dinámica */
            .stat-card:hover,
            .customer-card:hover,
            .exchange-rate-card:hover {
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        /* Transiciones suaves para todos los elementos interactivos */
        * {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Mejora en el contraste de colores */
        .text-muted {
            color: #6c757d !important;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        .text-info {
            color: #17a2b8 !important;
        }

        /* Efecto de carga para los botones */
        .action-btn.loading {
            position: relative;
            pointer-events: none;
        }

        .action-btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Mejora en la accesibilidad */
        .action-btn:focus,
        .filter-btn:focus,
        .search-input:focus,
        .modern-input:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Los modales ahora usan Tailwind CSS completamente */

        /* --- BOTONES DE ACCIÓN EN TABLA --- */
        .td-actions .action-buttons {
            display: flex !important;
            flex-direction: row !important;
            gap: 0.5rem !important;
            justify-content: flex-start;
            align-items: center;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            min-width: 36px;
            min-height: 36px;
            border: none;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            font-size: 1.1rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            background: #bdbdbd;
            padding: 0;
        }

            .action-btn-view {
                background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            }

            .action-btn-edit {
                background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            }

            .action-btn-delete {
                background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            }

            .action-btn-sale {
                background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
                color: var(--dark-color);
            }

            .action-btn:hover,
            .action-btn:focus {
                filter: brightness(1.1) drop-shadow(0 2px 8px rgba(0, 0, 0, 0.08));
            transform: scale(1.08);
            outline: none;
        }

        .action-btn i {
            margin: 0;
            font-size: 1.1rem;
        }

        /* --- BOTONES GENERALES --- */
            .action-btn,
            .modern-btn,
            .update-rate-btn,
            .filter-btn,
            .search-clear {
            border-radius: 10px !important;
            font-weight: 600;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

            .modern-btn,
            .update-rate-btn {
            padding: 0.6rem 1.2rem;
            font-size: 1rem;
        }

        .update-rate-btn {
            background: var(--primary-gradient);
            color: #fff;
        }

        .update-rate-btn:hover {
            filter: brightness(1.1);
        }

        .filter-btn {
            font-size: 0.95rem;
            padding: 0.5rem 1.1rem;
        }

        .search-clear {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e0e0e0;
            color: #666;
        }

        .search-clear:hover {
            background: #bdbdbd;
            color: #222;
        }

        /* --- Ajuste para iconos en botones --- */
            .action-btn span,
            .action-btn i {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- Responsive para botones en tabla --- */
        @media (max-width: 768px) {
            .td-actions .action-buttons {
                flex-wrap: wrap;
                gap: 0.3rem !important;
            }

            .action-btn {
                width: 32px;
                height: 32px;
                min-width: 32px;
                min-height: 32px;
                font-size: 1rem;
            }
        }

        .redesigned-rate-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }

        .update-rate-btn {
            margin-left: 1rem;
            align-self: stretch;
            height: 48px;
            display: flex;
            align-items: center;
        }

        @media (max-width: 767px) {
            .redesigned-rate-row {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
            }

            .update-rate-btn {
                margin-left: 0 !important;
                width: 100%;
                height: auto;
            }
        }

        .exchange-block.redesigned-left {
            display: flex;
            flex-direction: row;
            align-items: center;
            min-width: 320px;
            max-width: 420px;
            flex: 1 1 350px;
            padding-right: 2rem;
            border-right: 1.5px solid #f0f0f0;
            gap: 0;
        }

        .redesigned-rate-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            flex-wrap: nowrap;
            width: 100%;
        }

        .update-rate-btn {
            margin-left: 1rem;
            height: 48px;
            display: flex;
            align-items: center;
            padding-top: 0;
            padding-bottom: 0;
        }

        @media (max-width: 991px) {
            .exchange-block.redesigned-left {
                flex-direction: column;
                align-items: stretch;
                padding-right: 0;
                border-right: none;
            }

            .redesigned-rate-row {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
                width: 100%;
            }

            .update-rate-btn {
                margin-left: 0 !important;
                width: 100%;
                height: auto;
            }
        }

        @media (max-width: 576px) {
            .card-actions {
                padding: 0.75rem 0.5rem;
            }

            .action-buttons {
                display: flex !important;
                flex-direction: row !important;
                gap: 0.4rem !important;
                overflow-x: auto;
                justify-content: flex-start;
                align-items: center;
                padding-bottom: 0.2rem;
                    scrollbar-width: none;
                    /* Firefox */
            }

            .action-buttons::-webkit-scrollbar {
                    display: none;
                    /* Chrome/Safari */
            }

            .action-btn {
                min-width: 44px;
                min-height: 44px;
                width: 44px;
                height: 44px;
                font-size: 1.3rem;
                padding: 0;
                border-radius: 12px !important;
                justify-content: center;
            }

            .action-btn span {
                display: none !important;
            }

            .action-btn:active {
                transform: scale(0.93);
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.10);
            }
        }

        @media (max-width: 576px) {
            .redesigned-rate-row {
                flex-direction: row !important;
                align-items: center !important;
                gap: 0.5rem !important;
                flex-wrap: nowrap !important;
                justify-content: flex-start !important;
            }

            .update-rate-btn {
                margin-left: 0.5rem !important;
                width: auto !important;
                min-width: 44px;
                height: 44px !important;
                padding: 0 1.2rem !important;
                align-self: auto !important;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .rate-input {
                width: 90px !important;
                min-width: 70px;
                height: 44px !important;
                padding: 0.5rem !important;
                font-size: 1.1rem !important;
            }

                .currency-symbol,
                .currency-code {
                font-size: 1rem !important;
            }

            .filters-search-row {
                flex-direction: row !important;
                align-items: center !important;
                gap: 0.5rem !important;
                flex-wrap: nowrap !important;
                justify-content: flex-start !important;
            }

            .filters-btns {
                flex-direction: row !important;
                gap: 0.3rem !important;
                flex-wrap: nowrap !important;
            }

            .redesigned-search-group {
                margin-left: 0.5rem !important;
                max-width: 140px !important;
                min-width: 80px !important;
            }

            .search-input {
                font-size: 0.95rem !important;
                padding: 0.5rem 1rem 0.5rem 2.2rem !important;
                height: 38px !important;
            }
        }

        @media (max-width: 576px) {

            /* ...otros estilos responsivos... */
            .update-rate-btn span {
                display: none !important;
            }

            .filters-search-row {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 0.5rem !important;
            }

            .redesigned-search-group {
                margin-left: 0 !important;
                max-width: 100% !important;
                min-width: 0 !important;
            }
        }

        /* --- FILTROS: BOTONES RESPONSIVOS Y CENTRADOS --- */
        .filters-btns-scroll {
            display: flex;
            gap: 0.7rem;
            flex-wrap: nowrap;
        }

        .redesigned-search-group {
            max-width: 260px;
            min-width: 120px;
            width: 100%;
            margin-left: 0.7rem;
            flex: 0 0 auto;
        }

        .search-container {
             position: relative;
             display: flex;
             align-items: center;
             width: 100%;
         }

        .search-input {
            width: 100%;
            min-width: 0;
            max-width: 260px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-clear {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 50%;
            transition: var(--transition);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e0e0e0;
        }

        .search-clear:hover {
            background: #bdbdbd;
            color: #222;
        }

        /* Ocultar campo de búsqueda visual de DataTables pero mantener funcionalidad */
        .dataTables_filter {
            display: none !important;
        }
        
        /* Ocultar también el label "Search:" si aparece */
        .dataTables_filter label {
            display: none !important;
        }
        
        /* Ocultar el input de búsqueda nativo de DataTables */
        .dataTables_filter input {
            display: none !important;
        }
        
        /* Estilos responsivos para botones de filtro */
        @media (max-width: 575px) {
            .filters-btns-scroll {
                gap: 0.5rem;
                flex-wrap: nowrap;
                width: 100%;
                overflow-x: auto;
                padding-bottom: 0.5rem;
                    scrollbar-width: none;
                    /* Firefox */
            }

            .filters-btns-scroll::-webkit-scrollbar {
                    display: none;
                    /* Chrome/Safari */
            }
            
            .filter-btn {
                min-width: 44px;
                width: 44px;
                height: 44px;
                padding: 0.5rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                margin: 0;
            }
            
            .filter-btn i {
                font-size: 1.1rem;
                margin: 0;
            }
            
            .filter-btn span {
                display: none !important;
            }
        }
@stop

@section('js')

            <script src="{{ asset('vendor/config.js') }}"></script><script>
        // Funciones de Alpine.js

        function exchangeFilters() {
            return {
                exchangeRate: 120.00,
                updating: false,
                
                init() {
                    // Cargar el tipo de cambio guardado
                    const savedRate = localStorage.getItem('exchangeRate');
                    if (savedRate) {
                        this.exchangeRate = parseFloat(savedRate);
                    }
                },
                
                updateExchangeRate() {
                    if (this.exchangeRate <= 0) return;
                    
                    this.updating = true;
                    
                    // Simular actualización
                    setTimeout(() => {
                        currentExchangeRate = this.exchangeRate;
                        localStorage.setItem('exchangeRate', this.exchangeRate);
                        updateBsValues(this.exchangeRate);
                        
                        this.updating = false;
                        
                        // Mostrar notificación
                        Swal.fire({
                            icon: 'success',
                            title: 'Tipo de cambio actualizado',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }, 500);
                }
            }
        }



        // Variable global para almacenar el tipo de cambio actual
        let currentExchangeRate = 1.0;

        $(document).ready(function() {
            // Cargar DataTables dinámicamente
            loadDataTables(function() {
                // Inicializar DataTable
                const table = $('#customersTable').DataTable({
                responsive: true,
                autoWidth: false,
                stateSave: true, // Guarda la página y el estado del paginador
                searching: true, // Mantener búsqueda habilitada para filtros personalizados
                lengthChange: false,
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
                }
            });

            // Conectar el campo de búsqueda con DataTables (vista desktop)
            $('#mobileSearch').on('keyup', function() {
                applyFiltersAndSearch();
            });

            // Mostrar/ocultar botón de limpiar búsqueda
            $('#mobileSearch').on('input', function() {
                const hasValue = $(this).val().length > 0;
                $('#clearSearch').toggle(hasValue);
            });

            // Cargar el tipo de cambio guardado en localStorage (si existe)
            const savedRate = localStorage.getItem('exchangeRate');
            if (savedRate) {
                currentExchangeRate = parseFloat(savedRate);
                $('#exchangeRate').val(currentExchangeRate);
                updateBsValues(currentExchangeRate);
            } else {
                // Si no hay valor guardado, usar el valor por defecto del input
                currentExchangeRate = parseFloat($('#exchangeRate').val());
                updateBsValues(currentExchangeRate);
            }
            
            // Actualizar valores en Bs cuando se cambia el tipo de cambio - Usar delegación de eventos
            $(document).on('click', '.update-exchange-rate', function() {
                const rate = parseFloat($('#exchangeRate').val());
                if (rate > 0) {
                    currentExchangeRate = rate;
                    // Guardar en localStorage para futuras visitas
                    localStorage.setItem('exchangeRate', rate);
                    updateBsValues(rate);
                    
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: 'Tipo de cambio actualizado',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
            
            // Función para actualizar todos los valores en Bs
            function updateBsValues(rate) {
                $('.bs-debt').each(function() {
                    const debtUsd = parseFloat($(this).data('debt'));
                    const debtBs = debtUsd * rate;
                                $(this).html('Bs. ' + debtBs.toLocaleString('es-VE', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }));
                });
            }
            
            // Botón de reporte de deudas
            $('#debtReportBtn').click(function() {
                // Mostrar el modal de carga inmediatamente
                $('#debtReportModal').modal('show');
                
                // Remover aria-hidden cuando el modal se muestra
                $('#debtReportModal').removeAttr('aria-hidden');
                
                // Cargar el reporte mediante AJAX con timeout
                $.ajax({
                                url: '{{ route('admin.customers.debt-report') }}',
                    type: 'GET',
                    data: {
                        exchange_rate: currentExchangeRate
                    },
                    timeout: 30000, // 30 segundos de timeout
                    success: function(response) {
                        // Llenar el modal con la respuesta
                        $('#debtReportModal .modal-content').html(response);
                        
                        // Pasar el tipo de cambio actual al modal
                                    $('#debtReportModal').data('exchangeRate',
                                        currentExchangeRate);
                    },
                    error: function(xhr, status, error) {
                        // Cerrar el modal de carga
                        $('#debtReportModal').modal('hide');
                        
                        if (status === 'timeout') {
                                        Swal.fire('Error',
                                            'El reporte tardó demasiado en cargar. Inténtalo de nuevo.',
                                            'error');
                        } else {
                                        Swal.fire('Error',
                                            'No se pudo cargar el reporte de deudas',
                                            'error');
                        }
                    }
                });
            });

            // Escuchar el evento de modal mostrado
            $(document).on('shown.bs.modal', '#debtReportModal', function() {
                
                // Asegurar que aria-hidden esté removido
                $('#debtReportModal').removeAttr('aria-hidden');
                
                // Establecer el valor del tipo de cambio en el modal
                $('#modalExchangeRate').val(currentExchangeRate);
                
                // Actualizar los valores en Bs en el modal
                updateModalBsValues(currentExchangeRate);
            });
            
            // Escuchar el evento de modal oculto para restaurar aria-hidden
            $(document).on('hidden.bs.modal', '#debtReportModal', function() {
                // Restaurar aria-hidden cuando el modal se cierra
                $('#debtReportModal').attr('aria-hidden', 'true');
            });
            




            // Animación de contadores
            $('.counter').each(function() {
                const $this = $(this);
                const countTo = parseInt($this.text());

                $({
                    countNum: 0
                }).animate({
                    countNum: countTo
                }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(this.countNum);
                    }
                });
            });

            // Variable para mantener el filtro actual
            let currentFilter = 'all';
            
            // Filtros de estado - Mantener compatibilidad con Alpine.js
            $(document).on('click touchstart', '.filter-btn', function(e) {
                e.stopPropagation();
                
                // Si Alpine.js está disponible, usar su sistema
                if (window.Alpine && window.Alpine.store) {
                    const filter = $(this).data('filter');
                    if (window.Alpine.store('filters')) {
                        window.Alpine.store('filters').currentFilter = filter;
                    }
                } else {
                    // Fallback al sistema original
                    $('.filter-btn').removeClass('active');
                    $(this).addClass('active');
                    currentFilter = $(this).data('filter');
                }
                
                applyFiltersAndSearch();
            });
            
            // Función para aplicar filtros y búsqueda
            function applyFiltersAndSearch() {
                // Obtener el término de búsqueda
                const searchTerm = $('#mobileSearch').val();
                
                // Obtener el filtro actual
                let currentFilter = 'all';
                
                // Verificar si hay un botón activo
                const activeButton = $('.filter-btn.active');
                if (activeButton.length > 0) {
                    currentFilter = activeButton.data('filter');
                }
                
                // Si Alpine.js está disponible, usar su estado
                if (window.Alpine && window.Alpine.store && window.Alpine.store('filters')) {
                    currentFilter = window.Alpine.store('filters').currentFilter || currentFilter;
                }
                
                // Filtrar tabla (vista desktop)
                if (table) {
                    // Limpiar filtros previos
                    table.search('').columns().search('').draw();
                    
                    // Aplicar filtro de estado
                    if (currentFilter === 'active') {
                        table.column(6).search('Activo').draw();
                    } else if (currentFilter === 'inactive') {
                        table.column(6).search('Inactivo').draw();
                    } else if (currentFilter === 'defaulters') {
                        // Filtrar clientes morosos usando una función personalizada
                        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                            // Verificar si la fila tiene el icono de advertencia (moroso)
                            const row = table.row(dataIndex).node();
                                        const hasWarningIcon = $(row).find('.debt-warning-badge').length >
                                            0;
                            return hasWarningIcon;
                        });
                        table.draw();
                        // Remover el filtro personalizado después de aplicarlo
                        $.fn.dataTable.ext.search.pop();
                    }
                    
                    // Aplicar búsqueda si hay término de búsqueda
                    if (searchTerm) {
                        table.search(searchTerm).draw();
                    }
                }
                
                // Filtrar tarjetas móviles
                $('.customer-card').each(function() {
                    const $card = $(this);
                    const cardStatus = $card.data('status');
                    const dataDefaulter = $card.data('defaulter');
                    const isDefaulter = dataDefaulter === true || dataDefaulter === 'true';
                    let shouldShow = false;
                    
                    // Aplicar filtro de estado
                    if (currentFilter === 'all') {
                        shouldShow = true;
                    } else if (currentFilter === 'active' && cardStatus === 'active') {
                        shouldShow = true;
                    } else if (currentFilter === 'inactive' && cardStatus === 'inactive') {
                        shouldShow = true;
                    } else if (currentFilter === 'defaulters' && isDefaulter) {
                        shouldShow = true;
                    }
                    
                    // Aplicar búsqueda si hay término de búsqueda
                    if (shouldShow && searchTerm) {
                        const customerName = $card.find('.customer-name').text().toLowerCase();
                                    const customerEmail = $card.find('.customer-email').text()
                                        .toLowerCase();
                        const customerPhone = $card.find('.info-value').text().toLowerCase();
                        
                        shouldShow = customerName.includes(searchTerm.toLowerCase()) || 
                                   customerEmail.includes(searchTerm.toLowerCase()) || 
                                   customerPhone.includes(searchTerm.toLowerCase());
                    }
                    
                    // Mostrar/ocultar tarjeta
                    if (shouldShow) {
                        $card.show();
                    } else {
                        $card.hide();
                    }
                });
            }



            // Limpiar búsqueda
            $('#clearSearch').click(function() {
                $('#mobileSearch').val('');
                applyFiltersAndSearch();
            });

            // Tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Inicializar filtros al cargar la página
            applyFiltersAndSearch();



                                        // Variable global para almacenar las ventas del cliente actual
                            let currentCustomerSales = [];

            // Ver detalles del cliente - Usar delegación de eventos para funcionar con DataTable
            $(document).on('click', '.show-customer', function() {
                const customerId = $(this).data('id');

                $.ajax({
                    url: `/customers/${customerId}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const customer = response.customer;

                            // Mostrar nombre del cliente en el encabezado
                            $('#customerName').text(customer.name);

                            // Guardar las ventas globalmente para filtrado
                            currentCustomerSales = customer.sales || [];

                            // Llenar tabla de historial de ventas
                            displaySales(currentCustomerSales);

                            // Limpiar filtros
                            $('#dateFrom').val('');
                            $('#dateTo').val('');
                            $('#amountFrom').val('');
                            $('#amountTo').val('');

                            $('#showCustomerModal').modal('show');
                        }
                    },
                    error: function() {
                                    Swal.fire('Error',
                                        'No se pudieron cargar los detalles del cliente',
                                        'error');
                    }
                });
            });

                            // Función para mostrar las ventas
                            function displaySales(sales) {
                                const salesTable = $('#salesHistoryTable');
                                salesTable.empty();
                                
                                if (sales && sales.length > 0) {
                                    sales.forEach(function(sale) {
                                        const row = `
                                            <tr>
                                                <td>
                                                    <div class="sale-date">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        ${sale.date}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="sale-products">
                                                        <i class="fas fa-box"></i>
                                                        ${sale.total_products} productos
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="sale-amount">
                                                        <i class="fas fa-dollar-sign"></i>
                                                        {{ $currency->symbol }}${parseFloat(sale.total_amount).toLocaleString('es-PE', {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2
                                                        })}
                                                    </div>
                                                </td>
                                            </tr>
                                        `;
                                        salesTable.append(row);
                                    });
                                    
                                    // Actualizar contador
                                    $('#salesCount').text(sales.length);
                                } else {
                                    salesTable.html(`
                                        <tr>
                                            <td colspan="3" class="empty-state">
                                                <div class="empty-icon">
                                                    <i class="fas fa-info-circle"></i>
                                                </div>
                                                <p>No hay ventas que coincidan con los filtros</p>
                                            </td>
                                        </tr>
                                    `);
                                    $('#salesCount').text('0');
                                }
                            }

                            // Función para filtrar ventas
                            function filterSales() {
                                const dateFrom = $('#dateFrom').val();
                                const dateTo = $('#dateTo').val();
                                const amountFrom = parseFloat($('#amountFrom').val()) || 0;
                                const amountTo = parseFloat($('#amountTo').val()) || Infinity;

                                let filteredSales = currentCustomerSales.filter(function(sale) {
                                    // Convertir fecha de venta a objeto Date para comparación
                                    const saleDate = new Date(sale.date.split('/').reverse().join('-'));
                                    
                                    // Filtro de fecha
                                    let dateMatch = true;
                                    if (dateFrom) {
                                        const fromDate = new Date(dateFrom);
                                        dateMatch = dateMatch && saleDate >= fromDate;
                                    }
                                    if (dateTo) {
                                        const toDate = new Date(dateTo);
                                        dateMatch = dateMatch && saleDate <= toDate;
                                    }
                                    
                                    // Filtro de monto
                                const amountMatch = sale.total_amount >= amountFrom && sale.total_amount <=
                                    amountTo;
                                    
                                    return dateMatch && amountMatch;
                                });

                                displaySales(filteredSales);
                            }

                            // Aplicar filtros
                            $('#applyFilters').click(function() {
                                filterSales();
                            });

                            // Limpiar filtros
                            $('#clearFilters').click(function() {
                                $('#dateFrom').val('');
                                $('#dateTo').val('');
                                $('#amountFrom').val('');
                                $('#amountTo').val('');
                                displaySales(currentCustomerSales);
                            });

                            // Aplicar filtros al presionar Enter en los inputs
                            $('#dateFrom, #dateTo, #amountFrom, #amountTo').keypress(function(e) {
                                if (e.which === 13) {
                                    filterSales();
                                }
            });

            // Eliminar cliente - Usar delegación de eventos para funcionar con DataTable
            $(document).on('click', '.delete-customer', function() {
                const id = $(this).data('id');

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
                        $.ajax({
                            url: `/customers/delete/${id}`,
                            type: 'DELETE',
                            headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                                .attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: '¡Eliminado!',
                                        text: response.message,
                                        icon: response.icons
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    // Mostrar mensaje de error con formato mejorado y botones adicionales
                                    let showCancelButton = false;
                                    let cancelButtonText = '';
                                    let confirmButtonText = 'Entendido';
                                    
                                    // Si tiene ventas, mostrar botón para ir a ventas
                                    if (response.has_sales) {
                                        showCancelButton = true;
                                        cancelButtonText = 'Ver Ventas';
                                        confirmButtonText = 'Entendido';
                                    }
                                    
                                    Swal.fire({
                                                    title: response.icons ===
                                                        'warning' ?
                                                        'No se puede eliminar' :
                                                        'Error',
                                                    html: response.message
                                                        .replace(/\n/g, '<br>'),
                                        icon: response.icons,
                                        showCancelButton: showCancelButton,
                                                    confirmButtonColor: response
                                                        .icons === 'warning' ?
                                                        '#ed8936' : '#667eea',
                                        cancelButtonColor: '#667eea',
                                        confirmButtonText: confirmButtonText,
                                        cancelButtonText: cancelButtonText
                                    }).then((result) => {
                                                    if (result.dismiss === Swal
                                                        .DismissReason.cancel &&
                                                        response.has_sales) {
                                            // Redirigir a la página de ventas con filtro por cliente
                                                        window.location.href =
                                                            '/sales?search=' +
                                                            encodeURIComponent(
                                                                response
                                                                .customer_name ||
                                                                '');
                                        }
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                            let errorMessage =
                                                'No se pudo eliminar el cliente';
                                let iconType = 'error';
                                
                                // Intentar obtener el mensaje de error del servidor
                                            if (xhr.responseJSON && xhr.responseJSON
                                                .message) {
                                    errorMessage = xhr.responseJSON.message;
                                    // Determinar el tipo de icono basado en la respuesta del servidor
                                                if (xhr.responseJSON.icons ===
                                                    'warning') {
                                        iconType = 'warning';
                                    }
                                } else if (xhr.status === 422) {
                                                errorMessage =
                                                    'No se puede eliminar este cliente debido a restricciones del sistema';
                                } else if (xhr.status === 404) {
                                                errorMessage =
                                                    'El cliente no fue encontrado';
                                } else if (xhr.status === 403) {
                                                errorMessage =
                                                    'No tienes permisos para eliminar este cliente';
                                } else if (xhr.status === 500) {
                                                errorMessage =
                                                    'Error interno del servidor al eliminar el cliente';
                                }
                                
                                Swal.fire({
                                                title: iconType === 'warning' ?
                                                    'No se puede eliminar' :
                                                    'Error',
                                                html: errorMessage.replace(
                                                    /\n/g, '<br>'),
                                    icon: iconType,
                                                confirmButtonColor: iconType ===
                                                    'warning' ? '#ed8936' :
                                                    '#667eea',
                                    confirmButtonText: 'Entendido'
                                });
                            }
                        });
                    }
                });
            });

            // Exportar clientes
            $('#exportCustomers').click(function() {
                Swal.fire({
                    title: 'Exportar Clientes',
                    text: 'Seleccione el formato de exportación',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Excel',
                    cancelButtonText: 'PDF'
                }).then((result) => {
                    const format = result.isConfirmed ? 'excel' : 'pdf';
                    window.location.href = `/customers/export/${format}`;
                });
            });

            // Manejar el botón de editar deuda
                        $(document).on('click', '.edit-debt-btn, .edit-debt-btn-small', function() {
                let $debtValue, customerId, currentDebt, customerName;
                
                // Verificar si estamos en la vista de escritorio (tabla) o móvil (tarjetas)
                if ($(this).closest('td').length > 0) {
                    // Vista de escritorio (tabla)
                    $debtValue = $(this).closest('td').find('.debt-value');
                    customerId = $debtValue.data('customer-id');
                    currentDebt = parseFloat($debtValue.data('original-value'));
                    customerName = $(this).closest('tr').find('.customer-name').text();
                } else {
                    // Vista móvil (tarjetas)
                    $debtValue = $(this).closest('.customer-card').find('.debt-value');
                    customerId = $debtValue.data('customer-id');
                    currentDebt = parseFloat($debtValue.data('original-value'));
                                customerName = $(this).closest('.customer-card').find('.customer-name')
                                    .text();
                }
                
                // Obtener la fecha actual en formato YYYY-MM-DD usando la fecha del servidor
                const todayString = '{{ date('Y-m-d') }}';
                const currentTime = '{{ date('H:i') }}';
                
                // Llenar el modal con los datos del cliente
                $('#payment_customer_id').val(customerId);
                $('#customer_name').val(customerName);
                $('#current_debt').val(currentDebt.toFixed(2));
                $('#payment_amount').val('').attr('max', currentDebt);
                $('#remaining_debt').val('');
                $('#payment_notes').val('');
                $('#payment_date').val(todayString).attr('max', todayString);
                $('#payment_time').val(currentTime); // Establecer hora actual del servidor
                
                // Mostrar el modal
                $('#debtPaymentModal').modal('show');
            });
            
            // Calcular deuda restante al cambiar el monto del pago
            $('#payment_amount').on('input', function() {
                const currentDebt = parseFloat($('#current_debt').val());
                const paymentAmount = parseFloat($(this).val()) || 0;
                
                if (paymentAmount > currentDebt) {
                    $(this).val(currentDebt);
                    const remainingDebt = 0;
                    $('#remaining_debt').val(remainingDebt.toFixed(2));
                } else {
                    const remainingDebt = currentDebt - paymentAmount;
                    $('#remaining_debt').val(remainingDebt.toFixed(2));
                }
            });
            
            // Manejar el envío del formulario de pago
            $('#debtPaymentForm').submit(function(e) {
                e.preventDefault();
                
                const customerId = $('#payment_customer_id').val();
                const paymentAmount = parseFloat($('#payment_amount').val());
                const paymentDate = $('#payment_date').val();
                const paymentTime = $('#payment_time').val();
                const notes = $('#payment_notes').val();
                
                // Validar que la fecha no sea mayor a hoy usando la fecha del servidor
                const todayString = '{{ date('Y-m-d') }}';
                const selectedDate = new Date(paymentDate);
                const todayDate = new Date(todayString);
                
                if (selectedDate > todayDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Fecha inválida',
                        text: 'La fecha del pago no puede ser mayor a hoy',
                        confirmButtonText: 'Aceptar'
                    });
                    return;
                }
                
                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Procesando pago...',
                    html: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>',
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                
                $.ajax({
                    url: `/admin/customers/${customerId}/register-payment`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        payment_amount: paymentAmount,
                        payment_date: paymentDate,
                        payment_time: paymentTime,
                        notes: notes
                    },
                    success: function(response) {
                        
                        // Actualizar la deuda en todas las vistas (tabla y tarjetas)
                                    const $debtValues = $(
                                        `.debt-value[data-customer-id="${customerId}"]`);
                        $debtValues.each(function() {
                                        $(this).data('original-value', response
                                            .new_debt);
                                        $(this).find('.debt-amount').text(response
                                            .formatted_new_debt);
                        });
                        
                        // Actualizar la deuda en Bs para todas las vistas
                                    const $bsDebts = $(`.bs-debt[data-debt]`).filter(
                                        function() {
                            return $(this).data('debt') !== undefined;
                        });
                        
                        $bsDebts.each(function() {
                            // Verificar si esta deuda pertenece al cliente actual
                                        const $relatedDebtValue = $(this).closest(
                                            'tr, .customer-card').find(
                                            `.debt-value[data-customer-id="${customerId}"]`
                                        );
                            if ($relatedDebtValue.length > 0) {
                                $(this).data('debt', response.new_debt);
                                
                                // Recalcular el valor en Bs con el tipo de cambio actual
                                            const rate = parseFloat($('#exchangeRate')
                                                .val());
                                const debtBs = response.new_debt * rate;
                                            $(this).html('Bs. ' + debtBs.toLocaleString(
                                                'es-VE', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                }));
                            }
                        });
                        
                        // Cerrar el modal
                        $('#debtPaymentModal').modal('hide');
                        
                        // Mostrar mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: 'Pago registrado',
                            text: 'El pago ha sido registrado correctamente',
                            confirmButtonText: 'Aceptar'
                        });
                    },
                    error: function(xhr) {
                        console.error('Error en la solicitud:', xhr);
                        
                                    let errorMessage =
                                        'Ha ocurrido un error al registrar el pago';
                        
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                                        errorMessage = Object.values(xhr.responseJSON.errors)[0]
                                            [0];
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            });
            
            // Función para reinicializar eventos después de cargar nueva página
            function initializeCustomerEvents() {
                // Reinicializar tooltips
                if (typeof $ !== 'undefined' && $.fn.tooltip) {
                    $('[data-toggle="tooltip"]').tooltip();
                }
                
                // Reinicializar eventos de botones de acción
                $('.show-customer').off('click').on('click', function() {
                    const customerId = $(this).data('id');
                    showCustomerDetails(customerId);
                });
                
                $('.delete-customer').off('click').on('click', function() {
                    const customerId = $(this).data('id');
                    deleteCustomer(customerId);
                });
                
                $('.edit-debt-btn, .edit-debt-btn-small').off('click').on('click', function() {
                    const customerId = $(this).closest('[data-customer-id]').data('customer-id');
                    showDebtPaymentModal(customerId);
                });
                
                // Reinicializar eventos de filtros
                $('#applyFilters').off('click').on('click', function() {
                    applySalesFilters();
                });
                
                $('#clearFilters').off('click').on('click', function() {
                    clearSalesFilters();
                });
                
                console.log('Eventos de clientes reinicializados');
            }
            
            // Llamar a la función al cargar la página
            $(document).ready(function() {
                initializeCustomerEvents();
            });
            
            // ===== PAGINACIÓN (igual que categorías) =====
            // Variables globales para paginación
            let currentPage = 1;
            const itemsPerPage = 25;
            let allCustomers = [];
            let filteredCustomers = [];

            // Inicializar la página
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Clientes page loaded');
                console.log('Pagination elements:', {
                    container: document.querySelector('.custom-pagination'),
                    info: document.getElementById('paginationInfo'),
                    prevBtn: document.getElementById('prevPage'),
                    nextBtn: document.getElementById('nextPage'),
                    pageNumbers: document.getElementById('pageNumbers')
                });
                initializeCustomersPage();
                initializeEventListeners();
            });

            // Inicializar la página de clientes
            function initializeCustomersPage() {
                console.log('Initializing customers page...');
                
                // Obtener todas las categorías
                getAllCustomers();
                
                // Mostrar primera página
                showPage(1);
            }

            // Obtener todas las categorías
            function getAllCustomers() {
                const tableRows = document.querySelectorAll('#customersTableBody tr');
                const customerCards = document.querySelectorAll('.customer-card');
                const mobileCards = document.querySelectorAll('.mobile-card');
                
                allCustomers = [];
                
                // Procesar filas de tabla
                tableRows.forEach((row, index) => {
                    const customerName = row.querySelector('.customer-name').textContent.trim();
                    const customerEmail = row.querySelector('.customer-email').textContent.trim();
                    
                    allCustomers.push({
                        element: row,
                        cardElement: customerCards[index],
                        mobileElement: mobileCards[index],
                        data: {
                            id: row.dataset.customerId,
                            name: customerName,
                            email: customerEmail
                        }
                    });
                });
                
                filteredCustomers = [...allCustomers];
                console.log('Customers loaded:', allCustomers.length);
            }

            // Mostrar página específica
            function showPage(page) {
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                
                // Ocultar todas las filas/tarjetas
                document.querySelectorAll('#customersTableBody tr').forEach(row => row.style.display = 'none');
                document.querySelectorAll('.customer-card').forEach(card => card.style.display = 'none');
                document.querySelectorAll('.mobile-card').forEach(card => card.style.display = 'none');
                
                // Mostrar solo los elementos de la página actual
                filteredCustomers.slice(startIndex, endIndex).forEach((customer, index) => {
                    if (customer.element) customer.element.style.display = 'table-row';
                    if (customer.cardElement) customer.cardElement.style.display = 'block';
                    if (customer.mobileElement) customer.mobileElement.style.display = 'block';
                    
                    // Actualizar números de fila
                    if (customer.element) {
                        customer.element.querySelector('.row-number').textContent = startIndex + index + 1;
                    }
                });
                
                // Actualizar información de paginación
                updatePaginationInfo(page, filteredCustomers.length);
                updatePaginationControls(page, Math.ceil(filteredCustomers.length / itemsPerPage));
            }

            // Actualizar información de paginación
            function updatePaginationInfo(currentPage, totalItems) {
                const startItem = (currentPage - 1) * itemsPerPage + 1;
                const endItem = Math.min(currentPage * itemsPerPage, totalItems);
                document.getElementById('paginationInfo').textContent = `Mostrando ${startItem}-${endItem} de ${totalItems} clientes`;
            }

            // Actualizar controles de paginación
            function updatePaginationControls(currentPage, totalPages) {
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
                        <button class="page-number ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">
                            ${i}
                        </button>
                    `;
                }
                
                pageNumbers.innerHTML = pageNumbersHTML;
            }

            // Ir a página específica
            function goToPage(page) {
                currentPage = page;
                showPage(page);
            }

            // Función de búsqueda
            function filterCustomers(searchTerm) {
                const searchLower = searchTerm.toLowerCase().trim();
                
                if (!searchLower) {
                    filteredCustomers = [...allCustomers];
                } else {
                    filteredCustomers = allCustomers.filter(customer => {
                        const nameMatch = customer.data.name.toLowerCase().includes(searchLower);
                        const emailMatch = customer.data.email.toLowerCase().includes(searchLower);
                        return nameMatch || emailMatch;
                    });
                }
                
                currentPage = 1;
                showPage(1);
            }

            // Inicializar event listeners
            function initializeEventListeners() {
                console.log('Initializing event listeners...');
                
                // Paginación
                document.getElementById('prevPage').addEventListener('click', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        showPage(currentPage);
                    }
                });
                
                document.getElementById('nextPage').addEventListener('click', function() {
                    const totalPages = Math.ceil(filteredCustomers.length / itemsPerPage);
                    if (currentPage < totalPages) {
                        currentPage++;
                        showPage(currentPage);
                    }
                });
                
                console.log('Event listeners initialized');
            }
            
            })
        });
            </script>< !-- JavaScript adicional ya está definido arriba -->< !-- CSS adicional --><style>

            /* Animaciones personalizadas */
            @keyframes blob {
                0% {
                    transform: translate(0px, 0px) scale(1);
                }

                33% {
                    transform: translate(30px, -50px) scale(1.1);
                }

                66% {
                    transform: translate(-20px, 20px) scale(0.9);
                }

                100% {
                    transform: translate(0px, 0px) scale(1);
                }
            }

            .animate-blob {
                animation: blob 7s infinite;
            }

            .animation-delay-2000 {
                animation-delay: 2s;
            }

            .animation-delay-4000 {
                animation-delay: 4s;
            }

            /* Mejoras visuales adicionales */
            .backdrop-blur-sm {
                backdrop-filter: blur(4px);
            }

            /* Asegurar que el gradiente de fondo cubra toda la página */
            .min-h-screen {
                min-height: 100vh;
            }

            /* ===== ESTILOS FINALES DE PAGINACIÓN ===== */
            .custom-pagination {
                background: white !important;
                border-top: 1px solid #e2e8f0 !important;
                border-radius: 0 0 24px 24px !important;
                padding: 2rem !important;
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
            }

            .pagination-info {
                color: #64748b !important;
                font-size: 1rem !important;
                font-weight: 600 !important;
            }

            .pagination-controls {
                display: flex !important;
                align-items: center !important;
                gap: 1rem !important;
            }

            .pagination-btn {
                display: flex !important;
                align-items: center !important;
                gap: 0.75rem !important;
                padding: 0.75rem 1.25rem !important;
                border: 2px solid #e2e8f0 !important;
                background: white !important;
                color: #64748b !important;
                border-radius: 12px !important;
                cursor: pointer !important;
                transition: all 0.3s ease !important;
                font-size: 1rem !important;
                font-weight: 600 !important;
            }

            .pagination-btn:hover:not(:disabled) {
                background: #f8fafc !important;
                border-color: #667eea !important;
                color: #667eea !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.07) !important;
            }

            .pagination-btn:disabled {
                opacity: 0.5 !important;
                cursor: not-allowed !important;
                transform: none !important;
                box-shadow: none !important;
            }

            .page-numbers {
                display: flex !important;
                gap: 0.5rem !important;
            }

            .page-number {
                width: 40px !important;
                height: 40px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                border: 2px solid #e2e8f0 !important;
                background: white !important;
                color: #64748b !important;
                border-radius: 10px !important;
                cursor: pointer !important;
                transition: all 0.3s ease !important;
                font-size: 1rem !important;
                font-weight: 600 !important;
            }

            .page-number:hover {
                background: #f8fafc !important;
                border-color: #667eea !important;
                color: #667eea !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.07) !important;
            }

            .page-number.active {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                color: white !important;
                border-color: #667eea !important;
                box-shadow: 0 4px 16px rgba(0,0,0,0.12) !important;
            }
        </style>
@stop
