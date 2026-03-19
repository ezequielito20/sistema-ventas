@if (count($sales) === 0)
    <tr>
        <td colspan="4" class="px-4 py-12 text-center">
            <div class="flex flex-col items-center space-y-3">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-info-circle text-2xl text-gray-400"></i>
                </div>
                <p class="text-gray-500">No hay ventas registradas</p>
            </div>
        </td>
    </tr>
@else
    @foreach ($sales as $sale)
        <tr class="hover:bg-gray-50 transition-colors duration-150 border-b border-gray-100 last:border-0">
            <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                {{ $sale['date'] }}
            </td>
            <td class="px-4 py-3 text-sm text-gray-600 products-cell">
                {!! $sale['products'] !!}
            </td>
            <td class="px-4 py-3 text-sm text-right">
                <div class="font-semibold text-gray-900">
                    {{ $currencySymbol }} {{ number_format($sale['total'], 2) }}
                </div>
                @if (!$sale['is_paid'])
                    <div class="text-xs text-red-500 font-medium mt-0.5">
                        Pendiente: {{ $currencySymbol }} {{ number_format($sale['remaining_debt'], 2) }}
                    </div>
                @else
                    <div
                        class="text-[10px] text-green-600 font-bold uppercase tracking-wider mt-0.5 bg-green-50 px-1.5 py-0.5 rounded inline-block">
                        Pagado
                    </div>
                @endif
            </td>
            <td class="px-4 py-3 text-center">
                @if ($sale['is_paid'])
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check text-[10px]"></i>
                    </span>
                @else
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-600">
                        <i class="fas fa-clock text-[10px]"></i>
                    </span>
                @endif
            </td>
        </tr>
    @endforeach
@endif
