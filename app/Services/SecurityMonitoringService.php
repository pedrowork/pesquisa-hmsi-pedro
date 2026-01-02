<?php

namespace App\Services;

use App\Models\SecurityAlert;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SecurityMonitoringService
{
    /**
     * Cria um alerta de segurança.
     */
    public function createAlert(
        string $alertType,
        string $severity,
        string $title,
        string $description,
        ?User $user = null,
        ?array $metadata = null,
        ?Request $request = null
    ): SecurityAlert {
        $request = $request ?? request();

        return SecurityAlert::create([
            'user_id' => $user?->id,
            'alert_type' => $alertType,
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * Monitora tentativas de acesso não autorizadas.
     */
    public function monitorUnauthorizedAccess(
        string $email,
        string $reason,
        ?Request $request = null
    ): void {
        $request = $request ?? request();

        // Verificar se há múltiplas tentativas do mesmo IP
        $recentAttempts = app(LoginSecurityService::class)->getRecentFailedAttempts($request->ip(), 15);
        
        $severity = 'medium';
        if ($recentAttempts >= 10) {
            $severity = 'high';
        }
        if ($recentAttempts >= 20) {
            $severity = 'critical';
        }

        $this->createAlert(
            'unauthorized_access',
            $severity,
            'Tentativa de acesso não autorizada',
            "Tentativa de acesso não autorizada para o email: {$email}. Motivo: {$reason}",
            null,
            [
                'email' => $email,
                'reason' => $reason,
                'failed_attempts' => $recentAttempts,
            ],
            $request
        );

        // Log adicional para sistemas de SIEM
        Log::warning('Unauthorized access attempt', [
            'email' => $email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reason' => $reason,
            'failed_attempts' => $recentAttempts,
        ]);
    }

    /**
     * Monitora mudanças em permissões críticas.
     */
    public function monitorPermissionChange(
        User $user,
        array $oldPermissions,
        array $newPermissions,
        ?User $changedBy = null,
        ?Request $request = null
    ): void {
        $changedBy = $changedBy ?? Auth::user();
        $request = $request ?? request();

        // Identificar permissões críticas que foram alteradas
        $criticalPermissions = config('security.critical_permissions', [
            'admin.*',
            'users.*',
            'roles.*',
            'permissions.*',
        ]);

        $addedCritical = [];
        $removedCritical = [];

        foreach ($criticalPermissions as $permission) {
            $hadPermission = $this->hasPermission($oldPermissions, $permission);
            $hasPermission = $this->hasPermission($newPermissions, $permission);

            if (!$hadPermission && $hasPermission) {
                $addedCritical[] = $permission;
            } elseif ($hadPermission && !$hasPermission) {
                $removedCritical[] = $permission;
            }
        }

        if (!empty($addedCritical) || !empty($removedCritical)) {
            $severity = 'high';
            if (!empty($addedCritical)) {
                $severity = 'critical'; // Adicionar permissões críticas é mais grave
            }

            $this->createAlert(
                'permission_change',
                $severity,
                'Mudança em permissões críticas',
                "Permissões críticas foram alteradas para o usuário {$user->email}",
                $user,
                [
                    'changed_by' => $changedBy?->id,
                    'added_permissions' => $addedCritical,
                    'removed_permissions' => $removedCritical,
                    'old_permissions' => $oldPermissions,
                    'new_permissions' => $newPermissions,
                ],
                $request
            );

            Log::warning('Critical permission change', [
                'user_id' => $user->id,
                'changed_by' => $changedBy?->id,
                'added' => $addedCritical,
                'removed' => $removedCritical,
            ]);
        }
    }

    /**
     * Monitora atividade suspeita.
     */
    public function monitorSuspiciousActivity(
        User $user,
        string $activityType,
        string $description,
        ?array $metadata = null,
        ?Request $request = null
    ): void {
        $request = $request ?? request();

        // Verificar padrões suspeitos
        $suspiciousPatterns = [
            'multiple_failed_logins' => $this->checkMultipleFailedLogins($user),
            'unusual_location' => $this->checkUnusualLocation($user, $request),
            'unusual_time' => $this->checkUnusualTime($user),
            'rapid_actions' => $this->checkRapidActions($user),
        ];

        $suspiciousCount = count(array_filter($suspiciousPatterns));

        if ($suspiciousCount > 0) {
            $severity = $suspiciousCount >= 3 ? 'critical' : ($suspiciousCount >= 2 ? 'high' : 'medium');

            $this->createAlert(
                'suspicious_activity',
                $severity,
                'Atividade suspeita detectada',
                $description,
                $user,
                array_merge($metadata ?? [], [
                    'activity_type' => $activityType,
                    'suspicious_patterns' => $suspiciousPatterns,
                ]),
                $request
            );

            Log::warning('Suspicious activity detected', [
                'user_id' => $user->id,
                'activity_type' => $activityType,
                'patterns' => $suspiciousPatterns,
            ]);
        }
    }

    /**
     * Obtém métricas de segurança para o dashboard.
     */
    public function getSecurityMetrics(?int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $alerts = SecurityAlert::where('created_at', '>=', $startDate)
            ->selectRaw('
                alert_type,
                severity,
                COUNT(*) as count,
                SUM(CASE WHEN is_resolved = 0 THEN 1 ELSE 0 END) as unresolved_count
            ')
            ->groupBy('alert_type', 'severity')
            ->get();

        $metrics = [
            'total_alerts' => SecurityAlert::where('created_at', '>=', $startDate)->count(),
            'unresolved_alerts' => SecurityAlert::where('created_at', '>=', $startDate)
                ->where('is_resolved', false)
                ->count(),
            'critical_alerts' => SecurityAlert::where('created_at', '>=', $startDate)
                ->where('severity', 'critical')
                ->where('is_resolved', false)
                ->count(),
            'high_alerts' => SecurityAlert::where('created_at', '>=', $startDate)
                ->where('severity', 'high')
                ->where('is_resolved', false)
                ->count(),
            'by_type' => $alerts->groupBy('alert_type')->map(function ($group) {
                return [
                    'total' => $group->sum('count'),
                    'unresolved' => $group->sum('unresolved_count'),
                    'by_severity' => $group->keyBy('severity')->map->count->toArray(),
                ];
            }),
            'by_severity' => $alerts->groupBy('severity')->map(function ($group) {
                return [
                    'total' => $group->sum('count'),
                    'unresolved' => $group->sum('unresolved_count'),
                ];
            }),
            'recent_alerts' => SecurityAlert::where('created_at', '>=', $startDate)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return $metrics;
    }

    /**
     * Verifica se o usuário tem uma permissão específica.
     */
    protected function hasPermission(array $permissions, string $permission): bool
    {
        foreach ($permissions as $userPermission) {
            if ($userPermission === $permission || fnmatch($permission, $userPermission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica múltiplas tentativas de login falhadas.
     */
    protected function checkMultipleFailedLogins(User $user): bool
    {
        $recentFailures = app(LoginSecurityService::class)
            ->getRecentFailedAttempts($user->email, 15);

        return $recentFailures >= 5;
    }

    /**
     * Verifica localização incomum.
     */
    protected function checkUnusualLocation(User $user, ?Request $request): bool
    {
        if (!$request || !$user->last_login_ip) {
            return false;
        }

        // Se o IP mudou drasticamente (simplificado - em produção usar geolocalização)
        return $user->last_login_ip !== $request->ip();
    }

    /**
     * Verifica horário incomum de acesso.
     */
    protected function checkUnusualTime(User $user): bool
    {
        if (!$user->last_login_at) {
            return false;
        }

        $hour = $user->last_login_at->hour;
        // Considerar incomum se for entre 2h e 5h da manhã
        return $hour >= 2 && $hour <= 5;
    }

    /**
     * Verifica ações rápidas demais (possível bot).
     */
    protected function checkRapidActions(User $user): bool
    {
        // Verificar ações recentes do usuário nos últimos minutos
        $recentActions = app(AuditService::class)->getRecentUserActions($user->id, 5);
        
        return $recentActions >= 20; // Mais de 20 ações em 5 minutos é suspeito
    }

    /**
     * Integração com sistemas SIEM - exporta alertas em formato estruturado.
     */
    public function exportAlertsForSIEM(?int $limit = 100, bool $onlyUnresolved = true): array
    {
        $query = SecurityAlert::query()
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        if ($onlyUnresolved) {
            $query->where('is_resolved', false);
        }

        $alerts = $query->limit($limit)->get();

        return $alerts->map(function ($alert) {
            return [
                'id' => $alert->id,
                'timestamp' => $alert->created_at->toIso8601String(),
                'alert_type' => $alert->alert_type,
                'severity' => $alert->severity,
                'title' => $alert->title,
                'description' => $alert->description,
                'user_id' => $alert->user_id,
                'user_email' => $alert->user?->email,
                'ip_address' => $alert->ip_address,
                'user_agent' => $alert->user_agent,
                'metadata' => $alert->metadata,
                'is_resolved' => $alert->is_resolved,
            ];
        })->toArray();
    }
}

