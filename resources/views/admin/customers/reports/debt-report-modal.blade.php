<div class="modal-header bg-gradient-dark text-white">
    <h5 class="modal-title font-weight-bold" id="debtReportModalLabel">
        <i class="fas fa-file-invoice-dollar mr-2"></i>Reporte de Deudas
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-2">
                        <h4 class="mb-0 font-weight-bold">{{ $company->name }}</h4>
                        <span class="badge badge-info ml-2">{{ date('d/m/Y') }}</span>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="far fa-clock mr-1"></i>Generado: {{ date('H:i:s') }}
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-md-end align-items-center">
                        <div class="form-group mb-0 mr-3">
                            <label for="modalExchangeRate" class="small text-muted mb-1">Tipo de Cambio (1 USD =)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="modalExchangeRate" class="form-control" step="0.01" min="0.01" value="1.00">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" id="updateModalExchangeRate">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.customers.debt-report.download') }}" id="viewPdfBtn" class="btn btn-sm btn-danger" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i>Ver PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros de búsqueda -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label for="searchFilter" class="small text-muted mb-1">
                            <i class="fas fa-search mr-1"></i>Buscar cliente
                        </label>
                        <input type="text" id="searchFilter" class="form-control" placeholder="Buscar por nombre, teléfono, email o cédula...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label for="orderFilter" class="small text-muted mb-1">
                            <i class="fas fa-sort mr-1"></i>Ordenar por
                        </label>
                        <select id="orderFilter" class="form-control form-control-sm">
                            <option value="name_asc">Nombre (A-Z)</option>
                            <option value="name_desc">Nombre (Z-A)</option>
                            <option value="debt_desc" selected>Deuda (Mayor a menor)</option>
                            <option value="debt_asc">Deuda (Menor a mayor)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label class="small text-muted mb-1">
                            <i class="fas fa-filter mr-1"></i>Filtrar por deuda
                        </label>
                        <div class="d-flex align-items-center">
                            <input type="number" id="debtMinFilter" class="form-control form-control-sm mr-2" placeholder="Mín $" min="0" style="width: 80px;">
                            <span class="mx-1">-</span>
                            <input type="number" id="debtMaxFilter" class="form-control form-control-sm" placeholder="Máx $" min="0" style="width: 80px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Resumen:</strong> <span id="clientCount">{{ $customers->count() }}</span> clientes con deudas pendientes
        </div>
        <div class="text-right">
            <div class="font-weight-bold">Deuda Total: <span id="totalDebtDisplay">{{ $currency->symbol }} {{ number_format($totalDebt, 2) }}</span></div>
            <div class="modal-bs-debt" data-debt="{{ $totalDebt }}">
                <span id="totalBsDebtDisplay">Bs. {{ number_format($totalDebt, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th class="sortable-header" data-sort="name" style="cursor: pointer;" title="Haz clic para ordenar por nombre">
                        Cliente 
                        <i class="fas fa-sort ml-1 sort-icon" data-sort="name"></i>
                    </th>
                    <th>Contacto</th>
                    <th>Cédula</th>
                    <th class="sortable-header" data-sort="debt" style="cursor: pointer;" title="Haz clic para ordenar por deuda">
                        Deuda Total 
                        <i class="fas fa-sort ml-1 sort-icon" data-sort="debt"></i>
                    </th>
                    <th>Deuda en Bs</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $index => $customer)
                    <tr class="customer-row" 
                        data-customer-id="{{ $customer->id }}"
                        data-name="{{ strtolower($customer->name) }}"
                        data-phone="{{ strtolower($customer->phone ?? '') }}"
                        data-email="{{ strtolower($customer->email ?? '') }}"
                        data-nit="{{ strtolower($customer->nit_number ?? '') }}"
                        data-debt="{{ $customer->total_debt }}">
                        <td class="row-number">{{ $index + 1 }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>
                            {{ $customer->phone ?? '' }}<br>
                            <small class="text-muted">{{ $customer->email ?? '' }}</small>
                        </td>
                        <td>{{ $customer->nit_number ?? '' }}</td>
                        <td class="text-right text-danger font-weight-bold">
                            {{ $currency->symbol }} {{ number_format($customer->total_debt, 2) }}
                        </td>
                        <td class="text-right text-danger font-weight-bold bs-debt" data-debt="{{ $customer->total_debt }}">
                            Bs. {{ number_format($customer->total_debt, 2) }}
                        </td>
                        {{-- <td>
                            @if($customer->lastSale)
                                {{ $customer->lastSale->sale_date->format('d/m/Y') }}
                            @else
                                
                            @endif
                        </td> --}}
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No hay clientes con deudas pendientes</td>
                    </tr>
                @endforelse
                <tr class="bg-light font-weight-bold" id="totalRow">
                    <td colspan="4" class="text-right">TOTAL DEUDA PENDIENTE:</td>
                    <td class="text-right text-danger" id="totalDebtTable">
                        {{ $currency->symbol }} {{ number_format($totalDebt, 2) }}
                    </td>
                    <td class="text-right text-danger" id="totalBsDebtTable">
                        Bs. {{ number_format($totalDebt, 2) }}
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
    .sortable-header {
        user-select: none;
        transition: background-color 0.2s ease;
    }
    
    .sortable-header:hover {
        background-color: #e9ecef !important;
    }
    
    .sort-icon {
        font-size: 0.8em;
        color: #6c757d;
        transition: color 0.2s ease;
    }
    
    .sortable-header:hover .sort-icon {
        color: #495057;
    }
    
    .sort-icon.fa-sort-up,
    .sort-icon.fa-sort-down {
        color: #007bff;
    }
    
    .sortable-header.active {
        background-color: #e3f2fd !important;
    }
</style>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
    <a href="{{ route('admin.customers.report') }}" id="generatePdfBtn" target="_blank" class="btn btn-primary">
        <i class="fas fa-file-pdf mr-1"></i>Generar PDF
    </a>
</div>

<script>
    // Este script se ejecutará cuando el modal se cargue
    $(document).ready(function() {
        console.log('Modal cargado, esperando valor de tipo de cambio');
        
        // Variables para el filtrado
        let originalRows = $('.customer-row').clone();
        let currencySymbol = '{{ $currency->symbol }}';
        
        // Función para actualizar los números de fila
        function updateRowNumbers() {
            $('.customer-row:visible').each(function(index) {
                $(this).find('.row-number').text(index + 1);
            });
        }
        
        // Función para actualizar el resumen
        function updateSummary() {
            let visibleRows = $('.customer-row:visible');
            let totalDebt = 0;
            
            visibleRows.each(function() {
                totalDebt += parseFloat($(this).data('debt'));
            });
            
            $('#clientCount').text(visibleRows.length);
            $('#totalDebtDisplay').text(currencySymbol + ' ' + totalDebt.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            // Actualizar deuda en Bs
            let exchangeRate = parseFloat($('#modalExchangeRate').val()) || 1;
            let totalDebtBs = totalDebt * exchangeRate;
            $('#totalBsDebtDisplay').text('Bs. ' + totalDebtBs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            // Actualizar los totales en la tabla (fila inferior)
            $('#totalDebtTable').text(currencySymbol + ' ' + totalDebt.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#totalBsDebtTable').text('Bs. ' + totalDebtBs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }
        
        // Función para ordenar filas
        function sortRows() {
            const order = $('#orderFilter').val();
            const $tbody = $('#debtReportModal').find('tbody');
            const rows = $('.customer-row:visible').get();
            const $totalRow = $('#totalRow');

            rows.sort(function(a, b) {
                const nameA = $(a).data('name');
                const nameB = $(b).data('name');
                const debtA = parseFloat($(a).data('debt')) || 0;
                const debtB = parseFloat($(b).data('debt')) || 0;

                if (order === 'name_asc') {
                    return nameA.localeCompare(nameB);
                } else if (order === 'name_desc') {
                    return nameB.localeCompare(nameA);
                } else if (order === 'debt_desc') {
                    return debtB - debtA;
                } else if (order === 'debt_asc') {
                    return debtA - debtB;
                }
                return 0;
            });

            // Primero las filas de clientes
            $.each(rows, function(idx, row) {
                $tbody.append(row);
            });
            // Luego la fila de total
            $tbody.append($totalRow);
        }

        // Función para aplicar filtros
        function applyFilters() {
            let searchTerm = $('#searchFilter').val().toLowerCase();
            let debtMin = parseFloat($('#debtMinFilter').val()) || 0;
            let debtMax = parseFloat($('#debtMaxFilter').val()) || Infinity;
            
            $('.customer-row').each(function() {
                let row = $(this);
                let showRow = true;
                
                // Filtro de búsqueda
                if (searchTerm) {
                    let name = row.data('name');
                    let phone = row.data('phone');
                    let email = row.data('email');
                    let nit = row.data('nit');
                    
                    if (!name.includes(searchTerm) && 
                        !phone.includes(searchTerm) && 
                        !email.includes(searchTerm) && 
                        !nit.includes(searchTerm)) {
                        showRow = false;
                    }
                }
                
                // Filtro por rango de deuda
                if (showRow) {
                    let debt = parseFloat(row.data('debt'));
                    
                    if (debt < debtMin || debt > debtMax) {
                        showRow = false;
                    }
                }
                
                if (showRow) {
                    row.show();
                } else {
                    row.hide();
                }
            });
            
            sortRows();
            updateRowNumbers();
            updateSummary();
            updatePdfLinks();
        }
        
        // Función para actualizar los enlaces del PDF con los filtros
        function updatePdfLinks() {
            let searchTerm = $('#searchFilter').val();
            let debtMin = $('#debtMinFilter').val();
            let debtMax = $('#debtMaxFilter').val();
            let exchangeRate = $('#modalExchangeRate').val();
            let order = $('#orderFilter').val();

            let params = new URLSearchParams();
            if (searchTerm) params.append('search', searchTerm);
            if (debtMin) params.append('debt_min', debtMin);
            if (debtMax) params.append('debt_max', debtMax);
            if (exchangeRate) params.append('exchange_rate', exchangeRate);
            if (order) params.append('order', order);

            let queryString = params.toString();
            let baseUrl1 = '{{ route("admin.customers.debt-report.download") }}';
            let baseUrl2 = '{{ route("admin.customers.report") }}';

            $('#viewPdfBtn').attr('href', baseUrl1 + (queryString ? '?' + queryString : ''));
            $('#generatePdfBtn').attr('href', baseUrl2 + (queryString ? '?' + queryString : ''));
        }
        
        // Event listeners para los filtros
        $('#searchFilter').on('input', function() {
            applyFilters();
        });
        
        $('#debtMinFilter, #debtMaxFilter').on('input change', function() {
            applyFilters();
        });

        // Ordenamiento
        $('#orderFilter').on('change', function() {
            sortRows();
            updateRowNumbers();
            updateSortIcons();
        });
        
        // Variables para el control de ordenamiento por click en encabezados
        let currentSortColumn = 'debt'; // Por defecto ordenar por deuda
        let currentSortOrder = 'desc'; // Por defecto de mayor a menor
        
        // Función para actualizar los iconos de ordenamiento
        function updateSortIcons() {
            const order = $('#orderFilter').val();
            
            // Resetear todos los iconos y clases activas
            $('.sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
            $('.sortable-header').removeClass('active');
            
            // Actualizar el icono correspondiente y marcar como activo
            if (order === 'name_asc') {
                $('.sort-icon[data-sort="name"]').removeClass('fa-sort fa-sort-down').addClass('fa-sort-up');
                $('.sortable-header[data-sort="name"]').addClass('active');
            } else if (order === 'name_desc') {
                $('.sort-icon[data-sort="name"]').removeClass('fa-sort fa-sort-up').addClass('fa-sort-down');
                $('.sortable-header[data-sort="name"]').addClass('active');
            } else if (order === 'debt_desc') {
                $('.sort-icon[data-sort="debt"]').removeClass('fa-sort fa-sort-up').addClass('fa-sort-down');
                $('.sortable-header[data-sort="debt"]').addClass('active');
            } else if (order === 'debt_asc') {
                $('.sort-icon[data-sort="debt"]').removeClass('fa-sort fa-sort-down').addClass('fa-sort-up');
                $('.sortable-header[data-sort="debt"]').addClass('active');
            }
        }
        
        // Manejar clicks en los encabezados de columna
        $('.sortable-header').on('click', function() {
            const column = $(this).data('sort');
            
            // Si es la misma columna, cambiar el orden
            if (currentSortColumn === column) {
                currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                // Si es una columna diferente, establecer el orden por defecto
                currentSortColumn = column;
                currentSortOrder = column === 'name' ? 'asc' : 'desc'; // Nombres por defecto A-Z, deudas por defecto mayor a menor
            }
            
            // Actualizar el select para reflejar el cambio
            const selectValue = column + '_' + currentSortOrder;
            $('#orderFilter').val(selectValue);
            
            // Aplicar el ordenamiento
            sortRows();
            updateRowNumbers();
            updateSortIcons();
        });
        
        // Inicializar iconos de ordenamiento
        updateSortIcons();
        
        // Actualizar tipo de cambio
        $('#updateModalExchangeRate').on('click', function() {
            let rate = parseFloat($('#modalExchangeRate').val()) || 1;
            
            // Actualizar todas las celdas de deuda en Bs de las filas de clientes
            $('.customer-row .bs-debt').each(function() {
                let debt = parseFloat($(this).data('debt'));
                let debtBs = debt * rate;
                $(this).text('Bs. ' + debtBs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            });
            
            updateSummary();
            updatePdfLinks();
        });
        
        // Inicializar
        updatePdfLinks();
    });
</script> 