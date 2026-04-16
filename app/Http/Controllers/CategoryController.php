<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Services\CategoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function index(Request $request)
    {
        try {
            return view('admin.v2.categories.index');
        } catch (\Exception $e) {
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
            if ($referrerUrl && ! str_contains($referrerUrl, 'categories/create')) {
                session(['categories_referrer' => $referrerUrl]);
            }

            return view('admin.v2.categories.create', compact('company'));
        } catch (\Exception $e) {
            return redirect()->route('admin.categories.index')
                ->with('message', 'Error al cargar el formulario de creación')
                ->with('icons', 'error');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CategoryService $categoryService)
    {
        $validated = $request->validate(
            $categoryService->rulesForCreate(Auth::user()->company_id),
            $categoryService->validationMessages()
        );

        try {
            DB::beginTransaction();

            $categoryService->createCategory((int) Auth::user()->company_id, $validated);

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

            return redirect()->back()
                ->with('message', 'Error al crear la categoría: '.$e->getMessage())
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
        $category = Category::where('id', $id)->where('company_id', $company->id)->firstOrFail();

        // Capturar la URL de referencia para redirección posterior
        $referrerUrl = $request->header('referer');
        if ($referrerUrl && ! str_contains($referrerUrl, 'categories/edit')) {
            session(['categories_referrer' => $referrerUrl]);
        }

        return view('admin.v2.categories.edit', compact('category', 'company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, CategoryService $categoryService)
    {
        $category = Category::where('id', $id)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        $validated = $request->validate(
            $categoryService->rulesForUpdate($category, (int) Auth::user()->company_id),
            $categoryService->validationMessages()
        );

        try {
            DB::beginTransaction();

            $categoryService->updateCategory($category, (int) Auth::user()->company_id, $validated);

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
        try {
            $category = Category::withCount('products')
                ->where('company_id', Auth::user()->company_id)
                ->findOrFail($id);

            // Verificar si la categoría tiene productos asociados
            if ($category->products_count > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede eliminar la categoría porque tiene productos asociados',
                    'products_count' => $category->products_count,
                ], 200);
            }

            $category->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Categoría eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar la categoría',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $category = Category::where('company_id', Auth::user()->company_id)->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->formattedDescription,
                    'created_at' => $category->created_at->format('d/m/Y H:i'),
                    'updated_at' => $category->updated_at->format('d/m/Y H:i'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los datos de la categoría',
            ], 500);
        }
    }

    public function report(Request $request)
    {
        $company = Company::find(Auth::user()->company_id);
        $categories = Category::withCount('products')
            ->where('company_id', $company->id)
            ->orderByDesc('products_count')
            ->orderBy('name', 'asc')
            ->get();

        $emittedAt = now();
        $filename = 'reporte-categorias-'.$emittedAt->format('Y-m-d_His').'.pdf';

        $pdf = Pdf::loadView('pdf.categories.report', compact('categories', 'company', 'emittedAt'))
            ->setPaper('letter', 'portrait')
            ->setOption('enable_php', true)
            ->addInfo([
                'Title' => 'Informe de categorías',
                'Author' => $company->name ?? config('app.name'),
            ]);

        if ($request->boolean('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
