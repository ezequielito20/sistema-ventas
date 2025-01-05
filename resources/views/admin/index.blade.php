@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="text-dark font-weight-bold">Dashboard</h1>
@stop

@section('content')
<div class="row">
    {{-- Widget de Usuarios --}}
    <div class="col-lg-3 col-12">
        <div class="small-box bg-info shadow zoomP">
            <div class="inner">
                <h3>{{ $usersCount }}</h3>
                <p>Usuarios Registrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                Ver usuarios <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Widget de Roles --}}
    <div class="col-lg-3 col-12">
        <div class="small-box bg-success shadow zoomP">
            <div class="inner">
                <h3>{{ $rolesCount }}</h3>
                <p>Roles del Sistema</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="small-box-footer">
                Ver roles <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- Gráficos o estadísticas adicionales --}}
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Distribución de Usuarios por Rol
                </h3>
            </div>
            <div class="card-body">
                <canvas id="usersByRoleChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-1"></i>
                    Usuarios Registrados por Mes
                </h3>
            </div>
            <div class="card-body">
                <canvas id="usersPerMonthChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box {
        transition: transform .3s;
    }
    
    .small-box:hover {
        transform: translateY(-5px);
    }

    .small-box .icon {
        transition: all .3s linear;
        position: absolute;
        top: 5px;
        right: 10px;
        z-index: 0;
        font-size: 70px;
        color: rgba(0,0,0,0.15);
    }

    .small-box:hover .icon {
        font-size: 75px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de usuarios por rol
    new Chart(document.getElementById('usersByRoleChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($usersByRole->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($usersByRole->pluck('count')) !!},
                backgroundColor: [
                    '#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Gráfico de usuarios por mes
    new Chart(document.getElementById('usersPerMonthChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($usersPerMonth->pluck('month')) !!},
            datasets: [{
                label: 'Usuarios Registrados',
                data: {!! json_encode($usersPerMonth->pluck('count')) !!},
                fill: false,
                borderColor: '#00a65a',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@stop