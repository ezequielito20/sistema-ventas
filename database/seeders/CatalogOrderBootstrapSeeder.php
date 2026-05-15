<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyDeliveryMethod;
use App\Models\CompanyPaymentMethod;
use App\Models\DeliverySlot;
use Illuminate\Database\Seeder;

/**
 * Crea métodos de pago y entrega mínimos para que el checkout del catálogo funcione en cada empresa.
 */
class CatalogOrderBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Company::query()->cursor() as $company) {
            if (CompanyPaymentMethod::query()->where('company_id', $company->id)->exists()) {
                continue;
            }

            $pay = CompanyPaymentMethod::query()->create([
                'company_id' => $company->id,
                'name' => 'Efectivo (divisa)',
                'instructions' => "Pago en efectivo USD.\nCoordina con la tienda el punto de encuentro.",
                'discount_percent' => 0,
                'sort_order' => 0,
                'is_active' => true,
            ]);

            CompanyPaymentMethod::query()->create([
                'company_id' => $company->id,
                'name' => 'Pago móvil',
                'instructions' => "Datos de pago móvil: actualizar en Configuración > métodos de pago.\nIndicar referencia al confirmar.",
                'discount_percent' => 0,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            $pickup = CompanyDeliveryMethod::query()->create([
                'company_id' => $company->id,
                'type' => CompanyDeliveryMethod::TYPE_PICKUP,
                'name' => 'Retiro en tienda',
                'instructions' => 'Retira tu pedido en el horario acordado.',
                'pickup_address' => $company->address ?? 'Dirección por confirmar',
                'sort_order' => 0,
                'is_active' => true,
            ]);

            $delivery = CompanyDeliveryMethod::query()->create([
                'company_id' => $company->id,
                'type' => CompanyDeliveryMethod::TYPE_DELIVERY,
                'name' => 'Delivery',
                'instructions' => 'Entrega a domicilio según zona y horario disponible.',
                'pickup_address' => null,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            $zone = $delivery->zones()->create([
                'name' => 'Zona general',
                'extra_fee_usd' => 3,
                'is_active' => true,
            ]);

            $start = now()->addDay()->setTime(9, 0);
            DeliverySlot::query()->create([
                'company_id' => $company->id,
                'company_delivery_method_id' => $pickup->id,
                'delivery_zone_id' => null,
                'starts_at' => $start,
                'ends_at' => (clone $start)->addHours(2),
                'max_orders' => 1,
                'booked_count' => 0,
                'is_active' => true,
            ]);

            $start2 = now()->addDay()->setTime(14, 0);
            DeliverySlot::query()->create([
                'company_id' => $company->id,
                'company_delivery_method_id' => $delivery->id,
                'delivery_zone_id' => $zone->id,
                'starts_at' => $start2,
                'ends_at' => (clone $start2)->addHours(2),
                'max_orders' => 1,
                'booked_count' => 0,
                'is_active' => true,
            ]);

            unset($pay, $pickup, $delivery, $zone);
        }
    }
}
