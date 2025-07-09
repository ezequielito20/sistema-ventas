@extends('adminlte::page')

@section('title', 'Historial de Pagos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-dark font-weight-bold">Historial de Pagos de Deudas</h1>
            <p class="mb-0">Registro histórico de todos los pagos de deudas realizados por los clientes</p>
        </div>
        <div>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver a Clientes
            </a>
            {{-- Comentar o eliminar este botón hasta que implementes la funcionalidad
            <a href="{{ route('admin.customers.payment-history.export') }}" class="btn btn-success">
                <i class="fas fa-file-excel mr-1"></i>Exportar a Excel
            </a>
            --}}
        </div>
    </div>
@stop

@section('content')
    {{-- Filtros --}}
    <div class="card card-outline card-info mb-4">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>
        <div class="card-body">
            <form id="filterForm" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="customer_filter">Cliente</label>
                            <select class="form-control select2" id="customer_filter" name="customer_id">
                                <option value="">Todos los clientes</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">Fecha desde</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">Fecha hasta</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i>Filtrar
                                </button>
                                <a href="{{ route('admin.customers.payment-history') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo mr-1"></i>Reiniciar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $currency->symbol }} {{ number_format($totalPayments, 2) }}</h3>
                    <p>Total Pagos Recibidos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $paymentsCount }}</h3>
                    <p>Número de Pagos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $currency->symbol }} {{ number_format($averagePayment, 2) }}</h3>
                    <p>Pago Promedio</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calculator"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $currency->symbol }} {{ number_format($totalRemainingDebt, 2) }}</h3>
                    <p>Deuda Total Restante</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Historial --}}
    <div class="card">
        <div class="card-body">
            <table id="paymentsTable" class="table table-striped table-hover">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Deuda Anterior</th>
                        <th>Monto Pagado</th>
                        <th>Deuda Restante</th>
                        <th>Registrado por</th>
                        <th>Notas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <strong>{{ $payment->customer->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $payment->customer->email }}</small>
                            </td>
                            <td class="text-danger">
                                {{ $currency->symbol }} {{ number_format($payment->previous_debt, 2) }}
                            </td>
                            <td class="text-success font-weight-bold">
                                {{ $currency->symbol }} {{ number_format($payment->payment_amount, 2) }}
                            </td>
                            <td class="text-danger">
                                {{ $currency->symbol }} {{ number_format($payment->remaining_debt, 2) }}
                            </td>
                            <td>
                                {{ $payment->user->name }}
                            </td>
                            <td>
                                {{ $payment->notes ?? 'Sin notas' }}
                            </td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm delete-payment" 
                                        data-payment-id="{{ $payment->id }}"
                                        data-customer-name="{{ $payment->customer->name }}"
                                        data-payment-amount="{{ $payment->payment_amount }}"
                                        data-customer-id="{{ $payment->customer_id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="mt-3 d-flex justify-content-center">
                {{ $payments->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pagos por Día de la Semana</h3>
                </div>
                <div class="card-body">
                    <canvas id="weekdayChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pagos por Mes</h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <style>
        /* Estilos para el paginador de Laravel */
        .pagination {
            margin-bottom: 0;
        }
        
        .pagination .page-link {
            color: #007bff;
            background-color: #fff;
            border: 1px solid #dee2e6;
            padding: 0.375rem 0.75rem;
            margin-left: -1px;
            line-height: 1.25;
            text-decoration: none;
        }
        
        .pagination .page-link:hover {
            z-index: 2;
            color: #0056b3;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
        
        .pagination .page-item.active .page-link {
            z-index: 3;
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            cursor: not-allowed;
        }
        
        .pagination .page-item:first-child .page-link {
            margin-left: 0;
            border-top-left-radius: 0.25rem;
            border-bottom-left-radius: 0.25rem;
        }
        
        .pagination .page-item:last-child .page-link {
            border-top-right-radius: 0.25rem;
            border-bottom-right-radius: 0.25rem;
        }
        
        /* Asegurar que DataTables no interfiera con nuestro paginador */
        .dataTables_paginate {
            display: none !important;
        }
        
        /* Estilos adicionales para mejorar la apariencia */
        .pagination .page-link {
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .pagination .page-item.active .page-link {
            box-shadow: 0 2px 4px rgba(0,123,255,0.25);
        }
        
        /* Asegurar que el paginador sea visible */
        .pagination {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }
        
        /* Mejorar el espaciado */
        .card-body .table-responsive + .mt-3 {
            margin-top: 1.5rem !important;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#paymentsTable').DataTable({
                responsive: true,
                autoWidth: false,
                paging: false, // Desactivar paginación de DataTables
                info: false, // Desactivar información de registros
                searching: true, // Mantener la búsqueda
                ordering: true, // Mantener el ordenamiento
                order: [[0, 'desc']], // Ordenar por la primera columna (Fecha) en orden descendente
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                },
                dom: 'rt', // Solo mostrar la tabla y el campo de búsqueda
                initComplete: function() {
                    // Asegurar que el paginador de Laravel sea visible
                    $('.pagination').show();
                }
            });

            // Inicializar gráficos si existen los elementos
            if (document.getElementById('weekdayChart')) {
                const weekdayCtx = document.getElementById('weekdayChart').getContext('2d');
                const weekdayChart = new Chart(weekdayCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($weekdayLabels) !!},
                        datasets: [{
                            label: 'Pagos por día de la semana',
                            data: {!! json_encode($weekdayData) !!},
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '{{ $currency->symbol }} ' + value.toFixed(2);
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '{{ $currency->symbol }} ' + context.raw.toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            if (document.getElementById('monthlyChart')) {
                const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
                const monthlyChart = new Chart(monthlyCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($monthlyLabels) !!},
                        datasets: [{
                            label: 'Pagos por mes',
                            data: {!! json_encode($monthlyData) !!},
                            backgroundColor: 'rgba(75, 192, 192, 0.5)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 2,
                            tension: 0.3
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '{{ $currency->symbol }} ' + value.toFixed(2);
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '{{ $currency->symbol }} ' + context.raw.toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Manejar la eliminación de pagos
            $(document).on('click', '.delete-payment', function() {
                const paymentId = $(this).data('payment-id');
                const customerName = $(this).data('customer-name');
                const paymentAmount = $(this).data('payment-amount');
                const $button = $(this);

                console.log('Botón de eliminar clickeado:', {
                    paymentId: paymentId,
                    customerName: customerName,
                    paymentAmount: paymentAmount
                });

                Swal.fire({
                    title: '¿Estás seguro?',
                    html: `
                        <p>Vas a eliminar el pago de <strong>${paymentAmount} {{ $currency->symbol }}</strong> del cliente <strong>${customerName}</strong>.</p>
                        <p>Esta acción restaurará la deuda al cliente.</p>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostrar indicador de carga
                        Swal.fire({
                            title: 'Eliminando pago...',
                            html: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>',
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });

                        // Enviar solicitud de eliminación
                        $.ajax({
                            url: `/admin/customers/payment-history/${paymentId}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                console.log('Respuesta del servidor:', response);
                                
                                // Eliminar la fila de la tabla
                                $button.closest('tr').fadeOut(300, function() {
                                    $(this).remove();
                                });

                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Pago eliminado',
                                    text: 'El pago ha sido eliminado y la deuda ha sido restaurada',
                                    confirmButtonText: 'Aceptar'
                                });

                                // Actualizar estadísticas si las hay en la página
                                if (response.statistics) {
                                    $('#totalPayments').text(response.statistics.totalPayments);
                                    $('#paymentsCount').text(response.statistics.paymentsCount);
                                    $('#averagePayment').text(response.statistics.averagePayment);
                                }
                            },
                            error: function(xhr) {
                                console.error('Error en la solicitud:', xhr);
                                
                                let errorMessage = 'Ha ocurrido un error al eliminar el pago';
                                
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMessage,
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop 