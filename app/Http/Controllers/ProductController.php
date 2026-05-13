<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
            if (! $this->currencies) {
                $this->currencies = DB::table('currencies')
                    ->select('id', 'name', 'code', 'symbol', 'country_id')
                    ->where('country_id', $this->company->country)
                    ->first();
            }

            return $next($request);
        });
    }

    public function index()
    {
        Gate::authorize('products.index');

        return view('admin.v2.products.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('admin.v2.products.create');
        } catch (\Exception $e) {

            return redirect()->back()
                ->with('message', 'Error al cargar el formulario')
                ->with('icons', 'error');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ProductService $productService)
    {
        // Validación (formularios clásicos en admin/products)
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
            'category_id' => 'required|exists:categories,id',
            'include_in_catalog' => 'sometimes|boolean',
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
            'category_id.exists' => 'La categoría seleccionada no existe',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('message', 'Error de validación')
                ->with('icons', 'error');
        }

        try {
            $data = $validator->validated();
            $data['include_in_catalog'] = $request->boolean('include_in_catalog', true);
            $image = $data['image'] ?? null;
            unset($data['image']);

            $productService->create($data, (int) Auth::user()->company_id, $image);

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
            return redirect()->back()
                ->with('message', 'Error al crear el producto: '.$e->getMessage())
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
                    'purchase_price' => number_format((float) $product->purchase_price, 2),
                    'sale_price' => number_format((float) $product->sale_price, 2),
                    'entry_date' => $product->entry_date->format('d/m/Y'),
                    'entry_days_ago' => $product->entry_date->diffForHumans(),
                    'category' => $product->category->name,
                    'created_at' => $product->created_at->format('d/m/Y H:i'),
                    'updated_at' => $product->updated_at->format('d/m/Y H:i'),
                ],
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'icons' => 'error',
                'message' => 'Error al obtener los datos del producto',
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $product = Product::select('id', 'code', 'name', 'description', 'image', 'stock', 'min_stock', 'max_stock', 'purchase_price', 'sale_price', 'entry_date', 'category_id', 'company_id', 'created_at', 'updated_at')
                ->where('id', $id)
                ->where('company_id', $this->company->id)
                ->firstOrFail();

            return view('admin.v2.products.edit', compact('product'));
        } catch (\Exception $e) {

            return redirect()->route('admin.products.index')
                ->with('message', 'Error al cargar el formulario de edición')
                ->with('icons', 'error');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, ProductService $productService)
    {
        // Optimizar consulta de producto con select específico
        $product = Product::select('id', 'code', 'name', 'description', 'image', 'stock', 'min_stock', 'max_stock', 'purchase_price', 'sale_price', 'entry_date', 'category_id', 'company_id')
            ->where('id', $id)
            ->where('company_id', $this->company->id)
            ->firstOrFail();

        // Validación
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:products,code,'.$product->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'required|integer|gt:min_stock',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0|gt:purchase_price',
            'entry_date' => 'required|date|before_or_equal:today',
            'category_id' => 'required|exists:categories,id',
            'include_in_catalog' => 'sometimes|boolean',
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
            'category_id.exists' => 'La categoría seleccionada no existe',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('message', 'Error de validación')
                ->with('icons', 'error');
        }

        try {
            $data = $validator->validated();
            $data['include_in_catalog'] = $request->boolean('include_in_catalog', true);
            $image = $data['image'] ?? null;
            unset($data['image']);

            $productService->update($product, $data, $image);

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
            return redirect()->back()
                ->with('message', 'Error al actualizar el producto: '.$e->getMessage())
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
                    $reasons[] = 'tiene ventas asociadas';
                }
                if ($product->purchase_details_count > 0) {
                    $reasons[] = 'tiene compras asociadas';
                }

                $reasonText = implode(' y ', $reasons);

                return response()->json([
                    'status' => 'error',
                    'message' => "No se puede eliminar el producto porque {$reasonText}",
                    'sales_count' => $product->sale_details_count,
                    'purchases_count' => $product->purchase_details_count,
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
                'message' => 'Producto eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el producto',
            ], 500);
        }
    }

    public function report(Request $request)
    {
        Gate::authorize('products.report');

        $company = $this->company;
        $currency = $this->currencies;

        $products = Product::query()
            ->with(['category', 'supplier'])
            ->where('products.company_id', $this->company->id)
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->orderBy('categories.name')
            ->orderByDesc('products.stock')
            ->orderBy('products.name')
            ->select('products.*')
            ->get();

        $emittedAt = now();
        $filename = 'reporte-productos-'.$emittedAt->format('Y-m-d_His').'.pdf';

        $pdf = Pdf::loadView('pdf.products.report', compact('products', 'company', 'currency', 'emittedAt'))
            ->setPaper('letter', 'portrait')
            ->setOption('enable_php', true)
            ->addInfo([
                'Title' => 'Inventario de productos',
                'Author' => $company->name ?? config('app.name'),
            ]);

        if ($request->boolean('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
