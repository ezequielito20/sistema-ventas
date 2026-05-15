<div class="rounded-lg border border-slate-700 bg-slate-900/40 p-4">
    <div class="flex flex-wrap gap-2 border-b border-slate-800 pb-4">
        <button type="button" wire:click="$set('status', '')" class="rounded px-3 py-1 text-sm {{ $status === '' ? 'bg-cyan-600 text-white' : 'text-slate-400 hover:bg-slate-800' }}">Todos</button>
        <button type="button" wire:click="$set('status', 'pending')" class="rounded px-3 py-1 text-sm {{ $status === 'pending' ? 'bg-amber-600 text-white' : 'text-slate-400 hover:bg-slate-800' }}">Pendientes</button>
        <button type="button" wire:click="$set('status', 'processed')" class="rounded px-3 py-1 text-sm {{ $status === 'processed' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:bg-slate-800' }}">Procesados</button>
        <button type="button" wire:click="$set('status', 'cancelled')" class="rounded px-3 py-1 text-sm {{ $status === 'cancelled' ? 'bg-rose-600 text-white' : 'text-slate-400 hover:bg-slate-800' }}">Cancelados</button>
    </div>
    <div class="py-4">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar cliente, teléfono o # pedido…"
               class="w-full max-w-md rounded border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm text-slate-200">
            <thead class="border-b border-slate-700 text-slate-400">
                <tr>
                    <th class="py-2 pr-4">#</th>
                    <th class="py-2 pr-4">Cliente</th>
                    <th class="py-2 pr-4">Teléfono</th>
                    <th class="py-2 pr-4">Ítems</th>
                    <th class="py-2 pr-4">Total USD</th>
                    <th class="py-2 pr-4">Estado</th>
                    <th class="py-2 pr-4">Fecha</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-b border-slate-800/80" wire:key="order-{{ $order->id }}">
                        <td class="py-2 pr-4 font-mono">{{ $order->id }}</td>
                        <td class="py-2 pr-4">{{ $order->customer_name }}</td>
                        <td class="py-2 pr-4">{{ $order->customer_phone }}</td>
                        <td class="py-2 pr-4">{{ $order->items_count }}</td>
                        <td class="py-2 pr-4">${{ number_format($order->total_usd, 2) }}</td>
                        <td class="py-2 pr-4 uppercase text-xs">{{ $order->status }}</td>
                        <td class="py-2 pr-4 text-slate-400">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-2">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-cyan-400 hover:underline">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">No hay pedidos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
