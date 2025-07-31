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
    @if ($layoutHelper->isLayoutTopnavEnabled()) class="navbar-brand {{ config('adminlte.classes_brand') }}"
    @else
        class="brand-link {{ config('adminlte.classes_brand') }}" @endif>

    {{-- Logo --}}
    @if($userCompany && $userCompany->logo)
                    <img src="{{ $userCompany->logo_url }}"
             alt="{{ config('adminlte.logo_img_alt', 'Company Logo') }}"
             class="{{ config('adminlte.logo_img_class', 'brand-image img-circle elevation-3') }}"
             style="opacity:.8">
    @else
        <img src="{{ asset(config('adminlte.logo_img', 'vendor/adminlte/dist/img/AdminLTELogo.png')) }}"
             alt="{{ config('adminlte.logo_img_alt', 'AdminLTE') }}"
             class="{{ config('adminlte.logo_img_class', 'brand-image img-circle elevation-3') }}"
             style="opacity:.8">
    @endif

    {{-- Company Name / System Name --}}
    <span class="{{ config('adminlte.classes_brand_text') }}">
        @if($userCompany)
            <b>{{ $userCompany->name }}</b>
        @else
            {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}
        @endif
    </span>

</a>
