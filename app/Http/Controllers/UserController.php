<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withTrashed()->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|exists:roles,name', // Validar que el rol existe
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        // Asignar el rol al usuario
        $user->assignRole($request->roles);

        return redirect()->route('user.index')
            ->with('mensaje', 'El usuario se ha creado exitosamente.')
            ->with('icono', 'success');
    }

    public function edit($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        // 🔒 PROTECCIÓN REFORZADA PARA SUPER ADMIN
        if ($user->is_protected) {
            // Si es super admin y NO es el mismo usuario, no permitir cambios
            if ($user->id !== Auth::id()) {
                return redirect()->route('user.index')
                    ->with('mensaje', '❌ No puedes modificar al Super Administrador.')
                    ->with('icono', 'error');
            }

             $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'current_password' => 'required|string',
                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/[A-Z]/',
                    'regex:/[a-z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*?&#]/',
                ],
            ], [
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un carácter especial (@$!%*?&#).',
            ]);

            // Verificar contraseña actual
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.'])
                            ->withInput();
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();

            return redirect()->route('user.index')
                ->with('mensaje', '✅ Tu perfil se ha actualizado correctamente.')
                ->with('icono', 'success');
        }

        // PARA USUARIOS NORMALES
        // Si es el mismo usuario editando su propio perfil
        if ($user->id === Auth::id()) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'current_password' => 'required|string',
                'password' => 'nullable|string|min:8|confirmed',
            ]);

            // Verificar contraseña actual
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.'])
                            ->withInput();
            }

            $user->name = $request->name;
            $user->email = $request->email;

            // Actualizar contraseña solo si se proporcionó una nueva
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return redirect()->route('user.index')
                ->with('mensaje', '✅ Tu perfil se ha actualizado correctamente.')
                ->with('icono', 'success');
        }

        // 👑 ADMINISTRADOR editando a OTRO USUARIO (NO requiere contraseña actual)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // Sincronizar roles si se enviaron
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return redirect()->route('user.index')
            ->with('mensaje', '✅ Usuario actualizado exitosamente.')
            ->with('icono', 'success');
    }

    public function asignar(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array',
        ]);

        $user = User::withTrashed()->findOrFail($id);
        $user->roles()->sync($request->roles);

        return redirect()->route('user.index')
            ->with('mensaje', 'Se asignó el Rol exitosamente.')
            ->with('icono', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $usuario = User::findOrFail($id);

            // 🔒 PROTECCIÓN: Verificar admin protegido
            if ($usuario->is_protected) {
                return redirect()->route('user.index')
                    ->with('mensaje', '❌ No se puede eliminar al administrador del sistema.')
                    ->with('icono', 'error');
            }

            // 🔒 PROTECCIÓN: Evitar auto-eliminación
            if ($usuario->id === Auth::id()) {
                return redirect()->route('user.index')
                    ->with('mensaje', '❌ No puedes eliminar tu propio usuario.')
                    ->with('icono', 'error');
            }

            $usuario->delete();

            return redirect()->route('user.index')
                ->with('mensaje', 'El usuario se ha eliminado exitosamente.')
                ->with('icono', 'success');

        } catch (\Exception $e) {
            return redirect()->route('user.index')
                ->with('mensaje', 'Error: ' . $e->getMessage())
                ->with('icono', 'error');
        }
    }

    /**
     * Restaurar usuario eliminado
     */
    public function restaurar($id)
    {
        $usuario = User::withTrashed()->findOrFail($id);
        $usuario->restore();

        return redirect()->route('user.index')
            ->with('mensaje', 'El usuario se ha restaurado exitosamente.')
            ->with('icono', 'success');
    }

    /**
     * Eliminar permanentemente un usuario
     */
    public function forceDelete($id)
    {
        try {
            $usuario = User::withTrashed()->findOrFail($id);

            // 🔒 PROTECCIÓN: Verificar admin protegido
            if ($usuario->is_protected) {
                return redirect()->route('user.index')
                    ->with('mensaje', '❌ No se puede eliminar permanentemente al administrador del sistema.')
                    ->with('icono', 'error');
            }

            $usuario->forceDelete();

            return redirect()->route('user.index')
                ->with('mensaje', 'El usuario se ha eliminado permanentemente.')
                ->with('icono', 'success');

        } catch (\Exception $e) {
            return redirect()->route('user.index')
                ->with('mensaje', 'Error: ' . $e->getMessage())
                ->with('icono', 'error');
        }
    }
}
