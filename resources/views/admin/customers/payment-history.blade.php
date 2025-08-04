@extends('adminlte::page')

@section('title', 'Historial de Pagos')

@section('content_header')
    <div class="hero-section mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            <i class="fas fa-history-gradient"></i>
                            Historial de Pagos
                        </h1>
                        <p class="hero-subtitle">Registro histórico de todos los pagos de deudas realizados por los clientes</p>
                        <div class="hero-stats"></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <div class="hero-action-buttons d-flex justify-content-lg-end justify-content-center align-items-center gap-3 flex-wrap">
                        <a href="{{ route('admin.customers.index') }}" class="hero-btn hero-btn-secondary" data-toggle="tooltip" title="Volver a Clientes">
                            <i class="fas fa-arrow-left"></i>
                            <span class="d-none d-md-inline">Volver a Clientes</span>
                        </a>
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
    .hero-btn-secondary { color: #f5576c; }
    .hero-btn-secondary i { color: #f5576c; }
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
    {{-- Filtros con diseño moderno --}}
    <div class="exchange-filters-card mb-4">
        <div class="exchange-filters-content redesigned">
            <div class="filters-block redesigned-right">
                <form id="filterForm" method="GET">
                    <div class="filters-search-row">
                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fas fa-user"></i>
                                Cliente
                            </label>
                            <select class="form-control select2-modern" id="customer_filter" name="customer_id">
                                <option value="">Todos los clientes</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fas fa-calendar-alt"></i>
                                Fecha desde
                            </label>
                            <input type="date" class="form-control input-modern" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fas fa-calendar-check"></i>
                                Fecha hasta
                            </label>
                            <input type="date" class="form-control input-modern" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary modern-btn">
                                <i class="fas fa-search"></i>
                                <span>Filtrar</span>
                            </button>
                            <a href="{{ route('admin.customers.payment-history') }}" class="btn btn-secondary modern-btn">
                                <i class="fas fa-undo"></i>
                                <span>Reiniciar</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Estadísticas con diseño de tarjetas modernas --}}
    <div class="stats-grid mb-4">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $currency->symbol }} {{ number_format($totalPayments, 2) }}</h3>
                </div>
                <p class="stat-label">Total Pagos Recibidos</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
            </div>
        </div>
        
        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $paymentsCount }}</h3>
                </div>
                <p class="stat-label">Número de Pagos</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
            </div>
        </div>
        
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $currency->symbol }} {{ number_format($averagePayment, 2) }}</h3>
                </div>
                <p class="stat-label">Pago Promedio</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
            </div>
        </div>
        
        <div class="stat-card stat-card-purple">
            <div class="stat-icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $currency->symbol }} {{ number_format($totalRemainingDebt, 2) }}</h3>
                </div>
                <p class="stat-label">Deuda Total Restante</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Historial con diseño moderno --}}
    <div class="customers-container">
        {{-- Vista de tabla para pantallas grandes --}}
        <div class="table-view d-none d-lg-block">
            <div class="table-container">
                <table id="paymentsTable" class="customers-table">
                    <thead>
                        <tr>
                            <th class="th-customer">Fecha</th>
                            <th class="th-contact">Cliente</th>
                            <th class="th-id">Deuda Anterior</th>
                            <th class="th-sales">Monto Pagado</th>
                            <th class="th-debt">Deuda Restante</th>
                            <th class="th-debt-bs">Registrado por</th>
                            <th class="th-status">Notas</th>
                            <th class="th-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr class="customer-row">
                                <td class="td-customer">
                                    <div class="customer-info">
                                        <div class="customer-avatar">
                                            <div class="avatar-circle">
                                                <i class="fas fa-calendar"></i>
                                            </div>
                                        </div>
                                        <div class="customer-details">
                                            <div class="customer-name">{{ $payment->created_at->format('d/m/Y') }}</div>
                                            <div class="customer-email">
                                                <i class="fas fa-clock"></i>
                                                {{ $payment->created_at->format('H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="td-contact">
                                    <div class="contact-info">
                                        <i class="fas fa-user"></i>
                                        <span>{{ $payment->customer->name }}</span>
                                    </div>
                                </td>
                                <td class="td-id">
                                    <span class="id-badge debt-badge">{{ $currency->symbol }} {{ number_format($payment->previous_debt, 2) }}</span>
                                </td>
                                <td class="td-sales">
                                    <div class="sales-info">
                                        <div class="sales-amount payment-amount">{{ $currency->symbol }} {{ number_format($payment->payment_amount, 2) }}</div>
                                        <div class="sales-count">Pago registrado</div>
                                    </div>
                                </td>
                                <td class="td-debt">
                                    <div class="debt-info">
                                        <div class="debt-amount debt-value">
                                            {{ $currency->symbol }} {{ number_format($payment->remaining_debt, 2) }}
                                        </div>
                                        <div class="debt-status">Deuda restante</div>
                                    </div>
                                </td>
                                <td class="td-debt-bs">
                                    <div class="user-info">
                                        <i class="fas fa-user-cog"></i>
                                        <span>{{ $payment->user->name }}</span>
                                    </div>
                                </td>
                                <td class="td-status">
                                    <span class="status-badge status-active">
                                        <i class="fas fa-sticky-note"></i>
                                        {{ $payment->notes ?? 'Sin notas' }}
                                    </span>
                                </td>
                                <td class="td-actions">
                                    <div class="action-buttons">
                                        <button class="action-btn action-btn-delete delete-payment" 
                                                data-payment-id="{{ $payment->id }}"
                                                data-customer-name="{{ $payment->customer->name }}"
                                                data-payment-amount="{{ $payment->payment_amount }}"
                                                data-customer-id="{{ $payment->customer_id }}"
                                                data-toggle="tooltip" title="Eliminar Pago">
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

        {{-- Vista de tarjetas para móviles --}}
        <div class="cards-view d-lg-none">
            <div class="cards-container" id="mobilePaymentsContainer">
                @foreach($payments as $payment)
                    <div class="payment-card">
                        <div class="card-header">
                            <div class="payment-avatar">
                                <div class="avatar-circle">
                                    <i class="fas fa-calendar"></i>
                                </div>
                            </div>
                            <div class="payment-info">
                                <h6 class="payment-date">{{ $payment->created_at->format('d/m/Y') }}</h6>
                                <div class="payment-time">
                                    <i class="fas fa-clock"></i>
                                    {{ $payment->created_at->format('H:i') }}
                                </div>
                            </div>
                            <div class="payment-amount-display">
                                <span class="amount-badge payment-badge">
                                    {{ $currency->symbol }} {{ number_format($payment->payment_amount, 2) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-user"></i>
                                        Cliente
                                    </div>
                                    <div class="info-value">{{ $payment->customer->name }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-arrow-up text-danger"></i>
                                        Deuda Anterior
                                    </div>
                                    <div class="info-value debt-value">
                                        {{ $currency->symbol }} {{ number_format($payment->previous_debt, 2) }}
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-balance-scale"></i>
                                        Deuda Restante
                                    </div>
                                    <div class="info-value debt-remaining">
                                        {{ $currency->symbol }} {{ number_format($payment->remaining_debt, 2) }}
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-user-cog"></i>
                                        Registrado por
                                    </div>
                                    <div class="info-value">{{ $payment->user->name }}</div>
                                </div>
                                <div class="info-item full-width">
                                    <div class="info-label">
                                        <i class="fas fa-sticky-note"></i>
                                        Notas
                                    </div>
                                    <div class="info-value notes-value">
                                        {{ $payment->notes ?? 'Sin notas' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-actions">
                            <div class="action-buttons">
                                <button class="action-btn action-btn-delete delete-payment" 
                                        data-payment-id="{{ $payment->id }}"
                                        data-customer-name="{{ $payment->customer->name }}"
                                        data-payment-amount="{{ $payment->payment_amount }}"
                                        data-customer-id="{{ $payment->customer_id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <div class="pagination-container">
            <div class="pagination-info">
                Mostrando {{ $payments->firstItem() ?? 0 }} a {{ $payments->lastItem() ?? 0 }} de {{ $payments->total() }} registros
            </div>
            <div class="pagination-wrapper">
                {{ $payments->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- Gráficos con diseño moderno --}}
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Pagos por Día de la Semana
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="weekdayChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        Pagos por Mes
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet" />
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

        /* Header hero */
        .hero-header {
            position: relative;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #e2e8f0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .title-icon {
            font-size: 3rem;
            animation: bounce 2s infinite;
        }

        .hero-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            margin: 0.5rem 0 0 0;
        }

        .btn-glass {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        /* Decoraciones flotantes */
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .shape-1 {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 60px;
            height: 60px;
            top: 60%;
            right: 20%;
            animation-delay: 2s;
        }

        .shape-3 {
            width: 40px;
            height: 40px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape-4 {
            width: 100px;
            height: 100px;
            top: 30%;
            right: 10%;
            animation-delay: 1s;
        }

        /* Filtros */
        .filter-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-medium);
        }

        .filter-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .filter-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .filter-header h3 {
            color: white;
            margin: 0;
            font-weight: 600;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .filter-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .select2-modern, .input-modern {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .select2-modern:focus, .input-modern:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
            outline: none;
        }

        .select2-modern option {
            background: var(--dark-color);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn-modern {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            border: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        /* Estadísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-heavy);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .stat-card:hover::before {
            opacity: 0.1;
        }

        .stat-primary::before { background: var(--gradient-primary); }
        .stat-success::before { background: var(--gradient-success); }
        .stat-warning::before { background: var(--gradient-warning); }
        .stat-danger::before { background: var(--gradient-danger); }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stat-primary .stat-icon { background: var(--gradient-primary); }
        .stat-success .stat-icon { background: var(--gradient-success); }
        .stat-warning .stat-icon { background: var(--gradient-warning); }
        .stat-danger .stat-icon { background: var(--gradient-danger); }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        /* Tabla moderna */
        .table-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-medium);
        }

        .table-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            color: white;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .search-box input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            color: white;
            width: 300px;
        }

        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .table-modern {
            width: 100%;
            border-collapse: collapse;
        }

        .th-modern {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .td-modern {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.9);
        }

        .table-row-modern {
            transition: all 0.3s ease;
        }

        .table-row-modern:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Celdas especializadas */
        .date-cell {
            display: flex;
            flex-direction: column;
        }

        .date-main {
            font-weight: 600;
            color: white;
        }

        .date-time {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .customer-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .customer-name {
            font-weight: 600;
            color: white;
        }

        .customer-email {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .amount-cell {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            width: fit-content;
        }

        .amount-debt {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .amount-payment {
            background: rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
        }

        .amount-remaining {
            background: rgba(245, 158, 11, 0.2);
            color: #fcd34d;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background: var(--gradient-success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
        }

        .notes-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .actions-cell {
            display: flex;
            justify-content: center;
        }

        .btn-action {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 10px;
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
        }

        .btn-action:hover {
            background: rgba(239, 68, 68, 0.4);
            transform: scale(1.1);
        }

        .tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--dark-color);
            color: white;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .btn-action:hover .tooltip {
            opacity: 1;
        }

        /* Paginación */
        .pagination-container {
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: center;
        }

        .pagination {
            display: flex;
            gap: 0.5rem;
        }

        .page-link {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
        }

        .page-item.active .page-link {
            background: var(--gradient-primary);
            border-color: transparent;
        }

        /* Gráficos */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 1.5rem;
        }

        .chart-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-medium);
        }

        .chart-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .chart-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .chart-header h3 {
            color: white;
            margin: 0;
            font-weight: 600;
        }

        .chart-body {
            padding: 2rem;
        }

        /* Animaciones */
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .hero-title {
                font-size: 2rem;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .table-header {
                flex-direction: column;
                gap: 1rem;
            }

            .search-box input {
                width: 100%;
            }
        }

        /* Ocultar elementos de DataTables */
        .dataTables_wrapper .dataTables_paginate,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            display: none !important;
        }

        /* Asegurar que la paginación de Laravel sea visible */
        .pagination {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Estilos adicionales para la paginación de Laravel */
        .pagination .page-item {
            list-style: none;
            margin: 0;
        }

        .pagination .page-link {
            position: relative;
            display: block;
            text-decoration: none;
        }

        /* Mejorar la apariencia de los números de página */
        .pagination .page-item:not(.active):not(.disabled) .page-link {
            background: white;
            color: var(--dark-color);
        }

        .pagination .page-item:not(.active):not(.disabled) .page-link:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Estilos para el texto de información de paginación */
        .pagination-info {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        /* ===== ESTILOS ESPECÍFICOS PARA PAYMENT HISTORY ===== */
        
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

        /* ===== EXCHANGE RATE CARD ===== */
        .exchange-filters-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem 2rem 1.5rem 2rem;
            margin-bottom: 2rem;
        }

        .exchange-filters-content {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .filters-block.redesigned-right {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            flex: 2 1 500px;
            min-width: 260px;
            max-width: 700px;
            padding-left: 2rem;
        }

        .filters-search-row {
            display: flex;
            align-items: center;
            gap: 1.1rem;
            width: 100%;
            justify-content: flex-start;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex: 1;
            min-width: 0;
        }

        .filter-group:first-child {
            flex: 1.5;
            min-width: 200px;
        }

        .filter-label {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .select2-modern, .input-modern {
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            transition: var(--transition);
            width: 100%;
        }

        .select2-modern:focus, .input-modern:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Estilos para Select2 */
        .select2-container--bootstrap4 {
            width: 100% !important;
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: auto;
            min-height: 45px;
            display: flex;
            align-items: center;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
        }

        .select2-container--bootstrap4 .select2-selection--single:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            color: #495057;
        }

        .select2-container--bootstrap4 .select2-selection__arrow {
            height: 100%;
            right: 1rem;
        }

        .select2-dropdown {
            border: 2px solid var(--primary-color);
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow);
        }

        .select2-results__option {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }

        .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary-color);
        }

        .filter-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .modern-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-primary.modern-btn {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-secondary.modern-btn {
            background: #6c757d;
            color: white;
        }

        .modern-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* ===== STATS GRID ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stat-card-primary::before { background: var(--primary-gradient); }
        .stat-card-success::before { background: var(--success-gradient); }
        .stat-card-warning::before { background: var(--warning-gradient); }
        .stat-card-purple::before { background: var(--purple-gradient); }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stat-card-primary .stat-icon { background: var(--primary-gradient); }
        .stat-card-success .stat-icon { background: var(--success-gradient); }
        .stat-card-warning .stat-icon { background: var(--warning-gradient); }
        .stat-card-purple .stat-icon { background: var(--purple-gradient); }

        .stat-content {
            position: relative;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .stat-progress {
            height: 4px;
            background: #f0f0f0;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: var(--primary-gradient);
            border-radius: 2px;
            transition: width 1s ease-in-out;
        }

        /* ===== CUSTOMERS CONTAINER ===== */
        .customers-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* ===== TABLE VIEW ===== */
        .table-container {
            overflow-x: auto;
        }

        .customers-table {
            width: 100%;
            border-collapse: collapse;
        }

        .customers-table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: 2px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .customers-table td {
            padding: 1rem;
            border-bottom: 1px solid #f8f9fa;
            vertical-align: middle;
        }

        .customers-table tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .customer-row {
            transition: var(--transition);
        }

        .customer-row:hover {
            transform: scale(1.01);
            box-shadow: var(--shadow);
        }

        /* Customer Info */
        .customer-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .customer-avatar {
            flex-shrink: 0;
        }

        .avatar-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .avatar-circle:hover {
            transform: scale(1.1);
        }

        .customer-details {
            flex: 1;
        }

        .customer-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }

        .customer-email {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Contact Info */
        .contact-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
        }

        /* ID Badge */
        .id-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .debt-badge {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        /* Sales Info */
        .sales-info {
            text-align: center;
        }

        .sales-amount {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }

        .payment-amount {
            color: #28a745;
        }

        .sales-count {
            color: #666;
            font-size: 0.8rem;
        }

        /* Debt Info */
        .debt-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .debt-amount {
            font-weight: 600;
            color: #dc3545;
        }

        .debt-status {
            font-size: 0.8rem;
            color: #666;
        }

        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
        }

        /* Status Badge */
        .status-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            font-size: 0.9rem;
        }

        .action-btn-delete {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .action-btn:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-hover);
        }

        /* ===== CARDS VIEW (MOBILE) ===== */
        .cards-container {
            padding: 1.5rem;
            display: grid;
            gap: 1.5rem;
        }

        .payment-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }

        .payment-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .payment-card .card-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid #f8f9fa;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .payment-avatar {
            flex-shrink: 0;
        }

        .payment-info {
            flex: 1;
        }

        .payment-date {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }

        .payment-time {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-amount-display {
            flex-shrink: 0;
        }

        .amount-badge {
            background: var(--success-gradient);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .payment-badge {
            background: var(--success-gradient);
        }

        .payment-card .card-body {
            padding: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-item.full-width {
            grid-column: 1 / -1;
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
            color: var(--dark-color);
        }

        .debt-value {
            color: #dc3545;
        }

        .debt-remaining {
            color: #fd7e14;
        }

        .notes-value {
            font-weight: normal;
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .payment-card .card-actions {
            padding: 1.5rem;
            border-top: 1px solid #f8f9fa;
            background: #f8f9fa;
        }

        .payment-card .action-buttons {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
        }

        .payment-card .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            min-width: 120px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .payment-card .action-btn-delete {
            background: var(--danger-gradient);
            color: white;
            border: none;
            font-weight: 600;
        }

        .payment-card .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
            filter: brightness(1.1);
        }

        .payment-card .action-btn:active {
            transform: translateY(0);
        }

        .payment-card .action-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .payment-card .action-btn:disabled:hover {
            transform: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            filter: none;
        }

        /* Responsive para tarjetas */
        @media (max-width: 576px) {
            .cards-container {
                padding: 1rem;
                gap: 1rem;
            }

            .payment-card .card-header {
                padding: 1rem;
            }

            .payment-card .card-body {
                padding: 1rem;
            }

            .payment-card .card-actions {
                padding: 1rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .payment-card .action-btn {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
                min-width: 100px;
            }

            .payment-card .action-btn span {
                font-size: 0.85rem;
            }

            .payment-card .action-btn i {
                font-size: 0.9rem;
            }
        }

        /* Pagination */
        .pagination-container {
            padding: 1.5rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            gap: 1rem;
        }

        .pagination-wrapper {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .pagination {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        .page-item {
            margin: 0;
        }

        .page-link {
            background: white;
            border: 2px solid #e9ecef;
            color: var(--dark-color);
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            min-width: 44px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-link:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .page-item.active .page-link {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
            box-shadow: var(--shadow);
        }

        .page-item.disabled .page-link {
            background: #f8f9fa;
            border-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .page-item.disabled .page-link:hover {
            background: #f8f9fa;
            border-color: #e9ecef;
            color: #6c757d;
            transform: none;
            box-shadow: none;
        }

        /* Estilos específicos para los botones de navegación */
        .page-item:first-child .page-link,
        .page-item:last-child .page-link {
            font-weight: 600;
        }

        /* Responsive para paginación */
        @media (max-width: 576px) {
            .pagination-container {
                padding: 1rem;
                gap: 0.75rem;
            }

            .pagination-info {
                font-size: 0.8rem;
                text-align: center;
                line-height: 1.4;
            }

            .pagination {
                gap: 0.3rem;
                flex-wrap: wrap;
            }

            .page-link {
                padding: 0.6rem 0.8rem;
                font-size: 0.9rem;
                min-width: 40px;
            }

            /* Ocultar algunos números de página en móviles para ahorrar espacio */
            .pagination .page-item:not(.active):not(:first-child):not(:last-child):not(.disabled) {
                display: none;
            }

            .pagination .page-item.active,
            .pagination .page-item:first-child,
            .pagination .page-item:last-child,
            .pagination .page-item.disabled {
                display: block;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 1.5rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .filters-search-row {
                flex-direction: column;
                gap: 1rem;
            }

            .filter-group {
                width: 100%;
                min-width: 100%;
            }

            .filter-group:first-child {
                flex: 1;
                min-width: 100%;
            }

            .filter-actions {
                justify-content: center;
                width: 100%;
            }

            .filter-actions .modern-btn {
                flex: 1;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .exchange-filters-card {
                padding: 1.5rem 1rem;
            }

            .filters-block.redesigned-right {
                padding-left: 0;
                max-width: 100%;
            }

            .filters-search-row {
                gap: 0.75rem;
            }

            .filter-group {
                gap: 0.4rem;
            }

            .filter-label {
                font-size: 0.85rem;
            }

            .select2-modern, .input-modern {
                padding: 0.6rem 0.8rem;
                font-size: 0.9rem;
            }

            .modern-btn {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            .modern-btn span {
                font-size: 0.9rem;
            }

            /* Select2 responsive para móviles */
            .select2-container--bootstrap4 {
                width: 100% !important;
                min-width: 100% !important;
            }

            .select2-container--bootstrap4 .select2-selection--single {
                min-height: 42px;
                padding: 0.5rem 0.8rem;
            }

            .select2-container--bootstrap4 .select2-selection__rendered {
                padding: 0.5rem 0.8rem;
                font-size: 0.9rem;
                line-height: 1.2;
            }

            .select2-container--bootstrap4 .select2-selection__arrow {
                right: 0.8rem;
            }

            .select2-dropdown {
                font-size: 0.9rem;
            }

            .select2-results__option {
                padding: 0.6rem 0.8rem;
                font-size: 0.9rem;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable con configuración moderna
            // Los pagos ya vienen ordenados del más reciente al más antiguo desde el servidor
            $('#paymentsTable').DataTable({
                responsive: true,
                autoWidth: false,
                paging: false,
                info: false,
                searching: false, // Usamos nuestro propio buscador
                ordering: false, // Deshabilitar ordenamiento del DataTable para respetar el orden del servidor
                language: {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    },
                    "buttons": {
                        "copy": "Copiar",
                        "colvis": "Visibilidad"
                    }
                },
                dom: 'rt',
                initComplete: function() {
                    $('.pagination').show();
                }
            });

            // Inicializar Select2
            $('.select2-modern').select2({
                theme: 'bootstrap4',
                dropdownParent: $('body')
            });

            // Inicializar gráficos con colores modernos
            if (document.getElementById('weekdayChart')) {
                const weekdayCtx = document.getElementById('weekdayChart').getContext('2d');
                const weekdayChart = new Chart(weekdayCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($weekdayLabels) !!},
                        datasets: [{
                            label: 'Pagos por día de la semana',
                            data: {!! json_encode($weekdayData) !!},
                            backgroundColor: 'rgba(102, 126, 234, 0.6)',
                            borderColor: 'rgba(102, 126, 234, 1)',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: 'rgba(255, 255, 255, 0.8)',
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(31, 41, 55, 0.9)',
                                titleColor: 'white',
                                bodyColor: 'white',
                                borderColor: 'rgba(255, 255, 255, 0.2)',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        return '{{ $currency->symbol }} ' + context.raw.toFixed(2);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.8)'
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.8)',
                                    callback: function(value) {
                                        return '{{ $currency->symbol }} ' + value.toFixed(2);
                                    }
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                }
                            }
                        }
                    }
                });
            }

            if (document.getElementById('monthlyChart')) {
                const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
                const monthlyChart = new Chart(monthlyCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($monthlyLabels) !!},
                        datasets: [{
                            label: 'Pagos por mes',
                            data: {!! json_encode($monthlyData) !!},
                            backgroundColor: 'rgba(16, 185, 129, 0.2)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 3,
                            tension: 0.4,
                            pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                            pointBorderColor: 'white',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: 'rgba(255, 255, 255, 0.8)',
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(31, 41, 55, 0.9)',
                                titleColor: 'white',
                                bodyColor: 'white',
                                borderColor: 'rgba(255, 255, 255, 0.2)',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        return '{{ $currency->symbol }} ' + context.raw.toFixed(2);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.8)'
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.8)',
                                    callback: function(value) {
                                        return '{{ $currency->symbol }} ' + value.toFixed(2);
                                    }
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                }
                            }
                        }
                    }
                });
            }

            // Animaciones para las tarjetas de estadísticas
            $('.stat-card').each(function(index) {
                $(this).css({
                    'animation-delay': (index * 0.1) + 's'
                });
            });

            // Manejar la eliminación de pagos con diseño moderno
            $(document).on('click', '.delete-payment', function() {
                const paymentId = $(this).data('payment-id');
                const customerName = $(this).data('customer-name');
                const paymentAmount = $(this).data('payment-amount');
                const $button = $(this);

                Swal.fire({
                    title: '¿Estás seguro?',
                    html: `
                        <div style="text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🗑️</div>
                            <p>Vas a eliminar el pago de <strong style="color: #10b981;">${paymentAmount} {{ $currency->symbol }}</strong></p>
                            <p>del cliente <strong style="color: #667eea;">${customerName}</strong></p>
                            <p style="color: #ef4444; font-weight: 600;">Esta acción restaurará la deuda al cliente.</p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        popup: 'swal-modern',
                        confirmButton: 'btn btn-danger btn-modern',
                        cancelButton: 'btn btn-secondary btn-modern'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostrar estado de carga en el botón
                        const originalText = $button.html();
                        $button.html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');
                        $button.prop('disabled', true);

                        Swal.fire({
                            title: 'Eliminando pago...',
                            html: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>',
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });

                        $.ajax({
                            url: `/admin/customers/payment-history/${paymentId}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                // Eliminar la fila de la tabla o la tarjeta
                                const $row = $button.closest('tr');
                                const $card = $button.closest('.payment-card');
                                
                                if ($row.length > 0) {
                                    $row.fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                } else if ($card.length > 0) {
                                    $card.fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                }

                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Pago eliminado!',
                                    text: 'El pago ha sido eliminado y la deuda ha sido restaurada',
                                    confirmButtonText: 'Aceptar',
                                    customClass: {
                                        popup: 'swal-modern'
                                    }
                                });

                                if (response.statistics) {
                                    $('.stat-value').each(function() {
                                        $(this).addClass('animate-value');
                                    });
                                }
                            },
                            error: function(xhr) {
                                // Restaurar el botón
                                $button.html(originalText);
                                $button.prop('disabled', false);

                                let errorMessage = 'Ha ocurrido un error al eliminar el pago';
                                
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMessage,
                                    confirmButtonText: 'Aceptar',
                                    customClass: {
                                        popup: 'swal-modern'
                                    }
                                });
                            }
                        });
                    }
                });
            });

            // Efectos hover adicionales
            $('.stat-card').hover(
                function() {
                    $(this).find('.stat-icon').addClass('pulse');
                },
                function() {
                    $(this).find('.stat-icon').removeClass('pulse');
                }
            );
        });
    </script>
@stop 