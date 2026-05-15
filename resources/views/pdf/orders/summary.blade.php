@include('pdf.partials.styles-base')
<div style="padding: 16px; font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111;">
    <h1 style="font-size: 16px;">Resumen de pedido #{{ $order->id }}</h1>
    <p style="margin: 4px 0;">{{ $order->company->name ?? '' }}</p>
    <p style="margin: 4px 0;">Emitido: {{ $emittedAt->format('d/m/Y H:i') }}</p>
    <hr style="margin: 12px 0;">
    <p><strong>Cliente:</strong> {{ $order->customer_name }} — {{ $order->customer_phone }}</p>
    <table style="width:100%; border-collapse: collapse; margin-top: 12px;">
        <thead>
            <tr>
                <th style="text-align:left; border-bottom:1px solid #ccc;">Producto</th>
                <th style="text-align:right; border-bottom:1px solid #ccc;">Cant.</th>
                <th style="text-align:right; border-bottom:1px solid #ccc;">P. unit USD</th>
                <th style="text-align:right; border-bottom:1px solid #ccc;">Total USD</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td style="padding:4px 0; border-bottom:1px solid #eee;">{{ $item->product_name }}</td>
                    <td style="text-align:right; border-bottom:1px solid #eee;">{{ $item->quantity }}</td>
                    <td style="text-align:right; border-bottom:1px solid #eee;">{{ number_format($item->unit_price_usd, 2) }}</td>
                    <td style="text-align:right; border-bottom:1px solid #eee;">{{ number_format($item->line_total_usd, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top:12px;"><strong>Subtotal productos USD:</strong> {{ number_format($order->subtotal_products_usd, 2) }}</p>
    <p><strong>Descuento método de pago:</strong> -{{ number_format($order->payment_discount_amount_usd, 2) }} USD</p>
    <p><strong>Costo entrega / zona:</strong> {{ number_format($order->delivery_fee_usd, 2) }} USD</p>
    <p><strong>Total USD:</strong> {{ number_format($order->total_usd, 2) }}</p>
    <p><strong>Tasa Bs/USD usada:</strong> {{ $order->exchange_rate_used }}</p>
    <p><strong>Total Bs:</strong> {{ number_format($order->total_bs, 2) }}</p>
    <hr style="margin: 12px 0;">
    <p style="margin-top:8px;"><strong>Pago</strong></p>
    <p style="white-space: pre-line;">{{ $order->payment_method_snapshot }}</p>
    <p style="margin-top:8px;"><strong>Entrega</strong></p>
    <p style="white-space: pre-line;">{{ $order->delivery_method_snapshot }}</p>
</div>
