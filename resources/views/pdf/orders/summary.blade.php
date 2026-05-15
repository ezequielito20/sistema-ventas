@extends('pdf.layouts.document')

@section('pdf-document-title', 'Resumen de pedido')

@section('pdf-title')
    {{ __('Resumen de pedido') }} #{{ $order->id }}
@endsection

@section('pdf-subtitle')
    Pedido desde el catálogo público. Montos expresados en USD y equivalencia en bolívares según la tasa registrada en el momento del pedido.
@endsection

@push('pdf-module-styles')
    .pdf-money {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .pdf-totals {
        width: 100%;
        max-width: 320pt;
        margin-left: auto;
        margin-top: 10pt;
        border-collapse: collapse;
        font-size: 10pt;
    }
    .pdf-totals td {
        padding: 4pt 0;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    .pdf-totals td:first-child {
        color: #64748b;
    }
    .pdf-totals tr:last-child td {
        border-bottom: none;
        font-weight: bold;
        font-size: 11pt;
        padding-top: 8pt;
        color: #0f172a;
    }
@endpush

@section('pdf-content')
    @php
        $statusLabels = [
            'pending' => ['label' => 'Pendiente', 'badge' => 'pdf-badge--system'],
            'confirmed' => ['label' => 'Confirmado', 'badge' => ''],
            'cancelled' => ['label' => 'Cancelado', 'badge' => 'pdf-badge--system'],
            'processed' => ['label' => 'Procesado', 'badge' => ''],
        ];
        $st = $statusLabels[$order->status] ?? ['label' => ucfirst((string) $order->status), 'badge' => 'pdf-badge--system'];
        $createdAt = $order->created_at?->timezone(config('app.timezone'));
    @endphp

    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="vertical-align: top;">
                            <strong>{{ __('Cliente') }}:</strong> {{ $order->customer_name }}
                            &nbsp;·&nbsp;
                            <strong>{{ __('Teléfono') }}:</strong> {{ $order->customer_phone }}
                            @if ($createdAt)
                                &nbsp;·&nbsp;
                                <span class="pdf-muted">{{ __('Registrado') }}: {{ $createdAt->format('d/m/Y H:i') }}</span>
                            @endif
                            @if ($order->scheduled_delivery_date)
                                <br><span class="pdf-muted">{{ __('Entrega programada') }}:</span>
                                {{ $order->scheduled_delivery_date->format('d/m/Y') }}
                            @endif
                            @if (trim((string) ($order->notes ?? '')) !== '')
                                <br><span class="pdf-muted">{{ __('Notas') }}:</span> {{ $order->notes }}
                            @endif
                        </td>
                        <td style="width: 110pt; vertical-align: top; text-align: right; white-space: nowrap;">
                            <span class="pdf-badge {{ $st['badge'] }}">{{ $st['label'] }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 44%;">{{ __('Producto') }}</th>
                <th style="width: 11%;" class="pdf-num">{{ __('Cant.') }}</th>
                <th style="width: 21%;" class="pdf-money">{{ __('P. unit USD') }}</th>
                <th style="width: 24%;" class="pdf-money">{{ __('Total USD') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td><strong>{{ $item->product_name }}</strong></td>
                    <td class="pdf-num">{{ $item->quantity }}</td>
                    <td class="pdf-money">{{ number_format((float) $item->unit_price_usd, 2) }}</td>
                    <td class="pdf-money">{{ number_format((float) $item->line_total_usd, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="pdf-totals" cellspacing="0">
        <tr>
            <td>{{ __('Subtotal productos (USD)') }}</td>
            <td class="pdf-money">{{ number_format((float) $order->subtotal_products_usd, 2) }}</td>
        </tr>
        <tr>
            <td>{{ __('Descuento método de pago (USD)') }}</td>
            <td class="pdf-money">-{{ number_format((float) $order->payment_discount_amount_usd, 2) }}</td>
        </tr>
        <tr>
            <td>{{ __('Costo entrega / zona (USD)') }}</td>
            <td class="pdf-money">{{ number_format((float) $order->delivery_fee_usd, 2) }}</td>
        </tr>
        <tr>
            <td>{{ __('Tasa Bs/USD utilizada') }}</td>
            <td class="pdf-money">{{ $order->exchange_rate_used }}</td>
        </tr>
        <tr>
            <td>{{ __('Total (USD)') }}</td>
            <td class="pdf-money">{{ number_format((float) $order->total_usd, 2) }}</td>
        </tr>
        <tr>
            <td>{{ __('Total (Bs)') }}</td>
            <td class="pdf-money">{{ number_format((float) $order->total_bs, 2) }}</td>
        </tr>
    </table>

    <table class="pdf-summary" cellspacing="0" style="margin-top: 16pt;">
        <tr>
            <td>
                <strong>{{ __('Pago') }}</strong>
                <div class="pdf-muted" style="margin-top: 4pt; white-space: pre-line;">{{ trim((string) ($order->payment_method_snapshot ?? '')) ?: '—' }}</div>
            </td>
        </tr>
    </table>

    <table class="pdf-summary" cellspacing="0" style="margin-top: 10pt;">
        <tr>
            <td>
                <strong>{{ __('Entrega') }}</strong>
                <div class="pdf-muted" style="margin-top: 4pt; white-space: pre-line;">{{ trim((string) ($order->delivery_method_snapshot ?? '')) ?: '—' }}</div>
            </td>
        </tr>
    </table>
@endsection

@section('pdf-footer-module')
    {{ __('Módulo: Pedidos · Resumen del pedido desde catálogo') }}
@endsection
