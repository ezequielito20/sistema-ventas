<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #{{ $sale->id }}</title>
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

        .company-info {
            font-size: 10px;
            line-height: 1.3;
        }

        .invoice-title {
            text-align: center;
            width: 40%;
            float: left;
        }

        .invoice-title h1 {
            font-size: 18px;
            margin: 100px 0 0 0;
        }

        .invoice-details {
            float: right;
            width: 30%;
            text-align: right;
            font-size: 10px;
            line-height: 1.3;
        }

        .customer-container {
            margin: 20px 0;
            padding: 5px;
            border: 1px solid #000000;
            border-radius: 8px;
            background-color: #f8fafc;
        }

        .customer-header {
            border-bottom: 1px solid #000000;
            padding-bottom: 8px;
            margin-bottom: 12px;
            font-size: 14px;
            color: #000000;
            font-weight: bold;
            padding-left: 35px;
            
        }

        .customer-info {
            display: flex;
            align-items: center;
        }

        .customer-item {
            display: inline-flex;
            align-items: baseline;
            white-space: nowrap;
        }

        .customer-label {
            font-weight: bold;
            color: #000000;
            font-size: 11px;
            margin-right: 5px;
        }

        .customer-value {
            color: #2d3748;
            font-size: 11px;
            margin-right: 30px;
        }

        .products-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 10px 0;
            font-size: 11px;
            border: 1px solid #000000;
            border-radius: 8px;
            overflow: hidden;
        }

        .products-table th {
            background: #f8f9fa;
            padding: 8px;
            text-align: left;
            border: 1px solid #000000;
        }

        .products-table td {
            border: 1px solid #000000;
            padding: 8px;
        }

        .summary {
            float: right;
            width: 30%;
            margin-top: 15px;
            font-size: 11px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            text-align: center;
            color: #6c757d;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>
    <div class="header-container clearfix">
        <div class="company-section">
            @if ($company->logo)
                <img src="{{ public_path('storage/' . $company->logo) }}" alt="Logo" class="logo">
            @endif
            <div class="company-info">
                <strong>{{ $company->name }}</strong><br>
                {{ $company->address }}<br>
                Tel: {{ $company->phone }}<br>
                Email: {{ $company->email }}
            </div>
        </div>

        <div class="invoice-title">
            <h1>FACTURA</h1>
        </div>

        <div class="invoice-details">
            <strong>NIT:</strong> {{ $company->nit }}<br>
            <strong>N° Factura:</strong> {{ str_pad($sale->id, 8, '0', STR_PAD_LEFT) }}<br>
            <strong class="original margin-top-10" style="margin-top: 40px; display: block;">ORIGINAL</strong>
        </div>
    </div>

    <!-- Información del cliente -->
    <div class="customer-container">
        <div class="customer-header">
            Información del Cliente
        </div>
        <div class="customer-info">
            <div class="customer-item">
                <span class="customer-label">Nombre:</span>
                <span class="customer-value">{{ $customer->name }}</span>
            </div>
            <div class="customer-item">
                <span class="customer-label">NIT:</span>
                <span class="customer-value">{{ $customer->nit_number }}</span>
            </div>
            <div class="customer-item">
                <span class="customer-label">Teléfono:</span>
                <span class="customer-value">{{ $customer->phone }}</span>
            </div>
            <div class="customer-item">
                <span class="customer-label">Email:</span>
                <span class="customer-value">{{ $customer->email }}</span>
            </div>
        </div>
    </div>

    <!-- Tabla de productos -->
    <table class="products-table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Precio Unit.</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($saleDetails as $detail)
                <tr>
                    <td>{{ $detail->product->code }}</td>
                    <td>{{ $detail->product->name }}</td>
                    <td class="text-right">{{ $detail->quantity }}</td>
                    <td class="text-right">{{ number_format($detail->product->sale_price, 2) }}</td>
                    <td class="text-right">{{ number_format($detail->quantity * $detail->product->sale_price, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table width="100%">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{ number_format($sale->total_price / (1 + $company->tax_amount / 100), 2) }}
                </td>
            </tr>
            <tr>
                <td>{{ $company->tax_name }} ({{ $company->tax_amount }}%):</td>
                <td class="text-right">
                    {{ number_format($sale->total_price - $sale->total_price / (1 + $company->tax_amount / 100), 2) }}
                </td>
            </tr>
            <tr class="font-bold">
                <td>Total:</td>
                <td class="text-right">{{ number_format($sale->total_price, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Gracias por su compra</p>
        <small>Este documento es una representación impresa de una factura electrónica</small>
    </div>
</body>

</html>
