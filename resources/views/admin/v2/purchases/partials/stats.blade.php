<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-ui.stat-card variant="info" icon="fas fa-boxes" trend="Catálogo" label="Productos únicos"
        :value="number_format((int) $totalPurchases)" meta="Con compras registradas" />
    <x-ui.stat-card variant="success" icon="fas fa-chart-line" trend="Capital" label="Total invertido"
        :value="$currency->symbol . ' ' . number_format((float) $totalAmount, 2)" meta="Histórico" />
    <x-ui.stat-card variant="warning" icon="fas fa-calendar-check" trend="Mes actual" label="Compras del mes"
        :value="number_format((int) $monthlyPurchases)" meta="Actividad reciente" />
    <x-ui.stat-card variant="danger" icon="fas fa-hourglass-half" trend="Pendientes" label="Entregas pendientes"
        :value="number_format((int) $pendingDeliveries)" meta="Sin recibo de pago" />
</div>
