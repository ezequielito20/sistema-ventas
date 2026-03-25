<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Clientes</title>
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
            border-bottom: 2px solid #0d9488;
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
            color: #0d9488;
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
            background: #f0fdfa;
            border: 1px solid #ccfbf1;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            width: 30%;
            display: inline-block;
            margin-right: 3%;
            border-top: 3px solid #0d9488;
        }

        .stat-box.last {
            margin-right: 0;
        }

        .stat-val {
            font-size: 18px;
            font-weight: bold;
            color: #0d9488;
            display: block;
        }

        .stat-lbl {
            font-size: 8px;
            color: #134e4a;
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
            background: #0d9488;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f0fdfa;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background: #f9fdfd;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }

        .bg-teal {
            background: #0d9488;
        }

        .bg-gray {
            background: #94a3b8;
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
            <h1 class="report-title">Clientes</h1>
            <span style="font-size: 9px; color: #64748b;">Directorio y Resumen Comercial</span>
        </div>
        <div class="date-col">
            <strong>Emisión:</strong> {{ now()->format('d/m/Y') }}<br>
            <strong>Hora:</strong> {{ now()->format('H:i:s') }}
        </div>
    </div>

    <div class="stats-grid clearfix">
        <div class="stat-box">
            <span class="stat-val">{{ $customers->count() }}</span>
            <span class="stat-lbl">Clientes Registrados</span>
        </div>
        <div class="stat-box">
            <span class="stat-val">{{ $customers->where('sales_count', '>', 0)->count() }}</span>
            <span class="stat-lbl">Clientes Activos</span>
        </div>
        <div class="stat-box last">
            <span class="stat-val">{{ $currency->symbol }}
                {{ number_format($customers->sum('total_sales_amount'), 2) }}</span>
            <span class="stat-lbl">Facturación Acumulada</span>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">#</th>
                <th width="25%">Nombre Completo</th>
                <th width="15%">Documento / NIT</th>
                <th width="15%">Teléfono</th>
                <th width="20%">Correo Electrónico</th>
                <th width="12%" class="text-right">Total Compras</th>
                <th width="8%" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td class="text-center" style="color: #94a3b8;">{{ $loop->iteration }}</td>
                    <td style="font-weight: bold; color: #1e293b;">{{ $customer->formatted_name ?: $customer->name }}
                    </td>
                    <td>{{ $customer->nit_number ?: 'P/N' }}</td>
                    <td>{{ $customer->formatted_phone ?: $customer->phone }}</td>
                    <td style="color: #64748b;">{{ $customer->email ?: 'N/D' }}</td>
                    <td class="text-right" style="font-weight: bold; color: #0d9488;">
                        {{ $currency->symbol }} {{ number_format($customer->total_sales_amount, 2) }}
                    </td>
                    <td class="text-center">
                        @if ($customer->sales_count > 0)
                            <span class="badge bg-teal">ACTIVO</span>
                        @else
                            <span class="badge bg-gray">INACTIVO</span>
                        @endif
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
