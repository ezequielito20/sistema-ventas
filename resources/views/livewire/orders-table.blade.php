<div class="min-h-screen bg-gray-50 py-6" x-data="ordersTable()">
    <!-- Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <!-- Header con título y filtros -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-white text-lg"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Gestión de Pedidos</h1>
                            <p class="text-sm text-gray-500">Administra los pedidos de tus clientes</p>
                        </div>
                    </div>
                    
                    <!-- Filtros de estado -->
                    <div class="mt-4 sm:mt-0">
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="$set('status', '')" 
                                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ $status === '' ? 'bg-blue-100 text-blue-700 border-2 border-blue-200' : 'bg-white text-gray-700 border-2 border-gray-200 hover:bg-gray-50' }}">
                                <i class="fas fa-list mr-2"></i>Todos
                            </button>
                            <button wire:click="$set('status', 'pending')" 
                                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-700 border-2 border-yellow-200' : 'bg-white text-gray-700 border-2 border-gray-200 hover:bg-gray-50' }}">
                                <i class="fas fa-clock mr-2"></i>Pendientes
                            </button>
                            <button wire:click="$set('status', 'processed')" 
                                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ $status === 'processed' ? 'bg-green-100 text-green-700 border-2 border-green-200' : 'bg-white text-gray-700 border-2 border-gray-200 hover:bg-gray-50' }}">
                                <i class="fas fa-check mr-2"></i>Procesados
                            </button>
                            <button wire:click="$set('status', 'cancelled')" 
                                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ $status === 'cancelled' ? 'bg-red-100 text-red-700 border-2 border-red-200' : 'bg-white text-gray-700 border-2 border-gray-200 hover:bg-gray-50' }}">
                                <i class="fas fa-times mr-2"></i>Cancelados
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de búsqueda -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input wire:model.live.debounce.300ms="search" 
                                   type="text" 
                                   placeholder="Buscar por cliente, teléfono o producto..."
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">
                            {{ $orders->total() }} pedidos encontrados
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tabla de pedidos -->
            <div class="overflow-x-auto">
                @if($orders->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th wire:click="sortBy('id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                    <div class="flex items-center space-x-1">
                                        <span>#</span>
                                        @if($sortField === 'id')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('customer_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                    <div class="flex items-center space-x-1">
                                        <span>Cliente</span>
                                        @if($sortField === 'customer_name')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Teléfono
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Producto
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cantidad
                                </th>
                                <th wire:click="sortBy('total_price')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                    <div class="flex items-center space-x-1">
                                        <span>Total</span>
                                        @if($sortField === 'total_price')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                    <div class="flex items-center space-x-1">
                                        <span>Estado</span>
                                        @if($sortField === 'status')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                    <div class="flex items-center space-x-1">
                                        <span>Fecha</span>
                                        @if($sortField === 'created_at')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $order->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $order->customer_name }}</div>
                                            @if($order->customer)
                                                <div class="text-xs text-green-600 flex items-center">
                                                    <i class="fas fa-user-check mr-1"></i>Cliente registrado
                                                </div>
                                            @else
                                                <div class="text-xs text-blue-600 flex items-center">
                                                    <i class="fas fa-user-plus mr-1"></i>Cliente nuevo
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $order->customer_phone }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $order->product->name ?? 'Producto eliminado' }}
                                            </div>
                                            @if($order->notes)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ Str::limit($order->notes, 30) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $order->quantity }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            ${{ number_format($order->total_price, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $this->getStatusBadgeClass($order->status) }}">
                                            <i class="{{ $this->getStatusIcon($order->status) }} mr-2"></i>
                                            @switch($order->status)
                                                @case('pending')
                                                    Pendiente
                                                    @break
                                                @case('processed')
                                                    Procesado
                                                    @break
                                                @case('cancelled')
                                                    Cancelado
                                                    @break
                                            @endswitch
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('admin.orders.show', $order) }}" 
                                               class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors duration-200"
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($order->status === 'pending')
                                                <button wire:click="openProcessModal({{ $order->id }})"
                                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 transition-colors duration-200"
                                                        title="Procesar pedido">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                
                                                <button wire:click="cancelOrder({{ $order->id }})"
                                                        wire:confirm="¿Está seguro de cancelar este pedido?"
                                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 transition-colors duration-200"
                                                        title="Cancelar pedido">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-12">
                        <div class="mx-auto h-24 w-24 text-gray-300">
                            <i class="fas fa-shopping-cart text-6xl"></i>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No hay pedidos</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            @if($status)
                                No se encontraron pedidos con el estado seleccionado.
                            @elseif($search)
                                No se encontraron pedidos que coincidan con tu búsqueda.
                            @else
                                Los pedidos realizados por los clientes aparecerán aquí.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            <!-- Paginación -->
            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de Procesar Pedido -->
    @if($showProcessModal && $selectedOrder)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="true" x-transition>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="true" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" x-show="true" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-white text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-medium text-white">
                                        Procesar Pedido #{{ $selectedOrder->id }}
                                    </h3>
                                </div>
                            </div>
                            <button wire:click="closeProcessModal" class="text-white hover:text-gray-200 transition-colors duration-200">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Información del procesamiento</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            @if(!$selectedOrder->customer)
                                                <li>Se creará un nuevo cliente: <strong>{{ $selectedOrder->customer_name }}</strong></li>
                                            @endif
                                            <li>Se creará una nueva venta por <strong>${{ number_format($selectedOrder->total_price, 2) }}</strong></li>
                                            <li>Se actualizará la deuda del cliente</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="sale_date" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-2"></i>Fecha de la venta
                            </label>
                            <input wire:model="saleDate" 
                                   type="datetime-local" 
                                   id="sale_date"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200">
                            <p class="mt-1 text-xs text-gray-500">Puedes cambiar la fecha si es necesario</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                        <button wire:click="closeProcessModal" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </button>
                        <button wire:click="processOrder" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors duration-200">
                            <i class="fas fa-check mr-2"></i>Procesar Pedido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Notificaciones -->
    <div x-data="{ notifications: [] }" 
         @showNotification.window="notifications.push({ id: Date.now(), type: $event.detail.type, message: $event.detail.message }); setTimeout(() => { notifications = notifications.filter(n => n.id !== notifications[notifications.length - 1].id) }, 3000)"
         class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="notification in notifications" :key="notification.id">
            <div x-show="true" 
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i x-show="notification.type === 'success'" class="fas fa-check-circle text-green-400"></i>
                            <i x-show="notification.type === 'error'" class="fas fa-exclamation-circle text-red-400"></i>
                            <i x-show="notification.type === 'warning'" class="fas fa-exclamation-triangle text-yellow-400"></i>
                            <i x-show="notification.type === 'info'" class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p x-text="notification.message" class="text-sm font-medium text-gray-900"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="notifications = notifications.filter(n => n.id !== notification.id)" 
                                    class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function ordersTable() {
    return {
        init() {
            // Auto refresh para pedidos pendientes
            if (window.location.search.includes('status=pending') || !window.location.search.includes('status=')) {
                setInterval(() => {
                    if (document.querySelector('[class*="badge-warning"]')) {
                        window.location.reload();
                    }
                }, 30000);
            }
        }
    }
}
</script>
