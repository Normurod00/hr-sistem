<?php

namespace App\Services\Employee;

use App\Models\EmployeeAiRecommendation;
use App\Models\EmployeeKpiSnapshot;
use App\Models\EmployeeProfile;
use App\Services\Integrations\Kpi\KpiClient;
use Illuminate\Support\Collection;

/**
 * Сервис для работы с KPI сотрудников
 *
 * Агрегирует данные из KpiClient и добавляет бизнес-логику
 */
class EmployeeKpiService
{
    public function __construct(
        private readonly KpiClient $kpiClient
    ) {}

    /**
     * Получить дашборд KPI для сотрудника
     */
    public function getDashboard(EmployeeProfile $employee): array
    {
        $currentSnapshot = $this->kpiClient->getOrFetchSnapshot($employee, 'month');
        $quarterSnapshot = $this->kpiClient->getOrFetchSnapshot(
            $employee,
            'quarter',
            now()->startOfQuarter()
        );

        $trend = $this->kpiClient->getKpiTrend($employee, 6);
        $riskScore = $this->kpiClient->calculateRiskScore($employee);

        $recommendations = $employee->aiRecommendations()
            ->pending()
            ->byPriority()
            ->limit(5)
            ->get();

        return [
            'current' => $currentSnapshot?->toArray(),
            'quarter' => $quarterSnapshot?->toArray(),
            'trend' => $trend,
            'risk' => $riskScore,
            'recommendations' => $recommendations,
            'metrics_definitions' => $this->kpiClient->getMetricsDefinitions(),
        ];
    }

    /**
     * Получить детали KPI snapshot с анализом
     */
    public function getSnapshotDetails(EmployeeKpiSnapshot $snapshot): array
    {
        $lowMetrics = $snapshot->getLowPerformingMetrics();
        $highMetrics = $snapshot->getHighPerformingMetrics();

        // Получаем предыдущий период для сравнения
        $previousSnapshot = $snapshot->employeeProfile
            ->kpiSnapshots()
            ->where('period_type', $snapshot->period_type)
            ->where('period_start', '<', $snapshot->period_start)
            ->orderByDesc('period_start')
            ->first();

        $comparison = $previousSnapshot
            ? $this->compareSnapshots($snapshot, $previousSnapshot)
            : null;

        return [
            'snapshot' => $snapshot->toArray(),
            'low_metrics' => $lowMetrics,
            'high_metrics' => $highMetrics,
            'comparison' => $comparison,
            'bonus_analysis' => $this->analyzeBonusEligibility($snapshot),
            'recommendations' => $snapshot->recommendations()->byPriority()->get(),
        ];
    }

    /**
     * Сравнить два snapshot'а
     */
    public function compareSnapshots(
        EmployeeKpiSnapshot $current,
        EmployeeKpiSnapshot $previous
    ): array {
        $changes = [];

        foreach ($current->metrics as $key => $metric) {
            $prevMetric = $previous->metrics[$key] ?? null;

            if (!$prevMetric) {
                continue;
            }

            $valueDelta = $metric['value'] - $prevMetric['value'];
            $completionDelta = $metric['completion'] - ($prevMetric['completion'] ?? 0);

            $changes[$key] = [
                'name' => $metric['name'],
                'current_value' => $metric['value'],
                'previous_value' => $prevMetric['value'],
                'value_delta' => round($valueDelta, 2),
                'completion_delta' => round($completionDelta, 2),
                'trend' => $valueDelta > 0 ? 'up' : ($valueDelta < 0 ? 'down' : 'stable'),
            ];
        }

        return [
            'previous_period' => $previous->period_label,
            'score_delta' => round($current->total_score - $previous->total_score, 2),
            'metrics_changes' => $changes,
        ];
    }

    /**
     * Анализ права на бонус
     */
    public function analyzeBonusEligibility(EmployeeKpiSnapshot $snapshot): array
    {
        $bonusInfo = $snapshot->bonus_info ?? [];
        $eligible = $bonusInfo['eligible'] ?? false;
        $amount = $bonusInfo['amount'] ?? 0;
        $paid = $bonusInfo['paid'] ?? false;

        $reasons = [];

        if (!$eligible) {
            if ($snapshot->total_score < 50) {
                $reasons[] = [
                    'type' => 'low_kpi',
                    'message' => 'KPI ниже минимального порога 50%',
                    'value' => $snapshot->total_score,
                    'threshold' => 50,
                ];
            }

            // Проверяем отдельные метрики
            foreach ($snapshot->metrics as $key => $metric) {
                if (($metric['completion'] ?? 0) < 30) {
                    $reasons[] = [
                        'type' => 'metric_critical',
                        'message' => "Критически низкий показатель: {$metric['name']}",
                        'metric' => $key,
                        'value' => $metric['completion'],
                        'threshold' => 30,
                    ];
                }
            }
        }

        $multiplierExplanation = null;
        if ($eligible && isset($bonusInfo['multiplier'])) {
            $multiplierExplanation = match (true) {
                $bonusInfo['multiplier'] >= 1.5 => 'Максимальный коэффициент за превышение плана',
                $bonusInfo['multiplier'] >= 1.2 => 'Повышенный коэффициент за отличные результаты',
                $bonusInfo['multiplier'] >= 1.0 => 'Стандартный коэффициент',
                default => 'Сниженный коэффициент из-за частичного выполнения плана',
            };
        }

        return [
            'eligible' => $eligible,
            'amount' => $amount,
            'paid' => $paid,
            'reasons' => $reasons,
            'multiplier' => $bonusInfo['multiplier'] ?? null,
            'multiplier_explanation' => $multiplierExplanation,
            'potential_amount' => $this->calculatePotentialBonus($snapshot),
        ];
    }

