{{--
    Vista de prueba del shell (fondo + sidebar + topbar).
    /ui/shell-preview — misma plantilla que el resto del admin.
--}}
@extends('layouts.app')

@section('title', 'Vista previa — Shell UI')

@section('content')
    <div class="mx-auto max-w-5xl space-y-8">
        <div class="ui-panel">
            <div class="ui-panel__header">
                <div>
                    <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-[0.2em] text-cyan-400/90">Sistema</p>
                    <h1 class="ui-panel__title">Interfaz base · modo oscuro</h1>
                    <p class="ui-panel__subtitle max-w-2xl">
                        Malla ligera, auroras suaves y acentos cian/violeta. Navegación con pesos claros y foco accesible.
                        Pensado para leer mucho tiempo sin fatiga.
                    </p>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="ui-badge ui-badge-success">Legible</span>
                    <span class="ui-badge ui-badge-warning">HUD</span>
                </div>
            </div>
            <div class="ui-panel__body space-y-4 text-sm leading-relaxed text-slate-300">
                <p>
                    El lateral usa una franja luminosa y un borde degradado para separarse del contenido. La barra
                    superior tiene cristal esmerilado y una línea de acento tipo panel de control.
                </p>
                <p class="text-slate-400">
                    Compará con <strong class="text-slate-200">Roles</strong> o cualquier módulo: mismo marco, mismas
                    reglas visuales.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="ui-widget ui-widget--info">
                <div class="ui-widget__top">
                    <span class="ui-widget__icon"><i class="fas fa-eye"></i></span>
                    <span class="ui-widget__trend">Lectura</span>
                </div>
                <p class="ui-widget__label">Contraste</p>
                <p class="ui-widget__value">Alto</p>
                <p class="ui-widget__meta">Texto base #e8edf4</p>
            </div>
            <div class="ui-widget ui-widget--success">
                <div class="ui-widget__top">
                    <span class="ui-widget__icon"><i class="fas fa-bolt"></i></span>
                    <span class="ui-widget__trend">Acento</span>
                </div>
                <p class="ui-widget__label">Futurista</p>
                <p class="ui-widget__value">Cian</p>
                <p class="ui-widget__meta">Sin recargar el layout</p>
            </div>
            <div class="ui-widget ui-widget--neutral">
                <div class="ui-widget__top">
                    <span class="ui-widget__icon"><i class="fas fa-code"></i></span>
                    <span class="ui-widget__trend">Código</span>
                </div>
                <p class="ui-widget__label">Tokens</p>
                <p class="ui-widget__value text-base">SCSS</p>
                <p class="ui-widget__meta truncate" title="resources/sass/_app-shell.scss">_app-shell.scss</p>
            </div>
        </div>
    </div>
@endsection
