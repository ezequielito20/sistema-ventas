@error('plan')
    <div
        role="alert"
        {{ $attributes->merge([
            'class' => 'mb-4 rounded-lg border border-amber-500/45 bg-amber-950/45 px-4 py-3 text-sm text-amber-100 shadow-sm',
        ]) }}
    >
        <div class="flex items-start gap-2">
            <i class="fas fa-layer-group mt-0.5 shrink-0 text-amber-400/90" aria-hidden="true"></i>
            <span>{{ $message }}</span>
        </div>
    </div>
@enderror
