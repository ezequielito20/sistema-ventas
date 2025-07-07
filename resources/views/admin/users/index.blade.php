@extends('adminlte::page')

@section('title', 'Gestión de Usuarios')

@section('content_header')
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-12 col-lg-8">
                <h1 class="text-dark font-weight-bold mb-2 mb-lg-0">
                    <i class="fas fa-users mr-2 text-primary"></i>
                    Gestión de Usuarios
                </h1>
            </div>
            <div class="col-12 col-lg-4">
                <div class="d-flex flex-column flex-sm-row justify-content-lg-end">
                    @can('users.report')
                        <a href="{{ route('admin.users.report') }}" class="btn btn-info btn-sm mb-2 mb-sm-0 mr-sm-2" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i>
                            <span class="d-none d-sm-inline">Reporte</span>
                        </a>
                    @endcan
                    @can('users.create')
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus mr-1"></i>
                            <span class="d-none d-sm-inline">Nuevo Usuario</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
<style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
            --warning-gradient: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            --danger-gradient: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --card-shadow-hover: 0 15px 40px rgba(0, 0, 0, 0.15);
            --border-radius: 15px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .main-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            border: none;
            overflow: hidden;
            transition: var(--transition);
        }

        .main-card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-2px);
        }

        .card-header-custom {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 1.5rem;
        }

        .card-header-custom .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        /* Desktop Table View */
        .desktop-view {
            display: none;
        }

        @media (min-width: 992px) {
            .desktop-view {
                display: block;
            }
            .mobile-view {
                display: none;
            }
        }

        /* Mobile Cards View */
        .mobile-view {
            display: block;
        }

        .user-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: none;
            margin-bottom: 1rem;
            transition: var(--transition);
            overflow: hidden;
        }

        .user-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            transform: translateY(-3px);
        }

        .user-card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1rem;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 1rem;
        }

        .user-info h5 {
            margin: 0;
            font-weight: 600;
            color: #2c3e50;
        }

        .user-info p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .user-card-body {
            padding: 1rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }

        .info-value {
            font-size: 0.9rem;
            color: #6c757d;
        }

        /* Enhanced Badges */
        .badge-modern {
            padding: 0.4em 0.8em;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .badge-success-modern {
            background: var(--success-gradient);
            color: #155724;
        }

        .badge-warning-modern {
            background: var(--warning-gradient);
            color: #856404;
        }

        .badge-info-modern {
            background: var(--info-gradient);
            color: #0c5460;
        }

        /* Enhanced Buttons */
        .btn-modern {
            border-radius: 25px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-primary-modern {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-success-modern {
            background: var(--success-gradient);
            color: #155724;
        }

        .btn-info-modern {
            background: var(--info-gradient);
            color: #0c5460;
        }

        .btn-danger-modern {
            background: var(--danger-gradient);
            color: #721c24;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        /* Enhanced Table for Desktop */
        .table-modern {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .table-modern thead th {
            background: var(--primary-gradient);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
            border: none;
            padding: 1rem 0.75rem;
        }

        .table-modern tbody tr {
            transition: var(--transition);
        }

        .table-modern tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
            transform: scale(1.01);
        }

        .table-modern tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-color: rgba(0, 0, 0, 0.05);
        }

        /* Search and Filter Bar */
        .search-bar {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-input {
            border: 2px solid #e9ecef;
            border-radius: 25px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Loading Animation */
        .loading-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            box-shadow: var(--card-shadow);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Enhanced Modal */
        .modal-modern .modal-content {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .modal-modern .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            border: none;
        }

        .modal-modern .modal-body {
            padding: 2rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
        }

        .detail-value {
            color: #6c757d;
        }

        /* Responsive Utilities */
        @media (max-width: 576px) {
            .card-header-custom {
                padding: 1rem;
            }
            
            .card-header-custom .card-title {
                font-size: 1.1rem;
            }
            
            .action-buttons {
                justify-content: center;
            }
            
            .btn-modern {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
        }

        /* DataTables Responsive Overrides */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            padding: 0.5rem 1rem;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .dt-buttons {
            margin-bottom: 1rem;
        }

        .dt-button {
            border-radius: 25px !important;
            margin-right: 0.5rem !important;
            margin-bottom: 0.5rem !important;
    }
</style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Stats Cards Row -->
        <div class="row mb-4">
            <div class="col-6 col-md-3">
                <div class="card main-card">
                    <div class="card-body text-center">
                        <div class="user-avatar mx-auto mb-2">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="font-weight-bold text-primary">{{ $users->count() }}</h4>
                        <p class="text-muted mb-0">Total Usuarios</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card main-card">
                    <div class="card-body text-center">
                        <div class="user-avatar mx-auto mb-2" style="background: var(--success-gradient);">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h4 class="font-weight-bold text-success">{{ $users->where('email_verified_at', '!=', null)->count() }}</h4>
                        <p class="text-muted mb-0">Verificados</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card main-card">
                    <div class="card-body text-center">
                        <div class="user-avatar mx-auto mb-2" style="background: var(--warning-gradient);">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <h4 class="font-weight-bold text-warning">{{ $users->where('email_verified_at', null)->count() }}</h4>
                        <p class="text-muted mb-0">Pendientes</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card main-card">
                    <div class="card-body text-center">
                        <div class="user-avatar mx-auto mb-2" style="background: var(--info-gradient);">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h4 class="font-weight-bold text-info">{{ $users->filter(function($user) { return $user->roles->count() > 0; })->count() }}</h4>
                        <p class="text-muted mb-0">Con Roles</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop View -->
        <div class="desktop-view">
            <div class="card main-card">
                <div class="card-header card-header-custom">
            <h3 class="card-title">
                        <i class="fas fa-table mr-2"></i>
                        Lista de Usuarios
            </h3>
            <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="usersTable" class="table table-modern table-hover mb-0">
                            <thead>
                    <tr class="text-center">
                        <th>#</th>
                                    <th>Usuario</th>
                        <th>Email</th>
                        <th>Empresa</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="text-center">
                            <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="user-avatar mr-2" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </div>
                                                <div class="text-left">
                                                    <div class="font-weight-bold">{{ $user->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->company->name ?? 'N/A' }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                                <span class="badge badge-modern badge-info-modern">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if ($user->email_verified_at)
                                                <span class="badge badge-modern badge-success-modern">Verificado</span>
                                @else
                                                <span class="badge badge-modern badge-warning-modern">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->last_login)
                                    <span data-toggle="tooltip" title="{{ $user->last_login }}">
                                        {{ $user->last_login->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-muted">Nunca</span>
                                @endif
                            </td>
                            <td>
                                            <div class="action-buttons justify-content-center">
                                                @can('users.show')
                                                    <button type="button" class="action-btn btn-success-modern show-user"
                                            data-id="{{ $user->id }}" data-toggle="tooltip" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                                @endcan
                                                @can('users.edit')
                                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn btn-info-modern"
                                            data-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                                @endcan
                                    @if ($user->id !== auth()->id())
                                                    @can('users.destroy')
                                                        <button type="button" class="action-btn btn-danger-modern delete-user"
                                                data-id="{{ $user->id }}" data-toggle="tooltip" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                                    @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile View -->
        <div class="mobile-view">
            <div class="search-bar">
                <div class="input-group">
                    <input type="text" class="form-control search-input" id="mobileSearch" placeholder="Buscar usuarios...">
                    <div class="input-group-append">
                        <button class="btn btn-primary-modern" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="usersContainer">
                @foreach ($users as $user)
                    <div class="user-card" data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . ($user->company->name ?? '')) }}">
                        <div class="user-card-header">
                            <div class="d-flex align-items-center">
                                <div class="user-avatar">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div class="user-info flex-grow-1">
                                    <h5>{{ $user->name }}</h5>
                                    <p>{{ $user->email }}</p>
                                </div>
                                <div class="text-right">
                                    @if ($user->email_verified_at)
                                        <span class="badge badge-modern badge-success-modern">Verificado</span>
                                    @else
                                        <span class="badge badge-modern badge-warning-modern">Pendiente</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="user-card-body">
                            <div class="info-item">
                                <span class="info-label">Empresa:</span>
                                <span class="info-value">{{ $user->company->name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Roles:</span>
                                <div class="info-value">
                                    @foreach ($user->roles as $role)
                                        <span class="badge badge-modern badge-info-modern">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Último Acceso:</span>
                                <span class="info-value">
                                    @if ($user->last_login)
                                        {{ $user->last_login->diffForHumans() }}
                                    @else
                                        Nunca
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Acciones:</span>
                                <div class="action-buttons">
                                    @can('users.show')
                                        <button type="button" class="action-btn btn-success-modern show-user"
                                            data-id="{{ $user->id }}" data-toggle="tooltip" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    @can('users.edit')
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn btn-info-modern"
                                            data-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @if ($user->id !== auth()->id())
                                        @can('users.destroy')
                                            <button type="button" class="action-btn btn-danger-modern delete-user"
                                                data-id="{{ $user->id }}" data-toggle="tooltip" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Enhanced Modal -->
    <div class="modal fade modal-modern" id="showUserModal" tabindex="-1" role="dialog" aria-labelledby="showUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showUserModalLabel">
                        <i class="fas fa-user-circle mr-2"></i>
                        Detalles del Usuario
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label">Nombre:</span>
                                <span class="detail-value" id="userName"></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value" id="userEmail"></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Empresa:</span>
                                <span class="detail-value" id="userCompany"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label">Roles:</span>
                                <div class="detail-value" id="userRoles"></div>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Estado:</span>
                                <div class="detail-value" id="userVerification"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modern btn-primary-modern" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Initialize DataTables for desktop view
            $('#usersTable').DataTable({
                responsive: false,
                autoWidth: false,
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'collection',
                    text: '<i class="fas fa-download mr-2"></i>Exportar',
                    className: 'btn btn-primary btn-modern',
                    buttons: [{
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel mr-2"></i>Excel',
                            className: 'btn btn-success btn-modern',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                            className: 'btn btn-danger btn-modern',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print mr-2"></i>Imprimir',
                            className: 'btn btn-info btn-modern',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            }
                        }
                    ]
                }],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    emptyTable: "No hay información disponible",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ usuarios",
                    infoEmpty: "Mostrando 0 a 0 de 0 usuarios",
                    infoFiltered: "(Filtrado de _MAX_ total usuarios)",
                    lengthMenu: "Mostrar _MENU_ usuarios",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    search: "Buscar:",
                    zeroRecords: "Sin resultados encontrados",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                }
            });

            // Mobile search functionality
            $('#mobileSearch').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('.user-card').each(function() {
                    const cardData = $(this).data('search');
                    if (cardData.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Enhanced delete user functionality
            $('.delete-user').click(function() {
                const userId = $(this).data('id');
                const userName = $(this).closest('tr, .user-card').find('.font-weight-bold, h5').first().text();

                Swal.fire({
                    title: '¿Estás seguro?',
                    html: `¿Deseas eliminar al usuario <strong>${userName}</strong>?<br><small class="text-muted">Esta acción no se puede revertir</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74c3c',
                    cancelButtonColor: '#95a5a6',
                    confirmButtonText: '<i class="fas fa-trash mr-2"></i>Sí, eliminar',
                    cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-danger btn-modern',
                        cancelButton: 'btn btn-secondary btn-modern'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Eliminando...',
                            html: 'Por favor espera mientras se elimina el usuario',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        const csrfToken = $('meta[name="csrf-token"]').attr('content');

                        $.ajax({
                            url: `/users/delete/${userId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        title: '¡Eliminado!',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonText: 'Entendido',
                                        customClass: {
                                            confirmButton: 'btn btn-success btn-modern'
                                        },
                                        buttonsStyling: false
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: response.message,
                                        icon: 'error',
                                        confirmButtonText: 'Entendido',
                                        customClass: {
                                            confirmButton: 'btn btn-danger btn-modern'
                                        },
                                        buttonsStyling: false
                                    });
                                }
                            },
                            error: function(xhr) {
                                const response = xhr.responseJSON;
                Swal.fire({
                                    title: 'Error',
                                    text: response?.message || 'No se pudo eliminar el usuario',
                                    icon: 'error',
                                    confirmButtonText: 'Entendido',
                                    customClass: {
                                        confirmButton: 'btn btn-danger btn-modern'
                                    },
                                    buttonsStyling: false
                                });
                            }
                        });
                    }
                });
            });

            // Enhanced show user functionality
            $('.show-user').click(function() {
                const userId = $(this).data('id');

                // Show loading
                Swal.fire({
                    title: 'Cargando...',
                    html: 'Obteniendo información del usuario',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `/users/${userId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#userName').text(response.user.name);
                            $('#userEmail').text(response.user.email);
                            $('#userCompany').text(response.user.company_name || 'N/A');

                            const rolesHtml = response.user.roles.map(role =>
                                `<span class="badge badge-modern badge-info-modern mr-1">${role.display_name}</span>`
                            ).join('');
                            $('#userRoles').html(rolesHtml ||
                                '<span class="text-muted">Sin rol asignado</span>');

                            $('#userVerification').html(response.user.verified ?
                                '<span class="badge badge-modern badge-success-modern">Verificado</span>' :
                                '<span class="badge badge-modern badge-warning-modern">Pendiente de verificación</span>'
                            );

                            Swal.close();
                            $('#showUserModal').modal('show');
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'No se pudieron obtener los datos del usuario',
                                icon: 'error',
                                confirmButtonText: 'Entendido',
                                customClass: {
                                    confirmButton: 'btn btn-danger btn-modern'
                                },
                                buttonsStyling: false
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudieron obtener los datos del usuario',
                            icon: 'error',
                            confirmButtonText: 'Entendido',
                            customClass: {
                                confirmButton: 'btn btn-danger btn-modern'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            });

            // Smooth animations for cards
            $('.user-card, .main-card').each(function(index) {
                $(this).css({
                    'animation-delay': (index * 0.1) + 's',
                    'animation': 'fadeInUp 0.6s ease-out both'
                });
            });

            // Add CSS animation keyframes
            $('<style>').text(`
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            `).appendTo('head');
        });
    </script>
@stop
