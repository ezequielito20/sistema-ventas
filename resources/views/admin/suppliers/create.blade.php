@extends('adminlte::page')

@section('title', 'Crear Proveedor')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Crear Nuevo Proveedor</h1>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck mr-2"></i>
                    Información del Proveedor
                </h3>
            </div>
            <form action="{{ route('admin.suppliers.store') }}" method="POST" id="supplierForm">
                @csrf
                <div class="card-body">
                    {{-- Información de la Empresa --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="text-primary">
                                <i class="fas fa-building mr-2"></i>
                                Datos de la Empresa
                            </h4>
                            <hr>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_name" class="required">
                                    Nombre de la Empresa
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-building"></i>
                                        </span>
                                    </div>
                                    <input type="text" 
                                           class="form-control @error('company_name') is-invalid @enderror" 
                                           id="company_name"
                                           name="company_name"
                                           value="{{ old('company_name') }}"
                                           placeholder="Ingrese el nombre de la empresa"
                                           required>
                                    @error('company_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_email" class="required">
                                    Correo Electrónico
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                    </div>
                                    <input type="email" 
                                           class="form-control @error('company_email') is-invalid @enderror"
                                           id="company_email"
                                           name="company_email"
                                           value="{{ old('company_email') }}"
                                           placeholder="ejemplo@empresa.com"
                                           required>
                                    @error('company_email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_phone" class="required">
                                    Teléfono de la Empresa
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                    </div>
                                    <input type="tel" 
                                           class="form-control @error('company_phone') is-invalid @enderror"
                                           id="company_phone"
                                           name="company_phone"
                                           value="{{ old('company_phone') }}"
                                           placeholder="(123) 456-7890"
                                           required>
                                    @error('company_phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_address" class="required">
                                    Dirección de la Empresa
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" 
                                           class="form-control @error('company_address') is-invalid @enderror"
                                           id="company_address"
                                           name="company_address"
                                           value="{{ old('company_address') }}"
                                           placeholder="Ingrese la dirección completa"
                                           required>
                                    @error('company_address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Información del Contacto --}}
                    <div class="row">
                        <div class="col-12">
                            <h4 class="text-primary">
                                <i class="fas fa-user mr-2"></i>
                                Datos del Contacto
                            </h4>
                            <hr>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="supplier_name" class="required">
                                    Nombre del Contacto
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    </div>
                                    <input type="text" 
                                           class="form-control @error('supplier_name') is-invalid @enderror"
                                           id="supplier_name"
                                           name="supplier_name"
                                           value="{{ old('supplier_name') }}"
                                           placeholder="Nombre completo del contacto"
                                           required>
                                    @error('supplier_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="supplier_phone" class="required">
                                    Teléfono del Contacto
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-mobile-alt"></i>
                                        </span>
                                    </div>
                                    <input type="tel" 
                                           class="form-control @error('supplier_phone') is-invalid @enderror"
                                           id="supplier_phone"
                                           name="supplier_phone"
                                           value="{{ old('supplier_phone') }}"
                                           placeholder="(123) 456-7890"
                                           required>
                                    @error('supplier_phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .required:after {
        content: ' *';
        color: red;
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .input-group-text {
        width: 40px;
        justify-content: center;
    }

    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    .btn {
        padding: 8px 20px;
    }

    .card-footer {
        background-color: #f8f9fa;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
<script>
$(document).ready(function() {
    // Máscara para teléfonos
    $('#company_phone, #supplier_phone').inputmask('(999) 999-9999');

    // Validación del formulario
    $('#supplierForm').on('submit', function(e) {
        let isValid = true;
        
        // Remover espacios en blanco extras
        $('input[type="text"], input[type="email"]').each(function() {
            $(this).val($(this).val().trim());
        });

        // Validar email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test($('#company_email').val())) {
            $('#company_email').addClass('is-invalid');
            isValid = false;
        }

        // Validar teléfonos
        const phoneRegex = /^\(\d{3}\)\s\d{3}-\d{4}$/;
        ['#company_phone', '#supplier_phone'].forEach(selector => {
            if (!phoneRegex.test($(selector).val())) {
                $(selector).addClass('is-invalid');
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error de Validación',
                text: 'Por favor, revise los campos marcados en rojo.',
                confirmButtonText: 'Entendido'
            });
        }
    });

    // Limpiar validación al escribir
    $('input').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>
@stop
