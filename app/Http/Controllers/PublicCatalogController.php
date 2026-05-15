<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Services\ImageUrlService;
use App\Support\CatalogAccess;
use App\Support\CatalogUrlGenerator;
use Illuminate\Http\Request;

class PublicCatalogController extends Controller
{
    /**
     * Display the public product catalog for a company.
     */
    public function index(Request $request, Company $company)
    {
        CatalogAccess::assert($request, $company);

        $products = $company->products()
            ->with([
                'category',
                'images' => function ($q) {
                    $q->orderBy('sort_order');
                },
            ])
            ->visibleInPublicCatalog()
            ->orderBy('name')
            ->get();

        $products->each(function ($product) {
            $product->category_name = $product->category?->name ?? 'Sin categoría';
        });

        $categoryCounts = $products->groupBy('category_id')->map->count();

        $categoryIds = $products->pluck('category_id')->filter()->unique();
        $categories = Category::whereIn('id', $categoryIds)->orderBy('name')->get()
            ->each(function ($cat) use ($categoryCounts) {
                $cat->product_count = $categoryCounts[$cat->id] ?? 0;
            });

        return view('catalog.index', [
            'company' => $company,
            'categories' => $categories,
            'products' => $products,
            'catalogCartUrls' => [
                'get' => CatalogUrlGenerator::cartShow($company),
                'sync' => CatalogUrlGenerator::cartSync($company),
                'checkout' => CatalogUrlGenerator::checkout($company),
            ],
        ]);
    }

    /**
     * Display a single product detail page.
     */
    public function show(Request $request, Company $company, Product $product)
    {
        CatalogAccess::assert($request, $company);

        if ($product->company_id !== $company->id) {
            abort(404);
        }

        if (! $product->isVisibleInPublicCatalog()) {
            abort(404);
        }

        $product->load([
            'images' => function ($q) {
                $q->orderBy('sort_order');
            },
            'category',
        ]);

        $relatedProducts = Product::where('company_id', $company->id)
            ->where('category_id', $product->category_id)
            ->visibleInPublicCatalog()
            ->where('id', '!=', $product->id)
            ->with([
                'category',
                'images' => function ($q) {
                    $q->orderBy('sort_order')->limit(1);
                },
            ])
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('catalog.show', [
            'company' => $company,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'catalogCartUrls' => [
                'get' => CatalogUrlGenerator::cartShow($company),
                'sync' => CatalogUrlGenerator::cartSync($company),
                'checkout' => CatalogUrlGenerator::checkout($company),
            ],
        ]);
    }

    /**
     * Logo recortado para Open Graph / WhatsApp: ancho &lt; 300px muestra miniatura compacta a la izquierda.
     *
     * @see https://developers.facebook.com/docs/sharing/webmasters/images/
     */
    public function ogLogo(Request $request, Company $company)
    {
        CatalogAccess::assert($request, $company);

        if (! $company->logo) {
            return $this->catalogOgFallbackPng();
        }

        $rawLogo = trim((string) $company->logo);
        if (preg_match('#^https?://#i', $rawLogo)) {
            return redirect(ImageUrlService::absolutePublicUrl($rawLogo), 302);
        }

        $path = ImageUrlService::normalizeStoredPath($company->logo);
        if ($path === '') {
            return $this->catalogOgFallbackPng();
        }

        $served = ImageUrlService::serve($path);
        if (! $served) {
            return $this->catalogOgFallbackPng();
        }

        $mime = strtolower((string) $served['mime']);
        $content = $served['content'];
        $maxDimension = 280;

        if (! extension_loaded('gd') || ! $this->ogLogoResizeSupportsMime($mime)) {
            return response($content, 200, [
                'Content-Type' => $served['mime'],
                'Cache-Control' => 'public, max-age=604800',
            ]);
        }

        $image = @imagecreatefromstring($content);
        if ($image === false) {
            return response($content, 200, [
                'Content-Type' => $served['mime'],
                'Cache-Control' => 'public, max-age=604800',
            ]);
        }

        $w = imagesx($image);
        $h = imagesy($image);
        if ($w <= 0 || $h <= 0) {
            imagedestroy($image);

            return response($content, 200, [
                'Content-Type' => $served['mime'],
                'Cache-Control' => 'public, max-age=604800',
            ]);
        }

        if ($w > $maxDimension || $h > $maxDimension) {
            if ($w >= $h) {
                $newW = $maxDimension;
                $newH = (int) max(1, round($h * ($maxDimension / $w)));
            } else {
                $newH = $maxDimension;
                $newW = (int) max(1, round($w * ($maxDimension / $h)));
            }
            $scaled = imagescale($image, $newW, $newH);
            if ($scaled !== false) {
                imagedestroy($image);
                $image = $scaled;
            }
        }

        if (imageistruecolor($image)) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        ob_start();
        imagepng($image);
        imagedestroy($image);
        $png = ob_get_clean();

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    protected function ogLogoResizeSupportsMime(string $mime): bool
    {
        return in_array($mime, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ], true);
    }

    /**
     * PNG neutro pequeño cuando no hay logo (WhatsApp suele ignorar SVG en og:image).
     */
    protected function catalogOgFallbackPng()
    {
        if (! extension_loaded('gd')) {
            return redirect(ImageUrlService::absolutePublicUrl(
                ImageUrlService::getImageUrl(null)
            ), 302);
        }

        $w = 200;
        $h = 200;
        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $transparent);
        ob_start();
        imagepng($im);
        imagedestroy($im);
        $png = ob_get_clean();

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
