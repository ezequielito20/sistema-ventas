@extends('adminlte::page')

@section('title', 'Detalles del Pedido #' . $order->id)

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/orders/show.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/admin/orders/show.js') }}" defer></script>
@endpush

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-shopping-cart"></i> 
            Pedido #{{ $order->id }}
            @switch($order->status)
                @case('pending')
                    <span class="status-badge pending ml-2">
                        <i class="fas fa-clock"></i> Pendiente
                    </span>
                    @break
                @case('processed')
                    <span class="status-badge processed ml-2">
                        <i class="fas fa-check"></i> Procesado
                    </span>
                    @break
                @case('cancelled')
                    <span class="status-badge cancelled ml-2">
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
            <div class="card order-info-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        Información del Pedido
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 customer-info-section">
                            <h5><i class="fas fa-user"></i> Información del Cliente</h5>
                            <table class="table table-sm table-borderless info-table">
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
                        
                        <div class="col-md-6 product-info-section">
                            <h5><i class="fas fa-box"></i> Información del Producto</h5>
                            <table class="table table-sm table-borderless info-table">
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
                                        <div class="total-price">
                                            <h4>
                                                <strong>${{ number_format($order->total_price, 2) }}</strong>
                                            </h4>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($order->notes)
                        <div class="notes-section">
                            <h5><i class="fas fa-sticky-note"></i> Notas del Cliente</h5>
                            <div class="notes-content">
                                {{ $order->notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Order Status & Actions -->
        <div class="col-md-4">
            <div class="card actions-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i>
                        Acciones
                    </h3>
                </div>
                <div class="card-body">
                    @if($order->status === 'pending')
                        <div class="status-alert warning">
                            <i class="fas fa-clock"></i>
                            <strong>Pedido Pendiente</strong><br>
                            Este pedido está esperando ser procesado.
                        </div>
                        
                        <button type="button" 
                                class="action-button success" 
                                data-toggle="modal" 
                                data-target="#processModal">
                            <i class="fas fa-check"></i> Procesar Pedido
                        </button>
                        
                        <form action="{{ route('admin.orders.cancel', $order) }}" 
                              method="POST">
                            @csrf
                            <button type="submit" class="action-button danger">
                                <i class="fas fa-times"></i> Cancelar Pedido
                            </button>
                        </form>
                    @else
                        <div class="status-alert info">
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
                               class="action-button primary">
                                <i class="fas fa-receipt"></i> Ver Venta Generada
                            </a>
                        @endif
                    @endif
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="card actions-card">
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
        <div class="modal fade process-modal" id="processModal" tabindex="-1">
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
                                        <div class="summary-card">
                                            <div class="product-name">{{ $order->product->name ?? 'Producto' }}</div>
                                            <div class="product-details">{{ $order->quantity }} × ${{ number_format($order->unit_price, 2) }}</div>
                                            <div class="total-amount">
                                                Total: ${{ number_format($order->total_price, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="action-button secondary" data-dismiss="modal">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="submit" class="action-button success">
                                <i class="fas fa-check"></i> Confirmar y Procesar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@stop


@stop