<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SessionSecurityService
{
    /**
     * Regenera o ID da sessão para prevenir session fixation.
     */
    public function regenerateSessionId(): void
    {
        Session::regenerate(true);
    }

    /**
     * Atualiza a última atividade do usuário.
     */
    public function updateLastActivity(User $user): void
    {
        // Usar update direto no banco para evitar loops e eventos do modelo
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'last_activity' => now(),
                'current_session_id' => Session::getId(),
            ]);
        
        // Atualizar o modelo em memória sem salvar
        $user->last_activity = now();
        $user->current_session_id = Session::getId();
    }

    /**
     * Invalida todas as outras sessões do usuário (exceto a atual).
     */
    public function invalidateOtherSessions(User $user, ?string $currentSessionId = null): int
    {
        $currentSessionId = $currentSessionId ?? Session::getId();

        // Deletar todas as sessões do usuário exceto a atual
        $deleted = DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return $deleted;
    }

    /**
     * Invalida todas as sessões do usuário (incluindo a atual).
     */
    public function invalidateAllSessions(User $user): int
    {
        $deleted = DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        // Limpar o session_id atual do usuário
        $user->update([
            'current_session_id' => null,
        ]);

        return $deleted;
    }

    /**
     * Verifica se a sessão atual é válida (para sessão única).
     */
    public function isSessionValid(User $user, ?string $sessionId = null): bool
    {
        if (!$user->single_session_enabled) {
            return true; // Se não está habilitado, sempre válido
        }

        $sessionId = $sessionId ?? Session::getId();

        // Se não há sessão registrada, é válida
        if (!$user->current_session_id) {
            return true;
        }

        // Verificar se a sessão atual corresponde à registrada
        return $user->current_session_id === $sessionId;
    }

    /**
     * Verifica se o usuário está inativo há mais tempo que o permitido.
     */
    public function isUserInactive(User $user, ?int $timeoutMinutes = null): bool
    {
        if (!$user->last_activity) {
            return false; // Se nunca teve atividade, considerar ativo
        }

        $timeoutMinutes = $timeoutMinutes ?? config('session.inactivity_timeout', 30);

        return $user->last_activity->addMinutes($timeoutMinutes)->isPast();
    }

    /**
     * Obtém todas as sessões ativas do usuário.
     */
    public function getActiveSessions(User $user): array
    {
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('last_activity', '>', now()->subMinutes(config('session.lifetime', 120)))
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_activity' => date('Y-m-d H:i:s', $session->last_activity),
                    'is_current' => $session->id === Session::getId(),
                ];
            })
            ->toArray();

        return $sessions;
    }

    /**
     * Obtém contagem de sessões ativas do usuário.
     */
    public function getActiveSessionCount(User $user): int
    {
        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('last_activity', '>', now()->subMinutes(config('session.lifetime', 120)))
            ->count();
    }

    /**
     * Habilita sessão única para o usuário e invalida outras sessões.
     */
    public function enableSingleSession(User $user): int
    {
        $user->update([
            'single_session_enabled' => true,
        ]);

        return $this->invalidateOtherSessions($user);
    }

    /**
     * Desabilita sessão única para o usuário.
     */
    public function disableSingleSession(User $user): void
    {
        $user->update([
            'single_session_enabled' => false,
            'current_session_id' => null,
        ]);
    }

    /**
     * Registra a sessão do usuário após login.
     */
    public function registerSession(User $user): void
    {
        $sessionId = Session::getId();

        // Se sessão única está habilitada, invalidar outras
        if ($user->single_session_enabled) {
            $this->invalidateOtherSessions($user, $sessionId);
        }

        // Atualizar informações de sessão
        $user->update([
            'current_session_id' => $sessionId,
            'last_activity' => now(),
        ]);

        // Regenerar ID da sessão para prevenir session fixation
        $this->regenerateSessionId();

        // Atualizar novamente com o novo ID
        $user->update([
            'current_session_id' => Session::getId(),
        ]);
    }
}

