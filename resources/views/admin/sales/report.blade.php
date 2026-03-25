<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas</title>
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
            border-bottom: 2px solid #6366f1;
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
            color: #6366f1;
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
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            width: 30%;
            display: inline-block;
            margin-right: 3%;
            border-top: 3px solid #6366f1;
        }

        .stat-box.last {
            margin-right: 0;
        }

        .stat-val {
            font-size: 18px;
            font-weight: bold;
            color: #6366f1;
            display: block;
        }

        .stat-lbl {
            font-size: 8px;
            color: #4338ca;
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
            background: #6366f1;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ede9fe;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background: #fbfbfe;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }

        .bg-indigo {
            background: #6366f1;
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
            <h1 class="report-title">Ventas</h1>
            <span style="font-size: 9px; color: #64748b;">Análisis de Actividad Comercial</span>
        </div>
        <div class="date-col">
            <strong>Emisión:</strong> {{ now()->format('d/m/Y') }}<br>
            <strong>Hora:</strong> {{ now()->format('H:i:s') }}
        </div>
    </div>

    <div class="stats-grid clearfix">
        <div class="stat-box">
            <span class="stat-val">{{ $sales->count() }}</span>
            <span class="stat-lbl">Ventas Realizadas</span>
        </div>
        <div class="stat-box">
            <span class="stat-val">{{ $currency->symbol }} {{ number_format($sales->sum('total_price'), 2) }}</span>
            <span class="stat-lbl">Ingresos Totales</span>
        </div>
        <div class="stat-box last">
            <span class="stat-val">
                @php
                    $avg = $sales->count() > 0 ? $sales->sum('total_price') / $sales->count() : 0;
                @endphp
                {{ $currency->symbol }} {{ number_format($avg, 2) }}
            </span>
            <span class="stat-lbl">Ticket Promedio</span>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">#</th>
                <th width="15%">Fecha Venta</th>
                <th width="35%">Cliente</th>
                <th width="20%" class="text-center">Comprobante</th>
                <th width="25%" class="text-right">Total Facturado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
                <tr>
                    <td class="text-center" style="color: #94a3b8;">{{ $loop->iteration }}</td>
                    <td style="font-weight: bold;">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                    <td>
                        <div style="font-weight: bold; color: #1e293b;">
                            {{ $sale->customer->name ?: 'Consumidor Final' }}</div>
                        <div style="font-size: 8px; color: #64748b;">CI/NIT: {{ $sale->customer->nit_number ?: 'N/A' }}
                        </div>
                    </td>
                    <td class="text-center"><span
                            class="badge bg-indigo">{{ $sale->payment_receipt ?: 'VNT-' . $sale->id }}</span></td>
                    <td class="text-right" style="font-weight: bold; color: #4f46e5;">
                        {{ $currency->symbol }} {{ number_format($sale->total_price, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer clearfix">
        <div style="float: left;">{{ $company->name }} - Sistema de Gestión | Generado por: {{ Auth::user()->name }}
        </div>
        <div style="float: right;">Página 1 de 1</div>
    </div>
</body>

</html>
