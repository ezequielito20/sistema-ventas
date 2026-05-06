<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $sale->getFormattedInvoiceNumber() }}</title>
    <style>
        @page {
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #1e293b;
            background: #fff;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            position: relative;
        }
        .invoice-header {
            display: table;
            width: 100%;
            margin-bottom: 8mm;
        }
        .invoice-header-col {
            display: table-cell;
            vertical-align: top;
        }
        .invoice-header-col--left {
            width: 40%;
        }
        .invoice-header-col--center {
            width: 20%;
            text-align: center;
            vertical-align: middle;
        }
        .invoice-header-col--right {
            width: 40%;
            text-align: right;
        }
        .company-logo {
            max-width: 70px;
            max-height: 50px;
            object-fit: contain;
            margin-bottom: 6px;
        }
        .company-name {
            font-size: 13pt;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        .company-meta {
            font-size: 8.5pt;
            color: #64748b;
            line-height: 1.4;
            margin-top: 3px;
        }
        .invoice-badge {
            display: inline-block;
            background: #0f172a;
            color: #fff;
            font-size: 11pt;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 8px 20px;
            border-radius: 4px;
        }
        .invoice-number {
            font-size: 9pt;
            color: #64748b;
            margin-top: 6px;
        }
        .invoice-number strong {
            color: #0f172a;
            font-size: 11pt;
        }
        .invoice-date {
            font-size: 9pt;
            color: #64748b;
            margin-top: 4px;
        }
        .divider {
            height: 3px;
            background: #0f172a;
            border-radius: 2px;
            margin: 6mm 0;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 8mm;
        }
        .info-grid-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10mm;
        }
        .info-grid-col:last-child {
            padding-right: 0;
            padding-left: 10mm;
            border-left: 1px solid #e2e8f0;
        }
        .info-label {
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 10pt;
            color: #0f172a;
            line-height: 1.4;
        }
        .info-value strong {
            font-size: 11pt;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6mm 0;
            font-size: 9.5pt;
        }
        .products-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 8px 10px;
            text-align: left;
            border-bottom: 2px solid #0f172a;
        }
        .products-table thead th.text-right {
            text-align: right;
        }
        .products-table tbody td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .products-table tbody tr:last-child td {
            border-bottom: 2px solid #0f172a;
        }
        .product-name {
            font-weight: 600;
            color: #0f172a;
        }
        .product-code {
            font-size: 8pt;
            color: #94a3b8;
        }
        .text-right {
            text-align: right;
        }
        .tabular {
            font-variant-numeric: tabular-nums;
        }
        .totals-section {
            display: table;
            width: 100%;
            margin-top: 4mm;
        }
        .totals-spacer {
            display: table-cell;
            width: 60%;
        }
        .totals-box {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        .totals-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals-table td:last-child {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }
        .totals-table tr.total-row td {
            background: #0f172a;
            color: #fff;
            font-size: 11pt;
            font-weight: 700;
            border-bottom: none;
            border-radius: 0 0 4px 4px;
        }
        .totals-table tr.total-row td:first-child {
            border-radius: 4px 0 0 4px;
        }
        .notes-section {
            margin-top: 10mm;
            padding: 5mm;
            background: #f8fafc;
            border-radius: 4px;
            font-size: 8.5pt;
            color: #64748b;
        }
        .notes-section strong {
            color: #475569;
        }
        .footer {
            position: absolute;
            bottom: 15mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4mm;
        }
        .footer-brand {
            font-weight: 700;
            color: #475569;
            margin-bottom: 2px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 60pt;
            font-weight: 900;
            color: rgba(0,0,0,0.03);
            letter-spacing: 0.2em;
            text-transform: uppercase;
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="watermark">{{ $company->name }}</div>

        {{-- Header --}}
        <div class="invoice-header">
            <div class="invoice-header-col invoice-header-col--left">
                @php
                    $printLogoSrc = null;
                    if ($company->logo) {
                        $relative = str_starts_with($company->logo, 'storage/') ? substr($company->logo, strlen('storage/')) : $company->logo;
                        if (Storage::disk('public')->exists($relative)) {
                            $printLogoSrc = 'file://' . storage_path('app/public/' . $relative);
                        } else {
                            try {
                                $disk = Storage::disk(config('filesystems.default', 'public'));
                                if ($disk->exists($relative)) {
                                    $content = $disk->get($relative);
                                    $mime = 'image/' . pathinfo($relative, PATHINFO_EXTENSION);
                                    $printLogoSrc = 'data:' . $mime . ';base64,' . base64_encode($content);
                                }
                            } catch (\Throwable) {}
                        }
                    }
                @endphp
                @if ($printLogoSrc)
                    <img src="{{ $printLogoSrc }}" alt="Logo" class="company-logo">
                @endif
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-meta">
                    @if ($company->address){{ $company->address }}<br>@endif
                    @if ($company->phone)Tel: {{ $company->phone }}<br>@endif
                    @if ($company->nit)RIF/NIT: {{ $company->nit }}<br>@endif
                    @if ($company->email){{ $company->email }}@endif
                </div>
            </div>
            <div class="invoice-header-col invoice-header-col--center">
                <div class="invoice-badge">Factura</div>
            </div>
            <div class="invoice-header-col invoice-header-col--right">
                <div class="invoice-number">
                    <strong>N° {{ $sale->getFormattedInvoiceNumber() }}</strong>
                </div>
                <div class="invoice-date">
                    <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}<br>
                    <strong>Hora:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('H:i') }}
                </div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Cliente y Vendedor --}}
        <div class="info-grid">
            <div class="info-grid-col">
                <div class="info-label">Facturado a</div>
                <div class="info-value">
                    <strong>{{ $customer->name ?? 'Consumidor Final' }}</strong><br>
                    @if ($customer)
                        @if ($customer->nit_number)CI/NIT: {{ $customer->nit_number }}<br>@endif
                        @if ($customer->phone)Tel: {{ $customer->phone }}<br>@endif
                        @if ($customer->address){{ $customer->address }}@endif
                    @endif
                </div>
            </div>
            <div class="info-grid-col">
                <div class="info-label">Información de pago</div>
                <div class="info-value">
                    <strong>Método:</strong> Efectivo / Transferencia<br>
                    <strong>Estado:</strong> {{ $sale->total_price > 0 ? 'Completado' : 'Pendiente' }}<br>
                    @if ($sale->note)<strong>Nota:</strong> {{ $sale->note }}@endif
                </div>
            </div>
        </div>

        {{-- Productos --}}
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 42%;">Producto</th>
                    <th style="width: 12%;" class="text-right">Cant.</th>
                    <th style="width: 18%;" class="text-right">Precio Unit.</th>
                    <th style="width: 20%;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($saleDetails as $detail)
                    <tr>
                        <td class="tabular">{{ $loop->iteration }}</td>
                        <td>
                            <div class="product-name">{{ $detail->product->name ?? '—' }}</div>
                            <div class="product-code">{{ $detail->product->code ?? '' }}</div>
                        </td>
                        <td class="text-right tabular">{{ number_format($detail->quantity, 0) }}</td>
                        <td class="text-right tabular">{{ $currency->symbol }} {{ number_format($detail->unit_price ?? $detail->product->sale_price ?? 0, 2) }}</td>
                        <td class="text-right tabular">{{ $currency->symbol }} {{ number_format($detail->subtotal ?? ($detail->quantity * ($detail->unit_price ?? $detail->product->sale_price ?? 0)), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totales --}}
        <div class="totals-section">
            <div class="totals-spacer"></div>
            <div class="totals-box">
                <table class="totals-table">
                    @php
                        $subtotal = $sale->subtotal_before_discount ?? $sale->total_price;
                        $discount = $sale->general_discount_value ?? 0;
                        $tax = $sale->total_price - $subtotal + $discount;
                    @endphp
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
                    <tr class="total-row">
                        <td>Total</td>
                        <td>{{ $currency->symbol }} {{ number_format($sale->total_price, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Notas --}}
        <div class="notes-section">
            <strong>Nota:</strong> Este documento es una representación impresa de una factura electrónica.
            Para cualquier consulta o reclamo, contacte a {{ $company->name }}.
            @if ($company->phone)Tel: {{ $company->phone }}@endif
        </div>

        {{-- Footer --}}
        <div class="footer">
            <div class="footer-brand">{{ $company->name }}</div>
            <div>Documento generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }} · Sistema de Gestión</div>
        </div>
    </div>
</body>
</html>
