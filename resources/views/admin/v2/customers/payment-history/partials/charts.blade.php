<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <section class="ui-panel overflow-hidden">
        <div class="ui-panel__header">
            <h2 class="ui-panel__title">Pagos por Día</h2>
            <p class="ui-panel__subtitle">Distribución semanal de pagos.</p>
        </div>
        <div class="ui-panel__body h-[300px]">
            <canvas id="weekdayChart" height="250"></canvas>
        </div>
    </section>

    <section class="ui-panel overflow-hidden">
        <div class="ui-panel__header">
            <h2 class="ui-panel__title">Pagos por Mes</h2>
            <p class="ui-panel__subtitle">Tendencia mensual del año actual.</p>
        </div>
        <div class="ui-panel__body h-[300px]">
            <canvas id="monthlyChart" height="250"></canvas>
        </div>
    </section>
</div>
