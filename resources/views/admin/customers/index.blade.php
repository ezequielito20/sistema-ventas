@extends('adminlte::page')

@section('title', 'Gestión de Clientes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-dark font-weight-bold">Gestión de Clientes</h1>
            <p class="mb-0">Administra y visualiza todos tus clientes en un solo lugar</p>
        </div>
        <div class="d-flex">
            <button class="btn btn-outline-primary mr-2" id="exportCustomers">
                <i class="fas fa-file-export mr-2"></i>Exportar
            </button>
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle mr-2"></i>Nuevo Cliente
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Widgets de Estadísticas con Animación --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info shadow-sm">
                <div class="inner">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="counter">{{ $totalCustomers }}</h3>
                            <p>Total Clientes</p>
                        </div>
                        <div class="d-flex align-items-center">
                            @if ($customerGrowth > 0)
                                <span class="badge badge-success">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    {{ $customerGrowth }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success shadow-sm">
                <div class="inner">
                    <h3>{{ $activeCustomers }}</h3>
                    <p>Clientes Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning shadow-sm">
                <div class="inner">
                    <h3>{{ $newCustomers }}</h3>
                    <p>Nuevos este mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-purple shadow-sm">
                <div class="inner">
                    <h3>${{ number_format($totalRevenue, 2) }}</h3>
                    <p>Ingresos Totales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Clientes con Filtros Avanzados --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-tie mr-2"></i>
                Lista de Clientes
            </h3>
            <div class="card-tools">
                <div class="d-flex">
                    <div class="btn-group mr-2">
                        <button type="button" class="btn btn-sm btn-outline-primary active filter-btn" data-filter="all">
                            Todos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success filter-btn" data-filter="active">
                            Activos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger filter-btn" data-filter="inactive">
                            Inactivos
                        </button>
                    </div>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table id="customersTable" class="table table-striped table-hover">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th>NIT</th>
                        <th>Última Compra</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="customer-avatar mr-2">
                                        <div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px; font-size: 1.2em;">
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <strong>{{ $customer->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope mr-1"></i>
                                            {{ $customer->email }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <i class="fas fa-phone mr-1"></i>
                                {{ $customer->phone }}
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $customer->nit_number }}
                                </span>
                            </td>
                            <td>
                                @if(isset($customer->lastPurchase))
                                    <div>
                                        {{ $customer->lastPurchase->created_at->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">
                                            ${{ number_format($customer->lastPurchase->total, 2) }}
                                        </small>
                                    </div>
                                @else
                                    <span class="text-muted">Sin compras</span>
                                @endif
                            </td>
                            <td>
                                @if ($customer->isActive())
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Activo
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-info btn-sm show-customer"
                                        data-id="{{ $customer->id }}" data-toggle="tooltip" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                        class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm delete-customer"
                                        data-id="{{ $customer->id }}" data-toggle="tooltip" title="Eliminar">
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

    {{-- Modal de Detalles del Cliente --}}
    <div class="modal fade" id="showCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-tie mr-2"></i>
                        Detalles del Cliente
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- Información Personal --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user mr-2"></i>
                                        Información Personal
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Nombre:</th>
                                            <td id="customerName"></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td id="customerEmail"></td>
                                        </tr>
                                        <tr>
                                            <th>Teléfono:</th>
                                            <td id="customerPhone"></td>
                                        </tr>
                                        <tr>
                                            <th>NIT:</th>
                                            <td id="customerNit"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Estadísticas del Cliente --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chart-pie mr-2"></i>
                                        Estadísticas
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6 text-center border-right">
                                            <h4 class="mb-0" id="totalPurchases">0</h4>
                                            <small class="text-muted">Compras Totales</small>
                                        </div>
                                        <div class="col-6 text-center">
                                            <h4 class="mb-0" id="totalSpent">$0</h4>
                                            <small class="text-muted">Gasto Total</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Historial de Compras --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-shopping-cart mr-2"></i>
                                        Historial de Compras
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="purchaseHistoryChart" style="height: 200px;"></canvas>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <style>
        .small-box {
            transition: all .3s ease-in-out;
        }

        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .customer-avatar {
            transition: transform .3s ease-in-out;
        }

        .customer-avatar:hover {
            transform: scale(1.1);
        }

        .table td {
            vertical-align: middle !important;
        }

        .badge {
            padding: 8px 12px;
        }

        .counter {
            animation: countUp 2s ease-out;
        }

        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .filter-btn {
            transition: all .2s ease-in-out;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
        }

        .card {
            transition: all .3s ease-in-out;
        }

        .card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable con exportación
            const table = $('#customersTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel mr-2"></i>Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4]
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                        className: 'btn btn-danger btn-sm',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4]
                        }
                    }
                ],
                language: {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                }
            });

            // Filtros de estado
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');

                const filter = $(this).data('filter');
                if (filter === 'all') {
                    table.column(4).search('').draw();
                } else {
                    table.column(4).search(filter).draw();
                }
            });

            // Tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Ver detalles del cliente
            $('.show-customer').click(function() {
                const id = $(this).data('id');

                // Limpiar datos anteriores
                $('#customerName, #customerEmail, #customerPhone, #customerNit, #totalPurchases, #totalSpent')
                    .text('');

                $.ajax({
                    url: `/customers/${id}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const customer = response.customer;

                            // Información personal
                            $('#customerName').text(customer.name);
                            $('#customerEmail').text(customer.email);
                            $('#customerPhone').text(customer.phone);
                            $('#customerNit').text(customer.nit_number);

                            // Estadísticas
                            $('#totalPurchases').text(customer.stats.total_purchases);
                            $('#totalSpent').text('$' + customer.stats.total_spent
                                .toLocaleString());

                            // Actualizar gráfico
                            updatePurchaseHistory(customer.stats.purchase_history);

                            $('#showCustomerModal').modal('show');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron cargar los datos del cliente',
                            'error');
                    }
                });
            });

            // Eliminar cliente
            $('.delete-customer').click(function() {
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
                            url: `/customers/${id}`,
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
                            error: function(xhr) {
                                Swal.fire('Error', 'No se pudo eliminar el cliente',
                                    'error');
                            }
                        });
                    }
                });
            });

            // Función para actualizar el gráfico de historial de compras
            function updatePurchaseHistory(data) {
                if (window.purchaseChart) {
                    window.purchaseChart.destroy();
                }

                const ctx = document.getElementById('purchaseHistoryChart').getContext('2d');
                window.purchaseChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Compras Mensuales',
                            data: data.values,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@stop