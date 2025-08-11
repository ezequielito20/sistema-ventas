@extends('adminlte::page')

@section('title', 'Detalles del Pedido #' . $order->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-shopping-cart"></i> 
            Pedido #{{ $order->id }}
            @switch($order->status)
                @case('pending')
                    <span class="badge badge-warning ml-2">
                        <i class="fas fa-clock"></i> Pendiente
                    </span>
                    @break
                @case('processed')
                    <span class="badge badge-success ml-2">
                        <i class="fas fa-check"></i> Procesado
                    </span>
                    @break
                @case('cancelled')
                    <span class="badge badge-danger ml-2">
                        <i class="fas fa-times"></i> Cancelado
                    </span>
                    @break
            @endswitch
        </h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Order Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        Información del Pedido
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-user"></i> Información del Cliente</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td>{{ $order->customer_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Teléfono:</strong></td>
                                    <td>{{ $order->customer_phone }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td>
                                        @if($order->customer)
                                            <span class="badge badge-info">Cliente registrado</span>
                                        @else
                                            <span class="badge badge-warning">Cliente nuevo</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($order->customer)
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>{{ $order->customer->email ?? 'No registrado' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Deuda actual:</strong></td>
                                        <td>
                                            <span class="badge {{ $order->customer->total_debt > 0 ? 'badge-danger' : 'badge-success' }}">
                                                ${{ number_format($order->customer->total_debt, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5><i class="fas fa-box"></i> Información del Producto</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Producto:</strong></td>
                                    <td>{{ $order->product->name ?? 'Producto eliminado' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cantidad:</strong></td>
                                    <td>{{ $order->quantity }} unidades</td>
                                </tr>
                                <tr>
                                    <td><strong>Precio unitario:</strong></td>
                                    <td>${{ number_format($order->unit_price, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total:</strong></td>
                                    <td>
                                        <h4 class="text-primary">
                                            <strong>${{ number_format($order->total_price, 2) }}</strong>
                                        </h4>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($order->notes)
                        <div class="mt-4">
                            <h5><i class="fas fa-sticky-note"></i> Notas del Cliente</h5>
                            <div class="alert alert-info">
                                <i class="fas fa-quote-left"></i>
                                {{ $order->notes }}
                                <i class="fas fa-quote-right"></i>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Order Status & Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i>
                        Acciones
                    </h3>
                </div>
                <div class="card-body">
                    @if($order->status === 'pending')
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i>
                            <strong>Pedido Pendiente</strong><br>
                            Este pedido está esperando ser procesado.
                        </div>
                        
                        <button type="button" 
                                class="btn btn-success btn-block mb-3" 
                                data-toggle="modal" 
                                data-target="#processModal">
                            <i class="fas fa-check"></i> Procesar Pedido
                        </button>
                        
                        <form action="{{ route('admin.orders.cancel', $order) }}" 
                              method="POST" 
                              onsubmit="return confirm('¿Está seguro de cancelar este pedido?')">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-times"></i> Cancelar Pedido
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Estado:</strong> {{ $order->status_label }}<br>
                            @if($order->processed_at)
                                <strong>Procesado:</strong> {{ $order->processed_at->format('d/m/Y H:i') }}<br>
                            @endif
                            @if($order->processedBy)
                                <strong>Por:</strong> {{ $order->processedBy->name }}
                            @endif
                        </div>
                        
                        @if($order->sale)
                            <a href="{{ route('admin.sales.show', $order->sale) }}" 
                               class="btn btn-primary btn-block">
                                <i class="fas fa-receipt"></i> Ver Venta Generada
                            </a>
                        @endif
                    @endif
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i>
                        Historial
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-primary">{{ $order->created_at->format('d/m/Y') }}</span>
                        </div>
                        
                        <div>
                            <i class="fas fa-plus bg-success"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-clock"></i> {{ $order->created_at->format('H:i') }}
                                </span>
                                <h3 class="timeline-header">Pedido Creado</h3>
                                <div class="timeline-body">
                                    El cliente {{ $order->customer_name }} realizó un pedido.
                                </div>
                            </div>
                        </div>
                        
                        @if($order->processed_at)
                            <div>
                                <i class="fas fa-{{ $order->status === 'processed' ? 'check' : 'times' }} bg-{{ $order->status === 'processed' ? 'success' : 'danger' }}"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="fas fa-clock"></i> {{ $order->processed_at->format('H:i') }}
                                    </span>
                                    <h3 class="timeline-header">
                                        Pedido {{ $order->status === 'processed' ? 'Procesado' : 'Cancelado' }}
                                    </h3>
                                    <div class="timeline-body">
                                        @if($order->processedBy)
                                            Por {{ $order->processedBy->name }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($order->status === 'pending')
        <!-- Process Modal -->
        <div class="modal fade" id="processModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h4 class="modal-title">
                            <i class="fas fa-check"></i>
                            Procesar Pedido #{{ $order->id }}
                        </h4>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.orders.process', $order) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Al procesar este pedido se realizarán las siguientes acciones:</strong>
                                <ul class="mb-0 mt-2">
                                    @if(!$order->customer)
                                        <li>Se creará un nuevo cliente: <strong>{{ $order->customer_name }}</strong></li>
                                    @endif
                                    <li>Se generará una nueva venta por <strong>${{ number_format($order->total_price, 2) }}</strong></li>
                                    <li>Se actualizará la deuda del cliente</li>
                                    <li>El pedido cambiará a estado "Procesado"</li>
                                </ul>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sale_date">
                                            <i class="fas fa-calendar"></i>
                                            Fecha de la venta
                                        </label>
                                        <input type="datetime-local" 
                                               class="form-control" 
                                               id="sale_date"
                                               name="sale_date"
                                               value="{{ now()->format('Y-m-d\TH:i') }}"
                                               required>
                                        <small class="text-muted">
                                            Puedes cambiar la fecha si es necesario
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <i class="fas fa-calculator"></i>
                                            Resumen del pedido
                                        </label>
                                        <div class="card bg-light">
                                            <div class="card-body p-3">
                                                <strong>{{ $order->product->name ?? 'Producto' }}</strong><br>
                                                <small>{{ $order->quantity }} × ${{ number_format($order->unit_price, 2) }}</small><br>
                                                <hr class="my-2">
                                                <strong class="text-primary">
                                                    Total: ${{ number_format($order->total_price, 2) }}
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Confirmar y Procesar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@stop

@section('css')
    <style>
        .timeline {
            position: relative;
            margin: 0 0 30px 0;
            padding: 0;
            list-style: none;
        }

        .timeline:before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 31px;
            width: 2px;
            margin-left: -1.5px;
            background-color: #dee2e6;
        }

        .timeline > div {
            position: relative;
            margin-right: 10px;
            margin-bottom: 15px;
        }

        .timeline > div > .timeline-item {
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            border-radius: 3px;
            margin-top: 0;
            background: #fff;
            color: #495057;
            margin-left: 60px;
            margin-right: 15px;
            padding: 0;
            position: relative;
        }

        .timeline > div > .fas {
            width: 30px;
            height: 30px;
            font-size: 15px;
            line-height: 30px;
            position: absolute;
            color: #666;
            background: #dee2e6;
            border-radius: 50%;
            text-align: center;
            left: 18px;
            top: 0;
        }

        .timeline > .time-label > span {
            font-weight: 600;
            color: #fff;
            border-radius: 4px;
            display: inline-block;
            padding: 5px 10px;
        }

        .timeline-header {
            margin: 0;
            color: #495057;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 15px;
            font-size: 16px;
            line-height: 1.1;
        }

        .timeline-body {
            padding: 10px 15px;
        }

        .timeline .time {
            color: #999;
            float: right;
            padding: 10px;
            font-size: 12px;
        }

        .modal-header.bg-success {
            color: white;
        }

        .badge {
            font-size: 0.9rem;
        }
    </style>
@stop