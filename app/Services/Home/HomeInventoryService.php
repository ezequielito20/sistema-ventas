<?php

namespace App\Services\Home;

use App\Contracts\DeductResult;
use App\Events\Home\ProductConsumed;
use App\Models\Home\HomeProduct;
use App\Models\Home\HomeProductMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeInventoryService
{
    public function deduct(
        HomeProduct $product,
        int $quantity,
        string $type,
        ?array $metadata = null,
    ): DeductResult {
        return DB::transaction(function () use ($product, $quantity, $type, $metadata) {
            $fresh = $product->fresh();
            $fresh = $fresh ?? $product;

            if ($fresh->quantity < $quantity) {
                return DeductResult::insufficientStock($fresh->name, $fresh->quantity);
            }

            $fresh->decrement('quantity', $quantity);

            $movement = $fresh->movements()->create([
                'company_id' => $fresh->company_id,
                'user_id' => Auth::id(),
                'type' => $type,
                'quantity' => -$quantity,
                'metadata' => $metadata,
                'notes' => $metadata['notes'] ?? null,
            ]);

            Log::channel('home')->info('home.inventory.deduct', [
                'company_id' => $fresh->company_id,
                'product_id' => $fresh->id,
                'quantity' => $quantity,
                'type' => $type,
                'user_id' => Auth::id(),
            ]);

            ProductConsumed::dispatch($fresh->fresh(), $quantity, $type);

            return DeductResult::success($fresh->fresh(), $movement);
        });
    }

    public function addStock(
        HomeProduct $product,
        int $quantity,
        ?string $notes = null,
    ): HomeProductMovement {
        return DB::transaction(function () use ($product, $quantity, $notes) {
            $product->increment('quantity', $quantity);

            $movement = $product->movements()->create([
                'company_id' => $product->company_id,
                'user_id' => Auth::id(),
                'type' => 'in',
                'quantity' => $quantity,
                'notes' => $notes,
            ]);

            Log::channel('home')->info('home.inventory.add_stock', [
                'company_id' => $product->company_id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);

            return $movement;
        });
    }

    public function undo(HomeProductMovement $movement): void
    {
        DB::transaction(function () use ($movement) {
            $product = $movement->product;

            $reverseQuantity = abs($movement->quantity);
            $product->increment('quantity', $reverseQuantity);

            $product->movements()->create([
                'company_id' => $product->company_id,
                'user_id' => Auth::id(),
                'type' => 'undo',
                'quantity' => $reverseQuantity,
                'notes' => "Reversión de movimiento #{$movement->id}",
                'metadata' => json_encode(['reverted_movement_id' => $movement->id]),
            ]);

            Log::channel('home')->info('home.inventory.undo', [
                'company_id' => $product->company_id,
                'product_id' => $product->id,
                'movement_id' => $movement->id,
                'quantity' => $reverseQuantity,
            ]);
        });
    }
}
