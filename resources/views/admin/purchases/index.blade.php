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
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $purchase)
                        <tr>
                            <td>
                                <strong>#{{ str_pad($purchase->id, 6, '0', STR_PAD_LEFT) }}</strong>
                            </td>
                            <td>
                                <i class="fas fa-calendar-day mr-1"></i>
                                {{ $purchase->purchase_date->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="supplier-avatar mr-2">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px; font-size: 1.2em;">
                                            {{ strtoupper(substr($purchase->supplier->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        {{ $purchase->supplier->name }}
                                    </div>
                                </div>
                            </td>
                            <td>{{ $purchase->product->name }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $purchase->quantity }} unidades
                                </span>
                            </td>
                            <td>
                                <strong class="text-success">
                                    ${{ number_format($purchase->total_price, 2) }}
                                </strong>
                            </td>
                            <td>
                                @if ($purchase->payment_receipt)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Completado
                                    </span>
                                @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock mr-1"></i>
                                        Pendiente
                                    </span>
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

    {{-- Modal para mostrar compra --}}
    <div class="modal fade" id="showPurchaseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Detalles de la Compra
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- Información de la compra --}}
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Información General
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Nº Compra:</th>
                                            <td id="purchaseId"></td>
                                        </tr>
                                        <tr>
                                            <th>Fecha:</th>
                                            <td id="purchaseDate"></td>
                                        </tr>
                                        <tr>
                                            <th>Total:</th>
                                            <td id="purchaseTotal"></td>
                                        </tr>
                                        <tr>
                                            <th>Estado:</th>
                                            <td id="purchaseStatus"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Información del producto y proveedor --}}
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-box mr-2"></i>
                                        Detalles del Producto
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Producto:</th>
                                            <td id="productName"></td>
                                        </tr>
                                        <tr>
                                            <th>Cantidad:</th>
                                            <td id="productQuantity"></td>
                                        </tr>
                                        <tr>
                                            <th>Proveedor:</th>
                                            <td id="supplierName"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Recibo de pago --}}
                    <div class="row mt-3" id="receiptSection">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-file-invoice mr-2"></i>
                                        Recibo de Pago
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <img id="receiptImage" src="" alt="Recibo" class="img-fluid"
                                        style="max-height: 300px;">
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
                    url: `/admin/purchases/${id}`,
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
                            url: `/admin/purchases/delete/${id}`,
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
        });
    </script>
@stop
