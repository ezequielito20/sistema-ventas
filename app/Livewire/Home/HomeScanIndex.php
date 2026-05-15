<?php

namespace App\Livewire\Home;

use App\Services\Home\HomeBarcodeService;
use App\Services\Home\HomeInventoryService;
use App\Services\Home\HomeVisionService;
use App\Models\Home\HomeProduct;
use App\Models\Home\HomeProductMovement;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class HomeScanIndex extends Component
{
    public bool $showModal = false;

    public ?string $lastBarcode = null;

    public ?string $lastPhotoData = null;

    public ?array $matchedProduct = null;

    public ?array $candidates = [];

    public ?string $message = null;

    public ?string $messageType = null;

    public bool $canUndo = false;

    public ?int $lastMovementId = null;

    public int $deductQuantity = 1;

    protected function rules(): array
    {
        return [
            'deductQuantity' => 'required|integer|min:1',
        ];
    }

    public function mount(): void
    {
        Gate::authorize('home.scan_deduct');
    }

    public function deductByBarcode(string $barcode, HomeBarcodeService $barcodeService): void
    {
        $this->validate();
        $companyId = (int) Auth::user()->company_id;

        $result = $barcodeService->deductByBarcode($barcode, $companyId, $this->deductQuantity);

        if ($result->success) {
            $this->lastMovementId = $result->movement?->id;
            $this->canUndo = true;
            $this->message = "\"{$result->product->name}\": -{$this->deductQuantity} unidad(es).";
            $this->messageType = 'success';
        } else {
            $this->message = $result->message;
            $this->messageType = 'error';
        }
    }

    public function identifyByPhoto(string $imageData, HomeVisionService $vision, HomeBarcodeService $barcodeService): void
    {
        $companyId = (int) Auth::user()->company_id;

        if (!$vision->checkDailyQuota($companyId)) {
            $this->message = 'Límite diario de reconocimiento alcanzado.';
            $this->messageType = 'warning';
            return;
        }

        $result = $vision->identifyProductFromImage($imageData, 'image/jpeg');

        if (!$result->name) {
            $this->message = 'No se pudo identificar el producto. Seleccioná manualmente.';
            $this->messageType = 'warning';
            return;
        }

        $match = $vision->matchProductAgainstInventory($result, $companyId);

        if ($match && $match['is_reliable']) {
            $this->matchedProduct = [
                'id' => $match['product']->id,
                'name' => $match['product']->name,
                'confidence' => round($match['score'] * 100) . '%',
            ];
            $this->showModal = true;
        } elseif ($match) {
            $this->candidates = [$match['product']->toArray()];
            $this->message = "Posiblemente: {$match['product']->name} ({$match['score']})";
            $this->messageType = 'info';
        } else {
            $this->message = 'No se encontró coincidencia en el inventario.';
            $this->messageType = 'warning';
        }
    }

    public function confirmDeduct(HomeInventoryService $inventory): void
    {
        if (!$this->matchedProduct) {
            return;
        }

        $product = HomeProduct::findOrFail($this->matchedProduct['id']);

        $result = $inventory->deduct($product, $this->deductQuantity, 'photo_deduct', [
            'notes' => 'Reconocimiento por foto',
            'confidence' => $this->matchedProduct['confidence'],
        ]);

        if ($result->success) {
            $this->lastMovementId = $result->movement?->id;
            $this->canUndo = true;
            $this->message = "\"{$product->name}\": -{$this->deductQuantity} unidad(es) por foto.";
            $this->messageType = 'success';
        } else {
            $this->message = $result->message;
            $this->messageType = 'error';
        }

        $this->showModal = false;
        $this->matchedProduct = null;
    }

    public function selectProduct(int $productId, HomeInventoryService $inventory): void
    {
        $product = HomeProduct::where('company_id', Auth::user()->company_id)->findOrFail($productId);

        $result = $inventory->deduct($product, $this->deductQuantity, 'photo_deduct');

        if ($result->success) {
            $this->lastMovementId = $result->movement?->id;
            $this->canUndo = true;
            $this->message = "\"{$product->name}\": -{$this->deductQuantity} unidad(es).";
            $this->messageType = 'success';
        } else {
            $this->message = $result->message;
            $this->messageType = 'error';
        }

        $this->candidates = [];
    }

    public function undoLast(HomeInventoryService $inventory): void
    {
        if (!$this->lastMovementId) {
            return;
        }

        $movement = HomeProductMovement::find($this->lastMovementId);

        if (!$movement) {
            $this->message = 'El movimiento ya no existe.';
            $this->messageType = 'error';
            return;
        }

        if ($movement->created_at->diffInSeconds(now()) > 30) {
            $this->message = 'Ya pasó el tiempo para deshacer.';
            $this->messageType = 'warning';
            $this->canUndo = false;
            return;
        }

        $inventory->undo($movement);

        $this->canUndo = false;
        $this->lastMovementId = null;
        $this->message = 'Movimiento revertido.';
        $this->messageType = 'success';
    }

    public function render(): View
    {
        $products = HomeProduct::where('company_id', Auth::user()->company_id)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'brand', 'quantity', 'unit']);

        return view('livewire.home-scan-index', [
            'products' => $products,
        ]);
    }
}
