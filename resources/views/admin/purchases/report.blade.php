<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Compras</title>
    <style>
        @page {
            margin: 1cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #334155;
            margin: 0;
            padding: 0;
        }

        .header-wrapper {
            width: 100%;
            border-bottom: 2px solid #d97706;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-col {
            float: left;
            width: 35%;
        }

        .title-col {
            float: left;
            width: 30%;
            text-align: center;
            padding-top: 10px;
        }

        .date-col {
            float: right;
            width: 35%;
            text-align: right;
            color: #64748b;
            padding-top: 10px;
        }

        .logo {
            max-width: 60px;
            max-height: 60px;
            margin-bottom: 5px;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #d97706;
            display: block;
        }

        .report-title {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            margin: 0;
        }

        .stats-grid {
            width: 100%;
            margin-bottom: 20px;
        }

        .stat-box {
            background: #fdfaf3;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            width: 30%;
            display: inline-block;
            margin-right: 3%;
            border-top: 3px solid #d97706;
        }

        .stat-box.last {
            margin-right: 0;
        }

        .stat-val {
            font-size: 18px;
            font-weight: bold;
            color: #d97706;
            display: block;
        }

        .stat-lbl {
            font-size: 8px;
            color: #92400e;
            text-transform: uppercase;
            font-weight: 600;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table th {
            background: #d97706;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #fef3c7;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background: #fffcf0;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }

        .bg-amber {
            background: #f59e0b;
        }

        .footer {
            position: fixed;
            bottom: -10px;
            width: 100%;
            text-align: center;
            color: #94a3b8;
            font-size: 8px;
            border-top: 1px solid #f1f5f9;
            padding-top: 8px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header-wrapper clearfix">
        <div class="company-col">
            @if ($company->logo)
                <img src="{{ public_path('storage/' . $company->logo) }}" alt="Logo" class="logo">
            @endif
            <span class="company-name">{{ $company->name }}</span>
            <span style="font-size: 9px;">{{ $company->address }} | Tel: {{ $company->phone }}</span>
        </div>
        <div class="title-col">
            <h1 class="report-title">Compras</h1>
            <span style="font-size: 9px; color: #64748b;">Historial de Abastecimiento</span>
        </div>
        <div class="date-col">
            <strong>Emisión:</strong> {{ now()->format('d/m/Y') }}<br>
            <strong>Hora:</strong> {{ now()->format('H:i:s') }}
        </div>
    </div>

    <div class="stats-grid clearfix">
        <div class="stat-box">
            <span class="stat-val">{{ $purchases->count() }}</span>
            <span class="stat-label">Total Operaciones</span>
        </div>
        <div class="stat-box">
            <span class="stat-val">{{ $currency->symbol }} {{ number_format($purchases->sum('total_price'), 2) }}</span>
            <span class="stat-label">Inversión Total</span>
        </div>
        <div class="stat-box last">
            <span class="stat-val">
                @php
                    $avg = $purchases->count() > 0 ? $purchases->sum('total_price') / $purchases->count() : 0;
                @endphp
                {{ $currency->symbol }} {{ number_format($avg, 2) }}
            </span>
            <span class="stat-label">Promedio de Compra</span>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">#</th>
                <th width="15%">Fecha Compra</th>
                <th width="30%">Comprobante / Recibo</th>
                <th width="15%" class="text-center">Artículos</th>
                <th width="15%" class="text-right">Total Invertido</th>
                <th width="20%" class="text-right">Fecha Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchases as $purchase)
                <tr>
                    <td class="text-center" style="color: #94a3b8;">{{ $loop->iteration }}</td>
                    <td style="font-weight: bold;">
                        {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
                    <td><span class="badge bg-amber">{{ $purchase->payment_receipt ?: 'S/C' }}</span></td>
                    <td class="text-center">
                        @php
                            $itemsCount = $purchase->details->sum('quantity');
                        @endphp
                        {{ $itemsCount }} uds.
                    </td>
                    <td class="text-right" style="font-weight: bold; color: #d97706;">
                        {{ $currency->symbol }} {{ number_format($purchase->total_price, 2) }}
                    </td>
                    <td class="text-right" style="color: #64748b; font-size: 9px;">
                        {{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer clearfix">
        <div style="float: left;">{{ $company->name }} - Sistema de Gestión | Generado por: {{ Auth::user()->name }}
        </div>
        <div style="float: right;">Reporte de Compras - Página 1 de 1</div>
    </div>
</body>

</html>
