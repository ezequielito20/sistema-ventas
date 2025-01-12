@extends('adminlte::page')

@section('title', 'Gestión de Roles')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Gestión de Roles</h1>
        <div>
            <a href="{{ route('admin.roles.report') }}" class="btn btn-info mr-2" target="_blank">
                <i class="fas fa-file-pdf mr-2"></i>Reporte
            </a>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle mr-2"></i>Crear Nuevo Rol
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-shield mr-2"></i>
                Roles del Sistema
            </h3>
        </div>
        <div class="card-body">
            <table id="rolesTable" class="table table-striped table-hover table-sm">
                <thead class="bg-primary text-white">
                    <tr class="text-center">
                        <th style="width: 5%">#</th>
                        <th style="width: 20%">Nombre del Rol</th>
                        <th style="width: 20%">Fecha de Creación</th>
                        <th style="width: 20%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr class="text-center">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="font-weight-bold">{{ $role->name }}</span>
                            </td>
                            <td>{{ $role->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success btn-sm show-role"
                                        data-id="{{ $role->id }}" data-toggle="tooltip" title="Mostrar">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-info btn-sm"
                                        data-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a type="button" class="btn btn-warning btn-sm assign-permissions"
                                        data-id="{{ $role->id }}" data-name="{{ $role->name }}" data-toggle="tooltip"
                                        title="Asignar Permisos">
                                        <i class="fas fa-key"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm delete-role"
                                        data-id="{{ $role->id }}" data-toggle="tooltip" title="Eliminar">
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

    {{-- Modal para mostrar rol --}}
    <div class="modal fade" id="showRoleModal" tabindex="-1" role="dialog" aria-labelledby="showRoleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="showRoleModalLabel">
                        <i class="fas fa-user-shield mr-2"></i>
                        Detalles del Rol
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Nombre del Rol:</label>
                                <p id="roleName" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Fecha de Creación:</label>
                                <p id="roleCreated" class="form-control-static"></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Última Actualización:</label>
                                <p id="roleUpdated" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Usuarios Asignados:</label>
                                <p id="roleUsers" class="form-control-static"></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Permisos Asignados:</label>
                                <p id="rolePermissions" class="form-control-static"></p>
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

    {{-- Modal de Asignación de Permisos --}}
    <div class="modal fade" id="permissionsModal" tabindex="-1" role="dialog" aria-labelledby="permissionsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="permissionsModalLabel">
                        <i class="fas fa-key mr-2"></i>
                        Asignar Permisos al Rol: <span id="roleName" class="font-weight-bold"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="search-box">
                                <input type="text" id="searchPermission" class="form-control"
                                    placeholder="Buscar permisos...">
                                <i class="fas fa-search search-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-center justify-content-end">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="selectAllPermissions">
                                <label class="custom-control-label pl-2" for="selectAllPermissions"
                                    style="margin-left: 15px;">
                                    Seleccionar todos los permisos
                                </label>
                            </div>
                        </div>
                    </div>

                    <form id="permissionsForm">
                        @csrf
                        <input type="hidden" id="roleId" name="role_id">

                        <div class="row permissions-container">
                            @foreach ($permissions as $module => $modulePermissions)
                                <div class="col-md-6 mb-4">
                                    <div class="card card-outline card-warning h-100">
                                        <div class="card-header">
                                            <div
                                                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                                <h3 class="card-title text-capitalize mb-2 mb-md-0">
                                                    <i class="fas fa-folder mr-2"></i>{{ $module }}
                                                </h3>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input group-selector"
                                                        id="group_{{ $module }}" data-group="{{ $module }}">
                                                    <label class="custom-control-label pl-2"
                                                        for="group_{{ $module }}" style="margin-left: 15px;">
                                                        Seleccionar todo
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @foreach ($modulePermissions as $permission)
                                                <div class="permission-item" data-group="{{ $module }}">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox"
                                                            class="custom-control-input permission-checkbox"
                                                            id="permission_{{ $permission->id }}"
                                                            value="{{ $permission->id }}"
                                                            data-group="{{ $module }}">
                                                        <label class="custom-control-label"
                                                            for="permission_{{ $permission->id }}">
                                                            {{ $permission->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" id="savePermissions">
                        <i class="fas fa-save mr-2"></i>Guardar Cambios
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

        /* Estilos para el modal de permisos */
        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-box .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .search-box input {
            padding-right: 40px;
            border-radius: 20px;
        }

        .permission-group .card {
            transition: all 0.3s ease;
        }

        .permission-group .card:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .permission-item {
            padding: 8px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .permission-item:hover {
            background-color: #f8f9fa;
        }

        .custom-switch .custom-control-label::before {
            width: 2.5rem;
        }

        .custom-switch .custom-control-label::after {
            width: calc(1.5rem - 4px);
        }

        .custom-switch .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(1rem);
        }

        .custom-control-label {
            padding-left: 15px;
            cursor: pointer;
        }

        .modal-xl {
            max-width: 1200px;
        }

        .permissions-container {
            max-height: 70vh;
            overflow-y: auto;
        }

        .permissions-container::-webkit-scrollbar {
            width: 8px;
        }

        .permissions-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .permissions-container::-webkit-scrollbar-thumb {
            background: #ffc107;
            border-radius: 4px;
        }

        .permissions-container::-webkit-scrollbar-thumb:hover {
            background: #e0a800;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicialización de tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Inicialización de DataTables
            $('#rolesTable').DataTable({
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
                                columns: [0, 1, 2, 3]
                            }
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                            className: 'btn btn-danger',
                            exportOptions: {
                                columns: [0, 1, 2, 3]
                            }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print mr-2"></i>Imprimir',
                            className: 'btn btn-info',
                            exportOptions: {
                                columns: [0, 1, 2, 3]
                            }
                        }
                    ]
                }],
                "language": {
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Roles",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Roles",
                    "infoFiltered": "(Filtrado de _MAX_ total Roles)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ Roles",
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

            // Manejo de eliminación de roles
            $('.delete-role').click(function() {
                const roleId = $(this).data('id');

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
                        // Token CSRF
                        const csrfToken = $('meta[name="csrf-token"]').attr('content');

                        // Enviar solicitud de eliminación
                        $.ajax({
                            url: `/roles/delete/${roleId}`,
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
                                        // Recargar la página o eliminar la fila
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error',
                                        response.message,
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr) {
                                const response = xhr.responseJSON;
                                Swal.fire(
                                    'Error',
                                    response.message ||
                                    'No se pudo eliminar el rol',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Manejo de visualización de rol
            $('.show-role').click(function() {
                const roleId = $(this).data('id');

                // Mostrar loading
                Swal.fire({
                    title: 'Cargando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Obtener datos del rol
                $.ajax({
                    url: `/roles/${roleId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Llenar datos en el modal
                            $('#roleName').text(response.role.name);
                            $('#roleCreated').text(response.role.created_at);
                            $('#roleUpdated').text(response.role.updated_at);
                            $('#roleUsers').text(response.role.users_count + ' usuario(s)');
                            $('#rolePermissions').text(response.role.permissions_count +
                                ' permiso(s)');

                            // Cerrar loading y mostrar modal
                            Swal.close();
                            $('#showRoleModal').modal('show');
                        } else {
                            Swal.fire('Error', 'No se pudieron obtener los datos del rol',
                                'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron obtener los datos del rol', 'error');
                    }
                });
            });

            // Scripts para el modal de permisos
            $('.assign-permissions').click(function() {
                const roleId = $(this).data('id');
                const roleName = $(this).data('name');

                $('#roleId').val(roleId);
                $('#roleName').text(roleName);

                // Limpiar checkboxes
                $('.permission-checkbox').prop('checked', false);

                // Cargar permisos actuales del rol
                $.get(`/roles/${roleId}/permissions`, function(data) {
                    data.permissions.forEach(function(permission) {
                        $(`#permission_${permission.id}`).prop('checked', true);
                    });

                    // Actualizar estados de los selectores de grupo
                    updateGroupSelectors();
                });

                $('#permissionsModal').modal('show');
            });

            // Búsqueda de permisos
            $('#searchPermission').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();

                // Recorrer cada card de módulo
                $('.permissions-container .card').each(function() {
                    const $card = $(this);
                    const $permissionItems = $card.find('.permission-item');
                    let hasVisiblePermissions = false;

                    // Revisar los permisos dentro de este card
                    $permissionItems.each(function() {
                        const text = $(this).text().toLowerCase();
                        const isVisible = text.includes(searchTerm);
                        $(this).toggle(isVisible);
                        if (isVisible) {
                            hasVisiblePermissions = true;
                        }
                    });

                    // Mostrar u ocultar el card completo según si tiene permisos visibles
                    $card.closest('.col-md-6').toggle(hasVisiblePermissions);
                });
            });

            // Selector de grupo
            $('.group-selector').change(function() {
                const group = $(this).data('group');
                const checked = $(this).prop('checked');

                $(`.permission-checkbox[data-group="${group}"]`).prop('checked', checked);
            });

            // Actualizar selector de grupo cuando cambian los permisos individuales
            $('.permission-checkbox').change(function() {
                updateGroupSelectors();
            });

            // Guardar cambios
            $('#savePermissions').click(function() {
                const roleId = $('#roleId').val();
                const permissions = $('.permission-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                $.ajax({
                    url: `/roles/${roleId}/permissions`,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        permissions: permissions
                    },
                    success: function(response) {
                        $('#permissionsModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Los permisos han sido actualizados correctamente',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        // Recargar la tabla de roles
                        window.LaravelDataTables['rolesTable'].ajax.reload();
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar los permisos'
                        });
                    }
                });
            });

            function updateGroupSelectors() {
                $('.group-selector').each(function() {
                    const group = $(this).data('group');
                    const totalPermissions = $(`.permission-checkbox[data-group="${group}"]`).length;
                    const checkedPermissions = $(`.permission-checkbox[data-group="${group}"]:checked`)
                        .length;

                    $(this).prop('checked', totalPermissions === checkedPermissions);
                });
            }

            // Selector para todos los permisos
            $('#selectAllPermissions').change(function() {
                const isChecked = $(this).prop('checked');
                $('.permission-checkbox').prop('checked', isChecked);
                $('.group-selector').prop('checked', isChecked);
            });

            // Actualizar el selector general cuando se cambien los permisos individuales
            $('.permission-checkbox').change(function() {
                const totalPermissions = $('.permission-checkbox').length;
                const checkedPermissions = $('.permission-checkbox:checked').length;
                $('#selectAllPermissions').prop('checked', totalPermissions === checkedPermissions);
            });
        });
    </script>
@stop
