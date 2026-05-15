@extends('pdf.layouts.document')

@section('pdf-document-title', 'Informe de métodos de entrega — catálogo')

@section('pdf-title', 'Métodos de entrega del catálogo')

@section('pdf-subtitle')
    Métodos de entrega, zonas delivery y franjas horarias configurados para el checkout público.
@endsection

@section('pdf-content')
    @php
        $deliveryCount = $deliveryMethods->where('type', App\Models\CompanyDeliveryMethod::TYPE_DELIVERY)->count();
        $pickupCount = $deliveryMethods->where('type', App\Models\CompanyDeliveryMethod::TYPE_PICKUP)->count();
        $activos = $deliveryMethods->where('is_active', true)->count();
    @endphp
    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Resumen:</strong>
                {{ $deliveryMethods->count() }} {{ $deliveryMethods->count() === 1 ? 'método' : 'métodos' }}
                ({{ $pickupCount }} entrega · {{ $deliveryCount }} delivery)
                · {{ $activos }} activos
                · {{ $deliverySlots->count() }} {{ $deliverySlots->count() === 1 ? 'franja' : 'franjas' }} registradas
            </td>
        </tr>
    </table>

    <p style="font-size: 11pt; font-weight: bold; margin: 12pt 0 6pt;">Métodos</p>
    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 26%;">Nombre</th>
                <th style="width: 12%;">Tipo</th>
                <th style="width: 14%;">Dirección Entrega</th>
                <th style="width: 10%;">Activo</th>
                <th style="width: 11%;" class="pdf-num">Pedidos</th>
                <th style="width: 11%;" class="pdf-num">Zonas</th>
                <th style="width: 11%;" class="pdf-num">Franjas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($deliveryMethods as $method)
                <tr>
                    <td class="pdf-num">{{ $loop->iteration }}</td>
                    <td><strong>{{ $method->name }}</strong></td>
                    <td>{{ $method->type === 'delivery' ? 'Delivery' : 'Entrega' }}</td>
                    <td>{{ $method->pickup_address ? \Illuminate\Support\Str::limit($method->pickup_address, 40) : '—' }}</td>
                    <td>{{ $method->is_active ? 'Sí' : 'No' }}</td>
                    <td class="pdf-num">{{ $method->orders_count }}</td>
                    <td class="pdf-num">{{ $method->zones_count }}</td>
                    <td class="pdf-num">{{ $method->delivery_slots_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($deliveryMethods->filter(fn ($m) => $m->zones->isNotEmpty())->isNotEmpty())
        <p style="font-size: 11pt; font-weight: bold; margin: 12pt 0 6pt;">Zonas por método delivery</p>
        @foreach ($deliveryMethods as $method)
            @if ($method->type !== 'delivery' || $method->zones->isEmpty())
                @continue
            @endif
            <p style="margin: 0.5rem 0 0.2rem;"><strong>{{ $method->name }}</strong></p>
            <table class="pdf-table" cellspacing="0">
                <thead>
                    <tr>
                        <th>Zona</th>
                        <th style="width: 18%;" class="pdf-num">Extra USD</th>
                        <th style="width: 14%;">Activo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($method->zones as $z)
                        <tr>
                            <td>{{ $z->name }}</td>
                            <td class="pdf-num">{{ $z->extra_fee_usd }}</td>
                            <td>{{ $z->is_active ? 'Sí' : 'No' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif

    <p style="font-size: 11pt; font-weight: bold; margin: 12pt 0 6pt;">Franjas horarias</p>
    @if ($deliverySlots->isEmpty())
        <p>No hay franjas registradas.</p>
    @else
        <table class="pdf-table" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 6%;">#</th>
                    <th style="width: 22%;">Método</th>
                    <th style="width: 22%;">Zona</th>
                    <th style="width: 9%;">Día</th>
                    <th style="width: 7%;">Desde</th>
                    <th style="width: 7%;">Hasta</th>
                    <th style="width: 7%;" class="pdf-num">Máx.</th>
                    <th style="width: 7%;">Activo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deliverySlots as $slot)
                    <tr>
                        <td class="pdf-num">{{ $loop->iteration }}</td>
                        <td>{{ $slot->deliveryMethod->name }}</td>
                        <td>{{ $slot->delivery_zone_id ? ($slot->zone?->name ?? '—') : '—' }}</td>
                        <td>{{ $slot->weekdayLabelEs() }}</td>
                        <td>{{ $slot->timeShort() }}</td>
                        <td>{{ $slot->timeEndShort() }}</td>
                        <td class="pdf-num">{{ $slot->max_orders }}</td>
                        <td>{{ $slot->is_active ? 'Sí' : 'No' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection

@section('pdf-footer-module')
    Módulo: Métodos de entrega del catálogo · Informe estándar
@endsection
