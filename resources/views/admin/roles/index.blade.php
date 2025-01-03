@extends('adminlte::page')

@section('title', 'Gestión de Roles')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Gestión de Roles</h1>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-2"></i>Crear Nuevo Rol
        </a>
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
                            <td >{{ $loop->iteration }}</td>
                            <td>
                                <span class="font-weight-bold">{{ $role->name }}</span>
                            </td>
                            <td>{{ $role->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" 
                                       class="btn btn-info btn-sm" 
                                       data-toggle="tooltip" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-danger btn-sm delete-role" 
                                            data-id="{{ $role->id }}"
                                            data-toggle="tooltip" 
                                            title="Eliminar">
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <style>
        .card {
            border-radius: 0.75rem;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        .table th {
            background-color: #007bff !important;
            color: white !important;
        }
        .btn-group {
            box-shadow: 0 2px 4px rgba(0,0,0,.04);
        }
        .btn-sm {
            border-radius: 0.5rem;
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
                buttons: [
                    {
                        extend: 'collection',
                        text: '<i class="fas fa-file-export mr-2"></i>Exportar',
                        className: 'btn btn-primary',
                        buttons: [
                            {
                                extend: 'excel',
                                text: '<i class="fas fa-file-excel mr-2"></i>Excel',
                                className: 'btn btn-success',
                                exportOptions: { columns: [0,1,2,3] }
                            },
                            {
                                extend: 'pdf',
                                text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                                className: 'btn btn-danger',
                                exportOptions: { columns: [0,1,2,3] }
                            },
                            {
                                extend: 'print',
                                text: '<i class="fas fa-print mr-2"></i>Imprimir',
                                className: 'btn btn-info',
                                exportOptions: { columns: [0,1,2,3] }
                            }
                        ]
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
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
                        // Enviar solicitud de eliminación
                        axios.delete(`/roles/delete/${roleId}`)
                            .then(response => {
                                Swal.fire(
                                    '¡Eliminado!',
                                    'El rol ha sido eliminado.',
                                    'success'
                                ).then(() => {
                                    window.location.reload();
                                });
                            })
                            .catch(error => {
                                Swal.fire(
                                    'Error',
                                    'No se pudo eliminar el rol.',
                                    'error'
                                );
                            });
                    }
                });
            });
        });
    </script>
@stop