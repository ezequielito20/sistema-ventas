@extends('layouts.app')

@section('title', 'Reporte de Permisos')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Reporte de Permisos</h1>
            <p class="text-gray-600 mt-1">Reporte detallado de todos los permisos del sistema</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.permissions.index') }}" class="btn-outline">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
            <button onclick="window.print()" class="btn-primary">
                <i class="fas fa-print mr-2"></i>
                Imprimir
            </button>
        </div>
    </div>

    <!-- Información del Reporte -->
    <div class="modern-card" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div class="modern-card-header" style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); padding: 2rem; border-radius: 16px 16px 0 0; border-bottom: 2px solid #e2e8f0;">
            <div class="title-content" style="display: flex; align-items: center; gap: 1rem;">
                <div class="title-icon" style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div>
                    <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 700; margin: 0;">Reporte de Permisos</h3>
                    <p style="color: #64748b; margin: 0.5rem 0 0 0;">Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>
        </div>
        
        <div class="modern-card-body" style="padding: 2rem; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 0 0 16px 16px;">
            <!-- Estadísticas del Reporte -->
            <div class="report-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="stat-item" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid #667eea;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem;">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Permisos</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #667eea;">{{ $permissions->count() }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="stat-item" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid #10b981;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Roles Activos</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #10b981;">{{ $rolesCount ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="stat-item" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid #f59e0b;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Usuarios</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #f59e0b;">{{ $usersCount ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Permisos -->
            <div class="report-table" style="background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: hidden;">
                <div class="table-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 1.5rem;">
                    <h4 style="margin: 0; font-weight: 700; font-size: 1.1rem;">Lista de Permisos</h4>
                </div>
                
                <div class="table-responsive" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #374151; border-bottom: 2px solid #e5e7eb; font-size: 0.875rem;">#</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #374151; border-bottom: 2px solid #e5e7eb; font-size: 0.875rem;">Nombre del Permiso</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #374151; border-bottom: 2px solid #e5e7eb; font-size: 0.875rem;">Guard</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 700; color: #374151; border-bottom: 2px solid #e5e7eb; font-size: 0.875rem;">Roles</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 700; color: #374151; border-bottom: 2px solid #e5e7eb; font-size: 0.875rem;">Usuarios</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 700; color: #374151; border-bottom: 2px solid #e5e7eb; font-size: 0.875rem;">Fecha Creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $permission)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 1rem; color: #6b7280; font-weight: 600;">{{ $loop->iteration }}</td>
                                    <td style="padding: 1rem;">
                                        <span style="font-weight: 700; color: #667eea;">{{ $permission->name }}</span>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <span style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">{{ $permission->guard_name }}</span>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <span style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">{{ $permission->roles->count() }}</span>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <span style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">{{ $permission->users_count ?? 0 }}</span>
                                    </td>
                                    <td style="padding: 1rem; text-align: center; color: #6b7280; font-size: 0.875rem;">{{ $permission->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="report-footer" style="margin-top: 2rem; padding: 1.5rem; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h5 style="color: #374151; font-weight: 700; margin-bottom: 1rem; font-size: 1rem;">Información del Sistema</h5>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-server" style="color: #667eea;"></i>
                        <span style="color: #6b7280; font-size: 0.875rem;">Sistema: Laravel {{ app()->version() }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-calendar" style="color: #10b981;"></i>
                        <span style="color: #6b7280; font-size: 0.875rem;">Fecha: {{ now()->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-user" style="color: #f59e0b;"></i>
                        <span style="color: #6b7280; font-size: 0.875rem;">Generado por: {{ Auth::user()->name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
    /* Estilos para impresión */
    @media print {
        .btn-outline,
        .btn-primary {
            display: none !important;
        }
        
        .modern-card {
            box-shadow: none !important;
            border: 1px solid #000 !important;
        }
        
        .modern-card-header {
            background: #f0f0f0 !important;
            color: #000 !important;
        }
        
        .title-icon {
            background: #000 !important;
            color: #fff !important;
        }
        
        .stat-item {
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }
        
        .report-table {
            box-shadow: none !important;
            border: 1px solid #000 !important;
        }
        
        .table-header {
            background: #000 !important;
            color: #fff !important;
        }
        
        .report-footer {
            box-shadow: none !important;
            border: 1px solid #000 !important;
        }
    }
    
    /* Estilos para botones */
    .btn-primary {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(245, 158, 11, 0.2);
        cursor: pointer;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(245, 158, 11, 0.3);
    }
    
    .btn-outline {
        background: white;
        border: 2px solid #e2e8f0;
        color: #64748b;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    
    .btn-outline:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
        transform: translateY(-1px);
    }
    
    /* Responsividad */
    @media (max-width: 768px) {
        .report-stats {
            grid-template-columns: 1fr !important;
        }
        
        .modern-card-header {
            padding: 1.5rem !important;
        }
        
        .modern-card-body {
            padding: 1.5rem !important;
        }
        
        .title-content {
            flex-direction: column !important;
            text-align: center !important;
            gap: 0.75rem !important;
        }
        
        .title-icon {
            width: 50px !important;
            height: 50px !important;
            font-size: 1.25rem !important;
        }
        
        .table-responsive {
            font-size: 0.75rem;
        }
        
        .table-responsive th,
        .table-responsive td {
            padding: 0.5rem !important;
        }
    }
</style>
@endpush

@push('js')
<script>
    $(document).ready(function() {
        // Efectos hover para botones
        $('.btn-primary, .btn-outline').on('mouseenter', function() {
            $(this).css('transform', 'translateY(-2px)');
        }).on('mouseleave', function() {
            $(this).css('transform', 'translateY(0)');
        });
        
        // Efectos hover para estadísticas
        $('.stat-item').on('mouseenter', function() {
            $(this).css('transform', 'translateY(-2px)');
            $(this).css('box-shadow', '0 4px 12px rgba(0,0,0,0.1)');
        }).on('mouseleave', function() {
            $(this).css('transform', 'translateY(0)');
            $(this).css('box-shadow', '0 2px 4px rgba(0,0,0,0.05)');
        });
    });
</script>
@endpush
@endsection
