<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Categorías</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        /* Estructura Original del Encabezado Reemplazada por una más elegante pero manteniendo la división */
        .header-container {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #4338ca;
            padding-bottom: 15px;
        }

        .company-section {
            float: left;
            width: 35%;
        }

        .logo {
            max-width: 70px;
            max-height: 70px;
            margin-bottom: 8px;
        }

        .company-info strong {
            font-size: 14px;
            color: #4338ca;
            display: block;
            margin-bottom: 4px;
        }

        .report-title {
            text-align: center;
            width: 30%;
            float: left;
            padding-top: 10px;
        }

        .report-title h1 {
            font-size: 18px;
            margin: 0;
            color: #1e1b4b;
            text-transform: uppercase;
        }

        .date-section {
            float: right;
            width: 35%;
            text-align: right;
            font-size: 10px;
            color: #666;
            padding-top: 10px;
        }

        /* Caja de Estadísticas Moderna */
        .stats-wrapper {
            margin-bottom: 25px;
            width: 100%;
        }

        .stat-card {
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e2e8f0;
            width: 30%;
            display: inline-block;
            margin-right: 2%;
        }

        .stat-card.last {
            margin-right: 0;
        }

        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #4338ca;
            display: block;
        }

        .stat-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* Tabla Estilizada */
        .categories-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            border-radius: 8px;
            overflow: hidden;
        }

        .categories-table th {
            background: #4338ca;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        .categories-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .categories-table tr:nth-child(even) {
            background-color: #fcfdfe;
        }

        .badge-count {
            background: #e0e7ff;
            color: #4338ca;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
        }

        .percent-bar {
            height: 6px;
            background: #f1f5f9;
            border-radius: 3px;
            width: 100%;
            margin-top: 4px;
        }

        .percent-fill {
            height: 100%;
            background: #4338ca;
            border-radius: 3px;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-size: 9px;
            text-align: center;
            color: #666;
        }

        .content-highlight {
            margin-top: 15px;
            padding: 10px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #4338ca;
            font-size: 10px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>
    <!-- Header: Manteniendo Estructura Original de 3 columnas -->
    <div class="header-container clearfix">
        <div class="company-section">
            @if ($company->logo)
                <img src="{{ public_path('storage/' . $company->logo) }}" alt="Logo" class="logo">
            @endif
            <div class="company-info">
                <strong>{{ $company->name }}</strong>
                {{ $company->address }}<br>
                Tel: {{ $company->phone }}
            </div>
        </div>

        <div class="report-title">
            <h1>Reporte de Categorías</h1>
        </div>

        <div class="date-section">
            <strong>Fecha de Emisión:</strong><br>
            {{ now()->format('d/m/Y') }}<br>
            <strong>Hora:</strong> {{ now()->format('H:i:s') }}
        </div>
    </div>

    <!-- Estadísticas: Un toque moderno sin romper la lógica -->
    <div class="stats-wrapper clearfix">
        <div class="stat-card">
            <span class="stat-number">{{ $categories->count() }}</span>
            <span class="stat-label">Categorías Totales</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">{{ $categories->sum('products_count') }}</span>
            <span class="stat-label">Productos Registrados</span>
        </div>
        <div class="stat-card last">
            <span class="stat-number">
                @php
                    $avg =
                        $categories->count() > 0
                            ? round($categories->sum('products_count') / $categories->count(), 1)
                            : 0;
                @endphp
                {{ $avg }}
            </span>
            <span class="stat-label">Promedio de Artículos</span>
        </div>
    </div>

    <!-- Tabla Detallada -->
    <table class="categories-table">
        <thead>
            <tr>
                <th style="width: 5%" align="center">#</th>
                <th style="width: 35%">Nombre de Categoría</th>
                <th style="width: 40%">Descripción</th>
                <th style="width: 20%" align="center">Productos</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td align="center" style="color: #94a3b8;">{{ $loop->iteration }}</td>
                    <td><strong style="color: #1e1b4b;">{{ $category->name }}</strong></td>
                    <td style="color: #64748b; font-size: 10px;">{{ $category->description ?: 'Sin descripción' }}</td>
                    <td align="center">
                        <span class="badge-count">{{ $category->products_count }}</span>
                        @php
                            $totalProducts = $categories->sum('products_count');
                            $percent =
                                $totalProducts > 0 ? round(($category->products_count / $totalProducts) * 100, 1) : 0;
                        @endphp
                        <div class="percent-bar">
                            <div class="percent-fill" style="width: {{ $percent }}%"></div>
                        </div>
                        <div style="font-size: 8px; color: #4338ca; margin-top: 2px;">{{ $percent }}% del total
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($categories->count() > 0)
        <div class="content-highlight">
            <strong>Análisis del Inventario:</strong> La categoría con mayor presencia es
            <strong>"{{ $categories->first()->name }}"</strong> con un total de
            {{ $categories->first()->products_count }} productos asociados.
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>{{ $company->name }} - Sistema de Gestión de Ventas</p>
        <small>Este documento es un reporte generado de forma automática. Página 1 de 1</small>
    </div>
</body>

</html>
