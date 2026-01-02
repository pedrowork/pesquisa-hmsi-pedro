<?php

namespace App\Services;

use App\Helpers\DataMaskingHelper;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Registra um evento de auditoria.
     */
    public function log(
        string $eventType,
        string $category,
        string $description = null,
        ?Model $model = null,
        array $oldValues = null,
        array $newValues = null,
        array $metadata = null,
        string $severity = 'info',
        bool $isSecurityAlert = false,
        ?Request $request = null
    ): ?AuditLog {
        try {
            $user = Auth::user();
            $request = $request ?? (request() ?: null);

            // Mascarar dados sensíveis
            $maskedOldValues = $oldValues ? DataMaskingHelper::maskSensitiveData($oldValues) : null;
            $maskedNewValues = $newValues ? DataMaskingHelper::maskSensitiveData($newValues) : null;
            $maskedMetadata = $metadata ? DataMaskingHelper::maskSensitiveData($metadata) : null;

            return AuditLog::create([
                'user_id' => $user?->id,
                'event_type' => $eventType,
                'category' => $category,
                'severity' => $severity,
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model?->id,
                'old_values' => $maskedOldValues,
                'new_values' => $maskedNewValues,
                'metadata' => $maskedMetadata,
                'description' => $description,
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? $request->userAgent() : null,
                'is_security_alert' => $isSecurityAlert,
            ]);
        } catch (\Exception $e) {
            // Em caso de erro, não quebrar o fluxo da aplicação
            \Log::warning('Failed to create audit log', [
                'error' => $e->getMessage(),
                'event_type' => $eventType,
            ]);
            return null;
        }
    }

    /**
     * Registra criação de usuário.
     */
    public function logUserCreated(
        Model $user,
        array $userData,
        ?string $description = null,
        ?Request $request = null
    ): ?AuditLog {
        return $this->log(
            'user.created',
            'user_management',
            $description ?? "Usuário criado: {$user->email}",
            $user,
            null,
            $userData,
            null,
            'info',
            false,
            $request
        );
    }

    /**
     * Registra atualização de usuário.
     */
    public function logUserUpdated(
        Model $user,
        array $oldValues,
        array $newValues,
        ?string $description = null,
        ?Request $request = null
    ): ?AuditLog {
        $isSecurityAlert = $this->isSecuritySensitiveUpdate($oldValues, $newValues);

        return $this->log(
            'user.updated',
            'user_management',
            $description ?? "Usuário atualizado: {$user->email}",
            $user,
            $oldValues,
            $newValues,
            null,
            $isSecurityAlert ? 'warning' : 'info',
            $isSecurityAlert,
            $request
        );
    }

    /**
     * Registra exclusão de usuário.
     */
    public function logUserDeleted(
        Model $user,
        array $deletedData,
        ?string $description = null,
        ?Request $request = null
    ): ?AuditLog {
        return $this->log(
            'user.deleted',
            'user_management',
            $description ?? "Usuário excluído: {$user->email}",
            $user,
            $deletedData,
            null,
            null,
            'warning',
            true, // Exclusão sempre é alerta de segurança
            $request
        );
    }

    /**
     * Registra mudança de senha.
     */
    public function logPasswordChanged(
        Model $user,
        ?string $description = null,
        ?Request $request = null
    ): ?AuditLog {
        return $this->log(
            'password.changed',
            'security',
            $description ?? "Senha alterada para usuário: {$user->email}",
            $user,
            null,
            null,
            null,
            'warning',
            true, // Mudança de senha é alerta de segurança
            $request
        );
    }

    /**
     * Registra tentativa de login falhada.
     */
    public function logFailedLogin(
        string $email,
        ?string $description = null,
        ?Request $request = null
    ): ?AuditLog {
        return $this->log(
            'login.failed',
            'security',
            $description ?? "Tentativa de login falhada para: {$email}",
            null,
            null,
            null,
            ['email' => $email],
            'warning',
            true, // Login falhado é alerta
            $request
        );
    }

    /**
     * Registra ação administrativa.
     */
    public function logAdminAction(
        string $action,
        ?Model $model = null,
        array $details = null,
        ?string $description = null,
        ?Request $request = null
    ): ?AuditLog {
        return $this->log(
            "admin.{$action}",
            'system',
            $description ?? "Ação administrativa: {$action}",
            $model,
            null,
            null,
            $details,
            'info',
            false,
            $request
        );
    }

    /**
     * Verifica se uma atualização é sensível do ponto de vista de segurança.
     */
    protected function isSecuritySensitiveUpdate(array $oldValues, array $newValues): bool
    {
        $sensitiveFields = ['email', 'status', 'password', 'two_factor_secret', 'failed_login_attempts', 'account_locked_until'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($oldValues[$field]) && isset($newValues[$field])) {
                if ($oldValues[$field] !== $newValues[$field]) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Retorna logs de auditoria com filtros.
     */
    public function getLogs(
        ?int $userId = null,
        ?string $category = null,
        ?string $eventType = null,
        ?string $severity = null,
        ?string $modelType = null,
        ?int $modelId = null,
        bool $securityAlertsOnly = false,
        int $limit = 100
    ) {
        $query = AuditLog::query()
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        if ($severity) {
            $query->where('severity', $severity);
        }

        if ($modelType) {
            $query->where('model_type', $modelType);
        }

        if ($modelId) {
            $query->where('model_id', $modelId);
        }

        if ($securityAlertsOnly) {
            $query->securityAlerts();
        }

        return $query->limit($limit)->get();
    }

    /**
     * Retorna ações recentes de um usuário.
     */
    public function getRecentUserActions(int $userId, int $minutes = 5): int
    {
        return AuditLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }
}

