@extends('layouts.app')

@section('title', 'Cambiar Contraseña')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="ui-panel">
        <div class="ui-panel__header">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-500/15">
                    <i class="fas fa-key text-cyan-400"></i>
                </div>
                <div>
                    <h1 class="ui-panel__title">Cambiar Contraseña</h1>
                    <p class="ui-panel__subtitle">Actualizá tu contraseña de acceso al sistema.</p>
                </div>
            </div>
        </div>
        <div class="ui-panel__body">
            @if (session('message'))
                <div class="mb-5 rounded-lg bg-emerald-500/15 border border-emerald-500/30 p-3 text-sm text-emerald-300">
                    <i class="fas fa-check-circle mr-1.5"></i> {{ session('message') }}
                </div>
            @endif

            <form method="POST" action="{{ route('profile.change-password.update') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="current_password" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Contraseña actual
                    </label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-4 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        placeholder="Ingresá tu contraseña actual"
                    />
                    @error('current_password')
                        <p class="mt-1.5 text-sm text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_password" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Nueva contraseña
                    </label>
                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-4 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        placeholder="Mínimo 8 caracteres"
                    />
                    @error('new_password')
                        <p class="mt-1.5 text-sm text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_password_confirmation" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Confirmar nueva contraseña
                    </label>
                    <input
                        type="password"
                        id="new_password_confirmation"
                        name="new_password_confirmation"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-4 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        placeholder="Repetí la nueva contraseña"
                    />
                </div>

                <div class="flex items-center justify-between border-t border-slate-700/50 pt-5">
                    <a href="{{ route('admin.index') }}" class="text-sm text-slate-400 hover:text-slate-200 transition-colors">
                        <i class="fas fa-arrow-left mr-1.5"></i> Volver al inicio
                    </a>
                    <button type="submit" class="ui-btn ui-btn-primary">
                        <i class="fas fa-save mr-1.5"></i> Cambiar contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
