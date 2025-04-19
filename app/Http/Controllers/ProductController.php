<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductController extends Controller
{
   public $currencies;
   protected $company;

   public function __construct()
   {
      $this->middleware(function ($request, $next) {
         $this->company = Auth::user()->company;
         $this->currencies = DB::table('currencies')
            ->where('country_id', $this->company->country)
            ->first();

         return $next($request);
      });
   }
   public function index()
   {
      try {
         $products = Product::with('category')
            ->where('products.company_id', $this->company->id)
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->orderBy('categories.name')
            ->orderBy('products.stock', 'desc')
            ->orderBy('products.name')
            ->select('products.*')
            ->get();
         $categories = Category::where('company_id', $this->company->id)->get();
         $currency = $this->currencies;
         $company = $this->company;
         
         // Calcular estadísticas
         $totalProducts = $products->count();
         $lowStockProducts = $products->filter->hasLowStock()->count();
         
         // Calcula el valor total del inventario basado en precio de compra
         $totalPurchaseValue = $products->sum(function ($product) {
            return $product->stock * $product->purchase_price;
         });

         // Calcula el valor total del inventario basado en precio de venta
         $totalSaleValue = $products->sum(function ($product) {
            return $product->stock * $product->sale_price;
         });

         // Calcula la ganancia potencial
         $potentialProfit = $totalSaleValue - $totalPurchaseValue;

         // Calcula el porcentaje de ganancia
         $profitPercentage = $totalPurchaseValue > 0 
            ? (($totalSaleValue - $totalPurchaseValue) / $totalPurchaseValue) * 100 
            : 0;

         return view('admin.products.index', compact(
            'products',
            'categories',
            'totalProducts',
            'lowStockProducts',
            'totalPurchaseValue',
            'totalSaleValue',
            'potentialProfit',
            'profitPercentage',
            'currency',
            'company'
         ));
      } catch (\Exception $e) {
         Log::error('Error loading products: ' . $e->getMessage());
         return redirect()->back()
            ->with('message', 'Error al cargar los productos: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      try {
         $categories = Category::where('company_id', $this->company->id)->get();
         $currency = $this->currencies;
         $company = $this->company;
         return view('admin.products.create', compact('categories', 'currency', 'company'));
      } catch (\Exception $e) {
         Log::error('Error loading create product form: ' . $e->getMessage());
         return redirect()->back()
            ->with('message', 'Error al cargar el formulario')
            ->with('icons', 'error');
      }
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request)
   {
      // Validación
      $validator = Validator::make($request->all(), [
         'code' => 'required|string|max:50|unique:products',
         'name' => 'required|string|max:255',
         'description' => 'nullable|string',
         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
         'stock' => 'required|integer|min:0',
         'min_stock' => 'required|integer|min:0',
         'max_stock' => 'required|integer|gt:min_stock',
         'purchase_price' => 'required|numeric|min:0',
         'sale_price' => 'required|numeric|min:0|gt:purchase_price',
         'entry_date' => 'required|date|before_or_equal:today',
         'category_id' => 'required|exists:categories,id'
      ], [
         'code.required' => 'El código es obligatorio',
         'code.unique' => 'Este código ya está en uso',
         'name.required' => 'El nombre es obligatorio',
         'stock.min' => 'El stock no puede ser negativo',
         'min_stock.min' => 'El stock mínimo no puede ser negativo',
         'max_stock.gt' => 'El stock máximo debe ser mayor que el stock mínimo',
         'purchase_price.min' => 'El precio de compra debe ser mayor a 0',
         'sale_price.gt' => 'El precio de venta debe ser mayor al precio de compra',
         'category_id.required' => 'La categoría es obligatoria',
         'category_id.exists' => 'La categoría seleccionada no existe'
      ]);

      if ($validator->fails()) {
         return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('message', 'Error de validación')
            ->with('icons', 'error');
      }

      try {
         DB::beginTransaction();

         $data = $validator->validated();
         $data['company_id'] = Auth::user()->company_id;

         // Procesar imagen si existe
         if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = 'storage/' . $path;
         }

         // Crear producto
         $product = Product::create($data);

         DB::commit();

         return redirect()->route('admin.products.index')
            ->with('message', 'Producto creado exitosamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Error creating product: ' . $e->getMessage());

         // Eliminar imagen si se subió
         if (isset($path)) {
            Storage::disk('public')->delete($path);
         }

         return redirect()->back()
            ->with('message', 'Error al crear el producto: ' . $e->getMessage())
            ->with('icons', 'error')
            ->withInput();
      }
   }

   /**
    * Display the specified resource.
    */
   public function show($id)
   {
      try {
         $product = Product::with('category')->findOrFail($id);

         return response()->json([
            'status' => 'success',
            'product' => [
               'id' => $product->id,
               'code' => $product->code,
               'name' => $product->name,
               'description' => $product->description ?? 'Sin descripción',
               'image' => $product->image ? Storage::url($product->image) : null,
               'stock' => $product->stock,
               'min_stock' => $product->min_stock,
               'max_stock' => $product->max_stock,
               'purchase_price' => number_format($product->purchase_price, 2),
               'sale_price' => number_format($product->sale_price, 2),
               'entry_date' => $product->entry_date->format('d/m/Y'),
               'entry_days_ago' => $product->entry_date->diffForHumans(),
               'category' => $product->category->name,
               'created_at' => $product->created_at->format('d/m/Y H:i'),
               'updated_at' => $product->updated_at->format('d/m/Y H:i')
            ]
         ]);
      } catch (\Exception $e) {
         Log::error('Error showing product: ' . $e->getMessage());
         return response()->json([
            'icons' => 'error',
            'message' => 'Error al obtener los datos del producto'
         ], 500);
      }
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit($id)
   {
      try {
         $product = Product::find($id);
         $categories = Category::where('company_id', $this->company->id)->get();
         $currency = $this->currencies;
         $company = $this->company;
         return view('admin.products.edit', compact('product', 'categories', 'currency', 'company'));
      } catch (\Exception $e) {
         Log::error('Error loading edit product form: ' . $e->getMessage());
         return redirect()->back()
            ->with('message', 'Error al cargar el formulario de edición')
            ->with('icons', 'error');
      }
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, $id)
   {
      $product = Product::find($id);

      // Validación
      $validator = Validator::make($request->all(), [
         'code' => 'required|string|max:50|unique:products,code,' . $product->id,
         'name' => 'required|string|max:255',
         'description' => 'nullable|string',
         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
         'stock' => 'required|integer|min:0',
         'min_stock' => 'required|integer|min:0',
         'max_stock' => 'required|integer|gt:min_stock',
         'purchase_price' => 'required|numeric|min:0',
         'sale_price' => 'required|numeric|min:0|gt:purchase_price',
         'entry_date' => 'required|date|before_or_equal:today',
         'category_id' => 'required|exists:categories,id'
      ], [
         'code.required' => 'El código es obligatorio',
         'code.unique' => 'Este código ya está en uso',
         'name.required' => 'El nombre es obligatorio',
         'stock.min' => 'El stock no puede ser negativo',
         'min_stock.min' => 'El stock mínimo no puede ser negativo',
         'max_stock.gt' => 'El stock máximo debe ser mayor que el stock mínimo',
         'purchase_price.min' => 'El precio de compra debe ser mayor a 0',
         'sale_price.gt' => 'El precio de venta debe ser mayor al precio de compra',
         'category_id.required' => 'La categoría es obligatoria',
         'category_id.exists' => 'La categoría seleccionada no existe'
      ]);

      if ($validator->fails()) {
         return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('message', 'Error de validación')
            ->with('icons', 'error');
      }

      try {
         DB::beginTransaction();

         $data = $validator->validated();

         // Procesar nueva imagen si existe
         if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($product->image && Storage::disk('public')->exists(str_replace('storage/', '', $product->image))) {
               Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
            }

            $path = $request->file('image')->store('products', 'public');
            $data['image'] = 'storage/' . $path;
         }

         $product->update($data);

         DB::commit();

         return redirect()->route('admin.products.index')
            ->with('message', 'Producto actualizado exitosamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Error updating product: ' . $e->getMessage());

         // Eliminar nueva imagen si se subió
         if (isset($path)) {
            Storage::disk('public')->delete($path);
         }

         return redirect()->back()
            ->with('message', 'Error al actualizar el producto: ' . $e->getMessage())
            ->with('icons', 'error')
            ->withInput();
      }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy($id)
   {
      try {
         $product = Product::findOrFail($id);
         DB::beginTransaction();

         // Eliminar imagen si existe
         if ($product->image && Storage::disk('public')->exists(str_replace('storage/', '', $product->image))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
         }

         $product->delete();

         DB::commit();

         return response()->json([
            'status' => 'success',
            'message' => 'Producto eliminado exitosamente'
         ]);
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Error deleting product: ' . $e->getMessage());

         return response()->json([
            'status' => 'error',
            'message' => 'Error al eliminar el producto'
         ], 500);
      }
   }

   public function report()
   {
      $company = $this->company;
      $currency = $this->currencies;
      $products = Product::with(['category','supplier'])
         ->where('products.company_id', $this->company->id)
         ->join('categories', 'products.category_id', '=', 'categories.id')
         ->orderBy('categories.name')
         ->orderBy('products.stock', 'desc')
         ->orderBy('products.name')
         ->select('products.*')
            ->get();
      $pdf = PDF::loadView('admin.products.report', compact('products', 'company', 'currency'))
         ->setPaper('a4', 'landscape');
      return $pdf->stream('reporte-productos.pdf');
   }
}
