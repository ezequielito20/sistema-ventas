<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-ui.stat-card variant="info" icon="fas fa-money-bill-wave" trend="Acumulado" label="Total pagos"
        :value="$currency->symbol . ' ' . number_format((float) $totalPayments, 2)" meta="Histórico" />
    <x-ui.stat-card variant="success" icon="fas fa-receipt" trend="Registros" label="Número de pagos"
        :value="number_format((int) $paymentsCount)" meta="Movimientos" />
    <x-ui.stat-card variant="warning" icon="fas fa-calculator" trend="Promedio" label="Pago promedio"
        :value="$currency->symbol . ' ' . number_format((float) $averagePayment, 2)" meta="Por transacción" />
    <x-ui.stat-card variant="danger" icon="fas fa-file-invoice-dollar" trend="Saldo" label="Deuda restante"
        :value="$currency->symbol . ' ' . number_format((float) $totalRemainingDebt, 2)" meta="Cartera actual" />
</div>
