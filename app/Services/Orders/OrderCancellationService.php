<?php

namespace App\Services\Orders;

use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderCancellationService
{
    public function cancelPending(Order $order, ?int $processedByUserId = null): void
    {
        if ($order->status !== 'pending') {
            throw ValidationException::withMessages([
                'order' => 'Solo se pueden cancelar pedidos pendientes.',
            ]);
        }

        DB::transaction(function () use ($order, $processedByUserId): void {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->first();
            if (! $order || $order->status !== 'pending') {
                throw ValidationException::withMessages([
                    'order' => 'Solo se pueden cancelar pedidos pendientes.',
                ]);
            }

            $order->load(['items']);

            foreach ($order->items as $item) {
                $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            $order->forceFill([
                'status' => 'cancelled',
                'processed_at' => now(),
                'processed_by' => $processedByUserId,
            ])->save();

            Notification::query()->where('order_id', $order->id)->delete();
        });
    }
}
