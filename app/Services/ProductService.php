<?php

namespace App\Services;

use App\Http\Controllers\ProductController;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $companyId, UploadedFile|TemporaryUploadedFile|null $image = null): Product
    {
        $storedPath = null;

        try {
            return DB::transaction(function () use ($data, $companyId, $image, &$storedPath) {
                $data['company_id'] = $companyId;

                if ($image !== null) {
                    $storedPath = $image->store('products', 'public');
                    $data['image'] = 'storage/'.$storedPath;
                }

                return Product::create($data);
            });
        } catch (\Throwable $e) {
            if ($storedPath !== null) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data, UploadedFile|TemporaryUploadedFile|null $image = null): void
    {
        $newStoredPath = null;
        $oldRelative = $product->image ? str_replace('storage/', '', $product->image) : null;

        try {
            DB::transaction(function () use ($product, $data, $image, &$newStoredPath, $oldRelative) {
                if ($image !== null) {
                    $newStoredPath = $image->store('products', 'public');
                    $data['image'] = 'storage/'.$newStoredPath;

                    if ($oldRelative && Storage::disk('public')->exists($oldRelative)) {
                        Storage::disk('public')->delete($oldRelative);
                    }
                }

                $product->update($data);
            });
        } catch (\Throwable $e) {
            if ($newStoredPath !== null) {
                Storage::disk('public')->delete($newStoredPath);
            }

            throw $e;
        }
    }

    /**
     * Elimina un producto si no tiene líneas de venta ni de compra (misma regla que {@see ProductController::destroy}).
     *
     * @return array{id:int,name:string,deleted:bool,reason:?string}
     */
    public function deleteProductWithResult(Product $product): array
    {
        if (! array_key_exists('sale_details_count', $product->getAttributes())) {
            $product->loadCount(['saleDetails', 'purchaseDetails']);
        }

        if ($product->sale_details_count > 0 || $product->purchase_details_count > 0) {
            $reasons = [];
            if ($product->sale_details_count > 0) {
                $reasons[] = 'tiene ventas asociadas';
            }
            if ($product->purchase_details_count > 0) {
                $reasons[] = 'tiene compras asociadas';
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'deleted' => false,
                'reason' => implode(' y ', $reasons),
            ];
        }

        try {
            DB::beginTransaction();

            if ($product->image && Storage::disk('public')->exists(str_replace('storage/', '', $product->image))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
            }

            $product->delete();

            DB::commit();

            return [
                'id' => $product->id,
                'name' => $product->name,
                'deleted' => true,
                'reason' => null,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * @param  array<int|string>  $productIds
     * @return array<int, array{id:int,name:string,deleted:bool,reason:?string}>
     */
    public function bulkDeleteProducts(int $companyId, array $productIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $productIds)));

        $products = Product::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $ids)
            ->withCount(['saleDetails', 'purchaseDetails'])
            ->orderBy('name')
            ->get();

        $results = [];

        foreach ($products as $product) {
            $results[] = $this->deleteProductWithResult($product);
        }

        return $results;
    }
}
