<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario de Productos</title>
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
            border-bottom: 2px solid #059669;
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
            color: #059669;
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
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            width: 23%;
            display: inline-block;
            margin-right: 1.5%;
            border-top: 3px solid #059669;
        }

        .stat-box.last {
            margin-right: 0;
        }

        .stat-val {
            font-size: 16px;
            font-weight: bold;
            color: #059669;
            display: block;
        }

        .stat-lbl {
            font-size: 8px;
            color: #64748b;
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
            background: #059669;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background: #fcfdfe;
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

        .bg-emerald {
            background: #10b981;
        }

        .stock-warning {
            color: #dc2626;
            font-weight: bold;
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
            <h1 class="report-title">Inventario</h1>
            <span style="font-size: 9px; color: #64748b;">Reporte Global de Productos</span>
        </div>
        <div class="date-col">
            <strong>Emisión:</strong> {{ now()->format('d/m/Y') }}<br>
            <strong>Hora:</strong> {{ now()->format('H:i:s') }}
        </div>
    </div>

    <div class="stats-grid clearfix">
        <div class="stat-box">
            <span class="stat-val">{{ $products->count() }}</span>
            <span class="stat-lbl">Productos</span>
        </div>
        <div class="stat-box">
            <span class="stat-val">{{ $products->sum('stock') }}</span>
            <span class="stat-lbl">Existencia Total</span>
        </div>
        <div class="stat-box">
            <span class="stat-val">{{ $currency->symbol }}
                {{ number_format($products->sum(function ($p) {return $p->purchase_price * $p->stock;}),2) }}</span>
            <span class="stat-lbl">Valor Inventario (C)</span>
        </div>
        <div class="stat-box last">
            <span class="stat-val">{{ $currency->symbol }}
                {{ number_format($products->sum(function ($p) {return $p->sale_price * $p->stock;}),2) }}</span>
            <span class="stat-lbl">Valor Estimado (V)</span>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">#</th>
                <th width="12%">Código</th>
                <th width="28%">Producto / Proveedor</th>
                <th width="15%">Categoría</th>
                <th width="8%" class="text-center">Stock</th>
                <th width="16%" class="text-right">P. Compra</th>
                <th width="16%" class="text-right">P. Venta</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td class="text-center" style="color: #94a3b8;">{{ $loop->iteration }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $product->code }}</td>
                    <td>
                        <div style="font-weight: bold; color: #1e293b;">{{ $product->name }}</div>
                        <div style="font-size: 8px; color: #64748b;">
                            {{ $product->supplier->company_name ?? 'Sin proveedor' }}</div>
                    </td>
                    <td><span class="badge bg-indigo">{{ $product->category->name ?? 'S/C' }}</span></td>
                    <td class="text-center {{ $product->stock <= 5 ? 'stock-warning' : '' }}">
                        {{ $product->stock }}
                    </td>
                    <td class="text-right">{{ $currency->symbol }} {{ number_format($product->purchase_price, 2) }}
                    </td>
                    <td class="text-right" style="font-weight: bold; color: #059669;">{{ $currency->symbol }}
                        {{ number_format($product->sale_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer clearfix">
        <div style="float: left;">{{ $company->name }} - Sistema Control de Ventas | Generado por:
            {{ Auth::user()->name }}</div>
        <div style="float: right;">Página 1 de 1</div>
    </div>
</body>

</html>
