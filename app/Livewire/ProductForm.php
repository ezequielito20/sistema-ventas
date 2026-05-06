<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Services\ImageUrlService;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
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

    public string $entry_date;

    /** @var int|string|null */
    public $category_id = null;

    public $image = null;

    public ?string $existingImagePath = null;

    public function mount(?int $productId = null): void
    {
        $this->productId = $productId;
        $this->captureProductsReferrer();

        if ($this->productId !== null) {
            Gate::authorize('products.edit');

            $product = Product::query()
                ->where('company_id', Auth::user()->company_id)
                ->where('id', $this->productId)
                ->firstOrFail();

            $this->code = $product->code;
            $this->name = $product->name;
            $this->description = (string) ($product->description ?? '');
            $this->stock = (int) $product->stock;
            $this->min_stock = (int) $product->min_stock;
            $this->max_stock = (int) $product->max_stock;
            $this->purchase_price = (string) $product->purchase_price;
            $this->sale_price = (string) $product->sale_price;
            $this->entry_date = $product->entry_date->format('Y-m-d');
            $this->category_id = $product->category_id;
            $this->existingImagePath = $product->image;

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
        // Auto-generar código si el nombre tiene texto y el código está vacío
        if ($this->name && ! $this->code) {
            $this->code = 'PROD' . substr((string) time(), -6);
        }
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
        $referrerUrl = request()->header('referer');
        if (! $referrerUrl) {
            return;
        }

        if ($this->productId === null && ! str_contains($referrerUrl, 'products/create')) {
            session(['products_referrer' => $referrerUrl]);
        } elseif ($this->productId !== null && ! str_contains($referrerUrl, 'products/edit')) {
            session(['products_referrer' => $referrerUrl]);
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'required|integer|gt:min_stock',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0|gt:purchase_price',
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
        $upload = $validated['image'] ?? null;
        unset($validated['image']);
        $validated['category_id'] = (int) $validated['category_id'];

        $companyId = (int) Auth::user()->company_id;

        try {
            if ($this->productId === null) {
                $productService->create($validated, $companyId, $upload);

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

                $productService->update($product, $validated, $upload);

                session()->flash('message', 'Producto actualizado exitosamente');
                session()->flash('icons', 'success');
            }

            return $this->redirectAfterSave();
        } catch (\Throwable $e) {
            $this->addError('code', 'Error al guardar: '.$e->getMessage());

            return null;
        }
    }

    protected function redirectAfterSave(): mixed
    {
        $referrerUrl = session('products_referrer');
        if ($referrerUrl) {
            session()->forget('products_referrer');

            return $this->redirect($referrerUrl);
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

        $existingImageUrl = $this->existingImagePath
            ? ImageUrlService::getImageUrl($this->existingImagePath)
            : null;

        return view('livewire.product-form', [
            'isEdit' => $isEdit,
            'categories' => $categories,
            'currency' => $currency,
            'headingTitle' => $isEdit ? 'Editar producto' : 'Crear producto',
            'headingSubtitle' => $isEdit
                ? 'Actualiza la información del producto en el inventario.'
                : 'Registra un nuevo producto en el inventario.',
            'existingImageUrl' => $existingImageUrl,
        ]);
    }
}
