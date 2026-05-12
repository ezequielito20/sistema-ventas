<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;

class PublicCatalogController extends Controller
{
    /**
     * Display the public product catalog for a company.
     */
    public function index(Company $company)
    {
        if (! $company->catalog_is_public) {
            abort(404);
        }

        // Cargar productos directamente de la empresa (no a través de categorías)
        $products = $company->products()
            ->with([
                'category',
                'images' => function ($q) {
                    $q->orderBy('sort_order');
                },
            ])
            ->where(function ($q) {
                $q->where('stock', '>', 0)->orWhereNull('stock');
            })
            ->orderBy('name')
            ->get();

        // Agregar category_name a cada producto
        $products->each(function ($product) {
            $product->category_name = $product->category?->name ?? 'Sin categoría';
        });

        // Calcular conteo de productos por categoría
        $categoryCounts = $products->groupBy('category_id')->map->count();

        // Cargar categorías únicas que tienen al menos un producto visible (para los filtros)
        $categoryIds = $products->pluck('category_id')->filter()->unique();
        $categories = Category::whereIn('id', $categoryIds)->orderBy('name')->get()
            ->each(function ($cat) use ($categoryCounts) {
                $cat->product_count = $categoryCounts[$cat->id] ?? 0;
            });

        return view('catalog.index', [
            'company' => $company,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * Display a single product detail page.
     */
    public function show(Company $company, Product $product)
    {
        // If catalog is not public, return 404
        if (! $company->catalog_is_public) {
            abort(404);
        }

        // Verify product belongs to this company
        if ($product->company_id !== $company->id) {
            abort(404);
        }

        // Load product with all images and category
        $product->load([
            'images' => function ($q) {
                $q->orderBy('sort_order');
            },
            'category',
        ]);

        // Related products: same category, stock > 0 or NULL, different from current
        $relatedProducts = Product::where('company_id', $company->id)
            ->where('category_id', $product->category_id)
            ->where(function ($q) {
                $q->where('stock', '>', 0)->orWhereNull('stock');
            })
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
        ]);
    }
}
