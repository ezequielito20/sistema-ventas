<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #{{ $sale->id }}</title>
    <style>
        /* Estilos generales */
        body {
            font-family: 'Helvetica', sans-serif;
            color: #2d3748;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        /* Cabecera */
        .header {
            border-bottom: 2px solid #4299e1;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-info {
            float: left;
            width: 50%;
        }

        .company-logo {
            max-width: 200px;
            height: auto;
        }

        .invoice-details {
            float: right;
            width: 40%;
            text-align: right;
        }

        /* Información del cliente */
        .customer-info {
            background: #f7fafc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }

        /* Tabla de productos */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .products-table th {
            background: #4299e1;
            color: white;
            padding: 12px;
            text-align: left;
        }

        .products-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .products-table tr:nth-child(even) {
            background: #f7fafc;
        }

        /* Resumen y totales */
        .summary {
            float: right;
            width: 35%;
            margin-top: 20px;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 30px;
        }

        .summary-table td {
            padding: 8px;
        }

        .summary-table .total {
            font-size: 1.2em;
            font-weight: bold;
            color: #2b6cb0;
        }

        /* Pie de página */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 0.9em;
            color: #718096;
        }

        /* Utilidades */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="company-info">
            @if($company->logo)
            <img src="{{ public_path('storage/' . $company->logo) }}" alt="Logo de {{ $company->name }}" style="max-width: 150px; max-height: 150px;">
            @endif
            <h2>{{ $company->name }}</h2>
            <p>
                {{ $company->address }}<br>
                {{ $company->city }}, {{ $company->state }} {{ $company->postal_code }}<br>
                Tel: {{ $company->phone }}<br>
                Email: {{ $company->email }}<br>
                NIT: {{ $company->nit }}
            </p>
        </div>
        <div class="invoice-details">
            <h1>FACTURA</h1>
            <p>
                <strong>N° Factura:</strong> {{ str_pad($sale->id, 8, '0', STR_PAD_LEFT) }}<br>
                <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}<br>
                <strong>Hora:</strong> {{ \Carbon\Carbon::parse($sale->created_at)->format('H:i:s') }}
            </p>
        </div>
    </div>

    <div class="customer-info">
        <h3>Información del Cliente</h3>
        <div class="clearfix">
            <div style="float: left; width: 50%;">
                <p>
                    <strong>Nombre:</strong> {{ $customer->name }}<br>
                    <strong>NIT:</strong> {{ $customer->nit_number }}<br>
                    <strong>Teléfono:</strong> {{ $customer->phone }}<br>
                    <strong>Email:</strong> {{ $customer->email }}
                </p>
            </div>
        </div>
    </div>

    <table class="products-table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($saleDetails as $detail)
                <tr>
                    <td>{{ $detail->product->code }}</td>
                    <td>
                        {{ $detail->product->name }}
                        @if($detail->product->description)
                            <br>
                            <small>{{ $detail->product->description }}</small>
                        @endif
                    </td>
                    <td>{{ $detail->quantity }}</td>
                    <td class="text-right">{{ $company->currency }} {{ number_format($detail->product->sale_price, 2) }}</td>
                    <td class="text-right">{{ $company->currency }} {{ number_format($detail->quantity * $detail->product->sale_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table class="summary-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{ $company->currency }} {{ number_format($sale->total_price / (1 + $company->tax_amount/100), 2) }}</td>
            </tr>
            <tr>
                <td>{{ $company->tax_name }} ({{ $company->tax_amount }}%):</td>
                <td class="text-right">{{ $company->currency }} {{ number_format($sale->total_price - ($sale->total_price / (1 + $company->tax_amount/100)), 2) }}</td>
            </tr>
            <tr class="total">
                <td>Total:</td>
                <td class="text-right">{{ $company->currency }} {{ number_format($sale->total_price, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>
            Gracias por su compra<br>
            {{ $company->name }} - {{ $company->business_type }}<br>
            {{ $company->address }}, {{ $company->city }}, {{ $company->country }}<br>
            Tel: {{ $company->phone }} - Email: {{ $company->email }}
        </p>
        <p class="text-center">
            Este documento es una representación impresa de una factura electrónica
        </p>
    </div>
</body>
</html>
