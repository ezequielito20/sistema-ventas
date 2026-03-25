<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Permisos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
            color: #333;
        }

        .header-container {
            width: 100%;
            margin-bottom: 20px;
            position: relative;
        }

        .company-section {
            float: left;
            width: 50%;
        }

        .logo {
            max-width: 60px;
            max-height: 60px;
            margin-bottom: 5px;
        }

        .report-title {
            text-align: right;
            width: 50%;
            float: right;
        }

        .report-title h1 {
            font-size: 18px;
            margin: 0;
            color: #1a2a5a;
        }

        .date-section {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        .stats-container {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .stat-box {
            display: inline-block;
            width: 32%;
            text-align: center;
        }

        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #1a2a5a;
        }

        .stat-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }

        .permissions-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .permissions-table th {
            background: #f8f9fa;
            padding: 8px;
            border-bottom: 2px solid #ddd;
            text-align: left;
            font-weight: bold;
        }

        .permissions-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            color: white;
            font-weight: bold;
        }

        .badge-blue {
            background-color: #3490dc;
        }

        .badge-green {
            background-color: #38c172;
        }

        .badge-purple {
            background-color: #9561e2;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 9px;
            text-align: center;
            color: #666;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
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
            <h1>REPORTE DE PERMISOS</h1>
            <div class="date-section">
                Emitido el: {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-value">{{ $permissions->count() }}</div>
            <div class="stat-label">Total Permisos</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $rolesCount }}</div>
            <div class="stat-label">Roles Activos</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $usersCount }}</div>
            <div class="stat-label">Usuarios</div>
        </div>
    </div>

    <!-- Tabla -->
    <table class="permissions-table">
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 45%">Nombre del Permiso</th>
                <th style="width: 15%" class="text-center">Guard</th>
                <th style="width: 10%" class="text-center">Roles</th>
                <th style="width: 10%" class="text-center">Usuarios</th>
                <th style="width: 15%" class="text-right">Fecha Creación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($permissions as $permission)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $permission->name }}</strong></td>
                    <td class="text-center">
                        <span class="badge badge-blue">{{ $permission->guard_name }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-green">{{ $permission->roles->count() }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-purple">{{ $permission->users_count ?? 0 }}</span>
                    </td>
                    <td class="text-right">{{ $permission->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>{{ $company->name }} - Sistema de Gestión | Generado por: {{ Auth::user()->name }}</p>
    </div>
</body>

</html>
