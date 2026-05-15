@extends('layouts.app')

@section('title', 'Pedido #'.$order->id)

@section('content')
    @can('orders.index')
        @php
            $status = $order->status;
            $statusStyles = match ($status) {
                'pending' => ['label' => 'Pendiente', 'badge' => 'bg-amber-500/15 text-amber-200 ring-amber-400/35', 'dot' => 'bg-amber-400'],
                'cancelled' => ['label' => 'Cancelado', 'badge' => 'bg-rose-500/15 text-rose-200 ring-rose-400/35', 'dot' => 'bg-rose-400'],
                'processed' => ['label' => 'Procesado', 'badge' => 'bg-emerald-500/15 text-emerald-200 ring-emerald-400/35', 'dot' => 'bg-emerald-400'],
                default => ['label' => ucfirst((string) $status), 'badge' => 'bg-slate-600/40 text-slate-200 ring-slate-500/30', 'dot' => 'bg-slate-400'],
            };
        @endphp
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <a href="{{ route('admin.orders.index') }}"
               class="group inline-flex items-center gap-2 text-sm font-medium text-cyan-400/95 transition-colors hover:text-cyan-300">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-800/90 text-cyan-400 ring-1 ring-slate-600/70 transition group-hover:bg-slate-700/90 group-hover:ring-cyan-500/30">
                    <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                </span>
                Volver al listado
            </a>

            {{-- Encabezado del pedido --}}
            <div class="relative mt-6 overflow-hidden rounded-2xl border border-slate-700/80 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 p-6 shadow-xl shadow-black/25 ring-1 ring-cyan-500/10 sm:p-8">
                <div class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-cyan-500/10 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-20 left-1/4 h-32 w-32 rounded-full bg-indigo-500/10 blur-3xl" aria-hidden="true"></div>

                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <span class="inline-block h-1 w-8 rounded-full bg-gradient-to-r from-cyan-500 to-indigo-500" aria-hidden="true"></span>
                            Pedido
                        </p>
                        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-50 sm:text-4xl">
                            #{{ $order->id }}
                        </h1>
                        <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm">
                            <span class="inline-flex items-center gap-2 rounded-lg bg-slate-800/70 px-3 py-2 text-slate-200 ring-1 ring-slate-600/50">
                                <i class="fas fa-user text-cyan-400/90" aria-hidden="true"></i>
                                {{ $order->customer_name }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-lg bg-slate-800/70 px-3 py-2 text-slate-200 ring-1 ring-slate-600/50">
                                <i class="fas fa-phone text-cyan-400/90" aria-hidden="true"></i>
                                {{ $order->customer_phone }}
                            </span>
                        </div>
                    </div>

                    @if ($order->created_at)
                        <div class="shrink-0 rounded-xl border border-slate-700/70 bg-slate-950/50 px-4 py-3 text-right text-xs text-slate-400 ring-1 ring-slate-800/80">
                            <p class="font-medium uppercase tracking-wide text-slate-500">Recibido</p>
                            <p class="mt-1 text-sm font-semibold text-slate-200">{{ $order->created_at->timezone(config('app.timezone'))->format('d/m/Y') }}</p>
                            <p class="text-slate-500">{{ $order->created_at->timezone(config('app.timezone'))->format('H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Estado --}}
            <div class="mt-8 rounded-2xl border border-slate-700/75 bg-slate-900/60 p-5 shadow-lg shadow-black/20 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estado</p>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-bold uppercase tracking-wide text-slate-100 ring-1 ring-inset {{ $statusStyles['badge'] }}">
                                <span class="h-2 w-2 rounded-full {{ $statusStyles['dot'] }}" aria-hidden="true"></span>
                                {{ $statusStyles['label'] }}
                            </span>
                        </div>
                    </div>
                    <div class="grid w-full gap-3 sm:w-auto sm:min-w-[320px] sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-700/60 bg-slate-950/40 p-4 ring-1 ring-slate-800/70">
                            <p class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <i class="fas fa-credit-card text-cyan-500/85" aria-hidden="true"></i>
                                Pago
                            </p>
                            <p class="mt-2 text-sm font-medium text-slate-200">
                                {{ $order->paid_at ? $order->paid_at->format('d/m/Y H:i') : 'Pendiente' }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-slate-700/60 bg-slate-950/40 p-4 ring-1 ring-slate-800/70">
                            <p class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <i class="fas fa-truck text-violet-400/90" aria-hidden="true"></i>
                                Entrega
                            </p>
                            <p class="mt-2 text-sm font-medium text-slate-200">
                                {{ $order->delivered_at ? $order->delivered_at->format('d/m/Y H:i') : 'Pendiente' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ítems --}}
            <div class="mt-8 rounded-2xl border border-slate-700/75 bg-slate-900/60 shadow-lg shadow-black/20 overflow-hidden">
                <div class="flex flex-col gap-1 border-b border-slate-700/70 bg-gradient-to-r from-slate-800/90 to-slate-900/80 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <h2 class="flex items-center gap-2 text-base font-semibold text-slate-100">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-500/15 text-cyan-400 ring-1 ring-cyan-500/25">
                            <i class="fas fa-shopping-basket text-sm" aria-hidden="true"></i>
                        </span>
                        Ítems
                    </h2>
                    <p class="text-xs text-slate-500">{{ $order->items->count() }} {{ $order->items->count() === 1 ? 'línea' : 'líneas' }}</p>
                </div>

                <div class="overflow-x-auto px-1 sm:px-0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-700/70 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                <th class="px-5 py-3 sm:pl-6">Producto</th>
                                <th class="whitespace-nowrap px-3 py-3 text-center">Cant.</th>
                                <th class="whitespace-nowrap px-3 py-3 text-right">P. unit USD</th>
                                <th class="whitespace-nowrap px-5 py-3 text-right sm:pr-6">Total USD</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-200">
                            @foreach ($order->items as $item)
                                <tr class="border-b border-slate-800/90 transition-colors hover:bg-slate-800/35">
                                    <td class="px-5 py-3.5 align-middle font-medium text-slate-100 sm:pl-6">{{ $item->product_name }}</td>
                                    <td class="px-3 py-3.5 text-center align-middle tabular-nums text-slate-300">{{ $item->quantity }}</td>
                                    <td class="whitespace-nowrap px-3 py-3.5 text-right align-middle tabular-nums text-slate-300">${{ number_format($item->unit_price_usd, 2) }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right align-middle tabular-nums font-medium text-slate-100 sm:pr-6">${{ number_format($item->line_total_usd, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totales --}}
                <div class="border-t border-slate-700/70 bg-gradient-to-br from-slate-950/80 via-slate-900/95 to-slate-950 px-5 py-5 sm:px-6 sm:py-6">
                    <dl class="ml-auto grid max-w-md gap-3 text-sm text-slate-300">
                        <div class="flex justify-between gap-6 border-b border-slate-700/50 pb-2">
                            <dt>Subtotal productos</dt>
                            <dd class="tabular-nums font-medium text-slate-100">${{ number_format($order->subtotal_products_usd, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-6 border-b border-slate-700/50 pb-2">
                            <dt>Descuento método de pago</dt>
                            <dd class="tabular-nums text-rose-300/95">-${{ number_format($order->payment_discount_amount_usd, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-6 border-b border-slate-700/50 pb-2">
                            <dt>Delivery / zona</dt>
                            <dd class="tabular-nums font-medium text-slate-100">${{ number_format($order->delivery_fee_usd, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-6 pt-1 text-base font-semibold text-slate-50">
                            <dt>Total USD</dt>
                            <dd class="tabular-nums">${{ number_format($order->total_usd, 2) }}</dd>
                        </div>
                        <div class="mt-2 flex justify-between gap-4 rounded-xl border border-cyan-500/35 bg-gradient-to-r from-cyan-500/[0.07] to-indigo-500/[0.05] px-4 py-3 text-base ring-1 ring-cyan-500/20">
                            <dt class="max-w-[60%] text-sm font-semibold leading-snug text-cyan-100/95">
                                Total Bs <span class="block text-xs font-normal text-cyan-200/65">tasa {{ $order->exchange_rate_used }}</span>
                            </dt>
                            <dd class="shrink-0 tabular-nums text-lg font-bold text-cyan-200">Bs {{ number_format($order->total_bs, 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Pago y entrega --}}
            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-700/75 bg-slate-900/60 p-5 shadow-md shadow-black/15 ring-1 ring-slate-800/50 sm:p-6">
                    <h2 class="flex items-center gap-3 border-b border-slate-700/60 pb-3 text-base font-semibold text-slate-100">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/12 text-emerald-400 ring-1 ring-emerald-500/25">
                            <i class="fas fa-money-bill-wave text-sm" aria-hidden="true"></i>
                        </span>
                        Pago
                    </h2>
                    <div class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $order->payment_method_snapshot ?: '—' }}</div>
                </div>

                <div class="rounded-2xl border border-slate-700/75 bg-slate-900/60 p-5 shadow-md shadow-black/15 ring-1 ring-slate-800/50 sm:p-6">
                    <h2 class="flex items-center gap-3 border-b border-slate-700/60 pb-3 text-base font-semibold text-slate-100">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/12 text-violet-300 ring-1 ring-violet-400/25">
                            <i class="fas fa-map-marker-alt text-sm" aria-hidden="true"></i>
                        </span>
                        Entrega
                    </h2>
                    <div class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $order->delivery_method_snapshot ?: '—' }}</div>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="mt-10 flex flex-wrap gap-3 rounded-2xl border border-dashed border-slate-700/55 bg-slate-900/30 p-5">
                @if ($order->status === 'pending')
                    @can('orders.update')
                        <form action="{{ route('admin.orders.paid', $order) }}" method="post">@csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-500">
                                <i class="fas fa-check-circle" aria-hidden="true"></i>
                                Marcar pagado
                            </button>
                        </form>
                        <form action="{{ route('admin.orders.delivered', $order) }}" method="post">@csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-sky-950/30 transition hover:bg-sky-500">
                                <i class="fas fa-box-open" aria-hidden="true"></i>
                                Marcar entregado
                            </button>
                        </form>
                    @endcan
                    @can('orders.cancel')
                        <form action="{{ route('admin.orders.cancel', $order) }}" method="post" onsubmit="return confirm('¿Cancelar este pedido?');">@csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rose-700 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-950/30 transition hover:bg-rose-600">
                                <i class="fas fa-times-circle" aria-hidden="true"></i>
                                Cancelar
                            </button>
                        </form>
                    @endcan
                @endif
                @can('orders.update')
                    <a href="{{ $order->summaryUrl() }}" target="_blank"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-600/80 bg-slate-800/50 px-4 py-2.5 text-sm font-medium text-slate-200 shadow-sm ring-1 ring-slate-600/40 transition hover:border-cyan-500/35 hover:bg-slate-800 hover:text-white">
                        <i class="fas fa-external-link-alt text-cyan-400/90" aria-hidden="true"></i>
                        Abrir resumen público
                    </a>
                    <form action="{{ route('admin.orders.regenerate-summary', $order) }}" method="post">@csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-600/80 bg-slate-800/50 px-4 py-2.5 text-sm font-medium text-slate-200 shadow-sm ring-1 ring-slate-600/40 transition hover:border-slate-500 hover:bg-slate-800 hover:text-white">
                            <i class="fas fa-link text-slate-400" aria-hidden="true"></i>
                            Nuevo enlace de resumen
                        </button>
                    </form>
                    <a href="{{ route('admin.orders.pdf', $order) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-600/80 bg-slate-800/50 px-4 py-2.5 text-sm font-medium text-slate-200 shadow-sm ring-1 ring-slate-600/40 transition hover:border-indigo-500/35 hover:bg-slate-800 hover:text-white">
                        <i class="fas fa-file-pdf text-rose-300/90" aria-hidden="true"></i>
                        Descargar PDF
                    </a>
                @endcan
            </div>
        </div>
    @endcan
@endsection
