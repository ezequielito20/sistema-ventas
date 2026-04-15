{{--
    Vista de prueba del shell (fondo + sidebar + topbar).
    Revisa /ui/shell-preview con sesión iniciada y aprueba o pide ajustes antes de generalizar.
--}}
@extends('layouts.app')

@section('title', 'Vista previa — Shell UI')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="ui-panel">
            <div class="ui-panel__header">
                <div>
                    <h1 class="ui-panel__title">Plantilla base (prueba)</h1>
                    <p class="ui-panel__subtitle">
                        Fondo oscuro con malla suave, lateral slate/cyan y barra superior alineada a los paneles
                        <code class="text-cyan-300/90">ui-*</code>. Si algo no encaja, lo afinamos.
                    </p>
                </div>
                <span class="ui-badge ui-badge-success">Trial v1</span>
            </div>
            <div class="ui-panel__body text-sm text-slate-300">
                <p class="leading-relaxed">
                    Esta ruta solo muestra el layout: mismo fondo, mismo sidebar y mismo encabezado que el resto del
                    admin. Navega al menú lateral para comparar con pantallas reales (por ejemplo Roles).
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="ui-widget ui-widget--info">
                <div class="ui-widget__top">
                    <span class="ui-widget__icon"><i class="fas fa-layer-group"></i></span>
                    <span class="ui-widget__trend">Contraste</span>
                </div>
                <p class="ui-widget__label">Tarjeta de ejemplo</p>
                <p class="ui-widget__value">OK</p>
                <p class="ui-widget__meta">Debería leerse bien sobre el fondo.</p>
            </div>
            <div class="ui-widget ui-widget--neutral">
                <div class="ui-widget__top">
                    <span class="ui-widget__icon"><i class="fas fa-palette"></i></span>
                    <span class="ui-widget__trend">Tokens</span>
                </div>
                <p class="ui-widget__label">Estilos</p>
                <p class="ui-widget__value">app-shell</p>
                <p class="ui-widget__meta">Ver <code class="text-slate-200">resources/sass/_app-shell.scss</code></p>
            </div>
        </div>
    </div>
@endsection
