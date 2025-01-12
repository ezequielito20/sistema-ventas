@extends('adminlte::page')

@section('title', 'Gestión de Usuarios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Gestión de Usuarios</h1>
        <div>
            <a href="{{ route('admin.users.report') }}" class="btn btn-info mr-2" target="_blank">
                <i class="fas fa-file-pdf mr-2"></i>Reporte
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus mr-2"></i>Crear Nuevo Usuario
            </a>
        </div>
    </div>
@stop
<style>
    .card {
        border-radius: 0.75rem;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, .125);
    }

    .table th {
        background-color: #007bff !important;
        color: white !important;
    }

    .btn-group {
        box-shadow: 0 2px 4px rgba(0, 0, 0, .04);
    }

    .btn-sm {
        border-radius: 0.5rem;
    }

    .modal-header {
        border-radius: 0.3rem 0.3rem 0 0;
    }

    .modal-content {
        border-radius: 0.3rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .form-control-static {
        padding: 0.375rem 0.75rem;
        margin-bottom: 0;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }

    .badge {
        font-size: 85%;
        font-weight: 600;
        padding: 0.35em 0.65em;
    }

    #userActivity {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
</style>
@section('content')
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users mr-2"></i>
                Usuarios del Sistema
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="usersTable" class="table table-striped table-hover table-sm">
                <thead class="bg-primary text-white">
                    <tr class="text-center">
                        <th>#</th>
                        <th>Nombre</th>
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
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->company->name ?? 'N/A' }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="badge badge-info">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if ($user->email_verified_at)
                                    <span class="badge badge-success">Verificado</span>
                                @else
                                    <span class="badge badge-warning">Pendiente</span>
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
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success btn-sm show-user"
                                        data-id="{{ $user->id }}" data-toggle="tooltip" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-info btn-sm"
                                        data-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <button type="button" class="btn btn-warning btn-sm reset-password"
                                            data-id="{{ $user->id }}" data-toggle="tooltip"
                                            title="Resetear Contraseña">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm delete-user"
                                            data-id="{{ $user->id }}" data-toggle="tooltip" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal para mostrar usuario --}}
    <div class="modal fade" id="showUserModal" tabindex="-1" role="dialog" aria-labelledby="showUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="showUserModalLabel">
                        <i class="fas fa-user mr-2"></i>
                        Detalles del Usuario
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Nombre:</label>
                                <p id="userName" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Email:</label>
                                <p id="userEmail" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Empresa:</label>
                                <p id="userCompany" class="form-control-static"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Roles:</label>
                                <div id="userRoles" class="form-control-static"></div>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Estado de Verificación:</label>
                                <p id="userVerification" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

@stop
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicialización de tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Inicialización de DataTables
            $('#usersTable').DataTable({
                responsive: true,
                autoWidth: false,
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'collection',
                    text: '<i class="fas fa-file-export mr-2"></i>Exportar',
                    className: 'btn btn-primary',
                    buttons: [{
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel mr-2"></i>Excel',
                            className: 'btn btn-success',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                            className: 'btn btn-danger',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print mr-2"></i>Imprimir',
                            className: 'btn btn-info',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            }
                        }
                    ]
                }],
                "language": {
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Usuarios",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Usuarios",
                    "infoFiltered": "(Filtrado de _MAX_ total Usuarios)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ Usuarios",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscador:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                }
            });

            // Manejo de eliminación de usuarios
            $('.delete-user').click(function() {
                const userId = $(this).data('id');

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
                                        icon: 'success'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                const response = xhr.responseJSON;
                                Swal.fire('Error', response.message ||
                                    'No se pudo eliminar el usuario', 'error');
                            }
                        });
                    }
                });
            });

            // Manejo de reseteo de contraseña
            $('.reset-password').click(function() {
                const userId = $(this).data('id');

                Swal.fire({
                    title: '¿Resetear contraseña?',
                    text: "Se enviará un email al usuario con las instrucciones",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, resetear',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const csrfToken = $('meta[name="csrf-token"]').attr('content');

                        $.ajax({
                            url: `/users/reset-password/${userId}`,
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            success: function(response) {
                                Swal.fire('¡Éxito!',
                                    'Se ha enviado el email de reseteo', 'success');
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo resetear la contraseña',
                                    'error');
                            }
                        });
                    }
                });
            });

            // Manejo de visualización de usuario
            $('.show-user').click(function() {
                const userId = $(this).data('id');

                Swal.fire({
                    title: 'Cargando...',
                    allowOutsideClick: false,
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
                            $('#userCompany').text(response.user.company_name);

                            const rolesHtml = response.user.roles.map(role =>
                                `<span class="badge badge-info mr-1">${role.display_name}</span>`
                            ).join('');
                            $('#userRoles').html(rolesHtml ||
                                '<span class="text-muted">Sin rol asignado</span>');

                            $('#userVerification').html(response.user.verified ?
                                '<span class="badge badge-success">Verificado</span>' :
                                '<span class="badge badge-warning">Pendiente de verificación</span>'
                            );

                            Swal.close();
                            $('#showUserModal').modal('show');
                        } else {
                            Swal.fire('Error', 'No se pudieron obtener los datos del usuario',
                                'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron obtener los datos del usuario',
                            'error');
                    }
                });
            });
        });
    </script>
@stop
