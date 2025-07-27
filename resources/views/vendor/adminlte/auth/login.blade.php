<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Iniciar Sesión</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    
    <!-- AdminLTE CSS (includes Bootstrap) -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
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
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.2) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(1deg); }
            66% { transform: translateY(10px) rotate(-1deg); }
        }

        /* Floating Elements */
        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: floatElement 15s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 20%;
            right: 15%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .floating-element:nth-child(4) {
            width: 100px;
            height: 100px;
            bottom: 10%;
            right: 10%;
            animation-delay: 6s;
        }

        @keyframes floatElement {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
            50% { transform: translateY(-30px) rotate(180deg); opacity: 1; }
        }

        /* Main Container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-strong);
            border: 1px solid var(--glass-border);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 600px;
            position: relative;
        }

        /* Left Side - Branding */
        .branding-side {
            background: var(--primary-gradient);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .branding-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .logo-container {
            position: relative;
            z-index: 2;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .logo-icon i {
            font-size: 36px;
            color: white;
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .brand-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
            margin-bottom: 40px;
        }

        .features-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .features-list li {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }

        .features-list li i {
            margin-right: 12px;
            font-size: 16px;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Right Side - Login Form */
        .form-side {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .form-subtitle {
            color: #7f8c8d;
            font-size: 1rem;
            font-weight: 400;
        }

        .login-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            color: #2c3e50;
            background: #f8f9fa;
            transition: var(--transition);
            outline: none;
        }

        .form-input:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .form-input::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            font-size: 18px;
            transition: var(--transition);
            z-index: 2;
        }

        .form-input:focus + .input-icon {
            color: #667eea;
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

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .checkbox-wrapper {
            position: relative;
            display: inline-block;
        }

        .checkbox-input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkbox-custom {
            height: 20px;
            width: 20px;
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            display: inline-block;
            position: relative;
            transition: var(--transition);
            cursor: pointer;
        }

        .checkbox-input:checked ~ .checkbox-custom {
            background-color: #667eea;
            border-color: #667eea;
        }

        .checkbox-input:checked ~ .checkbox-custom::after {
            content: '';
            position: absolute;
            left: 6px;
            top: 2px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .checkbox-label {
            margin-left: 12px;
            color: #2c3e50;
            font-weight: 500;
            cursor: pointer;
            user-select: none;
        }

        .login-button {
            width: 100%;
            padding: 16px;
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .login-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .login-button:hover::before {
            left: 100%;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .form-footer {
            text-align: center;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .footer-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .footer-link:hover {
            color: #5a6fd8;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .footer-link i {
            margin-right: 8px;
            font-size: 14px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-card {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .branding-side {
                padding: 40px 30px;
                order: 2;
            }

            .form-side {
                padding: 40px 30px;
                order: 1;
            }

            .brand-title {
                font-size: 2rem;
            }

            .form-title {
                font-size: 1.75rem;
            }

            .floating-elements {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 10px;
            }

            .branding-side,
            .form-side {
                padding: 30px 20px;
            }

            .brand-title {
                font-size: 1.75rem;
            }

            .form-title {
                font-size: 1.5rem;
            }
        }

        /* Loading Animation */
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

    <div class="login-container">
        <div class="login-card">
            <!-- Left Side - Branding -->
            <div class="branding-side">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h1 class="brand-title">Sistema Ventas</h1>
                    <p class="brand-subtitle">Control total de tu negocio</p>
                </div>
                
                <ul class="features-list">
                    <li>
                        <i class="fas fa-chart-bar"></i>
                        Gestión completa de ventas y compras
                    </li>
                    <li>
                        <i class="fas fa-users"></i>
                        Control de clientes y proveedores
                    </li>
                    <li>
                        <i class="fas fa-boxes"></i>
                        Inventario en tiempo real
                    </li>
                    <li>
                        <i class="fas fa-cash-register"></i>
                        Arqueo de caja automatizado
                    </li>
                    <li>
                        <i class="fas fa-chart-pie"></i>
                        Reportes detallados y análisis
                    </li>
                </ul>
            </div>

            <!-- Right Side - Login Form -->
            <div class="form-side">
                <div class="form-header">
                    <h2 class="form-title">¡Bienvenido de vuelta!</h2>
                    <p class="form-subtitle">Ingresa tus credenciales para continuar</p>
                </div>

                <form action="{{ route('login') }}" method="POST" class="login-form" id="loginForm" autocomplete="on" novalidate>
                    @csrf

                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <div class="input-group">
                            <input type="email" 
                                   id="email"
                                   name="email" 
                                   class="form-input @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" 
                                   placeholder="tu@email.com" 
                                   autocomplete="username"
                                   autocapitalize="none"
                                   spellcheck="false"
                                   autofocus
                                   required>
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                        @error('email')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                Correo electrónico incorrecto
                            </div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input type="password" 
                                   id="password"
                                   name="password" 
                                   class="form-input @error('password') is-invalid @enderror"
                                   placeholder="Tu contraseña"
                                   autocomplete="current-password"
                                   autocapitalize="none"
                                   spellcheck="false"
                                   data-lpignore="true"
                                   required>
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                        @error('password')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                Contraseña incorrecta
                            </div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="remember-me">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" name="remember" id="remember" class="checkbox-input" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember" class="checkbox-custom"></label>
                        </div>
                        <label for="remember" class="checkbox-label">Recordarme</label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="login-button" id="loginButton">
                        <span class="button-text">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Iniciar sesión
                        </span>
                        <div class="loading">
                            <div class="spinner"></div>
                        </div>
                        <div class="success-checkmark">
                            <i class="fas fa-check"></i>
                        </div>
                    </button>
                </form>

                <!-- Form Footer -->
                <div class="form-footer">
                    <div class="footer-links">
                        @if(route('password.request'))
                            <a href="{{ route('password.request') }}" class="footer-link">
                                <i class="fas fa-key"></i>
                                Recuperar contraseña
                            </a>
                        @endif
                        
                        @if(route('admin.company.create'))
                            <a href="{{ route('admin.company.create') }}" class="footer-link">
                                <i class="fas fa-building"></i>
                                Crear una Empresa
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            const buttonText = loginButton.querySelector('.button-text');
            const loading = loginButton.querySelector('.loading');
            const successCheckmark = loginButton.querySelector('.success-checkmark');
            
            // Password field security
            const passwordField = document.querySelector('input[name="password"]');
            const emailField = document.querySelector('input[name="email"]');
            
            // Clear password field on load
            if (passwordField) {
                passwordField.value = '';
                
                setTimeout(function() {
                    passwordField.value = '';
                }, 100);
                
                // Clear when focused
                passwordField.addEventListener('focus', function() {
                    this.value = '';
                });
                
                // Prevent paste in plain text
                passwordField.addEventListener('paste', function(e) {
                    setTimeout(function() {
                        passwordField.type = 'password';
                    }, 1);
                });
                
                // Prevent context menu
                passwordField.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    return false;
                });
            }
            
            // Form submission with loading animation
            loginForm.addEventListener('submit', function(e) {
                const initialAmount = parseFloat(document.getElementById('initial_amount')?.value || 0);
                
                if (initialAmount < 0) {
                    e.preventDefault();
                    showNotification('error', 'Error', 'El monto inicial no puede ser negativo');
                    return;
                }
                
                // Show loading state
                buttonText.style.opacity = '0';
                loading.classList.add('active');
                loginButton.disabled = true;
                
                // Simulate loading time (remove in production)
                setTimeout(() => {
                    loading.classList.remove('active');
                    successCheckmark.classList.add('active');
                    
                    setTimeout(() => {
                        // Form will submit naturally
                    }, 500);
                }, 1000);
            });
            
            // Input focus effects
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
            
            // Checkbox animation
            const checkboxes = document.querySelectorAll('.checkbox-input');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const customCheckbox = this.nextElementSibling;
                    if (this.checked) {
                        customCheckbox.style.transform = 'scale(1.1)';
                        setTimeout(() => {
                            customCheckbox.style.transform = 'scale(1)';
                        }, 150);
                    }
                });
            });
            
            // Notification function
            function showNotification(type, title, message) {
                // You can implement a custom notification system here
                alert(`${title}: ${message}`);
            }
            
            // Add some interactive effects
            document.addEventListener('mousemove', function(e) {
                const cards = document.querySelectorAll('.floating-element');
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;
                
                cards.forEach((card, index) => {
                    const speed = (index + 1) * 0.5;
                    const xOffset = (x - 0.5) * speed;
                    const yOffset = (y - 0.5) * speed;
                    
                    card.style.transform = `translate(${xOffset}px, ${yOffset}px)`;
                });
            });
        });
    </script>
</body>
</html>
