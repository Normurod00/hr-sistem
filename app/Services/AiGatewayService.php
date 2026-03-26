<?php

namespace App\Services;

use App\Enums\AiContextType;
use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\AuditLog;
use App\Models\EmployeeAiConversation;
use App\Models\EmployeeAiMessage;
use App\Models\EmployeeProfile;
use App\Models\IntegrationLog;
use App\Services\Employee\PolicySearchService;
use App\Services\Integrations\Kpi\KpiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gateway сервис для AI
 *
 * Единственная точка взаимодействия Laravel с FastAPI AI сервером.
 * Обогащает запросы контекстом (KPI, политики) и сохраняет историю.
 */
class AiGatewayService
{
    private string $aiUrl;
    private int $timeout;

    public function __construct(
        private readonly KpiClient $kpiClient,
        private readonly PolicySearchService $policyService
    ) {
        $this->aiUrl = rtrim(config('ai.url', 'http://127.0.0.1:8095'), '/');
        $this->timeout = config('ai.timeout', 120);
    }

    /**
     * Отправить сообщение в чат
     */
    public function chat(
        EmployeeProfile $employee,
        EmployeeAiConversation $conversation,
        string $message
    ): array {
        $startTime = microtime(true);

        // Сохраняем сообщение пользователя
        $userMessage = $conversation->addMessage('user', $message);

        // Определяем intent
        $intent = $this->detectIntent($message);

        // Собираем контекст
        $context = $this->buildContext($employee, $conversation, $message, $intent);

        // Формируем запрос к AI
        $payload = [
            'context' => [
                'type' => 'employee',
                'employee_id' => $employee->employee_number,
                'department' => $employee->department,
                'position' => $employee->position,
                'conversation_type' => $conversation->context_type->value,
            ],
            'message' => $message,
            'intent' => $intent,
            'history' => $conversation->getMessagesForAi(10),
            'facts' => $context['facts'] ?? [],
            'policies' => $context['policies'] ?? [],
        ];

        // Логируем запрос
        $log = IntegrationLog::logRequest(
            IntegrationType::AiServer,
            'employee_chat',
            ['intent' => $intent, 'message_length' => strlen($message)],
            auth()->id()
        );

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->aiUrl}/ai/chat", $payload);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                $log->markError("HTTP {$response->status()}", $durationMs);

