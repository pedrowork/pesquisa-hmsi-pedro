<?php

namespace App\Services;

use App\Models\PermissionAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionAuditService
{
    /**
     * Registra uma ação de auditoria relacionada a permissões.
     */
    public function log(
        string $action,
        string $targetType,
        ?int $targetId = null,
        ?int $permissionId = null,
        ?int $roleId = null,
        ?array $changes = null,
        ?string $description = null,
        ?Request $request = null
    ): PermissionAuditLog {
        $user = Auth::user();
        $request = $request ?? request();

        return PermissionAuditLog::create([
            'user_id' => $user?->id,
            'action' => $action, // 'granted', 'revoked', 'updated', 'created', 'deleted'
            'target_type' => $targetType, // 'user_permission', 'role_permission', 'role', 'permission'
            'target_id' => $targetId,
            'permission_id' => $permissionId,
            'role_id' => $roleId,
            'changes' => $changes,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Registra a concessão de permissão a um usuário.
     */
    public function logPermissionGrantedToUser(
        int $userId,
        int $permissionId,
        ?\DateTimeInterface $expiresAt = null,
        bool $deny = false,
        ?Request $request = null
    ): PermissionAuditLog {
        return $this->log(
            'granted',
            'user_permission',
            $userId,
            $permissionId,
            null,
            [
                'expires_at' => $expiresAt?->format('Y-m-d H:i:s'),
                'deny' => $deny,
            ],
            "Permissão concedida ao usuário ID: {$userId}",
            $request
        );
    }

    /**
     * Registra a revogação de permissão de um usuário.
     */
    public function logPermissionRevokedFromUser(
        int $userId,
        int $permissionId,
        ?Request $request = null
    ): PermissionAuditLog {
        return $this->log(
            'revoked',
            'user_permission',
            $userId,
            $permissionId,
            null,
            null,
            "Permissão revogada do usuário ID: {$userId}",
            $request
        );
    }

    /**
     * Registra a concessão de permissão a uma role.
     */
    public function logPermissionGrantedToRole(
        int $roleId,
        int $permissionId,
        ?\DateTimeInterface $expiresAt = null,
        bool $deny = false,
        ?Request $request = null
    ): PermissionAuditLog {
        return $this->log(
            'granted',
            'role_permission',
            $roleId,
            $permissionId,
            $roleId,
            [
                'expires_at' => $expiresAt?->format('Y-m-d H:i:s'),
                'deny' => $deny,
            ],
            "Permissão concedida à role ID: {$roleId}",
            $request
        );
    }

    /**
     * Registra a revogação de permissão de uma role.
     */
    public function logPermissionRevokedFromRole(
        int $roleId,
        int $permissionId,
        ?Request $request = null
    ): PermissionAuditLog {
        return $this->log(
            'revoked',
            'role_permission',
            $roleId,
            $permissionId,
            $roleId,
            null,
            "Permissão revogada da role ID: {$roleId}",
            $request
        );
    }

    /**
     * Registra atualização de permissão.
     */
    public function logPermissionUpdated(
        string $targetType,
        int $targetId,
        int $permissionId,
        array $oldValues,
        array $newValues,
        ?Request $request = null
    ): PermissionAuditLog {
        return $this->log(
            'updated',
            $targetType,
            $targetId,
            $permissionId,
            $targetType === 'role_permission' ? $targetId : null,
            [
                'old' => $oldValues,
                'new' => $newValues,
            ],
            "Permissão atualizada para {$targetType} ID: {$targetId}",
            $request
        );
    }

    /**
     * Retorna o histórico de auditoria.
     */
    public function getAuditLogs(
        ?int $userId = null,
        ?int $permissionId = null,
        ?int $roleId = null,
        ?string $action = null,
        int $limit = 100
    ) {
        $query = PermissionAuditLog::query()
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        if ($userId) {
            $query->where('target_id', $userId)
                ->where('target_type', 'user_permission');
        }

        if ($permissionId) {
            $query->where('permission_id', $permissionId);
        }

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        if ($action) {
            $query->where('action', $action);
        }

        return $query->limit($limit)->get();
    }
}
