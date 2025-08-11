@extends('adminlte::page')

@section('title', 'Gestión de Pedidos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-shopping-cart"></i> Gestión de Pedidos</h1>
        <div class="btn-group" role="group">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> Todos
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="btn btn-outline-warning">
                <i class="fas fa-clock"></i> Pendientes
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'processed']) }}" class="btn btn-outline-success">
                <i class="fas fa-check"></i> Procesados
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="btn btn-outline-danger">
                <i class="fas fa-times"></i> Cancelados
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        Lista de Pedidos
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Teléfono</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>
                                                <strong>{{ $order->customer_name }}</strong>
                                                @if($order->customer)
                                                    <br><small class="text-muted">Cliente registrado</small>
                                                @else
                                                    <br><small class="text-info">Cliente nuevo</small>
                                                @endif
                                            </td>
                                            <td>{{ $order->customer_phone }}</td>
                                            <td>
                                                {{ $order->product->name ?? 'Producto eliminado' }}
                                                @if($order->notes)
                                                    <br><small class="text-muted">{{ Str::limit($order->notes, 30) }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $order->quantity }}</td>
                                            <td>
                                                <span class="badge badge-info">${{ number_format($order->total_price, 2) }}</span>
                                            </td>
                                            <td>
                                                @switch($order->status)
                                                    @case('pending')
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock"></i> Pendiente
                                                        </span>
                                                        @break
                                                    @case('processed')
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check"></i> Procesado
                                                        </span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times"></i> Cancelado
                                                        </span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <small>{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                                       class="btn btn-sm btn-info" 
                                                       data-toggle="tooltip" 
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($order->status === 'pending')
                                                        <button type="button" 
                                                                class="btn btn-sm btn-success" 
                                                                data-toggle="modal" 
                                                                data-target="#processModal{{ $order->id }}"
                                                                title="Procesar pedido">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        
                                                        <form action="{{ route('admin.orders.cancel', $order) }}" 
                                                              method="POST" 
                                                              style="display: inline;"
                                                              onsubmit="return confirm('¿Está seguro de cancelar este pedido?')">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-danger"
                                                                    data-toggle="tooltip" 
                                                                    title="Cancelar pedido">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>

                                        @if($order->status === 'pending')
                                            <!-- Process Modal -->
                                            <div class="modal fade" id="processModal{{ $order->id }}" tabindex="-1">
                                                <div class="modal-dialog">
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
                                                                    Al procesar este pedido se creará:
                                                                    <ul class="mb-0 mt-2">
                                                                        @if(!$order->customer)
                                                                            <li>Un nuevo cliente: <strong>{{ $order->customer_name }}</strong></li>
                                                                        @endif
                                                                        <li>Una nueva venta por <strong>${{ number_format($order->total_price, 2) }}</strong></li>
                                                                        <li>Se actualizará la deuda del cliente</li>
                                                                    </ul>
                                                                </div>
                                                                
                                                                <div class="form-group">
                                                                    <label for="sale_date{{ $order->id }}">
                                                                        <i class="fas fa-calendar"></i>
                                                                        Fecha de la venta
                                                                    </label>
                                                                    <input type="datetime-local" 
                                                                           class="form-control" 
                                                                           id="sale_date{{ $order->id }}"
                                                                           name="sale_date"
                                                                           value="{{ now()->format('Y-m-d\TH:i') }}">
                                                                    <small class="text-muted">
                                                                        Puedes cambiar la fecha si es necesario
                                                                    </small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                    <i class="fas fa-times"></i> Cancelar
                                                                </button>
                                                                <button type="submit" class="btn btn-success">
                                                                    <i class="fas fa-check"></i> Procesar Pedido
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay pedidos registrados</h4>
                            <p class="text-muted">Los pedidos realizados por los clientes aparecerán aquí.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .badge {
            font-size: 0.8rem;
        }
        
        .btn-group .btn {
            border-radius: 0;
        }
        
        .btn-group .btn:first-child {
            border-top-left-radius: 0.25rem;
            border-bottom-left-radius: 0.25rem;
        }
        
        .btn-group .btn:last-child {
            border-top-right-radius: 0.25rem;
            border-bottom-right-radius: 0.25rem;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .modal-header.bg-success {
            color: white;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Auto refresh every 30 seconds for pending orders
            @if(request('status') === 'pending' || !request('status'))
                setInterval(function() {
                    if ($('.badge-warning').length > 0) {
                        location.reload();
                    }
                }, 30000);
            @endif
        });
    </script>
@stop