@props([
    'company',
    'emittedAt',
])
<div class="pdf-accent-bar"></div>
<table class="pdf-header-table">
    <tr>
        <td>
            <table class="pdf-header-main" width="100%" cellspacing="0">
                <tr>
                    <td class="pdf-header-col" style="width: 30%;">
                        @php
                            $logoSrc = null;
                            if ($company->logo) {
                                $logoDiskPath = storage_path('app/public/' . $company->logo);
                                if (file_exists($logoDiskPath) && is_readable($logoDiskPath)) {
                                    // Usar ruta file:// que DomPDF soporta nativamente
                                    $logoSrc = 'file://' . $logoDiskPath;
                                }
                            }
                        @endphp
                        @if ($logoSrc)
                            <img class="pdf-logo" src="{{ $logoSrc }}" alt="Logo">
                        @else
                            <div class="pdf-logo-placeholder">{{ strtoupper(substr($company->name, 0, 2)) }}</div>
                        @endif
                        <p class="pdf-brand-name">{{ $company->name }}</p>
                        @if ($company->address)
                            <p class="pdf-brand-line">{{ $company->address }}</p>
                        @endif
                    </td>
                    <td class="pdf-header-col pdf-header-col--center" style="width: 36%;">
                        <h2 class="pdf-header-doc-title">Documento de gestión</h2>
                        <p class="pdf-header-doc-line">
                            <strong>Tipo:</strong> Reporte administrativo
                        </p>
                        <p class="pdf-header-doc-line">
                            <strong>Formato:</strong> Carta vertical
                        </p>
                        <p class="pdf-header-doc-line pdf-header-doc-line--muted">
                            Emitido desde el sistema interno de {{ $company->name }}
                        </p>
                    </td>
                    <td class="pdf-header-col pdf-header-col--right" style="width: 34%;">
                        <p class="pdf-company-title">{{ strtoupper($company->name) }}</p>
                        <p class="pdf-company-meta">
                            @if ($company->nit)
                                <strong>NIT:</strong> {{ $company->nit }}<br>
                            @endif
                            @if ($company->address)
                                {{ $company->address }}<br>
                            @endif
                        </p>
                        <table class="pdf-contact-table" align="right">
                            @if ($company->phone)
                                <tr>
                                    <td class="pdf-contact-value">{{ $company->phone }}</td>
                                </tr>
                            @endif
                            @if ($company->email)
                                <tr>
                                    <td class="pdf-contact-value">{{ $company->email }}</td>
                                </tr>
                            @endif
                            @if ($company->ig)
                                <tr>
                                    <td class="pdf-contact-value">{{ $company->ig }}</td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div class="pdf-emission">
    <strong>Fecha y hora de emisión:</strong>
    {{ $emittedAt->copy()->timezone(config('app.timezone'))->format('d/m/Y \a \l\a\s H:i \h\r\s') }}
</div>
