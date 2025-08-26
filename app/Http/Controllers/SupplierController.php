<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;

class SupplierController extends Controller
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
         $companyId = Auth::user()->company_id;
         $currency = $this->currencies;
         $company = $this->company;

         // Consulta base de proveedores con paginación
         $query = Supplier::where('company_id', $companyId);

         // Búsqueda por nombre de empresa, contacto, email o teléfono
         if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
               $q->where('company_name', 'ILIKE', "%{$search}%")
                 ->orWhere('supplier_name', 'ILIKE', "%{$search}%")
                 ->orWhere('company_email', 'ILIKE', "%{$search}%")
                 ->orWhere('company_phone', 'ILIKE', "%{$search}%")
                 ->orWhere('supplier_phone', 'ILIKE', "%{$search}%")
                 ->orWhere('company_address', 'ILIKE', "%{$search}%");
            });
         }

         // Filtro por estado (activo/inactivo) - para futura implementación
         if ($request->filled('status')) {
            $status = $request->input('status');
            // Aquí se puede implementar cuando se añada el campo status
         }

         // Filtro por rango de fechas de registro
         if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
         }

         if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
         }

         // Filtro por cantidad de productos (proveedores con más/menos productos)
         if ($request->filled('products_min')) {
            $query->withCount('products')->having('products_count', '>=', $request->input('products_min'));
         }

         if ($request->filled('products_max')) {
            $query->withCount('products')->having('products_count', '<=', $request->input('products_max'));
         }

         // Paginación del lado del servidor
         $suppliers = $query->orderBy('company_name', 'asc')->paginate(12)->withQueryString();
         
         // Aplicar paginación inteligente
         $suppliers = $this->generateSmartPagination($suppliers, 2);

         // Estadísticas para los widgets (usando consultas directas para eficiencia)
         $totalSuppliers = Supplier::where('company_id', $companyId)->count();
         $activeSuppliers = $totalSuppliers; // Por ahora todos están activos
         $recentSuppliers = Supplier::where('company_id', $companyId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
         $inactiveSuppliers = 0; // Para futura implementación

         // Optimización de gates
         $permissions = [
            'suppliers.report' => Gate::allows('suppliers.report'),
            'suppliers.create' => Gate::allows('suppliers.create'),
            'suppliers.show' => Gate::allows('suppliers.show'),
            'suppliers.edit' => Gate::allows('suppliers.edit'),
            'suppliers.destroy' => Gate::allows('suppliers.destroy'),
         ];

         // Retornar vista con datos
         return view('admin.suppliers.index', compact(
            'suppliers',
            'totalSuppliers',
            'activeSuppliers',
            'recentSuppliers',
            'inactiveSuppliers',
            'currency',
            'company',
            'permissions'
         ))
            ->with('message', 'Proveedores cargados correctamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         // Redireccionar con mensaje de error
         return redirect()->route('admin.index')
            ->with('message', 'Hubo un problema al cargar los proveedores: ' . $e->getMessage())
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
         if ($referrerUrl && !str_contains($referrerUrl, 'suppliers/create')) {
            session(['suppliers_referrer' => $referrerUrl]);
         }
         
         return view('admin.suppliers.create', compact('company'));
      } catch (\Exception $e) {
         return redirect()->route('admin.suppliers.index')
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
            'company_name' => [
               'required',
               'string',
               'max:255',
               'unique:suppliers,company_name,NULL,id,company_id,' . Auth::user()->company_id,
            ],
            'company_address' => ['required', 'string', 'max:255'],
            'company_phone' => [
               'required',
               'string',
               'max:20',
               'regex:/^\(\d{3}\)\s\d{3}-\d{4}$/',
            ],
            'company_email' => [
               'required',
               'email',
               'max:255',
               'unique:suppliers,company_email,NULL,id,company_id,' . Auth::user()->company_id,
            ],
            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_phone' => [
               'required',
               'string',
               'max:20',
               'regex:/^\(\d{3}\)\s\d{3}-\d{4}$/',
            ],
         ], [
            'company_name.required' => 'El nombre de la empresa es obligatorio.',
            'company_name.unique' => 'Esta empresa ya está registrada en su compañía.',
            'company_address.required' => 'La dirección de la empresa es obligatoria.',
            'company_phone.required' => 'El teléfono de la empresa es obligatorio.',
            'company_phone.regex' => 'El formato del teléfono debe ser (123) 456-7890.',
            'company_email.required' => 'El correo electrónico es obligatorio.',
            'company_email.email' => 'Ingrese un correo electrónico válido.',
            'company_email.unique' => 'Este correo electrónico ya está registrado.',
            'supplier_name.required' => 'El nombre del contacto es obligatorio.',
            'supplier_phone.required' => 'El teléfono del contacto es obligatorio.',
            'supplier_phone.regex' => 'El formato del teléfono debe ser (123) 456-7890.',
         ]);

         // Preparar los datos para guardar
         $supplierData = array_merge($validated, [
            'company_id' => Auth::user()->company_id,
         ]);

         // Crear el proveedor
         $supplier = Supplier::create($supplierData);

         // Si es una petición AJAX, devolver JSON
         if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
               'success' => true,
               'message' => '¡Proveedor creado exitosamente!',
               'supplier' => $supplier
            ]);
         }

         // Verificar si hay una URL de referencia guardada
         $referrerUrl = session('suppliers_referrer');
         if ($referrerUrl) {
            // Limpiar la session
            session()->forget('suppliers_referrer');
            
            return redirect($referrerUrl)
                ->with('message', '¡Proveedor creado exitosamente!')
                ->with('icons', 'success');
         }

         // Fallback: redirigir a la lista de proveedores
         return redirect()->route('admin.suppliers.index')
            ->with('message', '¡Proveedor creado exitosamente!')
            ->with('icons', 'success');
      } catch (\Illuminate\Validation\ValidationException $e) {
         // Si es una petición AJAX, devolver errores en JSON
         if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
               'success' => false,
               'message' => 'Por favor, corrija los errores en el formulario.',
               'errors' => $e->errors()
            ], 422);
         }

         return redirect()->back()
            ->withErrors($e->validator)
            ->withInput()
            ->with('message', 'Por favor, corrija los errores en el formulario.')
            ->with('icons', 'error');
      } catch (\Exception $e) {
         // Si es una petición AJAX, devolver error en JSON
         if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
               'success' => false,
               'message' => 'Hubo un problema al crear el proveedor. Por favor, inténtelo de nuevo.'
            ], 500);
         }

         return redirect()->back()
            ->withInput()
            ->with('message', 'Hubo un problema al crear el proveedor. Por favor, inténtelo de nuevo.')
            ->with('icons', 'error');
      }
   }

   /**
    * Display the specified resource.
    */
   public function show($id)
   {
      try {
         $supplier = Supplier::findOrFail($id);

         // Verificar que el proveedor pertenece a la compañía del usuario
         if ($supplier->company_id !== Auth::user()->company_id) {
            return response()->json([
               'icons' => 'error',
               'message' => 'No tiene permiso para ver este proveedor'
            ], 403);
         }

         // Obtener productos del proveedor usando el modelo Product
         $productDetails = Product::where('supplier_id', $id)
            ->orderBy('name')
            ->get();

         return response()->json([
            'icons' => 'success',
            'supplier' => [
               'company_name' => $supplier->company_name,
               'company_email' => $supplier->company_email,
               'company_phone' => $supplier->company_phone,
               'company_address' => $supplier->company_address,
               'supplier_name' => $supplier->supplier_name,
               'supplier_phone' => $supplier->supplier_phone,
               'created_at' => $supplier->created_at->format('d/m/Y H:i'),
               'updated_at' => $supplier->updated_at->format('d/m/Y H:i'),
            ],
            'stats' => $productDetails
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'icons' => 'error',
            'message' => 'Error al cargar los datos del proveedor'
         ], 500);
      }
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(Request $request, $id)
   {
      try {
         $company = $this->company;
         // Buscar el proveedor
         $supplier = Supplier::findOrFail($id);

         // Verificar que el proveedor pertenece a la compañía del usuario
         if ($supplier->company_id !== Auth::user()->company_id) {
            return redirect()->route('admin.suppliers.index')
               ->with('message', 'No tiene permiso para editar este proveedor')
               ->with('icons', 'error');
         }

         // Capturar la URL de referencia para redirección posterior
         $referrerUrl = $request->header('referer');
         if ($referrerUrl && !str_contains($referrerUrl, 'suppliers/edit')) {
            session(['suppliers_referrer' => $referrerUrl]);
         }

         // Retornar vista con datos del proveedor
         return view('admin.suppliers.edit', compact('supplier', 'company'));
      } catch (\Exception $e) {
         return redirect()->route('admin.suppliers.index')
            ->with('message', 'No se pudo cargar el formulario de edición')
            ->with('icons', 'error');
      }
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, $id)
   {
      try {
         // Buscar el proveedor
         $supplier = Supplier::findOrFail($id);

         // Verificar que el proveedor pertenece a la compañía del usuario
         if ($supplier->company_id !== Auth::user()->company_id) {
            // Si es una petición AJAX, devolver error en JSON
            if ($request->ajax() || $request->wantsJson()) {
               return response()->json([
                  'success' => false,
                  'message' => 'No tiene permiso para actualizar este proveedor'
               ], 403);
            }

            return redirect()->route('admin.suppliers.index')
               ->with('message', 'No tiene permiso para actualizar este proveedor')
               ->with('icons', 'error');
         }

         // Validación personalizada
         $validated = $request->validate([
            'company_name' => [
               'required',
               'string',
               'max:255',
               'unique:suppliers,company_name,' . $id . ',id,company_id,' . Auth::user()->company_id,
            ],
            'company_address' => ['required', 'string', 'max:255'],
            'company_phone' => [
               'required',
               'string',
               'max:20',
               'regex:/^\(\d{3}\)\s\d{3}-\d{4}$/',
            ],
            'company_email' => [
               'required',
               'email',
               'max:255',
               'unique:suppliers,company_email,' . $id . ',id,company_id,' . Auth::user()->company_id,
            ],
            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_phone' => [
               'required',
               'string',
               'max:20',
               'regex:/^\(\d{3}\)\s\d{3}-\d{4}$/',
            ],
         ], [
            'company_name.required' => 'El nombre de la empresa es obligatorio.',
            'company_name.unique' => 'Esta empresa ya está registrada en su compañía.',
            'company_address.required' => 'La dirección de la empresa es obligatoria.',
            'company_phone.required' => 'El teléfono de la empresa es obligatorio.',
            'company_phone.regex' => 'El formato del teléfono debe ser (123) 456-7890.',
            'company_email.required' => 'El correo electrónico es obligatorio.',
            'company_email.email' => 'Ingrese un correo electrónico válido.',
            'company_email.unique' => 'Este correo electrónico ya está registrado.',
            'supplier_name.required' => 'El nombre del contacto es obligatorio.',
            'supplier_phone.required' => 'El teléfono del contacto es obligatorio.',
            'supplier_phone.regex' => 'El formato del teléfono debe ser (123) 456-7890.',
         ]);

         // Actualizar el proveedor
         $supplier->update($validated);

         // Si es una petición AJAX, devolver JSON
         if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
               'success' => true,
               'message' => '¡Proveedor actualizado exitosamente!',
               'supplier' => $supplier
            ]);
         }

         // Verificar si hay una URL de referencia guardada
         $referrerUrl = session('suppliers_referrer');
         if ($referrerUrl) {
            // Limpiar la session
            session()->forget('suppliers_referrer');
            
            return redirect($referrerUrl)
                ->with('message', '¡Proveedor actualizado exitosamente!')
                ->with('icons', 'success');
         }

         // Fallback: redirigir a la lista de proveedores
         return redirect()->route('admin.suppliers.index')
            ->with('message', '¡Proveedor actualizado exitosamente!')
            ->with('icons', 'success');
      } catch (\Illuminate\Validation\ValidationException $e) {
         // Si es una petición AJAX, devolver errores en JSON
         if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
               'success' => false,
               'message' => 'Por favor, corrija los errores en el formulario.',
               'errors' => $e->errors()
            ], 422);
         }

         return redirect()->back()
            ->withErrors($e->validator)
            ->withInput()
            ->with('message', 'Por favor, corrija los errores en el formulario.')
            ->with('icons', 'error');
      } catch (\Exception $e) {
         // Si es una petición AJAX, devolver error en JSON
         if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
               'success' => false,
               'message' => 'Hubo un problema al actualizar el proveedor. Por favor, inténtelo de nuevo.'
            ], 500);
         }

         return redirect()->back()
            ->withInput()
            ->with('message', 'Hubo un problema al actualizar el proveedor. Por favor, inténtelo de nuevo.')
            ->with('icons', 'error');
      }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy($id)
   {
      try {
         // Buscar el proveedor
         $supplier = Supplier::findOrFail($id);

         // Verificar que el proveedor pertenece a la compañía del usuario
         if ($supplier->company_id !== Auth::user()->company_id) {
            return response()->json([
               'success' => false,
               'message' => 'No tiene permiso para eliminar este proveedor',
               'icons' => 'error'
            ], 403);
         }

         // Guardar información para el log antes de eliminar
         $supplierInfo = [
            'id' => $supplier->id,
            'company_name' => $supplier->company_name,
            'company_id' => $supplier->company_id
         ];

         // Eliminar el proveedor
         $supplier->delete();

         // Retornar respuesta exitosa
         return response()->json([
            'success' => true,
            'message' => '¡Proveedor eliminado exitosamente!',
            'icons' => 'success'
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Hubo un problema al eliminar el proveedor. Por favor, inténtelo de nuevo.',
            'icons' => 'error'
         ], 500);
      }
   }

   public function report()
   {
      $company = Company::find(Auth::user()->company_id);
      $suppliers = Supplier::withCount('products')->where('company_id', $company->id)->orderBy('company_name', 'asc')->get();
      $pdf = PDF::loadView('admin.suppliers.report', compact('suppliers', 'company'))
         ->setPaper('a4', 'landscape');
      return $pdf->stream('reporte-proveedores.pdf');
   }
}
