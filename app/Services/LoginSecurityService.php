<?php

namespace App\Services;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginSecurityService
{
    /**
     * Registra uma tentativa de login.
     */
    public function recordLoginAttempt(
        ?User $user,
        string $email,
        bool $successful,
        Request $request
    ): void {
        LoginAttempt::create([
            'user_id' => $user?->id,
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => $successful,
            'attempted_at' => now(),
        ]);

        if (!$successful && $user) {
            $user->incrementFailedLoginAttempts();
        }
        
        // Se bem-sucedido, apenas registrar no histórico (recordSuccessfulLogin já foi chamado)
        // Não precisamos chamar recordSuccessfulLogin aqui para evitar duplicação
    }

    /**
     * Verifica se o email/usuário está bloqueado.
     */
    public function isBlocked(string $email): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        return $user->isAccountLocked();
    }

    /**
     * Retorna o histórico de login de um usuário.
     */
    public function getLoginHistory(int $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return LoginAttempt::where('user_id', $userId)
            ->orderBy('attempted_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Retorna estatísticas de tentativas de login por IP.
     */
    public function getLoginAttemptsByIp(string $ipAddress, int $minutes = 60): int
    {
        return LoginAttempt::where('ip_address', $ipAddress)
            ->where('attempted_at', '>=', now()->subMinutes($minutes))
            ->where('successful', false)
            ->count();
    }

    /**
     * Retorna tentativas falhadas recentes por email ou IP.
     */
    public function getRecentFailedAttempts(string $identifier, int $minutes = 15): int
    {
        return LoginAttempt::where('attempted_at', '>=', now()->subMinutes($minutes))
            ->where('successful', false)
            ->where(function ($query) use ($identifier) {
                $query->where('email', $identifier)
                    ->orWhere('ip_address', $identifier);
            })
            ->count();
    }
}
