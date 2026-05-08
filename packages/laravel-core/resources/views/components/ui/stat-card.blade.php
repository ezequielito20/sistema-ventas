@props([
    'variant' => 'neutral',
    'icon',
    'trend',
    'label',
    'value',
    'meta' => null,
])

<div {{ $attributes->class(['ui-widget ui-widget--dense ui-widget--' . $variant]) }}>
    <div class="ui-widget__top">
        <span class="ui-widget__icon"><i class="{{ $icon }}"></i></span>
        <span class="ui-widget__trend">{{ $trend }}</span>
    </div>
    <p class="ui-widget__label">{{ $label }}</p>
    <p class="ui-widget__value">{{ $value }}</p>
    @if ($meta)
        <p class="ui-widget__meta">{{ $meta }}</p>
    @endif
</div>
