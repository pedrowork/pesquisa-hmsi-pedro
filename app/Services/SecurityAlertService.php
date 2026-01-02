<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SecurityAlertService
{
    /**
     * Analisa logs recentes e identifica padrões suspeitos.
     */
    public function analyzeSecurityThreats(int $hours = 24): array
    {
        $alerts = [];

        // Múltiplas tentativas de login falhadas
        $failedLogins = $this->checkMultipleFailedLogins($hours);
        if (!empty($failedLogins)) {
            $alerts[] = [
                'type' => 'multiple_failed_logins',
                'severity' => 'warning',
                'message' => 'Múltiplas tentativas de login falhadas detectadas',
                'data' => $failedLogins,
            ];
        }

        // Múltiplas mudanças de senha em curto período
        $passwordChanges = $this->checkMultiplePasswordChanges($hours);
        if (!empty($passwordChanges)) {
            $alerts[] = [
                'type' => 'multiple_password_changes',
                'severity' => 'critical',
                'message' => 'Múltiplas mudanças de senha detectadas em curto período',
                'data' => $passwordChanges,
            ];
        }

        // Exclusões de usuários
        $userDeletions = $this->checkUserDeletions($hours);
        if (!empty($userDeletions)) {
            $alerts[] = [
                'type' => 'user_deletions',
                'severity' => 'critical',
                'message' => 'Exclusões de usuários detectadas',
                'data' => $userDeletions,
            ];
        }

        // Atividades de admin fora do horário normal
        $afterHoursAdmin = $this->checkAfterHoursAdminActivity($hours);
        if (!empty($afterHoursAdmin)) {
            $alerts[] = [
                'type' => 'after_hours_admin_activity',
                'severity' => 'warning',
                'message' => 'Atividades administrativas fora do horário normal',
                'data' => $afterHoursAdmin,
            ];
        }

        // Múltiplos IPs para o mesmo usuário
        $multipleIps = $this->checkMultipleIpsForUser($hours);
        if (!empty($multipleIps)) {
            $alerts[] = [
                'type' => 'multiple_ips_user',
                'severity' => 'warning',
                'message' => 'Múltiplos IPs detectados para o mesmo usuário',
                'data' => $multipleIps,
            ];
        }

        return $alerts;
    }

    /**
     * Verifica múltiplas tentativas de login falhadas.
     */
    protected function checkMultipleFailedLogins(int $hours, int $threshold = 5): array
    {
        $since = now()->subHours($hours);

        return AuditLog::where('event_type', 'login.failed')
            ->where('created_at', '>=', $since)
            ->select('ip_address', DB::raw('COUNT(*) as count'))
            ->groupBy('ip_address')
            ->having('count', '>=', $threshold)
            ->get()
            ->toArray();
    }

    /**
     * Verifica múltiplas mudanças de senha.
     */
    protected function checkMultiplePasswordChanges(int $hours, int $threshold = 3): array
    {
        $since = now()->subHours($hours);

        return AuditLog::where('event_type', 'password.changed')
            ->where('created_at', '>=', $since)
            ->select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->having('count', '>=', $threshold)
            ->with('user:id,email,name')
            ->get()
            ->toArray();
    }

    /**
     * Verifica exclusões de usuários.
     */
    protected function checkUserDeletions(int $hours): array
    {
        $since = now()->subHours($hours);

        return AuditLog::where('event_type', 'user.deleted')
            ->where('created_at', '>=', $since)
            ->with('user:id,email,name')
            ->get()
            ->toArray();
    }

    /**
     * Verifica atividades administrativas fora do horário normal (22h - 6h).
     */
    protected function checkAfterHoursAdminActivity(int $hours): array
    {
        $since = now()->subHours($hours);

        return AuditLog::where('category', 'system')
            ->where('created_at', '>=', $since)
            ->where(function ($query) {
                $query->whereRaw('HOUR(created_at) >= 22')
                    ->orWhereRaw('HOUR(created_at) < 6');
            })
            ->with('user:id,email,name')
            ->get()
            ->toArray();
    }

    /**
     * Verifica múltiplos IPs para o mesmo usuário.
     */
    protected function checkMultipleIpsForUser(int $hours, int $threshold = 3): array
    {
        $since = now()->subHours($hours);

        return AuditLog::whereNotNull('user_id')
            ->whereNotNull('ip_address')
            ->where('created_at', '>=', $since)
            ->select('user_id', DB::raw('COUNT(DISTINCT ip_address) as ip_count'))
            ->groupBy('user_id')
            ->having('ip_count', '>=', $threshold)
            ->with('user:id,email,name')
            ->get()
            ->toArray();
    }

    /**
     * Envia notificações para alertas críticos.
     */
    public function notifyCriticalAlerts(array $alerts): void
    {
        $criticalAlerts = array_filter($alerts, fn($alert) => $alert['severity'] === 'critical');

        if (empty($criticalAlerts)) {
            return;
        }

        // Log dos alertas críticos
        Log::critical('Security alerts detected', ['alerts' => $criticalAlerts]);

        // Aqui você pode adicionar notificações por email, Slack, etc.
        // Exemplo:
        // $admins = User::whereHas('roles', fn($q) => $q->where('slug', 'admin'))->get();
        // Notification::send($admins, new SecurityAlertNotification($criticalAlerts));
    }

    /**
     * Gera relatório de segurança.
     */
    public function generateSecurityReport(int $days = 7): array
    {
        $since = now()->subDays($days);

        return [
            'period' => [
                'start' => $since->format('Y-m-d H:i:s'),
                'end' => now()->format('Y-m-d H:i:s'),
            ],
            'summary' => [
                'total_events' => AuditLog::where('created_at', '>=', $since)->count(),
                'security_alerts' => AuditLog::where('created_at', '>=', $since)
                    ->where('is_security_alert', true)
                    ->count(),
                'failed_logins' => AuditLog::where('created_at', '>=', $since)
                    ->where('event_type', 'login.failed')
                    ->count(),
                'password_changes' => AuditLog::where('created_at', '>=', $since)
                    ->where('event_type', 'password.changed')
                    ->count(),
                'user_creations' => AuditLog::where('created_at', '>=', $since)
                    ->where('event_type', 'user.created')
                    ->count(),
                'user_deletions' => AuditLog::where('created_at', '>=', $since)
                    ->where('event_type', 'user.deleted')
                    ->count(),
            ],
            'alerts' => $this->analyzeSecurityThreats($days * 24),
        ];
    }
}
