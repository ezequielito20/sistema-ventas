@props([
    'company',
])
<div class="pdf-footer-wrap">
    <div class="pdf-footer-brand">{{ $company->name }} · Sistema de gestión</div>
    <div class="pdf-muted">
        Documento generado electrónicamente para uso interno. Los datos reflejan el estado del sistema en la fecha de emisión indicada arriba.
    </div>
    @hasSection('pdf-footer-module')
        <div class="pdf-footer-module">@yield('pdf-footer-module')</div>
    @endif
</div>
