<?php

namespace App\Livewire;

use App\Models\CashCount;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Services\SaleService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class SaleForm extends Component
{
    use WithPagination;

    // ─── Modo (create / edit) ─────────────────────────────
    public ?int $saleId = null;
    public bool $isEdit = false;
    public string $headingTitle = 'Nueva venta';
    public string $headingSubtitle = 'Registre una nueva transacción de venta';

    // ─── Datos de la venta ────────────────────────────────
    public string $sale_date;
    public string $sale_time;
    public ?int $customer_id = null;
    public string $note = '';
    public bool $already_paid = false;

    // ─── Items (productos en la venta) ────────────────────
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

    // ─── Modal crear cliente rápido ──────────────────────
    public bool $show_customer_modal = false;
    public string $new_customer_name = '';
    public string $new_customer_nit = '';
    public string $new_customer_phone = '';
    public string $new_customer_email = '';
    public string $new_customer_debt = '';

    // ─── Verificación de caja ─────────────────────────────
    public bool $hasCashOpen = false;

    // ─── Ventas Masivas ─────────────────────────────────────
    public bool $showBulkModal = false;
    public string $bulkProductSearch = '';
    public ?int $bulkProductId = null;
    public string $bulkSaleDate = '';
    public string $bulkSaleTime = '';
    public string $bulkRawData = '';

    // Analysis results
    /** @var array<int, array> */
    public array $bulkResults = [];
    public bool $bulkIsAnalyzing = false;

    // ─── Refs para redirección ───────────────────────────
    protected ?string $referrerUrl = null;

    public function mount(?int $saleId = null): void
    {
        $this->saleId = $saleId;
        $this->captureReferrer();

        // Check open cash count
        $this->hasCashOpen = CashCount::where('company_id', Auth::user()->company_id)
            ->whereNull('closing_date')
            ->exists();

        if ($this->saleId !== null) {
            Gate::authorize('sales.edit');
            $this->isEdit = true;
            $this->headingTitle = 'Editar venta';
            $this->headingSubtitle = 'Modifique los datos y productos de la venta.';
            $this->loadSale();
            return;
        }

        Gate::authorize('sales.create');
        $this->sale_date = now()->format('Y-m-d');
        $this->sale_time = now()->format('H:i');

        // ── Auto-add single product with stock (legacy business rule) ──
        $productCount = Product::where('company_id', Auth::user()->company_id)
            ->where('stock', '>', 0)
            ->count();
        if ($productCount === 1 && empty($this->items)) {
            $product = Product::where('company_id', Auth::user()->company_id)
                ->where('stock', '>', 0)
                ->first();
            if ($product) {
                $this->items[] = [
                    'product_id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'image_url' => $product->image_url,
                    'stock' => (int) $product->stock,
                    'quantity' => 1,
                    'price' => (float) $product->sale_price,
                    'discount_value' => 0,
                    'discount_type' => 'fixed',
                ];
                $this->js("window.uiNotifications?.showToast?.('" . addslashes($product->name) . " se agregó automáticamente (único producto con stock)', {type:'info', title:'Atención', timeout:6000, theme:'futuristic'})");
            }
        }

        // Pre-select customer from URL ?customer_id= param
        if (request()->has('customer_id')) {
            $customerId = (int) request()->input('customer_id');
            $customer = Customer::where('company_id', Auth::user()->company_id)
                ->where('id', $customerId)
                ->first();
            if ($customer) {
                $this->customer_id = $customerId;
            }
        }
    }

    protected function loadSale(): void
    {
        $sale = Sale::with(['saleDetails.product', 'saleDetails.product.category', 'customer'])
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $this->saleId)
            ->firstOrFail();

        $this->sale_date = $sale->sale_date->format('Y-m-d');
        $this->sale_time = $sale->sale_date->format('H:i');
        $this->customer_id = $sale->customer_id;
        $this->note = $sale->note ?? '';
        $this->general_discount_value = (string) ($sale->general_discount_value ?? 0);
        $this->general_discount_type = $sale->general_discount_type ?? 'fixed';
        $this->already_paid = false; // Can't change payment status on edit

        $this->items = $sale->saleDetails->map(function (SaleDetail $detail) {
            return [
                'product_id' => $detail->product_id,
                'code' => $detail->product->code ?? '',
                'name' => $detail->product->name ?? '',
                'image_url' => $detail->product->image_url ?? '',
                'stock' => (int) ($detail->product->stock ?? 0),
                'quantity' => (int) $detail->quantity,
                'price' => (float) ($detail->original_price ?? $detail->unit_price),
                'discount_value' => (float) ($detail->discount_value ?? 0),
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
        $service = app(SaleService::class);
        return $service->calculateSubtotal($this->items);
    }

    public function getTotalAmountProperty(): float
    {
        $service = app(SaleService::class);
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
        $service = app(SaleService::class);
        return $service->calculateItemSubtotal($this->items[$index]);
    }

    public function getCustomersProperty()
    {
        return Customer::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'total_debt']);
    }

    /**
     * All customers for bulk sale fuzzy matching (minimal columns).
     */
    public function getAllCustomersProperty()
    {
        return Customer::where('company_id', Auth::user()->company_id)
            ->select(['id', 'name', 'phone'])
            ->orderBy('name')
            ->get();
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

        // Sales require stock > 0
        if ($product->stock <= 0) {
            $this->js("window.uiNotifications?.showToast?.('Este producto no tiene stock disponible', {type:'error', title:'Atención', timeout:4800, theme:'futuristic'})");
            return;
        }

        $this->items[] = [
            'product_id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'image_url' => $product->image_url,
            'stock' => (int) $product->stock,
            'quantity' => 1,
            'price' => (float) $product->sale_price,
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

    // ─── MODAL CREAR CLIENTE ─────────────────────────────

    public function openCustomerModal(): void
    {
        $this->show_customer_modal = true;
    }

    public function closeCustomerModal(): void
    {
        $this->show_customer_modal = false;
    }

    public function saveCustomer(): void
    {
        $validated = $this->validate([
            'new_customer_name' => ['required', 'string', 'max:255'],
            'new_customer_nit' => ['nullable', 'string', 'max:20', 'unique:customers,nit_number'],
            'new_customer_phone' => ['nullable', 'string', 'max:20'],
            'new_customer_email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'new_customer_debt' => ['nullable', 'numeric', 'min:0'],
        ], [
            'new_customer_name.required' => 'El nombre es obligatorio.',
            'new_customer_nit.unique' => 'Este NIT ya está registrado.',
            'new_customer_email.email' => 'Ingresa un correo válido.',
            'new_customer_email.unique' => 'Este correo ya está registrado.',
            'new_customer_debt.numeric' => 'La deuda debe ser un número.',
            'new_customer_debt.min' => 'La deuda no puede ser negativa.',
        ]);

        $customer = Customer::create([
            'name' => ucwords(strtolower($validated['new_customer_name'])),
            'nit_number' => $validated['new_customer_nit'] ?: null,
            'phone' => $validated['new_customer_phone'] ?: null,
            'email' => $validated['new_customer_email'] ? strtolower($validated['new_customer_email']) : null,
            'total_debt' => $validated['new_customer_debt'] !== '' ? (float) $validated['new_customer_debt'] : 0,
            'company_id' => Auth::user()->company_id,
        ]);

        // Seleccionar el cliente recién creado
        $this->customer_id = $customer->id;

        // Limpiar formulario
        $this->new_customer_name = '';
        $this->new_customer_nit = '';
        $this->new_customer_phone = '';
        $this->new_customer_email = '';
        $this->new_customer_debt = '';

        $this->show_customer_modal = false;

        $this->toast('Cliente "' . $customer->name . '" creado y seleccionado.');
    }

    protected function toast(string $message, string $type = 'success'): void
    {
        $this->js(
            'if(window.uiNotifications?.showToast){window.uiNotifications.showToast('
            . json_encode($message) . ', {type: ' . json_encode($type) . ', theme: "futuristic"});'
            . '}else if(typeof Swal !== "undefined"){Swal.fire({icon:"' . $type . '",text:' . json_encode($message)
            . ',timer:2500,showConfirmButton:false,toast:true,position:"top-end"});}'
        );
    }

    // ─── VENTAS MASIVAS ────────────────────────────────

    public function openBulkModal(): void
    {
        $this->showBulkModal = true;
        $this->bulkProductId = null;
        $this->bulkProductSearch = '';
        $this->bulkSaleDate = now()->format('Y-m-d');
        $this->bulkSaleTime = now()->format('H:i');
        $this->bulkRawData = '';
    }

    public function closeBulkModal(): void
    {
        $this->showBulkModal = false;
        $this->bulkProductId = null;
        $this->bulkProductSearch = '';
        $this->bulkRawData = '';
        $this->bulkResults = [];
        $this->bulkIsAnalyzing = false;
    }

    public function getBulkProductsProperty()
    {
        return Product::where('company_id', Auth::user()->company_id)
            ->where('stock', '>', 0)
            ->select(['id', 'code', 'name', 'sale_price', 'stock', 'image'])
            ->when($this->bulkProductSearch !== '', function ($q) {
                $q->where(function ($q2) {
                    $q2->where('name', 'ILIKE', "%{$this->bulkProductSearch}%")
                        ->orWhere('code', 'ILIKE', "%{$this->bulkProductSearch}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    /**
     * Analyze bulk sale data: parse lines, extract client name + quantity + payment info,
     * normalize names, fuzzy-match customers, and build results array.
     */
    public function analyzeBulkData(): void
    {
        if (trim($this->bulkRawData) === '') {
            $this->bulkResults = [];
            $this->js("window.uiNotifications?.showToast?.('Datos Vacíos', {type:'warning', title:'Ingrese datos para analizar', timeout:4800, theme:'futuristic'})");
            return;
        }

        $this->bulkIsAnalyzing = true;
        $this->bulkResults = [];

        $lines = array_filter(array_map('trim', explode("\n", $this->bulkRawData)));
        $allCustomers = $this->all_customers;

        // Normalize helper: remove accents, lowercase
        $normalize = fn (string $str): string => strtolower(
            iconv('UTF-8', 'ASCII//TRANSLIT', $str)
        );

        foreach ($lines as $line) {
            $parts = preg_split('/\s+/', $line);

            if (count($parts) < 2) {
                $this->bulkResults[] = [
                    'originalText' => $line,
                    'error' => 'Formato inválido (falta cantidad)',
                    'status' => 'error',
                ];
                continue;
            }

            // Last part = quantity info
            $quantityPart = array_pop($parts);

            $quantity = 0;
            $remainingQuantity = null;
            $isPaid = false;
            $isPartialPayment = false;

            if (str_contains($quantityPart, '-')) {
                $qParts = explode('-', $quantityPart);
                $quantity = (int) $qParts[0];
                $remainingQuantity = (int) $qParts[1];

                if (!is_nan($remainingQuantity)) {
                    if ($remainingQuantity == 0) {
                        $isPaid = true; // Paid in full (0 remaining debt)
                    } elseif ($remainingQuantity < $quantity) {
                        $isPartialPayment = true; // Partial payment
                    }
                }
            } else {
                $quantity = (int) $quantityPart;
            }

            $clientNameRaw = implode(' ', $parts);
            $clientNameNormalized = $normalize($clientNameRaw);

            if (is_nan($quantity) || $quantity <= 0) {
                $this->bulkResults[] = [
                    'originalText' => $line,
                    'error' => 'Cantidad no es un número',
                    'status' => 'error',
                ];
                continue;
            }

            // Fuzzy match: exact or substring (both normalized)
            $matches = [];
            foreach ($allCustomers as $c) {
                $dbNameNormalized = $normalize($c->name);
                if (str_contains($dbNameNormalized, $clientNameNormalized)
                    || str_contains($clientNameNormalized, $dbNameNormalized)) {
                    $matches[] = [
                        'id' => $c->id,
                        'name' => $c->name,
                        'phone' => $c->phone ?? '',
                    ];
                }
            }

            $result = [
                'originalText' => $line,
                'clientName' => $clientNameRaw,
                'quantity' => $quantity,
                'remainingQuantity' => $remainingQuantity,
                'isPaid' => $isPaid,
                'isPartialPayment' => $isPartialPayment,
                'matches' => $matches,
                'selectedCustomer' => null,
                'status' => 'pending',
            ];

            if (count($matches) === 1) {
                $result['selectedCustomer'] = $matches[0];
                $result['status'] = 'resolved';
            } elseif (count($matches) > 1) {
                $result['status'] = 'ambiguous';
            } else {
                $result['status'] = 'not_found';
            }

            $this->bulkResults[] = $result;
        }

        $this->bulkIsAnalyzing = false;
        $this->js("window.uiNotifications?.showToast?.('Análisis Completado', {type:'info', title:'Revise los resultados abajo', timeout:4800, theme:'futuristic'})");
    }

    /**
     * Resolve an ambiguous match by selecting a specific customer.
     */
    public function resolveBulkMatch(int $index, int $customerId): void
    {
        if (!isset($this->bulkResults[$index])) {
            return;
        }

        $customer = Customer::where('company_id', Auth::user()->company_id)
            ->where('id', $customerId)
            ->first(['id', 'name', 'phone']);

        if ($customer) {
            $this->bulkResults[$index]['selectedCustomer'] = [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone ?? '',
            ];
            $this->bulkResults[$index]['status'] = 'resolved';
        }
    }

    /**
     * Mark a line as ignored.
     */
    public function ignoreBulkLine(int $index): void
    {
        if (isset($this->bulkResults[$index])) {
            $this->bulkResults[$index]['status'] = 'ignored';
        }
    }

    /**
     * Restore an ignored line back to its original status.
     */
    public function restoreBulkLine(int $index): void
    {
        if (!isset($this->bulkResults[$index])) {
            return;
        }

        $result = &$this->bulkResults[$index];
        if ($result['status'] !== 'ignored') {
            return;
        }

        // Re-determine original status
        if (isset($result['selectedCustomer'])) {
            $result['status'] = 'resolved';
        } elseif (!empty($result['matches'])) {
            $result['status'] = 'ambiguous';
        } else {
            $result['status'] = 'not_found';
        }
    }

    public function updatedModalSearch(): void
    {
        $this->resetPage('modalPage');
    }

    // ─── VALIDACIÓN ────────────────────────────────────

    protected function rules(): array
    {
        $rules = app(SaleService::class)->rulesForCreate();

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
            'customer_id' => 'cliente',
            'sale_date' => 'fecha de venta',
            'sale_time' => 'hora de venta',
            'items' => 'productos',
            'general_discount_value' => 'descuento general',
        ];
    }

    protected function messages(): array
    {
        return app(SaleService::class)->validationMessages();
    }

    // ─── PERSISTENCIA ──────────────────────────────────

    public function saveAndBack(SaleService $service): mixed
    {
        return $this->persist($service, false);
    }

    public function saveAndCreateAnother(SaleService $service): mixed
    {
        return $this->persist($service, true);
    }

    protected function persist(SaleService $service, bool $createAnother): mixed
    {
        if ($this->saleId !== null) {
            Gate::authorize('sales.edit');
        } else {
            Gate::authorize('sales.create');
        }

        if (empty($this->items)) {
            $this->addError('items', 'Debe agregar al menos un producto a la venta.');
            return null;
        }

        if (! $this->customer_id) {
            $this->addError('customer_id', 'Debe seleccionar un cliente.');
            return null;
        }

        if (! $this->hasCashOpen && ! $this->isEdit) {
            $this->addError('sale_date', 'No hay una caja abierta. Debe abrir una caja antes de registrar ventas.');
            return null;
        }

        // Validar stock para cada producto
        foreach ($this->items as $index => $item) {
            $product = Product::find($item['product_id']);
            if ($product && (int) ($item['quantity'] ?? 0) > (int) $product->stock) {
                $this->addError("items.{$index}.quantity", "Stock insuficiente para {$product->name}. Disponible: {$product->stock}");
                return null;
            }
        }

        // Construir el array de items en el formato que espera el servicio
        $itemsForService = [];
        foreach ($this->items as $item) {
            $itemsForService[] = [
                'product_id' => $item['product_id'],
                'quantity' => (int) ($item['quantity'] ?? 1),
                'price' => (float) ($item['price'] ?? 0),
                'discount_value' => (float) ($item['discount_value'] ?? 0),
                'discount_type' => $item['discount_type'] ?? 'fixed',
            ];
        }

        try {
            if ($this->saleId === null) {
                $sale = $service->createSale([
                    'customer_id' => $this->customer_id,
                    'sale_date' => $this->sale_date,
                    'sale_time' => $this->sale_time,
                    'items' => $itemsForService,
                    'general_discount_value' => (float) $this->general_discount_value,
                    'general_discount_type' => $this->general_discount_type,
                    'note' => $this->note,
                    'already_paid' => $this->already_paid,
                ], (int) Auth::user()->company_id);

                session()->flash(
                    'message',
                    $createAnother
                        ? '¡Venta registrada exitosamente! Puede crear otra venta.'
                        : '¡Venta registrada exitosamente!'
                );
                session()->flash('icons', 'success');

                if ($createAnother) {
                    return $this->redirect(route('admin.sales.create'));
                }
            } else {
                $service->updateSale($this->saleId, [
                    'customer_id' => $this->customer_id,
                    'sale_date' => $this->sale_date,
                    'sale_time' => $this->sale_time,
                    'items' => $itemsForService,
                    'general_discount_value' => (float) $this->general_discount_value,
                    'general_discount_type' => $this->general_discount_type,
                    'note' => $this->note,
                ], (int) Auth::user()->company_id);

                session()->flash('message', '¡Venta actualizada exitosamente!');
                session()->flash('icons', 'success');
            }

            return $this->redirectAfterSave();
        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->validator->errors()->messages() as $key => $messages) {
                foreach ($messages as $message) {
                    $this->addError($key, $message);
                }
            }
            return null;
        } catch (\RuntimeException $e) {
            $this->addError('sale_date', $e->getMessage());
            return null;
        } catch (\Throwable $e) {
            $this->addError('sale_date', 'Error al guardar la venta: ' . $e->getMessage());
            return null;
        }
    }

    protected function redirectAfterSave(): mixed
    {
        if ($this->referrerUrl) {
            return $this->redirect($this->referrerUrl);
        }

        return $this->redirect(route('admin.sales.index'));
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
            ! str_contains($referrerUrl, 'sales/create')
            && ! str_contains($referrerUrl, 'sales/edit')
            && ! str_contains($referrerUrl, 'sales/v2/create')
            && ! str_contains($referrerUrl, 'sales/v2/edit')
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
            ->select(['id', 'code', 'name', 'sale_price', 'stock', 'image', 'category_id', 'company_id'])
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

        $customers = Customer::where('company_id', $companyId)
            ->select(['id', 'name', 'phone', 'total_debt'])
            ->orderBy('name')
            ->get();

        $currency = $this->resolveCurrency();
        $isEdit = $this->saleId !== null;

        return view('livewire.sale-form', [
            'isEdit' => $isEdit,
            'currency' => $currency,
            'headingTitle' => $this->headingTitle,
            'headingSubtitle' => $this->headingSubtitle,
            'modalProducts' => $modalProducts,
            'existingProductIds' => collect($this->items)->pluck('product_id')->all(),
            'customers' => $customers,
            'bulkProducts' => $this->bulk_products,
            'allCustomers' => $this->all_customers,
        ]);
    }
}