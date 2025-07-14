<div class="modal-header debt-modal-header">
    <div class="header-icon">
        <i class="fas fa-file-invoice-dollar"></i>
    </div>
    <div class="header-content">
        <h5 class="modal-title font-weight-bold">Reporte de Deudas</h5>
        <div class="header-subtitle">Visualiza y filtra clientes con deudas pendientes</div>
    </div>
    <button type="button" class="close close-btn" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body debt-modal-body">
    <div class="row mb-3">
        <div class="col-lg-5 mb-3 mb-lg-0">
            <div class="card company-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="company-info">
                        <div class="company-name font-weight-bold">{{ $company->name }}</div>
                        <div class="company-meta text-muted small">
                            <i class="far fa-clock mr-1"></i>{{ date('d/m/Y H:i:s') }}
                        </div>
                    </div>
                </div>
                                </div>
                            </div>
        <div class="col-lg-7">
            <div class="card rate-card">
                <div class="card-body d-flex align-items-center gap-2 debt-rate-row-redesigned">
                    <div class="rate-label mr-2">Conversión:</div>
                    <span class="currency-code mx-2">1 USD</span>
                    <input type="number" id="modalExchangeRate" class="form-control rate-input-compact mx-2" step="0.01" min="0.01" value="{{ $exchangeRate ?? 1 }}" style="text-align: center; font-weight: 600;">
                    <button class="btn btn-primary update-rate-btn mx-2" type="button" id="updateModalExchangeRate" data-toggle="tooltip" title="Actualizar tipo de cambio">
                        <i class="fas fa-sync-alt"></i>
                        <span class="d-none d-md-inline">Actualizar</span>
                    </button>
                    <a href="{{ route('admin.customers.debt-report.download') }}" target="_blank" class="btn btn-pdf-modal mx-2" id="viewPdfBtn" data-toggle="tooltip" title="Ver PDF de deudores">
                        <i class="fas fa-file-pdf"></i>
                        <span class="d-none d-md-inline">PDF</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-3 filter-card">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label for="searchFilter" class="filter-label"><i class="fas fa-search mr-1"></i>Buscar cliente</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="searchFilter" class="form-control" placeholder="Nombre, teléfono, email o cédula...">
                        <div class="input-group-append">
                            <button class="btn btn-light clear-search" type="button" onclick="$('#searchFilter').val('').trigger('input')" data-toggle="tooltip" title="Limpiar búsqueda"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <label for="orderFilter" class="filter-label"><i class="fas fa-sort mr-1"></i>Ordenar por</label>
                        <select id="orderFilter" class="form-control form-control-sm">
                            <option value="name_asc">Nombre (A-Z)</option>
                            <option value="name_desc">Nombre (Z-A)</option>
                            <option value="debt_desc" selected>Deuda (Mayor a menor)</option>
                            <option value="debt_asc">Deuda (Menor a mayor)</option>
                        </select>
                </div>
                <div class="col-md-4">
                    <label class="filter-label"><i class="fas fa-filter mr-1"></i>Filtrar por deuda</label>
                        <div class="d-flex align-items-center">
                            <input type="number" id="debtMinFilter" class="form-control form-control-sm mr-2" placeholder="Mín $" min="0" style="width: 80px;">
                            <span class="mx-1">-</span>
                            <input type="number" id="debtMaxFilter" class="form-control form-control-sm" placeholder="Máx $" min="0" style="width: 80px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <div class="card mb-3 summary-card summary-gradient-card">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center summary-flex-better">
            <div class="summary-info d-flex align-items-center gap-3 summary-info-block">
                <div class="summary-icon">
                    <i class="fas fa-info-circle"></i>
    </div>
        <div>
                    <div class="summary-title">Clientes con deudas</div>
                    <div class="summary-value"><span id="clientCount">{{ $customers->count() }}</span></div>
                </div>
            </div>
            <div class="summary-totals-block d-flex flex-column align-items-end justify-content-center">
                <div class="summary-total-label mb-1">Deuda Total</div>
                <div class="summary-totals-row d-flex align-items-center gap-2">
                    <span id="totalDebtDisplay" class="badge badge-danger summary-badge-big">{{ $currency->symbol }} {{ number_format($totalDebt, 2) }}</span>
                    <span id="totalBsDebtDisplay" class="badge badge-primary summary-badge-big">Bs. {{ number_format($totalDebt * ($exchangeRate ?? 1), 2) }}</span>
        </div>
            </div>
        </div>
    </div>
    <div class="table-responsive debt-modal-table-responsive">
        <table class="table table-striped table-bordered debt-modal-table">
            <thead class="bg-light sticky-top">
                <tr>
                    <th>#</th>
                    <th class="sortable-header" data-sort="name" style="cursor: pointer;" title="Haz clic para ordenar por nombre">
                        Cliente <i class="fas fa-sort ml-1 sort-icon" data-sort="name"></i>
                    </th>
                    <th class="d-none d-md-table-cell">Contacto</th>
                    <th class="sortable-header" data-sort="debt" style="cursor: pointer;" title="Haz clic para ordenar por deuda">
                        Deuda Total <i class="fas fa-sort ml-1 sort-icon" data-sort="debt"></i>
                    </th>
                    <th>Deuda en Bs</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $index => $customer)
                    <tr class="customer-row-modal" 
                        data-customer-id="{{ $customer->id }}"
                        data-name="{{ strtolower($customer->name) }}"
                        data-phone="{{ strtolower($customer->phone ?? '') }}"
                        data-email="{{ strtolower($customer->email ?? '') }}"
                        data-nit="{{ strtolower($customer->nit_number ?? '') }}"
                        data-debt="{{ $customer->total_debt }}">
                        <td class="row-number-modal">{{ $index + 1 }}</td>
                        <td>{{ $customer->name }}</td>
                        <td class="d-none d-md-table-cell">
                            {{ $customer->phone ?? '' }}<br>
                            <small class="text-muted">{{ $customer->email ?? '' }}</small>
                        </td>
                        <td class="text-right text-danger font-weight-bold">
                            {{ $currency->symbol }} {{ number_format($customer->total_debt, 2) }}
                        </td>
                        <td class="text-right text-danger font-weight-bold bs-debt-modal" data-debt="{{ $customer->total_debt }}">
                            Bs. {{ number_format($customer->total_debt * ($exchangeRate ?? 1), 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No hay clientes con deudas pendientes</td>
                    </tr>
                @endforelse
                <tr class="bg-light font-weight-bold" id="totalRowModal">
                    <td colspan="3" class="text-right d-none d-md-table-cell">TOTAL DEUDA PENDIENTE:</td>
                    <td colspan="2" class="text-right d-md-none">TOTAL DEUDA PENDIENTE:</td>
                    <td class="text-right text-danger" id="totalDebtTableModal">
                        {{ $currency->symbol }} {{ number_format($totalDebt, 2) }}
                    </td>
                    <td class="text-right text-danger" id="totalBsDebtTableModal">
                        Bs. {{ number_format($totalDebt * ($exchangeRate ?? 1), 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer debt-modal-footer">
    <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Cerrar</button>
    {{-- <a href="{{ route('admin.customers.report') }}" id="generatePdfBtn" target="_blank" class="btn btn-primary btn-lg">
        <i class="fas fa-file-pdf mr-1"></i>Generar PDF
    </a> --}}
</div>
<style>
.debt-modal-header {
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem 2rem 1.2rem 2rem;
    border-bottom: none;
}
.header-icon {
    background: #fff2;
    border-radius: 50%;
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    box-shadow: 0 2px 8px rgba(102,126,234,0.08);
}
.header-content {
    flex: 1;
}
.header-content .modal-title {
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 0.2rem;
}
.header-subtitle {
    font-size: 1rem;
    opacity: 0.85;
    margin-bottom: 0;
}
.close-btn {
    color: #fff;
    font-size: 1.5rem;
    opacity: 0.8;
    background: none;
    border: none;
    transition: 0.2s;
}
.close-btn:hover {
    opacity: 1;
    color: #fff;
    background: #fff3;
}
.debt-modal-body {
    background: #f8f9fa;
    padding: 2rem 2rem 1.5rem 2rem;
}
.company-card, .rate-card, .filter-card, .summary-card {
    border-radius: 0.7rem;
    box-shadow: 0 2px 12px rgba(102,126,234,0.07);
    border: none;
}
.company-card .company-name {
    font-size: 1.2rem;
    font-weight: 600;
}
.company-meta {
    font-size: 0.95rem;
    }
.rate-card .rate-label {
    font-weight: 600;
    color: #764ba2;
    font-size: 1rem;
}
.rate-input-group input {
    font-weight: 600;
    font-size: 1.1rem;
    min-width: 80px;
    text-align: center;
}
.update-rate-btn {
    font-size: 1.1rem;
    padding: 0.4rem 0.9rem;
    border-radius: 0.5rem;
    margin-left: 0.3rem;
}
.currency-code {
    font-weight: 600;
    color: #667eea;
    font-size: 1rem;
}
.filter-card {
    background: #fff;
    margin-bottom: 1.2rem;
}
.filter-label {
    font-weight: 600;
    color: #764ba2;
    font-size: 0.97rem;
}
.clear-search {
    color: #764ba2;
    background: #f3e8ff;
    border: none;
    border-radius: 0.4rem;
    transition: 0.2s;
}
.clear-search:hover {
    background: #e0cfff;
    color: #4b2e83;
}
.summary-card {
    background: linear-gradient(90deg, #e0c3fc 0%, #8ec5fc 100%);
    color: #333;
    margin-bottom: 1.2rem;
}
.summary-info {
    gap: 1.2rem;
}
.summary-icon {
    background: #fff;
    color: #764ba2;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 2px 8px rgba(102,126,234,0.08);
}
.summary-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.1rem;
}
.summary-value {
    font-size: 1.7rem;
    font-weight: 700;
}
.summary-totals {
    min-width: 180px;
}
.summary-total-label {
    font-size: 1rem;
    font-weight: 600;
    color: #764ba2;
}
.summary-total-value, .summary-total-bs {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 0.2rem;
}
.summary-total-value .badge, .summary-total-bs .badge {
    font-size: 1.1rem;
    padding: 0.6em 1.1em;
    border-radius: 1.2em;
}
.debt-table-responsive {
    background: #fff;
    border-radius: 0.7rem;
    box-shadow: 0 2px 12px rgba(102,126,234,0.07);
    padding: 1.2rem 1rem 1.2rem 1rem;
    margin-bottom: 0.5rem;
}
.debt-table {
    margin-bottom: 0;
}
.debt-table thead th {
    background: linear-gradient(90deg, #f8fafc 0%, #e9ecef 100%);
    font-weight: 700;
    color: #764ba2;
    font-size: 1rem;
    border-bottom: 2px solid #e0e0e0;
    position: sticky;
    top: 0;
    z-index: 2;
}
.debt-table tbody tr {
    transition: background 0.2s;
}
.debt-table tbody tr:hover {
    background: #f3e8ff;
}
.debt-table td, .debt-table th {
    vertical-align: middle;
}
.debt-table .text-danger {
    font-weight: 700;
}
#totalRow {
    background: #f8fafc !important;
    font-size: 1.1rem;
    border-top: 2px solid #e0e0e0;
}
.debt-modal-footer {
    background: #f8f9fa;
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
    padding: 1.5rem 2rem 1.5rem 2rem;
    display: flex;
    gap: 1.2rem;
    justify-content: flex-end;
    }
.debt-rate-row-redesigned {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: nowrap;
    justify-content: flex-start;
    width: 100%;
}
.rate-input-compact {
    max-width: 110px;
    min-width: 70px;
    padding: 0.5rem 0.7rem;
    font-size: 1.05rem;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    box-shadow: none;
}
.rate-input-compact:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 2px #667eea22;
}
.summary-totals-row {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    margin-top: 0.2rem;
}
.summary-gradient-card {
    background: linear-gradient(90deg, #e0c3fc 0%, #8ec5fc 100%);
    border: none;
    box-shadow: 0 2px 16px rgba(102,126,234,0.07);
    border-radius: 18px;
}
.summary-flex-better {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    gap: 2.5rem;
    min-height: 110px;
}
.summary-info-block {
    min-width: 220px;
    flex: 1 1 220px;
}
.summary-totals-block {
    min-width: 220px;
    flex: 1 1 220px;
    align-items: flex-end;
}
.summary-badge-big {
    font-size: 1.2rem;
    padding: 0.7rem 2.1rem;
    border-radius: 18px;
    font-weight: 700;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
}
.summary-total-label {
    color: #7c3aed;
    font-weight: 600;
    font-size: 1.05rem;
    margin-bottom: 0.2rem;
    text-align: right;
}
@media (max-width: 768px) {
    .summary-flex-better {
        flex-direction: column;
        align-items: stretch;
        gap: 1.2rem;
    }
    .summary-totals-block {
        align-items: flex-start;
        min-width: 0;
    }
    .summary-totals-row {
        flex-direction: row;
        gap: 0.5rem;
    }
}
@media (max-width: 991px) {
    .debt-modal-body {
        padding: 1.2rem 0.5rem 1rem 0.5rem;
    }
    .modal-header, .modal-footer {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .debt-rate-row-redesigned {
        gap: 0.3rem;
    }
    .rate-label, .currency-code {
        font-size: 0.97rem;
        min-width: unset;
        margin-bottom: 0;
        margin-right: 0.08rem;
    }
    .rate-input-compact {
        min-width: 60px;
        max-width: 80px;
        font-size: 1.01rem;
        padding: 0.3rem 0.4rem;
        margin-bottom: 0;
    }
    .update-rate-btn, .btn-pdf-modal {
        min-width: 80px;
        max-width: 120px;
        padding: 0.3rem 0.7rem;
        font-size: 1.01rem;
        margin: 0;
        white-space: nowrap;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}
@media (max-width: 576px) {
    .debt-modal-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 1rem 0.7rem 0.7rem 0.7rem;
    }
    .header-icon {
        width: 40px;
        height: 40px;
        font-size: 1.3rem;
    }
    .header-content .modal-title {
        font-size: 1.15rem;
    }
    .header-subtitle {
        font-size: 0.93rem;
    }
    .company-card, .rate-card, .filter-card, .summary-card {
        margin-bottom: 0.5rem;
        padding: 0.6rem 0.3rem;
        border-radius: 0.6rem;
    }
    .company-card .company-name {
        font-size: 1.05rem;
    }
    .company-meta {
        font-size: 0.85rem;
    }
    .rate-card .rate-label {
        font-size: 0.97rem;
        margin-bottom: 0.2rem;
    }
    .debt-rate-row-redesigned {
        gap: 0.13rem;
        padding: 0.1rem 0;
        flex-wrap: nowrap;
    }
    .rate-label, .currency-code {
        font-size: 0.95rem;
        min-width: unset;
        margin-bottom: 0;
        margin-right: 0.05rem;
    }
    .rate-input-compact {
        min-width: 48px;
        max-width: 60px;
        font-size: 0.97rem;
        padding: 0.2rem 0.3rem;
        margin-bottom: 0;
    }
    .update-rate-btn, .btn-pdf-modal {
        min-width: 38px;
        max-width: 60px;
        padding: 0.2rem 0.3rem;
        font-size: 0.97rem;
        margin: 0;
        white-space: nowrap;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .update-rate-btn span, .btn-pdf-modal span {
        display: none;
    }
    #viewPdfBtn {
        width: 100%;
        margin-top: 0.3rem;
        font-size: 0.97rem;
        display: block;
        text-align: center;
    }
    .summary-card {
        padding: 0.7rem 0.3rem;
        border-radius: 0.7rem;
    }
    .summary-flex-better {
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        min-height: unset;
        text-align: center;
        padding: 0.2rem 0;
    }
    .summary-info-block {
        min-width: 0;
        flex: 1 1 0;
        margin-bottom: 0.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .summary-icon {
        width: 36px;
        height: 36px;
        font-size: 1.1rem;
    }
    .summary-title {
        font-size: 1.01rem;
        margin-bottom: 0.1rem;
    }
    .summary-value {
        font-size: 1.4rem;
        font-weight: 700;
        margin-left: 0.2rem;
    }
    .summary-totals-block {
        min-width: 0;
        flex: 1 1 0;
        align-items: center;
        margin-top: 0.2rem;
    }
    .summary-total-label {
        font-size: 0.99rem;
        margin-bottom: 0.1rem;
        text-align: center;
    }
    .summary-totals-row {
        flex-direction: row;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 0.1rem;
    }
    .summary-badge-big {
        font-size: 1.13rem;
        padding: 0.5rem 1.1rem;
        border-radius: 14px;
        margin-bottom: 0;
    }
}
@media (max-width: 767px) {
    #updateModalExchangeRate span {
        display: none !important;
    }
}
.btn-pdf-modal {
    background: linear-gradient(90deg, #ff5858 0%, #f857a6 100%);
    color: #fff;
    font-weight: 600;
    border: none;
    border-radius: 0.7rem;
    padding: 0.45rem 1.1rem;
    font-size: 1.08rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 2px 8px rgba(248,87,166,0.08);
    transition: background 0.18s, box-shadow 0.18s;
}
.btn-pdf-modal:hover, .btn-pdf-modal:focus {
    background: linear-gradient(90deg, #f857a6 0%, #ff5858 100%);
    color: #fff;
    box-shadow: 0 4px 16px rgba(248,87,166,0.13);
    text-decoration: none;
}
@media (max-width: 576px) {
    .btn-pdf-modal {
        width: 14%;
        min-width: 32px;
        max-width: 38px;
        padding: 0.3rem 0.4rem;
        font-size: 1.05rem;
        justify-content: center;
        gap: 0;
    }
    .btn-pdf-modal span {
        display: none;
    }
    }
</style>

<script>
    $(document).ready(function() {
        // Función para actualizar el enlace del PDF con los filtros aplicados
        function updatePdfLinks() {
            let searchTerm = $('#searchFilter').val();
            let debtMin = $('#debtMinFilter').val();
            let debtMax = $('#debtMaxFilter').val();
            let order = $('#orderFilter').val();
            let exchangeRate = $('#modalExchangeRate').val();
            
            // Construir la URL base
            let pdfUrl = '{{ route("admin.customers.debt-report.download") }}';
            let params = [];
            
            // Agregar parámetros solo si tienen valor
            if (searchTerm && searchTerm.trim() !== '') {
                params.push('search=' + encodeURIComponent(searchTerm.trim()));
            }
            if (debtMin && debtMin !== '') {
                params.push('debt_min=' + encodeURIComponent(debtMin));
            }
            if (debtMax && debtMax !== '') {
                params.push('debt_max=' + encodeURIComponent(debtMax));
            }
            if (order && order !== 'debt_desc') { // Solo agregar si no es el valor por defecto
                params.push('order=' + encodeURIComponent(order));
            }
            if (exchangeRate && exchangeRate !== '{{ $exchangeRate ?? 1 }}') {
                params.push('exchange_rate=' + encodeURIComponent(exchangeRate));
            }
            
            // Construir la URL final
            if (params.length > 0) {
                pdfUrl += '?' + params.join('&');
            }
            
            // Actualizar el enlace del botón PDF
            $('#viewPdfBtn').attr('href', pdfUrl);
        }

        // Función para actualizar los iconos de ordenamiento
        function updateSortIcons() {
            const order = $('#orderFilter').val();
            
            // Remover todas las clases de iconos
            $('.sort-icon').removeClass('fa-sort-up fa-sort-down fa-sort');
            
            // Aplicar el icono correcto según el orden
            if (order === 'name_asc') {
                $('.sort-icon[data-sort="name"]').addClass('fa-sort-up');
            } else if (order === 'name_desc') {
                $('.sort-icon[data-sort="name"]').addClass('fa-sort-down');
            } else if (order === 'debt_asc') {
                $('.sort-icon[data-sort="debt"]').addClass('fa-sort-up');
            } else if (order === 'debt_desc') {
                $('.sort-icon[data-sort="debt"]').addClass('fa-sort-down');
            } else {
                // Estado por defecto
                $('.sort-icon').addClass('fa-sort');
            }
        }

        // Función para actualizar los números de fila SOLO en el modal
        function updateRowNumbersModal() {
            $('.customer-row-modal:visible').each(function(index) {
                $(this).find('.row-number-modal').text(index + 1);
            });
        }
        // Función para actualizar el resumen SOLO en el modal
        function updateSummaryModal() {
            let visibleRows = $('.customer-row-modal:visible');
            let totalDebt = 0;
            visibleRows.each(function() {
                let debt = parseFloat($(this).data('debt'));
                if (!isNaN(debt)) {
                    totalDebt += debt;
                }
            });
            $('#clientCount').text(visibleRows.length);
            let exchangeRate = parseFloat($('#modalExchangeRate').val());
            if (isNaN(exchangeRate) || exchangeRate <= 0) exchangeRate = {{ $exchangeRate ?? 1 }};
            $('#totalDebtDisplay').text('{{ $currency->symbol }} ' + totalDebt.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            let totalDebtBs = totalDebt * exchangeRate;
            $('#totalBsDebtDisplay').text('Bs. ' + totalDebtBs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#totalDebtTableModal').text('{{ $currency->symbol }} ' + totalDebt.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#totalBsDebtTableModal').text('Bs. ' + totalDebtBs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }
        // Función para ordenar filas visibles SOLO en el modal
        function sortRowsModal() {
            const order = $('#orderFilter').val();
            const $tbody = $('.debt-modal-table').find('tbody');
            const rows = $('.customer-row-modal:visible').get();
            const $totalRow = $('#totalRowModal');
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
            $.each(rows, function(idx, row) {
                $tbody.append(row);
            });
            $tbody.append($totalRow);
        }
        // Función para aplicar filtros SOLO en el modal
        function applyFiltersModal() {
            let searchTerm = $('#searchFilter').val().toLowerCase();
            let debtMin = parseFloat($('#debtMinFilter').val());
            let debtMax = parseFloat($('#debtMaxFilter').val());
            if (isNaN(debtMin)) debtMin = 0;
            if (isNaN(debtMax)) debtMax = Infinity;
            $('.customer-row-modal').each(function() {
                let row = $(this);
                let name = row.data('name');
                let phone = row.data('phone');
                let email = row.data('email');
                let nit = row.data('nit');
                let debt = parseFloat(row.data('debt'));
                let showRow = true;
                if (searchTerm &&
                    !name.includes(searchTerm) &&
                    !phone.includes(searchTerm) &&
                    !email.includes(searchTerm) &&
                    !nit.includes(searchTerm)) {
                    showRow = false;
                }
                if (showRow && (debt < debtMin || debt > debtMax)) {
                    showRow = false;
                }
                if (showRow) {
                    row.show();
                } else {
                    row.hide();
                }
            });
            sortRowsModal();
            updateRowNumbersModal();
            updateSummaryModal();
            updatePdfLinks();
        }
        // Eventos reactivos para los filtros SOLO en el modal
        $('#searchFilter').on('input', function() {
            applyFiltersModal();
        });
        $('#debtMinFilter, #debtMaxFilter').on('input change', function() {
            applyFiltersModal();
        });
        $('#orderFilter').on('change', function() {
            sortRowsModal();
            updateRowNumbersModal();
            updateSortIcons();
            updatePdfLinks();
        });

        // Actualizar enlace PDF cuando cambie el tipo de cambio
        $('#modalExchangeRate').on('input change', function() {
            updatePdfLinks();
        });
        // Inicializar iconos de ordenamiento
        updateSortIcons();
        // Inicializar enlaces PDF
        updatePdfLinks();
        // Limpiar el modal al cerrarse
        $('#debtReportModal').on('hidden.bs.modal', function () {
            $('.customer-row-modal').show();
            $('#searchFilter').val('');
            $('#debtMinFilter').val('');
            $('#debtMaxFilter').val('');
            $('#orderFilter').val('debt_desc');
            updateSummaryModal();
            sortRowsModal();
        });
    });
</script> 