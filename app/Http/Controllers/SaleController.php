<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      try {
         $companyId = Auth::user()->company_id;

         // Obtener ventas con sus relaciones
         $sales = Sale::with(['saleDetails.product', 'customer', 'company'])
            ->where('company_id', $companyId)
            ->get();

         // Calcular estadísticas
         $totalSales = $sales->sum(function($sale) {
            return $sale->saleDetails->count();
         });
         $totalAmount = $sales->sum('total_price');
         $monthlySales = $sales->filter(function ($sale) {
            return $sale->sale_date->isCurrentMonth();
         })->count();
         
         // Calcular ticket promedio
         $averageTicket = $sales->count() > 0 
            ? $totalAmount / $sales->count() 
            : 0;

         return view('admin.sales.index', compact(
            'sales',
            'totalSales',
            'totalAmount',
            'monthlySales',
            'averageTicket'
         ));
      } catch (\Exception $e) {
         Log::error('Error en index de ventas: ' . $e->getMessage());
         return redirect()->back()
            ->with('message', 'Error al cargar las ventas')
            ->with('icons', 'error');
      }
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      try {
         $companyId = Auth::user()->company_id;

         // Obtener productos y clientes de la compañía actual
         $products = Product::where('company_id', $companyId)
            // ->where('status', true)
            ->get();
         $customers = Customer::where('company_id', $companyId)
            // ->where('status', true)
            ->get();

         return view('admin.sales.create', compact('products', 'customers'));
      } catch (\Exception $e) {
         Log::error('Error en SaleController@create: ' . $e->getMessage());
         return redirect()->back()
            ->with('message', 'Error al cargar el formulario de venta')
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
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
            // 'payment_type' => 'required|in:cash,card,transfer',
            'items' => 'required|array|min:1',
            'total_price' => 'required|numeric|min:0',
         ]);

         DB::beginTransaction();

         // Crear la venta principal
         $sale = Sale::create([
            'sale_date' => $validated['sale_date'],
            'total_price' => $validated['total_price'],
            'company_id' => Auth::user()->company_id,
            'customer_id' => $validated['customer_id'],
         ]);

         // Procesar cada producto en la venta
         foreach ($request->items as $key => $item) {
            // Obtener el producto
            $product = Product::where('id', $key)
               ->where('company_id', Auth::user()->company_id)
               ->firstOrFail();

            // Verificar stock disponible
            if ($product->stock < $item['quantity']) {
               throw new \Exception("Stock insuficiente para el producto: {$product->name}");
            }

            // Crear el detalle de la venta
            SaleDetail::create([
               'quantity' => $item['quantity'],
               'sale_id' => $sale->id,
               'product_id' => $product->id,
            ]);

            // Actualizar el stock del producto
            $product->stock -= $item['quantity'];
            $product->save();
         }

         // Log de la acción
         Log::info('Venta creada exitosamente', [
            'user_id' => Auth::user()->id,
            'sale_id' => $sale->id,
            'company_id' => Auth::user()->company_id,
            'total' => $validated['total_price'],
         ]);

         DB::commit();

         return redirect()->route('admin.sales.index')
            ->with('message', '¡Venta registrada exitosamente!')
            ->with('icons', 'success');

      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Error al crear venta: ' . $e->getMessage(), [
            'user_id' => Auth::user()->id,
            'company_id' => Auth::user()->company_id,
            'data' => $request->all()
         ]);

         return redirect()->back()
            ->with('message', 'Error al registrar la venta: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Display the specified resource.
    */
   public function show(Sale $sale)
   {
      //
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(Sale $sale)
   {
      //
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(UpdateSaleRequest $request, Sale $sale)
   {
      //
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(Sale $sale)
   {
      //
   }

   /**
    * Obtiene los detalles de un producto por su código para el modal
    */
   public function getProductDetails($code)
   {
      try {
         $product = Product::with('category')
            ->where('code', $code)
            ->where('company_id', Auth::user()->company_id)
            ->first();

         if (!$product) {
            return response()->json([
               'success' => false,
               'message' => 'Producto no encontrado'
            ], 404);
         }

         // Preparar la respuesta con los datos necesarios para la vista
         $productData = [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'stock' => $product->stock,
            'sale_price' => $product->sale_price,
            'category' => $product->category->name,
            'stock_status_class' => $product->stock > 10 ? 'success' : ($product->stock > 0 ? 'warning' : 'danger'),
            'image_url' => $product->image_url
         ];

         return response()->json([
            'success' => true,
            'product' => $productData
         ]);

      } catch (\Exception $e) {
         Log::error('Error al obtener detalles del producto: ' . $e->getMessage());
         return response()->json([
            'success' => false,
            'message' => 'Error al cargar los datos del producto'
         ], 500);
      }
   }

   /**
    * Busca un producto por código para la entrada rápida
    */
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

         // Verificar stock
         if ($product->stock <= 0) {
            return response()->json([
               'success' => false,
               'message' => 'El producto no tiene stock disponible'
            ], 400);
         }

         // Preparar la respuesta con los datos necesarios
         $productData = [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'stock' => $product->stock,
            'sale_price' => $product->sale_price,
            'stock_status_class' => $product->stock > 10 ? 'success' : 'warning'
         ];

         return response()->json([
            'success' => true,
            'product' => $productData
         ]);

      } catch (\Exception $e) {
         Log::error('Error al buscar producto por código: ' . $e->getMessage());
         return response()->json([
            'success' => false,
            'message' => 'Error al buscar el producto'
         ], 500);
      }
   }
}
