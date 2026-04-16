<?php

namespace App\Services;

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
}
