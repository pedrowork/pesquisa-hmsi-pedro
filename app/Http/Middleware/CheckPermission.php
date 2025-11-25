<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        // Se não estiver autenticado, redirecionar para login
        if (!$user) {
            return redirect()->route('login');
        }

        // Admin tem acesso total
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar se o usuário tem a permissão necessária
        if (!$user->hasPermission($permission)) {
            if ($request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'Você não tem permissão para acessar esta página.',
                    'permission' => $permission,
                ], 403);
            }
            abort(403, 'Você não tem permissão para acessar esta página.');
        }

        return $next($request);
    }
}


