@extends('adminlte::page')

@section('title', 'Gestión de Proveedores')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Gestión de Proveedores</h1>
        <div>
            @can('suppliers.report')
                <a href="{{ route('admin.suppliers.report') }}" class="btn btn-info mr-2" target="_blank">
                    <i class="fas fa-file-pdf mr-2"></i>Reporte
                </a>
            @endcan
            @can('suppliers.create')
                <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle mr-2"></i>Nuevo Proveedor
                </a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    {{-- Widgets de Estadísticas --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalSuppliers }}</h3>
                    <p>Total Proveedores</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $activeSuppliers }}</h3>
                    <p>Proveedores Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $recentSuppliers }}</h3>
                    <p>Nuevos este mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $inactiveSuppliers }}</h3>
                    <p>Inactivos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-slash"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Proveedores --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-truck mr-2"></i>
                Lista de Proveedores
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="suppliersTable" class="table table-striped table-hover">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Empresa</th>
                        <th>Contacto</th>
                        <th>Información de Contacto</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suppliers as $supplier)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="supplier-avatar mr-2">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px; font-size: 1.2em;">
                                            {{ strtoupper(substr($supplier->company_name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <strong>{{ $supplier->company_name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope mr-1"></i>
                                            {{ $supplier->company_email }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $supplier->supplier_name }}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-phone mr-1"></i>
                                    {{ $supplier->supplier_phone }}
                                </small>
                            </td>
                            <td>
                                <i class="fas fa-building mr-1"></i>
                                {{ $supplier->company_phone }}
                            </td>
                            <td>
                                <i class="fas fa-map-marker-alt mr-1 text-danger"></i>
                                {{ $supplier->company_address }}
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Activo
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    @can('suppliers.show')
                                        <button type="button" class="btn btn-info btn-sm show-supplier"
                                            data-id="{{ $supplier->id }}" data-toggle="tooltip" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    @can('suppliers.edit')
                                        <a href="{{ route('admin.suppliers.edit', $supplier->id) }}"
                                            class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('suppliers.destroy')
                                        <button type="button" class="btn btn-danger btn-sm delete-supplier"
                                            data-id="{{ $supplier->id }}" data-toggle="tooltip" title="Eliminar">
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

    {{-- Modal para mostrar proveedor --}}
    <div class="modal fade" id="showSupplierModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-truck mr-2"></i>
                        Detalles del Proveedor
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- Información de la empresa --}}
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-building mr-2"></i>
                                        Información de la Empresa
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Nombre:</th>
                                            <td id="companyName"></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td id="companyEmail"></td>
                                        </tr>
                                        <tr>
                                            <th>Teléfono:</th>
                                            <td id="companyPhone"></td>
                                        </tr>
                                        <tr>
                                            <th>Dirección:</th>
                                            <td id="companyAddress"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Información del contacto --}}
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user mr-2"></i>
                                        Información del Contacto
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Nombre:</th>
                                            <td id="supplierName"></td>
                                        </tr>
                                        <tr>
                                            <th>Teléfono:</th>
                                            <td id="supplierPhone"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Estadísticas del Proveedor --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-box mr-2"></i>
                                        Resumen de Productos Distribuidos
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th class="text-center">Cantidad</th>
                                                    <th class="text-right">Precio Unitario</th>
                                                    <th class="text-right">Sub Total</th>
                                                </tr>
                                            </thead>
                                            <tbody id="productDetails">
                                                <!-- Los detalles se cargarán dinámicamente -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-right"><strong>Total General:</strong>
                                                    </td>
                                                    <td class="text-right"><strong id="grandTotal">0.00</strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/responsive.bootstrap4.min.css') }}">
    <style>
        .small-box {
            transition: transform .3s;
        }

        .small-box:hover {
            transform: translateY(-5px);
        }

        .supplier-avatar {
            transition: transform .3s;
        }

        .supplier-avatar:hover {
            transform: scale(1.1);
        }

        .table td {
            vertical-align: middle !important;
        }

        .badge {
            padding: 8px 12px;
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script>
        // Primero, definimos la función number_format
        function number_format(number, decimals = 2) {
            return number.toLocaleString('es-PE', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }

        $(document).ready(function() {
            // Cargar todas las librerías necesarias
            loadDataTables(function() {

                // Inicializar DataTable
                $('#suppliersTable').DataTable({
                    responsive: true,
                    language: {
                        "emptyTable": "No hay proveedores registrados",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ proveedores",
                        "infoEmpty": "Mostrando 0 a 0 de 0 proveedores",
                        "infoFiltered": "(filtrado de _MAX_ proveedores totales)",
                        "lengthMenu": "Mostrar _MENU_ proveedores",
                        "loadingRecords": "Cargando...",
                        "processing": "Procesando...",
                        "search": "Buscar:",
                        "zeroRecords": "No se encontraron coincidencias",
                        "paginate": {
                            "first": "Primero",
                            "last": "Último",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        }
                    }
                });

                // Inicializar tooltips
                $('[data-toggle="tooltip"]').tooltip();

                // Mostrar modal de detalles del proveedor
                $('.show-supplier').click(function() {
                    const id = $(this).data('id');

                    // Limpiar datos anteriores
                    $('#companyName, #companyEmail, #companyPhone, #companyAddress, #supplierName, #supplierPhone')
                        .text('');

                    // Mostrar loader o spinner si lo deseas

                    $.ajax({
                        url: `/suppliers/${id}`,
                        type: 'GET',
                        success: function(response) {
                            if (response.icons === 'success') {
                                const supplier = response.supplier;

                                // Llenar datos de la empresa
                                $('#companyName').text(supplier.company_name);
                                $('#companyEmail').text(supplier.company_email);
                                $('#companyPhone').text(supplier.company_phone);
                                $('#companyAddress').text(supplier.company_address);

                                // Llenar datos del contacto
                                $('#supplierName').text(supplier.supplier_name);
                                $('#supplierPhone').text(supplier.supplier_phone);


                                // Si tienes un gráfico, actualizarlo aquí
                                if (response.stats) {
                                    const detailsContainer = document.getElementById(
                                        'productDetails');

                                    let detailsHTML = '';
                                    let grandTotal = 0;

                                    if (response.stats && response.stats.length > 0) {
                                        response.stats.forEach(product => {

                                            const subtotal = product.stock * product
                                                .purchase_price;
                                            grandTotal += subtotal;

                                            detailsHTML += `
                                <tr>
                                    <td>${product.name}</td>
                                    <td class="text-center">
                                        <span class="badge badge-primary">${product.stock}</span>
                                    </td>
                                    <td class="text-right">{{ $currency->symbol }} ${number_format(product.purchase_price)}</td>
                                    <td class="text-right">{{ $currency->symbol }} ${number_format(subtotal)}</td>
                                </tr>`;
                                        });
                                    } else {
                                        detailsHTML = `
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No hay productos registrados para este proveedor
                                </td>
                            </tr>`;
                                    }

                                    detailsContainer.innerHTML = detailsHTML;
                                    document.getElementById('grandTotal').innerHTML =
                                        `{{ $currency->symbol }} ${number_format(grandTotal)}`;

                                }

                                // Mostrar el modal
                                $('#showSupplierModal').modal('show');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudieron cargar los datos del proveedor',
                                'error');
                        }
                    });
                });

                // Eliminar proveedor
                $('.delete-supplier').click(function() {
                    const id = $(this).data('id');

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
                                url: `/suppliers/delete/${id}`,
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: '¡Eliminado!',
                                            text: response.message,
                                            icon: response.icons
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    let errorMessage = 'No se pudo eliminar el proveedor';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMessage = xhr.responseJSON.message;
                                    }
                                    Swal.fire('Error', errorMessage, 'error');
                                }
                            });
                        }
                    });
                });
            });
            
        });
    </script>
@stop
