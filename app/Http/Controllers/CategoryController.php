<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Category;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
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
         $company = $this->company;
         $categories = Category::where('company_id', $company->id)->orderBy('name', 'asc')->get();
         $totalCategories = $categories->count();

         return view('admin.categories.index', compact(
            'categories',
            'totalCategories',
            'company'
         ));
      } catch (\Exception $e) {
         Log::error('Error loading categories: ' . $e->getMessage());
         return redirect()->back()
            ->with('message', 'Error al cargar las categorías')
            ->with('icons', 'error');
      }
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create(Request $request)
   {
      try {
         $company = $this->company;
         
         // Capturar la URL de referencia para redirección posterior
         $referrerUrl = $request->header('referer');
         if ($referrerUrl && !str_contains($referrerUrl, 'categories/create')) {
            session(['categories_referrer' => $referrerUrl]);
         }
         
         return view('admin.categories.create', compact('company'));
      } catch (\Exception $e) {
         Log::error('Error loading create category form: ' . $e->getMessage());
         return redirect()->route('admin.categories.index')
            ->with('message', 'Error al cargar el formulario de creación')
            ->with('icons', 'error');
      }
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request)
   {
      // Validación del request
      $validated = $request->validate([
         'name' => [
            'required',
            'string',
            'max:255',
            'unique:categories,name',
            // Asegura que el nombre solo contenga letras, números, espacios y guiones
            'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
         ],
         'description' => [
            'nullable',
            'string',
            'max:255'
         ]
      ], [
         'name.required' => 'El nombre de la categoría es obligatorio',
         'name.string' => 'El nombre debe ser una cadena de texto',
         'name.max' => 'El nombre no puede exceder los 255 caracteres',
         'name.unique' => 'Ya existe una categoría con este nombre',
         'name.regex' => 'El nombre solo puede contener letras, números, espacios y guiones',
         'description.string' => 'La descripción debe ser una cadena de texto',
         'description.max' => 'La descripción no puede exceder los 255 caracteres',
      ]);

      try {
         DB::beginTransaction();

         // Limpieza y formateo del nombre
         $categoryName = trim($validated['name']);

         // Crear la categoría
         $category = Category::create([
            'name' => $categoryName,
            'description' => $validated['description'] ?? null,
            'company_id' => Auth::user()->company_id,
         ]);

         DB::commit();

         // Verificar si hay una URL de referencia guardada
         $referrerUrl = session('categories_referrer');
         if ($referrerUrl) {
            // Limpiar la session
            session()->forget('categories_referrer');
            
            return redirect($referrerUrl)
                ->with('message', 'Categoría creada exitosamente')
                ->with('icons', 'success');
         }

         // Fallback: redirigir a la lista de categorías
         return redirect()->route('admin.categories.index')
            ->with('message', 'Categoría creada exitosamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Error creating category: ' . $e->getMessage());

         return redirect()->back()
            ->with('message', 'Error al crear la categoría: ' . $e->getMessage())
            ->with('icons', 'error')
            ->withInput();
      }
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(Request $request, $id)
   {
      $company = $this->company;
      $category = Category::where('id', $id)->where('company_id', $company->id)->first();
      
      // Capturar la URL de referencia para redirección posterior
      $referrerUrl = $request->header('referer');
      if ($referrerUrl && !str_contains($referrerUrl, 'categories/edit')) {
         session(['categories_referrer' => $referrerUrl]);
      }
      
      return view('admin.categories.edit', compact('category', 'company'));
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, $id)
   {
      $category = Category::findOrFail($id);

      // Validación del request
      $validated = $request->validate([
         'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('categories')->ignore($category->id)->where(function ($query) {
               return $query->where('company_id', Auth::user()->company_id);
            }),
            'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
         ],
         'description' => [
            'nullable',
            'string',
            'max:255'
         ]
      ], [
         'name.required' => 'El nombre de la categoría es obligatorio',
         'name.string' => 'El nombre debe ser una cadena de texto',
         'name.max' => 'El nombre no puede exceder los 255 caracteres',
         'name.unique' => 'Ya existe una categoría con este nombre en tu empresa',
         'name.regex' => 'El nombre solo puede contener letras, números, espacios y guiones',
         'description.string' => 'La descripción debe ser una cadena de texto',
         'description.max' => 'La descripción no puede exceder los 255 caracteres',
      ]);

      try {
         DB::beginTransaction();

         $categoryName = trim($validated['name']);

         // Actualizar la categoría
         $category->update([
            'name' => $categoryName,
            'description' => $validated['description'] ?? null,

         ]);

         DB::commit();

         // Verificar si hay una URL de referencia guardada
         $referrerUrl = session('categories_referrer');
         if ($referrerUrl) {
            // Limpiar la session
            session()->forget('categories_referrer');
            
            return redirect($referrerUrl)
                ->with('message', 'Categoría actualizada exitosamente')
                ->with('icons', 'success');
         }

         // Fallback: redirigir a la lista de categorías
         return redirect()->route('admin.categories.index')
            ->with('message', 'Categoría actualizada exitosamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Error updating category: ' . $e->getMessage());

         return redirect()->back()
            ->with('message', 'Error al actualizar la categoría')
            ->with('icons', 'error')
            ->withInput();
      }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy($id)
   {
      $category = Category::findOrFail($id);

      try {


         $category->delete();

         return response()->json([
            'status' => 'success',
            'message' => 'Categoría eliminada exitosamente'
         ]);
      } catch (\Exception $e) {
         Log::error('Error deleting category: ' . $e->getMessage());
         return response()->json([
            'status' => 'error',
            'message' => 'Error al eliminar la categoría'
         ], 500);
      }
   }

   /**
    * Display the specified resource.
    */
   public function show($id)
   {
      try {
         $category = Category::findOrFail($id);
         return response()->json([
            'status' => 'success',
            'category' => [
               'id' => $category->id,
               'name' => $category->name,
               'description' => $category->formattedDescription,
               'created_at' => $category->created_at->format('d/m/Y H:i'),
               'updated_at' => $category->updated_at->format('d/m/Y H:i')
            ]
         ]);
      } catch (\Exception $e) {
         Log::error('Error showing category: ' . $e->getMessage());
         return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener los datos de la categoría'
         ], 500);
      }
   }

   public function report()
   {
      $company = $this->company;
      $categories = Category::withCount('products')->where('company_id', $company->id)
      ->orderBy('products_count', 'desc')
      ->orderBy('name', 'asc')
      ->get();
      $pdf = PDF::loadView('admin.categories.report', compact('categories', 'company'));
      return $pdf->stream('reporte-categorias.pdf');
   }
}
