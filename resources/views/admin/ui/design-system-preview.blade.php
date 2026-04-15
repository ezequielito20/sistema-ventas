@extends('layouts.app')

@section('title', 'UI Design System Preview')

@section('content')
    <div class="space-y-6">
        <div class="ui-panel">
            <div class="ui-panel__header">
                <div>
                    <h1 class="ui-panel__title">Design System Futurista</h1>
                    <p class="ui-panel__subtitle">Base visual oficial para botones, widgets, tablas y paginacion.</p>
                </div>
            </div>
            <div class="ui-panel__body">
                <div class="flex flex-wrap gap-3">
                    <button class="ui-btn ui-btn-primary"><i class="fas fa-bolt"></i>Primario</button>
                    <button class="ui-btn ui-btn-success"><i class="fas fa-check"></i>Exito</button>
                    <button class="ui-btn ui-btn-warning"><i class="fas fa-triangle-exclamation"></i>Advertencia</button>
                    <button class="ui-btn ui-btn-danger"><i class="fas fa-trash"></i>Peligro</button>
                    <button class="ui-btn ui-btn-ghost"><i class="fas fa-sliders"></i>Secundario</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="ui-widget">
                <p class="ui-widget__label">Ingresos del dia</p>
                <p class="ui-widget__value">$3,482.12</p>
                <p class="ui-widget__meta">+12.4% vs ayer</p>
            </div>
            <div class="ui-widget">
                <p class="ui-widget__label">Ventas cerradas</p>
                <p class="ui-widget__value">142</p>
                <p class="ui-widget__meta">Objetivo: 130</p>
            </div>
            <div class="ui-widget">
                <p class="ui-widget__label">Clientes nuevos</p>
                <p class="ui-widget__value">24</p>
                <p class="ui-widget__meta">Conversion 17.4%</p>
            </div>
            <div class="ui-widget">
                <p class="ui-widget__label">Alertas criticas</p>
                <p class="ui-widget__value">03</p>
                <p class="ui-widget__meta">Monitoreo activo</p>
            </div>
        </div>

        <div class="ui-panel">
            <div class="ui-panel__header">
                <div>
                    <h2 class="ui-panel__title">Tabla y estados</h2>
                    <p class="ui-panel__subtitle">Estructura para listados estandar con mismo look premium.</p>
                </div>
            </div>
            <div class="ui-panel__body">
                <div class="ui-table-wrap">
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Modulo</th>
                                <th>Estado</th>
                                <th>Monto</th>
                                <th>Actualizado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Comercial Nova</td>
                                <td>Ventas</td>
                                <td><span class="ui-badge ui-badge-success">Activo</span></td>
                                <td>$1,240.00</td>
                                <td>Hace 2 min</td>
                            </tr>
                            <tr>
                                <td>Distribuidora Delta</td>
                                <td>Cobranzas</td>
                                <td><span class="ui-badge ui-badge-warning">Pendiente</span></td>
                                <td>$980.50</td>
                                <td>Hace 7 min</td>
                            </tr>
                            <tr>
                                <td>Farmacia Central</td>
                                <td>Pagos</td>
                                <td><span class="ui-badge ui-badge-danger">Bloqueado</span></td>
                                <td>$430.00</td>
                                <td>Hace 12 min</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="ui-panel">
            <div class="ui-panel__header">
                <div>
                    <h2 class="ui-panel__title">Paginacion base</h2>
                    <p class="ui-panel__subtitle">Componente visual para todos los index del sistema.</p>
                </div>
            </div>
            <div class="ui-panel__body">
                <div class="ui-pagination">
                    <a class="ui-page-link" href="#"><i class="fas fa-chevron-left"></i></a>
                    <a class="ui-page-link is-active" href="#">1</a>
                    <a class="ui-page-link" href="#">2</a>
                    <a class="ui-page-link" href="#">3</a>
                    <a class="ui-page-link" href="#">4</a>
                    <a class="ui-page-link" href="#"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>
@endsection
