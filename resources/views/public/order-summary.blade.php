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
        .btn { display: inline-block; margin-top: 12px; padding: 10px 16px; background: #0891b2; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; font-size: 0.9rem; }
        .btn-danger { background: #be123c; }
        .btn-ghost { background: transparent; border: 1px solid #475569; color: #cbd5e1; }
        .alert { margin-top: 16px; padding: 12px 14px; border-radius: 8px; font-size: 0.875rem; }
        .alert-success { background: #064e3b; border: 1px solid #10b981; color: #a7f3d0; }
        .alert-error { background: #450a0a; border: 1px solid #f87171; color: #fecaca; }
        .alert-info { background: #0c4a6e; border: 1px solid #38bdf8; color: #bae6fd; }
        .cancel-box { margin-top: 24px; padding-top: 20px; border-top: 1px solid #334155; }
        .field { margin-top: 12px; }
        .field label { display: block; font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; margin-bottom: 4px; }
        .field input[type="text"] { width: 100%; box-sizing: border-box; padding: 10px 12px; border-radius: 8px; border: 1px solid #475569; background: #0f172a; color: #e2e8f0; font-size: 0.95rem; }
        .field-error { color: #fca5a5; font-size: 0.8rem; margin-top: 4px; }
        .check-row { display: flex; gap: 8px; align-items: flex-start; margin-top: 12px; font-size: 0.85rem; color: #cbd5e1; }
        .status-badge { display: inline-block; margin-top: 8px; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .status-pending { background: #422006; color: #fcd34d; }
        .status-cancelled { background: #4c0519; color: #fda4af; }
        .status-processed { background: #064e3b; color: #6ee7b7; }
    </style>
</head>
<body>
<div class="card">
    <h1>{{ $order->company->name }}</h1>
    <p style="color:#94a3b8;margin:0;">Resumen de pedido #{{ $order->id }}</p>

    @php
        $statusClass = match ($order->status) {
            'cancelled' => 'status-cancelled',
            'processed' => 'status-processed',
            default => 'status-pending',
        };
        $statusLabel = match ($order->status) {
            'cancelled' => 'Cancelado',
            'processed' => 'Procesado',
            default => 'Pendiente',
        };
    @endphp
    <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>

    @if (session('order_cancelled'))
        <div class="alert alert-success">
            El pedido #{{ $order->id }} fue cancelado. El stock quedó liberado y podés hacer un nuevo pedido desde el catálogo (si no alcanzaste el límite de pedidos por hora desde tu conexión).
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p style="margin-top:12px;"><strong>Cliente:</strong> {{ $order->customer_name }}<br>
        <strong>Teléfono:</strong> {{ $order->customer_phone }}</p>

    @if ($order->status !== 'cancelled')
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
            @php $pendingShip = $order->isCatalogShippingFeePending(); @endphp
            <div>Subtotal productos: <strong>${{ number_format($order->subtotal_products_usd, 2) }}</strong></div>
            <div>Descuento pago: <strong>-${{ number_format($order->payment_discount_amount_usd, 2) }}</strong></div>
            @if ($pendingShip)
                <div>Entrega: <strong>Por confirmar (+ envío)</strong></div>
                <div style="margin-top:8px;font-size:1.1rem;">Total USD: <strong>${{ number_format((float) $order->total_usd, 2) }} + envío</strong></div>
                <div style="margin-top:4px;color:#22d3ee;">Total Bs (tasa {{ $order->exchange_rate_used }}): <strong>Bs {{ number_format((float) $order->total_bs, 2) }} + envío</strong></div>
                <p style="margin-top:10px;font-size:0.8rem;color:#94a3b8;">El monto en bolívares corresponde solo a productos y descuento; el envío se cotiza aparte cuando la tienda confirme.</p>
            @else
                <div>Entrega: <strong>${{ number_format($order->delivery_fee_usd, 2) }}</strong></div>
                <div style="margin-top:8px;font-size:1.1rem;">Total USD: <strong>${{ number_format($order->total_usd, 2) }}</strong></div>
                <div style="margin-top:4px;color:#22d3ee;">Total Bs (tasa {{ $order->exchange_rate_used }}): <strong>Bs {{ number_format((float) $order->total_bs, 2) }}</strong></div>
            @endif
        </div>
        <div style="margin-top:20px;font-size:0.85rem;color:#cbd5e1;white-space:pre-line;">
            <strong>Pago</strong><br>{{ $order->payment_method_snapshot }}
        </div>
        <div style="margin-top:12px;font-size:0.85rem;color:#cbd5e1;white-space:pre-line;">
            <strong>Entrega</strong><br>{{ $order->delivery_method_snapshot }}
        </div>
        <a class="btn" href="{{ route('order.summary.pdf', ['token' => $order->public_summary_token]) }}">Descargar PDF</a>
    @endif

    @if ($order->canBeCancelledByCustomer())
        @php $deadline = $order->customerCancelDeadline(); @endphp
        <div class="cancel-box">
            <h2 style="font-size:1rem;margin:0 0 8px;">Cancelar pedido</h2>
            <p style="font-size:0.85rem;color:#94a3b8;margin:0 0 12px;">
                Si te equivocaste, podés cancelar este pedido hasta las {{ $deadline?->format('H:i') }} del {{ $deadline?->format('d/m/Y') }}
                ({{ $cancelWindowMinutes }} minutos desde que lo enviaste). Deberás ingresar el mismo teléfono del pedido.
            </p>
            <form method="post" action="{{ route('order.summary.cancel', ['token' => $order->public_summary_token]) }}"
                  onsubmit="return confirm('¿Cancelar el pedido #{{ $order->id }}? Esta acción no se puede deshacer.');">
                @csrf
                <div class="field">
                    <label for="customer_phone">Teléfono del pedido (11 dígitos)</label>
                    <input type="text" id="customer_phone" name="customer_phone" inputmode="numeric" maxlength="11"
                           value="{{ old('customer_phone') }}" placeholder="04148965789" required autocomplete="tel">
                    @error('customer_phone')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <label class="check-row">
                    <input type="checkbox" name="confirm_cancel" value="1" {{ old('confirm_cancel') ? 'checked' : '' }} required>
                    <span>Confirmo que deseo cancelar el pedido #{{ $order->id }}.</span>
                </label>
                @error('confirm_cancel')
                    <p class="field-error">{{ $message }}</p>
                @enderror
                <button type="submit" class="btn btn-danger">Cancelar pedido</button>
            </form>
        </div>
    @elseif ($order->status === 'pending')
        <div class="alert alert-info" style="margin-top:20px;">
            {{ $order->customerCancelBlockedReason() }}
        </div>
    @endif

    @if ($order->company->slug ?? false)
        <a class="btn btn-ghost" href="{{ route('catalog.index', ['company' => $order->company->slug]) }}">Volver al catálogo</a>
    @endif
</div>
</body>
</html>
