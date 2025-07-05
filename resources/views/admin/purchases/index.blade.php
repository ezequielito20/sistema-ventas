@extends('adminlte::page')

@section('title', 'Gestión de Compras')

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
        <h1 class="text-dark font-weight-bold mb-2 mb-md-0">Gestión de Compras</h1>
        <div class="d-flex">
            <a href="{{ route('admin.purchases.report') }}" class="btn btn-info btn-sm mr-2" target="_blank">
                <i class="fas fa-file-pdf mr-1 d-md-inline d-none"></i>
                <span class="d-md-inline d-none">Reporte</span>
                <i class="fas fa-file-pdf d-md-none"></i>
            </a>
            @if ($cashCount)
                <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-circle mr-1 d-md-inline d-none"></i>
                    <span class="d-md-inline d-none">Nueva Compra</span>
                    <i class="fas fa-plus-circle d-md-none"></i>
                </a>
            @else
                <a href="{{ route('admin.cash-counts.create') }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-plus-circle mr-1 d-md-inline d-none"></i>
                    <span class="d-md-inline d-none">Abrir caja</span>
                    <i class="fas fa-plus-circle d-md-none"></i>
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
    {{-- Widgets de Estadísticas --}}
    <div class="row">
        <div class="col-lg-3 col-md-6 col-6 mb-3">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3 class="mb-1">{{ $totalPurchases }}</h3>
                    <p class="mb-0 small">
                        <span class="d-md-inline d-none">Productos Únicos Comprados</span>
                        <span class="d-md-none">Productos Únicos</span>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6 mb-3">
            <div class="small-box bg-success shadow-sm">
                <div class="inner">
                    <h3 class="mb-1 text-truncate">{{ $currency->symbol }} {{ number_format($totalAmount, 2) }}</h3>
                    <p class="mb-0 small">
                        <span class="d-md-inline d-none">Total Invertido</span>
                        <span class="d-md-none">Invertido</span>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6 mb-3">
            <div class="small-box bg-warning shadow-sm">
                <div class="inner">
                    <h3 class="mb-1">{{ $monthlyPurchases }}</h3>
                    <p class="mb-0 small">
                        <span class="d-md-inline d-none">Compras este mes</span>
                        <span class="d-md-none">Compras Mes</span>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6 mb-3">
            <div class="small-box bg-danger shadow-sm">
                <div class="inner">
                    <h3 class="mb-1">{{ $pendingDeliveries }}</h3>
                    <p class="mb-0 small">Pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Compras --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <h3 class="card-title mb-2 mb-md-0">
                <i class="fas fa-shopping-cart mr-2"></i>
                Lista de Compras
            </h3>
                
                <div class="d-flex align-items-center">
                    <div class="input-group input-group-sm mr-2" style="width: 250px;">
                        <input type="text" id="purchasesSearch" class="form-control" placeholder="Buscar compra...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Vista de tabla para pantallas grandes --}}
            <div class="d-none d-lg-block">
            <table id="purchasesTable" class="table table-striped table-hover">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>#</th>
                        <th>Recibo de pago</th>
                        <th>Fecha</th>
                        <th>Total Productos</th>
                        <th>Monto Total</th>
                        <th>Detalle de la Compra</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $purchase)
                        <tr style="text-align: center">
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $purchase->payment_receipt }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
                            <td>
                                <div class="d-flex flex-column align-items-center">
                                    <div class="mb-1">
                                        <span class="badge badge-info">
                                            <i class="fas fa-boxes mr-1"></i>
                                            {{ $purchase->details->count() }} únicos
                                        </span>
                                    </div>
                                    <div>
                                        <span class="badge badge-primary">
                                            <i class="fas fa-cubes mr-1"></i>
                                            {{ $purchase->details->sum('quantity') }} totales
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $currency->symbol }} {{ number_format($purchase->total_price, 2) }}</td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm view-details"
                                    data-id="{{ $purchase->id }}" data-toggle="modal" data-target="#purchaseDetailsModal">
                                    <i class="fas fa-list"></i> Ver Detalle
                                </button>
                            </td>
                            <td>
                                @if ($purchase->payment_receipt)
                                    <span class="badge badge-success">Completado</span>
                                @else
                                    <span class="badge badge-warning">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.purchases.edit', $purchase->id) }}"
                                        class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm delete-purchase"
                                        data-id="{{ $purchase->id }}" data-toggle="tooltip" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            {{-- Vista de tarjetas para móviles --}}
            <div class="d-lg-none">
                <div class="row" id="mobilePurchasesContainer">
                    @foreach ($purchases as $purchase)
                        <div class="col-12 mb-3 purchase-card">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1 font-weight-bold purchase-receipt">
                                                <i class="fas fa-receipt mr-1 text-primary"></i>
                                                {{ $purchase->payment_receipt ?: 'Sin recibo' }}
                                            </h6>
                                            <small class="text-muted purchase-date">
                                                <i class="fas fa-calendar mr-1"></i>
                                                {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}
                                            </small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-success font-weight-bold">
                                                #{{ $loop->iteration }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Productos:</small>
                                            <div class="d-flex flex-column">
                                                <span class="badge badge-info mb-1">
                                                    <i class="fas fa-boxes mr-1"></i>
                                                    {{ $purchase->details->count() }} únicos
                                                </span>
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-cubes mr-1"></i>
                                                    {{ $purchase->details->sum('quantity') }} totales
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Monto Total:</small>
                                            <div class="font-weight-bold text-success h5 mb-0">
                                                {{ $currency->symbol }} {{ number_format($purchase->total_price, 2) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <button type="button" class="btn btn-primary btn-sm view-details mr-2"
                                                data-id="{{ $purchase->id }}" data-toggle="modal" data-target="#purchaseDetailsModal">
                                                <i class="fas fa-list mr-1"></i>
                                                <span class="d-none d-sm-inline">Ver Detalle</span>
                                            </button>
                                            
                                            @if ($purchase->payment_receipt)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Completado
                                                </span>
                                            @else
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Pendiente
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="btn-group-mobile">
                                            <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm delete-purchase"
                                                data-id="{{ $purchase->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{--  Modal para mostrar detalles  --}}
    <div class="modal fade" id="purchaseDetailsModal" tabindex="-1" role="dialog"
        aria-labelledby="purchaseDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="purchaseDetailsModalLabel">Detalle de la Compra</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
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
                        <tbody id="purchaseDetailsTableBody">
                            <!-- Los detalles se cargarán aquí dinámicamente -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                                <td colspan="2" class="text-right"><strong>{{ $currency->symbol }} <span
                                            id="modalTotal">0.00</span></strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer">
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

        .table-responsive {
            margin: 0;
            padding: 0;
        }

        /* Estilos responsive para botones */
        .btn-group-mobile {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .btn-group-mobile .btn {
            flex: 1;
            min-width: auto;
        }

        /* Estilos para tarjetas de compras */
        .purchase-card {
            transition: all 0.3s ease;
        }

        .purchase-card:hover {
            transform: translateY(-2px);
        }

        .purchase-card .card {
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
        }

        .purchase-card .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* Mejoras en badges */
        .badge {
            transition: all 0.2s ease;
        }

        .badge:hover {
            transform: translateY(-1px);
        }

        /* Responsive breakpoints */
        @media (max-width: 576px) {
            .btn-group-mobile {
                flex-direction: row;
                gap: 0.25rem;
                justify-content: center;
            }
            
            .btn-group-mobile .btn {
                flex: 1;
                min-width: auto;
            }

            .small-box .inner h3 {
                font-size: 1.2rem;
            }

            .small-box .inner p {
                font-size: 0.75rem;
            }

            .small-box .icon {
                font-size: 50px;
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

            .card-header .d-flex {
                width: 100%;
            }
            
            .input-group {
                max-width: 100%;
                width: 100% !important;
            }
        }

        @media (max-width: 992px) {
            .btn-group-mobile .btn {
                font-size: 0.8rem;
                padding: 0.25rem 0.5rem;
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
            const table = $('#purchasesTable').DataTable({
                responsive: true,
                language: {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                }
            });

            // Conectar búsqueda del header con DataTable y vista móvil
            $('#purchasesSearch').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                
                // Búsqueda en DataTable (vista desktop)
                table.search(this.value).draw();
                
                // Búsqueda en tarjetas móviles
                $('.purchase-card').each(function() {
                    const receiptText = $(this).find('.purchase-receipt').text().toLowerCase();
                    const dateText = $(this).find('.purchase-date').text().toLowerCase();
                    
                    if (receiptText.includes(searchTerm) || dateText.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Mostrar modal de detalles de la compra
            $('.show-purchase').click(function() {
                const id = $(this).data('id');

                // Limpiar datos anteriores
                $('#purchaseId, #purchaseDate, #purchaseTotal, #purchaseStatus, #productName, #productQuantity, #supplierName')
                    .text('');
                $('#receiptImage').attr('src', '');
                $('#receiptSection').hide();

                $.ajax({
                    url: `/purchases/${id}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const purchase = response.purchase;

                            // Llenar datos generales
                            $('#purchaseId').text(`#${String(purchase.id).padStart(6, '0')}`);
                            $('#purchaseDate').text(purchase.formatted_date);
                            $('#purchaseTotal').text(`$${purchase.total_price}`);
                            $('#purchaseStatus').html(purchase.payment_receipt ?
                                '<span class="badge badge-success">Completado</span>' :
                                '<span class="badge badge-warning">Pendiente</span>'
                            );

                            // Llenar datos del producto
                            $('#productName').text(purchase.product.name);
                            $('#productQuantity').text(`${purchase.quantity} unidades`);
                            $('#supplierName').text(purchase.supplier.name);

                            // Mostrar recibo si existe
                            if (purchase.payment_receipt) {
                                $('#receiptImage').attr('src', purchase.payment_receipt);
                                $('#receiptSection').show();
                            }

                            // Mostrar el modal
                            $('#showPurchaseModal').modal('show');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron cargar los datos de la compra',
                            'error');
                    }
                });
            });

            // Eliminar compra
            $('.delete-purchase').click(function() {
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
                            url: `/purchases/delete/${id}`,
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
                                Swal.fire('Error', 'No se pudo eliminar la compra',
                                    'error');
                            }
                        });
                    }
                });
            });

            // Ver detalles de la compra
            $('.view-details').click(function() {
                const purchaseId = $(this).data('id');
                $('#purchaseDetailsTableBody').empty();

                $.ajax({
                    url: `/purchases/${purchaseId}/details`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            let total = 0;

                            response.details.forEach(function(detail) {
                                const quantity = parseFloat(detail.quantity);
                                const price = parseFloat(detail.product_price);
                                const subtotal = quantity * price;
                                total += subtotal;

                                $('#purchaseDetailsTableBody').append(`
                                    <tr>
                                        <td>${detail.product.code || ''}</td>
                                        <td>${detail.product.name || ''}</td>
                                        <td>${detail.product.category || 'Sin categoría'}</td>
                                        <td class="text-center">${quantity}</td>
                                        <td class="text-right">{{ $currency->symbol }} ${price.toFixed(2)}</td>
                                        <td class="text-right">{{ $currency->symbol }} ${subtotal.toFixed(2)}</td>
                                        
                                    </tr>
                                `);
                            });

                            // Formatear el total con separador de miles
                            const formattedTotal = total.toFixed(2).replace(
                                /\B(?=(\d{3})+(?!\d))/g, ",");
                            $('#modalTotal').text(formattedTotal);
                            $('#purchaseDetailsModal').modal('show');
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
        });
    </script>
@stop
