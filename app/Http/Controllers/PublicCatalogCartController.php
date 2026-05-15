<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Services\Catalog\CatalogCartService;
use App\Support\CatalogAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicCatalogCartController extends Controller
{
    public function show(Request $request, Company $company, CatalogCartService $carts)
    {
        CatalogAccess::assert($request, $company);

        $cart = $carts->resolveCartFromRequest($request, $company);
        if (! $cart) {
            return response()->json([
                'items' => [],
                'line_count' => 0,
                'quantity_total' => 0,
                'subtotal_usd' => 0,
            ]);
        }

        $items = $carts->itemsWithProducts($cart)->map(function ($row) use ($company) {
            $p = $row->product;
            if (! $p || (int) $p->company_id !== (int) $company->id || ! $p->isVisibleInPublicCatalog()) {
                return null;
            }

            return [
                'product_id' => $p->id,
                'name' => $p->name,
                'quantity' => $row->quantity,
                'stock' => (int) $p->stock,
                'unit_price_usd' => (float) $p->final_price,
                'line_total_usd' => round((float) $p->final_price * $row->quantity, 2),
            ];
        })->filter()->values();

        $subtotal = round((float) $items->sum('line_total_usd'), 2);

        return response()->json([
            'items' => $items,
            'line_count' => $items->count(),
            'quantity_total' => (int) $items->sum('quantity'),
            'subtotal_usd' => $subtotal,
        ]);
    }

    public function sync(Request $request, Company $company, CatalogCartService $carts)
    {
        CatalogAccess::assert($request, $company);

        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:0|max:9999',
        ]);

        $product = Product::query()->findOrFail($data['product_id']);
        $carts->assertProductBelongsToCompany($product, $company);
        if (! $product->isVisibleInPublicCatalog()) {
            throw ValidationException::withMessages(['product_id' => 'Producto no disponible en catálogo.']);
        }

        $resolved = $carts->resolveOrCreateCart($request, $company);
        $cart = $resolved['cart'];

        if ((int) $data['quantity'] > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Stock insuficiente. Disponible: '.$product->stock,
            ]);
        }

        $carts->addOrUpdateItem($cart, $product->id, (int) $data['quantity']);

        return $this->show($request, $company, $carts)->withCookie($resolved['cookie']);
    }

    public function remove(Request $request, Company $company, int $product, CatalogCartService $carts)
    {
        CatalogAccess::assert($request, $company);

        $resolved = $carts->resolveOrCreateCart($request, $company);
        $cart = $resolved['cart'];
        $carts->removeItem($cart, $product);

        return $this->show($request, $company, $carts)->withCookie($resolved['cookie']);
    }
}
