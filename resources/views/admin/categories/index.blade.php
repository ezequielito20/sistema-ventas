@extends('adminlte::page')

@section('title', 'Gestión de Categorías')

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
        <h1 class="text-dark font-weight-bold mb-2 mb-md-0">Gestión de Categorías</h1>
        <div class="btn-group-mobile">
            <a href="{{ route('admin.categories.report') }}" class="btn btn-info btn-sm" target="_blank">
                <i class="fas fa-file-pdf mr-1 d-md-inline d-none"></i>
                <span class="d-md-inline d-none">Reporte</span>
                <i class="fas fa-file-pdf d-md-none"></i>
            </a>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle mr-1 d-md-inline d-none"></i>
                <span class="d-md-inline d-none">Nueva Categoría</span>
                <i class="fas fa-plus-circle d-md-none"></i>
            </a>
        </div>
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
            {{-- Vista de tabla para pantallas grandes --}}
            <div class="d-none d-lg-block">
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
                                        <button type="button" class="btn btn-success btn-sm show-category"
                                            data-id="{{ $category->id }}" data-toggle="tooltip" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ route('admin.categories.edit', $category->id) }}"
                                            class="btn btn-info btn-sm" data-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm delete-category"
                                            data-id="{{ $category->id }}" data-toggle="tooltip" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Vista de tarjetas para móviles --}}
            <div class="d-lg-none">
                {{-- Barra de búsqueda para móviles --}}
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="mobileSearch" placeholder="Buscar categoría...">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="row" id="mobileCategoriesContainer">
                    @foreach ($categories as $category)
                        <div class="col-12 mb-3 category-card">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="category-avatar mr-3">
                                                <div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center"
                                                    style="width: 45px; height: 45px; font-size: 1.3em;">
                                                    {{ strtoupper(substr($category->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 font-weight-bold category-name">{{ $category->name }}</h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-tag mr-1"></i>
                                                    Categoría #{{ $loop->iteration }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Descripción:</small>
                                        <div class="category-description">
                                            {{ Str::limit($category->description, 80) ?? 'Sin descripción' }}
                                        </div>
                                    </div>

                                    <div class="btn-group-mobile">
                                        <button type="button" class="btn btn-success btn-sm show-category"
                                            data-id="{{ $category->id }}">
                                            <i class="fas fa-eye d-md-none"></i>
                                            <span class="d-none d-md-inline">Ver</span>
                                        </button>
                                        <a href="{{ route('admin.categories.edit', $category->id) }}"
                                            class="btn btn-info btn-sm">
                                            <i class="fas fa-edit d-md-none"></i>
                                            <span class="d-none d-md-inline">Editar</span>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm delete-category"
                                            data-id="{{ $category->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para mostrar categoría --}}
    <div class="modal fade" id="showCategoryModal" tabindex="-1" role="dialog" aria-labelledby="showCategoryModalLabel"
        aria-hidden="true">
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

        /* Estilos responsive para categorías */
        .btn-group-mobile {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .btn-group-mobile .btn {
            flex: 1;
            min-width: auto;
            font-size: 0.875rem;
            padding: 0.375rem 0.5rem;
            white-space: nowrap;
        }

        /* Estilos para las tarjetas de categorías */
        .category-card {
            transition: transform 0.2s ease-in-out;
        }

        .category-card:hover {
            transform: translateY(-2px);
        }

        .category-card .card {
            border-left: 4px solid #007bff;
        }

        .category-avatar {
            transition: transform .3s ease-in-out;
        }

        .category-avatar:hover {
            transform: scale(1.1);
        }

        /* Media queries responsive */
        @media (max-width: 768px) {
            .btn-group-mobile .btn {
                font-size: 0.8rem;
                padding: 0.25rem 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .btn-group-mobile .btn {
                font-size: 0.75rem;
                padding: 0.25rem 0.4rem;
                min-width: 35px;
            }

            .category-card .btn-group-mobile .btn {
                flex: 0 1 auto;
                min-width: 35px;
            }
        }

        /* Mejoras para tablets */
        @media (min-width: 769px) and (max-width: 1199px) {
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .btn-group .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
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

            // Búsqueda en vista móvil
            $('#mobileSearch').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                
                $('.category-card').each(function() {
                    const categoryName = $(this).find('.category-name').text().toLowerCase();
                    const categoryDescription = $(this).find('.category-description').text().toLowerCase();
                    
                    if (categoryName.includes(searchTerm) || 
                        categoryDescription.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Inicialización de DataTables
            $('#categoriesTable').DataTable({
                responsive: true,
                autoWidth: false,
                "language": {
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Categorías",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Categorías",
                    "infoFiltered": "(Filtrado de _MAX_ total Categorías)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ Categorías",
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
                    url: `/categories/${categoryId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Llenar datos en el modal
                            $('#categoryName').text(response.category.name);
                            $('#categoryDescription').text(response.category.description);
                            $('#categoryCreated').text(response.category.created_at);
                            $('#categoryUpdated').text(response.category.updated_at);

                            // Cerrar loading y mostrar modal
                            Swal.close();
                            $('#showCategoryModal').modal('show');
                        } else {
                            Swal.fire('Error',
                                'No se pudieron obtener los datos de la categoría', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron obtener los datos de la categoría',
                            'error');
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
                        $.ajax({
                            url: `/categories/delete/${categoryId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
                                    'No se pudo eliminar la categoría', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
