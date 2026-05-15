<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resumen pedido #{{ $order->id }}</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; padding: 24px; }
        .card { max-width: 640px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 24px; border: 1px solid #334155; }
        h1 { font-size: 1.25rem; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 0.9rem; }
        th, td { text-align: left; padding: 8px 4px; border-bottom: 1px solid #334155; }
        th { color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; }
        .tot { margin-top: 16px; font-size: 0.95rem; }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 16px; background: #0891b2; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; }
    </style>
</head>
<body>
<div class="card">
    <h1>{{ $order->company->name }}</h1>
    <p style="color:#94a3b8;margin:0;">Resumen de pedido #{{ $order->id }}</p>
    <p style="margin-top:12px;"><strong>Cliente:</strong> {{ $order->customer_name }}<br>
        <strong>Teléfono:</strong> {{ $order->customer_phone }}</p>
    <table>
        <thead>
            <tr><th>Producto</th><th>Cant.</th><th>P. unit</th><th>Total USD</th></tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->unit_price_usd, 2) }}</td>
                    <td>${{ number_format($item->line_total_usd, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="tot">
        <div>Subtotal productos: <strong>${{ number_format($order->subtotal_products_usd, 2) }}</strong></div>
        <div>Descuento pago: <strong>-${{ number_format($order->payment_discount_amount_usd, 2) }}</strong></div>
        <div>Entrega: <strong>${{ number_format($order->delivery_fee_usd, 2) }}</strong></div>
        <div style="margin-top:8px;font-size:1.1rem;">Total USD: <strong>${{ number_format($order->total_usd, 2) }}</strong></div>
        <div style="margin-top:4px;color:#22d3ee;">Total Bs (tasa {{ $order->exchange_rate_used }}): <strong>Bs {{ number_format($order->total_bs, 2) }}</strong></div>
    </div>
    <div style="margin-top:20px;font-size:0.85rem;color:#cbd5e1;white-space:pre-line;">
        <strong>Pago</strong><br>{{ $order->payment_method_snapshot }}
    </div>
    <div style="margin-top:12px;font-size:0.85rem;color:#cbd5e1;white-space:pre-line;">
        <strong>Entrega</strong><br>{{ $order->delivery_method_snapshot }}
    </div>
    <a class="btn" href="{{ route('order.summary.pdf', ['token' => $order->public_summary_token]) }}">Descargar PDF</a>
</div>
</body>
</html>
