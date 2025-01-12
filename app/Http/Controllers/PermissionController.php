<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      try {
         // Obtener todos los permisos ordenados por nombre
         $permissions = Permission::with(['roles', 'users'])
            ->orderBy('name', 'asc')
            ->get();

         // Calcular estadísticas para los widgets
         $totalPermissions = $permissions->count();

         // Permisos activos (los que están en uso por roles o usuarios)
         $activePermissions = $permissions->filter(function ($permission) {
            return $permission->roles->count() > 0 || $permission->users->count() > 0;
         })->count();

         // Contar roles únicos que tienen permisos asignados
         $rolesCount = $permissions->pluck('roles')->flatten()->unique('id')->count();

         // Permisos sin usar (los que no están asignados a ningún rol ni usuario)
         $unusedPermissions = $permissions->filter(function ($permission) {
            return $permission->roles->count() === 0 && $permission->users->count() === 0;
         })->count();

         return view('admin.permissions.index', compact(
            'permissions',
            'totalPermissions',
            'activePermissions',
            'rolesCount',
            'unusedPermissions'
         ));
      } catch (\Exception $e) {
         Log::error('Error en index de permisos: ' . $e->getMessage());
         return redirect()->back()
            ->with('message', 'Error al cargar los permisos')
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
      'name.table_exists' => 'El módulo ":module" no es válido porque no existe una tabla correspondiente en la base de datos.',
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
               if (!Schema::hasTable($module)) {
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
         return view('admin.permissions.create');
      } catch (\Exception $e) {
         Log::error('Error en create de permisos: ' . $e->getMessage());
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

         // Registrar la acción en el log
         Log::info('Permiso creado: ' . $permission->name . ' por el usuario: ' . Auth::user()->name);

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
         Log::error('Error al crear permiso: ' . $e->getMessage());
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
         $permission = Permission::with(['roles', 'users'])->findOrFail($id);

         return response()->json([
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
            'roles' => $permission->roles->pluck('name'),
            'users' => $permission->users->pluck('name'),
            'created_at' => $permission->created_at->format('d/m/Y H:i:s'),
            'updated_at' => $permission->updated_at->format('d/m/Y H:i:s')
         ]);
      } catch (\Exception $e) {
         Log::error('Error al mostrar permiso: ' . $e->getMessage());
         return response()->json(['error' => 'Error al obtener los detalles del permiso'], 500);
      }
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(string $id)
   {
      try {
         $permission = Permission::findOrFail($id);
         return view('admin.permissions.edit', compact('permission'));
      } catch (\Exception $e) {
         Log::error('Error en edit de permisos: ' . $e->getMessage());
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
                    if (!Schema::hasTable($module)) {
                        $fail('El módulo "' . $module . '" no es válido porque no existe una tabla correspondiente en la base de datos.');
                    }
                },
                function ($attribute, $value, $fail) use ($permission) {
                    if ($permission->name !== strtolower($value) && 
                        ($permission->roles->count() > 0 || $permission->users->count() > 0)) {
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

         // Registrar la acción en el log
         Log::info('Permiso actualizado: ' . $permission->name . ' por el usuario: ' . Auth::user()->name);

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
         Log::error('Error al actualizar permiso: ' . $e->getMessage());
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

         // Registrar la acción en el log
         Log::info('Permiso eliminado: ' . $permission->name . ' por el usuario: ' . Auth::user()->name);

         return response()->json([
            'message' => 'Permiso eliminado correctamente',
            'icons' => 'success'
         ]);
      } catch (\Exception $e) {
         Log::error('Error al eliminar permiso: ' . $e->getMessage());
         return response()->json([
            'message' => 'Error al eliminar el permiso',
            'icons' => 'error'
         ], 500);
      }
   }

   public function report()
   {
      //
   }
}
