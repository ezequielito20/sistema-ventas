<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageUrlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixProductImages extends Command
{
    protected $signature = 'product:fix-images';
    protected $description = 'Fix broken product_images: mark covers and remove stale records';

    public function handle(): int
    {
        $disk = Storage::disk(ImageUrlService::getStorageDisk());

        $products = Product::with('images')->get();

        $fixed = 0;
        $removed = 0;

        foreach ($products as $product) {
            foreach ($product->images as $image) {
                $relative = str_replace('storage/', '', $image->image);

                if (! $disk->exists($relative)) {
                    $this->warn("Removing stale image #{$image->id} for product #{$product->id} (file not found: {$relative})");
                    $image->delete();
                    $removed++;
                }
            }

            $product->load('images');

            $hasCover = $product->images->contains('is_cover', true);

            if (! $hasCover && $product->images->isNotEmpty()) {
                $product->images->first()->update(['is_cover' => true]);
                $this->info("Marked image #{$product->images->first()->id} as cover for product \"{$product->name}\"");
                $fixed++;
            }
        }

        $this->newLine();
        $this->info("Done. Removed {$removed} stale images, fixed {$fixed} products with missing cover.");

        return self::SUCCESS;
    }
}
