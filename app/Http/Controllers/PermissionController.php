<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
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
         $company = $this->company;
         
         // Optimización de gates - verificar permisos una sola vez
         $permissions = [
            'can_report' => Gate::allows('permissions.report'),
            'can_create' => Gate::allows('permissions.create'),
            'can_edit' => Gate::allows('permissions.edit'),
            'can_show' => Gate::allows('permissions.show'),
            'can_destroy' => Gate::allows('permissions.destroy'),
         ];
         
         // Si es una petición AJAX para búsqueda, devolver JSON
         if ($request->ajax() && $request->has('search')) {
            return $this->searchPermissions($request);
         }
         
         // Optimización: Obtener estadísticas con consultas optimizadas
         $totalPermissions = Permission::count();
         
         // Obtener conteo de permisos activos (con roles o usuarios)
         $activePermissions = Permission::whereHas('roles')
            ->orWhereHas('users')
            ->count();
         
         // Obtener conteo de roles únicos que tienen permisos
         $rolesCount = DB::table('roles')
            ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->distinct('roles.id')
            ->count('roles.id');
         
         // Permisos sin usar
         $unusedPermissions = Permission::whereDoesntHave('roles')
            ->whereDoesntHave('users')
            ->count();

         // Optimización: Obtener permisos paginados con conteos optimizados
         $permissionsList = Permission::with(['roles'])
            ->orderBy('name', 'asc')
            ->paginate(10);

         // Optimización: Obtener conteo de usuarios por permiso en una sola consulta
         $usersCountByPermission = DB::table('permissions')
            ->leftJoin('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->leftJoin('roles', 'role_has_permissions.role_id', '=', 'roles.id')
            ->leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->select('permissions.id', DB::raw('COUNT(DISTINCT users.id) as users_count'))
            ->groupBy('permissions.id')
            ->pluck('users_count', 'permissions.id')
            ->toArray();

         // Asignar conteos de usuarios a los permisos
         $permissionsList->getCollection()->transform(function ($permission) use ($usersCountByPermission) {
            $permission->users_count = $usersCountByPermission[$permission->id] ?? 0;
            return $permission;
         });

         return view('admin.permissions.index', compact(
            'permissionsList',
            'permissions',
            'totalPermissions',
            'activePermissions',
            'rolesCount',
            'unusedPermissions',
            'company',
         ));
      } catch (\Exception $e) {
         return redirect()->back()
            ->with('message', 'Error al cargar los permisos: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Mensajes de validación personalizados
    */
   private $messages = [
      'name.required' => 'El nombre del permiso es obligatorio.',
      'name.string' => 'El nombre del permiso debe ser texto.',
      'name.max' => 'El nombre del permiso no puede tener más de :max caracteres.',
      'name.unique' => 'Ya existe un permiso con este nombre.',
      'name.regex' => 'El nombre del permiso debe seguir el formato: modulo.accion',
      'name.table_exists' => 'El módulo ":module" no es válido. Módulos válidos: users, categories, suppliers, products, customers, purchases, sales, cash_counts, cash_movements, debt_payments, companies, roles, permissions, orders, notifications.',
   ];

   /**
    * Obtener las reglas de validación
    */
   private function getValidationRules()
   {
      return [
         'name' => [
            'required',
            'string',
            'max:255',
            'unique:permissions,name',
            'regex:/^[a-z]+\.[a-z]+$/',
            function ($attribute, $value, $fail) {
               $module = explode('.', $value)[0];
               
               // Lista de módulos válidos (tablas existentes en el sistema)
               $validModules = [
                  'users', 'categories', 'suppliers', 'products', 'customers', 
                  'purchases', 'sales', 'cash_counts', 'cash_movements', 
                  'debt_payments', 'companies', 'roles', 'permissions',
                  'orders', 'notifications', 'countries', 'states', 'cities', 
                  'municipalities', 'parishes', 'currencies'
               ];
               
               if (!in_array($module, $validModules)) {
                  $fail(__($this->messages['name.table_exists'], ['module' => $module]));
               }
            },
         ]
      ];
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      try {
         $company = $this->company;
         return view('admin.permissions.create', compact('company'));
      } catch (\Exception $e) {
         return redirect()->route('admin.permissions.index')
            ->with('message', 'Error al cargar el formulario de creación')
            ->with('icons', 'error');
      }
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request)
   {
      try {
         // Validar los datos usando el método que retorna las reglas
         $validatedData = $request->validate($this->getValidationRules(), $this->messages);

         // Crear el permiso
         $permission = Permission::create([
            'name' => strtolower($validatedData['name']),
            'guard_name' => 'web'
         ]);



         return redirect()->route('admin.permissions.index')
            ->with('message', 'Permiso creado correctamente')
            ->with('icons', 'success');
      } catch (\Illuminate\Validation\ValidationException $e) {
         return redirect()->back()
            ->withErrors($e->validator)
            ->withInput()
            ->with('message', 'Error de validación')
            ->with('icons', 'error');
      } catch (\Exception $e) {

         return redirect()->back()
            ->with('message', 'Error al crear el permiso')
            ->with('icons', 'error')
            ->withInput();
      }
   }

   /**
    * Display the specified resource.
    */
   public function show(string $id)
   {
      try {
         $permission = Permission::with(['roles'])->findOrFail($id);
         
         // Optimización: Obtener conteo de usuarios en una sola consulta
         $usersCount = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->where('role_has_permissions.permission_id', $id)
            ->distinct('users.id')
            ->count('users.id');

         // Preparar datos para la respuesta
         $permissionData = [
            'id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
            'roles_count' => $permission->roles->count(),
            'users_count' => $usersCount,
            'created_at' => $permission->created_at->format('d/m/Y H:i'),
            'updated_at' => $permission->updated_at->format('d/m/Y H:i'),
            'roles' => $permission->roles->pluck('name')->toArray(),
            'users' => [] // Por simplicidad, no mostramos nombres de usuarios
         ];

         return response()->json([
            'status' => 'success',
            'permission' => $permissionData
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'No se pudieron obtener los datos del permiso'
         ], 500);
      }
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(string $id)
   {
      try {
         $company = $this->company;
         $permission = Permission::findOrFail($id);
         return view('admin.permissions.edit', compact('permission', 'company'));
      } catch (\Exception $e) {
         return redirect()->route('admin.permissions.index')
            ->with('message', 'Error al cargar el formulario de edición')
            ->with('icon', 'error');
      }
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, string $id)
   {
      try {
         $permission = Permission::findOrFail($id);

         // Validación personalizada
         $validated = $request->validate([
            'name' => [
               'required',
               'string',
               'max:255',
               'unique:permissions,name,' . $id,
               'regex:/^[a-z]+\.[a-z]+$/',
               function ($attribute, $value, $fail) {
                  $module = explode('.', $value)[0];
                  
                  // Lista de módulos válidos (tablas existentes en el sistema)
                  $validModules = [
                     'users', 'categories', 'suppliers', 'products', 'customers', 
                     'purchases', 'sales', 'cash_counts', 'cash_movements', 
                     'debt_payments', 'companies', 'roles', 'permissions',
                     'orders', 'notifications', 'countries', 'states', 'cities', 
                     'municipalities', 'parishes', 'currencies'
                  ];
                  
                  if (!in_array($module, $validModules)) {
                     $fail('El módulo "' . $module . '" no es válido. Módulos válidos: users, categories, suppliers, products, customers, purchases, sales, cash_counts, cash_movements, debt_payments, companies, roles, permissions, orders, notifications.');
                  }
               },
               function ($attribute, $value, $fail) use ($permission) {
                  if (
                     $permission->name !== strtolower($value) &&
                     ($permission->roles->count() > 0 || $permission->users->count() > 0)
                  ) {
                     $fail('No se puede modificar el nombre del permiso porque está en uso por roles o usuarios.');
                  }
               }
            ]
         ], [
            'name.required' => 'El nombre del permiso es obligatorio.',
            'name.string' => 'El nombre del permiso debe ser texto.',
            'name.max' => 'El nombre del permiso no puede tener más de :max caracteres.',
            'name.unique' => 'Ya existe un permiso con este nombre.',
            'name.regex' => 'El nombre del permiso debe seguir el formato: modulo.accion'
         ]);

         // Actualizar el permiso
         $permission->update([
            'name' => strtolower($validated['name'])
         ]);



         return redirect()->route('admin.permissions.index')
            ->with('message', 'Permiso actualizado correctamente')
            ->with('icons', 'success');
      } catch (\Illuminate\Validation\ValidationException $e) {
         return redirect()->back()
            ->withErrors($e->validator)
            ->withInput()
            ->with('message', 'Error de validación')
            ->with('icons', 'error');
      } catch (\Exception $e) {

         return redirect()->back()
            ->with('message', 'Error al actualizar el permiso')
            ->with('icons', 'error')
            ->withInput();
      }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(string $id)
   {
      try {
         $permission = Permission::findOrFail($id);

         // Verificar si el permiso está en uso
         if ($permission->roles->count() > 0 || $permission->users->count() > 0) {
            return response()->json([
               'message' => 'No se puede eliminar el permiso porque está en uso',
               'icons' => 'error'
            ], 422);
         }

         // Eliminar el permiso
         $permission->delete();



         return response()->json([
            'message' => 'Permiso eliminado correctamente',
            'icons' => 'success'
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'message' => 'Error al eliminar el permiso',
            'icons' => 'error'
         ], 500);
      }
   }

   /**
    * Buscar permisos via AJAX
    */
   private function searchPermissions(Request $request)
   {
      try {
         $searchTerm = $request->get('search', '');
         
         // Construir query base
         $query = Permission::with(['roles']);
         
         // Aplicar filtros de búsqueda
         if (!empty($searchTerm)) {
            $query->where(function($q) use ($searchTerm) {
               $q->where('name', 'ILIKE', "%{$searchTerm}%")
                 ->orWhere('guard_name', 'ILIKE', "%{$searchTerm}%");
            });
         }
         
         // Obtener todos los resultados (sin paginación para búsqueda completa)
         $permissionsList = $query->orderBy('name', 'asc')->get();

         // Optimización: Obtener conteo de usuarios por permiso en una sola consulta
         $usersCountByPermission = DB::table('permissions')
            ->leftJoin('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->leftJoin('roles', 'role_has_permissions.role_id', '=', 'roles.id')
            ->leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->select('permissions.id', DB::raw('COUNT(DISTINCT users.id) as users_count'))
            ->groupBy('permissions.id')
            ->pluck('users_count', 'permissions.id')
            ->toArray();

         // Asignar conteos de usuarios a los permisos
         $permissionsList->transform(function ($permission) use ($usersCountByPermission) {
            $permission->users_count = $usersCountByPermission[$permission->id] ?? 0;
            return $permission;
         });

         // Preparar datos para la vista
         $permissions = [
            'can_report' => Gate::allows('permissions.report'),
            'can_create' => Gate::allows('permissions.create'),
            'can_edit' => Gate::allows('permissions.edit'),
            'can_show' => Gate::allows('permissions.show'),
            'can_destroy' => Gate::allows('permissions.destroy'),
         ];

         return response()->json([
            'status' => 'success',
            'data' => [
               'permissions' => $permissionsList,
               'permissions_config' => $permissions,
               'total' => $permissionsList->count(),
               'search_term' => $searchTerm
            ]
         ]);

      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'Error al buscar permisos: ' . $e->getMessage()
         ], 500);
      }
   }

   public function report()
   {
      //
   }
}
