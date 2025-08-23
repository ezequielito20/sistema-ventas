<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class UserController extends Controller
{
   
   public function index()
   {
      $company = Auth::user()->company;
      $users = User::where('company_id', $company->id)
         ->with(['company', 'roles']) // Eager loading para evitar N+1 queries
         ->orderBy('name', 'asc')
         ->get();
      
      // Optimización de gates - array de permisos hardcodeado
      $permissions = [
         'users.report' => true,
         'users.create' => true,
         'users.show' => true,
         'users.edit' => true,
         'users.destroy' => true,
      ];
      
      return view('admin.users.index', compact('users', 'company', 'permissions'));
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      try {
         $company = Auth::user()->company;

         // Obtener las empresas disponibles
         $companies = Company::select('id', 'name')
            ->where('id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

         // Obtener los roles disponibles de la empresa usando select específico
         $roles = Role::select('id', 'name')
            ->byCompany(Auth::user()->company_id)
            ->get();

         return view('admin.users.create', compact('companies', 'roles', 'company'));
      } catch (\Exception $e) {
         return redirect()->route('admin.users.index')
            ->with('message', 'Error al cargar el formulario de creación: ' . $e->getMessage())
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
         'name' => ['required', 'string', 'max:255'],
         'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
         'password' => [
            'required',
            'string',
            'min:8',
            'confirmed',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/' // Al menos una mayúscula, una minúscula y un número
         ],
         'company_id' => ['required', 'exists:companies,id'],
         'role' => ['required', 'exists:roles,id'],
      ], [
         'name.required' => 'El nombre es obligatorio',
         'name.max' => 'El nombre no puede exceder los 255 caracteres',
         'email.required' => 'El correo electrónico es obligatorio',
         'email.email' => 'El correo electrónico debe ser válido',
         'email.unique' => 'Este correo electrónico ya está registrado',
         'password.required' => 'La contraseña es obligatoria',
         'password.min' => 'La contraseña debe tener al menos 8 caracteres',
         'password.confirmed' => 'Las contraseñas no coinciden',
         'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número',
         'company_id.required' => 'La empresa es obligatoria',
         'company_id.exists' => 'La empresa seleccionada no existe',
         'role.required' => 'El rol es obligatorio',
         'role.exists' => 'El rol seleccionado no existe'
      ]);

      try {
         DB::beginTransaction();

         // Verificar que la empresa pertenezca al usuario autenticado
         if (Auth::user()->company_id != $validated['company_id']) {
            throw new \Exception('No tiene permisos para crear usuarios en esta empresa');
         }

         // Crear el usuario
         $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']), // Convertir email a minúsculas
            'password' => Hash::make($validated['password']),
            'company_id' => $validated['company_id'],
            'email_verified_at' => now(), // Usuario verificado por defecto
         ]);

         // Asignar el rol
         $role = Role::findOrFail($validated['role']);

         // Los roles del sistema no se crean en empresas específicas, 
         // por lo que esta validación ya no es necesaria

         $user->assignRole($role);



         DB::commit();

         return redirect()->route('admin.users.index')
            ->with('message', 'Usuario creado exitosamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();

         return redirect()->back()
            ->with('message', 'Error al crear el usuario: ' . $e->getMessage())
            ->with('icons', 'error')
            ->withInput($request->except('password', 'password_confirmation'));
      }
   }

   /**
    * Display the specified resource.
    */
   public function show($id)
   {
      try {
         // Obtener usuario con sus relaciones usando select específico
         $user = User::select('id', 'name', 'email', 'company_id', 'email_verified_at')
            ->with(['company:id,name', 'roles:id,name'])
            ->where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

         // Preparar la respuesta con solo los campos necesarios
         return response()->json([
            'status' => 'success',
            'user' => [
               'name' => $user->name,
               'email' => $user->email,
               'company_name' => $user->company->name,
               'roles' => $user->roles->map(function ($role) {
                  return [
                     'name' => $role->name,
                     'display_name' => ucfirst($role->name)
                  ];
               }),
               'verified' => $user->email_verified_at ? true : false
            ]
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener los datos del usuario'
         ], 500);
      }
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(string $id)
   {
      try {
         // Obtener usuario con sus roles usando select específico
         $user = User::select('id', 'name', 'email', 'company_id')
            ->with(['roles:id,name'])
            ->where('company_id', Auth::user()->company_id)
            ->findOrFail($id);
         
         $company = Auth::user()->company;

         // Obtener empresas disponibles
         $companies = Company::select('id', 'name')
            ->where('id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

         // Obtener roles disponibles de la empresa usando select específico
         $roles = Role::select('id', 'name', 'display_name')
            ->byCompany(Auth::user()->company_id)
            ->get();

         return view('admin.users.edit', compact('user', 'companies', 'roles', 'company'));
      } catch (\Exception $e) {
         return redirect()->route('admin.users.index')
            ->with('message', 'Error al cargar el usuario: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, string $id)
   {
      try {
         $user = User::findOrFail($id);

         // Verificar permisos
         if ($user->company_id !== Auth::user()->company_id) {
            throw new \Exception('No tiene permisos para editar este usuario');
         }

         $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'company_id' => ['required', 'exists:companies,id'],
            'role' => ['required', 'exists:roles,id'],
         ];

         // Si se está intentando cambiar la contraseña
         if ($request->filled('password')) {
            $rules['current_password'] = ['required', function ($attribute, $value, $fail) use ($user) {
               if (!Hash::check($value, $user->password)) {
                  $fail('La contraseña actual es incorrecta.');
               }
            }];
            $rules['password'] = [
               'required',
               'string',
               'min:8',
               'confirmed',
               'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ];
         }

         $validated = $request->validate($rules, [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El formato del correo electrónico no es válido',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'current_password.required' => 'Debe proporcionar la contraseña actual para cambiarla',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres',
            'password.regex' => 'La nueva contraseña debe contener al menos una mayúscula, una minúscula y un número',
            'company_id.required' => 'La empresa es obligatoria',
            'role.required' => 'El rol es obligatorio',
         ]);

         DB::beginTransaction();

         // Actualizar datos básicos
         $user->name = $validated['name'];
         $user->email = strtolower($validated['email']);
         $user->company_id = $validated['company_id'];

         // Actualizar contraseña si se proporcionó
         if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
         }

         $user->save();

         // Actualizar rol
         $role = Role::findOrFail($validated['role']);

         // Los roles del sistema no se crean en empresas específicas, 
         // por lo que esta validación ya no es necesaria

         // Sincronizar roles (elimina roles anteriores y asigna el nuevo)
         $user->syncRoles([$role]);

         DB::commit();

         return redirect()->route('admin.users.index')
            ->with('message', 'Usuario actualizado exitosamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();

         return redirect()->back()
            ->with('message', 'Error al actualizar el usuario: ' . $e->getMessage())
            ->with('icons', 'error')
            ->withInput($request->except(['password', 'password_confirmation']));
      }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy($id)
   {
      try {
         $user = User::findOrFail($id);

         // Verificar que el usuario pertenezca a la misma empresa
         if ($user->company_id !== Auth::user()->company_id) {
            return response()->json([
               'status' => 'error',
               'message' => 'No tiene permisos para eliminar este usuario'
            ], 403);
         }

         // Verificar si es un usuario admin
         if ($user->hasRole('admin')) {
            return response()->json([
               'status' => 'error',
               'message' => 'No se pueden eliminar usuarios administradores'
            ], 403);
         }

         // Verificar que no se elimine a sí mismo
         if ($user->id === Auth::id()) {
            return response()->json([
               'status' => 'error',
               'message' => 'No puede eliminarse a sí mismo'
            ], 403);
         }

         $user->delete();

         return response()->json([
            'status' => 'success',
            'message' => 'Usuario eliminado exitosamente'
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'Error al eliminar el usuario'
         ], 500);
      }
   }

   public function report()
   {
      $company = Company::select('id', 'name')->find(Auth::user()->company_id);
      $users = User::select('id', 'name', 'email', 'company_id', 'email_verified_at', 'last_login')
         ->with(['roles:id,name'])
         ->where('company_id', $company->id)
         ->orderBy('name', 'asc')
         ->get();
      $pdf = PDF::loadView('admin.users.report', compact('users', 'company'));
      return $pdf->stream('reporte-usuarios.pdf');
   }
}
