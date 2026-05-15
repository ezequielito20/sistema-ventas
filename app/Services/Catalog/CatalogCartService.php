<?php

namespace App\Services\Catalog;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogCartService
{
    public function cookieName(Company $company): string
    {
        return 'catalog_cart_'.$company->id;
    }

    public function resolveCartFromRequest(Request $request, Company $company): ?Cart
    {
        $token = $request->cookie($this->cookieName($company));
        if (! $token) {
            return null;
        }

        return Cart::query()
            ->where('company_id', $company->id)
            ->where('token', $token)
            ->first();
    }

    /**
     * @return array{cart: Cart, cookie: \Symfony\Component\HttpFoundation\Cookie}
     */
    public function resolveOrCreateCart(Request $request, Company $company): array
    {
        $cart = $this->resolveCartFromRequest($request, $company);
        if ($cart) {
            $cart->forceFill(['last_seen_at' => now()])->save();

            return ['cart' => $cart, 'cookie' => $this->makeCookie($company, $cart->token)];
        }

        $cart = Cart::query()->create([
            'company_id' => $company->id,
            'token' => (string) Str::uuid(),
            'last_seen_at' => now(),
        ]);

        return ['cart' => $cart, 'cookie' => $this->makeCookie($company, $cart->token)];
    }

    public function makeCookie(Company $company, string $token): \Symfony\Component\HttpFoundation\Cookie
    {
        $minutes = (int) config('catalog.cart_cookie_lifetime_minutes', 43200);

        return Cookie::make(
            $this->cookieName($company),
            $token,
            $minutes,
            '/',
            null,
            config('session.secure'),
            true,
            false,
            config('session.same_site') ?? 'lax'
        );
    }

    public function addOrUpdateItem(Cart $cart, int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($cart, $productId);

            return;
        }

        DB::transaction(function () use ($cart, $productId, $quantity): void {
            $item = CartItem::query()->firstOrNew([
                'cart_id' => $cart->id,
                'product_id' => $productId,
            ]);
            $item->quantity = $quantity;
            $item->save();
        });
    }

    public function removeItem(Cart $cart, int $productId): void
    {
        CartItem::query()->where('cart_id', $cart->id)->where('product_id', $productId)->delete();
    }

    public function lineCount(Cart $cart): int
    {
        return (int) $cart->items()->count();
    }

    public function itemsWithProducts(Cart $cart)
    {
        return $cart->items()->with('product.category')->get();
    }

    public function assertProductBelongsToCompany(Product $product, Company $company): void
    {
        if ((int) $product->company_id !== (int) $company->id) {
            abort(404);
        }
    }
}
