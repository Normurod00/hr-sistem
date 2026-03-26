<?php

namespace App\Services\Integrations\Kpi;

use App\Models\EmployeeKpiSnapshot;
use App\Models\EmployeeProfile;
use App\Services\Integrations\Contracts\KpiProviderInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Клиент для работы с KPI данными
 *
 * Является единственной точкой доступа к KPI данным.
 * Автоматически выбирает провайдер (Mock или HTTP) в зависимости от конфигурации.
 */
class KpiClient
{
    private KpiProviderInterface $provider;

    public function __construct()
    {
        // Выбираем провайдер в зависимости от конфигурации
        if (config('integrations.kpi.enabled') && !config('integrations.mock.enabled')) {
            $this->provider = new HttpKpiProvider();
        } else {
            $this->provider = new MockKpiProvider();
        }
    }

    /**
     * Установить провайдер (для тестирования)
     */
    public function setProvider(KpiProviderInterface $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * Получить текущий провайдер
     */
    public function getProvider(): KpiProviderInterface
    {
        return $this->provider;
    }

    /**
     * Получить KPI сотрудника за период
     */
    public function getEmployeeKpi(
        EmployeeProfile $employee,
        string $periodType = 'month',
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): ?array {
        return $this->provider->getEmployeeKpi($employee, $periodType, $startDate, $endDate);
    }

    /**
     * Получить и сохранить snapshot KPI
     */
    public function fetchAndSaveSnapshot(
        EmployeeProfile $employee,
        string $periodType = 'month',
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): ?EmployeeKpiSnapshot {
        $kpiData = $this->getEmployeeKpi($employee, $periodType, $startDate, $endDate);

        if (!$kpiData) {
            return null;
        }

        return EmployeeKpiSnapshot::updateOrCreate(
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

    /**
     * Получить историю KPI сотрудника
     */
    public function getEmployeeKpiHistory(EmployeeProfile $employee, int $months = 12): Collection
    {
        return $this->provider->getEmployeeKpiHistory($employee, $months);
    }

    /**
     * Получить KPI отдела
     */
    public function getDepartmentKpi(string $department, string $periodType = 'month'): ?array
    {
        return $this->provider->getDepartmentKpi($department, $periodType);
    }

    /**
     * Получить доступные периоды
     */
    public function getAvailablePeriods(): array
    {
        return $this->provider->getAvailablePeriods();
    }

    /**
     * Получить определения метрик
     */
    public function getMetricsDefinitions(): array
    {
        return $this->provider->getMetricsDefinitions();
    }

    /**
     * Проверить здоровье интеграции
     */
    public function healthCheck(): array
    {
        return $this->provider->healthCheck();
    }

    /**
     * Синхронизировать KPI сотрудника
     */
    public function syncEmployeeKpi(EmployeeProfile $employee): bool
    {
        try {
            $result = $this->provider->syncEmployeeKpi($employee);

            if ($result) {
                // Также обновляем локальный snapshot
                $this->fetchAndSaveSnapshot($employee, 'month');
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to sync employee KPI', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Получить snapshots из локальной БД
     */
    public function getLocalSnapshots(
        EmployeeProfile $employee,
        ?string $periodType = null,
        int $limit = 12
    ): Collection {
        $query = $employee->kpiSnapshots()
            ->orderByDesc('period_end')
            ->limit($limit);

        if ($periodType) {
            $query->where('period_type', $periodType);
        }

        return $query->get();
    }

    /**
     * Получить snapshot из локальной БД или fetch свежий
     */
    public function getOrFetchSnapshot(
        EmployeeProfile $employee,
        string $periodType = 'month',
        ?\DateTimeInterface $startDate = null
    ): ?EmployeeKpiSnapshot {
        $startDate = $startDate ?? now()->startOfMonth();

        // Сначала ищем в локальной БД
        $snapshot = $employee->kpiSnapshots()
            ->where('period_type', $periodType)
            ->where('period_start', $startDate->format('Y-m-d'))
            ->first();

        // Если нет или устарел (> 1 часа), fetch свежий
        if (!$snapshot || $snapshot->synced_at?->diffInHours(now()) > 1) {
            $snapshot = $this->fetchAndSaveSnapshot($employee, $periodType, $startDate);
        }

        return $snapshot;
    }

    /**
     * Получить тренд KPI (для графика)
     */
    public function getKpiTrend(EmployeeProfile $employee, int $months = 6): array
    {
        $snapshots = $this->getLocalSnapshots($employee, 'month', $months);

        if ($snapshots->isEmpty()) {
            // Если нет локальных данных, пробуем получить из API
            $history = $this->getEmployeeKpiHistory($employee, $months);

            return $history->map(fn($item) => [
                'period' => $item['period_start'],
                'score' => $item['total_score'],
            ])->toArray();
        }

        return $snapshots->map(fn($s) => [
            'period' => $s->period_start->format('Y-m'),
            'score' => $s->total_score,
            'label' => $s->period_label,
        ])->reverse()->values()->toArray();
    }

    /**
     * Рассчитать risk score на основе тренда
     */
    public function calculateRiskScore(EmployeeProfile $employee): array
    {
        $trend = $this->getKpiTrend($employee, 3);

        if (count($trend) < 2) {
            return [
                'score' => 0,
                'level' => 'unknown',
                'message' => 'Недостаточно данных для анализа',
            ];
        }

        // Простой алгоритм: смотрим на тренд и текущий уровень
        $currentScore = $trend[count($trend) - 1]['score'] ?? 0;
        $previousScore = $trend[count($trend) - 2]['score'] ?? 0;
        $delta = $currentScore - $previousScore;

        $riskScore = match (true) {
            $currentScore < 50 && $delta < 0 => 90, // Низкий и падает
            $currentScore < 50 => 70, // Низкий
            $currentScore < 70 && $delta < -10 => 60, // Средний и сильно падает
            $currentScore < 70 && $delta < 0 => 40, // Средний и немного падает
            $delta < -15 => 50, // Резкое падение
            default => max(0, 30 - $currentScore / 5), // Нормальный
        };

        $level = match (true) {
            $riskScore >= 70 => 'high',
            $riskScore >= 40 => 'medium',
            $riskScore > 0 => 'low',
            default => 'none',
        };

        $message = match ($level) {
            'high' => 'Требуется срочное внимание: KPI критически низкий',
            'medium' => 'Рекомендуется обратить внимание на показатели',
            'low' => 'Незначительные отклонения, мониторинг продолжается',
            default => 'Показатели в норме',
        };

        return [
            'score' => round($riskScore),
            'level' => $level,
            'message' => $message,
            'current_kpi' => $currentScore,
            'delta' => round($delta, 2),
        ];
    }
}
