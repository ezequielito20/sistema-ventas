<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Clientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }

        .header-container {
            width: 100%;
            margin-bottom: 20px;
            position: relative;
        }

        .company-section {
            float: left;
            width: 30%;
        }

        .logo {
            max-width: 60px;
            max-height: 60px;
            margin-bottom: 5px;
        }

        .report-title {
            text-align: center;
            width: 40%;
            float: left;
        }

        .report-title h1 {
            font-size: 18px;
            margin: 10px 0;
        }

        .date-section {
            float: right;
            width: 30%;
            text-align: right;
            font-size: 12px;
        }

        .customers-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 10px 0;
            font-size: 11px;
            border: 1px solid #000000;
            border-radius: 8px;
            overflow: hidden;
        }

        .customers-table th {
            background: #f8f9fa;
            padding: 8px;
            text-align: left;
            border: 1px solid #000000;
        }

        .customers-table td {
            border: 1px solid #000000;
            padding: 8px;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            text-align: center;
            color: #474a4d;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .status-active {
            color: green;
            font-weight: bold;
        }

        .status-inactive {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header-container clearfix">
        <div class="company-section">
            @if ($company->logo)
                <img src="{{ public_path('storage/' . $company->logo) }}" alt="Logo" class="logo">
            @endif
            <div class="company-info">
                <strong>{{ $company->name }}</strong><br>
                {{ $company->address }}<br>
                Tel: {{ $company->phone }}
            </div>
        </div>

        <div class="report-title">
            <h1>REPORTE DE CLIENTES</h1>
        </div>

        <div class="date-section">
            <strong>Fecha de emisión:</strong><br>
            {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <!-- Tabla de clientes -->
    <table class="customers-table">
        <thead>
            <tr style="text-align: center">
                <th style="width: 5%">#</th>
                <th style="width: 20%">Nombre</th>
                <th style="width: 15%">NIT/CI</th>
                <th style="width: 15%">Teléfono</th>
                <th style="width: 20%">Email</th>
                <th style="width: 15%">Total Ventas</th>
                <th style="width: 10%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $customer->formatted_name }}</td>
                    <td>{{ $customer->nit_number }}</td>
                    <td>{{ $customer->formatted_phone }}</td>
                    <td>{{ $customer->email }}</td>
                    <td style="text-align: right">
                        {{ $currency->symbol }} {{ number_format($customer->total_sales_amount, 2) }}
                    </td>
                    <td>
                        @if ($customer->sales->count() > 0)
                            <span style="color: green">Activo</span>
                        @else
                            <span style="color: red">Inactivo</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>{{ $company->name }} - Sistema de Gestión</p>
        <small>Este documento es un reporte generado el {{ now()->format('d/m/Y H:i') }}</small>
    </div>
</body>

</html>
