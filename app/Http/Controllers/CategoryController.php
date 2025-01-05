<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // try {
            $categories = Category::withCount('products')->get();
            $totalCategories = $categories->count();
            // $productsCount = $categories->sum('products_count');

            return view('admin.categories.index', compact(
                'categories',
                'totalCategories',
                // 'productsCount'
            ));
        // } catch (\Exception $e) {
        //     Log::error('Error loading categories: ' . $e->getMessage());
        //     return redirect()->back()
        //         ->with('message', 'Error al cargar las categorías')
        //         ->with('icons', 'error');
        // }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación del request
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio',
            'name.unique' => 'Ya existe una categoría con este nombre',
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'description.max' => 'La descripción no puede exceder los 255 caracteres',
        ]);

        try {
            DB::beginTransaction();

            $category = Category::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Categoría creada exitosamente',
                'category' => $category
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating category: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear la categoría: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        try {
            $category->load('products');
            
            return response()->json([
                'status' => 'success',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'created_at' => $category->created_at->format('d/m/Y H:i:s'),
                    'updated_at' => $category->updated_at->format('d/m/Y H:i:s'),
                    'products_count' => $category->products->count()
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        try {
            return response()->json([
                'status' => 'success',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            Log::error('Error editing category: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cargar los datos de la categoría'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        // Validación del request
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category)],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio',
            'name.unique' => 'Ya existe una categoría con este nombre',
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'description.max' => 'La descripción no puede exceder los 255 caracteres',
        ]);

        try {
            DB::beginTransaction();

            $category->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Categoría actualizada exitosamente',
                'category' => $category
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating category: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar la categoría: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            // Verificar si tiene productos asociados
            if ($category->products()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede eliminar la categoría porque tiene productos asociados'
                ], 422);
            }

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
}
