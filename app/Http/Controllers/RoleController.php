<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Barryvdh\DomPDF\Facade\Pdf;

class RoleController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      $roles = Role::with('permissions')
         ->orderBy('created_at', 'desc')
         ->get();

      return view('admin.roles.index', compact('roles'));
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      return view('admin.roles.create');
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request)
   {
      // Validación básica del request
      $validated = $request->validate([
         'name' => [
            'required',
            'string',
            'max:255',
            'unique:roles,name',
            // Asegura que el nombre solo contenga letras, números, espacios y guiones
            'regex:/^[a-zA-Z0-9\s-]+$/',
         ]
      ], [
         'name.required' => 'El nombre del rol es obligatorio',
         'name.string' => 'El nombre debe ser una cadena de texto',
         'name.max' => 'El nombre no puede exceder los 255 caracteres',
         'name.unique' => 'Este nombre de rol ya existe',
         'name.regex' => 'El nombre solo puede contener letras, números, espacios y guiones',
      ]);

      try {
         DB::beginTransaction();

         // Limpieza y formateo del nombre
         $roleName = trim(strtolower($validated['name']));

         // Verificación adicional de roles del sistema
         $systemRoles = ['admin', 'superadmin', 'administrator', 'root'];
         if (in_array($roleName, $systemRoles)) {
            throw new \Exception('No se pueden crear roles del sistema');
         }

         // Crear el rol
         $role = Role::create([
            'name' => $roleName,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
         ]);

         DB::commit();

         // Registro exitoso
         return redirect()->route('admin.roles.index')
            ->with('message', 'Rol creado exitosamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();

         // Si es un error específico de roles del sistema
         if ($e->getMessage() === 'No se pueden crear roles del sistema') {
            return redirect()->back()
               ->with('message', 'No se pueden crear roles del sistema')
               ->with('icons', 'error')
               ->withInput();
         }

         // Para otros errores
         return redirect()->back()
            ->with('message', 'Error al crear el rol: ' . $e->getMessage())
            ->with('icons', 'error')
            ->withInput();
      }
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit($id)
   {
      $role = Role::findOrFail($id);
      return view('admin.roles.edit', compact('role'));
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, $id)
   {
      $role = Role::findOrFail($id);

      // Validación mejorada del request
      $validated = $request->validate([
         'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('roles')->ignore($role->id),
            // Asegura que el nombre solo contenga letras, números, espacios y guiones
            'regex:/^[a-zA-Z0-9\s-]+$/',
         ]
      ], [
         'name.required' => 'El nombre del rol es obligatorio',
         'name.string' => 'El nombre debe ser una cadena de texto',
         'name.max' => 'El nombre no puede exceder los 255 caracteres',
         'name.unique' => 'Este nombre de rol ya existe',
         'name.regex' => 'El nombre solo puede contener letras, números, espacios y guiones',
      ]);

      try {
         DB::beginTransaction();

         // Limpieza y formateo del nombre
         $roleName = trim(strtolower($validated['name']));

         // Verificación adicional de roles del sistema
         $systemRoles = ['admin', 'superadmin', 'administrator', 'root', 'user'];
         if (in_array($roleName, $systemRoles) && !in_array($role->name, $systemRoles)) {
            throw new \Exception('No se puede cambiar a un nombre de rol del sistema');
         }

         // Verificar si es un rol del sistema que intenta ser modificado
         if (in_array($role->name, $systemRoles) && $role->name !== $roleName) {
            throw new \Exception('No se pueden modificar roles del sistema');
         }

         // Actualizar el rol
         $role->update([
            'name' => $roleName,
            'updated_at' => now(),
         ]);

         DB::commit();

         return redirect()->route('admin.roles.index')
            ->with('message', 'Rol actualizado exitosamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();

         // Si es un error específico de roles del sistema
         if (str_contains($e->getMessage(), 'roles del sistema')) {
            return redirect()->back()
               ->with('message', $e->getMessage())
               ->with('icons', 'error')
               ->withInput();
         }

         // Para otros errores
         return redirect()->back()
            ->with('message', 'Error al actualizar el rol: ' . $e->getMessage())
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
         $role = Role::findOrFail($id);

         // Verificar si es un rol del sistema
         if (in_array($role->name, ['admin', 'user', 'superadmin'])) {
            return response()->json([
               'status' => 'error',
               'message' => 'No se pueden eliminar roles del sistema'
            ], 403);
         }

         // Verificar si el rol tiene usuarios asignados
         if ($role->users()->count() > 0) {
            return response()->json([
               'status' => 'error',
               'message' => 'No se puede eliminar un rol que tiene usuarios asignados'
            ], 403);
         }

         $role->delete();

         return response()->json([
            'status' => 'success',
            'message' => 'Rol eliminado exitosamente'
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'Error al eliminar el rol'
         ], 500);
      }
   }

   /**
    * Display the specified resource.
    */
   public function show($id)
   {
      try {
         $role = Role::with('permissions', 'users')->findOrFail($id);

         return response()->json([
            'status' => 'success',
            'role' => [
               'id' => $role->id,
               'name' => $role->name,
               'created_at' => $role->created_at->format('d/m/Y H:i'),
               'updated_at' => $role->updated_at->format('d/m/Y H:i'),
               'users_count' => $role->users->count(),
               'permissions_count' => $role->permissions->count()
            ]
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener los datos del rol'
         ], 500);
      }
   }

   public function report()
   {
      $roles = Role::all();
      $pdf = PDF::loadView('admin.roles.report', compact('roles'));
      return $pdf->stream('reporte-roles.pdf');
   }
}
