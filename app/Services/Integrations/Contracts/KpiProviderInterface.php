<?php

namespace App\Services\Integrations\Contracts;

use App\Models\EmployeeProfile;
use Illuminate\Support\Collection;

/**
 * Интерфейс провайдера KPI данных
 *
 * Реализуйте этот интерфейс для подключения к реальному KPI API
 */
interface KpiProviderInterface
{
    /**
     * Получить KPI сотрудника за период
     *
     * @param EmployeeProfile $employee
     * @param string $periodType month|quarter|half_year|year
     * @param \DateTimeInterface|null $startDate
     * @param \DateTimeInterface|null $endDate
     * @return array{
     *     employee_id: string,
     *     period_type: string,
     *     period_start: string,
     *     period_end: string,
     *     metrics: array<string, array{value: float, target: float, weight: float, name: string}>,
     *     total_score: float,
     *     status: string,
     *     bonus_info: array{eligible: bool, amount: float, paid: bool}|null
     * }|null
     */
    public function getEmployeeKpi(
        EmployeeProfile $employee,
        string $periodType,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): ?array;

    /**
     * Получить историю KPI сотрудника
     *
     * @param EmployeeProfile $employee
     * @param int $months Количество месяцев истории
     * @return Collection<int, array>
     */
    public function getEmployeeKpiHistory(EmployeeProfile $employee, int $months = 12): Collection;

    /**
     * Получить KPI отдела
     *
     * @param string $department
     * @param string $periodType
     * @return array|null
     */
    public function getDepartmentKpi(string $department, string $periodType): ?array;

    /**
     * Получить доступные периоды
     *
     * @return array<int, array{type: string, start: string, end: string, label: string}>
     */
    public function getAvailablePeriods(): array;

    /**
     * Получить список метрик
     *
     * @return array<string, array{name: string, description: string, unit: string}>
     */
    public function getMetricsDefinitions(): array;

    /**
     * Проверить здоровье интеграции
     *
     * @return array{healthy: bool, message: string, latency_ms: int|null}
     */
    public function healthCheck(): array;

    /**
     * Синхронизировать KPI сотрудника
     *
     * @param EmployeeProfile $employee
     * @return bool
     */
    public function syncEmployeeKpi(EmployeeProfile $employee): bool;
}
