@extends('adminlte::page')

@section('title', 'Crear Cliente')

@section('content_header')
    <div class="hero-section mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            <i class="fas fa-user-plus-gradient"></i>
                            Crear Nuevo Cliente
                        </h1>
                        <p class="hero-subtitle">Ingrese la información del cliente en el formulario para registrarlo en el sistema</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <div class="hero-action-buttons d-flex justify-content-lg-end justify-content-center align-items-center gap-3 flex-wrap">
                        <button onclick="goBack()" class="hero-btn hero-btn-secondary" data-toggle="tooltip" title="Volver">
                            <i class="fas fa-arrow-left"></i>
                            <span class="d-none d-md-inline">Volver</span>
                        </button>
                        <button type="submit" form="customerForm" class="hero-btn hero-btn-primary" data-toggle="tooltip" title="Guardar Cliente">
                            <i class="fas fa-save"></i>
                            <span class="d-none d-md-inline">Guardar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .hero-action-buttons {
        gap: 1rem !important;
    }
    .hero-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.85);
        color: var(--primary-color);
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 0.7rem 1.2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        transition: all 0.2s;
        cursor: pointer;
        min-width: 44px;
        min-height: 44px;
        position: relative;
        text-decoration: none;
        outline: none;
    }
    .hero-btn i {
        font-size: 1.3rem;
        color: var(--primary-color);
        margin-right: 0.2rem;
    }
    .hero-btn-secondary { color: #6c757d; }
    .hero-btn-secondary i { color: #6c757d; }
    .hero-btn-primary { color: #667eea; }
    .hero-btn-primary i { color: #667eea; }
    .hero-btn:hover, .hero-btn:focus {
        background: #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        transform: translateY(-2px) scale(1.04);
        color: var(--primary-color);
        text-decoration: none;
    }
    .hero-btn:active {
        transform: scale(0.97);
    }
    .hero-btn span {
        font-size: 1rem;
        font-weight: 600;
        color: inherit;
        white-space: nowrap;
    }
    @media (max-width: 991px) {
        .hero-action-buttons {
            justify-content: center !important;
        }
    }
    @media (max-width: 767px) {
        .hero-btn span {
            display: none !important;
        }
        .hero-btn {
            padding: 0.7rem !important;
            min-width: 44px;
        }
    }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form action="{{ route('admin.customers.store') }}" method="POST" id="customerForm" class="needs-validation"
                    novalidate>
                    @csrf
                    @if(request('return_to'))
                        <input type="hidden" name="return_to" value="{{ request('return_to') }}">
                    @endif
                    
                    <div class="form-card">
                        <div class="form-card-header">
                            <div class="header-content">
                                <div class="header-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="header-text">
                                    <h4>Información del Cliente</h4>
                                    <p>Complete todos los campos requeridos para crear el nuevo cliente</p>
                                </div>
                            </div>
                            <button type="button" class="collapse-btn" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>

                        <div class="form-card-body">
                            <div class="form-grid">
                                {{-- Nombre Completo --}}
                                <div class="form-group modern-form-group">
                                    <label for="name" class="modern-label required">
                                        <i class="fas fa-user"></i>
                                        Nombre Completo
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text"
                                            class="modern-input @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name') }}"
                                            placeholder="Ingrese el nombre completo" required autofocus>
                                        <div class="valid-feedback">
                                            <i class="fas fa-check-circle"></i>
                                            ¡Se ve bien!
                                        </div>
                                        @error('name')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- NIT --}}
                                <div class="form-group modern-form-group">
                                    <label for="nit_number" class="modern-label">
                                        <i class="fas fa-id-card"></i>
                                        Número de Cédula
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text"
                                            class="modern-input @error('nit_number') is-invalid @enderror"
                                            id="nit_number" name="nit_number" value="{{ old('nit_number') }}"
                                            placeholder="Ingrese la Cédula">
                                        @error('nit_number')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Teléfono --}}
                                <div class="form-group modern-form-group">
                                    <label for="phone" class="modern-label">
                                        <i class="fas fa-phone"></i>
                                        Teléfono
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="tel"
                                            class="modern-input @error('phone') is-invalid @enderror"
                                            id="phone" name="phone" value="{{ old('phone') }}"
                                            placeholder="(123) 456-7890" autocomplete="off">
                                        @error('phone')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Email --}}
                                <div class="form-group modern-form-group">
                                    <label for="email" class="modern-label">
                                        <i class="fas fa-envelope"></i>
                                        Correo Electrónico
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="email"
                                            class="modern-input @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email') }}"
                                            placeholder="ejemplo@correo.com">
                                        @error('email')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-card-footer">
                            <div class="footer-actions">
                                <button type="submit" class="action-btn action-btn-primary" name="action" value="save" id="submitCustomer">
                                    <i class="fas fa-save"></i>
                                    <span>Guardar Cliente</span>
                                </button>
                                <button type="submit" class="action-btn action-btn-success" name="action" value="save_and_new" id="submitCustomerAndNew">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Guardar y Crear Otro</span>
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
        /* ===== VARIABLES Y CONFIGURACIÓN GLOBAL ===== */
        :root {
            --primary-color: #667eea;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-color: #f093fb;
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-color: #4facfe;
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-color: #43e97b;
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --danger-color: #fa709a;
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --purple-color: #a8edea;
            --purple-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== HERO SECTION ===== */
        .hero-section {
            background: var(--primary-gradient);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .hero-title i {
            font-size: 3rem;
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        .hero-stats {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* ===== FORM CARD ===== */
        .form-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .form-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .form-card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .header-text h4 {
            margin: 0;
            font-weight: 600;
            color: var(--dark-color);
        }

        .header-text p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .collapse-btn {
            background: none;
            border: none;
            color: #666;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .collapse-btn:hover {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .form-card-body {
            padding: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        /* ===== MODERN FORM GROUPS ===== */
        .modern-form-group {
            position: relative;
        }

        .modern-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .modern-label i {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .modern-label.required::after {
            content: ' *';
            color: #dc3545;
            font-weight: bold;
        }

        .input-wrapper {
            position: relative;
        }

        .modern-input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: var(--transition);
            background: white;
        }

        .modern-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .modern-input::placeholder {
            color: #adb5bd;
        }

        /* ===== VALIDATION FEEDBACK ===== */
        .valid-feedback,
        .invalid-feedback {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            animation: fadeIn 0.3s ease;
        }

        .valid-feedback {
            color: #28a745;
        }

        .invalid-feedback {
            color: #dc3545;
        }

        .valid-feedback i,
        .invalid-feedback i {
            font-size: 1rem;
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

        /* ===== FORM CARD FOOTER ===== */
        .form-card-footer {
            background: #f8f9fa;
            padding: 1.5rem;
            border-top: 1px solid #dee2e6;
        }

        .footer-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        /* ===== ACTION BUTTONS ===== */
        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            color: white;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn-primary {
            background: var(--primary-gradient);
        }

        .action-btn-success {
            background: var(--success-gradient);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .action-btn:active {
            transform: scale(0.97);
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 768px) {
            .hero-section {
                padding: 1.5rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-stats {
                gap: 1rem;
            }

            .stat-number {
                font-size: 1.5rem;
            }

            .form-card-body {
                padding: 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .footer-actions {
                justify-content: center;
            }

            .action-btn {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 1.5rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .form-card-header {
                padding: 1rem;
            }

            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .header-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .footer-actions {
                flex-direction: column;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* ===== FOCUS STATES ===== */
        .modern-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* ===== DISABLED STATES ===== */
        .action-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .action-btn:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        /* ===== SCROLLBAR STYLING ===== */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #5a6fd8;
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Cargar Inputmask
            loadInputmask(function() {
                // Guardar la URL original cuando se carga la página por primera vez
                if (!sessionStorage.getItem('customers_original_referrer')) {
                    const referrer = document.referrer;
                    if (referrer && !referrer.includes('/customers/create')) {
                        sessionStorage.setItem('customers_original_referrer', referrer);
                    }
                }
                
                // Inicializar máscaras
                $('#phone').inputmask('(999) 999-9999');
                // $('#nit_number').inputmask('999-999999-999-9');

                // Validación del formulario
                $('#customerForm').on('submit', function(e) {
                    // Deshabilitar botones para prevenir múltiples envíos
                    $('#submitCustomer, #submitCustomerAndNew').prop('disabled', true);
                    
                    if (!this.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        // Rehabilitar botones si hay error
                        $('#submitCustomer, #submitCustomerAndNew').prop('disabled', false);
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
                    let words = $(this).val().split(' ');
                    words = words.map(word => {
                        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
                    });
                    $(this).val(words.join(' '));
                });

                // Mostrar tooltip con el formato requerido
                $('[data-toggle="tooltip"]').tooltip();

                // Animación suave al hacer focus en los inputs
                $('.modern-input').on('focus', function() {
                    $(this).closest('.modern-form-group').addClass('focused');
                }).on('blur', function() {
                    $(this).closest('.modern-form-group').removeClass('focused');
                });

                // Función para navegar de vuelta a la vista original
                window.goBack = function() {
                    // Verificar si hay una URL de referencia guardada en sessionStorage
                    const originalReferrer = sessionStorage.getItem('customers_original_referrer');
                    
                    if (originalReferrer && originalReferrer !== window.location.href) {
                        // Si tenemos una URL original guardada, ir allí
                        window.location.href = originalReferrer;
                    } else {
                        // Comportamiento normal del botón volver
                        window.history.back();
                    }
                }
                
                console.log('Inputmask cargado para customers create');
            });
        });
    </script>
@stop
