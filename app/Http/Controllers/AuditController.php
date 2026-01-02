<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Services\SecurityAlertService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditController extends Controller
{
    protected AuditService $auditService;
    protected SecurityAlertService $securityAlertService;

    public function __construct(AuditService $auditService, SecurityAlertService $securityAlertService)
    {
        $this->auditService = $auditService;
        $this->securityAlertService = $securityAlertService;
    }

    /**
     * Display the audit logs dashboard.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'category' => $request->get('category'),
            'severity' => $request->get('severity'),
            'event_type' => $request->get('event_type'),
            'user_id' => $request->get('user_id'),
            'security_alerts_only' => $request->boolean('security_alerts_only'),
            'days' => (int) $request->get('days', 7),
        ];

        $logs = $this->auditService->getLogs(
            userId: $filters['user_id'],
            category: $filters['category'],
            eventType: $filters['event_type'],
            severity: $filters['severity'],
            securityAlertsOnly: $filters['security_alerts_only'],
            limit: 100
        );

        // Gerar relatório de segurança
        $securityReport = $this->securityAlertService->generateSecurityReport($filters['days']);

        // Estatísticas gerais
        $stats = [
            'total_logs' => $logs->count(),
            'security_alerts' => $logs->where('is_security_alert', true)->count(),
            'by_category' => $logs->groupBy('category')->map->count(),
            'by_severity' => $logs->groupBy('severity')->map->count(),
        ];

        return Inertia::render('audit/index', [
            'logs' => $logs,
            'filters' => $filters,
            'stats' => $stats,
            'securityReport' => $securityReport,
        ]);
    }

    /**
     * Display security alerts.
     */
    public function alerts(Request $request): Response
    {
        $hours = (int) $request->get('hours', 24);
        
        $alerts = $this->securityAlertService->analyzeSecurityThreats($hours);
        
        // Notificar alertas críticos
        $this->securityAlertService->notifyCriticalAlerts($alerts);

        return Inertia::render('audit/alerts', [
            'alerts' => $alerts,
            'hours' => $hours,
        ]);
    }

    /**
     * Display security report.
     */
    public function report(Request $request): Response
    {
        $days = (int) $request->get('days', 7);
        
        $report = $this->securityAlertService->generateSecurityReport($days);

        return Inertia::render('audit/report', [
            'report' => $report,
            'days' => $days,
        ]);
    }
}
