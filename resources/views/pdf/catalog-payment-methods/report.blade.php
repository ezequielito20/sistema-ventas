@extends('pdf.layouts.document')

@section('pdf-document-title', 'Informe de métodos de pago — catálogo')

@section('pdf-title', 'Métodos de pago del catálogo')

@section('pdf-subtitle')
    Métodos de pago configurados para el checkout del catálogo público de su empresa.
@endsection

@section('pdf-content')
    @php
        $activos = $paymentMethods->where('is_active', true)->count();
        $withOrders = $paymentMethods->where('orders_count', '>', 0)->count();
    @endphp
    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Resumen:</strong>
                {{ $paymentMethods->count() }} {{ $paymentMethods->count() === 1 ? 'registro' : 'registros' }}
                · {{ $activos }} activos
                · {{ $withOrders }} con pedidos asociados
            </td>
        </tr>
    </table>

    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 28%;">Nombre</th>
                <th style="width: 9%;" class="pdf-num">Dto. %</th>
                <th style="width: 9%;" class="pdf-num">Orden</th>
                <th style="width: 10%;">Activo</th>
                <th style="width: 10%;" class="pdf-num">Pedidos</th>
                <th style="width: 31%;">Instrucciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($paymentMethods as $method)
                <tr>
                    <td class="pdf-num">{{ $loop->iteration }}</td>
                    <td><strong>{{ $method->name }}</strong></td>
                    <td class="pdf-num">{{ $method->discount_percent }}</td>
                    <td class="pdf-num">{{ $method->sort_order }}</td>
                    <td>{{ $method->is_active ? 'Sí' : 'No' }}</td>
                    <td class="pdf-num">{{ $method->orders_count }}</td>
                    <td>{{ \Illuminate\Support\Str::limit(trim((string) ($method->instructions ?? '')), 120) ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('pdf-footer-module')
    Módulo: Métodos de pago del catálogo · Informe estándar
@endsection
