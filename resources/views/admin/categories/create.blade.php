@extends('adminlte::page')

@section('title', 'Crear Categoría')

@section('content_header')
    <div class="hero-section mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            <i class="fas fa-tag-plus-gradient"></i>
                            Crear Nueva Categoría
                        </h1>
                        <p class="hero-subtitle">Agrega una nueva categoría para organizar mejor tus productos</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <div class="hero-action-buttons d-flex justify-content-lg-end justify-content-center align-items-center gap-3 flex-wrap">
                        <button onclick="goBack()" class="hero-btn hero-btn-secondary" data-toggle="tooltip" title="Volver">
                            <i class="fas fa-arrow-left"></i>
                            <span class="d-none d-md-inline">Volver</span>
                        </button>
                        <button type="submit" form="categoryForm" class="hero-btn hero-btn-primary" data-toggle="tooltip" title="Guardar Categoría">
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
    <div class="modern-form-container">
        <div class="form-card">
            <div class="form-header">
                <div class="form-title">
                    <i class="fas fa-tag"></i>
                    <span>Datos de la Categoría</span>
                </div>
                <div class="form-subtitle">
                    Completa la información para crear una nueva categoría
                </div>
            </div>
            
            <form id="categoryForm" action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                <div class="form-body">
                        <div class="form-group">
                        <label for="name" class="form-label">
                            <i class="fas fa-tag"></i>
                            Nombre de la Categoría
                            <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-tag"></i>
                                </span>
                            </div>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="Ej: Electrónicos, Ropa, Hogar..."
                                   required>
                        </div>
                        @error('name')
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <small class="form-text">
                            <i class="fas fa-info-circle"></i>
                            El nombre debe ser descriptivo y único. Máximo 255 caracteres.
                        </small>
                    </div>

                        <div class="form-group">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left"></i>
                            Descripción
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-align-left"></i>
                                </span>
                            </div>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Describe brevemente qué tipo de productos incluye esta categoría...">{{ old('description') }}</textarea>
                        </div>
                        @error('description')
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <small class="form-text">
                            <i class="fas fa-info-circle"></i>
                            La descripción es opcional pero ayuda a organizar mejor los productos.
                        </small>
                    </div>
                </div>

                <div class="form-footer">
                    <div class="form-actions">
                        <button type="button" onclick="goBack()" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            <span>Volver</span>
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitCategory">
                            <i class="fas fa-save"></i>
                            <span>Guardar Categoría</span>
                        </button>
                    </div>
                    </div>
                </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/animate-css/animate.min.css') }}">
    <style>
        :root {
            --primary-color: #667eea;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-color: #2d3748;
            --light-color: #f7fafc;
            --border-color: #e2e8f0;
            --shadow-light: 0 2px 8px rgba(0,0,0,0.07);
            --shadow-medium: 0 4px 16px rgba(0,0,0,0.12);
            --success-color: #48bb78;
            --warning-color: #ed8936;
            --danger-color: #f56565;
        }

        /* Hero Section */
        .hero-section {
            background: var(--gradient-primary);
            border-radius: 20px;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-medium);
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
            font-size: 2rem;
            background: rgba(255,255,255,0.2);
            padding: 1rem;
            border-radius: 50%;
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
            display: flex;
            flex-direction: column;
            align-items: center;
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 12px;
            min-width: 120px;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            text-align: center;
        }

        /* Contenedor del formulario */
        .modern-form-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-light);
            overflow: hidden;
        }

        .form-header {
            background: var(--gradient-primary);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .form-subtitle {
            opacity: 0.9;
            font-size: 1rem;
        }

        .form-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .form-label i {
            color: var(--primary-color);
        }

        .required {
            color: var(--danger-color);
            font-weight: 700;
        }

        .input-group {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-light);
        }

        .input-group-text {
            background: var(--light-color);
            border: 1px solid var(--border-color);
            color: var(--primary-color);
            font-weight: 600;
        }

        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 0 12px 12px 0;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-control.is-invalid {
            border-color: var(--danger-color);
        }

        .invalid-feedback {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--danger-color);
            font-weight: 500;
            margin-top: 0.5rem;
        }

        .form-text {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #718096;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .form-text i {
            color: var(--primary-color);
        }

        .form-footer {
            background: var(--light-color);
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--border-color);
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-stats {
                gap: 1rem;
            }

            .stat-item {
                min-width: 100px;
                padding: 0.75rem;
            }

            .form-header {
                padding: 1.5rem;
            }

            .form-body {
                padding: 1.5rem;
            }

            .form-footer {
                padding: 1rem 1.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Animaciones */
        .form-card {
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script>
    $(document).ready(function() {
        // Cargar SweetAlert2 y Animate.css
        loadSweetAlert2(function() {
            loadAnimateCSS(function() {
                // Función para volver atrás
                window.goBack = function() {
                    if (document.referrer) {
                        window.history.back();
                    } else {
                        window.location.href = '{{ route("admin.categories.index") }}';
                    }
                };

                // Validación del formulario
                $('#categoryForm').on('submit', function(e) {
                    const name = $('#name').val().trim();
                    const description = $('#description').val().trim();
                    
                    // Validar nombre
                    if (name.length === 0) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validación',
                            text: 'El nombre de la categoría es obligatorio'
                        });
                        $('#name').focus();
                        return false;
                    }
                    
                    if (name.length > 255) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validación',
                            text: 'El nombre no puede exceder los 255 caracteres'
                        });
                        $('#name').focus();
                        return false;
                    }

                    // Deshabilitar botón y mostrar loading
                    $('#submitCategory').prop('disabled', true).html(`
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Guardando...</span>
                    `);
                });

                // Contador de caracteres para descripción
                $('#description').on('input', function() {
                    const maxLength = 255;
                    const currentLength = $(this).val().length;
                    const remaining = maxLength - currentLength;
                    
                    // Actualizar texto de ayuda
                    const helpText = $(this).siblings('.form-text');
                    if (remaining < 0) {
                        helpText.html(`
                            <i class="fas fa-exclamation-triangle" style="color: var(--danger-color);"></i>
                            Has excedido el límite de caracteres por ${Math.abs(remaining)} caracteres
                        `);
                    } else {
                        helpText.html(`
                            <i class="fas fa-info-circle"></i>
                            La descripción es opcional pero ayuda a organizar mejor los productos. 
                            <span style="color: ${remaining < 50 ? 'var(--warning-color)' : 'inherit'}">
                                (${remaining} caracteres restantes)
                            </span>
                        `);
                    }
                });

                // Confirmación antes de salir si hay cambios
                let formChanged = false;
                
                $('#categoryForm input, #categoryForm textarea').on('change keyup', function() {
                    formChanged = true;
                });

                window.onbeforeunload = function() {
                    if (formChanged) {
                        return "¿Estás seguro de que quieres salir? Los cambios no guardados se perderán.";
                    }
                };

                // Desactivar la advertencia al enviar el formulario
                $('#categoryForm').on('submit', function() {
                    window.onbeforeunload = null;
                });

                // Animaciones de entrada
                $('.hero-section').addClass('animate__animated animate__fadeInDown');
                $('.form-card').addClass('animate__animated animate__fadeInUp');
                
            });
        });
    });
</script>
@stop
