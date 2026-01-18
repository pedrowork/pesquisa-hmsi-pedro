<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SingleSession
{
    /**
     * Handle an incoming request.
     * 
     * Verifica se a sessão atual do usuário é válida (sessão única).
     * Se o usuário fizer login em outro dispositivo/navegador, esta sessão será invalidada.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // PRIMEIRO: Verificar se é rota de autenticação - NÃO EXECUTAR NESTAS ROTAS
        $routeName = $request->route()?->getName();
        $path = trim($request->path(), '/'); // Remover barras do início/fim para comparação exata
        
        // Verificar se é rota de login (qualquer método para /login)
        $isLoginRoute = $path === 'login' || 
                        $request->is('login') ||
                        $request->is('login/*') ||
                        $request->fullUrlIs('*/login*') ||
                        $routeName === 'login' ||
                        $routeName === 'login.store' ||
                        str_starts_with($routeName ?? '', 'login') ||
                        str_contains($path, 'login');
        
        // Verificar se é rota de logout
        $isLogoutRoute = $path === 'logout' || 
                         $request->is('logout') ||
                         $request->is('logout/*') ||
                         $request->fullUrlIs('*/logout*') ||
                         $routeName === 'logout' ||
                         str_starts_with($routeName ?? '', 'logout') ||
                         str_contains($path, 'logout');
        
        // Outras rotas excluídas do Fortify
        $excludedRoutes = ['register', 'password.reset', 'password.email', 'verification.notice', 'verification.verify'];
        
        // Verificar se é rota Fortify (começam com /user/)
        $isFortifyRoute = str_starts_with($path, 'user/');
        
        // RETORNAR EARLY - não executar nenhuma verificação nestas rotas
        if ($isLoginRoute || $isLogoutRoute || $isFortifyRoute || in_array($routeName, $excludedRoutes)) {
            return $next($request);
        }

        // SEGUNDO: Só verificar sessão única se o usuário estiver autenticado
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $currentSessionId = Session::getId();
        
        // Recarregar o usuário do banco para garantir dados atualizados
        $user->refresh();
        
        // Verificar se sessão única está habilitada
        if ($user->single_session_enabled) {
            // Se o ID da sessão atual não corresponde ao armazenado no banco,
            // significa que o usuário fez login em outro lugar
            if ($user->current_session_id && $user->current_session_id !== $currentSessionId) {
                // Invalidar sessão atual e fazer logout
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Redirecionar para login com mensagem
                return redirect()->route('login')
                    ->with('error', 'Você foi desconectado porque fez login em outro dispositivo ou navegador.');
            }
            
            // Se não há session_id salvo, inicializar (primeira vez após login)
            if (!$user->current_session_id) {
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $user->id)
                    ->update(['current_session_id' => $currentSessionId]);
                $user->current_session_id = $currentSessionId;
            }
            
            // Atualizar last_activity para manter a sessão ativa (apenas se mudou)
            if (!$user->last_activity || $user->last_activity->lt(now()->subMinute())) {
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $user->id)
                    ->update(['last_activity' => now()]);
                $user->last_activity = now();
            }
        }

        return $next($request);
    }
}
