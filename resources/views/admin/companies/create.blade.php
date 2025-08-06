<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Crear Empresa</title>
    
    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/inter.css') }}">
    
    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-soft: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 20px 60px rgba(0, 0, 0, 0.15);
            --border-radius: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.2) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(1deg); }
            66% { transform: translateY(10px) rotate(-1deg); }
        }

        /* Floating Elements */
        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: floatElement 15s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            width: 60px;
            height: 60px;
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 80px;
            height: 80px;
            top: 20%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            width: 40px;
            height: 40px;
            bottom: 30%;
            left: 15%;
            animation-delay: 4s;
        }

        .floating-element:nth-child(4) {
            width: 70px;
            height: 70px;
            bottom: 20%;
            right: 5%;
            animation-delay: 6s;
        }

        @keyframes floatElement {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
            50% { transform: translateY(-30px) rotate(180deg); opacity: 1; }
        }

        /* Main Container */
        .main-container {
            position: relative;
            z-index: 10;
            padding: 20px;
            min-height: 100vh;
        }

        /* Header */
        .header-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .page-subtitle {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }

        /* Form Container */
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-strong);
            border: 1px solid var(--glass-border);
            overflow: hidden;
            max-width: 900px;
            margin: 0 auto;
        }

        .form-header {
            background: var(--primary-gradient);
            padding: 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .form-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .form-header-content {
            position: relative;
            z-index: 2;
        }

        .form-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            font-weight: 400;
        }

        /* Form Body */
        .form-body {
            padding: 30px;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--primary-gradient);
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.85rem;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px 16px 12px 45px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 500;
            color: #2c3e50;
            background: #f8f9fa;
            transition: var(--transition);
            outline: none;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            font-size: 16px;
            transition: var(--transition);
            z-index: 2;
        }

        .form-input:focus + .input-icon,
        .form-select:focus + .input-icon,
        .form-textarea:focus + .input-icon {
            color: #667eea;
        }

        .form-textarea {
            min-height: 80px;
            resize: vertical;
        }

        .form-textarea + .input-icon {
            top: 20px;
            transform: none;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .error-message i {
            margin-right: 6px;
            font-size: 14px;
        }

        /* File Upload */
        .file-upload-container {
            border: 2px dashed #e9ecef;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            transition: var(--transition);
            background: #f8f9fa;
        }

        .file-upload-container:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .file-upload-container.dragover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            transform: scale(1.02);
        }

        .file-upload-icon {
            font-size: 2.5rem;
            color: #adb5bd;
            margin-bottom: 12px;
            transition: var(--transition);
        }

        .file-upload-container:hover .file-upload-icon {
            color: #667eea;
        }

        .file-upload-text {
            color: #6c757d;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .file-input {
            display: none;
        }

        .file-label {
            background: var(--primary-gradient);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: inline-block;
        }

        .file-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .preview-container {
            margin-top: 15px;
            text-align: center;
        }

        .preview-image {
            max-width: 150px;
            max-height: 150px;
            border-radius: 10px;
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
        }

        .preview-image:hover {
            transform: scale(1.05);
        }

        /* Buttons */
        .form-actions {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid #e9ecef;
        }

        .btn {
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            margin: 0 8px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(108, 117, 125, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                padding: 20px 10px;
            }

            .form-body {
                padding: 20px;
            }

            .page-title {
                font-size: 2rem;
            }

            .form-title {
                font-size: 1.75rem;
            }

            .floating-elements {
                display: none;
            }

            .btn {
                margin: 5px;
                width: 100%;
                max-width: 300px;
            }
        }

        @media (max-width: 480px) {
            .form-body {
                padding: 15px;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .form-title {
                font-size: 1.5rem;
            }
        }

        /* Loading States */
        .loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .loading.active {
            display: block;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Success Animation */
        .success-checkmark {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 20px;
        }

        .success-checkmark.active {
            display: block;
            animation: checkmark 0.5s ease-in-out;
        }

        @keyframes checkmark {
            0% { transform: translate(-50%, -50%) scale(0); }
            50% { transform: translate(-50%, -50%) scale(1.2); }
            100% { transform: translate(-50%, -50%) scale(1); }
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <div class="main-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1 class="page-title">Crear Nueva Empresa</h1>
            <p class="page-subtitle">Configura tu empresa para comenzar a gestionar tu negocio</p>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <!-- Form Header -->
            <div class="form-header">
                <div class="form-header-content">
                    <h2 class="form-title">
                        <i class="fas fa-building mr-3"></i>
                        Información de la Empresa
                    </h2>
                    <p class="form-subtitle">Completa los datos para configurar tu empresa</p>
                </div>
            </div>

            <!-- Form Body -->
            <div class="form-body">
                <form action="{{ route('admin.company.store') }}" method="POST" enctype="multipart/form-data" id="companyForm">
                    @csrf

                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-info-circle mr-2"></i>
                            Información Básica
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Country field -->
                                <div class="form-group">
                                    <label for="country" class="form-label">País</label>
                                    <div class="input-group">
                                        <select id="country" name="country" class="form-select @error('country') is-invalid @enderror" required>
                                            <option value="">Seleccione un país</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}" {{ old('country') == $country->name ? 'selected' : '' }}>
                                                    {{ $country->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-globe input-icon"></i>
                                    </div>
                                    @error('country')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Company name field -->
                                <div class="form-group">
                                    <label for="name" class="form-label">Nombre de la Empresa</label>
                                    <div class="input-group">
                                        <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror"
                                               value="{{ old('name') }}" placeholder="Ingresa el nombre de tu empresa" required>
                                        <i class="fas fa-building input-icon"></i>
                                    </div>
                                    @error('name')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Business type field -->
                                <div class="form-group">
                                    <label for="business_type" class="form-label">Tipo de Negocio</label>
                                    <div class="input-group">
                                        <input type="text" id="business_type" name="business_type" class="form-input @error('business_type') is-invalid @enderror"
                                               value="{{ old('business_type') }}" placeholder="Ej: Comercio, Servicios, etc." required>
                                        <i class="fas fa-store input-icon"></i>
                                    </div>
                                    @error('business_type')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- NIT field -->
                                <div class="form-group">
                                    <label for="nit" class="form-label">Cédula / NIT</label>
                                    <div class="input-group">
                                        <input type="text" id="nit" name="nit" class="form-input @error('nit') is-invalid @enderror"
                                               value="{{ old('nit') }}" placeholder="Número de identificación tributaria" required>
                                        <i class="fas fa-id-card input-icon"></i>
                                    </div>
                                    @error('nit')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Phone field -->
                                <div class="form-group">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <div class="input-group">
                                        <input type="text" id="phone" name="phone" class="form-input @error('phone') is-invalid @enderror"
                                               value="{{ old('phone') }}" placeholder="Número de teléfono" required>
                                        <i class="fas fa-phone input-icon"></i>
                                    </div>
                                    @error('phone')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Email field -->
                                <div class="form-group">
                                    <label for="email" class="form-label">Correo Electrónico</label>
                                    <div class="input-group">
                                        <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror"
                                               value="{{ old('email') }}" placeholder="correo@empresa.com" required>
                                        <i class="fas fa-envelope input-icon"></i>
                                    </div>
                                    @error('email')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Tax amount field -->
                                <div class="form-group">
                                    <label for="tax_amount" class="form-label">Porcentaje de Impuesto</label>
                                    <div class="input-group">
                                        <input type="number" id="tax_amount" name="tax_amount" class="form-input @error('tax_amount') is-invalid @enderror"
                                               value="{{ old('tax_amount') }}" placeholder="Ej: 19" required>
                                        <i class="fas fa-percent input-icon"></i>
                                    </div>
                                    @error('tax_amount')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Tax name field -->
                                <div class="form-group">
                                    <label for="tax_name" class="form-label">Nombre del Impuesto</label>
                                    <div class="input-group">
                                        <input type="text" id="tax_name" name="tax_name" class="form-input @error('tax_name') is-invalid @enderror"
                                               value="{{ old('tax_name') }}" placeholder="Ej: IVA, GST, etc." required>
                                        <i class="fas fa-file-invoice-dollar input-icon"></i>
                                    </div>
                                    @error('tax_name')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Instagram field -->
                                <div class="form-group">
                                    <label for="ig" class="form-label">Usuario de Instagram</label>
                                    <div class="input-group">
                                        <input type="text" id="ig" name="ig" class="form-input @error('ig') is-invalid @enderror"
                                               value="{{ old('ig') }}" placeholder="@usuario_instagram">
                                        <i class="fab fa-instagram input-icon"></i>
                                    </div>
                                    @error('ig')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            Ubicación
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <!-- State -->
                                <div class="form-group">
                                    <label for="state" class="form-label">Estado / Provincia</label>
                                    <div class="input-group">
                                        <select name="state" id="state" class="form-select @error('state') is-invalid @enderror" required>
                                            <option value="">Seleccione estado</option>
                                        </select>
                                        <i class="fas fa-map input-icon"></i>
                                    </div>
                                    @error('state')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- City -->
                                <div class="form-group">
                                    <label for="city" class="form-label">Ciudad</label>
                                    <div class="input-group">
                                        <select name="city" id="city" class="form-select @error('city') is-invalid @enderror" required>
                                            <option value="">Seleccione ciudad</option>
                                        </select>
                                        <i class="fas fa-city input-icon"></i>
                                    </div>
                                    @error('city')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- Postal Code -->
                                <div class="form-group">
                                    <label for="postal_code" class="form-label">Código Postal</label>
                                    <div class="input-group">
                                        <input type="text" id="postal_code" name="postal_code" class="form-input @error('postal_code') is-invalid @enderror"
                                               value="{{ old('postal_code') }}" placeholder="Código postal" readonly>
                                        <i class="fas fa-mail-bulk input-icon"></i>
                                    </div>
                                    @error('postal_code')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="form-group">
                            <label for="address" class="form-label">Dirección Completa</label>
                            <div class="input-group">
                                <textarea id="address" name="address" class="form-textarea @error('address') is-invalid @enderror" 
                                          placeholder="Ingresa la dirección completa de tu empresa" required>{{ old('address') }}</textarea>
                                <i class="fas fa-map-marker-alt input-icon"></i>
                            </div>
                            @error('address')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Currency -->
                        <div class="form-group">
                            <label for="currency" class="form-label">Moneda de la Empresa</label>
                            <div class="input-group">
                                <input type="text" id="currency" name="currency" class="form-input @error('currency') is-invalid @enderror"
                                       value="{{ old('currency') }}" placeholder="Moneda" readonly required>
                                <i class="fas fa-coins input-icon"></i>
                            </div>
                            @error('currency')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Logo Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-image mr-2"></i>
                            Logo de la Empresa
                        </h3>
                        
                        <div class="file-upload-container" id="fileUploadContainer">
                            <div class="file-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text">
                                Arrastra y suelta tu logo aquí o haz clic para seleccionar
                            </div>
                            <input type="file" id="file" name="logo" class="file-input" accept="image/*">
                            <label for="file" class="file-label">
                                <i class="fas fa-upload mr-2"></i>
                                Seleccionar Archivo
                            </label>
                            <div class="preview-container" id="previewContainer"></div>
                        </div>
                        @error('logo')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="submitButton">
                            <span class="button-text">
                                <i class="fas fa-save mr-2"></i>
                                Crear Empresa
                            </span>
                            <div class="loading">
                                <div class="spinner"></div>
                            </div>
                            <div class="success-checkmark">
                                <i class="fas fa-check"></i>
                            </div>
                        </button>
                        
                        <button type="button" onclick="window.history.back()" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('vendor/config.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar SweetAlert2
            loadSweetAlert2(function() {
                const companyForm = document.getElementById('companyForm');
                const submitButton = document.getElementById('submitButton');
                const buttonText = submitButton.querySelector('.button-text');
                const loading = submitButton.querySelector('.loading');
                const successCheckmark = submitButton.querySelector('.success-checkmark');
                
                // File upload functionality
                const fileInput = document.getElementById('file');
                const fileUploadContainer = document.getElementById('fileUploadContainer');
                const previewContainer = document.getElementById('previewContainer');
                
                // Drag and drop functionality
                fileUploadContainer.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });
                
                fileUploadContainer.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                });
                
                fileUploadContainer.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        fileInput.files = files;
                        handleFileSelect(files[0]);
                    }
                });
                
                fileInput.addEventListener('change', function(e) {
                    if (this.files.length > 0) {
                        handleFileSelect(this.files[0]);
                    }
                });
                
                function handleFileSelect(file) {
                    if (!file.type.match('image.*')) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Por favor selecciona solo archivos de imagen'
                        });
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContainer.innerHTML = `
                            <img src="${e.target.result}" class="preview-image" alt="Preview">
                        `;
                    };
                    reader.readAsDataURL(file);
                }
                
                // Form submission with loading animation
                companyForm.addEventListener('submit', function(e) {
                    // Show loading state
                    buttonText.style.opacity = '0';
                    loading.classList.add('active');
                    submitButton.disabled = true;
                    
                    // Form will submit naturally
                });
                
                // Input focus effects
                const inputs = document.querySelectorAll('.form-input, .form-select, .form-textarea');
                inputs.forEach(input => {
                    input.addEventListener('focus', function() {
                        this.parentElement.style.transform = 'scale(1.02)';
                    });
                    
                    input.addEventListener('blur', function() {
                        this.parentElement.style.transform = 'scale(1)';
                    });
                });
                
                // Show notifications
                @if(session('error'))
                    Swal.fire({
                        title: '¡Error!',
                        text: '{{ session('error') }}',
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#d33',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: true,
                        background: '#fff',
                        showCloseButton: true,
                        timer: 10000,
                        timerProgressBar: true,
                        toast: false,
                        position: 'center'
                    });
                @endif

                @if(session('message'))
                    Swal.fire({
                        title: '¡Éxito!',
                        text: '{{ session('message') }}',
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#28a745',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        },
                        buttonsStyling: true,
                        background: '#fff',
                        showCloseButton: true,
                        timer: 10000,
                        timerProgressBar: true,
                        toast: false,
                        position: 'center'
                    });
                @endif
                
                console.log('SweetAlert2 cargado para companies create');
            });
        });

        // AJAX functionality for country/state/city
        $(document).ready(function() {
            $('#country').change(function() {
                var id_country = $(this).val();

                if (id_country) {
                    $.ajax({
                        url: "{{ route('admin.company.search_country', '') }}/" + id_country,
                        type: 'GET',
                        success: function(response) {
                            // Update state select
                            let stateSelect = $('#state');
                            stateSelect.empty().append('<option value="">Estado</option>');
                            
                            if(response.states && response.states.length > 0) {
                                response.states.forEach(function(state) {
                                    stateSelect.append('<option value="' + state.id + '">' + state.name + '</option>');
                                });
                            }
                            
                            // Update postal code and currency
                            $('input[name="postal_code"]').val(response.postal_code);
                            $('input[name="currency"]').val(response.currency_code);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al obtener estados:', error);
                            $('#state').empty().append('<option value="">Error al cargar estados</option>');
                        }
                    });
                } else {
                    $('#state').empty().append('<option value="">Estado</option>');
                    $('input[name="postal_code"]').val('');
                    $('input[name="currency"]').val('');
                }
            });

            // Handle state change
            $('#state').change(function() {
                var id_state = $(this).val();
                
                if (id_state) {
                    $.ajax({
                        url: "{{ route('admin.company.search_state', '') }}/" + id_state,
                        type: 'GET',
                        success: function(response) {
                            // Update cities
                            let citySelect = $('select[name="city"]');
                            citySelect.empty().append('<option value="">Ciudad</option>');
                            
                            if(response.cities && response.cities.length > 0) {
                                response.cities.forEach(function(city) {
                                    citySelect.append('<option value="' + city.id + '">' + city.name + '</option>');
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al obtener datos del estado:', error);
                            $('select[name="city"]').empty().append('<option value="">Error al cargar ciudades</option>');
                            $('input[name="postal_code"]').val('');
                        }
                    });
                } else {
                    $('select[name="city"]').empty().append('<option value="">Ciudad</option>');
                    $('input[name="postal_code"]').val('');
                }
            });
        });
    </script>
</body>
</html>
