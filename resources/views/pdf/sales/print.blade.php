@extends('pdf.layouts.document')

@section('pdf-document-title', 'Factura ' . $sale->getFormattedInvoiceNumber())

@section('pdf-title', 'Factura de venta')

@section('pdf-subtitle')
    Documento oficial de transacción comercial · {{ $company->name }}
@endsection

@push('pdf-module-styles')
    .pdf-money {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .pdf-factura-meta {
        width: 100%;
        border-collapse: collapse;
        margin: 0 0 14pt 0;
        font-size: 9.5pt;
    }
    .pdf-factura-meta td {
        padding: 6pt 0;
        vertical-align: top;
        border-bottom: 1px solid #e2e8f0;
    }
    .pdf-factura-meta td:first-child {
        width: 35%;
        color: #64748b;
        font-weight: 600;
    }
    .pdf-factura-meta td:last-child {
        color: #0f172a;
    }
    .pdf-factura-totals {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10pt;
        font-size: 10pt;
    }
    .pdf-factura-totals td {
        padding: 5pt 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .pdf-factura-totals td:last-child {
        text-align: right;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
    }
    .pdf-factura-totals tr.total-final td {
        background: #0f172a;
        color: #fff;
        font-size: 11pt;
        font-weight: 700;
        padding: 7pt 9pt;
        border-bottom: none;
        border-radius: 0;
    }
    .pdf-factura-totals tr.total-final td:first-child {
        border-radius: 4px 0 0 4px;
    }
    .pdf-factura-totals tr.total-final td:last-child {
        border-radius: 0 4px 4px 0;
    }
    .pdf-productos-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9.5pt;
        border: 1px solid #e2e8f0;
    }
    .pdf-productos-table thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        text-align: left;
        padding: 7pt 9pt;
        font-size: 8.5pt;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #0f172a;
    }
    .pdf-productos-table thead th.pdf-num {
        text-align: center;
    }
    .pdf-productos-table tbody td {
        padding: 7pt 9pt;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    .pdf-productos-table tbody tr:last-child td {
        border-bottom: 2px solid #0f172a;
    }
    .pdf-producto-nombre {
        font-weight: 600;
        color: #0f172a;
    }
    .pdf-producto-codigo {
        font-size: 8pt;
        color: #94a3b8;
    }
@endpush

@section('pdf-content')
    @php
        $subtotal = $sale->subtotal_before_discount ?? $sale->total_price;
        $discount = $sale->general_discount_value ?? 0;
        $tax = $sale->total_price - $subtotal + $discount;
    @endphp

    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Factura N° {{ $sale->getFormattedInvoiceNumber() }}</strong>
                · Fecha: {{ \Carbon\Carbon::parse($sale->sale_date)->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                · {{ count($saleDetails) }} {{ count($saleDetails) === 1 ? 'ítem' : 'ítems' }}
                · Total: <strong>{{ $currency->symbol }} {{ number_format($sale->total_price, 2) }}</strong>
            </td>
        </tr>
    </table>

    <table class="pdf-factura-meta" cellspacing="0">
        <tr>
            <td>Cliente</td>
            <td><strong>{{ $customer->name ?? 'Consumidor Final' }}</strong></td>
        </tr>
        @if ($customer)
            @if ($customer->nit_number)
                <tr>
                    <td>CI / NIT</td>
                    <td>{{ $customer->nit_number }}</td>
                </tr>
            @endif
            @if ($customer->phone)
                <tr>
                    <td>Teléfono</td>
                    <td>{{ $customer->phone }}</td>
                </tr>
            @endif
            @if ($customer->address)
                <tr>
                    <td>Dirección</td>
                    <td>{{ $customer->address }}</td>
                </tr>
            @endif
        @endif
        @if ($sale->note)
            <tr>
                <td>Nota</td>
                <td>{{ $sale->note }}</td>
            </tr>
        @endif
    </table>

    <table class="pdf-productos-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;" class="pdf-num">#</th>
                <th style="width: 45%;">Producto</th>
                <th style="width: 12%;" class="pdf-num">Cant.</th>
                <th style="width: 18%;" class="pdf-num">Precio Unit.</th>
                <th style="width: 20%;" class="pdf-num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($saleDetails as $detail)
                @php
                    $unitPrice = $detail->unit_price ?? $detail->product->sale_price ?? 0;
                    $lineSubtotal = $detail->subtotal ?? ($detail->quantity * $unitPrice);
                @endphp
                <tr>
                    <td class="pdf-num">{{ $loop->iteration }}</td>
                    <td>
                        <div class="pdf-producto-nombre">{{ $detail->product->name ?? '—' }}</div>
                        <div class="pdf-producto-codigo">{{ $detail->product->code ?? '' }}</div>
                    </td>
                    <td class="pdf-num">{{ number_format($detail->quantity, 0) }}</td>
                    <td class="pdf-num">{{ $currency->symbol }} {{ number_format($unitPrice, 2) }}</td>
                    <td class="pdf-num">{{ $currency->symbol }} {{ number_format($lineSubtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="display: table; width: 100%; margin-top: 10pt;">
        <div style="display: table-cell; width: 55%;"></div>
        <div style="display: table-cell; width: 45%; vertical-align: top;">
            <table class="pdf-factura-totals" cellspacing="0">
                <tr>
                    <td>Subtotal</td>
                    <td>{{ $currency->symbol }} {{ number_format($subtotal, 2) }}</td>
                </tr>
                @if ($discount > 0)
                    <tr>
                        <td>Descuento</td>
                        <td style="color: #16a34a;">- {{ $currency->symbol }} {{ number_format($discount, 2) }}</td>
                    </tr>
                @endif
                @if ($company->tax_amount > 0)
                    <tr>
                        <td>{{ $company->tax_name ?? 'Impuesto' }} ({{ $company->tax_amount }}%)</td>
                        <td>{{ $currency->symbol }} {{ number_format($tax > 0 ? $tax : 0, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-final">
                    <td>Total</td>
                    <td>{{ $currency->symbol }} {{ number_format($sale->total_price, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection

@section('pdf-footer-module')
    Factura N° {{ $sale->getFormattedInvoiceNumber() }} · {{ $company->name }} · Generado por: {{ Auth::user()->name ?? 'Usuario' }}
@endsection
