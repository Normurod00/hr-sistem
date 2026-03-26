<?php

namespace App\Services\Integrations\Kpi;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\EmployeeProfile;
use App\Models\IntegrationLog;
use App\Services\Integrations\Contracts\KpiProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP провайдер KPI для реального API
 *
 * ВАЖНО: Это ПЛЕЙСХОЛДЕР.
 * Когда будет готов реальный KPI API:
 * 1. Заполните .env переменные (KPI_API_BASE_URL, KPI_API_TOKEN)
 * 2. Раскомментируйте и адаптируйте методы ниже
 * 3. Проверьте формат ответа API и приведите к нужной структуре
 */
class HttpKpiProvider implements KpiProviderInterface
{
    private string $baseUrl;
    private string $token;
    private int $timeout;
    private array $retryConfig;
    private array $headers;
    private bool $cacheEnabled;
    private int $cacheTtl;

    public function __construct()
    {
        $config = config('integrations.kpi');

        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->token = $config['token'];
        $this->timeout = $config['timeout'];
        $this->retryConfig = $config['retry'];
        $this->headers = array_merge($config['headers'], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);
        $this->cacheEnabled = $config['cache']['enabled'];
        $this->cacheTtl = $config['cache']['ttl'];
    }

    public function getEmployeeKpi(
        EmployeeProfile $employee,
        string $periodType,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): ?array {
        $cacheKey = "kpi:{$employee->employee_number}:{$periodType}:" .
            ($startDate?->format('Y-m-d') ?? 'current');

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $endpoint = str_replace(
            '{employee_id}',
            $employee->employee_number,
            config('integrations.kpi.endpoints.employee_kpi')
        );

        $params = [
            'period_type' => $periodType,
        ];

        if ($startDate) {
            $params['start_date'] = $startDate->format('Y-m-d');
        }
        if ($endDate) {
            $params['end_date'] = $endDate->format('Y-m-d');
        }

        $result = $this->makeRequest('GET', $endpoint, $params, 'get_employee_kpi');

        if ($result && $this->cacheEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    public function getEmployeeKpiHistory(EmployeeProfile $employee, int $months = 12): Collection
    {
        $endpoint = str_replace(
            '{employee_id}',
            $employee->employee_number,
            config('integrations.kpi.endpoints.employee_kpi')
        ) . '/history';

        $result = $this->makeRequest('GET', $endpoint, [
            'months' => $months,
        ], 'get_employee_kpi_history');

        if (!$result || !isset($result['history'])) {
            return collect();
        }

        return collect($result['history']);
    }

    public function getDepartmentKpi(string $department, string $periodType): ?array
    {
        $endpoint = str_replace(
            '{department_id}',
            $department,
            config('integrations.kpi.endpoints.department_kpi')
        );

        return $this->makeRequest('GET', $endpoint, [
            'period_type' => $periodType,
        ], 'get_department_kpi');
    }

    public function getAvailablePeriods(): array
    {
        $cacheKey = 'kpi:periods';

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->makeRequest(
            'GET',
            config('integrations.kpi.endpoints.periods'),
            [],
            'get_periods'
        );

        $periods = $result['periods'] ?? [];

        if ($this->cacheEnabled && !empty($periods)) {
            Cache::put($cacheKey, $periods, $this->cacheTtl * 24); // дольше кэшируем справочники
        }

        return $periods;
    }

    public function getMetricsDefinitions(): array
    {
        $cacheKey = 'kpi:metrics_definitions';

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->makeRequest(
            'GET',
            config('integrations.kpi.endpoints.metrics'),
            [],
            'get_metrics'
        );

        $metrics = $result['metrics'] ?? [];

        if ($this->cacheEnabled && !empty($metrics)) {
            Cache::put($cacheKey, $metrics, $this->cacheTtl * 24);
        }

        return $metrics;
    }

    public function healthCheck(): array
    {
        $start = microtime(true);

        try {
            $response = Http::timeout(5)
                ->withHeaders($this->headers)
                ->get($this->baseUrl . '/health');

            $latency = (int) ((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                return [
                    'healthy' => true,
                    'message' => 'KPI API is operational',
                    'latency_ms' => $latency,
                ];
            }

            return [
                'healthy' => false,
                'message' => 'KPI API returned status ' . $response->status(),
                'latency_ms' => $latency,
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => $e->getMessage(),
                'latency_ms' => null,
            ];
        }
    }

    public function syncEmployeeKpi(EmployeeProfile $employee): bool
    {
        // Очищаем кэш для сотрудника
        $pattern = "kpi:{$employee->employee_number}:*";
        // В Redis можно использовать Cache::tags или паттерн
        // Для file/database драйвера просто пропускаем

        // Запрашиваем свежие данные
        $kpi = $this->getEmployeeKpi($employee, 'month');

        return $kpi !== null;
    }

    // ===== PRIVATE METHODS =====

    /**
     * Выполнить HTTP запрос с retry и логированием
     */
    private function makeRequest(
        string $method,
        string $endpoint,
        array $params,
        string $operation
    ): ?array {
        $url = $this->baseUrl . $endpoint;
        $startTime = microtime(true);

        $log = IntegrationLog::logRequest(
            IntegrationType::Kpi,
            $operation,
            ['endpoint' => $endpoint, 'params' => $params],
            auth()->id()
        );

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers)
                ->retry(
                    $this->retryConfig['times'],
                    $this->retryConfig['sleep'],
                    function ($exception, $request) {
                        // Retry только на временных ошибках
                        return $exception instanceof ConnectionException
                            || ($exception instanceof RequestException
                                && $exception->response->status() >= 500);
                    }
                )
                ->{strtolower($method)}($url, $params);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $log->markSuccess($data, $durationMs);
                return $data;
            }

            $log->markError(
                "HTTP {$response->status()}: " . $response->body(),
                $durationMs
            );

            Log::warning("KPI API error", [
                'operation' => $operation,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (ConnectionException $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markTimeout($durationMs);

            Log::error("KPI API timeout", [
                'operation' => $operation,
                'error' => $e->getMessage(),
            ]);

            return null;
        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            Log::error("KPI API exception", [
                'operation' => $operation,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
