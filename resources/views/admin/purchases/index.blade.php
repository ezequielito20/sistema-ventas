@extends('adminlte::page')

@section('title', 'Gestión de Compras')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Gestión de Compras</h1>
        <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-2"></i>Nueva Compra
        </a>
    </div>
@stop

@section('content')
    {{-- Widgets de Estadísticas --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalPurchases }}</h3>
                    <p>Total Compras</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($totalAmount, 2) }}</h3>
                    <p>Total Invertido</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $monthlyPurchases }}</h3>
                    <p>Compras este mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $pendingDeliveries }}</h3>
                    <p>Pendientes</p>
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
            <h3 class="card-title">
                <i class="fas fa-shopping-cart mr-2"></i>
                Lista de Compras
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="purchasesTable" class="table table-striped table-hover">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>#</th>
                        <th>ID Compra</th>
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
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $purchase->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
                            <td>{{ $purchase->details->sum('quantity') }}
                                {{ $purchase->details->sum('quantity') == 1 ? 'producto' : 'productos' }}</td>
                            <td>${{ number_format($purchase->total_price, 2) }}</td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm view-details"
                                    data-id="{{ $purchase->id }}" data-toggle="modal" data-target="#purchaseDetailsModal">
                                    <i class="fas fa-list"></i> Ver Detalle
                                </button>
                                {{-- <a href="/purchases/{{ $purchase->id }}/details" class="btn btn-primary btn-sm">
                                    <i class="fas fa-list"></i> Ver Detalle
                                </a> --}}
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
                                    <button type="button" class="btn btn-info btn-sm show-purchase"
                                        data-id="{{ $purchase->id }}" data-toggle="tooltip" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
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
                                <th style="width: 80px">Imagen</th>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-right">Precio Unit.</th>
                                <th class="text-right">Subtotal</th>
                                <th class="text-center">Stock</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseDetailsTableBody">
                            <!-- Los detalles se cargarán aquí dinámicamente -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                <td id="modalTotal"></td>
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
            $('#purchasesTable').DataTable({
                responsive: true,
                language: {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                }
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
                // alert(purchaseId); // Para debug

                // Limpiar la tabla
                $('#purchaseDetailsTableBody').empty();
                $('#modalTotal').text('0.00');

                // Cargar los detalles
                $.ajax({
                    url: `/purchases/${purchaseId}/details`, // URL corregida
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            let total = 0;

                            response.details.forEach(function(detail) {
                                // Convertir los valores a números
                                const quantity = parseFloat(detail.quantity);
                                const price = parseFloat(detail.product_price);
                                const subtotal = quantity * price;
                                total += subtotal;

                                $('#purchaseDetailsTableBody').append(`
                                    <tr>
                                        <td class="text-center">
                                            <img src="${detail.product.image_url || '/img/no-image.png'}" 
                                                 class="product-img" 
                                                 alt="${detail.product.name}"
                                                 onerror="this.src='/img/no-image.png'">
                                        </td>
                                        <td>${detail.product.code}</td>
                                        <td>${detail.product.name}</td>
                                        <td>${detail.product.category || 'Sin categoría'}</td>
                                        <td class="text-center">${quantity}</td>
                                        <td class="text-right">$${price.toFixed(2)}</td>
                                        <td class="text-right">$${subtotal.toFixed(2)}</td>
                                        <td class="text-center">
                                            <span class="badge ${detail.product.stock > 10 ? 'badge-success' : 'badge-warning'}">
                                                ${detail.product.stock || 0}
                                            </span>
                                        </td>
                                    </tr>
                                `);
                            });

                            $('#modalTotal').text(total.toFixed(2));
                            $('#purchaseDetailsModal').modal('show');
                        } else {
                            Swal.fire('Error', response.message ||
                                'Error al cargar los detalles', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        console.log('Status:', status);
                        console.log('Response:', xhr
                            .responseText); // Para ver más detalles del error
                        Swal.fire('Error', 'No se pudieron cargar los detalles de la compra',
                            'error');
                    }
                });
            });
        });
    </script>
@stop
