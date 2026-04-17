@extends('pdf.layouts.document')

@section('pdf-document-title', 'Reporte de clientes')

@section('pdf-title', 'Directorio de clientes')

@section('pdf-subtitle')
    Resumen consolidado de clientes, actividad comercial y facturación acumulada.
@endsection

@push('pdf-module-styles')
    .pdf-table {
        font-size: 9.1pt;
    }
    .pdf-table thead th {
        font-size: 8.2pt;
        padding: 6pt 7pt;
    }
    .pdf-table tbody td {
        padding: 5.5pt 7pt;
    }
    .pdf-money {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .pdf-customer-name {
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        font-size: 9.4pt;
    }
    .pdf-customer-sub {
        margin: 1pt 0 0;
        font-size: 7.7pt;
        color: #64748b;
    }
@endpush

@section('pdf-content')
    @php
        $totalCustomers = $customers->count();
        $activeCustomers = $customers->where('sales_count', '>', 0)->count();
        $totalBilling = $customers->sum('total_sales_amount');
        $totalBillingBs = $totalBilling * (float) ($exchangeRate ?? 1);
    @endphp

    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Resumen:</strong>
                {{ $totalCustomers }} {{ $totalCustomers === 1 ? 'cliente registrado' : 'clientes registrados' }}
                · {{ $activeCustomers }} activos
                · Facturación acumulada: <strong>{{ $currency->symbol }} {{ number_format($totalBilling, 2) }}</strong>
                · Facturación en Bs: <strong>Bs. {{ number_format($totalBillingBs, 2) }}</strong>
                · Tasa aplicada: <strong>{{ number_format((float) ($exchangeRate ?? 1), 2) }} Bs/USD</strong>
            </td>
        </tr>
    </table>

    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;" class="pdf-num">#</th>
                <th style="width: 28%;">Cliente</th>
                <th style="width: 14%;">Documento</th>
                <th style="width: 15%;">Teléfono</th>
                <th style="width: 21%;">Correo</th>
                <th style="width: 11%; text-align: right;">Compras</th>
                <th style="width: 11%; text-align: right;">Monto en Bs</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td class="pdf-num">{{ $loop->iteration }}</td>
                    <td>
                        <p class="pdf-customer-name">{{ $customer->formatted_name ?: $customer->name }}</p>
                        @if ($customer->address)
                            <p class="pdf-customer-sub">{{ $customer->address }}</p>
                        @endif
                    </td>
                    <td>{{ $customer->nit_number ?: '—' }}</td>
                    <td>{{ $customer->formatted_phone ?: $customer->phone ?: '—' }}</td>
                    <td>{{ $customer->email ?: 'Sin correo' }}</td>
                    <td class="pdf-money">{{ $currency->symbol }} {{ number_format((float) $customer->total_sales_amount, 2) }}</td>
                    <td class="pdf-money">Bs. {{ number_format((float) $customer->total_sales_amount * (float) ($exchangeRate ?? 1), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('pdf-footer-module')
    Módulo: Clientes · Reporte general · Generado por: {{ Auth::user()->name ?? 'Usuario' }}
@endsection