    /**
     * Рассчитать потенциальный бонус при 100% KPI
     */
    private function calculatePotentialBonus(EmployeeKpiSnapshot $snapshot): float
    {
        // Базовая логика расчёта потенциального бонуса
        $baseBonus = 1000000; // Базовый бонус
        $maxMultiplier = 1.5;

        return $baseBonus * $maxMultiplier;
    }

    /**
     * Получить snapshots для периода
     */
    public function getSnapshotsForPeriod(
        EmployeeProfile $employee,
        string $periodType,
        int $count = 12
    ): Collection {
        // Сначала проверяем локальные данные
        $snapshots = $this->kpiClient->getLocalSnapshots($employee, $periodType, $count);

        // Если мало данных, пробуем загрузить из API
        if ($snapshots->count() < $count) {
            $this->syncMissingSnapshots($employee, $periodType, $count);
            $snapshots = $this->kpiClient->getLocalSnapshots($employee, $periodType, $count);
        }

        return $snapshots;
    }

    /**
     * Синхронизировать недостающие snapshots
     */
    private function syncMissingSnapshots(
        EmployeeProfile $employee,
        string $periodType,
        int $count
    ): void {
        $history = $this->kpiClient->getEmployeeKpiHistory($employee, $count);

        foreach ($history as $kpiData) {
            if ($kpiData['period_type'] !== $periodType) {
                continue;
            }

            EmployeeKpiSnapshot::updateOrCreate(
                [
                    'employee_profile_id' => $employee->id,
                    'period_type' => $kpiData['period_type'],
                    'period_start' => $kpiData['period_start'],
                ],
                [
                    'period_end' => $kpiData['period_end'],
                    'metrics' => $kpiData['metrics'],
                    'total_score' => $kpiData['total_score'],
                    'status' => $kpiData['status'],
                    'bonus_info' => $kpiData['bonus_info'],
                    'synced_at' => now(),
                    'raw_response' => $kpiData,
                ]
            );
        }
    }

    /**
     * Получить агрегированную статистику для менеджера
     */
    public function getTeamStats(EmployeeProfile $manager): array
    {
        $teamIds = $manager->getTeamIds();

        $snapshots = EmployeeKpiSnapshot::whereIn('employee_profile_id', $teamIds)
            ->where('period_type', 'month')
            ->where('period_start', '>=', now()->subMonths(3)->startOfMonth())
            ->get()
            ->groupBy('employee_profile_id');

        $stats = [
            'team_size' => count($teamIds) - 1, // без самого менеджера
            'average_score' => 0,
            'top_performers' => [],
            'needs_attention' => [],
            'by_department' => [],
        ];

        $totalScore = 0;
        $count = 0;

        foreach ($snapshots as $employeeId => $employeeSnapshots) {
            $latestSnapshot = $employeeSnapshots->sortByDesc('period_end')->first();

            if (!$latestSnapshot) {
                continue;
            }

            $totalScore += $latestSnapshot->total_score;
            $count++;

            $employeeData = [
                'employee_id' => $employeeId,
                'name' => $latestSnapshot->employeeProfile->full_name,
                'score' => $latestSnapshot->total_score,
                'trend' => $this->calculateTrendDirection($employeeSnapshots),
            ];

            if ($latestSnapshot->total_score >= 90) {
                $stats['top_performers'][] = $employeeData;
            } elseif ($latestSnapshot->total_score < 60) {
                $stats['needs_attention'][] = $employeeData;
            }
        }

        $stats['average_score'] = $count > 0 ? round($totalScore / $count, 2) : 0;

        return $stats;
    }

    /**
     * Определить направление тренда
     */
    private function calculateTrendDirection(Collection $snapshots): string
    {
        if ($snapshots->count() < 2) {
            return 'stable';
        }

        $sorted = $snapshots->sortByDesc('period_end')->values();
        $current = $sorted->first()->total_score;
        $previous = $sorted->get(1)->total_score;

        $delta = $current - $previous;

        return match (true) {
            $delta > 5 => 'up',
            $delta < -5 => 'down',
            default => 'stable',
        };
    }
}
