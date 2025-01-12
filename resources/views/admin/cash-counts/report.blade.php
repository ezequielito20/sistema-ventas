<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Arqueos de Caja</title>
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

        .cash-counts-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 10px 0;
            font-size: 11px;
            border: 1px solid #000000;
            border-radius: 8px;
            overflow: hidden;
        }

        .cash-counts-table th {
            background: #f8f9fa;
            padding: 8px;
            text-align: left;
            border: 1px solid #000000;
        }

        .cash-counts-table td {
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

        .total-section {
            margin-top: 20px;
            text-align: right;
            font-weight: bold;
        }

        .status-open {
            color: green;
            font-weight: bold;
        }

        .status-closed {
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
            <h1>REPORTE DE ARQUEOS DE CAJA</h1>
        </div>

        <div class="date-section">
            <strong>Fecha de emisión:</strong><br>
            {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <!-- Tabla de arqueos -->
    <table class="cash-counts-table">
        <thead>
            <tr style="text-align: center">
                <th style="width: 5%">#</th>
                <th style="width: 15%">Apertura</th>
                <th style="width: 15%">Cierre</th>
                <th style="width: 15%">Monto Inicial</th>
                <th style="width: 15%">Monto Final</th>
                <th style="width: 25%">Observaciones</th>
                <th style="width: 10%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cashCounts as $cashCount)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($cashCount->opening_date)->format('d/m/Y H:i') }}</td>
                    <td>{{ $cashCount->closing_date ? \Carbon\Carbon::parse($cashCount->closing_date)->format('d/m/Y H:i') : 'En curso' }}</td>
                    <td style="text-align: right">{{ $currency->symbol }} {{ number_format($cashCount->initial_amount, 2) }}</td>
                    <td style="text-align: right">{{ $cashCount->final_amount ? $currency->symbol . ' ' . number_format($cashCount->final_amount, 2) : 'Pendiente' }}</td>
                    <td>{{ $cashCount->observations ?? 'Sin observaciones' }}</td>
                    <td class="{{ $cashCount->closing_date ? 'status-closed' : 'status-open' }}">
                        {{ $cashCount->closing_date ? 'Cerrado' : 'Abierto' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Sección de totales -->
    <div class="total-section">
        <p>
            <strong>Total Inicial:</strong> 
            {{ $currency->symbol }} {{ number_format($cashCounts->sum('initial_amount'), 2) }}
        </p>
        <p>
            <strong>Total Final:</strong> 
            {{ $currency->symbol }} {{ number_format($cashCounts->sum('final_amount'), 2) }}
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>{{ $company->name }} - Sistema de Gestión</p>
        <small>Este documento es un reporte generado el {{ now()->format('d/m/Y H:i') }}</small>
    </div>
</body>

</html>
