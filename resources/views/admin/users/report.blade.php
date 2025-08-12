<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Usuarios</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: #f8fafc;
        }

        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            position: relative;
        }

        .company-section {
            float: left;
            width: 30%;
        }

        .logo {
            max-width: 80px;
            max-height: 80px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .company-info {
            font-size: 13px;
            line-height: 1.6;
        }

        .company-info strong {
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
        }

        .report-title {
            text-align: center;
            width: 40%;
            float: left;
        }

        .report-title h1 {
            font-size: 24px;
            margin: 10px 0;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .date-section {
            float: right;
            width: 30%;
            text-align: right;
            font-size: 13px;
        }

        .date-section strong {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .content-section {
            padding: 30px;
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-item {
            background: white;
            padding: 25px 20px;
            border-radius: 16px;
            text-align: center;
            border: 2px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-item:nth-child(2)::before {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .stat-item:nth-child(3)::before {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .stat-item:nth-child(4)::before {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
            display: block;
            margin-bottom: 8px;
            line-height: 1;
        }

        .stat-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .table-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .table-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 20px 25px;
            border-bottom: 2px solid #e5e7eb;
        }

        .table-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-title::before {
            content: '👥';
            font-size: 20px;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .users-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .users-table th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 10px;
            border: none;
            position: relative;
        }

        .users-table th:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 60%;
            background: rgba(255, 255, 255, 0.3);
        }

        .users-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }

        .users-table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .users-table tbody tr:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            transform: scale(1.01);
        }

        .users-table tbody tr:last-child {
            border-bottom: none;
        }

        .users-table td {
            padding: 12px;
            border: none;
            vertical-align: middle;
        }

        .users-table td:first-child {
            font-weight: 600;
            color: #667eea;
        }

        .user-role {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-right: 3px;
            margin-bottom: 2px;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }

        .user-name {
            font-weight: 600;
            color: #1e293b;
        }

        .user-email {
            color: #64748b;
            font-size: 10px;
        }

        .user-date {
            color: #64748b;
            font-size: 10px;
        }

        .footer {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 25px 30px;
            border-top: 2px solid #e5e7eb;
            font-size: 11px;
            text-align: center;
            color: #64748b;
            position: relative;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 0 0 3px 3px;
        }

        .footer p {
            margin: 0 0 8px 0;
            font-weight: 700;
            color: #1e293b;
            font-size: 13px;
        }

        .footer small {
            opacity: 0.8;
            font-size: 10px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .report-container {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
            }
            
            .header-container {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .users-table thead {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .stat-item {
                box-shadow: none;
                border: 1px solid #e5e7eb;
            }
            
            .table-section {
                box-shadow: none;
                border: 1px solid #e5e7eb;
            }
            
            .users-table tbody tr:hover {
                transform: none;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .stats-summary {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .stat-item {
                padding: 20px 15px;
            }
            
            .stat-number {
                font-size: 24px;
            }
            
            .header-container {
                padding: 20px;
            }
            
            .company-section,
            .report-title,
            .date-section {
                width: 100%;
                float: none;
                text-align: center;
                margin-bottom: 15px;
            }
            
            .report-title h1 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="report-container">
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
                <h1>REPORTE DE USUARIOS</h1>
            </div>

            <div class="date-section">
                <strong>Fecha de emisión:</strong><br>
                {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>

        <!-- Content -->
        <div class="content-section">
            <!-- Estadísticas -->
            <div class="stats-summary">
                <div class="stat-item">
                    <span class="stat-number">{{ $users->count() }}</span>
                    <span class="stat-label">Total Usuarios</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $users->where('email_verified_at', '!=', null)->count() }}</span>
                    <span class="stat-label">Verificados</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $users->where('email_verified_at', null)->count() }}</span>
                    <span class="stat-label">Pendientes</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $users->filter(function($user) { return $user->roles->count() > 0; })->count() }}</span>
                    <span class="stat-label">Con Roles</span>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            <div class="table-section">
                <div class="table-header">
                    <h3 class="table-title">Lista de Usuarios</h3>
                </div>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 25%">Nombre</th>
                            <th style="width: 25%">Email</th>
                            <th style="width: 20%">Rol</th>
                            <th style="width: 25%">Fecha de Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="user-name">{{ $user->name }}</div>
                                </td>
                                <td>
                                    <div class="user-email">{{ $user->email }}</div>
                                </td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="user-role">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="user-date">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>{{ $company->name }} - Sistema de Gestión</p>
            <small>Este documento es un reporte generado el {{ now()->format('d/m/Y H:i') }}</small>
        </div>
    </div>
</body>

</html>
