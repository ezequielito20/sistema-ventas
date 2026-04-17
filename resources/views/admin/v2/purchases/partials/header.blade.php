<section class="ui-panel overflow-hidden">
    <div class="ui-panel__header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="ui-panel__title">Compras</h1>
            <p class="ui-panel__subtitle">Control de adquisiciones, montos y estado de entrega en tiempo real.</p>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2">
            @if ($permissions['can_report'])
                <a href="{{ route('admin.purchases.report') }}" target="_blank" rel="noopener"
                    class="ui-btn ui-btn-ghost text-sm">
                    <i class="fas fa-file-pdf"></i> Reporte PDF
                </a>
            @endif
            @if ($cashCount)
                @if ($permissions['can_create'])
                    <a href="{{ route('admin.purchases.create') }}" class="ui-btn ui-btn-primary text-sm">
                        <i class="fas fa-plus"></i> Nueva compra
                    </a>
                @endif
            @else
                @if ($permissions['can_create'])
                    <a href="{{ route('admin.cash-counts.create') }}" class="ui-btn ui-btn-danger text-sm">
                        <i class="fas fa-cash-register"></i> Abrir caja
                    </a>
                @endif
            @endif
        </div>
    </div>
</section>
