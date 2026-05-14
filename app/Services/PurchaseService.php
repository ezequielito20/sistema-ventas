<?php

namespace App\Services;

use App\Models\CashCount;
use App\Models\CashMovement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseService
{
    /**
     * Reglas de validación para crear una compra.
     *
     * @return array<string, mixed>
     */
    public function rulesForCreate(): array
    {
        return [
            'purchase_date' => ['required', 'date'],
            'purchase_time' => ['required', 'date_format:H:i'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'in:fixed,percentage'],
            'general_discount_value' => ['nullable', 'numeric', 'min:0'],
            'general_discount_type' => ['nullable', 'in:fixed,percentage'],
        ];
    }

    /**
     * Mensajes de validación en español.
     *
     * @return array<string, string>
     */
    public function validationMessages(): array
    {
        return [
            'purchase_date.required' => 'La fecha de compra es obligatoria.',
            'purchase_time.required' => 'La hora de compra es obligatoria.',
            'items.required' => 'Debe agregar al menos un producto a la compra.',
            'items.min' => 'Debe agregar al menos un producto a la compra.',
            'items.*.product_id.required' => 'El producto es obligatorio.',
            'items.*.product_id.exists' => 'El producto seleccionado no existe.',
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.min' => 'La cantidad mínima es 1.',
            'items.*.price.required' => 'El precio unitario es obligatorio.',
            'items.*.price.min' => 'El precio unitario debe ser mayor o igual a 0.',
        ];
    }

    /**
     * Registra una nueva compra con sus ítems, actualiza stock y registra movimiento de caja.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPurchase(int $companyId, array $data): Purchase
    {
        $validated = Validator::make($data, $this->rulesForCreate(), $this->validationMessages())->validate();

        if ($user = Auth::user()) {
            app(PlanEntitlementService::class)->assertCanCreateDocumentOnDate(
                $user,
                'purchases',
                Carbon::parse($validated['purchase_date'])->format('Y-m-d')
            );
        }

        // Verificar caja abierta
        $currentCashCount = CashCount::where('company_id', $companyId)
            ->whereNull('closing_date')
            ->first();

        if (! $currentCashCount) {
            throw new \RuntimeException('No hay una caja abierta. Debe abrir una caja antes de realizar compras.');
        }

        $totalPrice = $this->calculateTotalAmount($validated['items'], $validated['general_discount_value'] ?? 0, $validated['general_discount_type'] ?? 'fixed');

        $paymentReceipt = str_replace('-', '', $validated['purchase_date'])
            .count($validated['items'])
            .str_pad((int) $totalPrice, 6, '0', STR_PAD_LEFT);

        return DB::transaction(function () use ($validated, $companyId, $currentCashCount, $totalPrice, $paymentReceipt) {
            $purchase = Purchase::create([
                'purchase_date' => $validated['purchase_date'].' '.$validated['purchase_time'],
                'payment_receipt' => $paymentReceipt,
                'total_price' => $totalPrice,
                'company_id' => $companyId,
                'general_discount_value' => $validated['general_discount_value'] ?? 0,
                'general_discount_type' => $validated['general_discount_type'] ?? 'fixed',
                'subtotal_before_discount' => $this->calculateSubtotal($validated['items']),
                'total_with_discount' => $totalPrice,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('company_id', $companyId)
                    ->firstOrFail();

                PurchaseDetail::create([
                    'quantity' => $item['quantity'],
                    'purchase_id' => $purchase->id,
                    'supplier_id' => $product->supplier_id,
                    'product_id' => $product->id,
                    'discount_value' => $item['discount_value'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'fixed',
                    'original_price' => $item['price'],
                    'final_price' => $this->calculateItemFinalPrice($item),
                ]);

                $product->stock += $item['quantity'];
                $product->save();
            }

            $currentCashCount->movements()->create([
                'type' => 'expense',
                'amount' => $totalPrice,
                'description' => 'Compra #'.$purchase->id,
            ]);

            return $purchase;
        });
    }

    /**
     * Actualiza una compra existente (edit).
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePurchase(int $companyId, int $purchaseId, array $data): Purchase
    {
        $validated = Validator::make($data, $this->rulesForCreate(), $this->validationMessages())->validate();

        return DB::transaction(function () use ($validated, $companyId, $purchaseId) {
            $purchase = Purchase::where('company_id', $companyId)->findOrFail($purchaseId);

            $totalPrice = $this->calculateTotalAmount($validated['items'], $validated['general_discount_value'] ?? 0, $validated['general_discount_type'] ?? 'fixed');

            if (isset($validated['purchase_time'])) {
                $purchase->purchase_date = $validated['purchase_date'].' '.$validated['purchase_time'];
            } else {
                $purchase->purchase_date = $validated['purchase_date'];
            }
            $purchase->total_price = $totalPrice;
            $purchase->general_discount_value = $validated['general_discount_value'] ?? 0;
            $purchase->general_discount_type = $validated['general_discount_type'] ?? 'fixed';
            $purchase->subtotal_before_discount = $this->calculateSubtotal($validated['items']);
            $purchase->total_with_discount = $totalPrice;
            $purchase->save();

            $currentDetailIds = $purchase->details()->pluck('id')->toArray();
            $newDetailIds = [];

            foreach ($validated['items'] as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('company_id', $companyId)
                    ->firstOrFail();

                $detail = PurchaseDetail::where('purchase_id', $purchase->id)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if ($detail) {
                    $stockDifference = $item['quantity'] - $detail->quantity;
                    $product->stock += $stockDifference;

                    $detail->update([
                        'quantity' => $item['quantity'],
                        'discount_value' => $item['discount_value'] ?? 0,
                        'discount_type' => $item['discount_type'] ?? 'fixed',
                        'original_price' => $item['price'],
                        'final_price' => $this->calculateItemFinalPrice($item),
                    ]);
                    $newDetailIds[] = $detail->id;
                } else {
                    $newDetail = PurchaseDetail::create([
                        'quantity' => $item['quantity'],
                        'purchase_id' => $purchase->id,
                        'supplier_id' => $product->supplier_id,
                        'product_id' => $product->id,
                        'discount_value' => $item['discount_value'] ?? 0,
                        'discount_type' => $item['discount_type'] ?? 'fixed',
                        'original_price' => $item['price'],
                        'final_price' => $this->calculateItemFinalPrice($item),
                    ]);
                    $product->stock += $item['quantity'];
                    $newDetailIds[] = $newDetail->id;
                }
                $product->save();
            }

            // Eliminar detalles removidos
            $detailsToDelete = array_diff($currentDetailIds, $newDetailIds);
            foreach ($detailsToDelete as $detailId) {
                $detail = PurchaseDetail::find($detailId);
                if ($detail) {
                    $product = Product::find($detail->product_id);
                    if ($product) {
                        $product->stock -= $detail->quantity;
                        $product->save();
                    }
                    $detail->delete();
                }
            }

            return $purchase;
        });
    }

    /**
     * Calcula el subtotal (suma de precio × cantidad) antes de descuento general.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    public function calculateSubtotal(array $items): float
    {
        return round(
            collect($items)->sum(function ($item) {
                $price = (float) ($item['price'] ?? 0);
                $qty = (float) ($item['quantity'] ?? 0);

                return $price * $qty;
            }),
            2
        );
    }

    /**
     * Calcula el total final aplicando descuento general.
     */
    public function calculateTotalAmount(array $items, float $generalDiscountValue, string $generalDiscountType): float
    {
        $subtotal = $this->calculateSubtotal($items);

        if ($generalDiscountValue <= 0) {
            return $subtotal;
        }

        $discount = $generalDiscountType === 'percentage'
            ? $subtotal * ($generalDiscountValue / 100)
            : $generalDiscountValue;

        return round(max(0, $subtotal - $discount), 2);
    }

    /**
     * Calcula el precio final de un ítem aplicando su descuento individual.
     */
    public function calculateItemFinalPrice(array $item): float
    {
        $price = (float) ($item['price'] ?? 0);
        $discountValue = (float) ($item['discount_value'] ?? 0);
        $discountType = $item['discount_type'] ?? 'fixed';

        if ($discountValue <= 0) {
            return $price;
        }

        $discount = $discountType === 'percentage'
            ? $price * ($discountValue / 100)
            : $discountValue;

        return round(max(0, $price - $discount), 2);
    }

    /**
     * Calcula el subtotal de un ítem (precio final × cantidad).
     */
    public function calculateItemSubtotal(array $item): float
    {
        $finalPrice = $this->calculateItemFinalPrice($item);

        return round($finalPrice * (float) ($item['quantity'] ?? 0), 2);
    }

    /**
     * Elimina una compra si no genera stock negativo.
     *
     * @return array{success: bool, message: string, type: string, products_affected?: array}
     */
    public function deletePurchase(int $companyId, int $purchaseId): array
    {
        $purchase = Purchase::where('company_id', $companyId)->findOrFail($purchaseId);

        return DB::transaction(function () use ($companyId, $purchase) {
            // Verificar stock negativo
            $productsWithNegativeStock = [];
            foreach ($purchase->details as $detail) {
                $product = $detail->product;
                $newStock = $product->stock - $detail->quantity;
                if ($newStock < 0) {
                    $productsWithNegativeStock[] = [
                        'name' => $product->name,
                        'current_stock' => $product->stock,
                        'quantity_to_remove' => $detail->quantity,
                        'new_stock' => $newStock,
                    ];
                }
            }

            if (! empty($productsWithNegativeStock)) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar: algunos productos quedarían con stock negativo.',
                    'type' => 'warning',
                    'products_affected' => $productsWithNegativeStock,
                ];
            }

            // Revertir stock
            foreach ($purchase->details as $detail) {
                $product = $detail->product;
                $product->stock -= $detail->quantity;
                $product->save();
            }

            // Eliminar movimientos de caja asociados
            CashMovement::where('description', 'Compra #'.$purchase->id)
                ->whereHas('cashCount', fn ($q) => $q->where('company_id', $companyId))
                ->delete();

            $purchase->delete();

            return [
                'success' => true,
                'message' => '¡Compra eliminada exitosamente!',
                'type' => 'success',
            ];
        });
    }

    /**
     * Borrado masivo de compras.
     *
     * @param  array<int>  $purchaseIds
     * @return array<int, array{id:int,name:string,deleted:bool,reason:?string}>
     */
    public function bulkDeletePurchases(int $companyId, array $purchaseIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $purchaseIds)));
        $purchases = Purchase::where('company_id', $companyId)
            ->whereIn('id', $ids)
            ->with('details.product')
            ->orderBy('id')
            ->get();

        $results = [];
        foreach ($purchases as $purchase) {
            $result = $this->deletePurchase($companyId, $purchase->id);
            $results[] = [
                'id' => $purchase->id,
                'name' => 'Compra #'.$purchase->id,
                'deleted' => $result['success'],
                'reason' => $result['success'] ? null : $result['message'],
            ];
        }

        return $results;
    }
}
