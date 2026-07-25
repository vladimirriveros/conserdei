<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        // Validación con mensajes personalizados
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed', // Busca new_password_confirmation
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
            ],
        ], [
            'new_password.required' => 'La nueva contraseña es obligatoria.',
            'new_password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'new_password.confirmed' => 'Las contraseñas no coinciden. Verifica que ambas contraseñas sean idénticas.',
            'new_password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un carácter especial (@$!%*?&#).',
        ]);

        $user = Auth::user();

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }

        // Verificar que la nueva contraseña no sea igual a la actual
        if (Hash::check($request->new_password, $user->password)) {
            return back()->withErrors(['new_password' => 'La nueva contraseña no puede ser igual a la actual.']);
        }

        // Actualizar contraseña
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Opcional: Cerrar sesión en otros dispositivos
        // Auth::logoutOtherDevices($request->new_password);

        return redirect()->route('home')
            ->with('mensaje', '✅ Contraseña actualizada exitosamente.')
            ->with('icono', 'success');
    }
}
