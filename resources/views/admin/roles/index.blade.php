@extends('adminlte::page')

@section('title', 'Gestión de Roles')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Gestión de Roles</h1>
        <div>
            @can('roles.report')
                <a href="{{ route('admin.roles.report') }}" class="btn btn-info mr-2" target="_blank">
                    <i class="fas fa-file-pdf mr-2"></i>Reporte
                </a>
            @endcan
            @can('roles.create')
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle mr-2"></i>Crear Nuevo Rol
                </a>
            @endcan
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
                                    @can('roles.show')
                                        <button type="button" class="btn btn-success btn-sm show-role"
                                            data-id="{{ $role->id }}" data-toggle="tooltip" title="Mostrar">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    @can('roles.edit')
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-info btn-sm"
                                            data-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('roles.edit')
                                        <a type="button" class="btn btn-warning btn-sm assign-permissions"
                                            data-id="{{ $role->id }}" data-name="{{ $role->name }}" data-toggle="tooltip"
                                            title="Asignar Permisos">
                                            <i class="fas fa-key"></i>
                                        </a>
                                    @endcan
                                    @can('roles.destroy')
                                        <button type="button" class="btn btn-danger btn-sm delete-role"
                                            data-id="{{ $role->id }}" data-toggle="tooltip" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
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
                                                            data-group="{{ $module }}"
                                                            data-name="{{ $permission->name }}">
                                                        <label class="custom-control-label"
                                                            for="permission_{{ $permission->id }}"
                                                            title="{{ $permission->name }}">
                                                            {{ $permission->friendly_name }}
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
                                let errorMessage = 'No se pudo eliminar el rol';
                                let detailsHtml = '';
                                
                                try {
                                    const response = xhr.responseJSON;
                                    if (response && response.message) {
                                        errorMessage = response.message;
                                        
                                        // Agregar detalles si están disponibles
                                        if (response.details) {
                                            let details = [];
                                            
                                            if (response.details.error_type) {
                                                details.push(`Tipo: ${response.details.error_type}`);
                                            }
                                            
                                            if (response.details.users_count) {
                                                details.push(`Usuarios asignados: ${response.details.users_count}`);
                                            }
                                            
                                            if (response.details.role_name) {
                                                details.push(`Rol: ${response.details.role_name}`);
                                            }
                                            
                                            if (details.length > 0) {
                                                detailsHtml = '<br><small class="text-muted"><strong>Información:</strong><br>' + 
                                                             details.join('<br>') + '</small>';
                                            }
                                        }
                                    }
                                } catch (e) {
                                    console.error('Error al procesar respuesta de error:', e);
                                    if (xhr.status) {
                                        errorMessage += ` (Código: ${xhr.status})`;
                                    }
                                }
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error al Eliminar Rol',
                                    html: errorMessage + detailsHtml,
                                    confirmButtonText: 'Entendido',
                                    customClass: {
                                        confirmButton: 'btn btn-danger'
                                    },
                                    buttonsStyling: false
                                });
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
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al Obtener Datos',
                                text: 'No se pudieron obtener los datos del rol',
                                confirmButtonText: 'Entendido',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                },
                                buttonsStyling: false
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'No se pudieron obtener los datos del rol';
                        
                        try {
                            const response = xhr.responseJSON;
                            if (response && response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            console.error('Error al procesar respuesta:', e);
                            if (xhr.status) {
                                errorMessage += ` (Código: ${xhr.status})`;
                            }
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al Obtener Datos',
                            text: errorMessage,
                            confirmButtonText: 'Entendido',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            buttonsStyling: false
                        });
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
                $.get(`/roles/${roleId}/permissions`)
                    .done(function(data) {
                        if (data.status === 'success') {
                            data.permissions.forEach(function(permission) {
                                $(`#permission_${permission.id}`).prop('checked', true);
                            });

                            // Actualizar estados de los selectores de grupo
                            updateGroupSelectors();
                            
                            // Mostrar información adicional del rol si está disponible
                            if (data.role_info && data.role_info.is_system_role) {
                                $('#roleName').append(' <small class="badge badge-warning">Rol del Sistema</small>');
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al Cargar Permisos',
                                text: data.message || 'No se pudieron cargar los permisos del rol'
                            });
                        }
                    })
                    .fail(function(xhr) {
                        let errorMessage = 'No se pudieron cargar los permisos del rol';
                        
                        try {
                            const response = xhr.responseJSON;
                            if (response && response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            console.error('Error al procesar respuesta:', e);
                            if (xhr.status) {
                                errorMessage += ` (Código: ${xhr.status})`;
                            }
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al Cargar Permisos',
                            text: errorMessage,
                            confirmButtonText: 'Cerrar',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            buttonsStyling: false
                        }).then(() => {
                            $('#permissionsModal').modal('hide');
                        });
                    });

                $('#permissionsModal').modal('show');
            });

            // Selector para todos los permisos
            $('#selectAllPermissions').change(function() {
                const isChecked = $(this).prop('checked');
                const searchTerm = $('#searchPermission').val().toLowerCase();

                if (searchTerm) {
                    // Si hay término de búsqueda, solo seleccionar los permisos visibles
                    $('.permission-checkbox').each(function() {
                        const $permissionItem = $(this).closest('.permission-item');
                        if ($permissionItem.is(':visible')) {
                            $(this).prop('checked', isChecked);
                        }
                    });

                    // Actualizar los selectores de grupo
                    $('.group-selector').each(function() {
                        const $card = $(this).closest('.card');
                        if ($card.closest('.col-md-6').is(':visible')) {
                            const totalVisible = $card.find('.permission-item:visible').length;
                            const checkedVisible = $card.find(
                                '.permission-item:visible .permission-checkbox:checked').length;
                            $(this).prop('checked', totalVisible === checkedVisible);
                        }
                    });
                } else {
                    // Si no hay búsqueda, comportamiento normal
                    $('.permission-checkbox').prop('checked', isChecked);
                    $('.group-selector').prop('checked', isChecked);
                }
            });

            // Búsqueda de permisos
            $('#searchPermission').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();

                $('.permissions-container .card').each(function() {
                    const $card = $(this);
                    const $permissionItems = $card.find('.permission-item');
                    let hasVisiblePermissions = false;

                    $permissionItems.each(function() {
                        const friendlyText = $(this).find('label').text().toLowerCase();
                        const technicalText = $(this).find('input').data('name')
                            .toLowerCase();
                        const isVisible = friendlyText.includes(searchTerm) || technicalText
                            .includes(searchTerm);
                        $(this).toggle(isVisible);
                        if (isVisible) {
                            hasVisiblePermissions = true;
                        }
                    });

                    $card.closest('.col-md-6').toggle(hasVisiblePermissions);
                });

                // Resetear el checkbox general
                $('#selectAllPermissions').prop('checked', false);
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
                    error: function(xhr) {
                        let errorMessage = 'Hubo un problema al actualizar los permisos';
                        let detailsHtml = '';
                        
                        try {
                            const response = xhr.responseJSON;
                            if (response && response.message) {
                                errorMessage = response.message;
                                
                                // Agregar detalles adicionales si están disponibles
                                if (response.details) {
                                    let details = [];
                                    
                                    if (response.details.error_type) {
                                        details.push(`Tipo: ${response.details.error_type}`);
                                    }
                                    
                                    if (response.details.debug_info) {
                                        const debug = response.details.debug_info;
                                        if (debug.role_id && debug.role_id !== 'N/A') {
                                            details.push(`ID del rol: ${debug.role_id}`);
                                        }
                                        if (debug.company_id && debug.company_id !== 'N/A') {
                                            details.push(`ID de empresa: ${debug.company_id}`);
                                        }
                                        if (debug.permissions_received !== undefined) {
                                            details.push(`Permisos recibidos: ${debug.permissions_received}`);
                                        }
                                        if (debug.user_authenticated !== undefined) {
                                            details.push(`Usuario autenticado: ${debug.user_authenticated ? 'Sí' : 'No'}`);
                                        }
                                    }
                                    
                                    if (details.length > 0) {
                                        detailsHtml = '<br><small class="text-muted"><strong>Detalles:</strong><br>' + 
                                                     details.join('<br>') + '</small>';
                                    }
                                }
                            }
                        } catch (e) {
                            console.error('Error al procesar respuesta de error:', e);
                            // Fallback a mensaje genérico si no se puede parsear la respuesta
                            if (xhr.status) {
                                errorMessage += ` (Código: ${xhr.status})`;
                            }
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al Actualizar Permisos',
                            html: errorMessage + detailsHtml,
                            confirmButtonText: 'Entendido',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            buttonsStyling: false,
                            allowOutsideClick: false
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
        });
    </script>
@stop
