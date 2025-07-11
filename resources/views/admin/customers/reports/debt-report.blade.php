<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Deudas de Clientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .company-logo {
            max-height: 60px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            margin-bottom: 5px;
            color: #333;
        }
        .report-date {
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-right {
            text-align: right;
        }
        .filters-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-size: 11px;
        }
        .filters-info h4 {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($company->logo)
            <img src="{{ public_path('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="company-logo">
        @endif
        <div class="company-name">{{ $company->name }}</div>
        <div class="report-title">REPORTE DE DEUDAS DE CLIENTES</div>
        <div class="report-date">Generado el: {{ date('d/m/Y H:i:s') }}</div>
    </div>
    
    @if(request()->has('search') || request()->has('debt_min') || request()->has('debt_max') || isset($exchangeRate))
        <div class="filters-info">
            <h4>Filtros Aplicados:</h4>
            @if(request()->has('search') && request()->search)
                <p><strong>Búsqueda:</strong> "{{ request()->search }}"</p>
            @endif
            @if(request()->has('debt_min') || request()->has('debt_max'))
                <p><strong>Rango de deuda:</strong>
                    @if(request()->filled('debt_min') && request()->filled('debt_max'))
                        {{ $currency->symbol }} {{ request()->debt_min }} - {{ $currency->symbol }} {{ request()->debt_max }}
                    @elseif(request()->filled('debt_min'))
                        Desde {{ $currency->symbol }} {{ request()->debt_min }}
                    @elseif(request()->filled('debt_max'))
                        Hasta {{ $currency->symbol }} {{ request()->debt_max }}
                    @endif
                </p>
            @endif
            @if(isset($exchangeRate) && $exchangeRate != 1)
                <p><strong>Tipo de cambio aplicado:</strong> 1 USD = {{ number_format($exchangeRate, 2) }} Bs</p>
            @endif
        </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Contacto</th>
                <th>Cédula</th>
                <th>Deuda Total</th>
                @if(isset($exchangeRate) && $exchangeRate != 1)
                    <th>Deuda en Bs</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php
                $orderedCustomers = $customers;
                $order = request('order');
                if ($order === 'name_asc') {
                    $orderedCustomers = $customers->sortBy(function($c) { return strtolower($c->name); });
                } elseif ($order === 'debt_desc') {
                    $orderedCustomers = $customers->sortByDesc('total_debt');
                } elseif ($order === 'debt_asc') {
                    $orderedCustomers = $customers->sortBy('total_debt');
                }
            @endphp
            @forelse($orderedCustomers as $index => $customer)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>
                        {{ $customer->phone ?? '' }}<br>
                        {{ $customer->email ?? '' }}
                    </td>
                    <td>{{ $customer->nit_number ?? '' }}</td>
                    <td class="text-right text-danger">
                        {{ $currency->symbol }} {{ number_format($customer->total_debt, 2) }}
                    </td>
                    @if(isset($exchangeRate) && $exchangeRate != 1)
                        <td class="text-right text-danger">
                            Bs. {{ number_format($customer->total_debt * $exchangeRate, 2) }}
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ isset($exchangeRate) && $exchangeRate != 1 ? '6' : '5' }}" style="text-align: center;">No hay clientes con deudas pendientes</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">TOTAL DEUDA PENDIENTE:</td>
                <td class="text-right text-danger">
                    {{ $currency->symbol }} {{ number_format($totalDebt, 2) }}
                </td>
                @if(isset($exchangeRate) && $exchangeRate != 1)
                    <td class="text-right text-danger">
                        Bs. {{ number_format($totalDebt * $exchangeRate, 2) }}
                    </td>
                @endif
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Este documento es un reporte oficial de {{ $company->name }}.</p>
        <p>{{ $company->address }} | {{ $company->phone }} | {{ $company->email }}</p>
    </div>
</body>
</html>