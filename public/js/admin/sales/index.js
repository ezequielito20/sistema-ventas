/**
 * JavaScript optimizado para la vista de ventas
 * Archivo: public/js/admin/sales/index.js
 * Versión: 1.0.0
 */

$(document).ready(function() {
    // Variable global para la tabla
    let table;
    // Variable para controlar peticiones AJAX en progreso
    let ajaxInProgress = false;

    // ===== FUNCIONES DE PAGINACIÓN =====
    
    /**
     * Función optimizada para crear paginación moderna
     */
    function createModernPagination() {
        if (!table) return;

        const info = table.page.info();
        const totalPages = info.pages;
        const currentPage = info.page + 1;
        const totalRecords = info.recordsTotal;
        const startRecord = info.start + 1;
        const endRecord = info.end;

        // Solo mostrar paginación si hay más de una página
        if (totalPages <= 1) {
            $('.modern-pagination-container').html(`
                <div class="modern-pagination-wrapper">
                    <div class="pagination-info">
                        <div class="records-info">
                            <span class="records-text">Mostrando</span>
                            <span class="records-numbers">${startRecord} - ${endRecord}</span>
                            <span class="records-text">de</span>
                            <span class="records-total">${totalRecords}</span>
                            <span class="records-text">registros</span>
                        </div>
                    </div>
                </div>
            `);
            return;
        }

        // Usar template strings más eficientes
        const paginationHTML = `
            <div class="modern-pagination-wrapper">
                <div class="pagination-info">
                    <div class="records-info">
                        <span class="records-text">Mostrando</span>
                        <span class="records-numbers">${startRecord} - ${endRecord}</span>
                        <span class="records-text">de</span>
                        <span class="records-total">${totalRecords}</span>
                        <span class="records-text">registros</span>
                    </div>
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn pagination-prev ${currentPage === 1 ? 'disabled' : ''}" 
                            data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-left"></i>
                        <span>Anterior</span>
                    </button>
                    
                    <div class="pagination-numbers">
                        ${generatePageNumbers(currentPage, totalPages)}
                    </div>
                    
                    <button class="pagination-btn pagination-next ${currentPage === totalPages ? 'disabled' : ''}" 
                            data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>
                        <span>Siguiente</span>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <div class="pagination-options">
                    <div class="page-size-selector">
                        <label for="pageSize">Mostrar:</label>
                        <select id="pageSize" class="page-size-select">
                            <option value="5" ${info.length === 5 ? 'selected' : ''}>5</option>
                            <option value="10" ${info.length === 10 ? 'selected' : ''}>10</option>
                            <option value="25" ${info.length === 25 ? 'selected' : ''}>25</option>
                            <option value="50" ${info.length === 50 ? 'selected' : ''}>50</option>
                            <option value="100" ${info.length === 100 ? 'selected' : ''}>100</option>
                        </select>
                    </div>
                </div>
            </div>
        `;

        $('.modern-pagination-container').html(paginationHTML);
    }

    /**
     * Función auxiliar optimizada para generar números de página
     */
    function generatePageNumbers(currentPage, totalPages) {
        let html = '';
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            html += `<button class="pagination-number" data-page="1">1</button>`;
            if (startPage > 2) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="pagination-number ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
            html += `<button class="pagination-number" data-page="${totalPages}">${totalPages}</button>`;
        }

        return html;
    }

    // ===== INICIALIZACIÓN DE DATATABLE =====
    
    /**
     * Inicializar DataTable con configuración optimizada
     */
    // Cargar DataTables dinámicamente
    loadDataTables(function() {
        table = $('#salesTable').DataTable({
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
        serverSide: false,
        drawCallback: function() {
            createModernPagination();
        }
    });

    // Forzar la creación de paginación después de la inicialización
    setTimeout(createModernPagination, 500);
    });

    // ===== EVENTOS DE PAGINACIÓN =====
    
    // Manejar clicks en paginación
    $(document).on('click', '.pagination-number, .pagination-prev, .pagination-next', function() {
        if (!$(this).hasClass('disabled')) {
            const page = parseInt($(this).data('page')) - 1;
            table.page(page).draw('page');
        }
    });

    // Manejar cambio de tamaño de página
    $(document).on('change', '#pageSize', function() {
        const pageSize = parseInt($(this).val());
        table.page.len(pageSize).draw();
    });

    // ===== FUNCIONES DE VISTA =====
    
    /**
     * Función para paginar tarjetas en vista de cards
     */
    function paginateCards() {
        const currentView = $('.view-toggle.active').data('view');
        if (currentView === 'cards') {
            const info = table.page.info();
            const startIndex = info.start;
            const endIndex = info.end;

            $('.modern-sale-card').each(function(index) {
                if (index >= startIndex && index < endIndex) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }

    // Llamar paginateCards cuando se cambie de página
    $(document).on('click', '.pagination-number, .pagination-prev, .pagination-next', function() {
        setTimeout(paginateCards, 100);
    });

    // Alternar entre vistas
    $('.view-toggle').click(function() {
        const view = $(this).data('view');

        $('.view-toggle').removeClass('active');
        $(this).addClass('active');

        if (view === 'table') {
            $('#tableView').show();
            $('#cardsView').hide();
        } else {
            $('#tableView').hide();
            $('#cardsView').show();
            setTimeout(paginateCards, 100);
        }
    });

    // ===== BÚSQUEDA =====
    
    // Búsqueda avanzada
    $('#salesSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();

        // Búsqueda en DataTable
        table.search(this.value).draw();

        // Búsqueda optimizada en tarjetas
        if ($('.view-toggle.active').data('view') === 'cards') {
            $('.modern-sale-card').each(function() {
                const customerName = $(this).find('.customer-name').text().toLowerCase();
                const customerEmail = $(this).find('.customer-email').text().toLowerCase();
                const saleDate = $(this).find('.detail-value').first().text().toLowerCase();

                if (customerName.includes(searchTerm) ||
                    customerEmail.includes(searchTerm) ||
                    saleDate.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });

    // ===== TOOLTIPS =====
    
    // Inicializar tooltips con configuración moderna
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover',
        placement: 'top',
        html: true
    });

    // Inicializar tooltips para las tarjetas de estadísticas
    $('.stats-card').tooltip({
        placement: 'top',
        trigger: 'hover',
        html: true,
        template: '<div class="tooltip modern-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
    });

    // ===== DETALLES DE VENTA =====
    
    // Ver detalles de la venta optimizado
    $(document).on('click', '.view-details', function() {
        // Verificar que DataTables esté cargado
        if (typeof $.fn.DataTable === 'undefined') {
            console.warn('DataTables no está cargado aún, esperando...');
            Swal.fire({
                title: 'Cargando...',
                text: 'Espere un momento mientras se cargan los componentes',
                icon: 'info',
                showConfirmButton: false,
                timer: 2000
            });
            return;
        }

        const saleId = $(this).data('id');
        const button = $(this);

        // Verificar que el modal esté disponible y listo
        const modal = $('#saleDetailsModal');
        if (modal.length === 0) {
            console.error('Modal no encontrado');
            return;
        }
        
        if (!modalReady) {
            console.warn('Modal no está listo aún, esperando...');
            Swal.fire({
                title: 'Cargando...',
                text: 'Espere un momento mientras se inicializa el modal',
                icon: 'info',
                showConfirmButton: false,
                timer: 1500
            });
            return;
        }


        // Verificar si ya hay una petición en progreso
        if (ajaxInProgress) {
            return;
        }

        // Marcar que hay una petición en progreso
        ajaxInProgress = true;

        // Indicador de carga simple
        button.html('<i class="fas fa-spinner fa-spin"></i> <span>Cargando...</span>');
        button.prop('disabled', true);

        $('#saleDetailsTableBody').empty();
        $('#noteCard').hide();

        
        $.ajax({
            url: `/test-sales-details/${saleId}`,
            method: 'GET',
            xhrFields: {
                withCredentials: true
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    let total = 0;

                    // Actualizar información del cliente y fecha
                    const customerPhone = response.sale.customer_phone || '-';
                    $('#customerInfo').html(`
                        <strong>Cliente:</strong> ${response.sale.customer_name}<br>
                        <strong>Teléfono:</strong> ${customerPhone}
                    `);
                    $('#saleDate').text(response.sale.date);

                    response.details.forEach(function(detail) {
                        const quantity = parseFloat(detail.quantity);
                        const price = parseFloat(detail.product_price);
                        const subtotal = quantity * price;
                        total += subtotal;
                        $('#saleDetailsTableBody').append(`
                            <tr>
                                <td>${detail.product.code || ''}</td>
                                <td>${detail.product.name || ''}</td>
                                <td>${detail.product.category || 'Sin categoría'}</td>
                                <td class="text-center">${quantity}</td>
                                <td class="text-right">${window.currencySymbol} ${price.toFixed(2)}</td>
                                <td class="text-right">${window.currencySymbol} ${subtotal.toFixed(2)}</td>
                            </tr>
                        `);
                    });

                                                const formattedTotal = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                            $('#modalTotal').text(formattedTotal);
                            $('#currencySymbol').text(window.currencySymbol);

                    // Manejar la nota
                    const noteCard = $('#noteCard');
                    const noteText = $('#noteText');
                    
                    if (response.sale.note && response.sale.note.trim() !== '') {
                        noteText.text(response.sale.note);
                        noteCard.show();
                    } else {
                        noteCard.hide();
                    }
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message || 'Error al cargar los detalles',
                        icon: 'error',
                        confirmButtonColor: '#667eea'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX:', {xhr, status, error});
                console.error('Status:', xhr.status);
                console.error('Response Text:', xhr.responseText);
                
                // Resetear variable de petición en progreso
                ajaxInProgress = false;
                
                if (xhr.status === 401) {
                    console.error('Usuario no autenticado');
                    Swal.fire({
                        title: 'Error de Autenticación',
                        text: 'Debe iniciar sesión para ver los detalles',
                        icon: 'warning',
                        confirmButtonColor: '#667eea'
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudieron cargar los detalles',
                        icon: 'error',
                        confirmButtonColor: '#667eea'
                    });
                }
            },
            complete: function() {
                // Resetear variable de petición en progreso
                ajaxInProgress = false;
                // Restaurar botón
                button.html('<i class="fas fa-list"></i> <span>Ver Detalle</span>');
                button.prop('disabled', false);
            }
        });
    });

    // ===== ELIMINACIÓN DE VENTAS =====
    
    // Eliminar venta con confirmación moderna
    $(document).on('click', '.delete-sale', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f56565',
            cancelButtonColor: '#718096',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            customClass: {
                popup: 'swal-modern',
                confirmButton: 'btn-modern-danger',
                cancelButton: 'btn-modern-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `/sales/delete/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#48bb78',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message,
                                icon: 'error',
                                confirmButtonColor: '#667eea'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = 'No se pudo eliminar la venta';
                        let iconType = 'error';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                            if (xhr.responseJSON.icons === 'warning') {
                                iconType = 'warning';
                            }
                        } else if (xhr.status === 422) {
                            errorMessage = 'No se puede eliminar esta venta debido a restricciones del sistema';
                        } else if (xhr.status === 404) {
                            errorMessage = 'La venta no fue encontrada';
                        } else if (xhr.status === 403) {
                            errorMessage = 'No tienes permisos para eliminar esta venta';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Error interno del servidor al eliminar la venta';
                        }
                        
                        Swal.fire({
                            title: iconType === 'warning' ? 'Advertencia' : 'Error',
                            text: errorMessage,
                            icon: iconType,
                            confirmButtonColor: iconType === 'warning' ? '#ed8936' : '#667eea',
                            confirmButtonText: 'Entendido'
                        });
                    }
                });
            }
        });
    });

    // ===== ACCIONES ADICIONALES =====
    
    // Imprimir venta
    $(document).on('click', '.print-sale, .print-details, .btn-print', function() {
        window.print();
    });

    // Editar venta
    $(document).on('click', '.btn-edit', function() {
        const saleId = $(this).data('id');

        if (saleId) {
            window.location.href = `/sales/edit/${saleId}`;
        } else {
            Swal.fire({
                title: 'Error',
                text: 'No se pudo identificar la venta a editar',
                icon: 'error',
                confirmButtonColor: '#667eea'
            });
        }
    });

    // ===== MODAL =====
    
    // Variable para controlar si el modal está listo
    let modalReady = false;
    
    // Arreglar problema de aria-hidden en el modal
    $('#saleDetailsModal').on('show.bs.modal', function() {
        $(this).removeAttr('aria-hidden');
        modalReady = true;
    });

    $('#saleDetailsModal').on('hidden.bs.modal', function() {
        $(this).attr('aria-hidden', 'true');
        modalReady = false;
        // Resetear variable de petición en progreso al cerrar modal
        ajaxInProgress = false;
    });

    // ===== FILTROS AVANZADOS =====
    
    // Toggle de filtros
    $('#filtersToggle').click(function() {
        const content = $('#filtersContent');
        const toggle = $(this);
        
        content.toggleClass('show');
        toggle.toggleClass('rotated');
        
        if (content.hasClass('show')) {
            toggle.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else {
            toggle.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
    });

    /**
     * Función para aplicar filtros
     */
    function applyFilters() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const amountMin = parseFloat($('#amountMin').val()) || 0;
        const amountMax = parseFloat($('#amountMax').val()) || Infinity;

        // Crear función de filtro personalizada para DataTable
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const saleDate = new Date(data[2].split(' ')[0].split('/').reverse().join('-'));
            const saleAmount = parseFloat(data[4].replace(/[^\d.,]/g, '').replace(',', ''));
            
            // Filtro de fecha
            let dateFilter = true;
            if (dateFrom) {
                const fromDate = new Date(dateFrom);
                dateFilter = dateFilter && saleDate >= fromDate;
            }
            if (dateTo) {
                const toDate = new Date(dateTo);
                toDate.setHours(23, 59, 59);
                dateFilter = dateFilter && saleDate <= toDate;
            }
            
            // Filtro de monto
            let amountFilter = true;
            if (amountMin > 0) {
                amountFilter = amountFilter && saleAmount >= amountMin;
            }
            if (amountMax < Infinity) {
                amountFilter = amountFilter && saleAmount <= amountMax;
            }
            
            return dateFilter && amountFilter;
        });

        // Aplicar filtros a las tarjetas si están visibles
        if ($('.view-toggle.active').data('view') === 'cards') {
            $('.modern-sale-card').each(function() {
                const card = $(this);
                const saleDateText = card.find('.detail-value').first().text().trim();
                const saleAmountText = card.find('.total-amount').text().trim();
                
                const saleDate = new Date(saleDateText.split(' ')[0].split('/').reverse().join('-'));
                const saleAmount = parseFloat(saleAmountText.replace(/[^\d.,]/g, '').replace(',', ''));
                
                let dateFilter = true;
                if (dateFrom) {
                    const fromDate = new Date(dateFrom);
                    dateFilter = dateFilter && saleDate >= fromDate;
                }
                if (dateTo) {
                    const toDate = new Date(dateTo);
                    toDate.setHours(23, 59, 59);
                    dateFilter = dateFilter && saleDate <= toDate;
                }
                
                let amountFilter = true;
                if (amountMin > 0) {
                    amountFilter = amountFilter && saleAmount >= amountMin;
                }
                if (amountMax < Infinity) {
                    amountFilter = amountFilter && saleAmount <= amountMax;
                }
                
                if (dateFilter && amountFilter) {
                    card.show();
                } else {
                    card.hide();
                }
            });
        }

        // Aplicar filtros a las tarjetas móviles si están visibles
        if ($(window).width() <= 768) {
            $('.mobile-sale-card').each(function() {
                const card = $(this);
                const saleDateText = card.find('.mobile-detail-value').first().text().trim();
                const saleAmountText = card.find('.total-amount-mobile').text().trim();
                
                const saleDate = new Date(saleDateText.split(' ')[0].split('/').reverse().join('-'));
                const saleAmount = parseFloat(saleAmountText.replace(/[^\d.,]/g, '').replace(',', ''));
                
                let dateFilter = true;
                if (dateFrom) {
                    const fromDate = new Date(dateFrom);
                    dateFilter = dateFilter && saleDate >= fromDate;
                }
                if (dateTo) {
                    const toDate = new Date(dateTo);
                    toDate.setHours(23, 59, 59);
                    dateFilter = dateFilter && saleDate <= toDate;
                }
                
                let amountFilter = true;
                if (amountMin > 0) {
                    amountFilter = amountFilter && saleAmount >= amountMin;
                }
                if (amountMax < Infinity) {
                    amountFilter = amountFilter && saleAmount <= amountMax;
                }
                
                if (dateFilter && amountFilter) {
                    card.show();
                } else {
                    card.hide();
                }
            });
        }

        // Redibujar la tabla
        table.draw();
        
        // Mostrar indicador de filtros activos
        const activeFilters = [];
        if (dateFrom || dateTo) activeFilters.push('fecha');
        if (amountMin > 0 || amountMax < Infinity) activeFilters.push('monto');
        
        if (activeFilters.length > 0) {
            $('#filtersStatus').show();
            $('#activeFiltersList').html(
                activeFilters.map(filter => `<span class="filter-badge">${filter}</span>`).join('')
            );
            
            Swal.fire({
                icon: 'success',
                title: 'Filtros aplicados',
                text: `Filtros de ${activeFilters.join(' y ')} aplicados correctamente`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            $('#filtersStatus').hide();
        }
    }

    /**
     * Función para limpiar filtros
     */
    function clearFilters() {
        // Limpiar inputs
        $('#dateFrom').val('');
        $('#dateTo').val('');
        $('#amountMin').val('');
        $('#amountMax').val('');
        
        // Limpiar filtros de DataTable
        $.fn.dataTable.ext.search.pop();
        
        // Mostrar todas las tarjetas si están visibles
        if ($('.view-toggle.active').data('view') === 'cards') {
            $('.modern-sale-card').show();
        }

        // Mostrar todas las tarjetas móviles si están visibles
        if ($(window).width() <= 768) {
            $('.mobile-sale-card').show();
        }
        
        // Ocultar indicador de filtros activos
        $('#filtersStatus').hide();
        
        // Redibujar tabla
        table.draw();
        
        // Mostrar notificación
        Swal.fire({
            icon: 'info',
            title: 'Filtros limpiados',
            text: 'Todos los filtros han sido removidos',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    // Event listeners para filtros
    $('#applyFilters').click(applyFilters);
    $('#clearFilters').click(clearFilters);

    // Aplicar filtros al presionar Enter en los inputs
    $('.filter-input').keypress(function(e) {
        if (e.which === 13) {
            applyFilters();
        }
    });

    // ===== VALIDACIONES =====
    
    // Validación de fechas
    $('#dateFrom, #dateTo').change(function() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        if (dateFrom && dateTo && dateFrom > dateTo) {
            Swal.fire({
                icon: 'warning',
                title: 'Rango de fechas inválido',
                text: 'La fecha "Desde" no puede ser mayor que la fecha "Hasta"',
                confirmButtonColor: '#667eea'
            });
            
            $(this).val('');
        }
    });

    // Validación de montos
    $('#amountMin, #amountMax').change(function() {
        const amountMin = parseFloat($('#amountMin').val()) || 0;
        const amountMax = parseFloat($('#amountMax').val()) || 0;
        
        if (amountMin > 0 && amountMax > 0 && amountMin > amountMax) {
            Swal.fire({
                icon: 'warning',
                title: 'Rango de montos inválido',
                text: 'El monto mínimo no puede ser mayor que el monto máximo',
                confirmButtonColor: '#667eea'
            });
            
            $(this).val('');
        }
    });

    // Establecer fecha máxima como hoy para el campo "Hasta"
    const today = new Date().toISOString().split('T')[0];
    $('#dateTo').attr('max', today);

    // ===== RESPONSIVE DESIGN =====
    
    /**
     * Función para ajustar vista según el tamaño de pantalla
     */
    function adjustViewForScreenSize() {
        const windowWidth = $(window).width();
        
        if (windowWidth <= 768) {
            // En móvil, ocultar tabla y mostrar vista móvil
            $('.table-view').hide();
            $('.cards-view').hide();
            $('.mobile-view').show();
            $('.view-toggles').hide();
        } else {
            // En desktop, mostrar tabla y botones de vista
            $('.table-view').show();
            $('.mobile-view').hide();
            $('.view-toggles').show();
            
            // Restaurar vista activa (tabla o cards)
            const activeView = $('.view-toggle.active').data('view');
            if (activeView === 'cards') {
                $('#tableView').hide();
                $('#cardsView').show();
            } else {
                $('#tableView').show();
                $('#cardsView').hide();
            }
        }
    }

    // Ajustar vista al cargar la página
    adjustViewForScreenSize();

    // Ajustar vista cuando cambie el tamaño de la ventana
    $(window).resize(adjustViewForScreenSize);
}); 