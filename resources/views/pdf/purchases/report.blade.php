@extends('pdf.layouts.document')

@section('pdf-document-title', 'Informe de compras')

@section('pdf-title', 'Informe de compras')

@section('pdf-subtitle')
    Registro de adquisiciones de la empresa: fechas, comprobantes, unidades y montos invertidos.
@endsection

@push('pdf-module-styles')
    .pdf-money {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .pdf-purchase-receipt {
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 2pt 0;
    }
    .pdf-purchase-sub {
        margin: 0;
        font-size: 8.25pt;
        color: #64748b;
    }
    .pdf-badge--success {
        background: #dcfce7;
        color: #15803d;
    }
    .pdf-badge--warning {
        background: #fef3c7;
        color: #b45309;
    }
@endpush

@section('pdf-content')
    @php
        $totalCompras = $purchases->count();
        $sumaTotal = (float) $purchases->sum('total_price');
        $promedio = $totalCompras > 0 ? $sumaTotal / $totalCompras : 0.0;
        $pendientes = $purchases->filter(fn ($p) => empty($p->payment_receipt))->count();
        $unidadesTotales = $purchases->sum(fn ($p) => (int) $p->details->sum('quantity'));
    @endphp

    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Resumen:</strong>
                {{ $totalCompras }} {{ $totalCompras === 1 ? 'compra' : 'compras' }}
                · {{ number_format($unidadesTotales, 0, '.', ',') }} unidades en líneas
                · Monto total: <strong>{{ $currency->symbol }} {{ number_format($sumaTotal, 2) }}</strong>
                · Promedio por operación: <strong>{{ $currency->symbol }} {{ number_format($promedio, 2) }}</strong>
                · {{ $pendientes }} {{ $pendientes === 1 ? 'pendiente de comprobante' : 'pendientes de comprobante' }}
            </td>
        </tr>
    </table>

    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 11%;">Fecha compra</th>
                <th style="width: 22%;">Comprobante / estado</th>
                <th style="width: 28%;">Productos</th>
                <th style="width: 9%;" class="pdf-num">Unid.</th>
                <th style="width: 14%;" class="pdf-money">Total</th>
                <th style="width: 12%;">Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchases as $purchase)
                @php
                    $qty = (int) $purchase->details->sum('quantity');
                    $names = $purchase->details
                        ->map(fn ($d) => $d->product->name ?? '—')
                        ->filter()
                        ->unique()
                        ->values();
                    $productsLine = $names->isEmpty()
                        ? '—'
                        : \Illuminate\Support\Str::limit(implode(', ', $names->all()), 120);
                    $completado = ! empty($purchase->payment_receipt);
                @endphp
                <tr>
                    <td class="pdf-num">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ \Carbon\Carbon::parse($purchase->purchase_date)->timezone(config('app.timezone'))->format('d/m/Y') }}</strong>
                        <br>
                        <span class="pdf-muted" style="font-size: 8.25pt;">
                            {{ \Carbon\Carbon::parse($purchase->purchase_date)->timezone(config('app.timezone'))->format('H:i') }}
                        </span>
                    </td>
                    <td>
                        <p class="pdf-purchase-receipt">{{ $purchase->payment_receipt ?: 'Sin recibo' }}</p>
                        <p class="pdf-purchase-sub">
                            @if ($completado)
                                <span class="pdf-badge pdf-badge--success">Completado</span>
                            @else
                                <span class="pdf-badge pdf-badge--warning">Pendiente</span>
                            @endif
                        </p>
                    </td>
                    <td>
                        <span class="pdf-muted" style="font-size: 8.75pt;">{{ $productsLine }}</span>
                    </td>
                    <td class="pdf-num">{{ number_format($qty, 0, '.', ',') }}</td>
                    <td class="pdf-money">{{ $currency->symbol }} {{ number_format((float) $purchase->total_price, 2) }}</td>
                    <td>
                        {{ $purchase->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('pdf-footer-module')
    Módulo: Compras · Informe de adquisiciones · Generado por: {{ Auth::user()->name ?? 'Usuario' }}
@endsection
