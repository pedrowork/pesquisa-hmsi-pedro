<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityAlert;
use App\Services\SecurityMonitoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SecurityDashboardController extends Controller
{
    protected SecurityMonitoringService $monitoringService;

    public function __construct(SecurityMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Exibe o dashboard de segurança.
     */
    public function index(Request $request): Response
    {
        $days = (int) $request->get('days', 30);
        $metrics = $this->monitoringService->getSecurityMetrics($days);

        $alerts = SecurityAlert::with(['user', 'resolver'])
            ->orderBy('created_at', 'desc')
            ->where('is_resolved', false)
            ->limit(20)
            ->get();

        return Inertia::render('admin/security/dashboard', [
            'metrics' => $metrics,
            'recentAlerts' => $alerts,
            'days' => $days,
        ]);
    }

    /**
     * Lista todos os alertas com filtros.
     */
    public function alerts(Request $request)
    {
        $query = SecurityAlert::with(['user', 'resolver'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('alert_type')) {
            $query->where('alert_type', $request->alert_type);
        }

        if ($request->has('resolved')) {
            $query->where('is_resolved', $request->boolean('resolved'));
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $alerts = $query->paginate(50);

        return Inertia::render('admin/security/alerts', [
            'alerts' => $alerts,
            'filters' => $request->only(['severity', 'alert_type', 'resolved', 'user_id']),
        ]);
    }

    /**
     * Resolve um alerta de segurança.
     */
    public function resolve(Request $request, SecurityAlert $alert)
    {
        $validated = $request->validate([
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $alert->resolve($request->user(), $validated['resolution_notes'] ?? null);

        return back()->with('success', 'Alerta resolvido com sucesso.');
    }

    /**
     * Exporta alertas para sistemas SIEM.
     */
    public function exportSIEM(Request $request)
    {
        $limit = (int) $request->get('limit', 100);
        $onlyUnresolved = $request->boolean('only_unresolved', true);

        $alerts = $this->monitoringService->exportAlertsForSIEM($limit, $onlyUnresolved);

        return response()->json([
            'format' => 'SIEM',
            'version' => '1.0',
            'timestamp' => now()->toIso8601String(),
            'alerts' => $alerts,
        ], 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="security_alerts_' . now()->format('Y-m-d_His') . '.json"',
        ]);
    }
}

