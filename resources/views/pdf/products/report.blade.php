@extends('pdf.layouts.document')

@section('pdf-document-title', 'Inventario de productos')

@section('pdf-title', 'Inventario de productos')

@section('pdf-subtitle')
    Catálogo de la empresa con existencias, precios y valores estimados de inventario (costo y venta).
@endsection

@push('pdf-module-styles')
    .pdf-money {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .pdf-product-name {
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 2pt 0;
    }
    .pdf-product-sub {
        margin: 0;
        font-size: 8.25pt;
        color: #64748b;
    }
    .pdf-stock--low {
        color: #b91c1c;
        font-weight: 700;
    }
    .pdf-code {
        font-family: DejaVu Sans Mono, DejaVu Sans, monospace;
        font-size: 8.75pt;
        font-weight: 600;
    }
@endpush

@section('pdf-content')
    @php
        $totalStock = (int) $products->sum('stock');
        $valorCompra = $products->sum(fn ($p) => (float) $p->purchase_price * (int) $p->stock);
        $valorVenta = $products->sum(fn ($p) => (float) $p->sale_price * (int) $p->stock);
        $bajoStock = $products->filter(fn ($p) => (int) $p->stock <= (int) $p->min_stock)->count();
    @endphp

    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Resumen:</strong>
                {{ $products->count() }} {{ $products->count() === 1 ? 'producto' : 'productos' }}
                · {{ number_format($totalStock, 0, '.', ',') }} unidades en stock
                · {{ $bajoStock }} {{ $bajoStock === 1 ? 'referencia baja' : 'referencias bajas' }} (≤ mínimo)
                · Valor inventario al costo: <strong>{{ $currency->symbol }} {{ number_format($valorCompra, 2) }}</strong>
                · Valor inventario a precio venta: <strong>{{ $currency->symbol }} {{ number_format($valorVenta, 2) }}</strong>
            </td>
        </tr>
    </table>

    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 10%;">Código</th>
                <th style="width: 26%;">Producto</th>
                <th style="width: 12%;">Categoría</th>
                <th style="width: 8%;" class="pdf-num">Stock</th>
                <th style="width: 13%; text-align: right;">P. compra</th>
                <th style="width: 13%; text-align: right;">P. venta</th>
                <th style="width: 14%;" class="pdf-num">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                @php
                    $esBajo = (int) $product->stock <= (int) $product->min_stock;
                @endphp
                <tr>
                    <td class="pdf-num">{{ $loop->iteration }}</td>
                    <td class="pdf-code">{{ $product->code }}</td>
                    <td>
                        <p class="pdf-product-name">{{ $product->name }}</p>
                        <p class="pdf-product-sub">
                            {{ $product->supplier?->company_name ?? 'Sin proveedor' }}
                        </p>
                    </td>
                    <td>
                        <span class="pdf-badge">{{ $product->category->name ?? '—' }}</span>
                    </td>
                    <td class="pdf-num {{ $esBajo ? 'pdf-stock--low' : '' }}">
                        {{ $product->stock }}
                    </td>
                    <td class="pdf-money">{{ $currency->symbol }} {{ number_format((float) $product->purchase_price, 2) }}</td>
                    <td class="pdf-money">{{ $currency->symbol }} {{ number_format((float) $product->sale_price, 2) }}</td>
                    <td class="pdf-num" style="font-size: 8.5pt;">{{ $product->stock_status_label ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('pdf-footer-module')
    Módulo: Productos · Informe de inventario · Generado por: {{ Auth::user()->name ?? 'Usuario' }}
@endsection
