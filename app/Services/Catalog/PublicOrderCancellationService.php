<?php

namespace App\Services\Catalog;

use App\Models\Order;
use App\Services\Orders\OrderCancellationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PublicOrderCancellationService
{
    public function __construct(
        protected OrderCancellationService $orderCancellation,
    ) {}

    public function cancelBySummaryToken(string $token, string $phoneInput): Order
    {
        $order = Order::query()
            ->where('public_summary_token', $token)
            ->first();

        if (! $order) {
            throw ValidationException::withMessages([
                'token' => 'El enlace de resumen no es válido.',
            ]);
        }

        if ($order->public_summary_expires_at && $order->public_summary_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'token' => 'El enlace de resumen ha expirado.',
            ]);
        }

        if (! $order->canBeCancelledByCustomer()) {
            throw ValidationException::withMessages([
                'order' => $order->customerCancelBlockedReason() ?? 'Este pedido ya no puede cancelarse desde el enlace.',
            ]);
        }

        $phoneDigits = preg_replace('/\D+/', '', $phoneInput);
        Validator::make(
            ['customer_phone' => $phoneDigits],
            ['customer_phone' => ['required', 'digits:11']],
            [
                'customer_phone.required' => 'Ingresá el teléfono del pedido para confirmar la cancelación.',
                'customer_phone.digits' => 'El teléfono debe tener exactamente 11 dígitos numéricos.',
            ]
        )->validate();

        if ($phoneDigits !== $order->customer_phone) {
            throw ValidationException::withMessages([
                'customer_phone' => 'El teléfono no coincide con el del pedido.',
            ]);
        }

        $this->orderCancellation->cancelPending($order, null);

        return $order->fresh(['company', 'items']);
    }
}
