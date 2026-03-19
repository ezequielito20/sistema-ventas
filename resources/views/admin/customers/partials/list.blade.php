@php
    $exchangeRate = $exchangeRate ?? 134;
@endphp

<!-- Vista de Tabla - Desktop/Tablet -->
<div x-show="viewMode === 'table'" class="hidden md:block">
    <div class="table-container">
        <table id="customersTable" class="modern-table">
            <thead>
                <tr>
                    <th>
                        <div class="th-content">
                            <span>#</span>
                        </div>
                    </th>
                    <th>
                        <div class="th-content">
                            <i class="fas fa-user"></i>
                            <span>Cliente</span>
                        </div>
                    </th>
                    <th>
                        <div class="th-content">
                            <i class="fas fa-phone"></i>
                            <span>Contacto</span>
                        </div>
                    </th>
                    <th>
                        <div class="th-content">
                            <i class="fas fa-id-card"></i>
                            <span>C.I</span>
                        </div>
                    </th>
                    <th>
                        <div class="th-content">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Total Compras</span>
                        </div>
                    </th>
                    <th>
                        <div class="th-content">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Deuda Total</span>
                        </div>
                    </th>
                    <th>
                        <div class="th-content">
                            <i class="fas fa-coins"></i>
                            <span>Deuda Bs</span>
                        </div>
                    </th>

                    <th>
                        <div class="th-content">
                            <i class="fas fa-cogs"></i>
                            <span>Acciones</span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody id="customersTableBody">
                @foreach ($customers as $customer)
                    @php
                        $customerSales = $customersData[$customer->id] ?? [];
                        $hasSales = isset($customerSales['hasOldSales']) || $customerSales['currentDebt'] > 0;
                    @endphp
                    <tr class="table-row" data-customer-id="{{ $customer->id }}"
                        data-defaulter="{{ $customersData[$customer->id]['isDefaulter'] ? 'true' : 'false' }}"
                        data-customer-name="{{ htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') }}">
                        <td>
                            <div class="row-number">
                                {{ $loop->iteration + ($customers->firstItem() - 1) }}
                            </div>
                        </td>
                        <td>
                            <div class="customer-info">
                                <div class="customer-avatar">
                                    <div class="avatar-circle">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="customer-details">
                                    <span
                                        class="customer-name truncate">{{ htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') }}</span>
                                    <div class="customer-email">
                                        <i class="fas fa-envelope"></i>
                                        <span
                                            class="truncate">{{ htmlspecialchars($customer->email, ENT_QUOTES, 'UTF-8') }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <i class="fas fa-phone"></i>
                                <span>{{ $customer->phone }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="id-info">
                                <span class="id-badge">{{ $customer->nit_number }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="sales-info">
                                @php
                                    $customerSales = $customersData[$customer->id] ?? [];
                                    $hasSales =
                                        isset($customerSales['hasOldSales']) || $customerSales['currentDebt'] > 0;
                                @endphp
                                @if ($hasSales)
                                    <div class="sales-amount">{{ $currency->symbol }}
                                        {{ number_format(($customerSales['previousDebt'] ?? 0) + ($customerSales['currentDebt'] ?? 0), 2) }}
                                    </div>
                                    <div class="sales-count">Con ventas</div>
                                @else
                                    <span class="no-sales">Sin ventas</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="debt-info">
                                @if ($customer->total_debt > 0)
                                    <div class="debt-amount debt-value flex items-center gap-2"
                                        data-customer-id="{{ $customer->id }}"
                                        data-original-value="{{ $customer->total_debt }}">
                                        <span>{{ $currency->symbol }} <span
                                                class="debt-amount-value">{{ number_format($customer->formatted_total_debt, 2) }}</span></span>
                                        @if ($customersData[$customer->id]['isDefaulter'])
                                            <span class="debt-warning-badge"
                                                title="Cliente con deudas de arqueos anteriores">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </span>
                                        @endif
                                        @if ($customer->total_debt > 0)
                                            <button class="edit-debt-btn-small"
                                                onclick="spaPaymentHandler.openPaymentModal({{ $customer->id }})">
                                                <i class="fas fa-dollar-sign"></i>
                                            </button>
                                        @else
                                            @if ($permissions['can_edit'])
                                                <button class="edit-debt-btn-small">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                @else
                                    <div class="debt-amount flex items-center gap-2">
                                        <span class="no-debt-badge">Sin deuda</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="debt-bs-info">
                                @if ($customer->total_debt > 0)
                                    <span class="bs-debt" data-debt="{{ $customer->total_debt }}">
                                        Bs.
                                        {{ number_format($customer->total_debt * ($exchangeRate ?? 134), 2) }}
                                    </span>
                                @else
                                    <span class="no-debt-badge">Sin deuda</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            <div class="action-buttons">
                                @if ($permissions['can_show'])
                                    <button type="button" class="btn-action btn-view"
                                        @click="openModal('showCustomerModal'); loadCustomerDetails({{ $customer->id }})"
                                        data-toggle="tooltip" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                                @if ($permissions['can_edit'])
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                        class="btn-action btn-edit" data-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if ($permissions['can_destroy'])
                                    <button type="button" class="btn-action btn-delete"
                                        @click="deleteCustomer({{ $customer->id }})" data-toggle="tooltip"
                                        title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                                @if ($permissions['can_create_sales'])
                                    <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                        class="btn-action btn-sale" data-toggle="tooltip" title="Nueva venta">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Vista de Tarjetas - Móvil y Desktop (cuando se selecciona) -->
<div x-show="viewMode === 'cards'" class="md:block">
    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="mobileCustomersContainer">
        @foreach ($customers as $customer)
            @php
                $customerSales = $customersData[$customer->id] ?? [];
                $hasSales = isset($customerSales['hasOldSales']) || $customerSales['currentDebt'] > 0;
            @endphp
            <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-l-4 border-blue-500"
                data-defaulter="{{ $customersData[$customer->id]['isDefaulter'] ? 'true' : 'false' }}"
                data-customer-name="{{ htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') }}">

                <!-- Header de la Tarjeta -->
                <div class="p-6 pb-4">
                    <div class="card-header-content">
                        <div class="card-header-info">
                            <div class="card-header-avatar">
                                <div
                                    class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="card-header-details">
                                <h3 class="text-lg font-semibold text-gray-900 truncate">
                                    {{ htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') }}
                                </h3>
                                <div class="flex items-center space-x-1 text-sm text-gray-500 mt-1">
                                    <i class="fas fa-envelope text-xs"></i>
                                    <span
                                        class="truncate">{{ htmlspecialchars($customer->email, ENT_QUOTES, 'UTF-8') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="card-header-status">
                            @if ($hasSales)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Activo
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Inactivo
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Información Principal -->
                <div class="px-6 pb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Teléfono -->
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                <i class="fas fa-phone"></i>
                                <span>Teléfono</span>
                            </div>
                            <p class="text-sm font-medium text-gray-900">{{ $customer->phone }}</p>
                        </div>

                        <!-- C.I -->
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                <i class="fas fa-id-card"></i>
                                <span>C.I</span>
                            </div>
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $customer->nit_number }}
                            </span>
                        </div>

                        <!-- Total Compras -->
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Total Compras</span>
                            </div>
                            @if ($hasSales)
                                <div class="flex flex-col">
                                    <p class="text-sm font-semibold text-gray-900">{{ $currency->symbol }}
                                        {{ number_format(($customerSales['previousDebt'] ?? 0) + ($customerSales['currentDebt'] ?? 0), 2) }}
                                    </p>
                                    <p class="text-xs text-gray-500">Con ventas</p>
                                </div>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                    Sin ventas
                                </span>
                            @endif
                        </div>

                        <!-- Deuda -->
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Deuda</span>
                            </div>
                            @if ($customer->total_debt > 0)
                                <div class="space-y-1">
                                    <div class="debt-value flex items-center gap-2"
                                        data-customer-id="{{ $customer->id }}"
                                        data-original-value="{{ $customer->total_debt }}">
                                        <p class="text-sm font-semibold text-red-600">
                                            {{ $currency->symbol }} <span
                                                class="debt-amount-value">{{ number_format($customer->formatted_total_debt, 2) }}</span>
                                        </p>
                                        @if ($customersData[$customer->id]['isDefaulter'])
                                            <span class="debt-warning-badge"
                                                title="Cliente con deudas de arqueos anteriores">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-0.5">
                                        <p class="text-xs font-medium text-gray-500">
                                            <span class="bs-debt text-indigo-600 font-semibold"
                                                data-debt="{{ $customer->total_debt }}">
                                                Bs.
                                                {{ number_format($customer->total_debt * ($exchangeRate ?? 1), 2) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            @else
                                <span class="no-debt-badge">Sin deuda</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <div class="flex justify-center gap-3">
                        @if ($permissions['can_show'])
                            <button type="button"
                                class="w-10 h-10 flex items-center justify-center rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                @click="openModal('showCustomerModal'); loadCustomerDetails({{ $customer->id }})"
                                title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                        @endif
                        @if ($permissions['can_edit'])
                            <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                class="w-10 h-10 flex items-center justify-center rounded-lg bg-green-500 hover:bg-green-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif
                        @if ($customer->total_debt > 0)
                            <button
                                class="w-10 h-10 flex items-center justify-center rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                onclick="spaPaymentHandler.openPaymentModal({{ $customer->id }})"
                                title="Pagar deuda">
                                <i class="fas fa-dollar-sign"></i>
                            </button>
                        @endif
                        @if ($permissions['can_create_sales'])
                            <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                class="w-10 h-10 flex items-center justify-center rounded-lg bg-purple-500 hover:bg-purple-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                title="Nueva venta">
                                <i class="fas fa-cart-plus"></i>
                            </a>
                        @endif
                        @if ($permissions['can_destroy'])
                            <button type="button"
                                class="w-10 h-10 flex items-center justify-center rounded-lg bg-red-500 hover:bg-red-600 text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                                @click="deleteCustomer({{ $customer->id }})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Paginación Inteligente -->
@if ($customers->hasPages())
    <div class="mt-8 px-6 pb-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="custom-pagination">
                <div class="pagination-info">
                    <span id="paginationInfo">Mostrando
                        {{ $customers->firstItem() ?? 0 }}-{{ $customers->lastItem() ?? 0 }} de
                        {{ $customers->total() }} clientes</span>
                </div>
                <div class="pagination-controls">
                    <!-- Botón Anterior -->
                    @if ($customers->hasPrevious)
                        <a href="{{ $customers->previousPageUrl }}" class="pagination-btn">
                            <i class="fas fa-chevron-left"></i>
                            Anterior
                        </a>
                    @else
                        <button class="pagination-btn" disabled>
                            <i class="fas fa-chevron-left"></i>
                            Anterior
                        </button>
                    @endif

                    <!-- Números de página inteligentes -->
                    <div class="page-numbers">
                        @foreach ($customers->smartLinks as $link)
                            @if ($link['isSeparator'])
                                <span class="page-separator">{{ $link['label'] }}</span>
                            @else
                                @if ($link['active'])
                                    <span class="page-number active">{{ $link['label'] }}</span>
                                @else
                                    <a href="{{ $link['url'] }}" class="page-number">{{ $link['label'] }}</a>
                                @endif
                            @endif
                        @endforeach
                    </div>

                    <!-- Botón Siguiente -->
                    @if ($customers->hasNext)
                        <a href="{{ $customers->nextPageUrl }}" class="pagination-btn">
                            Siguiente
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <button class="pagination-btn" disabled>
                            Siguiente
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
