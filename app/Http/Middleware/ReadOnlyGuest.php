<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReadOnlyGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('invitado')) {
            return $next($request);
        }

        if ($request->routeIs('logout') || in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        return redirect()->back()->with('warning', 'El usuario invitado es de solo lectura. No puede crear, editar ni eliminar información.');
    }
}
