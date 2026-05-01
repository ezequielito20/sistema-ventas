@extends('pdf.layouts.document')

@section('pdf-document-title', 'Informe de ventas')

@section('pdf-title', 'Informe de ventas')

@section('pdf-subtitle')
    Registro de transacciones comerciales: clientes, productos, montos y estados de pago.
@endsection

@push('pdf-module-styles')
    .pdf-money {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .pdf-badge--paid {
        background: #dcfce7;
        color: #15803d;
    }
    .pdf-badge--debt {
        background: #fef3c7;
        color: #b45309;
    }
@endpush

@section('pdf-content')
    @php
        $totalVentas = $sales->count();
        $sumaTotal = (float) $sales->sum('total_price');
        $promedio = $totalVentas > 0 ? $sumaTotal / $totalVentas : 0.0;
        $pagadas = $sales->filter(fn ($s) => (float) $s->total_price > 0 && $s->saleDetails->sum('quantity') > 0)->count();
    @endphp

    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Resumen:</strong>
                @if ($isLimited)
                    Mostrando {{ $totalVentas }} de {{ $totalCount }} ventas
                    · Monto en página: <strong>{{ $currency->symbol }} {{ number_format($sumaTotal, 2) }}</strong>
                @else
                    {{ $totalVentas }} {{ $totalVentas === 1 ? 'venta' : 'ventas' }}
                    · Monto total: <strong>{{ $currency->symbol }} {{ number_format($sumaTotal, 2) }}</strong>
                @endif
                · Promedio por operación: <strong>{{ $currency->symbol }} {{ number_format($promedio, 2) }}</strong>
            </td>
        </tr>
    </table>

    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 12%;">Fecha venta</th>
                <th style="width: 22%;">Cliente</th>
                <th style="width: 30%;">Productos</th>
                <th style="width: 10%;" class="pdf-num">Unid.</th>
                <th style="width: 14%;" class="pdf-money">Total</th>
                <th style="width: 8%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
                @php
                    $qty = (int) $sale->saleDetails->sum('quantity');
                    $names = $sale->saleDetails
                        ->map(fn ($d) => $d->product->name ?? '—')
                        ->filter()
                        ->unique()
                        ->values();
                    $productsLine = $names->isEmpty()
                        ? '—'
                        : \Illuminate\Support\Str::limit(implode(', ', $names->all()), 120);
                    $customerName = $sale->customer->name ?? 'Consumidor Final';
                    $hasDebt = $sale->customer && $sale->customer->total_debt > 0;
                @endphp
                <tr>
                    <td class="pdf-num">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ \Carbon\Carbon::parse($sale->sale_date)->timezone(config('app.timezone'))->format('d/m/Y') }}</strong>
                        <br>
                        <span class="pdf-muted" style="font-size: 8.25pt;">
                            {{ \Carbon\Carbon::parse($sale->sale_date)->timezone(config('app.timezone'))->format('H:i') }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ $customerName }}</strong>
                        @if ($sale->customer?->phone)
                            <br><span class="pdf-muted" style="font-size: 8.25pt;">{{ $sale->customer->phone }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="pdf-muted" style="font-size: 8.75pt;">{{ $productsLine }}</span>
                    </td>
                    <td class="pdf-num">{{ number_format($qty, 0, '.', ',') }}</td>
                    <td class="pdf-money">{{ $currency->symbol }} {{ number_format((float) $sale->total_price, 2) }}</td>
                    <td>
                        @if ($hasDebt)
                            <span class="pdf-badge pdf-badge--debt">Con deuda</span>
                        @else
                            <span class="pdf-badge pdf-badge--paid">Al día</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('pdf-footer-module')
    Módulo: Ventas · Informe de actividad comercial · Generado por: {{ Auth::user()->name ?? 'Usuario' }}
@endsection
