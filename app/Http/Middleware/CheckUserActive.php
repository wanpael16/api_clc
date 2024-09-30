<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class CheckUserActive
{
 

    public function handle(Request $request, Closure $next): Response
    {
      
        $user = Auth::user();

        if (!$user->is_active) {
            return response()->json(['message' => 'Acceso denegado. Usuario inactivo.'], 403);
        }

        if (!Cookie::has('cookie_token')) {
            return response()->json(['message' => 'No estás autenticado.'], 401);
        }

        return $next($request);
    }
}
