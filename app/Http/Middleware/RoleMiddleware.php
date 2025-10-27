<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Non autorisé'], 401);
        }

        // Vérifier si c'est un User (admin) ou Client
        if ($user instanceof \App\Models\User) {
            if ($role !== 'admin') {
                return response()->json(['error' => 'Accès refusé'], 403);
            }
        } elseif ($user instanceof \App\Models\Client) {
            if ($role !== 'client') {
                return response()->json(['error' => 'Accès refusé'], 403);
            }
        } else {
            return response()->json(['error' => 'Type d\'utilisateur non reconnu'], 403);
        }

        return $next($request);
    }
}
