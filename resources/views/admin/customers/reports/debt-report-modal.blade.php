<div class="modal-header bg-danger">
    <h5 class="modal-title text-white">
        <i class="fas fa-file-invoice-dollar mr-2"></i>Reporte de Deudas de Clientes
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">{{ $company->name }}</h4>
            <p class="text-muted mb-0">Fecha: {{ date('d/m/Y H:i:s') }}</p>
        </div>
        <div class="d-flex align-items-center">
            <div class="mr-3">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text">1 USD = </span>
                    </div>
                    <input type="number" id="exchangeRate" class="form-control" value="70.00" step="0.01" min="0">
                    <div class="input-group-append">
                        <span class="input-group-text">VES</span>
                        <button type="button" id="updateExchangeRate" class="btn btn-primary">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
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
</div>

<script>
    $(document).ready(function() {
        // Cargar el tipo de cambio guardado en localStorage (si existe)
        const savedRate = localStorage.getItem('exchangeRate');
        if (savedRate) {
            $('#exchangeRate').val(savedRate);
            updateBsValues(savedRate);
        }
        
        // Actualizar valores en Bs cuando se cambia el tipo de cambio
        $('#updateExchangeRate').click(function() {
            const rate = parseFloat($('#exchangeRate').val());
            if (rate > 0) {
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
            // Actualizar cada fila
            $('.bs-debt').each(function() {
                const debtUsd = parseFloat($(this).data('debt'));
                const debtBs = debtUsd * rate;
                $(this).html('Bs. ' + debtBs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            });
            
            // Actualizar el total
            const totalDebtUsd = {{ $totalDebt }};
            const totalDebtBs = totalDebtUsd * rate;
            $('#totalBsDebt').html('Bs. ' + totalDebtBs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }
    });
</script> 