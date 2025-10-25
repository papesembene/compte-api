<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour enregistrer les utilisateurs qui atteignent le rate limit.
 *
 * Responsabilité : Logger les tentatives de dépassement du rate limit pour monitoring.
 */
class RateLimitLoggerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Vérifier si la réponse indique un rate limit (status 429)
        if ($response->getStatusCode() === 429) {
            Log::warning('Rate limit atteint', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'timestamp' => now(),
            ]);
        }

        return $response;
    }
}