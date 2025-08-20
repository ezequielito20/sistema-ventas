<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pedidos Online - Sistema de Ventas</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap eliminado: usamos Tailwind -->
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    @livewireStyles

    <style>
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
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --border-radius: 20px;
            --border-radius-sm: 12px;
            --shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 20px 45px rgba(0, 0, 0, 0.15);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="2" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1.5" fill="white" opacity="0.08"/><circle cx="50" cy="10" r="1" fill="white" opacity="0.06"/><circle cx="10" cy="60" r="1" fill="white" opacity="0.08"/><circle cx="90" cy="40" r="1.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
            z-index: -1;
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .order-system-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
            opacity: 0.3;
        }

        .card-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }

        .card-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .header-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        .nav-tabs {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            padding: 0.5rem;
            margin: 0;
        }

        .nav-tabs .nav-link {
            background: transparent;
            border: none;
            color: #666;
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .nav-tabs .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            transition: var(--transition);
            z-index: -1;
        }

        .nav-tabs .nav-link.active,
        .nav-tabs .nav-link:hover {
            color: white;
            background: transparent;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .nav-tabs .nav-link.active::before,
        .nav-tabs .nav-link:hover::before {
            left: 0;
        }

        .tab-content {
            padding: 3rem 2rem;
            min-height: 500px;
        }

        .form-group {
            margin-bottom: 2rem;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            padding: 1rem 1.5rem;
            font-size: 1rem;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .input-group {
            position: relative;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            padding: 0.5rem;
            transition: var(--transition);
        }

        .quantity-controls:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .quantity-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .quantity-btn:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow);
        }

        .quantity-input {
            border: none;
            text-align: center;
            font-weight: 600;
            font-size: 1.2rem;
            width: 60px;
            background: transparent;
        }

        .quantity-input:focus {
            outline: none;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .total-display {
            background: var(--success-gradient);
            color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius-sm);
            text-align: center;
            margin: 2rem 0;
            position: relative;
            overflow: hidden;
        }

        .total-display::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="money" width="30" height="30" patternUnits="userSpaceOnUse"><text x="15" y="20" font-family="Arial" font-size="20" fill="white" opacity="0.1" text-anchor="middle">$</text></pattern></defs><rect width="100" height="100" fill="url(%23money)"/></svg>');
            opacity: 0.3;
        }

        .total-amount {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }

        .total-label {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .customer-info {
            background: rgba(102, 126, 234, 0.1);
            border-left: 4px solid var(--primary-color);
            padding: 1.5rem;
            border-radius: var(--border-radius-sm);
            margin: 1.5rem 0;
        }

        .customer-info h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .debt-result {
            background: white;
            border-radius: var(--border-radius-sm);
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-top: 2rem;
            text-align: center;
        }

        .debt-amount {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .debt-amount.has-debt {
            color: var(--danger-color);
        }

        .debt-amount.no-debt {
            color: var(--success-color);
        }

        .debt-info {
            background: rgba(245, 87, 108, 0.1);
            border-left: 4px solid var(--secondary-color);
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            margin-top: 1rem;
        }

        .success-message {
            background: var(--success-gradient);
            color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }

        .success-message::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="check" width="25" height="25" patternUnits="userSpaceOnUse"><text x="12.5" y="18" font-family="Arial" font-size="16" fill="white" opacity="0.1" text-anchor="middle">✓</text></pattern></defs><rect width="100" height="100" fill="url(%23check)"/></svg>');
            opacity: 0.3;
        }

        .success-icon {
            font-size: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .success-text {
            flex: 1;
            position: relative;
            z-index: 2;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            position: relative;
            z-index: 2;
        }

        .invalid-feedback {
            display: block;
            color: var(--danger-color);
            font-size: 0.9rem;
            margin-top: 0.5rem;
            font-weight: 500;
        }

        .is-invalid {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 3px rgba(250, 112, 154, 0.1);
        }

        .stock-info {
            background: rgba(67, 233, 123, 0.1);
            border: 1px solid rgba(67, 233, 123, 0.3);
            padding: 0.75rem;
            border-radius: var(--border-radius-sm);
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: var(--warning-color);
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

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

        .animate-fade-in {
            animation: fadeInUp 0.6s ease-out;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .card-header h1 {
                font-size: 2rem;
            }
            
            .tab-content {
                padding: 2rem 1rem;
            }
            
            .total-amount {
                font-size: 2rem;
            }
            
            .debt-amount {
                font-size: 2.5rem;
            }
            
            .nav-tabs .nav-link {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
        }

        /* Animations for tab switching */
        .tab-pane {
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s ease-in-out;
        }

        .tab-pane.active {
            opacity: 1;
            transform: translateX(0);
        }

        /* Pulse animation for notifications */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="order-system-card animate__animated animate__fadeInUp">
            <div class="card-header">
                <div class="header-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h1>Sistema de Pedidos</h1>
                <p>Haz tu pedido de manera fácil y rápida</p>
            </div>

            @livewire('order-system')
        </div>
    </div>

    <!-- Bootstrap JS eliminado -->
    
    @livewireScripts

    <script>
        // Auto-hide success message after 5 seconds
        document.addEventListener('livewire:init', () => {
            Livewire.on('hide-success-message', () => {
                setTimeout(() => {
                    Livewire.dispatch('closeSuccessMessage');
                }, 5000);
            });
        });

        // Smooth tab transitions
        document.addEventListener('DOMContentLoaded', function() {
            const tabTriggers = document.querySelectorAll('[data-bs-toggle="tab"]');
            
            tabTriggers.forEach(trigger => {
                trigger.addEventListener('shown.bs.tab', function(e) {
                    const targetPane = document.querySelector(e.target.getAttribute('data-bs-target'));
                    if (targetPane) {
                        targetPane.style.animation = 'fadeInUp 0.4s ease-out';
                    }
                });
            });
        });

        // Add loading state to buttons
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updating', ({ el, component, cleanup }) => {
                const submitBtn = el.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<span class="loading-spinner"></span> Enviando...';
                    submitBtn.disabled = true;
                }
            });

            Livewire.hook('morph.updated', ({ el, component }) => {
                const submitBtn = el.querySelector('button[type="submit"]');
                if (submitBtn && submitBtn.disabled) {
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Pedido';
                    submitBtn.disabled = false;
                }
            });
        });
    </script>
</body>
</html>