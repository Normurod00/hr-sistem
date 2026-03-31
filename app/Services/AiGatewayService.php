<?php

namespace App\Services;

use App\Enums\IntegrationType;
use App\Models\AuditLog;
use App\Models\EmployeeAiConversation;
use App\Models\EmployeeProfile;
use App\Models\IntegrationLog;
use App\Services\Employee\PolicySearchService;
use App\Services\Integrations\Kpi\KpiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiGatewayService
{
    private string $aiUrl;
    private int $timeout;

    public function __construct(
        private readonly KpiClient $kpiClient,
        private readonly PolicySearchService $policyService
    ) {
        $url = config('ai.url') ?: config('ai.server.url');

        if (empty($url)) {
            Log::error('AiGatewayService: HR_AI_URL не настроен в .env');
            $url = 'http://127.0.0.1:8095';
        }

        $this->aiUrl = rtrim($url, '/');
        $this->timeout = (int) config('ai.timeout', config('ai.server.timeout', 120));
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

        $userMessage = $conversation->addMessage('user', $message);
        $intent = $this->detectIntent($message);
        $context = $this->buildContext($employee, $conversation, $message, $intent);

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

        $log = IntegrationLog::logRequest(
            IntegrationType::AiServer,
            'employee_chat',
            ['intent' => $intent, 'message_length' => mb_strlen($message)],
            auth()->id()
        );

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->aiUrl}/ai/chat", $payload);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                $errorBody = $this->decodeResponseBody($response->body());

                $log->markError("HTTP {$response->status()}", $durationMs);

                Log::error('AI chat HTTP error', [
                    'status' => $response->status(),
                    'body' => $errorBody,
                    'payload' => $payload,
                ]);

                return $this->handleError(
                    $conversation,
                    $this->resolveAiErrorMessage($response->status(), 'chat')
                );
            }

            $data = $response->json();

            $log->markSuccess(
                ['response_length' => mb_strlen((string) ($data['response'] ?? ''))],
                $durationMs
            );

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

            $userMessage->update(['intent' => $intent]);

            AuditLog::logAiQuery($message, $data);

            return [
                'success' => true,
                'message' => $aiMessage,
                'response' => $data['response'] ?? '',
                'intent' => $intent,
                'confidence' => $data['confidence'] ?? null,
                'sources' => $data['sources'] ?? [],
            ];
        } catch (ConnectionException $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markTimeout($durationMs);

            Log::error('AI Gateway timeout', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return $this->handleError($conversation, 'Превышено время ожидания ответа');
        } catch (\Throwable $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            Log::error('AI Gateway error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return $this->handleError($conversation, 'Произошла ошибка при обработке запроса');
        }
    }

    /**
     * Получить объяснение KPI
     */
    public function explainKpi(EmployeeProfile $employee, array $kpiData): array
    {
        $startTime = microtime(true);
        $normalizedKpiData = $this->normalizeKpiDataForAi($kpiData);

        $payload = [
            'context' => [
                'type' => 'employee',
                'operation' => 'kpi_explain',
            ],
            'kpi_data' => $normalizedKpiData,
            'employee' => [
                'department' => $employee->department,
                'position' => $employee->position,
            ],
        ];

        $log = IntegrationLog::logRequest(
            IntegrationType::AiServer,
            'kpi_explain',
            ['metrics_count' => count($normalizedKpiData['metrics'] ?? [])],
            auth()->id()
        );

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->aiUrl}/ai/explain", $payload);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                $errorBody = $this->decodeResponseBody($response->body());

                $log->markError("HTTP {$response->status()}", $durationMs);

                Log::error('AI explain HTTP error', [
                    'status' => $response->status(),
                    'body' => $errorBody,
                    'payload' => $payload,
                ]);

                return [
                    'success' => false,
                    'error' => $this->resolveAiErrorMessage($response->status(), 'explain'),
                ];
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
        } catch (ConnectionException $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markTimeout($durationMs);

            Log::error('AI explain timeout', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error' => 'Превышено время ожидания ответа AI',
            ];
        } catch (\Throwable $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            Log::error('AI explain error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Получить рекомендации по улучшению KPI
     */
    public function getRecommendations(EmployeeProfile $employee, array $kpiData): array
    {
        $startTime = microtime(true);
        $normalizedKpiData = $this->normalizeKpiDataForAi($kpiData);

        $payload = [
            'context' => [
                'type' => 'employee',
                'operation' => 'recommendations',
            ],
            'kpi_data' => $normalizedKpiData,
            'employee' => [
                'department' => $employee->department,
                'position' => $employee->position,
                'tenure_months' => $employee->hire_date?->diffInMonths(now()) ?? 0,
            ],
        ];

        $log = IntegrationLog::logRequest(
            IntegrationType::AiServer,
            'get_recommendations',
            ['total_score' => $normalizedKpiData['total_score'] ?? 0],
            auth()->id()
        );

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->aiUrl}/ai/analyze", $payload);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                $errorBody = $this->decodeResponseBody($response->body());

                $log->markError("HTTP {$response->status()}", $durationMs);

                Log::error('AI analyze HTTP error', [
                    'status' => $response->status(),
                    'body' => $errorBody,
                    'payload' => $payload,
                ]);

                return [
                    'success' => false,
                    'error' => $this->resolveAiErrorMessage($response->status(), 'analyze'),
                ];
            }

            $data = $response->json();
            $log->markSuccess(
                ['recommendations_count' => count($data['recommendations'] ?? [])],
                $durationMs
            );

            return [
                'success' => true,
                'recommendations' => $data['recommendations'] ?? [],
                'priority_actions' => $data['priority_actions'] ?? [],
                'expected_improvement' => $data['expected_improvement'] ?? null,
            ];
        } catch (ConnectionException $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markTimeout($durationMs);

            Log::error('AI analyze timeout', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error' => 'Превышено время ожидания ответа AI',
            ];
        } catch (\Throwable $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            Log::error('AI analyze error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
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
        } catch (\Throwable $e) {
            return [
                'healthy' => false,
                'message' => $e->getMessage(),
                'latency_ms' => null,
            ];
        }
    }

    // ===== PRIVATE METHODS =====

    private function detectIntent(string $message): string
    {
        $message = mb_strtolower($message);

        $patterns = [
            'greeting' => ['привет', 'здравствуй', 'добрый день', 'добрый вечер', 'салом', 'ассалому', 'hello', 'hi'],
            'help' => ['помощь', 'помоги', 'что умеешь', 'что можешь', 'ёрдам', 'yordam', 'help'],

            'leave_balance' => ['остаток отпуск', 'сколько дней', 'дней отпуска', 'таътил қолди', 'неча кун таътил'],
            'leave_request' => ['отпуск', 'отгул', 'выходн', 'больничн', 'отсутств', 'таътил'],

            'kpi_explain' => ['почему kpi', 'почему низк', 'объясни kpi', 'разъясни', 'нега kpi паст'],
            'kpi_question' => ['kpi', 'кпи', 'показател', 'эффективност', 'результат', 'самарадорлик'],

            'bonus_inquiry' => ['бонус', 'премия', 'премии', 'мукофот', 'bonus'],
            'salary_question' => ['зарплат', 'оклад', 'маош', 'ойлик', 'salary'],

            'discipline_question' => ['дисциплин', 'выговор', 'взыскан', 'штраф', 'нарушен', 'интизом', 'жарима', 'огоҳлантириш'],
            'recognition_question' => ['признан', 'награ', 'достижен', 'благодарн', 'поощрен', 'эътироф', 'ютуқ'],

            'training_question' => ['обучен', 'курс', 'тренинг', 'сертификат', 'экзамен', 'ўқиш'],
            'schedule_question' => ['график работ', 'расписан', 'смен', 'рабоч врем', 'иш вақт', 'жадвал'],
            'benefits_question' => ['льгот', 'соцпакет', 'медицин страхов', 'дмс', 'корпоратив', 'имтиёз'],

            'policy_search' => ['политик', 'регламент', 'правил', 'порядок', 'процедур', 'сиёсат', 'қоида'],
        ];

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
            if (!isset($patterns[$intent])) {
                continue;
            }

            foreach ($patterns[$intent] as $keyword) {
                if (mb_strpos($message, $keyword) !== false) {
                    return $intent;
                }
            }
        }

        return 'general';
    }

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

        if (in_array($intent, ['kpi_question', 'kpi_explain', 'bonus_inquiry', 'salary_question'], true)) {
            $kpiSnapshot = $this->kpiClient->getOrFetchSnapshot($employee, 'month');

            if ($kpiSnapshot) {
                $context['facts']['current_kpi'] = [
                    'period' => $kpiSnapshot->period_label,
                    'total_score' => $kpiSnapshot->total_score,
                    'metrics' => $kpiSnapshot->metrics,
                    'bonus_eligible' => $kpiSnapshot->isBonusEligible(),
                ];
            }

            $trend = $this->kpiClient->getKpiTrend($employee, 3);
            if (!empty($trend)) {
                $context['facts']['kpi_trend'] = $trend;
            }
        }

        if ($intent === 'discipline_question') {
            $disciplineActions = $employee->disciplinaryActions()
                ->active()
                ->limit(5)
                ->get();

            $context['facts']['discipline'] = [
                'active_count' => $disciplineActions->count(),
                'actions' => $disciplineActions->map(fn ($a) => [
                    'type' => $a->type_label,
                    'status' => $a->status_label,
                    'date' => $a->action_date->format('d.m.Y'),
                ])->toArray(),
            ];
        }

        if ($intent === 'recognition_question') {
            $context['facts']['recognition'] = [
                'total_points' => $employee->recognition_points ?? 0,
            ];
        }

        $policyIntents = [
            'leave_request', 'leave_balance', 'policy_search', 'general',
            'discipline_question', 'training_question', 'benefits_question',
        ];

        if (in_array($intent, $policyIntents, true)) {
            $context['policies'] = $this->policyService->getPolicyContextForAi($message);
        }

        return $context;
    }

    private function normalizeKpiDataForAi(array $kpiData): array
    {
        $metrics = collect($kpiData['metrics'] ?? [])
            ->mapWithKeys(function ($value, $key) {
                return [$key => $this->normalizeMetric((string) $key, $value)];
            })
            ->toArray();

        $lowMetricsInput = $kpiData['low_metrics'] ?? null;
        $lowMetrics = [];

        if (is_array($lowMetricsInput) && array_is_list($lowMetricsInput)) {
            foreach ($lowMetricsInput as $metricKey) {
                if (isset($metrics[$metricKey])) {
                    $lowMetrics[$metricKey] = $metrics[$metricKey];
                }
            }
        } elseif (is_array($lowMetricsInput)) {
            foreach ($lowMetricsInput as $metricKey => $metricValue) {
                $lowMetrics[$metricKey] = $this->normalizeMetric((string) $metricKey, $metricValue);
            }
        }

        if (empty($lowMetrics)) {
            foreach ($metrics as $metricKey => $metricData) {
                if (($metricData['completion'] ?? 100) < 70) {
                    $lowMetrics[$metricKey] = $metricData;
                }
            }
        }

        return [
            ...$kpiData,
            'metrics' => $metrics,
            'low_metrics' => $lowMetrics,
        ];
    }

    private function normalizeMetric(string $metricKey, mixed $metricValue): array
    {
        if (is_array($metricValue)) {
            $completion = $metricValue['completion']
                ?? $metricValue['score']
                ?? $metricValue['value']
                ?? $metricValue['current']
                ?? $metricValue['percent']
                ?? 0;

            return [
                ...$metricValue,
                'name' => $metricValue['name'] ?? $this->makeMetricName($metricKey),
                'completion' => (float) $completion,
            ];
        }

        return [
            'name' => $this->makeMetricName($metricKey),
            'completion' => is_numeric($metricValue) ? (float) $metricValue : 0.0,
        ];
    }

    private function makeMetricName(string $metricKey): string
    {
        return str($metricKey)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }

    private function resolveAiErrorMessage(int $status, string $operation): string
    {
        return match ($status) {
            400, 422 => "AI вернул ошибку валидации для {$operation}",
            500, 502, 503, 504 => 'AI сервис временно недоступен',
            default => 'Ошибка при обращении к AI',
        };
    }

    private function decodeResponseBody(string $body): mixed
    {
        $decoded = json_decode($body, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $body;
    }

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