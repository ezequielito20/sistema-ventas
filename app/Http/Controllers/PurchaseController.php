<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      try {
         $companyId = Auth::user()->company_id;


         // Obtener compras con sus relaciones
         $purchases = Purchase::with(['details.product', 'details.supplier', 'company'])
            ->where('company_id', $companyId)
            ->get();

         // Calcular estadísticas
         $totalPurchases = $purchases->sum(function ($purchase) {
            return $purchase->details->count();
         });
         $totalAmount = $purchases->sum('total_price');
         $monthlyPurchases = $purchases->filter(function ($purchase) {
            return $purchase->purchase_date->isCurrentMonth();
         })->count();
         $pendingDeliveries = $purchases->whereNull('payment_receipt')->count();

         return view('admin.purchases.index', compact(
            'purchases',
            'totalPurchases',
            'totalAmount',
            'monthlyPurchases',
            'pendingDeliveries'
         ));
      } catch (\Exception $e) {
         Log::error('Error en index de compras: ' . $e->getMessage());
         return redirect()->back()
            ->with('message', 'Error al cargar las compras')
            ->with('icon', 'error');
      }
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
      // dd($request->all());
      try {
         // Validación de los datos
         $validated = $request->validate([
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'total_price' => 'required|numeric|min:0',
         ]);

         $payment_receipt = str_replace('-', '', $validated['purchase_date']) . count($request->items) . str_pad((int)$validated['total_price'], '0', STR_PAD_LEFT);
         // dd($payment_receipt);

         DB::beginTransaction();

         // Crear la compra principal
         $purchase = Purchase::create([
            'purchase_date' => $validated['purchase_date'],
            'payment_receipt' => $payment_receipt,
            'total_price' => $validated['total_price'],
            'company_id' => Auth::user()->company_id,
         ]);

         // Procesar cada producto en la compra
         foreach ($request->items as $key => $item) {
            // dd($key, $item);
            // Obtener el producto
            $product = Product::where('id', $key)
               ->where('company_id', Auth::user()->company_id)
               ->firstOrFail();

            // Crear el detalle de la compra
            PurchaseDetail::create([
               'quantity' => $item['quantity'],
               'purchase_id' => $purchase->id,
               'supplier_id' => $product->supplier_id,
               'product_id' => $product->id,
            ]);

            // Actualizar el stock del producto
            $product->stock += $item['quantity'];
            $product->save();
         }

         // Log de la acción
         Log::info('Compra creada exitosamente', [
            'user_id' => Auth::user()->id,
            'purchase_id' => $purchase->id,
            'company_id' => Auth::user()->company_id,
            'total' => $request->total,
            'items_count' => count($request->items)
         ]);

         DB::commit();

         return redirect()->route('admin.purchases.index')
            ->with('message', '¡Compra registrada exitosamente!')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Error al crear compra: ' . $e->getMessage(), [
            'user_id' => Auth::user()->id,
            'company_id' => Auth::user()->company_id,
            'data' => $request->all()
         ]);

         return redirect()->back()
            ->withInput()
            ->with('message', 'Error al registrar la compra: ' . $e->getMessage())
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
      try {
         $companyId = Auth::user()->company_id;

         // Obtener la compra con sus detalles y productos
         $purchase = Purchase::with(['details.product', 'details.supplier'])
            ->where('company_id', $companyId)
            ->findOrFail($id);

         // Formatear los detalles para JavaScript
         $purchaseDetails = $purchase->details->map(function ($detail) {
            return [
               'id' => $detail->product->id,
               'code' => $detail->product->code,
               'name' => $detail->product->name,
               'quantity' => $detail->quantity,
               'price' => $detail->product->purchase_price,
               'purchase_price' => $detail->product->purchase_price,
               'supplier_id' => $detail->supplier_id
            ];
         });

         // Obtener productos y proveedores
         $products = Product::where('company_id', $companyId)->get();
         $suppliers = Supplier::where('company_id', $companyId)->get();

         return view('admin.purchases.edit', compact('purchase', 'products', 'suppliers', 'purchaseDetails'));
      } catch (\Exception $e) {
         Log::error('Error en PurchaseController@edit: ' . $e->getMessage());
         return redirect()->route('admin.purchases.index')
            ->with('message', 'Error al cargar el formulario de edición')
            ->with('icons', 'error');
      }
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, $id)
   {
      // dd($request->all());
      try {
         // Validación de los datos
         $validated = $request->validate([
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
         ]);

         DB::beginTransaction();

         // Obtener la compra
         $purchase = Purchase::where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

         // Guardar el estado anterior para el log
         $previousState = [
            'purchase_date' => $purchase->purchase_date,
            'total_price' => $purchase->total_price,
            'details' => $purchase->details->map(function ($detail) {
               return [
                  'product_id' => $detail->product_id,
                  'quantity' => $detail->quantity,
                  'price' => $detail->product->purchase_price,
               ];
            })->toArray()
         ];

         // Actualizar fecha y total
         $purchase->purchase_date = $validated['purchase_date'];
         $purchase->total_price = $validated['total_price'];
         $purchase->save();

         // Obtener los IDs de los detalles actuales
         $currentDetailIds = $purchase->details->pluck('id')->toArray();
         $newDetailIds = [];

         // Procesar cada producto en la compra
         foreach ($request->items as $productId => $item) {
            $product = Product::where('id', $productId)
               ->where('company_id', Auth::user()->company_id)
               ->firstOrFail();

            // Buscar si ya existe el detalle
            $detail = PurchaseDetail::where('purchase_id', $purchase->id)
               ->where('product_id', $productId)
               ->first();

            if ($detail) {
               // Actualizar stock: restar la cantidad anterior y sumar la nueva
               $stockDifference = $item['quantity'] - $detail->quantity;
               $product->stock += $stockDifference;

               // Actualizar detalle existente
               $detail->update([
                  'quantity' => $item['quantity'],
               ]);

               $newDetailIds[] = $detail->id;
            } else {
               // Crear nuevo detalle
               $detail = PurchaseDetail::create([
                  'quantity' => $item['quantity'],
                  'purchase_id' => $purchase->id,
                  'supplier_id' => $product->supplier_id,
                  'product_id' => $product->id,
               ]);

               // Actualizar stock sumando la nueva cantidad
               $product->stock += $item['quantity'];
               $newDetailIds[] = $detail->id;
            }

            $product->save();
         }

         // Eliminar detalles que ya no están en la compra
         $detailsToDelete = array_diff($currentDetailIds, $newDetailIds);
         foreach ($detailsToDelete as $detailId) {
            $detail = PurchaseDetail::find($detailId);
            if ($detail) {
               // Restar del stock la cantidad que se elimina
               $product = Product::find($detail->product_id);
               if ($product) {
                  $product->stock -= $detail->quantity;
                  $product->save();
               }
               $detail->delete();
            }
         }

         // Log de la actualización
         Log::info('Compra actualizada exitosamente', [
            'user_id' => Auth::user()->id,
            'purchase_id' => $purchase->id,
            'previous_state' => $previousState,
            'new_state' => [
               'purchase_date' => $purchase->purchase_date,
               'total_price' => $purchase->total_price,
               'items' => $request->items
            ]
         ]);

         DB::commit();

         return redirect()->route('admin.purchases.index')
            ->with('message', '¡Compra actualizada exitosamente!')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Error al actualizar compra: ' . $e->getMessage(), [
            'user_id' => Auth::user()->id,
            'purchase_id' => $id,
            'request_data' => $request->all()
         ]);

         return redirect()->back()
            ->withInput()
            ->with('message', 'Error al actualizar la compra: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy($id)
   {
      try {
         // Buscar la compra
         $purchase = Purchase::findOrFail($id);

         // Verificar que la compra pertenece a la compañía del usuario
         if ($purchase->company_id !== Auth::user()->company_id) {
            Log::warning('Intento de eliminación no autorizada de compra', [
               'user_id' => Auth::user()->id,
               'purchase_id' => $id
            ]);

            return response()->json([
               'success' => false,
               'message' => 'No tiene permiso para eliminar esta compra',
               'icons' => 'error'
            ], 403);
         }

         // Iniciar transacción
         DB::beginTransaction();

         // Guardar información para el log antes de eliminar
         $purchaseInfo = [
            'id' => $purchase->id,
            'payment_receipt' => $purchase->payment_receipt,
            'total_price' => $purchase->total_price,
            'company_id' => $purchase->company_id
         ];

         // Revertir el stock de los productos
         foreach ($purchase->details as $detail) {
            $product = $detail->product;
            $product->stock -= $detail->quantity;
            $product->save();
         }

         // Eliminar la compra (esto también eliminará los detalles por la relación cascade)
         $purchase->delete();

         // Confirmar transacción
         DB::commit();

         // Log de la eliminación
         Log::info('Compra eliminada exitosamente', [
            'user_id' => Auth::user()->id,
            'purchase_info' => $purchaseInfo
         ]);

         // Retornar respuesta exitosa
         return response()->json([
            'success' => true,
            'message' => '¡Compra eliminada exitosamente!',
            'icons' => 'success'
         ]);
      } catch (\Exception $e) {
         // Revertir transacción en caso de error
         DB::rollBack();

         Log::error('Error al eliminar compra: ' . $e->getMessage(), [
            'user_id' => Auth::user()->id,
            'purchase_id' => $id
         ]);

         return response()->json([
            'success' => false,
            'message' => 'Hubo un problema al eliminar la compra. Por favor, inténtelo de nuevo.',
            'icons' => 'error'
         ], 500);
      }
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
               'price' => $product->purchase_price,
               'purchase_price' => $product->purchase_price,
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
               'price' => $product->purchase_price,
               'purchase_price' => $product->purchase_price,
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

   public function getDetails($id)
   {
      try {
         $purchaseDetails = PurchaseDetail::with(['product.category', 'purchase'])
            ->where('purchase_id', $id)
            ->get();

         $purchase = Purchase::find($id);

         $details = $purchaseDetails->map(function ($detail) {
            return [
               'quantity' => $detail->quantity,
               'product_price' => $detail->product->purchase_price,
               'product' => [
                  'code' => $detail->product->code,
                  'name' => $detail->product->name,
                  'category' => $detail->product->category->name ?? 'N/A',
                  'image_url' => $detail->product->image_url,
                  // 'stock' => $detail->product->stock
               ],
               'subtotal' => $detail->quantity * $detail->product_price
            ];
         });

         return response()->json([
            'success' => true,
            'purchase' => [
               'id' => $purchase->id,
               'date' => $purchase->purchase_date->format('d/m/Y'),
               'payment_receipt' => $purchase->payment_receipt,
               'total_price' => $purchase->total_price
            ],
            'details' => $details
         ]);
      } catch (\Exception $e) {
         Log::error('Error en getDetails: ' . $e->getMessage());
         return response()->json([
            'success' => false,
            'message' => 'Error al cargar los detalles de la compra'
         ], 500);
      }
   }
}
