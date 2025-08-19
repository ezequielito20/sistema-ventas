// ===== CONFIGURACIÓN GLOBAL =====
const CUSTOMERS_CONFIG = {
    routes: {
        delete: '/customers/delete',
        debtReport: '/admin/customers/debt-report',
        paymentHistory: '/admin/customers/payment-history',
        export: '/customers/export'
    },
    exchangeRate: {
        default: 134.0,
        localStorageKey: 'exchangeRate'
    }
};

// ===== FUNCIONES GLOBALES =====
window.customersIndex = {
    // Función para actualizar valores en Bs
    updateBsValues: function(rate) {
        // Actualizar elementos con clase bs-debt
        const bsDebtElements = document.querySelectorAll('.bs-debt');
        bsDebtElements.forEach(function(element) {
            const debtUsd = parseFloat(element.dataset.debt);
            if (!isNaN(debtUsd)) {
                const debtBs = debtUsd * rate;
                element.innerHTML = 'Bs. ' + debtBs.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        });
        
        // Actualizar elementos con clase debt-bs-info (para la tabla)
        const debtBsInfoElements = document.querySelectorAll('.debt-bs-info .bs-debt');
        debtBsInfoElements.forEach(function(element) {
            const debtUsd = parseFloat(element.dataset.debt);
            if (!isNaN(debtUsd)) {
                const debtBs = debtUsd * rate;
                element.innerHTML = 'Bs. ' + debtBs.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        });
    },

    // Función para eliminar cliente
    deleteCustomer: function(customerId) {
        console.log('Función deleteCustomer llamada para ID:', customerId);
        
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
                console.log('Confirmación aceptada, enviando petición fetch...');
                
                // Obtener el token CSRF
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch(`${CUSTOMERS_CONFIG.routes.delete}/${customerId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Respuesta recibida:', data);
                    
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
                            confirmButtonColor: data.icons === 'warning' ? '#ed8936' : '#667eea',
                            cancelButtonColor: '#667eea',
                            confirmButtonText: confirmButtonText,
                            cancelButtonText: cancelButtonText
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.cancel && data.has_sales) {
                                window.location.href = '/sales?search=' + encodeURIComponent(data.customer_name || '');
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar el cliente',
                        confirmButtonText: 'Aceptar'
                    });
                });
            }
        });
    },

    // Función para mostrar notificaciones
    showNotification: function(message, type = 'success') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: type === 'success' ? '¡Éxito!' : '¡Error!',
                text: message,
                icon: type,
                confirmButtonText: 'Aceptar',
                confirmButtonColor: type === 'success' ? '#28a745' : '#d33',
                customClass: {
                    confirmButton: `btn btn-${type === 'success' ? 'success' : 'danger'}`
                },
                buttonsStyling: true,
                background: '#fff',
                showCloseButton: true,
                timer: 10000,
                timerProgressBar: true,
                toast: false,
                position: 'center'
            });
        } else {
            // Fallback si SweetAlert2 no está disponible
            alert(type.toUpperCase() + ': ' + message);
        }
    }
};

// ===== FUNCIONES DE ALPINE.JS =====
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

window.dataTable = function() {
    return {
        viewMode: window.innerWidth >= 768 ? 'table' : 'cards', // Default: table en desktop, cards en móvil
        searchTerm: '',
        searchResultsCount: 0,

        init() {
            // Detectar cambios de tamaño de pantalla
            window.addEventListener('resize', () => {
                // En móvil siempre mostrar cards, en desktop permitir toggle
                if (window.innerWidth < 768) {
                    this.viewMode = 'cards';
                }
            });
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
        totalResults: window.totalCustomers || 0,

        init() {
            this.updateActiveFiltersIndicator();
        },

        toggleFilters() {
            this.filtersOpen = !this.filtersOpen;
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
        exchangeRate: 134.0, // Valor por defecto
        updating: false,

        init() {
            // Cargar el tipo de cambio guardado en localStorage
            const savedRate = localStorage.getItem(CUSTOMERS_CONFIG.exchangeRate.localStorageKey);
            if (savedRate) {
                this.exchangeRate = parseFloat(savedRate);
            } else {
                // Si no hay valor guardado, usar el valor por defecto
                this.exchangeRate = CUSTOMERS_CONFIG.exchangeRate.default;
                localStorage.setItem(CUSTOMERS_CONFIG.exchangeRate.localStorageKey, this.exchangeRate.toString());
            }
            
            // Actualizar valores en Bs inmediatamente después de inicializar
            setTimeout(() => {
                window.customersIndex.updateBsValues(this.exchangeRate);
            }, 100);
            
            // Sincronizar con el modal si está abierto
            setTimeout(() => {
                const modalExchangeRateInput = document.getElementById('modalExchangeRate');
                if (modalExchangeRateInput) {
                    modalExchangeRateInput.value = this.exchangeRate;
                }
            }, 500);
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

            // Usar la función centralizada para guardar
            if (typeof saveExchangeRate === 'function') {
                saveExchangeRate(this.exchangeRate);
            } else {
                // Fallback si la función no está disponible
                window.currentExchangeRate = this.exchangeRate;
                localStorage.setItem(CUSTOMERS_CONFIG.exchangeRate.localStorageKey, this.exchangeRate);
            }

            // Sincronizar con el modal si está abierto
            const modalExchangeRateInput = document.getElementById('modalExchangeRate');
            if (modalExchangeRateInput) {
                modalExchangeRateInput.value = this.exchangeRate;
            }

            // Actualizar valores en Bs en la tabla principal
            window.customersIndex.updateBsValues(this.exchangeRate);

            // Actualizar valores en Bs en el modal si está abierto
            if (typeof window.modalManager !== 'undefined' && window.modalManager().updateModalBsValues) {
                window.modalManager().updateModalBsValues(this.exchangeRate);
            }

            // Trigger el evento del botón original si existe
            const updateBtn = document.querySelector('.update-exchange-rate');
            if (updateBtn) {
                updateBtn.click();
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

            setTimeout(() => {
                this.updating = false;
            }, 1000);
        },

        // Método para sincronizar desde el modal
        syncFromModal(rate) {
            if (rate > 0 && rate !== this.exchangeRate) {
                this.exchangeRate = rate;
                
                // Actualizar valores en Bs en la tabla principal
                window.customersIndex.updateBsValues(rate);
            }
        },

        // Método para sincronizar hacia el modal
        syncToModal() {
            if (this.exchangeRate > 0) {
                // Guardar el valor en localStorage
                localStorage.setItem(CUSTOMERS_CONFIG.exchangeRate.localStorageKey, this.exchangeRate.toString());
                
                // Actualizar valores en Bs en tiempo real
                window.customersIndex.updateBsValues(this.exchangeRate);
                
                // Sincronizar con el modal si está abierto
                const modalExchangeRateInput = document.getElementById('modalExchangeRate');
                if (modalExchangeRateInput) {
                    modalExchangeRateInput.value = this.exchangeRate;
                    
                    // Trigger el evento input para actualizar los valores
                    modalExchangeRateInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        }
    }
}

// ===== FUNCIÓN PRINCIPAL DE INICIALIZACIÓN =====
function initializeCustomersIndex() {
    // Inicializar el tipo de cambio inmediatamente al cargar el script
    (function() {
        // Cargar el tipo de cambio guardado en localStorage
        const savedRate = localStorage.getItem(CUSTOMERS_CONFIG.exchangeRate.localStorageKey);
        if (savedRate) {
            const rate = parseFloat(savedRate);
            // Actualizar el input si existe
            const exchangeRateInput = document.getElementById('exchangeRate');
            if (exchangeRateInput) {
                exchangeRateInput.value = rate;
            }
        }
    })();

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

        // Inicializar el tipo de cambio usando la función centralizada
        initializeExchangeRate();
        
        // Actualizar valores en Bs cuando se cambia el tipo de cambio - Usar delegación de eventos
        $(document).on('click', '.update-exchange-rate', function() {
            const rate = parseFloat($('#exchangeRate').val());
            if (rate > 0) {
                // Usar la función centralizada para guardar
                if (typeof saveExchangeRate === 'function') {
                    saveExchangeRate(rate);
                } else {
                    window.currentExchangeRate = rate;
                    localStorage.setItem(CUSTOMERS_CONFIG.exchangeRate.localStorageKey, rate);
                }
                
                window.customersIndex.updateBsValues(rate);
                
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
        
        // Guardar automáticamente cuando se cambie el valor en el input principal
        $(document).on('input', '#exchangeRate', function() {
            const rate = parseFloat($(this).val());
            if (rate > 0) {
                // Guardar automáticamente en localStorage
                if (typeof saveExchangeRate === 'function') {
                    saveExchangeRate(rate);
                } else {
                    window.currentExchangeRate = rate;
                    localStorage.setItem(CUSTOMERS_CONFIG.exchangeRate.localStorageKey, rate);
                }
                
                // Actualizar valores en Bs en tiempo real
                window.customersIndex.updateBsValues(rate);
            }
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
                        const hasWarningIcon = $(row).find('.debt-warning-badge').length > 0;
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
                    const customerEmail = $card.find('.customer-email').text().toLowerCase();
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
    });
}

// ===== FUNCIONES DE UTILIDAD =====
function loadDataTables(callback) {
    if (typeof $.fn.DataTable !== 'undefined') {
        callback();
        return;
    }

    // Cargar DataTables dinámicamente
    const script = document.createElement('script');
    script.src = '/vendor/datatables/datatables.min.js';
    script.onload = function() {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = '/vendor/datatables/datatables.min.css';
        document.head.appendChild(link);
        callback();
    };
    script.onerror = function() {
        console.warn('DataTables no se pudo cargar');
        callback();
    };
    document.head.appendChild(script);
}

function loadSweetAlert2(callback) {
    if (typeof Swal !== 'undefined') {
        callback();
        return;
    }

    // Cargar SweetAlert2 dinámicamente si no está disponible
    const script = document.createElement('script');
    script.src = '/vendor/sweetalert2/sweetalert2.min.js';
    script.onload = function() {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = '/vendor/sweetalert2/sweetalert2.min.css';
        document.head.appendChild(link);
        callback();
    };
    script.onerror = function() {
        console.warn('SweetAlert2 no se pudo cargar, usando alertas nativas');
        callback();
    };
    document.head.appendChild(script);
}

// ===== INICIALIZAR CUANDO EL DOM ESTÉ LISTO =====
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar SweetAlert2 si está disponible
        loadSweetAlert2(function() {
            initializeCustomersIndex();
        });
    });
} else {
    // Si el DOM ya está listo
    loadSweetAlert2(function() {
        initializeCustomersIndex();
    });
}

// ===== FUNCIONES DE EXCHANGE RATE =====

// Variable global para almacenar el tipo de cambio actual
let currentExchangeRate = 134.0; // Valor por defecto

// Función centralizada para manejar la persistencia del tipo de cambio
function initializeExchangeRate() {
    // Cargar el tipo de cambio guardado en localStorage
    const savedRate = localStorage.getItem('exchangeRate');
    if (savedRate) {
        currentExchangeRate = parseFloat(savedRate);
    } else {
        // Si no hay valor guardado, usar el valor por defecto del input
        const exchangeRateInput = document.getElementById('exchangeRate');
        if (exchangeRateInput && exchangeRateInput.value) {
            currentExchangeRate = parseFloat(exchangeRateInput.value);
        } else {
            currentExchangeRate = 134.0; // Valor por defecto
        }
        
        // Guardar el valor por defecto en localStorage
        localStorage.setItem('exchangeRate', currentExchangeRate.toString());
    }
    
    // Actualizar el input principal
    const exchangeRateInput = document.getElementById('exchangeRate');
    if (exchangeRateInput) {
        exchangeRateInput.value = currentExchangeRate;
    }
    
    // Sincronizar todos los elementos
    if (typeof syncAllExchangeRateElements === 'function') {
        syncAllExchangeRateElements(currentExchangeRate);
    } else {
        // Fallback: solo actualizar valores en Bs
        window.customersIndex.updateBsValues(currentExchangeRate);
    }
    
    return currentExchangeRate;
}

// Función para guardar el tipo de cambio
function saveExchangeRate(rate) {
    if (rate > 0) {
        currentExchangeRate = rate;
        localStorage.setItem('exchangeRate', rate.toString());
        return true;
    }
    return false;
}

// Función para sincronizar todos los elementos con el tipo de cambio
function syncAllExchangeRateElements(rate) {
    // Sincronizar input principal
    const exchangeRateInput = document.getElementById('exchangeRate');
    if (exchangeRateInput) {
        exchangeRateInput.value = rate;
    }
    
    // Sincronizar modal si está abierto
    const modalExchangeRateInput = document.getElementById('modalExchangeRate');
    if (modalExchangeRateInput) {
        modalExchangeRateInput.value = rate;
        // Trigger evento para actualizar valores en Bs
        modalExchangeRateInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    // Sincronizar widget de Alpine.js
    const widgetElements = document.querySelectorAll('[x-data*="exchangeRateWidget"]');
    widgetElements.forEach(element => {
        if (element._x_dataStack && element._x_dataStack[0]) {
            const widget = element._x_dataStack[0];
            if (widget.syncFromModal) {
                widget.syncFromModal(rate);
            }
        }
    });
    
    // Actualizar valores en Bs
    window.customersIndex.updateBsValues(rate);
}

// ===== FUNCIONES DE ALPINE.JS =====

// Función para filtros de exchange rate
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
                window.customersIndex.updateBsValues(this.exchangeRate);
                
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
        }
    }
}

// ===== FUNCIONES DE UTILIDAD ADICIONALES =====

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

// ===== EXPONER FUNCIONES GLOBALMENTE =====
window.initializeExchangeRate = initializeExchangeRate;
window.saveExchangeRate = saveExchangeRate;
window.syncAllExchangeRateElements = syncAllExchangeRateElements;
window.exchangeFilters = exchangeFilters;
window.showNotification = showNotification;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.formatDateTime = formatDateTime;
