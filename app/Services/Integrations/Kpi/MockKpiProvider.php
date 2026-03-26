<?php

namespace App\Services\Integrations\Kpi;

use App\Models\EmployeeProfile;
use App\Services\Integrations\Contracts\KpiProviderInterface;
use Illuminate\Support\Collection;

/**
 * Mock провайдер KPI для разработки и тестирования
 *
 * Возвращает реалистичные тестовые данные
 */
class MockKpiProvider implements KpiProviderInterface
{
    private array $metricsDefinitions = [
        'sales' => [
            'name' => 'Продажи',
            'description' => 'Объём продаж продуктов банка',
            'unit' => 'шт',
        ],
        'customer_satisfaction' => [
            'name' => 'Удовлетворённость клиентов',
            'description' => 'NPS/CSI показатели',
            'unit' => '%',
        ],
        'task_completion' => [
            'name' => 'Выполнение задач',
            'description' => 'Процент выполненных задач в срок',
            'unit' => '%',
        ],
        'quality' => [
            'name' => 'Качество работы',
            'description' => 'Оценка качества выполненных задач',
            'unit' => 'балл',
        ],
        'attendance' => [
            'name' => 'Посещаемость',
            'description' => 'Процент рабочих дней без пропусков',
            'unit' => '%',
        ],
        'training' => [
            'name' => 'Обучение',
            'description' => 'Прохождение обязательных курсов',
            'unit' => '%',
        ],
    ];

    public function getEmployeeKpi(
        EmployeeProfile $employee,
        string $periodType,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): ?array {
        // Имитация задержки API
        $this->simulateDelay();

        $startDate = $startDate ?? $this->getDefaultStartDate($periodType);
        $endDate = $endDate ?? $this->getDefaultEndDate($periodType, $startDate);

        // Генерируем детерминированные данные на основе ID сотрудника и периода
        $seed = crc32($employee->employee_number . $periodType . $startDate->format('Y-m'));
        mt_srand($seed);

        $metrics = $this->generateMetrics($employee);
        $totalScore = $this->calculateTotalScore($metrics);

        return [
            'employee_id' => $employee->employee_number,
            'period_type' => $periodType,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'metrics' => $metrics,
            'total_score' => round($totalScore, 2),
            'status' => $totalScore >= 50 ? 'approved' : 'calculated',
            'bonus_info' => $this->generateBonusInfo($totalScore),
            'synced_at' => now()->toIso8601String(),
        ];
    }

    public function getEmployeeKpiHistory(EmployeeProfile $employee, int $months = 12): Collection
    {
        $history = collect();
        $currentDate = now()->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $date = $currentDate->copy()->subMonths($i);
            $kpi = $this->getEmployeeKpi(
                $employee,
                'month',
                $date->copy()->startOfMonth(),
                $date->copy()->endOfMonth()
            );

            if ($kpi) {
                $history->push($kpi);
            }
        }

