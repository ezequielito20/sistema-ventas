@extends('adminlte::page')

@section('title', 'Gestión de Ventas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Gestión de Ventas</h1>
        <a href="{{ route('admin.sales.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-2"></i>Nueva Venta
        </a>
    </div>
@stop

@section('content')
    {{-- Widgets de Estadísticas --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalSales }}</h3>
                    <p>Productos Unicos Vendidos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($totalAmount, 2) }}</h3>
                    <p>Total Ingresos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cash-register"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $monthlySales }}</h3>
                    <p>Ventas este mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>${{ number_format($averageTicket, 2) }}</h3>
                    <p>Ticket Promedio</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Ventas --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-shopping-bag mr-2"></i>
                Lista de Ventas
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="salesTable" class="table table-striped table-hover">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total Productos</th>
                        <th>Monto Total</th>
                        <th>Detalle de la Venta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sales as $sale)
                        <tr style="text-align: center">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $sale->customer->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                            <td>
                                <div class="d-flex flex-column align-items-center">
                                    <div class="mb-1">
                                        <span class="badge badge-info">
                                            <i class="fas fa-boxes mr-1"></i>
                                            {{ $sale->saleDetails->count() }} únicos
                                        </span>
                                    </div>
                                    <div>
                                        <span class="badge badge-primary">
                                            <i class="fas fa-cubes mr-1"></i>
                                            {{ $sale->saleDetails->sum('quantity') }} totales
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>${{ number_format($sale->total_price, 2) }}</td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm view-details"
                                    data-id="{{ $sale->id }}" data-toggle="modal" data-target="#saleDetailsModal">
                                    <i class="fas fa-list"></i> Ver Detalle
                                </button>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.sales.edit', $sale->id) }}" class="btn btn-warning btn-sm"
                                        data-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm delete-sale"
                                        data-id="{{ $sale->id }}" data-toggle="tooltip" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm print-sale"
                                        data-id="{{ $sale->id }}" data-toggle="tooltip" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal para mostrar detalles --}}
    <div class="modal fade" id="saleDetailsModal" tabindex="-1" role="dialog" aria-labelledby="saleDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="saleDetailsModalLabel">
                        <i class="fas fa-receipt mr-2"></i>Detalle de la Venta
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Información del Cliente</h6>
                            <p id="customerInfo"></p>
                        </div>
                        <div class="col-md-6 text-right">
                            <h6 class="font-weight-bold">Fecha de Venta</h6>
                            <p id="saleDate"></p>
                        </div>
                    </div>
                    <table class="table table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-right">Precio Unit.</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="saleDetailsTableBody">
                            <!-- Los detalles se cargarán aquí dinámicamente -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                                <td class="text-right"><strong>$<span id="modalTotal">0.00</span></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info print-details">
                        <i class="fas fa-print mr-2"></i>Imprimir
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <style>
        .small-box {
            transition: transform .3s;
        }

        .small-box:hover {
            transform: translateY(-5px);
        }

        .table td {
            vertical-align: middle !important;
        }

        .badge {
            padding: 8px 12px;
        }

        .btn-group .btn {
            margin: 0 2px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .modal-dialog {
                margin: 0.5rem;
            }

            .table td,
            .table th {
                padding: 0.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#salesTable').DataTable({
                responsive: true,
                language: {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                }
            });

            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Ver detalles de la venta
            $('.view-details').click(function() {
                const saleId = $(this).data('id');
                $('#saleDetailsTableBody').empty();

                $.ajax({
                    url: `/sales/${saleId}/details`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            let total = 0;

                            // Actualizar información del cliente y fecha
                            $('#customerInfo').html(`
                                <strong>Cliente:</strong> ${response.sale.customer_name}<br>
                                <strong>Email:</strong> ${response.sale.customer_email}
                            `);
                            $('#saleDate').text(response.sale.date);

                            response.details.forEach(function(detail) {
                                const quantity = parseFloat(detail.quantity);
                                const price = parseFloat(detail.product_price);
                                const subtotal = quantity * price;
                                total += subtotal;
                                $('#saleDetailsTableBody').append(`
                                    <tr>
                                        <td>${detail.product.code || ''}</td>
                                        <td>${detail.product.name || ''}</td>
                                        <td>${detail.product.category || 'Sin categoría'}</td>
                                        <td class="text-center">${quantity}</td>
                                        <td class="text-right">$${price.toFixed(2)}</td>
                                        <td class="text-right">$${subtotal.toFixed(2)}</td>
                                    </tr>
                                `);
                            });

                            const formattedTotal = total.toFixed(2).replace(
                                /\B(?=(\d{3})+(?!\d))/g, ",");
                            $('#modalTotal').text(formattedTotal);
                        } else {
                            Swal.fire('Error', response.message ||
                                'Error al cargar los detalles', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron cargar los detalles', 'error');
                    }
                });
            });

            // Eliminar venta
            $('.delete-sale').click(function() {
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
                            url: `/sales/delete/${id}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: '¡Eliminado!',
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo eliminar la venta',
                                    'error');
                            }
                        });
                    }
                });
            });

            // Imprimir venta
            $('.print-sale, .print-details').click(function() {
                window.print();
            });
        });
    </script>
@stop
