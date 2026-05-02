@extends('pdf.layouts.document')

@section('pdf-document-title', 'Reporte de Arqueos de Caja')

@section('pdf-title', 'Reporte de Arqueos de Caja')

@section('pdf-subtitle')
    Registro de aperturas y cierres de caja: fechas, montos, estados y observaciones.
@endsection

@push('pdf-module-styles')
    .pdf-money {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .pdf-badge--open {
        background: #dcfce7;
        color: #15803d;
    }
    .pdf-badge--closed {
        background: #f3f4f6;
        color: #475569;
    }
@endpush

@section('pdf-content')
    @php
        $totalArqueos = $cashCounts->count();
        $sumaInicial = (float) $cashCounts->sum('initial_amount');
        $sumaFinal = (float) $cashCounts->sum('final_amount');
        $abiertos = $cashCounts->whereNull('closing_date')->count();
        $cerrados = $cashCounts->whereNotNull('closing_date')->count();
    @endphp

    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Resumen:</strong>
                {{ $totalArqueos }} arqueo(s)
                · {{ $abiertos }} abierto(s)
                · {{ $cerrados }} cerrado(s)
                · Total inicial: <strong>{{ $currency->symbol }} {{ number_format($sumaInicial, 2) }}</strong>
                · Total final: <strong>{{ $currency->symbol }} {{ number_format($sumaFinal, 2) }}</strong>
            </td>
        </tr>
    </table>

    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 17%;">Apertura</th>
                <th style="width: 17%;">Cierre</th>
                <th style="width: 13%;" class="pdf-money">Monto inicial</th>
                <th style="width: 13%;" class="pdf-money">Monto final</th>
                <th style="width: 25%;">Observaciones</th>
                <th style="width: 10%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cashCounts as $cashCount)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($cashCount->opening_date)->format('d/m/Y H:i') }}</td>
                    <td>
                        @if ($cashCount->closing_date)
                            {{ \Carbon\Carbon::parse($cashCount->closing_date)->format('d/m/Y H:i') }}
                        @else
                            En curso
                        @endif
                    </td>
                    <td class="pdf-money">{{ $currency->symbol }} {{ number_format((float) $cashCount->initial_amount, 2) }}</td>
                    <td class="pdf-money">
                        @if ($cashCount->final_amount !== null)
                            {{ $currency->symbol }} {{ number_format((float) $cashCount->final_amount, 2) }}
                        @else
                            Pendiente
                        @endif
                    </td>
                    <td>{{ $cashCount->observations ?? '—' }}</td>
                    <td>
                        @if ($cashCount->closing_date)
                            <span class="pdf-badge pdf-badge--closed">Cerrado</span>
                        @else
                            <span class="pdf-badge pdf-badge--open">Abierto</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="pdf-summary" cellspacing="0" style="margin-top: 20px;">
        <tr>
            <td>
                <strong>Totales:</strong>
                Suma inicial: <strong>{{ $currency->symbol }} {{ number_format($sumaInicial, 2) }}</strong>
                · Suma final: <strong>{{ $currency->symbol }} {{ number_format($sumaFinal, 2) }}</strong>
                · Diferencia: <strong>{{ $currency->symbol }} {{ number_format($sumaFinal - $sumaInicial, 2) }}</strong>
            </td>
        </tr>
    </table>
@endsection
