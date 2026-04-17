@extends('pdf.layouts.document')

@section('pdf-document-title', 'Reporte de deudas de clientes')

@section('pdf-title', 'Reporte de deudas de clientes')

@section('pdf-subtitle')
    Listado de clientes con deuda pendiente en {{ $currency->symbol }} y su equivalente en bolívares según la tasa aplicada.
@endsection

@push('pdf-module-styles')
    .pdf-money {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .pdf-debt {
        color: #b91c1c;
        font-weight: 700;
    }
    .pdf-contact-sub {
        margin: 2pt 0 0;
        font-size: 8.25pt;
        color: #64748b;
    }
@endpush

@section('pdf-content')
    @php
        $hasBs = isset($exchangeRate) && (float) $exchangeRate > 0;
        $customersCount = $customers->count();
        $avgDebt = $customersCount > 0 ? ($totalDebt / $customersCount) : 0;
        $maxDebtCustomer = $customers->sortByDesc('total_debt')->first();
    @endphp

    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Resumen:</strong>
                {{ $customersCount }} {{ $customersCount === 1 ? 'cliente con deuda' : 'clientes con deuda' }}
                · Total pendiente: <strong>{{ $currency->symbol }} {{ number_format($totalDebt, 2) }}</strong>
                @if ($hasBs)
                    · Total en Bs: <strong>Bs. {{ number_format($totalDebt * (float) $exchangeRate, 2) }}</strong>
                    · Tasa aplicada: <strong>{{ number_format((float) $exchangeRate, 2) }} Bs/USD</strong>
                @endif
                · Promedio por cliente: <strong>{{ $currency->symbol }} {{ number_format($avgDebt, 2) }}</strong>
                @if ($maxDebtCustomer)
                    · Mayor deuda: <strong>{{ $maxDebtCustomer->name }} ({{ $currency->symbol }} {{ number_format((float) $maxDebtCustomer->total_debt, 2) }})</strong>
                @endif
            </td>
        </tr>
    </table>

    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 6%;" class="pdf-num">#</th>
                <th style="width: 28%;">Cliente</th>
                <th style="width: 24%;">Contacto</th>
                <th style="width: 20%; text-align: right;">Deuda USD</th>
                @if ($hasBs)
                    <th style="width: 22%; text-align: right;">Deuda Bs</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td class="pdf-num">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $customer->name }}</strong><br>
                        <span class="pdf-muted">{{ $customer->nit_number ?: 'Sin documento' }}</span>
                    </td>
                    <td>
                        {{ $customer->phone ?: 'Sin teléfono' }}
                        <p class="pdf-contact-sub">{{ $customer->email ?: 'Sin correo' }}</p>
                    </td>
                    <td class="pdf-money pdf-debt">{{ $currency->symbol }} {{ number_format((float) $customer->total_debt, 2) }}</td>
                    @if ($hasBs)
                        <td class="pdf-money pdf-debt">Bs. {{ number_format((float) $customer->total_debt * (float) $exchangeRate, 2) }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $hasBs ? 5 : 4 }}" class="pdf-num">No hay clientes con deudas pendientes</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('pdf-footer-module')
    Módulo: Clientes · Reporte de deudas · Generado por: {{ Auth::user()->name ?? 'Usuario' }}
@endsection