<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireApproval
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Não executar durante login/logout ou rotas do Fortify
        $excludedRoutes = ['login', 'logout', 'register', 'password.reset', 'password.email', 'verification.notice', 'verification.verify'];
        $routeName = $request->route()?->getName();
        
        if (in_array($routeName, $excludedRoutes) || str_starts_with($routeName ?? '', 'login') || str_starts_with($routeName ?? '', 'logout')) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();

            // Verificar se precisa de aprovação
            if ($user->isPendingApproval()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Sua conta está aguardando aprovação de um administrador.']);
            }

            // Verificar se foi rejeitado
            if ($user->approval_status === 'rejected') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Sua conta foi rejeitada. Entre em contato com o administrador.']);
            }
        }

        return $next($request);
    }
}

