<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener el ID de la compañía del usuario autenticado
        $companyId = Auth::user()->company_id;

        // Obtener todas las compras de la compañía con sus relaciones
        $purchases = Purchase::with(['supplier', 'product'])
            ->where('company_id', $companyId)
            ->latest()
            ->get();

        // Calcular estadísticas para los widgets
        $totalPurchases = $purchases->count();
        $totalAmount = $purchases->sum('total_price');
        
        // Calcular compras del mes actual
        $monthlyPurchases = $purchases->filter(function($purchase) {
            return $purchase->purchase_date->isCurrentMonth();
        })->count();

        // Calcular compras pendientes (las que no tienen recibo de pago)
        $pendingDeliveries = $purchases->whereNull('payment_receipt')->count();

        return view('admin.purchases.index', compact(
            'purchases',
            'totalPurchases',
            'totalAmount',
            'monthlyPurchases',
            'pendingDeliveries'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            // Obtener productos y proveedores de la compañía actual
            $companyId = Auth::user()->company_id;
            
            $products = Product::where('company_id', $companyId)
                ->get();
            
            $suppliers = Supplier::where('company_id', $companyId)
                ->get();

            return view('admin.purchases.create', compact('products', 'suppliers'));

        } catch (\Exception $e) {
            Log::error('Error en PurchaseController@create: ' . $e->getMessage());
            return redirect()->route('admin.purchases.index')
                ->with('message', 'Hubo un problema al cargar el formulario: ' . $e->getMessage())
                ->with('icons', 'error');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validación personalizada
            $validated = $request->validate([
                'purchase_date' => ['required', 'date'],
                'supplier_id' => [
                    'required',
                    'exists:suppliers,id,company_id,' . Auth::user()->company_id,
                ],
                'product_id' => [
                    'required',
                    'exists:products,id,company_id,' . Auth::user()->company_id,
                ],
                'quantity' => ['required', 'integer', 'min:1'],
                'total_price' => ['required', 'numeric', 'min:0'],
                'payment_receipt' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
            ], [
                'purchase_date.required' => 'La fecha de compra es obligatoria',
                'purchase_date.date' => 'La fecha debe ser válida',
                'supplier_id.required' => 'El proveedor es obligatorio',
                'supplier_id.exists' => 'El proveedor seleccionado no es válido',
                'product_id.required' => 'El producto es obligatorio',
                'product_id.exists' => 'El producto seleccionado no es válido',
                'quantity.required' => 'La cantidad es obligatoria',
                'quantity.integer' => 'La cantidad debe ser un número entero',
                'quantity.min' => 'La cantidad debe ser mayor a 0',
                'total_price.required' => 'El precio total es obligatorio',
                'total_price.numeric' => 'El precio total debe ser un número',
                'total_price.min' => 'El precio total debe ser mayor a 0',
                'payment_receipt.mimes' => 'El recibo debe ser una imagen o PDF',
                'payment_receipt.max' => 'El recibo no debe pesar más de 2MB',
            ]);

            DB::beginTransaction();

            // Preparar los datos para guardar
            $purchaseData = array_merge($validated, [
                'company_id' => Auth::user()->company_id,
            ]);

            // Manejar el archivo de recibo si se proporcionó
            if ($request->hasFile('payment_receipt')) {
                $file = $request->file('payment_receipt');
                $path = $file->store('purchases/receipts', 'public');
                $purchaseData['payment_receipt'] = $path;
            }

            // Crear la compra
            $purchase = Purchase::create($purchaseData);

            // Actualizar el stock del producto
            $product = Product::findOrFail($request->product_id);
            $product->stock += $request->quantity;
            $product->save();

            // Log de la acción
            Log::info('Compra creada exitosamente', [
                'user_id' => Auth::user()->id,
                'purchase_id' => $purchase->id,
                'company_id' => Auth::user()->company_id
            ]);

            DB::commit();

            return redirect()->route('admin.purchases.index')
                ->with('message', '¡Compra registrada exitosamente!')
                ->with('icons', 'success');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('message', 'Por favor, corrija los errores en el formulario.')
                ->with('icons', 'error');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Si se subió un archivo, eliminarlo
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }

            Log::error('Error al crear compra: ' . $e->getMessage(), [
                'user_id' => Auth::user()->id,
                'company_id' => Auth::user()->company_id,
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('message', 'Hubo un problema al registrar la compra. Por favor, inténtelo de nuevo.')
                ->with('icons', 'error');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function getProductDetails($code)
    {
        try {
            $product = Product::where('code', $code)
                ->where('company_id', Auth::user()->company_id)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'price' => $product->sale_price,
                    'purchase_price' => $product->sale_price,
                    'stock' => $product->stock
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener detalles del producto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos del producto'
            ], 500);
        }
    }

    public function getProductByCode($code)
    {
        try {
            $product = Product::where('code', $code)
                ->where('company_id', Auth::user()->company_id)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'price' => $product->sale_price,
                    'purchase_price' => $product->sale_price,
                    'stock' => $product->stock
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener producto por código: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar el producto'
            ], 500);
        }
    }
}
