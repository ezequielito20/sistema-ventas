<?php

namespace App\Livewire;

use App\Livewire\Concerns\MergesValidationErrors;
use App\Models\Category;
use App\Models\Product;
use App\Services\PlanEntitlementService;
use App\Services\ProductService;
use App\Support\ProductListingReturnUrl;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use MergesValidationErrors;
    use WithFileUploads;

    public ?int $productId = null;

    public string $code = '';

    public string $name = '';

    public string $description = '';

    public int $stock = 0;

    public int $min_stock = 0;

    public int $max_stock = 0;

    public string $purchase_price = '0';

    public string $sale_price = '0';

    public int $discount_percent = 0;

    public bool $include_in_catalog = true;

    public string $entry_date;

    /** @var int|string|null */
    public $category_id = null;

    public $image = null;

    public array $newImages = [];

    public ?int $newCoverIndex = null;

    public ?int $coverImageId = null;

    public array $imageToDelete = [];

    public ?string $existingImagePath = null;

    public $existingImages = [];

    public function mount(?int $productId = null): void
    {
        $this->productId = $productId;
        $this->captureProductsReferrer();

        if ($this->productId !== null) {
            Gate::authorize('products.edit');

            $product = Product::query()
                ->where('company_id', Auth::user()->company_id)
                ->where('id', $this->productId)
                ->with('images')
                ->firstOrFail();

            $this->code = $product->code;
            $this->name = $product->name;
            $this->description = (string) ($product->description ?? '');
            $this->stock = (int) $product->stock;
            $this->min_stock = (int) $product->min_stock;
            $this->max_stock = (int) $product->max_stock;
            $this->purchase_price = (string) $product->purchase_price;
            $this->sale_price = (string) $product->sale_price;
            $this->discount_percent = (int) $product->discount_percent;
            $this->include_in_catalog = (bool) $product->include_in_catalog;
            $this->entry_date = $product->entry_date->format('Y-m-d');
            $this->category_id = $product->category_id;
            $this->existingImagePath = $product->image;

            $this->existingImages = $product->images
                ->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => $img->image_url,
                    'is_cover' => (bool) $img->is_cover,
                    'path' => $img->image,
                ])
                ->values()
                ->toArray();

            $cover = collect($this->existingImages)->first(fn ($img) => ($img['is_cover'] ?? false) === true);
            if ($cover) {
                $this->coverImageId = $cover['id'];
                $this->existingImagePath = $cover['path'] ?? null;
            } else {
                $this->existingImagePath = $product->image;
            }

            return;
        }

        Gate::authorize('products.create');
        $this->entry_date = now()->format('Y-m-d');
    }

    public function updatedCategoryId(mixed $value): void
    {
        if ($value === '' || $value === null) {
            $this->category_id = null;
        }
    }

    public function updatedName(): void
    {
        if ($this->name && ! $this->code) {
            $this->code = 'PROD'.substr((string) time(), -6);
        }
    }

    public function removeExistingImage(int $imageId): void
    {
        Gate::authorize('products.edit');

        $this->imageToDelete[] = $imageId;
        $this->existingImages = array_values(array_filter($this->existingImages, fn ($img) => $img['id'] !== $imageId));

        if ($this->coverImageId === $imageId) {
            $this->coverImageId = null;
        }
    }

    public function removeNewImage(int $index): void
    {
        if ($this->newCoverIndex === $index) {
            $this->newCoverIndex = null;
        } elseif ($this->newCoverIndex !== null && $this->newCoverIndex > $index) {
            $this->newCoverIndex--;
        }
        unset($this->newImages[$index]);
        $this->newImages = array_values($this->newImages);
    }

    public function setCoverImage(?int $imageId): void
    {
        Gate::authorize('products.edit');
        $this->coverImageId = $imageId;

        if ($imageId !== null) {
            $cover = collect($this->existingImages)->first(fn ($img) => ($img['id'] ?? 0) === $imageId);
            if ($cover && isset($cover['path'])) {
                $this->existingImagePath = $cover['path'] ?? null;
                $this->image = null;
            }
        }
    }

    public function setNewCoverImage(int $index): void
    {
        $this->newCoverIndex = $index;
        $this->coverImageId = null;
    }

    /**
     * Margen sobre el costo de compra: (precio venta − precio compra) / precio compra × 100.
     * Null si el precio de compra no permite calcular (≤ 0).
     */
    public function profitMarginPercent(): ?float
    {
        $purchase = $this->parsePrice($this->purchase_price);
        $sale = $this->parsePrice($this->sale_price);

        if ($purchase <= 0.0) {
            return null;
        }

        return (($sale - $purchase) / $purchase) * 100.0;
    }

    /** Diferencia venta − compra (moneda), para mostrar junto al margen %. */
    public function profitAbsoluteAmount(): float
    {
        return round(
            $this->parsePrice($this->sale_price) - $this->parsePrice($this->purchase_price),
            2
        );
    }

    protected function parsePrice(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = str_replace(',', '.', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }

    protected function captureProductsReferrer(): void
    {
        $returnQuery = request()->query('return');
        $fromQuery = is_string($returnQuery)
            ? ProductListingReturnUrl::sanitizeInternalFullUrl($returnQuery)
            : null;
        if ($fromQuery !== null) {
            session(['products_referrer' => $fromQuery]);

            return;
        }

        $referrerUrl = request()->header('referer');
        if (! $referrerUrl) {
            return;
        }

        if ($this->productId === null && ! str_contains($referrerUrl, 'products/create')) {
            $safe = ProductListingReturnUrl::sanitizeInternalFullUrl($referrerUrl);
            if ($safe !== null) {
                session(['products_referrer' => $safe]);
            }

            return;
        }

        if ($this->productId !== null && ! str_contains($referrerUrl, 'products/edit')) {
            $safe = ProductListingReturnUrl::sanitizeInternalFullUrl($referrerUrl);
            if ($safe !== null) {
                session(['products_referrer' => $safe]);
            }
        }
    }

    protected function rules(): array
    {
        $companyId = (int) Auth::user()->company_id;

        $codeRule = ['required', 'string', 'max:50'];
        if ($this->productId === null) {
            $codeRule[] = 'unique:products,code';
        } else {
            $codeRule[] = Rule::unique('products', 'code')->ignore($this->productId);
        }

        return [
            'code' => $codeRule,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'newImages.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'required|integer|gt:min_stock',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0|gt:purchase_price',
            'discount_percent' => 'required|integer|min:0|max:99',
            'include_in_catalog' => ['boolean'],
            'entry_date' => 'required|date|before_or_equal:today',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'code' => 'código',
            'name' => 'nombre',
            'description' => 'descripción',
            'stock' => 'stock actual',
            'min_stock' => 'stock mínimo',
            'max_stock' => 'stock máximo',
            'purchase_price' => 'precio de compra',
            'sale_price' => 'precio de venta',
            'discount_percent' => 'descuento',
            'include_in_catalog' => 'inclusión en catálogo público',
            'entry_date' => 'fecha de ingreso',
            'category_id' => 'categoría',
            'image' => 'imagen',
        ];
    }

    protected function messages(): array
    {
        return [
            'code.required' => 'El código es obligatorio',
            'code.unique' => 'Este código ya está en uso',
            'name.required' => 'El nombre es obligatorio',
            'stock.min' => 'El stock no puede ser negativo',
            'min_stock.min' => 'El stock mínimo no puede ser negativo',
            'max_stock.gt' => 'El stock máximo debe ser mayor que el stock mínimo',
            'purchase_price.min' => 'El precio de compra debe ser mayor a 0',
            'sale_price.gt' => 'El precio de venta debe ser mayor al precio de compra',
            'category_id.required' => 'La categoría es obligatoria',
        ];
    }

    public function saveAndBack(ProductService $productService): mixed
    {
        return $this->persist($productService, false);
    }

    public function saveAndCreateAnother(ProductService $productService): mixed
    {
        return $this->persist($productService, true);
    }

    protected function persist(ProductService $productService, bool $createAnother): mixed
    {
        if ($this->productId !== null) {
            Gate::authorize('products.edit');
        } else {
            Gate::authorize('products.create');
        }

        $validated = $this->validate();
        $validated['category_id'] = (int) $validated['category_id'];

        $imageDeletions = $this->imageToDelete;
        $coverImageId = $this->coverImageId;

        $galleryImages = $this->newImages;
        $upload = null;

        if ($this->newCoverIndex !== null && isset($galleryImages[$this->newCoverIndex])) {
            $upload = $galleryImages[$this->newCoverIndex];
            unset($galleryImages[$this->newCoverIndex]);
            $galleryImages = array_values($galleryImages);
        } elseif (! empty($galleryImages)) {
            $upload = array_shift($galleryImages);
        }

        $companyId = (int) Auth::user()->company_id;

        try {
            if ($this->productId === null) {
                app(PlanEntitlementService::class)->assertCanCreate(Auth::user(), 'products');
                $productService->create($validated, $companyId, $upload, $galleryImages);

                session()->flash(
                    'message',
                    $createAnother
                        ? 'Producto creado exitosamente. Puedes registrar otro desde este formulario.'
                        : 'Producto creado exitosamente'
                );
                session()->flash('icons', 'success');

                if ($createAnother) {
                    return $this->redirect(route('admin.products.create'));
                }
            } else {
                $product = Product::query()
                    ->where('company_id', $companyId)
                    ->where('id', $this->productId)
                    ->firstOrFail();

                $productService->update($product, $validated, $upload, $galleryImages, $imageDeletions, $coverImageId);

                session()->flash('message', 'Producto actualizado exitosamente');
                session()->flash('icons', 'success');
            }

            return $this->redirectAfterSave();
        } catch (ValidationException $e) {
            $this->mergeValidationErrors($e);

            return null;
        } catch (\Throwable $e) {
            $this->addError('code', 'Error al guardar: '.$e->getMessage());

            return null;
        }
    }

    protected function redirectAfterSave(): mixed
    {
        $referrerUrl = session('products_referrer');
        session()->forget('products_referrer');

        if (is_string($referrerUrl) && $referrerUrl !== '') {
            $safe = ProductListingReturnUrl::sanitizeInternalFullUrl($referrerUrl);
            if ($safe !== null) {
                return $this->redirect($safe);
            }
        }

        return $this->redirect(route('admin.products.index'));
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

    public function render(): View
    {
        $companyId = (int) Auth::user()->company_id;

        $categories = Category::query()
            ->select('id', 'name', 'company_id')
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $currency = $this->resolveCurrency();

        $isEdit = $this->productId !== null;

        return view('livewire.product-form', [
            'isEdit' => $isEdit,
            'categories' => $categories,
            'currency' => $currency,
            'headingTitle' => $isEdit ? 'Editar producto' : 'Crear producto',
            'headingSubtitle' => $isEdit
                ? 'Actualiza la información del producto en el inventario.'
                : 'Registra un nuevo producto en el inventario.',
        ]);
    }
}
