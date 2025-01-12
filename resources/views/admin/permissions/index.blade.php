@extends('adminlte::page')

@section('title', 'Gestión de Permisos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Gestión de Permisos</h1>
        <div>
            <a href="{{ route('admin.permissions.report') }}" class="btn btn-info mr-2" target="_blank">
                <i class="fas fa-file-pdf mr-2"></i>Reporte
            </a>
            <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle mr-2"></i>Nuevo Permiso
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Widgets de Estadísticas --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalPermissions }}</h3>
                    <p>Total Permisos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-lock"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $activePermissions }}</h3>
                    <p>Permisos Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $rolesCount }}</h3>
                    <p>Roles Asociados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $unusedPermissions }}</h3>
                    <p>Permisos Sin Usar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Permisos --}}
    <div class="card card-outline card-primary ">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-key mr-2"></i>
                Lista de Permisos del Sistema
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="permissionsTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Guard</th>
                        <th>Roles Asignados</th>
                        <th>Usuarios con Permiso</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="font-weight-bold">{{ $permission->name }}</span>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $permission->guard_name }}</span>
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    {{ $permission->roles->count() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-warning">
                                    {{ $permission->users->count() }}
                                </span>
                            </td>
                            <td>{{ $permission->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-info btn-sm show-permission"
                                        data-id="{{ $permission->id }}" data-toggle="tooltip" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.permissions.edit', $permission->id) }}"
                                        class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm delete-permission"
                                        data-id="{{ $permission->id }}" data-toggle="tooltip" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#permissionsTable').DataTable({
                responsive: true,
                autoWidth: false,
                order: [
                    [1, 'asc']
                ], // Ordenar por nombre
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                }
            });

            // Eliminar permiso
            $('.delete-permission').click(function() {
                const permissionId = $(this).data('id');
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede revertir",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviar solicitud de eliminación
                        $.ajax({
                            url: `/admin/permissions/delete/${permissionId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                Swal.fire(
                                    '¡Eliminado!',
                                    'El permiso ha sido eliminado.',
                                    'success'
                                ).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error',
                                    'No se pudo eliminar el permiso.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Ver detalles del permiso
            $('.show-permission').click(function() {
                const permissionId = $(this).data('id');
                $.get(`/admin/permissions/${permissionId}`, function(permission) {
                    Swal.fire({
                        title: 'Detalles del Permiso',
                        html: `
                            <div class="text-left">
                                <p><strong>Nombre:</strong> ${permission.name}</p>
                                <p><strong>Guard:</strong> ${permission.guard_name}</p>
                                <p><strong>Roles:</strong> ${permission.roles.join(', ') || 'Ninguno'}</p>
                                <p><strong>Usuarios:</strong> ${permission.users.join(', ') || 'Ninguno'}</p>
                                <p><strong>Creado:</strong> ${permission.created_at}</p>
                                <p><strong>Actualizado:</strong> ${permission.updated_at}</p>
                            </div>
                        `,
                        icon: 'info'
                    });
                });
            });
        });
    </script>
@stop
