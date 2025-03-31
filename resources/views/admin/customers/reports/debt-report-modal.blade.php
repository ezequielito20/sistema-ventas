<div class="modal-header">
    <h5 class="modal-title" id="debtReportModalLabel">Reporte de Deudas</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-group">
                <label for="modalExchangeRate">Tipo de Cambio (1 USD =)</label>
                <div class="input-group">
                    <input type="number" id="modalExchangeRate" class="form-control" step="0.01" min="0.01" value="1.00">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="updateModalExchangeRate">
                            <i class="fas fa-sync-alt mr-1"></i>Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-info">
                <strong>Deuda Total:</strong> {{ $currency->symbol }} {{ number_format($totalDebt, 2) }}
                <span class="modal-bs-debt" data-debt="{{ $totalDebt }}">
                    Bs. {{ number_format($totalDebt, 2) }}
                </span>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">{{ $company->name }}</h4>
            <p class="text-muted mb-0">Fecha: {{ date('d/m/Y H:i:s') }}</p>
        </div>
        <div class="d-flex align-items-center">
            
            <a href="{{ route('admin.customers.debt-report.download') }}" class="btn btn-sm btn-outline-danger" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i>Descargar PDF
            </a>
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