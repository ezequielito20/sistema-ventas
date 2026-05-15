<?php

namespace App\Services\Catalog;

use App\Models\Cart;
use App\Models\Company;
use App\Models\CompanyDeliveryMethod;
use App\Models\CompanyPaymentMethod;
use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use App\Models\ExchangeRate;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\PlanEntitlementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CatalogOrderCheckoutService
{
    public function __construct(
        protected PlanEntitlementService $planEntitlementService
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function checkout(Company $company, Cart $cart, array $data): Order
    {
        if (! $this->planEntitlementService->companyHasModule($company, 'orders')) {
            throw ValidationException::withMessages([
                'plan' => 'Los pedidos desde catálogo no están habilitados en el plan de esta empresa.',
            ]);
        }

        $cart->load(['items.product']);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'El carrito está vacío.',
            ]);
        }

        $phoneDigits = preg_replace('/\D+/', '', (string) ($data['customer_phone'] ?? ''));
        Validator::make(
            ['customer_phone' => $phoneDigits],
            ['customer_phone' => ['required', 'digits:11']],
            [
                'customer_phone.required' => 'El teléfono es obligatorio',
                'customer_phone.digits' => 'El teléfono debe tener exactamente 11 dígitos numéricos (ej: 04148965789).',
            ]
        )->validate();
        $data['customer_phone'] = $phoneDigits;

        /** @var CompanyPaymentMethod|null $paymentMethod */
        $paymentMethod = CompanyPaymentMethod::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->whereKey($data['company_payment_method_id'] ?? 0)
            ->first();

        if (! $paymentMethod) {
            throw ValidationException::withMessages([
                'company_payment_method_id' => 'Método de pago no válido.',
            ]);
        }

        /** @var CompanyDeliveryMethod|null $deliveryMethod */
        $deliveryMethod = CompanyDeliveryMethod::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->whereKey($data['company_delivery_method_id'] ?? 0)
            ->first();

        if (! $deliveryMethod) {
            throw ValidationException::withMessages([
                'company_delivery_method_id' => 'Método de entrega no válido.',
            ]);
        }

        $zone = null;
        $customLocRaw = trim((string) ($data['delivery_custom_location'] ?? ''));
        $customLoc = $customLocRaw !== '' ? $customLocRaw : null;

        if ($deliveryMethod->isPickup()) {
            $customLoc = null;
        }

        if ($deliveryMethod->isDelivery()) {
            $zonesBaseQuery = DeliveryZone::query()
                ->where('company_delivery_method_id', $deliveryMethod->id)
                ->where('is_active', true);
            $hasZones = $zonesBaseQuery->exists();

            if ($customLoc !== null) {
                if (! empty($data['delivery_zone_id'])) {
                    throw ValidationException::withMessages([
                        'delivery_zone_id' => 'No puede combinar una zona de la lista con dirección personalizada.',
                    ]);
                }
                $zone = null;
            } elseif ($hasZones) {
                $zone = (clone $zonesBaseQuery)->whereKey($data['delivery_zone_id'] ?? 0)->first();
                if (! $zone) {
                    throw ValidationException::withMessages([
                        'delivery_zone_id' => 'Debe seleccionar una zona de entrega.',
                    ]);
                }
            } else {
                throw ValidationException::withMessages([
                    'delivery_custom_location' => 'Indique la zona o dirección para el delivery.',
                ]);
            }
        }

        $slot = null;
        if (! empty($data['delivery_slot_id'])) {
            $slot = DeliverySlot::query()
                ->where('company_id', $company->id)
                ->where('company_delivery_method_id', $deliveryMethod->id)
                ->where('is_active', true)
                ->whereKey($data['delivery_slot_id'])
                ->first();
            if (! $slot) {
                throw ValidationException::withMessages([
                    'delivery_slot_id' => 'El horario seleccionado no es válido.',
                ]);
            }
            if ($deliveryMethod->isDelivery() && $slot) {
                if ($slot->delivery_zone_id === null) {
                    throw ValidationException::withMessages([
                        'delivery_slot_id' => 'El horario no es válido para delivery.',
                    ]);
                }
                $slotZoneOk = DeliveryZone::query()
                    ->where('company_delivery_method_id', $deliveryMethod->id)
                    ->where('is_active', true)
                    ->whereKey($slot->delivery_zone_id)
                    ->exists();
                if (! $slotZoneOk) {
                    throw ValidationException::withMessages([
                        'delivery_slot_id' => 'El horario no corresponde a este método de entrega.',
                    ]);
                }
                if ($zone !== null && (int) $slot->delivery_zone_id !== (int) $zone->id) {
                    throw ValidationException::withMessages([
                        'delivery_slot_id' => 'El horario no corresponde a la zona elegida.',
                    ]);
                }
            }
            if ($deliveryMethod->isPickup() && $slot->delivery_zone_id !== null) {
                throw ValidationException::withMessages([
                    'delivery_slot_id' => 'El horario no corresponde a la entrega seleccionada.',
                ]);
            }
        }

        $rate = (float) ExchangeRate::current();
        if ($rate <= 0) {
            throw ValidationException::withMessages([
                'rate' => 'No hay tasa de cambio configurada.',
            ]);
        }

        return DB::transaction(function () use ($company, $cart, $data, $paymentMethod, $deliveryMethod, $zone, $slot, $rate, $customLoc): Order {
            $lines = [];
            $subtotal = 0.0;

            foreach ($cart->items as $cartItem) {
                /** @var Product $product */
                $product = Product::query()->whereKey($cartItem->product_id)->lockForUpdate()->first();
                if (! $product || (int) $product->company_id !== (int) $company->id) {
                    throw ValidationException::withMessages([
                        'cart' => 'Un producto del carrito ya no está disponible.',
                    ]);
                }
                if ($product->stock < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Stock insuficiente para «{$product->name}». Disponible: {$product->stock}.",
                    ]);
                }
                $unit = (float) $product->final_price;
                $lineSub = round($unit * $cartItem->quantity, 2);
                $subtotal = round($subtotal + $lineSub, 2);
                $lines[] = [
                    'product' => $product,
                    'quantity' => (int) $cartItem->quantity,
                    'unit' => $unit,
                    'line_sub' => $lineSub,
                ];
            }

            $discPercent = (float) $paymentMethod->discount_percent;
            $discountAmount = $discPercent > 0 ? round($subtotal * ($discPercent / 100), 2) : 0.0;
            $afterDiscount = round($subtotal - $discountAmount, 2);

            $deliveryFee = $zone ? (float) $zone->extra_fee_usd : 0.0;
            $totalUsd = round($afterDiscount + $deliveryFee, 2);
            $totalBs = round($totalUsd * $rate, 2);

            $scheduledDeliveryDate = null;
            /** @var Carbon|null */
            $slotNextCarbon = null;

            if ($slot) {
                $slotLocked = DeliverySlot::query()->whereKey($slot->id)->lockForUpdate()->first();
                if (! $slotLocked || ! $slotLocked->is_active) {
                    throw ValidationException::withMessages([
                        'delivery_slot_id' => 'El horario seleccionado no es válido.',
                    ]);
                }
                $slotNextCarbon = $slotLocked->resolveNextScheduledDeliveryDate();
                $scheduledDeliveryDate = $slotNextCarbon->format('Y-m-d');

                $bookedPending = Order::query()
                    ->where('delivery_slot_id', $slotLocked->id)
                    ->whereDate('scheduled_delivery_date', $scheduledDeliveryDate)
                    ->where('status', 'pending')
                    ->count();
                if ($bookedPending >= (int) $slotLocked->max_orders) {
                    throw ValidationException::withMessages([
                        'delivery_slot_id' => 'Ese horario ya no tiene cupos disponibles.',
                    ]);
                }
                $slot = $slotLocked;
                $slot->loadMissing('zone:id,name');
            }

            $allocatedDiscount = 0.0;
            $orderItemsPayload = [];
            foreach ($lines as $idx => $line) {
                $share = $subtotal > 0 ? ($line['line_sub'] / $subtotal) * $discountAmount : 0.0;
                $lineDisc = ($idx === count($lines) - 1)
                    ? round($discountAmount - $allocatedDiscount, 2)
                    : round($share, 2);
                $allocatedDiscount = round($allocatedDiscount + $lineDisc, 2);
                $lineTotal = round($line['line_sub'] - $lineDisc, 2);
                $orderItemsPayload[] = [
                    'product' => $line['product'],
                    'quantity' => $line['quantity'],
                    'unit' => $line['unit'],
                    'line_sub' => $line['line_sub'],
                    'line_disc' => $lineDisc,
                    'line_total' => $lineTotal,
                ];
            }

            $paymentSnapshot = $paymentMethod->name."\n".(string) $paymentMethod->instructions;
            $deliverySnapshot = $deliveryMethod->name."\n".(string) $deliveryMethod->instructions;
            if ($deliveryMethod->isPickup() && $deliveryMethod->pickup_address) {
                $deliverySnapshot .= "\n".$deliveryMethod->pickup_address;
            }
            if ($zone) {
                $deliverySnapshot .= "\nZona: ".$zone->name;
            }
            if ($customLoc) {
                $deliverySnapshot .= "\n\nZona solicitada (otra — costo de envío por confirmar):\n".$customLoc;
                $deliverySnapshot .= "\nLa tienda te informará el costo del envío al leer tu pedido.";
            }
            if ($slot && $slotNextCarbon !== null) {
                $deliverySnapshot .= "\nVentana: ".$slot->weekdayLabelEs().' '.$slot->deliveryWindowLabelShort().' (próx. '.$slotNextCarbon->format('d/m/Y').')';
                if ($customLoc !== null && $slot->zone !== null) {
                    $deliverySnapshot .= "\n(franja asociada operativamente a: ".$slot->zone->name.')';
                }
            }

            $token = Order::generateSummaryToken();
            while (Order::query()->where('public_summary_token', $token)->exists()) {
                $token = Order::generateSummaryToken();
            }

            $ttlHours = (int) config('catalog.summary_link_ttl_hours', 168);

            $order = Order::query()->create([
                'company_id' => $company->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
                'company_payment_method_id' => $paymentMethod->id,
                'company_delivery_method_id' => $deliveryMethod->id,
                'delivery_zone_id' => $zone?->id,
                'delivery_custom_location' => $customLoc,
                'delivery_slot_id' => $slot?->id,
                'scheduled_delivery_date' => $scheduledDeliveryDate,
                'exchange_rate_used' => $rate,
                'subtotal_products_usd' => $subtotal,
                'payment_discount_percent_snapshot' => $discPercent,
                'payment_discount_amount_usd' => $discountAmount,
                'delivery_fee_usd' => $deliveryFee,
                'total_usd' => $totalUsd,
                'total_bs' => $totalBs,
                'public_summary_token' => $token,
                'public_summary_expires_at' => now()->addHours($ttlHours),
                'payment_method_snapshot' => $paymentSnapshot,
                'delivery_method_snapshot' => $deliverySnapshot,
            ]);

            foreach ($orderItemsPayload as $row) {
                /** @var Product $p */
                $p = $row['product'];
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'quantity' => $row['quantity'],
                    'unit_price_usd' => $row['unit'],
                    'line_subtotal_usd' => $row['line_sub'],
                    'line_discount_usd' => $row['line_disc'],
                    'line_total_usd' => $row['line_total'],
                ]);
                $p->decrement('stock', $row['quantity']);
            }

            $cart->items()->delete();

            $this->dispatchNewOrderNotifications($order);

            return $order->load('items');
        });
    }

    protected function dispatchNewOrderNotifications(Order $order): void
    {
        $users = User::query()
            ->permission('orders.index')
            ->where('company_id', $order->company_id)
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            Notification::query()->create([
                'user_id' => $user->id,
                'company_id' => $order->company_id,
                'order_id' => $order->id,
                'type' => 'new_order',
                'title' => 'Nuevo pedido desde catálogo',
                'message' => "Pedido #{$order->id} — {$order->customer_name}",
                'data' => [
                    'order_id' => $order->id,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'total_usd' => (string) $order->total_usd,
                ],
                'is_read' => false,
            ]);
        }
    }
}
