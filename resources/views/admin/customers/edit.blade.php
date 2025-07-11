@extends('adminlte::page')

@section('title', 'Editar Cliente')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-dark font-weight-bold">Editar Cliente</h1>
            <p class="mb-0">Actualice la información del cliente en el formulario</p>
        </div>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST" id="customerForm"
                    class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-edit mr-2"></i>
                                Información del Cliente
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                {{-- Nombre Completo --}}
                                <div class="col-md-6">
                                    <div class="form-group position-relative">
                                        <label for="name" class="font-weight-bold ">
                                            Nombre Completo
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                            </div>
                                            <input type="text"
                                                class="form-control form-control-lg @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name', $customer->name) }}"
                                                placeholder="Ingrese el nombre completo"  autofocus>
                                            <div class="valid-feedback">
                                                ¡Se ve bien!
                                            </div>
                                            @error('name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- NIT --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nit_number" class="font-weight-bold ">
                                            Número de Cédula
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-id-card"></i>
                                                </span>
                                            </div>
                                            <input type="text"
                                                class="form-control form-control-lg @error('nit_number') is-invalid @enderror"
                                                id="nit_number" name="nit_number"
                                                value="{{ old('nit_number', $customer->nit_number) }}"
                                                placeholder="Ingrese la Cédula" >
                                            @error('nit_number')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Teléfono --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="font-weight-bold ">
                                            Teléfono
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-phone"></i>
                                                </span>
                                            </div>
                                            <input type="tel"
                                                class="form-control form-control-lg @error('phone') is-invalid @enderror"
                                                id="phone" name="phone" value="{{ old('phone', $customer->phone) }}"
                                                placeholder="(123) 456-7890" >
                                            @error('phone')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Email --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="font-weight-bold ">
                                            Correo Electrónico
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-envelope"></i>
                                                </span>
                                            </div>
                                            <input type="email"
                                                class="form-control form-control-lg @error('email') is-invalid @enderror"
                                                id="email" name="email" value="{{ old('email', $customer->email) }}"
                                                placeholder="ejemplo@correo.com" >
                                            @error('email')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Deuda Total --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="total_debt" class="font-weight-bold">
                                            Deuda Total
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </span>
                                            </div>
                                            <input type="number" step="0.01" min="0"
                                                class="form-control form-control-lg @error('total_debt') is-invalid @enderror"
                                                id="total_debt" name="total_debt" value="{{ old('total_debt', $customer->total_debt) }}"
                                                placeholder="0.00">
                                            @error('total_debt')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-lg btn-primary">
                                    <i class="fas fa-save mr-2"></i>
                                    Actualizar Cliente
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .:after {
            content: ' *';
            color: #dc3545;
            font-weight: bold;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .input-group-text {
            min-width: 46px;
            justify-content: center;
        }

        .card {
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .btn-lg {
            padding: 12px 30px;
            font-weight: bold;
        }

        .form-control-lg {
            border-radius: 0 8px 8px 0;
        }

        .input-group-text {
            border-radius: 8px 0 0 8px;
        }

        .valid-feedback,
        .invalid-feedback {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .was-validated .form-control:valid {
            border-color: #28a745;
            padding-right: calc(1.5em + .75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(.375em + .1875rem) center;
            background-size: calc(.75em + .375rem) calc(.75em + .375rem);
        }
    </style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar máscaras
            $('#phone').inputmask('(999) 999-9999');
            // $('#nit_number').inputmask('999-999999-999-9');

            // Validación del formulario
            $('#customerForm').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });

            // Validación en tiempo real del email
            $('#email').on('input', function() {
                const email = $(this).val();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (emailRegex.test(email)) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                } else {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                }
            });

            // Capitalizar automáticamente el nombre
            $('#name').on('input', function() {
                const input = $(this);
                const cursorPosition = input[0].selectionStart;
                const originalValue = input.val();
                
                let words = originalValue.split(' ');
                words = words.map(word => {
                    return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
                });
                const newValue = words.join(' ');
                
                // Solo actualizar si el valor cambió
                if (originalValue !== newValue) {
                    input.val(newValue);
                    
                    // Restaurar la posición del cursor
                    input[0].setSelectionRange(cursorPosition, cursorPosition);
                }
            });

            // Mostrar tooltip con el formato requerido
            $('[data-toggle="tooltip"]').tooltip();

            // Animación suave al hacer focus en los inputs
            $('.form-control').on('focus', function() {
                $(this).closest('.form-group').addClass('focused');
            }).on('blur', function() {
                $(this).closest('.form-group').removeClass('focused');
            });
        });
    </script>
@stop
