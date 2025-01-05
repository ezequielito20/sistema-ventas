@extends('adminlte::page')

@section('title', 'Gestión de Categorías')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Gestión de Categorías</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createCategoryModal">
            <i class="fas fa-plus-circle mr-2"></i>Nueva Categoría
        </button>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Tarjeta de Estadísticas -->
        <div class="col-12 mb-3">
            <div class="row">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $totalCategories ?? 0 }}</h3>
                            <p>Total Categorías</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tags"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $activeCategories ?? 0 }}</h3>
                            <p>Categorías Activas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $productsCount ?? 0 }}</h3>
                            <p>Productos Asociados</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $inactiveCategories ?? 0 }}</h3>
                            <p>Categorías Inactivas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Categorías -->
        <div class="col-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tags mr-2"></i>
                        Lista de Categorías
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="categoriesTable" class="table table-striped table-hover table-sm">
                        <thead class="bg-primary text-white">
                            <tr class="text-center">
                                <th width="5%">#</th>
                                <th width="10%">Imagen</th>
                                <th width="20%">Nombre</th>
                                <th width="25%">Descripción</th>
                                <th width="10%">Estado</th>
                                <th width="15%">Productos</th>
                                <th width="15%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr class="text-center">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <img src="{{ $category->image_url }}" 
                                             alt="{{ $category->name }}" 
                                             class="img-thumbnail category-thumbnail"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ Str::limit($category->description, 50) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $category->active ? 'success' : 'danger' }}">
                                            {{ $category->active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $category->products_count }} productos
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" 
                                                    class="btn btn-success btn-sm show-category" 
                                                    data-id="{{ $category->id }}"
                                                    data-toggle="tooltip" 
                                                    title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-info btn-sm edit-category" 
                                                    data-id="{{ $category->id }}"
                                                    data-toggle="tooltip" 
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
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
        </div>
    </div>

    <!-- Modal Crear/Editar Categoría -->
    <div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="categoryModalLabel">
                        <i class="fas fa-tag mr-2"></i>
                        <span id="modalAction">Nueva</span> Categoría
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="categoryForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Descripción</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="status">Estado</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="1">Activa</option>
                                        <option value="0">Inactiva</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image">Imagen</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="image" name="image">
                                        <label class="custom-file-label" for="image">Elegir archivo</label>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <img id="imagePreview" src="" alt="Vista previa" class="img-thumbnail d-none" style="max-width: 200px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Guardar
                        </button>
                    </div>
                </form>
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
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .small-box {
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }

        .small-box:hover {
            transform: translateY(-5px);
        }

        .btn-group {
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .btn-sm {
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-sm:hover {
            transform: translateY(-2px);
        }

        .category-thumbnail {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .category-thumbnail:hover {
            transform: scale(1.1);
        }

        .badge {
            font-size: 85%;
            font-weight: 600;
            padding: 0.35em 0.65em;
        }

        .modal-content {
            border-radius: 0.75rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .custom-file-label {
            border-radius: 0.5rem;
        }

        .table th {
            background-color: #007bff !important;
            color: white !important;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicialización de DataTables
            $('#categoriesTable').DataTable({
                responsive: true,
                autoWidth: false,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                }
            });

            // Preview de imagen
            $('#image').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').attr('src', e.target.result).removeClass('d-none');
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Inicialización de tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Variables globales
            let actionUrl = '';
            let method = '';

            // Abrir modal para crear
            $('.btn-primary[data-target="#createCategoryModal"]').click(function() {
                $('#modalAction').text('Nueva');
                $('#categoryForm').trigger('reset');
                $('#imagePreview').addClass('d-none');
                actionUrl = '/admin/categories';
                method = 'POST';
                $('#categoryModal').modal('show');
            });

            // Abrir modal para editar
            $('.edit-category').click(function() {
                const categoryId = $(this).data('id');
                $('#modalAction').text('Editar');
                actionUrl = `/admin/categories/${categoryId}`;
                method = 'PUT';

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
                    url: `/admin/categories/${categoryId}/edit`,
                    type: 'GET',
                    success: function(response) {
                        $('#name').val(response.category.name);
                        $('#description').val(response.category.description);
                        $('#status').val(response.category.active ? '1' : '0');
                        
                        if (response.category.image_url) {
                            $('#imagePreview').attr('src', response.category.image_url).removeClass('d-none');
                        }

                        Swal.close();
                        $('#categoryModal').modal('show');
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron cargar los datos de la categoría', 'error');
                    }
                });
            });

            // Mostrar detalles de la categoría
            $('.show-category').click(function() {
                const categoryId = $(this).data('id');

                Swal.fire({
                    title: 'Cargando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `/admin/categories/${categoryId}`,
                    type: 'GET',
                    success: function(response) {
                        const category = response.category;
                        
                        Swal.fire({
                            title: category.name,
                            html: `
                                <div class="text-left">
                                    <img src="${category.image_url}" class="img-thumbnail mb-3" style="max-width: 200px"><br>
                                    <strong>Descripción:</strong><br> ${category.description || 'Sin descripción'}<br><br>
                                    <strong>Estado:</strong> 
                                    <span class="badge badge-${category.active ? 'success' : 'danger'}">
                                        ${category.active ? 'Activa' : 'Inactiva'}
                                    </span><br><br>
                                    <strong>Productos:</strong> ${category.products_count}<br>
                                    <strong>Fecha de creación:</strong> ${category.created_at}<br>
                                    <strong>Última actualización:</strong> ${category.updated_at}
                                </div>
                            `,
                            width: '600px',
                            confirmButtonText: 'Cerrar',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron cargar los detalles de la categoría', 'error');
                    }
                });
            });

            // Manejar el envío del formulario
            $('#categoryForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                if (method === 'PUT') {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#categoryModal').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        let errorMessage = 'Ocurrió un error al procesar la solicitud';
                        
                        if (response.errors) {
                            errorMessage = Object.values(response.errors).join('\n');
                        } else if (response.message) {
                            errorMessage = response.message;
                        }

                        Swal.fire('Error', errorMessage, 'error');
                    }
                });
            });

            // Eliminar categoría
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
                        const csrfToken = $('meta[name="csrf-token"]').attr('content');

                        $.ajax({
                            url: `/admin/categories/${categoryId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.reload();
                                });
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

            // Actualizar nombre del archivo seleccionado
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });
    </script>
@stop
