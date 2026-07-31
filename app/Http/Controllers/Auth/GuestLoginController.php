<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestLoginController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $guest = User::query()->where('email', 'invitado@conserdei.demo')->first();

        if (!$guest) {
            return redirect()->route('login')->withErrors([
                'email' => 'El usuario invitado aún no fue creado. Ejecute los seeders del sistema.',
            ]);
        }

        Auth::login($guest);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}