                return $this->handleError($conversation, 'AI сервис временно недоступен');
            }

            $data = $response->json();
            $log->markSuccess(['response_length' => strlen($data['response'] ?? '')], $durationMs);

            // Сохраняем ответ AI
            $aiMessage = $conversation->addMessage(
                'assistant',
                $data['response'] ?? 'Извините, не удалось обработать запрос',
                $intent,
                [
                    'confidence' => $data['confidence'] ?? null,
                    'sources' => $data['sources'] ?? [],
                    'tokens_used' => $data['tokens_used'] ?? null,
                ]
            );

            // Обновляем intent сообщения пользователя
            $userMessage->update(['intent' => $intent]);

            // Логируем аудит
            AuditLog::logAiQuery($message, $data);

            return [
                'success' => true,
                'message' => $aiMessage,
                'response' => $data['response'],
                'intent' => $intent,
                'confidence' => $data['confidence'] ?? null,
                'sources' => $data['sources'] ?? [],
            ];
        } catch (ConnectionException $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markTimeout($durationMs);

            Log::error('AI Gateway timeout', ['error' => $e->getMessage()]);

            return $this->handleError($conversation, 'Превышено время ожидания ответа');
        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            Log::error('AI Gateway error', ['error' => $e->getMessage()]);

            return $this->handleError($conversation, 'Произошла ошибка при обработке запроса');
        }
    }

    /**
     * Получить объяснение KPI
     */
    public function explainKpi(EmployeeProfile $employee, array $kpiData): array
    {
        $startTime = microtime(true);

        $payload = [
            'context' => [
                'type' => 'employee',
                'operation' => 'kpi_explain',
            ],
            'kpi_data' => $kpiData,
            'employee' => [
                'department' => $employee->department,
                'position' => $employee->position,
            ],
        ];

        $log = IntegrationLog::logRequest(
            IntegrationType::AiServer,
            'kpi_explain',
            ['metrics_count' => count($kpiData['metrics'] ?? [])],
            auth()->id()
        );

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->aiUrl}/ai/explain", $payload);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                $log->markError("HTTP {$response->status()}", $durationMs);
                return ['success' => false, 'error' => 'AI сервис недоступен'];
            }

            $data = $response->json();
            $log->markSuccess($data, $durationMs);

            return [
                'success' => true,
                'explanation' => $data['explanation'] ?? '',
                'metric_explanations' => $data['metric_explanations'] ?? [],
                'improvement_suggestions' => $data['improvement_suggestions'] ?? [],
                'risk_assessment' => $data['risk_assessment'] ?? null,
            ];
        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Получить рекомендации по улучшению KPI
     */
    public function getRecommendations(EmployeeProfile $employee, array $kpiData): array
    {
        $startTime = microtime(true);

        $payload = [
            'context' => [
                'type' => 'employee',
                'operation' => 'recommendations',
            ],
            'kpi_data' => $kpiData,
            'employee' => [
                'department' => $employee->department,
                'position' => $employee->position,
                'tenure_months' => $employee->hire_date?->diffInMonths(now()) ?? 0,
            ],
        ];

        $log = IntegrationLog::logRequest(
            IntegrationType::AiServer,
            'get_recommendations',
            ['total_score' => $kpiData['total_score'] ?? 0],
            auth()->id()
        );

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->aiUrl}/ai/analyze", $payload);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                $log->markError("HTTP {$response->status()}", $durationMs);
                return ['success' => false, 'error' => 'AI сервис недоступен'];
            }

            $data = $response->json();
            $log->markSuccess(['recommendations_count' => count($data['recommendations'] ?? [])], $durationMs);

            return [
                'success' => true,
                'recommendations' => $data['recommendations'] ?? [],
                'priority_actions' => $data['priority_actions'] ?? [],
                'expected_improvement' => $data['expected_improvement'] ?? null,
            ];
        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Проверить здоровье AI сервера
     */
    public function healthCheck(): array
    {
        $start = microtime(true);

        try {
            $response = Http::timeout(5)->get("{$this->aiUrl}/health");
            $latency = (int) ((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                return [
                    'healthy' => true,
                    'message' => 'AI Server is operational',
                    'latency_ms' => $latency,
                    'version' => $response->json()['version'] ?? 'unknown',
                ];
            }

            return [
                'healthy' => false,
                'message' => "HTTP {$response->status()}",
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

    // ===== PRIVATE METHODS =====

    /**
     * Определить intent сообщения
     */
    private function detectIntent(string $message): string
    {
        $message = mb_strtolower($message);

        // Паттерны для определения намерения (русский + узбекский)
        $patterns = [
            // Приветствия (высокий приоритет)
            'greeting' => ['привет', 'здравствуй', 'добрый день', 'добрый вечер', 'салом', 'ассалому', 'hello', 'hi'],
            'help' => ['помощь', 'помоги', 'что умеешь', 'что можешь', 'ёрдам', 'yordam', 'help'],

            // Отпуска
            'leave_balance' => ['остаток отпуск', 'сколько дней', 'дней отпуска', 'таътил қолди', 'неча кун таътил'],
            'leave_request' => ['отпуск', 'отгул', 'выходн', 'больничн', 'отсутств', 'таътил'],

            // KPI
            'kpi_explain' => ['почему kpi', 'почему низк', 'объясни kpi', 'разъясни', 'нега kpi паст'],
            'kpi_question' => ['kpi', 'кпи', 'показател', 'эффективност', 'результат', 'самарадорлик'],

            // Финансы
            'bonus_inquiry' => ['бонус', 'премия', 'премии', 'мукофот', 'bonus'],
            'salary_question' => ['зарплат', 'оклад', 'маош', 'ойлик', 'salary'],

            // Дисциплина
            'discipline_question' => ['дисциплин', 'выговор', 'взыскан', 'штраф', 'нарушен', 'интизом', 'жарима', 'огоҳлантириш'],

            // Признание
            'recognition_question' => ['признан', 'награ', 'достижен', 'благодарн', 'поощрен', 'эътироф', 'ютуқ'],

            // Обучение
            'training_question' => ['обучен', 'курс', 'тренинг', 'сертификат', 'экзамен', 'ўқиш'],

            // График
            'schedule_question' => ['график работ', 'расписан', 'смен', 'рабоч врем', 'иш вақт', 'жадвал'],

            // Льготы
            'benefits_question' => ['льгот', 'соцпакет', 'медицин страхов', 'дмс', 'корпоратив', 'имтиёз'],

            // Политики
            'policy_search' => ['политик', 'регламент', 'правил', 'порядок', 'процедур', 'сиёсат', 'қоида'],
        ];

        // Приоритетный порядок проверки
        $priorityOrder = [
            'greeting', 'help',
            'leave_balance', 'leave_request',
            'kpi_explain', 'kpi_question',
            'discipline_question', 'recognition_question',
            'bonus_inquiry', 'salary_question',
            'training_question', 'schedule_question', 'benefits_question',
            'policy_search',
        ];

        foreach ($priorityOrder as $intent) {
            if (!isset($patterns[$intent])) continue;

            foreach ($patterns[$intent] as $keyword) {
                if (mb_strpos($message, $keyword) !== false) {
                    return $intent;
                }
            }
        }

        return 'general';
    }

    /**
     * Собрать контекст для AI
     */
    private function buildContext(
        EmployeeProfile $employee,
        EmployeeAiConversation $conversation,
        string $message,
        string $intent
    ): array {
        $context = [
            'facts' => [],
            'policies' => [],
        ];

        // Добавляем KPI факты для релевантных intents
        if (in_array($intent, ['kpi_question', 'kpi_explain', 'bonus_inquiry', 'salary_question'])) {
            $kpiSnapshot = $this->kpiClient->getOrFetchSnapshot($employee, 'month');

            if ($kpiSnapshot) {
                $context['facts']['current_kpi'] = [
                    'period' => $kpiSnapshot->period_label,
                    'total_score' => $kpiSnapshot->total_score,
                    'metrics' => $kpiSnapshot->metrics,
                    'bonus_eligible' => $kpiSnapshot->isBonusEligible(),
                ];
            }

            // Тренд
            $trend = $this->kpiClient->getKpiTrend($employee, 3);
            if (!empty($trend)) {
                $context['facts']['kpi_trend'] = $trend;
            }
        }

        // Добавляем данные о дисциплине
        if ($intent === 'discipline_question') {
            $disciplineActions = $employee->disciplinaryActions()
                ->active()
                ->limit(5)
                ->get();

            $context['facts']['discipline'] = [
                'active_count' => $disciplineActions->count(),
                'actions' => $disciplineActions->map(fn($a) => [
                    'type' => $a->type_label,
                    'status' => $a->status_label,
                    'date' => $a->action_date->format('d.m.Y'),
                ])->toArray(),
            ];
        }

        // Добавляем данные о признании
        if ($intent === 'recognition_question') {
            $context['facts']['recognition'] = [
                'total_points' => $employee->recognition_points ?? 0,
                // Можно добавить историю наград
            ];
        }

        // Добавляем политики для релевантных intents
        $policyIntents = [
            'leave_request', 'leave_balance', 'policy_search', 'general',
            'discipline_question', 'training_question', 'benefits_question',
        ];

        if (in_array($intent, $policyIntents)) {
            $policies = $this->policyService->getPolicyContextForAi($message);
            $context['policies'] = $policies;
        }

        return $context;
    }

    /**
     * Обработать ошибку
     */
    private function handleError(EmployeeAiConversation $conversation, string $errorMessage): array
    {
        $message = $conversation->addMessage(
            'assistant',
            "Извините, произошла ошибка: {$errorMessage}. Пожалуйста, попробуйте позже или обратитесь в HR отдел.",
            'error',
            ['error' => true]
        );

        return [
            'success' => false,
            'message' => $message,
            'response' => $message->content,
            'error' => $errorMessage,
        ];
    }
}
