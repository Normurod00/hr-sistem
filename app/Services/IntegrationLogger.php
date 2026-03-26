<?php

namespace App\Services;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Сервис для работы с логами интеграций
 */
class IntegrationLogger
{
    /**
     * Получить статистику по интеграциям за период
     */
    public function getStats(int $hours = 24): array
    {
        $since = now()->subHours($hours);

        $stats = IntegrationLog::where('created_at', '>=', $since)
            ->selectRaw('integration, status, COUNT(*) as count, AVG(duration_ms) as avg_duration')
            ->groupBy('integration', 'status')
            ->get();

        $result = [];

        foreach (IntegrationType::cases() as $type) {
            $typeStats = $stats->where('integration', $type->value);

            $total = $typeStats->sum('count');
            $successful = $typeStats->where('status', IntegrationStatus::Success->value)->sum('count');
            $failed = $total - $successful;

            $result[$type->value] = [
                'name' => $type->label(),
                'total_requests' => $total,
                'successful' => $successful,
                'failed' => $failed,
                'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
                'avg_duration_ms' => round($typeStats->avg('avg_duration') ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Получить последние ошибки
     */
    public function getRecentErrors(int $limit = 20): Collection
    {
        return IntegrationLog::whereIn('status', [
            IntegrationStatus::Error,
            IntegrationStatus::Timeout,
        ])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить здоровье всех интеграций
     */
    public function getHealthStatus(): array
    {
        $stats = $this->getStats(1); // за последний час

        $result = [];

        foreach ($stats as $key => $stat) {
            $healthy = true;
            $message = 'Работает нормально';

            if ($stat['total_requests'] === 0) {
                $healthy = null; // unknown
                $message = 'Нет запросов за последний час';
            } elseif ($stat['success_rate'] < 50) {
                $healthy = false;
                $message = 'Критическое количество ошибок';
            } elseif ($stat['success_rate'] < 90) {
                $healthy = false;
                $message = 'Повышенное количество ошибок';
            } elseif ($stat['avg_duration_ms'] > 5000) {
                $healthy = false;
                $message = 'Высокая задержка ответа';
            }

            $result[$key] = [
                ...$stat,
                'healthy' => $healthy,
                'message' => $message,
            ];
        }

        return $result;
    }

    /**
     * Получить историю запросов по интеграции
     */
    public function getHistory(
        IntegrationType $type,
        int $hours = 24,
        int $limit = 100
    ): Collection {
        return IntegrationLog::byIntegration($type)
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить график запросов по часам
     */
    public function getHourlyChart(IntegrationType $type, int $hours = 24): array
    {
        $since = now()->subHours($hours);

        // SQLite-совместимый запрос
        $data = IntegrationLog::byIntegration($type)
            ->where('created_at', '>=', $since)
            ->selectRaw("strftime('%Y-%m-%d %H:00:00', created_at) as hour, status, COUNT(*) as count")
            ->groupBy('hour', 'status')
            ->orderBy('hour')
            ->get();

        $chart = [];
        $current = $since->copy()->startOfHour();
        $end = now();

        while ($current <= $end) {
            $hourKey = $current->format('Y-m-d H:00:00');

            $hourData = $data->where('hour', $hourKey);

            $chart[] = [
                'hour' => $current->format('H:i'),
                'date' => $current->format('d.m'),
                'success' => $hourData->where('status', IntegrationStatus::Success->value)->sum('count'),
                'error' => $hourData->whereIn('status', [
                    IntegrationStatus::Error->value,
                    IntegrationStatus::Timeout->value,
                ])->sum('count'),
            ];

            $current->addHour();
        }

        return $chart;
    }

    /**
     * Очистить старые логи
     */
    public function cleanup(int $retentionDays = 30): int
    {
        $threshold = now()->subDays($retentionDays);

        return IntegrationLog::where('created_at', '<', $threshold)->delete();
    }
}