        return $history;
    }

    public function getDepartmentKpi(string $department, string $periodType): ?array
    {
        $this->simulateDelay();

        return [
            'department' => $department,
            'period_type' => $periodType,
            'average_score' => mt_rand(65, 85) + mt_rand(0, 99) / 100,
            'employee_count' => mt_rand(10, 50),
            'top_performers' => mt_rand(3, 8),
            'needs_improvement' => mt_rand(1, 5),
        ];
    }

    public function getAvailablePeriods(): array
    {
        $periods = [];
        $now = now();

        // Последние 12 месяцев
        for ($i = 0; $i < 12; $i++) {
            $date = $now->copy()->subMonths($i);
            $periods[] = [
                'type' => 'month',
                'start' => $date->startOfMonth()->format('Y-m-d'),
                'end' => $date->endOfMonth()->format('Y-m-d'),
                'label' => $date->translatedFormat('F Y'),
            ];
        }

        // Последние 4 квартала
        for ($i = 0; $i < 4; $i++) {
            $date = $now->copy()->subQuarters($i);
            $quarter = ceil($date->month / 3);
            $periods[] = [
                'type' => 'quarter',
                'start' => $date->copy()->firstOfQuarter()->format('Y-m-d'),
                'end' => $date->copy()->lastOfQuarter()->format('Y-m-d'),
                'label' => "Q{$quarter} {$date->year}",
            ];
        }

        return $periods;
    }

    public function getMetricsDefinitions(): array
    {
        return $this->metricsDefinitions;
    }

    public function healthCheck(): array
    {
        $start = microtime(true);
        $this->simulateDelay();
        $latency = (int) ((microtime(true) - $start) * 1000);

        return [
            'healthy' => true,
            'message' => 'Mock KPI Provider is operational',
            'latency_ms' => $latency,
        ];
    }

    public function syncEmployeeKpi(EmployeeProfile $employee): bool
    {
        $this->simulateDelay();
        return true;
    }

    // ===== PRIVATE HELPERS =====

    private function simulateDelay(): void
    {
        $delay = config('integrations.mock.delay_ms', 100);
        if ($delay > 0) {
            usleep($delay * 1000);
        }
    }

    private function getDefaultStartDate(string $periodType): \DateTimeInterface
    {
        return match ($periodType) {
            'quarter' => now()->startOfQuarter(),
            'half_year' => now()->month <= 6
                ? now()->startOfYear()
                : now()->startOfYear()->addMonths(6),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    private function getDefaultEndDate(string $periodType, \DateTimeInterface $startDate): \DateTimeInterface
    {
        $start = \Carbon\Carbon::parse($startDate);

        return match ($periodType) {
            'quarter' => $start->copy()->endOfQuarter(),
            'half_year' => $start->copy()->addMonths(6)->subDay(),
            'year' => $start->copy()->endOfYear(),
            default => $start->copy()->endOfMonth(),
        };
    }

    private function generateMetrics(EmployeeProfile $employee): array
    {
        $metrics = [];
        $weights = [
            'sales' => 0.25,
            'customer_satisfaction' => 0.20,
            'task_completion' => 0.20,
            'quality' => 0.15,
            'attendance' => 0.10,
            'training' => 0.10,
        ];

        foreach ($this->metricsDefinitions as $key => $definition) {
            $target = $this->getTargetForMetric($key);
            $value = $this->generateValue($key, $target);

            $metrics[$key] = [
                'name' => $definition['name'],
                'value' => round($value, 2),
                'target' => $target,
                'weight' => $weights[$key] ?? 0.10,
                'unit' => $definition['unit'],
                'completion' => $target > 0 ? round(min(100, ($value / $target) * 100), 2) : 0,
            ];
        }

        return $metrics;
    }

    private function getTargetForMetric(string $metric): float
    {
        return match ($metric) {
            'sales' => 100,
            'customer_satisfaction' => 90,
            'task_completion' => 95,
            'quality' => 4.5,
            'attendance' => 98,
            'training' => 100,
            default => 100,
        };
    }

    private function generateValue(string $metric, float $target): float
    {
        // Генерируем значение в диапазоне 50-120% от target
        $minPercent = 50;
        $maxPercent = 120;

        $percent = mt_rand($minPercent, $maxPercent);
        $value = $target * ($percent / 100);

        // Для процентных метрик ограничиваем 100%
        if (in_array($metric, ['customer_satisfaction', 'task_completion', 'attendance', 'training'])) {
            $value = min(100, $value);
        }

        return $value;
    }

    private function calculateTotalScore(array $metrics): float
    {
        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($metrics as $metric) {
            $weight = $metric['weight'];
            $completion = $metric['completion'];

            $weightedSum += $completion * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

    private function generateBonusInfo(float $totalScore): ?array
    {
        if ($totalScore < 50) {
            return [
                'eligible' => false,
                'amount' => 0,
                'paid' => false,
                'reason' => 'KPI ниже минимального порога (50%)',
            ];
        }

        $baseBonus = 1000000; // 1 млн сум
        $multiplier = match (true) {
            $totalScore >= 100 => 1.5,
            $totalScore >= 90 => 1.2,
            $totalScore >= 70 => 1.0,
            default => 0.7,
        };

        return [
            'eligible' => true,
            'amount' => $baseBonus * $multiplier,
            'paid' => mt_rand(0, 1) === 1,
            'multiplier' => $multiplier,
        ];
    }
}
