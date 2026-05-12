<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;

class PublicCatalogController extends Controller
{
    /**
     * Display the public product catalog for a company.
     */
    public function index(Company $company)
    {
        // If catalog is not public, return 404
        if (!$company->catalog_is_public) {
            abort(404);
        }

        // Load categories that have products in stock
        $categories = $company->categories()
            ->whereHas('products', function ($query) {
                $query->where('stock', '>', 0);
            })
            ->with(['products' => function ($query) {
                $query->where('stock', '>', 0)
                    ->orderBy('name')
                    ->with(['images' => function ($q) {
                        $q->orderBy('sort_order')->limit(1);
                    }]);
            }])
            ->orderBy('name')
            ->get();

        // Build a flat list of all products with their category for the view
        $allProducts = collect();
        foreach ($categories as $category) {
            foreach ($category->products as $product) {
                $product->category_name = $category->name;
                $allProducts->push($product);
            }
        }

        return view('catalog.index', [
            'company' => $company,
            'categories' => $categories,
            'products' => $allProducts,
        ]);
    }

    /**
     * Display a single product detail page.
     */
    public function show(Company $company, Product $product)
    {
        // If catalog is not public, return 404
        if (!$company->catalog_is_public) {
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

        // Related products: same category, stock > 0, different from current
        $relatedProducts = Product::where('company_id', $company->id)
            ->where('category_id', $product->category_id)
            ->where('stock', '>', 0)
            ->where('id', '!=', $product->id)
            ->with(['images' => function ($q) {
                $q->orderBy('sort_order')->limit(1);
            }])
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
