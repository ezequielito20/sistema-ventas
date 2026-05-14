@error('plan')
    <div
        role="alert"
        aria-live="polite"
        {{ $attributes->merge([
            'class' => 'mb-5 overflow-hidden rounded-xl border border-amber-400/50 bg-gradient-to-br from-amber-950/80 to-amber-950/40 shadow-lg shadow-amber-950/20 ring-1 ring-amber-400/20',
        ]) }}
    >
        <div class="flex gap-3 px-4 py-4 sm:px-5 sm:py-4">
            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-amber-500/20 text-amber-300"
                aria-hidden="true"
            >
                <i class="fas fa-exclamation-triangle text-lg"></i>
            </div>
            <div class="min-w-0 flex-1 pt-0.5">
                <p class="text-sm font-semibold tracking-wide text-amber-50">Límite de suscripción</p>
                <p class="mt-1.5 text-sm leading-relaxed text-amber-100/95">{{ $message }}</p>
            </div>
        </div>
    </div>
@enderror
