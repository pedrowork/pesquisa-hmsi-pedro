<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\LoginSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class AuthenticateUser
{
    protected LoginSecurityService $loginSecurityService;

    public function __construct(LoginSecurityService $loginSecurityService)
    {
        $this->loginSecurityService = $loginSecurityService;
    }

    /**
     * Authenticate the user.
     */
    public function __invoke(Request $request): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        $email = $request->input(Fortify::username());
        $password = $request->input('password');

        // Verificar se a conta está bloqueada
        if ($this->loginSecurityService->isBlocked($email)) {
            $user = User::where('email', $email)->first();
            throw ValidationException::withMessages([
                Fortify::username() => [
                    'Sua conta foi bloqueada devido a múltiplas tentativas de login falhadas. ' .
                    'Tente novamente após ' . $user->account_locked_until->diffForHumans() . '.',
                ],
            ]);
        }

        $user = User::where('email', $email)->first();

        // Verificar credenciais
        if (!$user || !Hash::check($password, $user->password)) {
            // Registrar tentativa falhada
            $this->loginSecurityService->recordLoginAttempt($user, $email, false, $request);
            
            // Registrar no audit log
            app(\App\Services\AuditService::class)->logFailedLogin($email, null, $request);
            
            // Monitorar acesso não autorizado
            app(\App\Services\SecurityMonitoringService::class)->monitorUnauthorizedAccess(
                $email,
                'Credenciais inválidas',
                $request
            );
            
            throw ValidationException::withMessages([
                Fortify::username() => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        // Verificar se a conta está bloqueada
        if ($user->isAccountLocked()) {
            throw ValidationException::withMessages([
                Fortify::username() => [
                    'Sua conta foi bloqueada devido a múltiplas tentativas de login falhadas. ' .
                    'Tente novamente após ' . $user->account_locked_until->diffForHumans() . '.',
                ],
            ]);
        }

        // Verificar se o email foi verificado (se necessário)
        // A verificação de email obrigatória é aplicada pelo middleware 'verified' nas rotas
        // Aqui apenas verificamos se a verificação está habilitada
        // O middleware vai redirecionar para verificação se necessário

        // Verificar status da conta
        if ($user->status !== 1) {
            throw ValidationException::withMessages([
                Fortify::username() => ['Sua conta está desativada. Entre em contato com o administrador.'],
            ]);
        }

        // Verificar aprovação (se necessário)
        if (config('security.user_approval_required', true)) {
            if ($user->approval_status === 'pending') {
                throw ValidationException::withMessages([
                    Fortify::username() => ['Sua conta está aguardando aprovação de um administrador.'],
                ]);
            }
            
            if ($user->approval_status === 'rejected') {
                throw ValidationException::withMessages([
                    Fortify::username() => ['Sua conta foi rejeitada. Entre em contato com o administrador.'],
                ]);
            }
        }

        // Login bem-sucedido - registrar como sucesso (usar update direto para evitar loops)
        try {
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                    'failed_login_attempts' => 0,
                    'account_locked_until' => null,
                ]);
            
            // Atualizar modelo em memória
            $user->last_login_at = now();
            $user->last_login_ip = $request->ip();
            $user->failed_login_attempts = 0;
            $user->account_locked_until = null;
        } catch (\Exception $e) {
            // Ignorar erros para não quebrar o login
        }

        // Registrar tentativa de login (sem fazer update no usuário novamente)
        try {
            $this->loginSecurityService->recordLoginAttempt($user, $email, true, $request);
        } catch (\Exception $e) {
            // Ignorar erros para não quebrar o login
        }

        // Registrar sessão de forma simplificada (sem múltiplos updates)
        try {
            $sessionService = app(\App\Services\SessionSecurityService::class);
            $sessionId = \Illuminate\Support\Facades\Session::getId();
            
            // Update direto no banco para evitar loops
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'current_session_id' => $sessionId,
                    'last_activity' => now(),
                ]);
            
            // Atualizar modelo em memória
            $user->current_session_id = $sessionId;
            $user->last_activity = now();
            
            // Regenerar ID da sessão para prevenir session fixation
            \Illuminate\Support\Facades\Session::regenerate(true);
            
            // Atualizar com novo ID de sessão
            $newSessionId = \Illuminate\Support\Facades\Session::getId();
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $user->id)
                ->update(['current_session_id' => $newSessionId]);
            
            $user->current_session_id = $newSessionId;
        } catch (\Exception $e) {
            // Ignorar erros para não quebrar o login
        }

        // Retornar o usuário para o Fortify fazer a autenticação
        return $user;
    }
}
