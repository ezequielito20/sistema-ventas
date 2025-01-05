@extends('adminlte::page')

@section('title', 'Gestión de Categorías')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Gestión de Categorías</h1>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-2"></i>Nueva Categoría
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tags mr-2"></i>
                Lista de Categorías
            </h3>
        </div>
        <div class="card-body">
            <table id="categoriesTable" class="table table-striped table-hover table-sm">
                <thead class="bg-primary text-white">
                    <tr class="text-center">
                        <th style="width: 10%">#</th>
                        <th style="width: 30%">Nombre</th>
                        <th style="width: 40%">Descripción</th>
                        <th style="width: 20%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr class="text-center">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ Str::limit($category->description, 100) ?? 'Sin descripción' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" 
                                            class="btn btn-success btn-sm show-category" 
                                            data-id="{{ $category->id }}"
                                            data-toggle="tooltip" 
                                            title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" 
                                       class="btn btn-info btn-sm" 
                                       data-toggle="tooltip" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-danger btn-sm delete-category" 
                                            data-id="{{ $category->id }}"
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

    {{-- Modal para mostrar categoría --}}
    <div class="modal fade" id="showCategoryModal" tabindex="-1" role="dialog" aria-labelledby="showCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="showCategoryModalLabel">
                        <i class="fas fa-tag mr-2"></i>
                        Detalles de la Categoría
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Nombre:</label>
                                <p id="categoryName" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Descripción:</label>
                                <p id="categoryDescription" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Fecha de Creación:</label>
                                <p id="categoryCreated" class="form-control-static"></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Última Actualización:</label>
                                <p id="categoryUpdated" class="form-control-static"></p>
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
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicialización de tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Inicialización de DataTables
            $('#categoriesTable').DataTable({
                responsive: true,
                autoWidth: false,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                }
            });

            // Manejo de visualización de categoría
            $('.show-category').click(function() {
                const categoryId = $(this).data('id');
                
                // Mostrar loading
                Swal.fire({
                    title: 'Cargando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Obtener datos de la categoría
                $.ajax({
                    url: `{{ route('admin.categories.show', '') }}/${categoryId}`,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            // Llenar datos en el modal
                            $('#categoryName').text(response.category.name);
                            $('#categoryDescription').text(response.category.description || 'Sin descripción');
                            $('#categoryCreated').text(response.category.created_at);
                            $('#categoryUpdated').text(response.category.updated_at);
                            
                            // Cerrar loading y mostrar modal
                            Swal.close();
                            $('#showCategoryModal').modal('show');
                        } else {
                            Swal.fire('Error', 'No se pudieron obtener los datos de la categoría', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron obtener los datos de la categoría', 'error');
                    }
                });
            });

            // Manejo de eliminación de categorías
            $('.delete-category').click(function() {
                const categoryId = $(this).data('id');
                
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
                            url: `{{ route('admin.categories.destroy', '') }}/${categoryId}`,
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
                                    response.message || 'No se pudo eliminar la categoría',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
