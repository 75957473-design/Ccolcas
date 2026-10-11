<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = Auth::user();

        if (! $usuario) {
            return redirect()->route('home')->with('error', 'Debes iniciar sesión.');
        }

        if (! $usuario->rol) {
            abort(403, 'Sin rol asignado.');
        }

        $nombreRol = $usuario->rol->nombre;

        if (empty($roles) || in_array($nombreRol, $roles, true)) {
            return $next($request);
        }

        abort(403, 'No tienes permiso para acceder a esta sección.');
    }
}
