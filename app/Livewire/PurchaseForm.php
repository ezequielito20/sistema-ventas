<?php

namespace App\Livewire;

use App\Livewire\Concerns\MergesValidationErrors;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Services\PurchaseService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseForm extends Component
{
    use MergesValidationErrors;
    use WithPagination;

    // ─── Modo (create / edit) ─────────────────────────────
    public ?int $purchaseId = null;

    // ─── Datos de la compra ───────────────────────────────
    public string $purchase_date;

    public string $purchase_time;

    // ─── Items (productos en la compra) ──────────────────
    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    // ─── Descuento general ───────────────────────────────
    public string $general_discount_value = '0';

    public string $general_discount_type = 'fixed'; // 'fixed' | 'percentage'

    // ─── Escaneo por código ──────────────────────────────
    public string $product_code = '';

    // ─── Modal de búsqueda de productos ──────────────────
    public bool $show_product_modal = false;

    public string $modal_search = '';

    // ─── Refs para redirección ───────────────────────────
    protected ?string $referrerUrl = null;

    public function mount(?int $purchaseId = null): void
    {
        $this->purchaseId = $purchaseId;
        $this->captureReferrer();

        if ($this->purchaseId !== null) {
            Gate::authorize('purchases.edit');
            $this->loadPurchase();

            return;
        }

        Gate::authorize('purchases.create');
        $this->purchase_date = now()->format('Y-m-d');
        $this->purchase_time = now()->format('H:i');

        // ── Auto-add single product (legacy business rule) ──
        $productCount = Product::where('company_id', Auth::user()->company_id)->count();
        if ($productCount === 1 && empty($this->items)) {
            $product = Product::where('company_id', Auth::user()->company_id)->first();
            if ($product) {
                $this->items[] = [
                    'product_id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'image_url' => $product->image_url,
                    'stock' => (int) $product->stock,
                    'quantity' => 1,
                    'price' => (float) $product->purchase_price,
                    'discount_value' => 0,
                    'discount_type' => 'fixed',
                ];
                $this->js("window.uiNotifications?.showToast?.('".addslashes($product->name)." se agregó automáticamente (único producto en inventario)', {type:'info', title:'Atención', timeout:6000, theme:'futuristic'})");
            }
        }
    }

    protected function loadPurchase(): void
    {
        $purchase = Purchase::with(['details.product', 'details.product.category'])
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $this->purchaseId)
            ->firstOrFail();

        $this->purchase_date = $purchase->purchase_date->format('Y-m-d');
        $this->purchase_time = $purchase->purchase_date->format('H:i');
        $this->general_discount_value = (string) $purchase->general_discount_value;
        $this->general_discount_type = $purchase->general_discount_type ?? 'fixed';

        $this->items = $purchase->details->map(function (PurchaseDetail $detail) {
            return [
                'product_id' => $detail->product_id,
                'code' => $detail->product->code ?? '',
                'name' => $detail->product->name ?? '',
                'image_url' => $detail->product->image_url ?? '',
                'stock' => (int) ($detail->product->stock ?? 0),
                'quantity' => (int) $detail->quantity,
                'price' => (float) $detail->original_price,
                'discount_value' => (float) $detail->discount_value,
                'discount_type' => $detail->discount_type ?? 'fixed',
            ];
        })->values()->all();
    }

    // ─── COMPUTED PROPERTIES ──────────────────────────────

    public function getTotalProductsProperty(): int
    {
        return count($this->items);
    }

    public function getTotalQuantityProperty(): int
    {
        return (int) collect($this->items)->sum(fn ($i) => (int) ($i['quantity'] ?? 0));
    }

    public function getSubtotalProperty(): float
    {
        $service = app(PurchaseService::class);

        return $service->calculateSubtotal($this->items);
    }

    public function getTotalAmountProperty(): float
    {
        $service = app(PurchaseService::class);

        return $service->calculateTotalAmount(
            $this->items,
            (float) $this->general_discount_value,
            $this->general_discount_type
        );
    }

    public function getItemSubtotal(int $index): float
    {
        if (! isset($this->items[$index])) {
            return 0;
        }
        $service = app(PurchaseService::class);

        return $service->calculateItemSubtotal($this->items[$index]);
    }

    // ─── ACCIONES DE PRODUCTOS ──────────────────────────

    /**
     * Agrega un producto desde el escaneo por código (llamado desde Alpine vía $wire).
     */
    public function addProductFromScan(int $productId, string $code, string $name, string $imageUrl, int $stock, float $price): void
    {
        if ($this->hasProduct($productId)) {
            return; // Ya está en la lista, Alpine muestra el toast
        }

        $this->items[] = [
            'product_id' => $productId,
            'code' => $code,
            'name' => $name,
            'image_url' => $imageUrl,
            'stock' => $stock,
            'quantity' => 1,
            'price' => $price,
            'discount_value' => 0,
            'discount_type' => 'fixed',
        ];
    }

    /**
     * Agrega un producto desde el modal de búsqueda.
     */
    public function addProductFromModal(int $productId): void
    {
        if ($this->hasProduct($productId)) {
            return;
        }

        $product = Product::with('category')
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $productId)
            ->first();

        if (! $product) {
            return;
        }

        $this->items[] = [
            'product_id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'image_url' => $product->image_url,
            'stock' => (int) $product->stock,
            'quantity' => 1,
            'price' => (float) $product->purchase_price,
            'discount_value' => 0,
            'discount_type' => 'fixed',
        ];
    }

    /**
     * Elimina un producto de la lista.
     */
    public function removeProduct(int $index): void
    {
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    /**
     * Actualiza la cantidad de un ítem.
     */
    public function updateItemQuantity(int $index, mixed $value): void
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['quantity'] = max(1, (int) $value);
        }
    }

    /**
     * Actualiza el precio unitario de un ítem.
     */
    public function updateItemPrice(int $index, float $value): void
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['price'] = max(0, $value);
        }
    }

    /**
     * Actualiza el descuento de un ítem.
     */
    public function updateItemDiscount(int $index, float $value): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $item = &$this->items[$index];
        $discountType = $item['discount_type'] ?? 'fixed';

        if ($discountType === 'percentage' && $value > 100) {
            $value = 100;
        }
        if ($discountType === 'fixed' && $value > ($item['price'] ?? 0)) {
            $value = $item['price'];
        }

        $item['discount_value'] = max(0, $value);
    }

    /**
     * Alterna el tipo de descuento de un ítem entre fijo y porcentaje.
     */
    public function toggleItemDiscountType(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $this->items[$index]['discount_type'] =
            ($this->items[$index]['discount_type'] ?? 'fixed') === 'percentage'
            ? 'fixed'
            : 'percentage';
    }

    /**
     * Alterna el tipo de descuento general entre fijo y porcentaje.
     */
    public function toggleGeneralDiscountType(): void
    {
        $this->general_discount_type =
            $this->general_discount_type === 'percentage' ? 'fixed' : 'percentage';
    }

    // ─── MODAL DE PRODUCTOS ────────────────────────────

    public function openProductModal(): void
    {
        $this->modal_search = '';
        $this->resetPage('modalPage');
        $this->show_product_modal = true;
    }

    public function closeProductModal(): void
    {
        $this->show_product_modal = false;
        $this->modal_search = '';
        $this->resetPage('modalPage');
    }

    public function updatedModalSearch(): void
    {
        $this->resetPage('modalPage');
    }

    // ─── VALIDACIÓN ────────────────────────────────────

    protected function rules(): array
    {
        $rules = app(PurchaseService::class)->rulesForCreate();

        // Validación adicional: verificar que los productos pertenecen a la empresa
        $companyId = (int) Auth::user()->company_id;
        $rules['items.*.product_id'] = [
            'required', 'integer',
            function ($attribute, $value, $fail) use ($companyId) {
                if (! Product::where('id', $value)->where('company_id', $companyId)->exists()) {
                    $fail('El producto seleccionado no pertenece a tu empresa.');
                }
            },
        ];

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'purchase_date' => 'fecha de compra',
            'purchase_time' => 'hora de compra',
            'items' => 'productos',
            'general_discount_value' => 'descuento general',
        ];
    }

    protected function messages(): array
    {
        return app(PurchaseService::class)->validationMessages();
    }

    // ─── PERSISTENCIA ──────────────────────────────────

    public function saveAndBack(PurchaseService $service): mixed
    {
        return $this->persist($service, false);
    }

    public function saveAndCreateAnother(PurchaseService $service): mixed
    {
        return $this->persist($service, true);
    }

    protected function persist(PurchaseService $service, bool $createAnother): mixed
    {
        if ($this->purchaseId !== null) {
            Gate::authorize('purchases.edit');
        } else {
            Gate::authorize('purchases.create');
        }

        if (empty($this->items)) {
            $this->addError('items', 'Debe agregar al menos un producto a la compra.');

            return null;
        }

        // Construir el array de items en el formato que espera el servicio
        $itemsForService = [];
        foreach ($this->items as $item) {
            $productId = $item['product_id'];
            $itemsForService[] = [
                'product_id' => $productId,
                'quantity' => (int) ($item['quantity'] ?? 1),
                'price' => (float) ($item['price'] ?? 0),
                'discount_value' => (float) ($item['discount_value'] ?? 0),
                'discount_type' => $item['discount_type'] ?? 'fixed',
            ];
        }

        try {
            if ($this->purchaseId === null) {
                $purchase = $service->createPurchase((int) Auth::user()->company_id, [
                    'purchase_date' => $this->purchase_date,
                    'purchase_time' => $this->purchase_time,
                    'items' => $itemsForService,
                    'general_discount_value' => (float) $this->general_discount_value,
                    'general_discount_type' => $this->general_discount_type,
                ]);

                session()->flash(
                    'message',
                    $createAnother
                        ? '¡Compra registrada exitosamente! Puede crear otra compra.'
                        : '¡Compra registrada exitosamente!'
                );
                session()->flash('icons', 'success');

                if ($createAnother) {
                    return $this->redirect(route('admin.purchases.create'));
                }
            } else {
                $service->updatePurchase((int) Auth::user()->company_id, $this->purchaseId, [
                    'purchase_date' => $this->purchase_date,
                    'purchase_time' => $this->purchase_time,
                    'items' => $itemsForService,
                    'general_discount_value' => (float) $this->general_discount_value,
                    'general_discount_type' => $this->general_discount_type,
                ]);

                session()->flash('message', '¡Compra actualizada exitosamente!');
                session()->flash('icons', 'success');
            }

            return $this->redirectAfterSave();
        } catch (ValidationException $e) {
            $this->mergeValidationErrors($e);

            return null;
        } catch (\RuntimeException $e) {
            $this->addError('purchase_date', $e->getMessage());

            return null;
        } catch (\Throwable $e) {
            $this->addError('purchase_date', 'Error al guardar la compra: '.$e->getMessage());

            return null;
        }
    }

    protected function redirectAfterSave(): mixed
    {
        if ($this->referrerUrl) {
            return $this->redirect($this->referrerUrl);
        }

        return $this->redirect(route('admin.purchases.index'));
    }

    // ─── HELPERS ───────────────────────────────────────

    protected function hasProduct(int $productId): bool
    {
        return collect($this->items)->contains('product_id', $productId);
    }

    protected function captureReferrer(): void
    {
        $referrerUrl = request()->header('referer');
        if (! $referrerUrl) {
            return;
        }

        if (
            ! str_contains($referrerUrl, 'purchases/create')
            && ! str_contains($referrerUrl, 'purchases/edit')
            && ! str_contains($referrerUrl, 'purchases/v2/create')
            && ! str_contains($referrerUrl, 'purchases/v2/edit')
            && filter_var($referrerUrl, FILTER_VALIDATE_URL)
        ) {
            $this->referrerUrl = $referrerUrl;
        }
    }

    /**
     * @return object{id: int, name: string, code: string, symbol: string, country_id: int|null}
     */
    protected function resolveCurrency(): object
    {
        $company = Auth::user()->company;

        if ($company && $company->currency) {
            $row = DB::table('currencies')
                ->select('id', 'name', 'code', 'symbol', 'country_id')
                ->where('code', $company->currency)
                ->first();
            if ($row) {
                return $row;
            }
        }

        $fallback = DB::table('currencies')
            ->select('id', 'name', 'code', 'symbol', 'country_id')
            ->where('country_id', $company->country)
            ->first();

        return $fallback ?? (object) [
            'id' => 0,
            'name' => '',
            'code' => '',
            'symbol' => '$',
            'country_id' => null,
        ];
    }

    // ─── RENDER ────────────────────────────────────────

    public function render(): View
    {
        $companyId = (int) Auth::user()->company_id;

        // Productos para el modal de búsqueda (paginados)
        $modalProducts = Product::query()
            ->select(['id', 'code', 'name', 'purchase_price', 'stock', 'image', 'category_id', 'company_id'])
            ->with(['category:id,name'])
            ->where('company_id', $companyId)
            ->when($this->modal_search !== '', function ($query) {
                $s = $this->modal_search;
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'ILIKE', "%{$s}%")
                        ->orWhere('code', 'ILIKE', "%{$s}%")
                        ->orWhereHas('category', fn ($cq) => $cq->where('name', 'ILIKE', "%{$s}%"));
                });
            })
            ->orderBy('name')
            ->paginate(12, ['*'], 'modalPage');

        $currency = $this->resolveCurrency();
        $isEdit = $this->purchaseId !== null;

        return view('livewire.purchase-form', [
            'isEdit' => $isEdit,
            'currency' => $currency,
            'headingTitle' => $isEdit ? 'Editar compra' : 'Nueva compra',
            'headingSubtitle' => $isEdit
                ? 'Modifica los datos y productos de la compra.'
                : 'Registra una nueva compra de productos al inventario.',
            'modalProducts' => $modalProducts,
            'existingProductIds' => collect($this->items)->pluck('product_id')->all(),
        ]);
    }
}
