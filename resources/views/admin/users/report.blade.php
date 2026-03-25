<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Usuarios</title>
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
            border-bottom: 2px solid #4f46e5;
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
            color: #4f46e5;
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
            color: #1a2a5a;
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
            width: 22%;
            display: inline-block;
            margin-right: 2%;
        }

        .stat-card.last {
            margin-right: 0;
        }

        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #4f46e5;
            display: block;
        }

        .stat-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* Tabla Estilizada */
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            border-radius: 8px;
            overflow: hidden;
        }

        .users-table th {
            background: #4f46e5;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        .users-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .users-table tr:nth-child(even) {
            background-color: #fcfdfe;
        }

        .badge-verified {
            background: #d1fae5;
            color: #065f46;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-pending {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }

        .role-pill {
            background: #eef2ff;
            color: #312e81;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            margin-right: 2px;
            font-weight: 600;
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
            <h1>Reporte de Usuarios</h1>
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
            <span class="stat-number">{{ $users->count() }}</span>
            <span class="stat-label">Total Usuarios</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">{{ $users->where('email_verified_at', '!=', null)->count() }}</span>
            <span class="stat-label">Verificados</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">{{ $users->where('email_verified_at', null)->count() }}</span>
            <span class="stat-label">Pendientes</span>
        </div>
        <div class="stat-card last">
            <span
                class="stat-number">{{ $users->filter(function ($user) {return $user->roles->count() > 0;})->count() }}</span>
            <span class="stat-label">Con Roles</span>
        </div>
    </div>

    <!-- Tabla Detallada -->
    <table class="users-table">
        <thead>
            <tr>
                <th style="width: 5%" align="center">ID</th>
                <th style="width: 30%">Usuario / Correo</th>
                <th style="width: 15%">Estado</th>
                <th style="width: 30%">Roles</th>
                <th style="width: 20%" align="right">Fecha Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td align="center" style="color: #94a3b8;">#{{ $user->id }}</td>
                    <td>
                        <strong style="color: #1e1b4b;">{{ $user->name }}</strong><br>
                        <span style="color: #64748b; font-size: 9px;">{{ $user->email }}</span>
                    </td>
                    <td align="center">
                        @if ($user->email_verified_at)
                            <span class="badge-verified">Verificado</span>
                        @else
                            <span class="badge-pending">S. Verificar</span>
                        @endif
                    </td>
                    <td>
                        @forelse ($user->roles as $role)
                            <span class="role-pill">{{ strtoupper($role->name) }}</span>
                        @empty
                            <span style="font-size: 8px; color: #94a3b8;">Sin roles</span>
                        @endforelse
                    </td>
                    <td align="right" style="color: #64748b;">{{ $user->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>{{ $company->name }} - Sistema de Gestión de Ventas</p>
        <small>Documento generado dinámicamente. Página 1 de 1</small>
    </div>
</body>

</html>
