<?php

namespace App\Http\Middleware;

use App\Services\SessionSecurityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionSecurity
{
    protected SessionSecurityService $sessionSecurityService;

    public function __construct(SessionSecurityService $sessionSecurityService)
    {
        $this->sessionSecurityService = $sessionSecurityService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Não executar durante login/logout, registro ou rotas do Fortify
        $excludedRoutes = ['login', 'logout', 'register', 'password.reset', 'password.email', 'verification.notice', 'verification.verify'];
        $routeName = $request->route()?->getName();
        
        if (in_array($routeName, $excludedRoutes) || str_starts_with($routeName ?? '', 'login') || str_starts_with($routeName ?? '', 'logout')) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();

            // Atualizar última atividade apenas a cada minuto para evitar muitos updates
            if (!$user->last_activity_at || $user->last_activity_at->lt(now()->subMinute())) {
                try {
                    \Illuminate\Support\Facades\DB::table('users')
                        ->where('id', $user->id)
                        ->update(['last_activity_at' => now()]);
                    $user->last_activity_at = now();
                } catch (\Exception $e) {
                    // Ignorar erros de atualização para não quebrar a requisição
                }
            }

            // Verificar se precisa trocar senha
            try {
                $passwordPolicy = app(\App\Services\PasswordPolicyService::class);
                if ($passwordPolicy->requiresPasswordChange($user) && !$request->routeIs('settings.password.*')) {
                    return redirect()->route('settings.password.edit')
                        ->with('warning', 'Você precisa alterar sua senha antes de continuar.');
                }
            } catch (\Exception $e) {
                // Ignorar erros para não quebrar a requisição
            }

            // Verificação de sessão única foi movida para o middleware SingleSession
            // para evitar conflitos e garantir dados atualizados do banco

            // Verificar inatividade
            try {
                if ($this->sessionSecurityService->isUserInactive($user)) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')
                        ->withErrors(['email' => 'Sua sessão expirou devido à inatividade.']);
                }
            } catch (\Exception $e) {
                // Ignorar erros para não quebrar a requisição
            }
        }

        return $next($request);
    }
}
