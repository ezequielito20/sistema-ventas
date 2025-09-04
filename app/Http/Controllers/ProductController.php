<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
         
         // Obtener la moneda de la empresa configurada
         if ($this->company && $this->company->currency) {
            // Buscar la moneda por código en lugar de por país
            $this->currencies = DB::table('currencies')
               ->select('id', 'name', 'code', 'symbol', 'country_id')
               ->where('code', $this->company->currency)
               ->first();
         }
         
         // Fallback si no se encuentra la moneda configurada
         if (!$this->currencies) {
            $this->currencies = DB::table('currencies')
               ->select('id', 'name', 'code', 'symbol', 'country_id')
               ->where('country_id', $this->company->country)
               ->first();
         }

         return $next($request);
      });
   }

   /**
    * Genera paginación inteligente con ventana dinámica
    */
   private function generateSmartPagination($paginator, $windowSize = 2)
   {
      $currentPage = $paginator->currentPage();
      $lastPage = $paginator->lastPage();
      
      $links = [];
      
      // Siempre agregar la primera página
      $links[] = 1;
      
      // Calcular el rango de páginas alrededor de la página actual
      $start = max(2, $currentPage - $windowSize);
      $end = min($lastPage - 1, $currentPage + $windowSize);
      
      // Agregar separador si hay gap entre la primera página y el rango
      if ($start > 2) {
         $links[] = '...';
      }
      
      // Agregar páginas en el rango
      for ($i = $start; $i <= $end; $i++) {
         if ($i > 1 && $i < $lastPage) {
            $links[] = $i;
         }
      }
      
      // Agregar separador si hay gap entre el rango y la última página
      if ($end < $lastPage - 1) {
         $links[] = '...';
      }
      
      // Siempre agregar la última página (si no es la primera)
      if ($lastPage > 1) {
         $links[] = $lastPage;
      }
      
      // Agregar propiedades al paginador
      $paginator->smartLinks = $links;
      $paginator->hasPrevious = $paginator->previousPageUrl() !== null;
      $paginator->hasNext = $paginator->nextPageUrl() !== null;
      $paginator->previousPageUrl = $paginator->previousPageUrl();
      $paginator->nextPageUrl = $paginator->nextPageUrl();
      $paginator->firstPageUrl = $paginator->url(1);
      $paginator->lastPageUrl = $paginator->url($lastPage);
      
      return $paginator;
   }

   public function index(Request $request)
   {
      try {
         // Optimizar consulta de productos con eager loading y select específico
         $query = Product::select('id', 'name', 'code', 'description', 'stock', 'purchase_price', 'sale_price', 'image', 'category_id', 'company_id', 'created_at', 'updated_at')
            ->with(['category:id,name'])
            ->where('company_id', $this->company->id);

         // Búsqueda por texto: nombre, código, categoría
         if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
               $q->where('name', 'ILIKE', "%{$search}%")
                 ->orWhere('code', 'ILIKE', "%{$search}%")
                 ->orWhereHas('category', function ($cq) use ($search) {
                    $cq->where('name', 'ILIKE', "%{$search}%");
                 });
            });
         }

         // Filtro por categoría
         if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
         }

         // Filtro por estado de stock: low|normal|high (según umbrales simples)
         if ($request->filled('stock_status')) {
            $stock = $request->input('stock_status');
            if ($stock === 'low') {
               $query->where('stock', '<=', 10);
            } elseif ($stock === 'high') {
               $query->where('stock', '>', 50);
            } else { // normal
               $query->whereBetween('stock', [11, 50]);
            }
         }

         // Paginación del lado del servidor
         $products = $query->orderBy('name')->paginate(12)->withQueryString();
         
         // Aplicar paginación inteligente
         $products = $this->generateSmartPagination($products, 2);

         // Optimizar consulta de categorías con select específico
         $categories = Category::select('id', 'name', 'company_id')
            ->where('company_id', $this->company->id)
            ->orderBy('name')
            ->get();

         $currency = $this->currencies;
         $company = $this->company;
         
         // Calcular estadísticas usando todos los productos de la empresa (no filtrados)
         $allProducts = Product::select('id', 'stock', 'purchase_price', 'sale_price')
            ->where('company_id', $this->company->id)
            ->get();

         $totalProducts = $allProducts->count();
         $lowStockProducts = $allProducts->where('stock', '<=', 10)->count();
         
         // Valor de compra total
         $totalPurchaseValue = $allProducts->sum(function ($product) {
            return $product->stock * $product->purchase_price;
         });

         // Valor de venta total
         $totalSaleValue = $allProducts->sum(function ($product) {
            return $product->stock * $product->sale_price;
         });

         // Ganancia potencial y porcentaje
         $potentialProfit = $totalSaleValue - $totalPurchaseValue;
         $profitPercentage = $totalPurchaseValue > 0 
            ? (($totalSaleValue - $totalPurchaseValue) / $totalPurchaseValue) * 100 
            : 0;

         // Optimización de gates - verificar permisos una sola vez
         $permissions = [
            'products.report' => Gate::allows('products.report'),
            'products.create' => Gate::allows('products.create'),
            'products.show' => Gate::allows('products.show'),
            'products.edit' => Gate::allows('products.edit'),
            'products.destroy' => Gate::allows('products.destroy')
         ];

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
            'company',
            'permissions'
         ));
      } catch (\Exception $e) {

         return redirect()->back()
            ->with('message', 'Error al cargar los productos: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create(Request $request)
   {
      try {
         // Optimizar consulta de categorías con select específico
         $categories = Category::select('id', 'name', 'company_id')
            ->where('company_id', $this->company->id)
            ->orderBy('name')
            ->get();
         $currency = $this->currencies;
         $company = $this->company;
         
         // Capturar la URL de referencia para redirección posterior
         $referrerUrl = $request->header('referer');
         if ($referrerUrl && !str_contains($referrerUrl, 'products/create')) {
            session(['products_referrer' => $referrerUrl]);
         }
         
         return view('admin.products.create', compact('categories', 'currency', 'company'));
      } catch (\Exception $e) {

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

         // Verificar si hay una URL de referencia guardada
         $referrerUrl = session('products_referrer');
         if ($referrerUrl) {
            // Limpiar la session
            session()->forget('products_referrer');
            
            return redirect($referrerUrl)
                ->with('message', 'Producto creado exitosamente')
                ->with('icons', 'success');
         }

         // Fallback: redirigir a la lista de productos
         return redirect()->route('admin.products.index')
            ->with('message', 'Producto creado exitosamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();


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
         // Optimizar consulta con select específico y eager loading
         $product = Product::select('id', 'code', 'name', 'description', 'image', 'stock', 'min_stock', 'max_stock', 'purchase_price', 'sale_price', 'entry_date', 'category_id', 'created_at', 'updated_at')
            ->with(['category:id,name'])
            ->where('id', $id)
            ->where('company_id', $this->company->id)
            ->firstOrFail();

         return response()->json([
            'status' => 'success',
            'product' => [
               'id' => $product->id,
               'code' => $product->code,
               'name' => $product->name,
               'description' => $product->description ?? 'Sin descripción',
               'image' => $product->image_url,
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

         return response()->json([
            'icons' => 'error',
            'message' => 'Error al obtener los datos del producto'
         ], 500);
      }
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(Request $request, $id)
   {
      try {
         // Optimizar consulta de producto con select específico
         $product = Product::select('id', 'code', 'name', 'description', 'image', 'stock', 'min_stock', 'max_stock', 'purchase_price', 'sale_price', 'entry_date', 'category_id', 'company_id', 'created_at', 'updated_at')
            ->where('id', $id)
            ->where('company_id', $this->company->id)
            ->firstOrFail();
            
         // Optimizar consulta de categorías con select específico
         $categories = Category::select('id', 'name', 'company_id')
            ->where('company_id', $this->company->id)
            ->orderBy('name')
            ->get();
         $currency = $this->currencies;
         $company = $this->company;
         
         // Capturar la URL de referencia para redirección posterior
         $referrerUrl = $request->header('referer');
         if ($referrerUrl && !str_contains($referrerUrl, 'products/edit')) {
            session(['products_referrer' => $referrerUrl]);
         }
         
         return view('admin.products.edit', compact('product', 'categories', 'currency', 'company'));
      } catch (\Exception $e) {

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
      // Optimizar consulta de producto con select específico
      $product = Product::select('id', 'code', 'name', 'description', 'image', 'stock', 'min_stock', 'max_stock', 'purchase_price', 'sale_price', 'entry_date', 'category_id', 'company_id')
         ->where('id', $id)
         ->where('company_id', $this->company->id)
         ->firstOrFail();

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

         // Verificar si hay una URL de referencia guardada
         $referrerUrl = session('products_referrer');
         if ($referrerUrl) {
            // Limpiar la session
            session()->forget('products_referrer');
            
            return redirect($referrerUrl)
                ->with('message', 'Producto actualizado exitosamente')
                ->with('icons', 'success');
         }

         // Fallback: redirigir a la lista de productos
         return redirect()->route('admin.products.index')
            ->with('message', 'Producto actualizado exitosamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();


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
         $product = Product::withCount(['saleDetails', 'purchaseDetails'])->findOrFail($id);

         // Verificar si el producto tiene ventas o compras asociadas
         if ($product->sale_details_count > 0 || $product->purchase_details_count > 0) {
            $reasons = [];
            if ($product->sale_details_count > 0) {
               $reasons[] = "tiene ventas asociadas";
            }
            if ($product->purchase_details_count > 0) {
               $reasons[] = "tiene compras asociadas";
            }
            
            $reasonText = implode(' y ', $reasons);
            
            return response()->json([
               'status' => 'error',
               'message' => "No se puede eliminar el producto porque {$reasonText}",
               'sales_count' => $product->sale_details_count,
               'purchases_count' => $product->purchase_details_count
            ], 200);
         }

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
