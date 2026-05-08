<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>@yield('pdf-document-title', 'Documento')</title>
    <style type="text/css">
        @include('pdf.partials.styles-base')
        @stack('pdf-module-styles')
    </style>
</head>
<body>
    @include('pdf.partials.company-header', ['company' => $company, 'emittedAt' => $emittedAt])

    <div class="pdf-doc-heading">
        <h1 class="pdf-doc-title">@yield('pdf-title')</h1>
        @hasSection('pdf-subtitle')
            <p class="pdf-doc-subtitle">@yield('pdf-subtitle')</p>
        @endif
    </div>

    <main>
        @yield('pdf-content')
    </main>

    @include('pdf.partials.document-footer', ['company' => $company])
    @include('pdf.partials.page-number-canvas')
</body>
</html>
