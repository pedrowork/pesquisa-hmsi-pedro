<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AuditService;

class UserObserver
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Apenas registrar se não estiver em modo seeding ou se houver usuário autenticado
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            // Durante seeders, pode não haver usuário autenticado
            return;
        }
        
        try {
            $this->auditService->logUserCreated($user, $user->getAttributes());
        } catch (\Exception $e) {
            // Não quebrar o fluxo se houver erro no log
            \Log::warning('Failed to log user creation', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }
        
        // Não registrar updates durante login para evitar loops
        // Campos relacionados a login são atualizados frequentemente
        $loginFields = ['last_login_at', 'last_login_ip', 'failed_login_attempts', 'account_locked_until', 
                        'current_session_id', 'last_activity', 'last_activity_at'];
        $changes = $user->getChanges();
        
        // Se apenas campos de login foram alterados, não registrar no audit
        $onlyLoginFields = !empty($changes) && empty(array_diff(array_keys($changes), $loginFields));
        if ($onlyLoginFields) {
            return;
        }
        
        try {
            $this->auditService->logUserUpdated(
                $user,
                $user->getOriginal(),
                $changes
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to log user update', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }
        
        try {
            $this->auditService->logUserDeleted($user, $user->getAttributes());
        } catch (\Exception $e) {
            \Log::warning('Failed to log user deletion', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }
        
        try {
            $this->auditService->log(
                'user.restored',
                'user_management',
                "Usuário restaurado: {$user->email}",
                $user
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to log user restoration', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }
        
        try {
            $this->auditService->log(
                'user.force_deleted',
                'user_management',
                "Usuário permanentemente excluído: {$user->email}",
                $user,
                $user->getAttributes(),
                null,
                null,
                'critical',
                true
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to log user force deletion', ['error' => $e->getMessage()]);
        }
    }
}
