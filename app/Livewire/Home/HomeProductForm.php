<?php

namespace App\Livewire\Home;

use App\Models\Home\HomeProduct;
use App\Support\Home\HomeProductCategories;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class HomeProductForm extends Component
{
    use WithFileUploads;

    public ?int $productId = null;

    public string $name = '';

    public string $brand = '';

    public string $category = '';

    public int $quantity = 0;

    public int $min_quantity = 1;

    public ?int $max_quantity = null;

    public string $unit = 'unidad';

    public float $purchase_price = 0;

    public string $barcode = '';

    public $image = null;

    public bool $showModal = false;

    public ?string $imagePreview = null;

    public bool $isEditing = false;

    protected $listeners = [
        'open-create-product' => 'openCreate',
        'edit-product' => 'openEdit',
    ];

    public function mount(): void
    {
        if ($this->productId) {
            $product = HomeProduct::where('company_id', Auth::user()->company_id)->findOrFail($this->productId);
            $this->name = $product->name;
            $this->brand = $product->brand ?? '';
            $this->category = $product->category;
            $this->quantity = $product->quantity;
            $this->min_quantity = $product->min_quantity;
            $this->max_quantity = $product->max_quantity;
            $this->unit = $product->unit;
            $this->purchase_price = (float) $product->purchase_price;
            $this->barcode = $product->barcode ?? '';
            $this->isEditing = true;
            $this->showModal = true;
        } elseif (request()->routeIs('admin.home.inventory.create')) {
            $this->openCreate();
        }
    }

    public function openEditFromEvent($params): void
    {
        $id = is_array($params) ? (int) ($params['id'] ?? 0) : (int) $params;
        $this->openEdit($id);
    }

    protected function rules(): array
    {
        $barcode = trim($this->barcode);
        $rules = [
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'category' => 'required|string|in:' . implode(',', HomeProductCategories::ALL),
            'min_quantity' => 'required|integer|min:1',
            'quantity' => 'nullable|integer|min:0',
            'max_quantity' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|in:unidad,kg,g,ml,l,paquete,caja,bolsa,rollo,par',
            'purchase_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:5120',
        ];

        if ($barcode !== '') {
            $rules['barcode'] = [
                'string', 'max:50',
                \Illuminate\Validation\Rule::unique('home_products', 'barcode')
                    ->where('company_id', Auth::user()->company_id)
                    ->ignore($this->productId),
            ];
        } else {
            $rules['barcode'] = 'nullable';
        }

        return $rules;
    }

    public function openCreate(): void
    {
        Gate::authorize('home.inventory.create');
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id = 0): void
    {
        if ($id <= 0) {
            return;
        }

        Gate::authorize('home.inventory.edit');

        $product = HomeProduct::where('company_id', Auth::user()->company_id)->findOrFail($id);

        $this->productId = $product->id;
        $this->name = $product->name;
        $this->brand = $product->brand ?? '';
        $this->category = $product->category;
        $this->quantity = $product->quantity;
        $this->min_quantity = $product->min_quantity;
        $this->max_quantity = $product->max_quantity;
        $this->unit = $product->unit;
        $this->purchase_price = (float) $product->purchase_price;
        $this->barcode = $product->barcode ?? '';
        $this->imagePreview = $product->image_url ?? null;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $companyId = (int) Auth::user()->company_id;

        // Convertir barcode vacío a null para evitar unique constraint con string vacío
        if (isset($data['barcode']) && trim($data['barcode']) === '') {
            $data['barcode'] = null;
        }

        // Asignar valores por defecto para campos opcionales
        $data['quantity'] = (int) ($data['quantity'] ?? 0);
        $data['min_quantity'] = (int) ($data['min_quantity'] ?? 1);
        $data['unit'] = $data['unit'] ?? 'unidad';
        $data['purchase_price'] = (float) ($data['purchase_price'] ?? 0);

        if ($this->image) {
            $data['image'] = $this->image->store("home/products/{$companyId}", 'public');
        } else {
            unset($data['image']);
        }

        if ($this->productId) {
            $product = HomeProduct::where('company_id', $companyId)->findOrFail($this->productId);
            $product->update($data);
            $this->dispatch('product-saved', message: 'Producto actualizado correctamente.');
        } else {
            $data['company_id'] = $companyId;
            HomeProduct::create($data);
            $this->dispatch('product-saved', message: 'Producto creado correctamente.');
        }

        $this->close();
    }

    public function close(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->productId = null;
        $this->name = '';
        $this->brand = '';
        $this->category = '';
        $this->quantity = 0;
        $this->min_quantity = 1;
        $this->max_quantity = null;
        $this->unit = 'unidad';
        $this->purchase_price = 0;
        $this->barcode = '';
        $this->image = null;
        $this->imagePreview = null;
        $this->isEditing = false;
    }

    public function render(): View
    {
        return view('admin.v2.home.inventory.form', [
            'categoryOptions' => HomeProductCategories::options(),
            'unitOptions' => [
                'unidad' => 'Unidad',
                'kg' => 'Kilogramo (kg)',
                'g' => 'Gramo (g)',
                'ml' => 'Mililitro (ml)',
                'l' => 'Litro (l)',
                'paquete' => 'Paquete',
                'caja' => 'Caja',
                'bolsa' => 'Bolsa',
                'rollo' => 'Rollo',
                'par' => 'Par',
            ],
        ]);
    }
}
