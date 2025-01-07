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
         $totalPurchases = $purchases->count();
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
            'total_amount' => 'required|numeric|min:0',
         ]);

         $payment_receipt = str_replace('-', '', $validated['purchase_date']) . count($request->items) . str_pad((int)$validated['total_amount'], '0', STR_PAD_LEFT);
         // dd($payment_receipt);

         DB::beginTransaction();

         // Crear la compra principal
         $purchase = Purchase::create([
            'purchase_date' => $validated['purchase_date'],
            'payment_receipt' => $payment_receipt,
            'total_price' => $validated['total_amount'],
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
               'product_price' => $item['price'],
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
            ->with('icon', 'success');

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
            ->with('icon', 'error');
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