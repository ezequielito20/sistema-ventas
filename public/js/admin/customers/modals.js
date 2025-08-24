// ===== GESTIÓN DE MODALES =====
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
            
            // Reiniciar filtros si se cierra el modal de reporte de deudas
            if (modalName === 'debtReportModal') {
                this.resetDebtReportFilters();
            }
        },
        
        closeAllModals() {
            this.showCustomerModal = false;
            this.debtReportModal = false;
            this.debtPaymentModal = false;
            document.body.style.overflow = 'auto';
            
            // Reiniciar filtros del reporte de deudas al cerrar
            this.resetDebtReportFilters();
        },
        
        // Función para abrir el modal de reporte de deudas
        openDebtReport() {
            this.debtReportModal = true;
            document.body.style.overflow = 'hidden';
            this.loadDebtReport();
        },
        
        // Función para reiniciar filtros del reporte de deudas
        resetDebtReportFilters() {
            const searchFilter = document.getElementById('searchFilter');
            const orderFilter = document.getElementById('orderFilter');
            const debtTypeFilter = document.getElementById('debtTypeFilter');
            
            // Establecer valores por defecto
            if (searchFilter) searchFilter.value = '';
            if (orderFilter) orderFilter.value = 'debt_desc';
            if (debtTypeFilter) debtTypeFilter.value = '';
            
            // Limpiar filtros guardados en localStorage
            localStorage.removeItem('debtReportFilters');
            
            // Aplicar filtros (mostrar todos los resultados)
            if (typeof applyFilters === 'function') {
                applyFilters();
            }
        },
        
        loadCustomerDetails(customerId) {
            // Cargar datos del cliente y su historial de ventas
            Promise.all([
                fetch(`/customers/${customerId}?customer_details=1`),
                fetch(`/admin/customers/${customerId}/sales-history`)
            ])
            .then(responses => Promise.all(responses.map(r => r.json())))
            .then(([customerData, salesData]) => {
                
                if (customerData.success) {
                    // Llenar información del cliente
                    const customerNameElement = document.getElementById('customerName');
                    const customerNameField = document.getElementById('customer_name_details');
                    const customerPhoneField = document.getElementById('customer_phone_details');
                    const customerStatusElement = document.getElementById('customer_status_details');
                    
                    if (customerNameElement) customerNameElement.textContent = customerData.customer.name;
                    if (customerNameField) customerNameField.value = customerData.customer.name;
                    if (customerPhoneField) customerPhoneField.value = customerData.customer.phone || 'No disponible';
                    
                    // Actualizar estado del cliente
                    if (customerStatusElement) {
                        if (customerData.customer.is_defaulter) {
                            customerStatusElement.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
                            customerStatusElement.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Moroso';
                        } else {
                            customerStatusElement.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                            customerStatusElement.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Actual';
                        }
                    }
                    
                    // Usar datos de ventas del endpoint correcto
                    if (salesData.success) {
                        window.customerSalesData = salesData.sales || [];
                    } else {
                        window.customerSalesData = [];
                    }
                    
                    // Cargar historial de ventas
                    this.loadSalesHistory();
                    
                    // Inicializar filtros
                    this.initializeCustomerDetailsFilters();
                    
                    // Limpiar filtros guardados al abrir el modal
                    this.clearSavedFiltersOnOpen();
                }
            })
            .catch(error => {
                // Error cargando detalles del cliente
            });
        },
        
        loadDebtPaymentData(customerId) {
            // Verificar que el modal esté abierto
            if (!this.debtPaymentModal) {
                this.debtPaymentModal = true;
            }
            
            // Cargar datos para el modal de pago de deuda
            fetch(`/customers/${customerId}?debt_payment_data=1`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Esperar un momento para que el modal esté completamente cargado
                        setTimeout(() => {
                            // Llenar los campos del modal
                            const customerIdField = document.getElementById('payment_customer_id');
                            const customerNameField = document.getElementById('customer_name');
                            const customerPhoneField = document.getElementById('customer_phone');
                            
                            if (customerIdField) customerIdField.value = data.customer_id;
                            if (customerNameField) customerNameField.value = data.customer_name;
                            if (customerPhoneField) customerPhoneField.value = data.customer_phone || 'No disponible';
                            
                            // Actualizar estado del cliente
                            const statusElement = document.getElementById('customer_status');
                            if (statusElement) {
                                if (data.is_defaulter) {
                                    statusElement.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
                                    statusElement.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Moroso';
                                } else {
                                    statusElement.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                                    statusElement.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Actual';
                                }
                            }
                            
                            // Guardar la deuda actual para cálculos
                            window.currentCustomerDebt = parseFloat(data.current_debt.replace(/,/g, ''));
                            
                            const currentDebtField = document.getElementById('current_debt');
                            const remainingDebtField = document.getElementById('remaining_debt');
                            
                            if (currentDebtField) {
                                const currentDebtSpan = currentDebtField.querySelector('span');
                                if (currentDebtSpan) currentDebtSpan.textContent = `$${data.current_debt}`;
                            }
                            if (remainingDebtField) {
                                const remainingDebtSpan = remainingDebtField.querySelector('span');
                                if (remainingDebtSpan) remainingDebtSpan.textContent = `$${data.current_debt}`;
                            }
                            
                            // Establecer fecha y hora actual en zona horaria local (UTC-4)
                            const now = new Date();
                            const today = now.toLocaleDateString('en-CA'); // Formato YYYY-MM-DD
                            const timeString = now.toLocaleTimeString('en-US', { 
                                hour12: false, 
                                hour: '2-digit', 
                                minute: '2-digit' 
                            });
                            
                            const paymentDateField = document.getElementById('payment_date');
                            const paymentTimeField = document.getElementById('payment_time');
                            const paymentAmountField = document.getElementById('payment_amount');
                            
                            if (paymentDateField) paymentDateField.value = today;
                            if (paymentTimeField) paymentTimeField.value = timeString;
                            if (paymentAmountField) paymentAmountField.value = '';
                            
                            // Inicializar eventos del modal de pago
                            this.initializeDebtPaymentEvents();
                        }, 100); // Pequeño delay para asegurar que el modal esté cargado
                    }
                })
                .catch(error => {
                    // Error cargando datos de pago de deuda
                });
        },

        loadSalesHistory() {
            if (!window.customerSalesData) return;
            
            const tableBody = document.getElementById('salesHistoryTable');
            const salesCountElement = document.getElementById('salesCount');
            
            if (tableBody && salesCountElement) {
                if (window.customerSalesData.length === 0) {
                    tableBody.innerHTML = `
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
                    salesCountElement.textContent = '0';
                } else {
                    const rows = window.customerSalesData.map(sale => {
                        // Usar directamente los datos del backend
                        const date = sale.date || 'Fecha no disponible';
                        const products = sale.products || 'Sin productos';
                        const total = sale.total || 0;
                        
                        const rowHtml = `
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">
                                    ${date}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 border-b border-gray-100 products-cell">
                                    ${products}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-green-600 border-b border-gray-100">
                                    $ ${parseFloat(total).toFixed(2)}
                                </td>
                            </tr>
                        `;
                        
                        return rowHtml;
                    }).join('');
                    
                    tableBody.innerHTML = rows;
                    salesCountElement.textContent = window.customerSalesData.length;
                }
            }
        },

        initializeCustomerDetailsFilters() {
            // Event listeners para filtros en tiempo real
            const dateFromInput = document.getElementById('dateFrom');
            const dateToInput = document.getElementById('dateTo');
            const amountFromInput = document.getElementById('amountFrom');
            const amountToInput = document.getElementById('amountTo');
            const clearFiltersBtn = document.getElementById('clearFilters');
            
            // Filtros en tiempo real con debounce
            let filterTimeout;
            const applyFilters = () => {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    this.filterSalesHistory();
                }, 300);
            };
            
            if (dateFromInput) dateFromInput.addEventListener('input', applyFilters);
            if (dateToInput) dateToInput.addEventListener('input', applyFilters);
            if (amountFromInput) amountFromInput.addEventListener('input', applyFilters);
            if (amountToInput) amountToInput.addEventListener('input', applyFilters);
            
            // Botón limpiar filtros
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', () => {
                    this.clearCustomerFilters();
                });
            }
        },

        filterSalesHistory() {
            if (!window.customerSalesData) return;
            
            const dateFrom = document.getElementById('dateFrom')?.value;
            const dateTo = document.getElementById('dateTo')?.value;
            const amountFrom = parseFloat(document.getElementById('amountFrom')?.value) || 0;
            const amountTo = parseFloat(document.getElementById('amountTo')?.value) || Infinity;
            
            // Guardar filtros actuales
            this.saveCustomerFilters({ dateFrom, dateTo, amountFrom, amountTo });
            
            // Filtrar datos
            let filteredData = window.customerSalesData.filter(sale => {
                // Convertir fecha del formato dd/mm/yyyy a objeto Date
                let saleDate;
                if (sale.date && sale.date.includes('/')) {
                    const [day, month, year] = sale.date.split('/');
                    saleDate = new Date(year, month - 1, day); // month - 1 porque los meses van de 0-11
                } else if (sale.created_at) {
                    saleDate = new Date(sale.created_at);
                } else {
                    saleDate = new Date();
                }
                
                const saleAmount = parseFloat(sale.total || 0);
                
                // Filtro de fecha
                if (dateFrom && saleDate < new Date(dateFrom)) return false;
                if (dateTo && saleDate > new Date(dateTo + 'T23:59:59')) return false;
                
                // Filtro de monto
                if (saleAmount < amountFrom) return false;
                if (amountTo !== Infinity && saleAmount > amountTo) return false;
                
                return true;
            });
            
            // Actualizar tabla
            this.updateSalesTable(filteredData);
        },

        updateSalesTable(filteredData) {
            const tableBody = document.getElementById('salesHistoryTable');
            const salesCountElement = document.getElementById('salesCount');
            
            if (tableBody && salesCountElement) {
                if (filteredData.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="3" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center space-y-3">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-search text-2xl text-gray-400"></i>
                                    </div>
                                    <p class="text-gray-500">No se encontraron ventas con los filtros aplicados</p>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    const rows = filteredData.map(sale => {
                        // Usar directamente los datos del backend
                        const date = sale.date || 'Fecha no disponible';
                        const products = sale.products || 'Sin productos';
                        const total = sale.total || 0;
                        
                        return `
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">
                                    ${date}
                                </td>
                            <td class="px-4 py-3 text-sm text-gray-600 border-b border-gray-100 products-cell">
                                ${products}
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-green-600 border-b border-gray-100">
                                $ ${parseFloat(total).toFixed(2)}
                            </td>
                        </tr>
                    `;
                    }).join('');
                    
                    tableBody.innerHTML = rows;
                }
                
                salesCountElement.textContent = filteredData.length;
            }
        },

        saveCustomerFilters(filters) {
            localStorage.setItem('customerDetailsFilters', JSON.stringify(filters));
        },

        loadSavedCustomerFilters() {
            const savedFilters = localStorage.getItem('customerDetailsFilters');
            if (savedFilters) {
                const filters = JSON.parse(savedFilters);
                
                const dateFromInput = document.getElementById('dateFrom');
                const dateToInput = document.getElementById('dateTo');
                const amountFromInput = document.getElementById('amountFrom');
                const amountToInput = document.getElementById('amountTo');
                
                if (dateFromInput && filters.dateFrom) dateFromInput.value = filters.dateFrom;
                if (dateToInput && filters.dateTo) dateToInput.value = filters.dateTo;
                if (amountFromInput && filters.amountFrom) amountFromInput.value = filters.amountFrom;
                if (amountToInput && filters.amountTo) amountToInput.value = filters.amountTo;
            }
        },

        clearSavedFiltersOnOpen() {
            // Limpiar filtros guardados
            localStorage.removeItem('customerDetailsFilters');
            
            // Limpiar campos de filtros
            const dateFromInput = document.getElementById('dateFrom');
            const dateToInput = document.getElementById('dateTo');
            const amountFromInput = document.getElementById('amountFrom');
            const amountToInput = document.getElementById('amountTo');
            
            if (dateFromInput) dateFromInput.value = '';
            if (dateToInput) dateToInput.value = '';
            if (amountFromInput) amountFromInput.value = '';
            if (amountToInput) amountToInput.value = '';
        },

        clearCustomerFilters() {
            const dateFromInput = document.getElementById('dateFrom');
            const dateToInput = document.getElementById('dateTo');
            const amountFromInput = document.getElementById('amountFrom');
            const amountToInput = document.getElementById('amountTo');
            
            if (dateFromInput) dateFromInput.value = '';
            if (dateToInput) dateToInput.value = '';
            if (amountFromInput) amountFromInput.value = '';
            if (amountToInput) amountToInput.value = '';
            
            // Limpiar filtros guardados
            localStorage.removeItem('customerDetailsFilters');
            
            // Recargar datos originales
            if (window.customerSalesData) {
                this.updateSalesTable(window.customerSalesData);
            }
        },

        initializeDebtPaymentEvents() {
            // Botón para pagar deuda completa
            const maxPaymentBtn = document.getElementById('max_payment_btn');
            if (maxPaymentBtn) {
                maxPaymentBtn.addEventListener('click', () => {
                    if (window.currentCustomerDebt) {
                        document.getElementById('payment_amount').value = window.currentCustomerDebt.toFixed(2);
                        this.calculateRemainingDebt();
                    }
                });
            }

            // Event listener para calcular deuda restante en tiempo real
            const paymentAmountInput = document.getElementById('payment_amount');
            if (paymentAmountInput) {
                paymentAmountInput.addEventListener('input', () => {
                    this.calculateRemainingDebt();
                });
            }

            // Event listener para validar fecha
            const paymentDateInput = document.getElementById('payment_date');
            if (paymentDateInput) {
                paymentDateInput.addEventListener('change', () => {
                    this.validatePaymentDate();
                });
            }

            // Event listener para el formulario
            const debtPaymentForm = document.getElementById('debtPaymentForm');
            if (debtPaymentForm) {
                debtPaymentForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.submitDebtPayment();
                });
            }
        },

        calculateRemainingDebt() {
            const paymentAmount = parseFloat(document.getElementById('payment_amount').value) || 0;
            const currentDebt = window.currentCustomerDebt || 0;
            
            if (paymentAmount > currentDebt) {
                // Mostrar error y ajustar el valor
                document.getElementById('payment_amount').value = currentDebt.toFixed(2);
                this.showPaymentError('El monto no puede ser mayor a la deuda actual');
                return;
            }
            
            const remainingDebt = currentDebt - paymentAmount;
            const remainingDebtField = document.getElementById('remaining_debt');
            if (remainingDebtField) {
                const remainingDebtSpan = remainingDebtField.querySelector('span');
                if (remainingDebtSpan) remainingDebtSpan.textContent = `$${remainingDebt.toFixed(2)}`;
            }
        },

        validatePaymentDate() {
            const paymentDate = new Date(document.getElementById('payment_date').value);
            const today = new Date();
            today.setHours(23, 59, 59, 999); // Fin del día actual
            
            if (paymentDate > today) {
                this.showPaymentError('La fecha del pago no puede ser mayor a hoy');
                // Usar zona horaria local para la fecha
                const todayLocal = today.toLocaleDateString('en-CA'); // Formato YYYY-MM-DD
                document.getElementById('payment_date').value = todayLocal;
            }
        },

        showPaymentError(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validación',
                    text: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        },

        submitDebtPayment() {
            const customerId = document.getElementById('payment_customer_id').value;
            const paymentAmount = parseFloat(document.getElementById('payment_amount').value);
            const paymentDate = document.getElementById('payment_date').value;
            const paymentTime = document.getElementById('payment_time').value;
            const notes = document.getElementById('payment_notes').value;

            // Validaciones
            if (!paymentAmount || paymentAmount <= 0) {
                this.showPaymentError('El monto del pago es obligatorio y debe ser mayor a 0');
                return;
            }

            if (paymentAmount > window.currentCustomerDebt) {
                this.showPaymentError('El monto no puede ser mayor a la deuda actual');
                return;
            }

            // Confirmar antes de enviar
            Swal.fire({
                title: '¿Confirmar pago?',
                text: `¿Estás seguro de que deseas registrar un pago de $${paymentAmount.toFixed(2)}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, registrar pago',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.processDebtPayment(customerId, paymentAmount, paymentDate, paymentTime, notes);
                }
            });
        },

        processDebtPayment(customerId, paymentAmount, paymentDate, paymentTime, notes) {
            // Mostrar loading
            Swal.fire({
                title: 'Procesando pago...',
                html: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('payment_amount', paymentAmount);
            formData.append('payment_date', paymentDate);
            formData.append('payment_time', paymentTime);
            formData.append('notes', notes);

            fetch(`/admin/customers/${customerId}/register-payment`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cerrar modal
                    this.closeModal('debtPaymentModal');
                    
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Pago registrado!',
                        text: data.message,
                        timer: 3000,
                        showConfirmButton: false
                    });

                    // Actualizar la deuda en todas las vistas
                    this.updateDebtInViews(customerId, data.new_debt, data.formatted_new_debt);
                    
                    // Recargar la página para actualizar estadísticas
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al registrar el pago'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al registrar el pago. Por favor, inténtelo de nuevo.',
                    confirmButtonText: 'Entendido'
                });
            });
        },

        updateDebtInViews(customerId, newDebt, formattedNewDebt) {
            // Actualizar en tabla y tarjetas
            const debtElements = document.querySelectorAll(`[data-customer-id="${customerId}"] .debt-value`);
            debtElements.forEach(element => {
                element.textContent = formattedNewDebt;
            });
        },
        
        loadDebtReport() {
            // Cargar el reporte de deudas
            const modalBody = document.querySelector('#debtReportModal .modal-body');
            if (!modalBody) return;
            
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
            const url = '/admin/customers/debt-report';
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.text();
            })
            .then(html => {
                // Crear un DOM temporal para extraer solo el contenido del modal
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Buscar el contenido del modal en el HTML
                const modalContentFromResponse = doc.querySelector('.modal-content') || 
                                               doc.querySelector('#debtReportModal') ||
                                               doc.querySelector('.debt-modal-body') ||
                                               doc.body;
                
                // Actualizar el contenido del modal
                const modalContent = document.querySelector('#debtReportModal .modal-content');
                if (modalContent && modalContentFromResponse) {
                    modalContent.innerHTML = modalContentFromResponse.innerHTML;
                    
                    // Inicializar event listeners después de cargar el contenido
                    this.initializeDebtReportEvents();
                    
                    // Reiniciar filtros a valores por defecto
                    this.resetDebtReportFilters();
                    
                    // Sincronizar con el valor guardado después de un breve delay
                    setTimeout(() => {
                        const savedRate = localStorage.getItem('exchangeRate');
                        if (savedRate) {
                            const rate = parseFloat(savedRate);
                            this.syncAllExchangeRateElements(rate);
                        }
                    }, 200);
                }
            })
            .catch(error => {
                // Error cargando reporte de deudas
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
            // Obtener el tipo de cambio actual usando la función centralizada
            let currentRate;
            if (typeof initializeExchangeRate === 'function') {
                currentRate = initializeExchangeRate();
            } else {
                // Fallback: cargar desde localStorage directamente
                const savedRate = localStorage.getItem('exchangeRate');
                if (savedRate) {
                    currentRate = parseFloat(savedRate);
                } else {
                    currentRate = document.getElementById('exchangeRate')?.value || 134;
                }
            }
            
            // Establecer el valor inicial en el modal
            const modalExchangeRateInput = document.getElementById('modalExchangeRate');
            if (modalExchangeRateInput) {
                modalExchangeRateInput.value = currentRate;
                
                // Trigger el evento input para actualizar los valores en Bs inmediatamente
                setTimeout(() => {
                    modalExchangeRateInput.dispatchEvent(new Event('input', { bubbles: true }));
                }, 100);
            }
            
            // Event listener para el input del tipo de cambio
            const exchangeRateInput = document.getElementById('modalExchangeRate');
            if (exchangeRateInput) {
                exchangeRateInput.addEventListener('input', (e) => {
                    const rate = parseFloat(e.target.value);
                    
                    if (rate > 0) {
                        this.updateModalBsValues(rate);
                        
                        // Sincronizar con el widget de Alpine.js
                        const widgetElements = document.querySelectorAll('[x-data*="exchangeRateWidget"]');
                        widgetElements.forEach(element => {
                            if (element._x_dataStack && element._x_dataStack[0]) {
                                const widget = element._x_dataStack[0];
                                if (widget.syncFromModal) {
                                    widget.syncFromModal(rate);
                                }
                            }
                        });
                    }
                });
            }
            
            // Event listener para el botón de actualizar
            const updateBtn = document.getElementById('updateModalExchangeRate');
            if (updateBtn) {
                updateBtn.addEventListener('click', () => {
                    const rate = parseFloat(document.getElementById('modalExchangeRate').value);
                    if (rate > 0) {
                        // Usar la función centralizada para guardar
                        if (typeof saveExchangeRate === 'function') {
                            saveExchangeRate(rate);
                        } else {
                            // Fallback si la función no está disponible
                            window.currentExchangeRate = rate;
                            localStorage.setItem('exchangeRate', rate);
                        }
                        
                        // Sincronizar con el widget de Alpine.js
                        const widgetElements = document.querySelectorAll('[x-data*="exchangeRateWidget"]');
                        widgetElements.forEach(element => {
                            if (element._x_dataStack && element._x_dataStack[0]) {
                                const widget = element._x_dataStack[0];
                                if (widget.syncFromModal) {
                                    widget.syncFromModal(rate);
                                }
                            }
                        });
                        
                        // Actualizar valores en Bs en el modal
                        this.updateModalBsValues(rate);
                        
                        // Actualizar valores en Bs en la tabla principal
                        if (typeof window.customersIndex !== 'undefined' && window.customersIndex.updateBsValues) {
                            window.customersIndex.updateBsValues(rate);
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
            }
            
            // Actualizar valores iniciales
            this.updateModalBsValues(currentRate);
            
            // Agregar event listener al input del modal para sincronización en tiempo real
            const modalInput = document.getElementById('modalExchangeRate');
            if (modalInput) {
                modalInput.addEventListener('input', (e) => {
                    const rate = parseFloat(e.target.value);
                    if (!isNaN(rate) && rate > 0) {
                        // Sincronizar con el widget principal
                        const widgetElements = document.querySelectorAll('[x-data*="exchangeRateWidget"]');
                        widgetElements.forEach(element => {
                            if (element._x_dataStack && element._x_dataStack[0]) {
                                const widget = element._x_dataStack[0];
                                if (widget.syncFromModal) {
                                    widget.syncFromModal(rate);
                                }
                            }
                        });
                        
                        // Actualizar valores en Bs en tiempo real
                        this.updateModalBsValues(rate);
                        if (typeof window.customersIndex !== 'undefined' && window.customersIndex.updateBsValues) {
                            window.customersIndex.updateBsValues(rate);
                        }
                    }
                });
            }
            
            // Inicializar filtros del modal
            this.initializeModalFilters();
        },
        
        updateModalBsValues(rate) {
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
                        }
                    }
                });
            }
        },

        // ===== FUNCIONALIDAD DE FILTROS DEL MODAL =====
        initializeModalFilters() {
            // Cargar filtros guardados desde localStorage
            this.loadSavedFilters();
            
            // Event listeners para filtros en tiempo real
            const searchFilter = document.getElementById('searchFilter');
            const orderFilter = document.getElementById('orderFilter');
            const debtTypeFilter = document.getElementById('debtTypeFilter');
            const clearFiltersBtn = document.getElementById('clearFiltersBtn');
            
            if (searchFilter) {
                let searchTimeout;
                searchFilter.addEventListener('input', (e) => {
                    this.saveFilter('search', e.target.value);
                    
                    // Debounce para evitar demasiadas peticiones
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        this.applyFilters();
                    }, 500); // Esperar 500ms después de que el usuario deje de escribir
                });
            }
            
            if (orderFilter) {
                orderFilter.addEventListener('change', (e) => {
                    this.saveFilter('order', e.target.value);
                    this.applyFilters();
                });
            }
            
            if (debtTypeFilter) {
                debtTypeFilter.addEventListener('change', (e) => {
                    this.saveFilter('debtType', e.target.value);
                    this.applyFilters();
                });
            }
            
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', () => {
                    this.clearAllFilters();
                });
            }
            
            // Event listener para el botón de descarga PDF
            const downloadPdfBtn = document.getElementById('downloadPdfBtn');
            if (downloadPdfBtn) {
                downloadPdfBtn.addEventListener('click', () => {
                    this.downloadPdfWithFilters();
                });
            }
        },

        saveFilter(key, value) {
            const filters = JSON.parse(localStorage.getItem('debtReportFilters') || '{}');
            filters[key] = value;
            localStorage.setItem('debtReportFilters', JSON.stringify(filters));
        },

        loadSavedFilters() {
            const filters = JSON.parse(localStorage.getItem('debtReportFilters') || '{}');
            
            const searchFilter = document.getElementById('searchFilter');
            const orderFilter = document.getElementById('orderFilter');
            const debtTypeFilter = document.getElementById('debtTypeFilter');
            
            if (searchFilter && filters.search) {
                searchFilter.value = filters.search;
            }
            
            if (orderFilter && filters.order) {
                orderFilter.value = filters.order;
            }
            
            if (debtTypeFilter && filters.debtType) {
                debtTypeFilter.value = filters.debtType;
            }
        },

        clearAllFilters() {
            // Limpiar localStorage
            localStorage.removeItem('debtReportFilters');
            
            // Limpiar inputs
            const searchFilter = document.getElementById('searchFilter');
            const orderFilter = document.getElementById('orderFilter');
            const debtTypeFilter = document.getElementById('debtTypeFilter');
            
            if (searchFilter) searchFilter.value = '';
            if (orderFilter) orderFilter.value = 'debt_desc';
            if (debtTypeFilter) debtTypeFilter.value = '';
            
            // Recargar datos sin filtros
            this.applyFilters();
        },

        applyFilters() {
            // Obtener valores de filtros
            const searchTerm = document.getElementById('searchFilter')?.value || '';
            const orderBy = document.getElementById('orderFilter')?.value || 'debt_desc';
            const debtType = document.getElementById('debtTypeFilter')?.value || '';
            const exchangeRate = document.getElementById('modalExchangeRate')?.value || 134;
            
            // Mostrar loading
            this.showFilterLoading();
            
            // Construir URL con filtros
            const url = new URL('/admin/customers/debt-report', window.location.origin);
            url.searchParams.set('ajax', '1');
            url.searchParams.set('exchange_rate', exchangeRate);
            
            if (searchTerm) url.searchParams.set('search', searchTerm);
            if (orderBy) url.searchParams.set('order', orderBy);
            if (debtType) url.searchParams.set('debt_type', debtType);
            
            // Hacer petición AJAX
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.text();
            })
            .then(html => {
                // Actualizar contenido del modal
                const modalContent = document.querySelector('#debtReportModal .modal-content');
                if (modalContent) {
                    modalContent.innerHTML = html;
                    
                    // Reinicializar eventos después de actualizar contenido
                    this.initializeDebtReportEvents();
                    this.initializeModalFilters();
                }
            })
            .catch(error => {
                // Error al aplicar filtros
                this.hideFilterLoading();
                
                // Mostrar mensaje de error
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al aplicar filtros',
                        text: 'No se pudieron aplicar los filtros. Inténtalo de nuevo.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
        },

        showFilterLoading() {
            const tableBody = document.querySelector('#debtReportModal tbody');
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-8 h-8 border-2 border-gray-200 border-t-blue-500 rounded-full animate-spin mb-2"></div>
                                <span class="text-sm text-gray-600">Aplicando filtros...</span>
                            </div>
                        </td>
                    </tr>
                `;
            }
        },

        hideFilterLoading() {
            // El loading se oculta automáticamente cuando se actualiza el contenido
        },

        // Función para obtener filtros actuales (usada para PDF)
        getCurrentFilters() {
            const searchTerm = document.getElementById('searchFilter')?.value || '';
            const orderBy = document.getElementById('orderFilter')?.value || 'debt_desc';
            const debtType = document.getElementById('debtTypeFilter')?.value || '';
            const exchangeRate = document.getElementById('modalExchangeRate')?.value || 134;
            
            return {
                search: searchTerm,
                order: orderBy,
                debt_type: debtType,
                exchange_rate: exchangeRate
            };
        },

        downloadPdfWithFilters() {
            // Obtener filtros actuales
            const filters = this.getCurrentFilters();
            
            // Construir URL con filtros
            const url = new URL('/admin/customers/debt-report', window.location.origin);
            
            // Agregar parámetros de filtros
            Object.keys(filters).forEach(key => {
                if (filters[key]) {
                    url.searchParams.set(key, filters[key]);
                }
            });
            
            // Abrir PDF en nueva pestaña
            window.open(url.toString(), '_blank');
        },

        // Función para sincronizar todos los elementos con el tipo de cambio
        syncAllExchangeRateElements(rate) {
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
            if (typeof window.customersIndex !== 'undefined' && window.customersIndex.updateBsValues) {
                window.customersIndex.updateBsValues(rate);
            }
        }
    }
}
