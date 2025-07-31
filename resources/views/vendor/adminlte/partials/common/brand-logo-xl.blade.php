@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php
    // Configurar dashboard_url
    $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home');
    
    if (config('adminlte.use_route_url', false)) {
        $dashboard_url = $dashboard_url ? route($dashboard_url) : '';
    } else {
        $dashboard_url = $dashboard_url ? url($dashboard_url) : '';
    }
    
    // Obtener la company una sola vez para evitar N+1 queries
    $userCompany = null;
    if (auth()->check()) {
        $userCompany = auth()->user()->company;
    }
@endphp

<a href="{{ $dashboard_url }}"
    @if($layoutHelper->isLayoutTopnavEnabled())
        class="navbar-brand logo-switch {{ config('adminlte.classes_brand') }}"
    @else
        class="brand-link logo-switch {{ config('adminlte.classes_brand') }}"
    @endif>

    {{-- Logo image --}}
    @if($userCompany && $userCompany->logo)
                        <img src="{{ asset('storage/' . $userCompany->logo) }}"
             alt="{{ config('adminlte.logo_img_alt', 'Company Logo') }}"
             class="{{ config('adminlte.logo_img_class', 'brand-image img-circle elevation-3') }}"
             style="opacity:.8">
    @else
        <img src="{{ asset(config('adminlte.logo_img')) }}"
             alt="{{ config('adminlte.logo_img_alt', 'AdminLTE') }}"
             class="{{ config('adminlte.logo_img_class', 'brand-image-xl logo-xs') }}"
             style="opacity:.8">
    @endif

    {{-- Company Name / System Name --}}
    <span class="{{ config('adminlte.classes_brand_text') }}">
        @if($userCompany)
            <b>{{ $userCompany->name }}</b>
        @else
            {!! config('adminlte.logo', '<b>Sistema de Ventas</b>') !!}
        @endif
    </span>
</a>
