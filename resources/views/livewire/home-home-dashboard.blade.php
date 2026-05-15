<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-100">Hogar</h1>
        <p class="text-sm text-slate-400 mt-1">Resumen de tu hogar</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-400">Productos</p>
                    <p class="text-2xl font-bold text-slate-100 mt-1">{{ $totalProducts }}</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-box text-blue-400"></i>
                </div>
            </div>
        </div>

        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-400">Stock bajo</p>
                    <p class="text-2xl font-bold text-rose-400 mt-1">{{ $lowStockProducts }}</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-rose-500/20 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-rose-400"></i>
                </div>
            </div>
        </div>

        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-400">Listas activas</p>
                    <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $activeLists }}</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-emerald-400"></i>
                </div>
            </div>
        </div>

        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-400">Gasto del mes</p>
                    <p class="text-2xl font-bold text-amber-400 mt-1">${{ number_format($monthlyExpenses, 2) }}</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-amber-500/20 flex items-center justify-center">
                    <i class="fas fa-wallet text-amber-400"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <h2 class="text-lg font-medium text-slate-100 mb-4">Últimos movimientos</h2>
            @if(count($recentMovements) > 0)
                <div class="space-y-3">
                    @foreach($recentMovements as $movement)
                        <div class="flex items-center justify-between py-2 border-b border-slate-700/30 last:border-0">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full {{ $movement['quantity'] < 0 ? 'bg-rose-500/20' : 'bg-emerald-500/20' }} flex items-center justify-center">
                                    <i class="fas {{ $movement['quantity'] < 0 ? 'fa-minus text-rose-400' : 'fa-plus text-emerald-400' }} text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-200">{{ $movement['product']['name'] ?? 'Producto' }}</p>
                                    <p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($movement['notes'] ?? $movement['type'], 40) }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-medium {{ $movement['quantity'] < 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                {{ $movement['quantity'] > 0 ? '+' : '' }}{{ $movement['quantity'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">Sin movimientos aún.</p>
            @endif
        </div>

        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <h2 class="text-lg font-medium text-slate-100 mb-4">Próximos vencimientos</h2>
            @if(count($upcomingBills) > 0)
                <div class="space-y-3">
                    @foreach($upcomingBills as $bill)
                        <div class="flex items-center justify-between py-2 border-b border-slate-700/30 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-slate-200">{{ $bill['service']['name'] ?? 'Servicio' }}</p>
                                <p class="text-xs text-slate-500">Vence: {{ \Carbon\Carbon::parse($bill['due_date'])->format('d/m/Y') }}</p>
                            </div>
                            <span class="text-sm font-medium text-slate-100">${{ number_format($bill['amount'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">Sin facturas próximas a vencer.</p>
            @endif
        </div>
    </div>

    @if($activeList)
    <div class="mt-6 bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-emerald-400"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-100">Lista de mercado activa</p>
                    <p class="text-xs text-slate-400">{{ $activeList['items_count'] }} items · {{ $activeList['purchased_count'] }} comprados · Est. ${{ number_format($activeList['total_estimated'], 2) }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.home.shopping-list.index') }}"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-700 text-slate-200 hover:bg-slate-600 transition-colors">
                    Ver lista
                </a>
                <a href="{{ route('admin.home.shopping-list.mobile') }}"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-500 transition-colors">
                    Modo supermercado
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
