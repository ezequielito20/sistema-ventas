@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')
    <style>
        .zoomP {
            /* Aumentamos la anchura y altura durante 2 segundos */
            transition: width 1.1s, height 1.1s, transform 1.1s;
            -moz-transition: width 1.1s, height 1.1s, -moz-transform 1.1s;
            -webkit-transition: width 1.1s, height 1.1s, -webkit-transform 1.1s;
            -o-transition: width 1.1s, height 1.1s, -o-transform 1.1s;
            border: 1px solid #c0c0c0;
            box-shadow: #c0c0c0 0px 5px 5px 0px;
        }

        .zoomP:hover {
            transform: scale(1.05);
            -webkit-transform: scale(1.05);
        }
    </style>
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if ($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if ($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if (!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if ($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
    @if (($message = Session::get('message')) && ($icons = Session::get('icons')))
        <script>
            Swal.fire({
                position: "center",
                icon: "{{ $icons }}",
                title: "{{ $message }}",
                showConfirmButton: false,
                timer: 2500
            });
        </script>
    @endif
@stop
