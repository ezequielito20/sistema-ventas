@extends('layouts.app')

@section('title', 'Pedido #'.$order->id)

@section('content')
    @can('orders.index')
        <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-cyan-400 hover:underline">&larr; Volver al listado</a>
            <h1 class="mt-4 text-2xl font-bold text-slate-100">Pedido #{{ $order->id }}</h1>
            <p class="text-sm text-slate-400">{{ $order->customer_name }} · {{ $order->customer_phone }}</p>

            <div class="mt-6 rounded-lg border border-slate-700 bg-slate-900/50 p-4">
                <p class="text-xs uppercase text-slate-500">Estado</p>
                <p class="text-lg font-semibold text-slate-100">{{ strtoupper($order->status) }}</p>
                <div class="mt-3 grid gap-2 text-sm text-slate-300 sm:grid-cols-2">
                    <div>Pago: {{ $order->paid_at ? $order->paid_at->format('d/m/Y H:i') : 'Pendiente' }}</div>
                    <div>Entrega: {{ $order->delivered_at ? $order->delivered_at->format('d/m/Y H:i') : 'Pendiente' }}</div>
                </div>
            </div>

            <div class="mt-6 rounded-lg border border-slate-700 bg-slate-900/50 p-4">
                <h2 class="text-sm font-semibold text-slate-200">Ítems</h2>
                <table class="mt-3 w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="pb-2">Producto</th>
                            <th class="pb-2">Cant.</th>
                            <th class="pb-2">P. unit USD</th>
                            <th class="pb-2">Total USD</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-200">
                        @foreach ($order->items as $item)
                            <tr class="border-t border-slate-800">
                                <td class="py-2">{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format($item->unit_price_usd, 2) }}</td>
                                <td>${{ number_format($item->line_total_usd, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <dl class="mt-4 space-y-1 text-sm text-slate-300">
                    <div class="flex justify-between"><dt>Subtotal productos</dt><dd>${{ number_format($order->subtotal_products_usd, 2) }}</dd></div>
                    <div class="flex justify-between"><dt>Descuento método de pago</dt><dd>-${{ number_format($order->payment_discount_amount_usd, 2) }}</dd></div>
                    <div class="flex justify-between"><dt>Delivery / zona</dt><dd>${{ number_format($order->delivery_fee_usd, 2) }}</dd></div>
                    <div class="flex justify-between text-base font-semibold text-slate-100"><dt>Total USD</dt><dd>${{ number_format($order->total_usd, 2) }}</dd></div>
                    <div class="flex justify-between text-base font-semibold text-cyan-300"><dt>Total Bs (tasa {{ $order->exchange_rate_used }})</dt><dd>Bs {{ number_format($order->total_bs, 2) }}</dd></div>
                </dl>
            </div>

            <div class="mt-6 rounded-lg border border-slate-700 bg-slate-900/50 p-4 text-sm text-slate-300 whitespace-pre-line">
                <h2 class="text-sm font-semibold text-slate-200">Pago</h2>
                <p class="mt-2">{{ $order->payment_method_snapshot }}</p>
                <h2 class="mt-4 text-sm font-semibold text-slate-200">Entrega</h2>
                <p class="mt-2">{{ $order->delivery_method_snapshot }}</p>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                @if ($order->status === 'pending')
                    @can('orders.update')
                        <form action="{{ route('admin.orders.paid', $order) }}" method="post">@csrf
                            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Marcar pagado</button>
                        </form>
                        <form action="{{ route('admin.orders.delivered', $order) }}" method="post">@csrf
                            <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-500">Marcar entregado</button>
                        </form>
                    @endcan
                    @can('orders.cancel')
                        <form action="{{ route('admin.orders.cancel', $order) }}" method="post" onsubmit="return confirm('¿Cancelar este pedido?');">@csrf
                            <button type="submit" class="rounded-lg bg-rose-700 px-4 py-2 text-sm font-medium text-white hover:bg-rose-600">Cancelar</button>
                        </form>
                    @endcan
                @endif
                @can('orders.update')
                    <a href="{{ $order->summaryUrl() }}" target="_blank" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">Abrir resumen público</a>
                    <form action="{{ route('admin.orders.regenerate-summary', $order) }}" method="post">@csrf
                        <button type="submit" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">Nuevo enlace de resumen</button>
                    </form>
                    <a href="{{ route('admin.orders.pdf', $order) }}" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">Descargar PDF</a>
                @endcan
            </div>
        </div>
    @endcan
@endsection
