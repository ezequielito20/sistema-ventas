{{-- Estilos base DomPDF: tabla y tipografía conservadoras (compatibilidad). --}}
@php
    $cInk = '#0f172a';
    $cMuted = '#64748b';
    $cBorder = '#e2e8f0';
    $cAccent = '#0d9488';
    $cAccentSoft = '#ecfdf5';
@endphp
@page {
    margin: 26pt 34pt 38pt 34pt;
}
* {
    box-sizing: border-box;
}
body {
    font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
    font-size: 10.5pt;
    line-height: 1.45;
    color: {{ $cInk }};
    margin: 0;
    padding: 0;
}
.pdf-muted {
    color: {{ $cMuted }};
    font-size: 9pt;
}
.pdf-accent-bar {
    height: 4px;
    background: {{ $cAccent }};
    border-radius: 2px;
    margin-bottom: 10pt;
}
.pdf-header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8pt;
}
.pdf-header-table td {
    vertical-align: top;
    padding: 0;
}
.pdf-header-main {
    border-bottom: 1px solid {{ $cBorder }};
    padding-bottom: 9pt;
}
.pdf-header-main td {
    vertical-align: top;
}
.pdf-header-col {
    padding-right: 12pt;
}
.pdf-header-col--center {
    border-left: 1px solid {{ $cBorder }};
    border-right: 1px solid {{ $cBorder }};
    padding: 0 12pt;
}
.pdf-header-col--right {
    padding-left: 12pt;
}
.pdf-logo {
    max-width: 120px;
    max-height: 56px;
    border-radius: 4px;
    border: 1px solid {{ $cBorder }};
    object-fit: contain;
    margin-bottom: 6pt;
}
.pdf-logo-placeholder {
    width: 120px;
    height: 56px;
    border-radius: 4px;
    border: 1px dashed {{ $cBorder }};
    background: #f8fafc;
    font-size: 8pt;
    color: {{ $cMuted }};
    text-align: center;
    line-height: 56px;
    margin-bottom: 6pt;
}
.pdf-brand-name {
    font-size: 12pt;
    font-weight: bold;
    letter-spacing: -0.02em;
    margin: 0 0 3pt 0;
    color: {{ $cInk }};
}
.pdf-brand-line {
    font-size: 8.5pt;
    color: {{ $cMuted }};
    margin: 0;
}
.pdf-header-doc-title {
    margin: 0 0 5pt 0;
    font-size: 10.5pt;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.pdf-header-doc-line {
    margin: 0 0 2pt 0;
    font-size: 8.5pt;
    color: {{ $cInk }};
}
.pdf-header-doc-line--muted {
    color: {{ $cMuted }};
}
.pdf-company-title {
    margin: 0 0 3pt 0;
    text-align: right;
    font-size: 9.5pt;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.pdf-company-meta {
    margin: 0;
    text-align: right;
    font-size: 8.25pt;
    color: {{ $cInk }};
    line-height: 1.35;
}
.pdf-contact-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.75pt;
    margin-top: 4pt;
}
.pdf-contact-table td {
    padding: 1.5pt 0;
    vertical-align: top;
}
.pdf-contact-label {
    color: {{ $cMuted }};
    width: 66px;
    font-size: 7.8pt;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.pdf-contact-value {
    font-weight: 600;
    color: {{ $cInk }};
    text-align: right;
}
.pdf-emission {
    margin-top: 7pt;
    padding: 6pt 9pt;
    background: #f8fafc;
    border: 1px solid {{ $cBorder }};
    border-radius: 4px;
    font-size: 8.6pt;
    text-align: right;
}
.pdf-emission strong {
    color: #0f172a;
}
.pdf-doc-heading {
    margin: 18pt 0 14pt 0;
    padding-bottom: 10pt;
    border-bottom: 1px solid {{ $cBorder }};
}
.pdf-doc-title {
    font-size: 17pt;
    font-weight: bold;
    margin: 0 0 6pt 0;
    letter-spacing: -0.03em;
    color: {{ $cInk }};
}
.pdf-doc-subtitle {
    margin: 0;
    font-size: 10pt;
    color: {{ $cMuted }};
}
.pdf-summary {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 14pt 0;
    background: #f8fafc;
    border: 1px solid {{ $cBorder }};
    border-radius: 6px;
}
.pdf-summary td {
    padding: 10pt 12pt;
    font-size: 10pt;
}
.pdf-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9.75pt;
    border: 1px solid {{ $cBorder }};
}
.pdf-table thead th {
    background: #1e293b;
    color: #fff;
    font-weight: bold;
    text-align: left;
    padding: 8pt 9pt;
    font-size: 9pt;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.pdf-table tbody td {
    border-top: 1px solid {{ $cBorder }};
    padding: 7pt 9pt;
    vertical-align: top;
}
.pdf-table tbody tr:nth-child(even) td {
    background: #fafafa;
}
.pdf-num {
    text-align: center;
    font-variant-numeric: tabular-nums;
}
.pdf-badge {
    display: inline-block;
    padding: 2pt 7pt;
    border-radius: 999px;
    font-size: 8pt;
    font-weight: bold;
    background: #e0f2fe;
    color: #0369a1;
}
.pdf-badge--system {
    background: #fef3c7;
    color: #b45309;
}
.pdf-footer-wrap {
    margin-top: 20pt;
    padding-top: 10pt;
    border-top: 1px solid {{ $cBorder }};
    font-size: 8.5pt;
    color: {{ $cMuted }};
}
.pdf-footer-brand {
    font-weight: bold;
    color: #475569;
    margin-bottom: 4pt;
}
.pdf-footer-module {
    margin-top: 6pt;
    font-size: 9pt;
    color: #475569;
}
