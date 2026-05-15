<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-100">Finanzas</h1>
        <p class="text-sm text-slate-400 mt-1">Control de gastos del hogar</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <p class="text-sm text-slate-400">Ingresos del mes</p>
            <p class="text-2xl font-bold text-emerald-400 mt-1">${{ number_format($monthlyTotals['income'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <p class="text-sm text-slate-400">Gastos del mes</p>
            <p class="text-2xl font-bold text-rose-400 mt-1">${{ number_format($monthlyTotals['expense'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <p class="text-sm text-slate-400">Balance</p>
            <p class="text-2xl font-bold {{ ($monthlyTotals['balance'] ?? 0) >= 0 ? 'text-blue-400' : 'text-rose-400' }} mt-1">
                ${{ number_format($monthlyTotals['balance'] ?? 0, 2) }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <h2 class="text-lg font-medium text-slate-100 mb-4">Gastos por categoría</h2>
            @if(count($expensesByCategory) > 0)
                <div class="space-y-3">
                    @foreach($expensesByCategory as $category => $total)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-300">{{ ucfirst($category) }}</span>
                            <div class="flex items-center gap-2">
                                <div class="h-2 rounded-full bg-blue-500" style="width: {{ max(2, ($total / max(array_sum($expensesByCategory), 1)) * 200) }}px"></div>
                                <span class="text-sm text-slate-400 w-20 text-right">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">Sin gastos registrados este mes.</p>
            @endif
        </div>

        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <h2 class="text-lg font-medium text-slate-100 mb-4">Evolución mensual</h2>
            @if(count($incomeExpenseTrend) > 0)
                <div class="space-y-2">
                    @foreach($incomeExpenseTrend as $month)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400 w-20">{{ $month['label'] }}</span>
                            <span class="text-emerald-400">+${{ number_format($month['income'], 0) }}</span>
                            <span class="text-rose-400">-${{ number_format($month['expense'], 0) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">Sin datos para mostrar.</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-medium text-slate-100">Próximos vencimientos</h2>
                <a href="{{ route('admin.home.finances.bills') }}" class="text-xs text-blue-400 hover:text-blue-300">
                    Ver todas
                </a>
            </div>
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

        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <h2 class="text-lg font-medium text-slate-100 mb-4">Accesos rápidos</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('admin.home.finances.services') }}"
                    class="flex items-center gap-3 p-3 rounded-lg bg-slate-700/30 hover:bg-slate-700/50 transition-colors">
                    <i class="fas fa-bolt text-yellow-400"></i>
                    <span class="text-sm text-slate-200">Servicios</span>
                </a>
                <a href="{{ route('admin.home.finances.bills') }}"
                    class="flex items-center gap-3 p-3 rounded-lg bg-slate-700/30 hover:bg-slate-700/50 transition-colors">
                    <i class="fas fa-file-invoice text-blue-400"></i>
                    <span class="text-sm text-slate-200">Facturas</span>
                </a>
                <a href="{{ route('admin.home.finances.transactions') }}"
                    class="flex items-center gap-3 p-3 rounded-lg bg-slate-700/30 hover:bg-slate-700/50 transition-colors">
                    <i class="fas fa-exchange-alt text-green-400"></i>
                    <span class="text-sm text-slate-200">Transacciones</span>
                </a>
                <a href="{{ route('admin.home.finances.accounts') }}"
                    class="flex items-center gap-3 p-3 rounded-lg bg-slate-700/30 hover:bg-slate-700/50 transition-colors">
                    <i class="fas fa-university text-purple-400"></i>
                    <span class="text-sm text-slate-200">Cuentas</span>
                </a>
            </div>
        </div>
    </div>
</div>
