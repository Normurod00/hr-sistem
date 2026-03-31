<?php

namespace App\Http\Controllers\Api;

use App\Enums\AiContextType;
use App\Http\Controllers\Controller;
use App\Models\EmployeeAiConversation;
use App\Services\AiGatewayService;
use App\Services\Employee\EmployeeKpiService;
use App\Services\InputSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Gateway для AI
 *
 * Универсальный контроллер для всех AI запросов (кандидаты и сотрудники)
 */
class AiGatewayController extends Controller
{
    public function __construct(
        private readonly AiGatewayService $aiGateway,
        private readonly EmployeeKpiService $kpiService
    ) {}

    /**
     * Универсальный чат с AI
     *
     * POST /api/ai/chat
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'context_type' => 'required|string|in:candidate,employee',
            'message' => 'required|string|min:1|max:4000',
            'conversation_id' => 'nullable|integer',
            'employee_context' => 'nullable|string|in:' . implode(',', AiContextType::values()),
        ]);

        $validated['message'] = InputSanitizer::sanitizeMessage($validated['message']);

        if (empty($validated['message'])) {
            return response()->json(['success' => false, 'error' => 'Сообщение не может быть пустым'], 422);
        }

        // Определяем тип контекста
        if ($validated['context_type'] === 'employee') {
            return $this->handleEmployeeChat($request, $validated);
        }

        // Для кандидатов — используем существующий AiClient
        return $this->handleCandidateChat($request, $validated);
    }

    /**
     * KPI Explain
     *
     * POST /api/ai/explain
     */
    public function explain(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'snapshot_id' => 'required|integer|exists:employee_kpi_snapshots,id',
        ]);

        $user = auth()->user();

        if (!$user->isEmployee()) {
            return response()->json(['error' => 'Only employees can use this endpoint'], 403);
        }

        $employee = $user->employeeProfile;
        $snapshot = \App\Models\EmployeeKpiSnapshot::findOrFail($validated['snapshot_id']);

        // Проверяем доступ
        if ($snapshot->employee_profile_id !== $employee->id) {
            if (!$employee->canViewEmployee($snapshot->employeeProfile)) {
                return response()->json(['error' => 'Access denied'], 403);
            }
        }

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
     * Универсальный анализ
     *
     * POST /api/ai/analyze
     */
    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'context_type' => 'required|string|in:candidate,employee',
            'operation' => 'required|string|in:kpi_recommendations,resume_analysis,match_score',
            'data' => 'required|array',
        ]);

        if ($validated['context_type'] === 'employee') {
            return $this->handleEmployeeAnalysis($request, $validated);
        }

        // Для кандидатов — перенаправляем к существующему сервису
        return $this->handleCandidateAnalysis($request, $validated);
    }

    /**
     * Health check
     */
    public function health(): JsonResponse
    {
        $health = $this->aiGateway->healthCheck();

        return response()->json($health, $health['healthy'] ? 200 : 503);
    }

    // ===== PRIVATE METHODS =====

    private function handleEmployeeChat(Request $request, array $validated): JsonResponse
    {
        $user = auth()->user();

        if (!$user->isEmployee()) {
            return response()->json(['error' => 'Only employees can use this endpoint'], 403);
        }

        $employee = $user->employeeProfile;

        // Находим или создаём разговор
        $conversation = null;

        if (!empty($validated['conversation_id'])) {
            $conversation = EmployeeAiConversation::find($validated['conversation_id']);

            if ($conversation && $conversation->employee_profile_id !== $employee->id) {
                return response()->json(['error' => 'Access denied'], 403);
            }
        }

        if (!$conversation) {
            $conversation = $employee->aiConversations()->create([
                'context_type' => $validated['employee_context'] ?? AiContextType::General->value,
                'status' => 'active',
            ]);
        }

        $result = $this->aiGateway->chat($employee, $conversation, $validated['message']);

        return response()->json([
            'success' => $result['success'],
            'conversation_id' => $conversation->id,
            'response' => $result['response'] ?? null,
            'intent' => $result['intent'] ?? null,
            'message_id' => $result['message']?->id ?? null,
        ]);
    }

    private function handleCandidateChat(Request $request, array $validated): JsonResponse
    {
        // Кандидатский AI-чат не поддерживается через этот endpoint.
        // Для кандидатов используются /parse-resume, /analyze, /match-score напрямую.
        return response()->json([
            'success' => false,
            'error' => 'Чат для кандидатов недоступен. Используйте специализированные эндпоинты для анализа резюме.',
        ], 400);
    }

    private function handleEmployeeAnalysis(Request $request, array $validated): JsonResponse
    {
        $user = auth()->user();

        if (!$user->isEmployee()) {
            return response()->json(['error' => 'Only employees can use this endpoint'], 403);
        }

        $employee = $user->employeeProfile;

        if ($validated['operation'] === 'kpi_recommendations') {
            $result = $this->aiGateway->getRecommendations($employee, $validated['data']);
            return response()->json($result);
        }

        return response()->json(['error' => 'Unknown operation'], 400);
    }

    private function handleCandidateAnalysis(Request $request, array $validated): JsonResponse
    {
        $aiClient = app(\App\Services\AiClient::class);

        $data = $validated['data'];

        if ($validated['operation'] === 'resume_analysis') {
            $result = $aiClient->analyzeCandidate(
                $data['profile'] ?? [],
                $data['vacancy'] ?? [],
                $data['application_id'] ?? null
            );
            return response()->json($result);
        }

        if ($validated['operation'] === 'match_score') {
            $result = $aiClient->calculateMatchScore(
                $data['profile'] ?? [],
                $data['vacancy'] ?? [],
                $data['application_id'] ?? null
            );
            return response()->json($result);
        }

        return response()->json(['error' => 'Unknown operation'], 400);
    }
}
