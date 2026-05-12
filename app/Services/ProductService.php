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
     * @param  array<UploadedFile|TemporaryUploadedFile>  $galleryImages
     */
    public function create(array $data, int $companyId, UploadedFile|TemporaryUploadedFile|null $image = null, array $galleryImages = []): Product
    {
        $storedPath = null;
        $galleryPaths = [];

        try {
            return DB::transaction(function () use ($data, $companyId, $image, $galleryImages, &$storedPath, &$galleryPaths) {
                $data['company_id'] = $companyId;
                $disk = ImageUrlService::getStorageDisk();

                if ($image !== null) {
                    $storedPath = $image->store('products', $disk);
                    $data['image'] = 'storage/'.$storedPath;
                }

                $product = Product::create($data);

                if ($image !== null) {
                    $product->images()->create([
                        'image' => $data['image'],
                        'sort_order' => 0,
                        'is_cover' => true,
                    ]);
                }

                foreach ($galleryImages as $index => $galleryImage) {
                    $galleryPath = $galleryImage->store('products', $disk);
                    $galleryPaths[] = $galleryPath;
                    $product->images()->create([
                        'image' => 'storage/'.$galleryPath,
                        'sort_order' => $index + 1,
                        'is_cover' => false,
                    ]);
                }

                return $product;
            });
        } catch (\Throwable $e) {
            if ($storedPath !== null) {
                Storage::disk('public')->delete($storedPath);
            }
            foreach ($galleryPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<UploadedFile|TemporaryUploadedFile>  $galleryImages
     * @param  array<int>  $imageDeletions
     */
    public function update(Product $product, array $data, UploadedFile|TemporaryUploadedFile|null $image = null, array $galleryImages = [], array $imageDeletions = [], ?int $coverImageId = null): void
    {
        $newStoredPath = null;
        $galleryPaths = [];
        $oldRelative = $product->image ? str_replace('storage/', '', $product->image) : null;

        try {
            DB::transaction(function () use ($product, $data, $image, $galleryImages, $imageDeletions, $coverImageId, &$newStoredPath, &$galleryPaths, $oldRelative) {
                $disk = ImageUrlService::getStorageDisk();

                if ($image !== null) {
                    $newStoredPath = $image->store('products', $disk);
                    $data['image'] = 'storage/'.$newStoredPath;

                    if ($oldRelative && Storage::disk($disk)->exists($oldRelative)) {
                        Storage::disk($disk)->delete($oldRelative);
                    }
                }

                $product->update($data);

                if (! empty($imageDeletions)) {
                    $deletedImages = $product->images()->whereIn('id', $imageDeletions)->get();
                    foreach ($deletedImages as $img) {
                        $relative = str_replace('storage/', '', $img->image);
                        if (Storage::disk($disk)->exists($relative)) {
                            Storage::disk($disk)->delete($relative);
                        }
                    }
                    $product->images()->whereIn('id', $imageDeletions)->delete();
                }

                if ($image !== null) {
                    $product->images()->where('is_cover', true)->update(['is_cover' => false]);

                    $product->images()->create([
                        'image' => $data['image'],
                        'sort_order' => 0,
                        'is_cover' => true,
                    ]);
                } elseif ($coverImageId !== null) {
                    $product->images()->where('id', '!=', $coverImageId)->update(['is_cover' => false]);
                    $product->images()->where('id', $coverImageId)->update(['is_cover' => true]);

                    $coverImg = $product->images()->find($coverImageId);
                    if ($coverImg) {
                        $product->update(['image' => $coverImg->image]);
                    }
                }

                $maxSort = $product->images()->max('sort_order') ?? 0;
                foreach ($galleryImages as $index => $galleryImage) {
                    $galleryPath = $galleryImage->store('products', $disk);
                    $galleryPaths[] = $galleryPath;
                    $product->images()->create([
                        'image' => 'storage/'.$galleryPath,
                        'sort_order' => $maxSort + $index + 1,
                        'is_cover' => false,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            if ($newStoredPath !== null) {
                Storage::disk('public')->delete($newStoredPath);
            }
            foreach ($galleryPaths as $path) {
                Storage::disk('public')->delete($path);
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

            if ($product->image) {
                $relative = str_replace('storage/', '', $product->image);
                $diskName = ImageUrlService::getStorageDisk();
                if (Storage::disk($diskName)->exists($relative)) {
                    Storage::disk($diskName)->delete($relative);
                }
            }

            foreach ($product->images as $img) {
                $relative = str_replace('storage/', '', $img->image);
                $diskName = ImageUrlService::getStorageDisk();
                if ($relative && Storage::disk($diskName)->exists($relative)) {
                    Storage::disk($diskName)->delete($relative);
                }
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
