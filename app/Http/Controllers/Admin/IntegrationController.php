<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IntegrationType;
use App\Http\Controllers\Controller;
use App\Services\AiGatewayService;
use App\Services\IntegrationLogger;
use App\Services\Integrations\Kpi\KpiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function __construct(
        private readonly IntegrationLogger $logger,
        private readonly KpiClient $kpiClient,
        private readonly AiGatewayService $aiGateway
    ) {}

    /**
     * Страница статусов интеграций
     */
    public function index(Request $request): View
    {
        $healthStatus = $this->logger->getHealthStatus();
        $stats = $this->logger->getStats(24);
        $recentErrors = $this->logger->getRecentErrors(10);

        // Конфигурация интеграций (без секретов)
        $integrationConfigs = [
            'kpi' => [
                'enabled' => config('integrations.kpi.enabled'),
                'base_url' => config('integrations.kpi.base_url'),
                'timeout' => config('integrations.kpi.timeout'),
                'mock_enabled' => config('integrations.mock.enabled'),
            ],
            'pulse' => [
                'enabled' => config('integrations.pulse.enabled'),
                'base_url' => config('integrations.pulse.base_url'),
            ],
            'smart_office' => [
                'enabled' => config('integrations.smart_office.enabled'),
                'base_url' => config('integrations.smart_office.base_url'),
            ],
            'iabs' => [
                'enabled' => config('integrations.iabs.enabled'),
                'base_url' => config('integrations.iabs.base_url'),
            ],
            'ai_server' => [
                'enabled' => true,
                'base_url' => config('ai.url'),
                'timeout' => config('ai.timeout'),
            ],
        ];

        return view('admin.integrations.index', [
            'healthStatus' => $healthStatus,
            'stats' => $stats,
            'recentErrors' => $recentErrors,
            'integrationConfigs' => $integrationConfigs,
        ]);
    }

    /**
     * Тестирование интеграции
     */
    public function test(Request $request, string $type): JsonResponse
    {
        $integrationType = IntegrationType::tryFrom($type);

        if (!$integrationType) {
            return response()->json(['error' => 'Unknown integration type'], 400);
        }

        $result = match ($integrationType) {
            IntegrationType::Kpi => $this->kpiClient->healthCheck(),
            IntegrationType::AiServer => $this->aiGateway->healthCheck(),
            default => ['healthy' => null, 'message' => 'Test not implemented'],
        };

        return response()->json($result);
    }

    /**
     * История запросов интеграции
     */
    public function history(Request $request, string $type): View
    {
        $integrationType = IntegrationType::tryFrom($type);

        if (!$integrationType) {
            abort(404);
        }

        $hours = (int) $request->query('hours', 24);

        $history = $this->logger->getHistory($integrationType, $hours, 100);
        $chart = $this->logger->getHourlyChart($integrationType, $hours);

        return view('admin.integrations.history', [
            'type' => $integrationType,
            'history' => $history,
            'chart' => $chart,
            'hours' => $hours,
        ]);
    }

    /**
     * Очистить старые логи
     */
    public function cleanup(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 30);

        $deleted = $this->logger->cleanup($days);

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }
}
