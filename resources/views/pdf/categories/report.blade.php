@extends('pdf.layouts.document')

@section('pdf-document-title', 'Informe de categorías')

@section('pdf-title', 'Informe de categorías')

@section('pdf-subtitle')
    Listado de categorías de su empresa con conteo de productos asociados.
@endsection

@section('pdf-content')
    @php
        $withProducts = $categories->where('products_count', '>', 0)->count();
    @endphp
    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Resumen:</strong>
                {{ $categories->count() }} {{ $categories->count() === 1 ? 'categoría' : 'categorías' }}
                · {{ $withProducts }} con productos
                · {{ $categories->count() - $withProducts }} sin productos
            </td>
        </tr>
    </table>

    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 6%;">#</th>
                <th style="width: 32%;">Categoría</th>
                <th style="width: 38%;">Descripción</th>
                <th style="width: 12%;" class="pdf-num">Productos</th>
                <th style="width: 12%;">Alta</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td class="pdf-num">{{ $loop->iteration }}</td>
                    <td><strong>{{ $category->name }}</strong></td>
                    <td>{{ \Illuminate\Support\Str::limit($category->description ?? '—', 80) }}</td>
                    <td class="pdf-num">{{ $category->products_count }}</td>
                    <td>{{ $category->created_at->timezone(config('app.timezone'))->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('pdf-footer-module')
    Módulo: Categorías · Informe estándar
@endsection
