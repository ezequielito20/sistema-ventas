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
                        <a href="{{ route('admin.customers.debt-report.download') }}" class="btn btn-sm btn-danger" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i>Descargar PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Resumen:</strong> {{ $customers->count() }} clientes con deudas pendientes
        </div>
        <div class="text-right">
            <div class="font-weight-bold">Deuda Total: {{ $currency->symbol }} {{ number_format($totalDebt, 2) }}</div>
            <div class="modal-bs-debt" data-debt="{{ $totalDebt }}">
                Bs. {{ number_format($totalDebt, 2) }}
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Contacto</th>
                    <th>NIT</th>
                    <th>Deuda Total</th>
                    <th>Deuda en Bs</th>
                    <th>Última Compra</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $index => $customer)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>
                            {{ $customer->phone ?? 'N/A' }}<br>
                            <small class="text-muted">{{ $customer->email ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $customer->nit_number ?? 'N/A' }}</td>
                        <td class="text-right text-danger font-weight-bold">
                            {{ $currency->symbol }} {{ number_format($customer->total_debt, 2) }}
                        </td>
                        <td class="text-right text-danger font-weight-bold bs-debt" data-debt="{{ $customer->total_debt }}">
                            Bs. {{ number_format($customer->total_debt * 70, 2) }}
                        </td>
                        <td>
                            @if($customer->lastSale)
                                {{ $customer->lastSale->sale_date->format('d/m/Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No hay clientes con deudas pendientes</td>
                    </tr>
                @endforelse
                <tr class="bg-light font-weight-bold">
                    <td colspan="4" class="text-right">TOTAL DEUDA PENDIENTE:</td>
                    <td class="text-right text-danger">
                        {{ $currency->symbol }} {{ number_format($totalDebt, 2) }}
                    </td>
                    <td class="text-right text-danger" id="totalBsDebt">
                        Bs. {{ number_format($totalDebt * 70, 2) }}
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
    <a href="{{ route('admin.customers.report') }}" target="_blank" class="btn btn-primary">
        <i class="fas fa-file-pdf mr-1"></i>Generar PDF
    </a>
</div>

<script>
    // Este script se ejecutará cuando el modal se cargue
    $(document).ready(function() {
        console.log('Modal cargado, esperando valor de tipo de cambio');
    });
</script> 