<?php

namespace App\Services\Orders;

use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class OrderAdminService
{
    public function __construct(
        protected OrderCancellationService $orderCancellation,
    ) {}

    public function markPaid(Order $order, User $user): void
    {
        $this->assertCompany($order, $user);
        if ($order->status !== 'pending') {
            throw ValidationException::withMessages(['order' => 'Solo se pueden marcar pedidos pendientes.']);
        }
        $order->forceFill([
            'paid_at' => $order->paid_at ?? now(),
            'processed_by' => $user->id,
        ])->save();
        $this->syncProcessedState($order);
    }

    public function markDelivered(Order $order, User $user): void
    {
        $this->assertCompany($order, $user);
        if ($order->status !== 'pending') {
            throw ValidationException::withMessages(['order' => 'Solo se pueden marcar pedidos pendientes.']);
        }
        $order->forceFill([
            'delivered_at' => $order->delivered_at ?? now(),
            'processed_by' => $user->id,
        ])->save();
        $this->syncProcessedState($order);
    }

    public function cancel(Order $order, User $user): void
    {
        $this->assertCompany($order, $user);
        if ($order->status !== 'pending') {
            throw ValidationException::withMessages(['order' => 'Solo se pueden cancelar pedidos pendientes.']);
        }

        $this->orderCancellation->cancelPending($order, $user->id);
    }

    public function regenerateSummaryLink(Order $order, User $user): void
    {
        $this->assertCompany($order, $user);
        $ttlHours = (int) config('catalog.summary_link_ttl_hours', 168);
        do {
            $token = Order::generateSummaryToken();
        } while (Order::query()->where('public_summary_token', $token)->where('id', '!=', $order->id)->exists());

        $order->forceFill([
            'public_summary_token' => $token,
            'public_summary_expires_at' => now()->addHours($ttlHours),
        ])->save();
    }

    protected function assertCompany(Order $order, User $user): void
    {
        if ((int) $user->company_id !== (int) $order->company_id) {
            abort(403);
        }
    }

    protected function syncProcessedState(Order $order): void
    {
        $order->refresh();
        if ($order->paid_at && $order->delivered_at && $order->status === 'processed') {
            Notification::query()->where('order_id', $order->id)->delete();
        }
    }
}
