@extends('adminlte::page')

@section('title', 'Gestión de Caja')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Gestión de Caja</h1>
        <div class="d-flex gap-2">
            @if ($currentCashCount)
                <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#newMovementModal">
                    <i class="fas fa-money-bill-wave mr-2"></i>Nuevo Movimiento
                </button>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#closeCashModal">
                    <i class="fas fa-cash-register mr-2"></i>Cerrar Caja
                </button>
            @else
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#openCashModal">
                    <i class="fas fa-cash-register mr-2"></i>Abrir Caja
                </button>
            @endif
        </div>
    </div>
@stop

@section('content')
    {{-- Estado Actual de Caja --}}
    @if ($currentCashCount)
        <div class="alert alert-info alert-dismissible">
            <h5><i class="icon fas fa-info"></i> Caja Actual</h5>
            <p class="mb-0">
                Abierta desde: {{ \Carbon\Carbon::parse($currentCashCount->opening_date)->format('d/m/Y H:i') }}
                | Monto Inicial: {{ $currency->symbol }} {{ number_format($currentCashCount->initial_amount, 2) }}
            </p>
        </div>
    @endif

    {{-- Widgets de Estadísticas --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info elevation-3">
                <div class="inner">
                    <h3>{{ $currency->symbol }} {{ number_format($currentBalance, 2) }}</h3>
                    <p>Balance Actual</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success elevation-3">
                <div class="inner">
                    <h3>{{ $currency->symbol }} {{ number_format($todayIncome, 2) }}</h3>
                    <p>Ingresos del Día</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger elevation-3">
                <div class="inner">
                    <h3>{{ $currency->symbol }} {{ number_format($todayExpenses, 2) }}</h3>
                    <p>Egresos del Día</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning elevation-3">
                <div class="inner">
                    <h3>{{ $totalMovements }}</h3>
                    <p>Movimientos del Día</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Historial de Arqueos --}}
    <div class="card card-outline card-primary elevation-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history mr-2"></i>
                Historial de Arqueos
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="cashCountsTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha Apertura</th>
                        <th>Fecha Cierre</th>
                        <th>Monto Inicial</th>
                        <th>Monto Final</th>
                        <th>Diferencia</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cashCounts as $cashCount)
                        <tr>
                            <td>{{ str_pad($cashCount->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ \Carbon\Carbon::parse($cashCount->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                @if ($cashCount->closing_date)
                                    {{ \Carbon\Carbon::parse($cashCount->closing_date)->format('d/m/Y H:i') }}
                                @else
                                    <span class="badge badge-info">En curso</span>
                                @endif
                            </td>
                            <td>{{ $currency->symbol }} {{ number_format($cashCount->initial_amount, 2) }}</td>
                            <td>
                                @if ($cashCount->final_amount)
                                    {{ $currency->symbol }} {{ number_format($cashCount->final_amount, 2) }}
                                @else
                                    <span class="badge badge-secondary">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @if ($cashCount->final_amount)
                                    @php
                                        $difference = $cashCount->final_amount - $cashCount->initial_amount;
                                        $badgeClass = $difference >= 0 ? 'success' : 'danger';
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }}">
                                        {{ $currency->symbol }} {{ number_format(abs($difference), 2) }}
                                        <i class="fas fa-{{ $difference >= 0 ? 'arrow-up' : 'arrow-down' }} ml-1"></i>
                                    </span>
                                @else
                                    <span class="badge badge-secondary">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @if ($cashCount->closing_date)
                                    <span class="badge badge-success">Cerrado</span>
                                @else
                                    <span class="badge badge-warning">Abierto</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-info btn-sm view-movements"
                                        data-id="{{ $cashCount->id }}" data-toggle="tooltip" title="Ver movimientos">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if (!$cashCount->closing_date)
                                        <a href="{{ route('admin.cash-counts.edit', $cashCount->id) }}"
                                            class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-danger btn-sm delete-cash-count"
                                        data-id="{{ $cashCount->id }}" data-toggle="tooltip" title="Eliminar">
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

    {{-- Gráfico de Movimientos --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary elevation-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2"></i>
                        Movimientos de Caja
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="cashMovementsChart" style="min-height: 250px"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-primary elevation-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Distribución de Movimientos
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="movementsDistributionChart" style="min-height: 250px"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para Abrir Caja --}}
    <div class="modal fade" id="openCashModal" tabindex="-1" role="dialog" aria-labelledby="openCashModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="openCashModalLabel">
                        <i class="fas fa-cash-register mr-2"></i>
                        Abrir Nueva Caja
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="openCashForm" action="{{ route('admin.cash-counts.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="initial_amount">Monto Inicial <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="initial_amount"
                                    name="initial_amount" value="0.00" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="observations">Observaciones</label>
                            <textarea class="form-control" id="observations" name="observations" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Abrir Caja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para Nuevo Movimiento --}}
    <div class="modal fade" id="newMovementModal" tabindex="-1" role="dialog" aria-labelledby="newMovementModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="newMovementModalLabel">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        Nuevo Movimiento de Caja
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="newMovementForm" action="{{ route('admin.cash-counts.store-movement') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="type">Tipo de Movimiento</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="income">Ingreso</option>
                                <option value="expense">Egreso</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Monto</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                                    required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-2"></i>Guardar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para Cerrar Caja --}}
    <div class="modal fade" id="closeCashModal" tabindex="-1" role="dialog" aria-labelledby="closeCashModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="closeCashModalLabel">
                        <i class="fas fa-cash-register mr-2"></i>
                        Cerrar Caja
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-money-bill-wave"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Monto Inicial</span>
                                    <span class="info-box-number">
                                        {{ $currency->symbol }}
                                        {{ number_format($currentCashCount->initial_amount ?? 0, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Nueva tarjeta para Total de Ingresos --}}
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-arrow-up"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Ingresos</span>
                                    <span class="info-box-number">
                                        {{ $currency->symbol }}
                                        {{ number_format($todayIncome, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Nueva tarjeta para Total de Productos --}}
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-box"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Productos Vendidos</span>
                                    <span class="info-box-number">
                                        {{ $totalProducts }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-cash-register"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Balance Actual</span>
                                    <span class="info-box-number">
                                        {{ $currency->symbol }} {{ number_format($currentBalance, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="closeCashForm" action="{{ route('admin.cash-counts.update', $currentCashCount->id ?? 0) }}"
                        method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="final_amount">Monto Final en Caja</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="final_amount"
                                    name="final_amount" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="closing_observations">Observaciones del Cierre</label>
                            <textarea class="form-control" id="closing_observations" name="observations" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" form="closeCashForm" class="btn btn-danger">
                        <i class="fas fa-lock mr-2"></i>Cerrar Caja
                    </button>
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
            font-size: 0.9rem;
            transition: all .2s;
        }

        .badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-group .btn {
            margin: 0 2px;
            transition: all .2s;
        }

        .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .modal-content {
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .info-box {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: transform .3s;
        }

        .info-box:hover {
            transform: translateY(-3px);
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#cashCountsTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                },
                order: [
                    [0, 'desc']
                ]
            });

            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Gráfico de Movimientos
            const ctx = document.getElementById('cashMovementsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartData['labels']) !!},
                    datasets: [{
                        label: 'Ingresos',
                        data: {!! json_encode($chartData['income']) !!},
                        borderColor: '#28a745',
                        tension: 0.1
                    }, {
                        label: 'Egresos',
                        data: {!! json_encode($chartData['expenses']) !!},
                        borderColor: '#dc3545',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });

            // Gráfico de Distribución
            const ctxPie = document.getElementById('movementsDistributionChart').getContext('2d');
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Ingresos', 'Egresos'],
                    datasets: [{
                        data: [
                            {{ $todayIncome }},
                            {{ $todayExpenses }}
                        ],
                        backgroundColor: ['#28a745', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Manejo de eliminación
            $('.delete-cash-count').click(function() {
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
                            url: `/cash-counts/delete/${id}`,
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
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo eliminar el arqueo',
                                    'error');
                            }
                        });
                    }
                });
            });

            // Ver movimientos
            $('.view-movements').click(function() {
                const id = $(this).data('id');
                // Implementar lógica para mostrar movimientos
            });
        });
    </script>
@stop
