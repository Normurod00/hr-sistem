<?php

namespace App\Http\Controllers\Employee;

use App\Enums\KpiPeriodType;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EmployeeAiRecommendation;
use App\Models\EmployeeKpiSnapshot;
use App\Services\AiGatewayService;
use App\Services\Employee\EmployeeKpiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeKpiController extends Controller
{
    public function __construct(
        private readonly EmployeeKpiService $kpiService,
        private readonly AiGatewayService $aiGateway
    ) {}

    /**
     * Обзор KPI
     */
    public function index(Request $request): View
    {
        $employee = auth()->user()->employeeProfile;

        $periodType = $request->query('period', 'month');
        $months = match ($periodType) {
            'quarter' => 8,
            'year' => 3,
            default => 12,
        };

        $snapshots = $this->kpiService->getSnapshotsForPeriod($employee, $periodType, $months);
        $trend = $snapshots->map(fn($s) => [
            'period' => $s->period_label,
            'score' => $s->total_score,
            'date' => $s->period_start->format('Y-m-d'),
        ])->reverse()->values();

        // Логируем просмотр
        AuditLog::logView('kpi_overview', $employee->id);

        return view('employee.kpi.index', [
            'employee' => $employee,
            'snapshots' => $snapshots,
            'trend' => $trend,
            'currentPeriod' => $periodType,
            'availablePeriods' => KpiPeriodType::cases(),
        ]);
    }

    /**
     * Детали KPI snapshot
     */
    public function show(Request $request, EmployeeKpiSnapshot $snapshot): View
    {
        $this->authorizeSnapshot($snapshot);

        $details = $this->kpiService->getSnapshotDetails($snapshot);

        // Логируем просмотр
        AuditLog::logView('kpi_snapshot', $snapshot->id);

        return view('employee.kpi.show', [
            'snapshot' => $snapshot,
            'details' => $details,
        ]);
    }

    /**
     * Получить AI объяснение KPI
     */
    public function explain(Request $request, EmployeeKpiSnapshot $snapshot): JsonResponse
    {
        $this->authorizeSnapshot($snapshot);

        $employee = auth()->user()->employeeProfile;

        $result = $this->aiGateway->explainKpi($employee, [
            'period' => $snapshot->period_label,
            'total_score' => $snapshot->total_score,
            'metrics' => $snapshot->metrics,
            'bonus_info' => $snapshot->bonus_info,
            'low_metrics' => $snapshot->getLowPerformingMetrics(),
        ]);

        return response()->json($result);
    }

    /**
     * Получить рекомендации по улучшению
     */
    public function recommendations(Request $request, EmployeeKpiSnapshot $snapshot): View
    {
        $this->authorizeSnapshot($snapshot);

        $employee = auth()->user()->employeeProfile;

        // Проверяем, есть ли уже сохранённые рекомендации
        $existingRecommendations = $snapshot->recommendations()->byPriority()->get();

        if ($existingRecommendations->isEmpty()) {
            // Запрашиваем у AI
            $result = $this->aiGateway->getRecommendations($employee, [
                'total_score' => $snapshot->total_score,
                'metrics' => $snapshot->metrics,
                'low_metrics' => $snapshot->getLowPerformingMetrics(),
            ]);

            if ($result['success'] && !empty($result['recommendations'])) {
                // Сохраняем рекомендации
                foreach ($result['recommendations'] as $index => $rec) {
                    EmployeeAiRecommendation::create([
                        'employee_profile_id' => $employee->id,
                        'kpi_snapshot_id' => $snapshot->id,
                        'type' => $rec['type'] ?? 'medium',
                        'priority' => $index + 1,
                        'action' => $rec['action'],
                        'expected_effect' => $rec['expected_effect'] ?? null,
                        'expected_impact' => $rec['expected_impact'] ?? null,
                        'status' => 'pending',
                    ]);
                }

                $existingRecommendations = $snapshot->recommendations()->byPriority()->get();
            }
        }

        return view('employee.kpi.recommendations', [
            'snapshot' => $snapshot,
            'recommendations' => $existingRecommendations,
        ]);
    }

    /**
     * Обновить статус рекомендации
     */
    public function updateRecommendation(
        Request $request,
        EmployeeAiRecommendation $recommendation
    ): JsonResponse {
        // Проверяем владельца
        if ($recommendation->employee_profile_id !== auth()->user()->employeeProfile->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:in_progress,completed,dismissed',
            'notes' => 'nullable|string|max:500',
        ]);

        match ($validated['status']) {
            'in_progress' => $recommendation->markInProgress(),
            'completed' => $recommendation->markCompleted($validated['notes'] ?? null),
            'dismissed' => $recommendation->dismiss($validated['notes'] ?? null),
        };

        return response()->json([
            'success' => true,
            'recommendation' => $recommendation->fresh(),
        ]);
    }

    /**
     * API: получить KPI данные
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $employee = auth()->user()->employeeProfile;

        $periodType = $request->query('period', 'month');
        $months = (int) $request->query('months', 6);

        $snapshots = $this->kpiService->getSnapshotsForPeriod($employee, $periodType, $months);

        return response()->json([
            'snapshots' => $snapshots,
            'trend' => $snapshots->map(fn($s) => [
                'period' => $s->period_label,
                'score' => $s->total_score,
            ])->reverse()->values(),
        ]);
    }

    /**
     * Проверить доступ к snapshot
     */
    private function authorizeSnapshot(EmployeeKpiSnapshot $snapshot): void
    {
        $employee = auth()->user()->employeeProfile;

        if ($snapshot->employee_profile_id !== $employee->id) {
            // Проверяем права
            if (!$employee->canViewEmployee($snapshot->employeeProfile)) {
                abort(403, 'Доступ запрещён');
            }
        }
    }
}
