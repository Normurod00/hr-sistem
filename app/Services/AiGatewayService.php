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
     * Чат сотрудника с AI
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
                'employee_id' => (string) ($employee->employee_number ?? ''),
                'department' => (string) ($employee->department ?? ''),
                'position' => (string) ($employee->position ?? ''),
                'conversation_type' => (string) $conversation->context_type->value,
            ],
            'message' => $message,
            'intent' => $intent,
            'history' => $this->normalizeHistoryForAi($conversation->getMessagesForAi(10)),
        ];

        if (!empty($context['facts']) && is_array($context['facts']) && !array_is_list($context['facts'])) {
            $payload['facts'] = $context['facts'];
        }

        if (!empty($context['policies'])) {
            $payload['policies'] = $context['policies'];
        }

        $log = IntegrationLog::logRequest(
            IntegrationType::AiServer,
            'employee_chat',
            [
                'intent' => $intent,
                'message_length' => mb_strlen($message),
            ],
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
                    $this->resolveAiErrorMessage($response->status())
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

            return $this->handleError($conversation, 'Превышено время ожидания ответа. Пожалуйста, попробуйте ещё раз.');
        } catch (\Throwable $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            Log::error('AI Gateway error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return $this->handleError($conversation, 'Произошла ошибка при обработке запроса. Пожалуйста, попробуйте позже.');
        }
    }

    /**
     * Объяснение KPI
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
                'department' => (string) ($employee->department ?? ''),
                'position' => (string) ($employee->position ?? ''),
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
                    'error' => $this->resolveAiErrorMessage($response->status()),
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
     * Рекомендации по улучшению KPI
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
                'department' => (string) ($employee->department ?? ''),
                'position' => (string) ($employee->position ?? ''),
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
                    'error' => $this->resolveAiErrorMessage($response->status()),
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
     * Health check AI
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

    // ========================
    // PRIVATE METHODS
    // ========================

    private function detectIntent(string $message): string
    {
        $message = mb_strtolower($message);

        $patterns = [
            'greeting' => ['привет', 'здравствуй', 'добрый день', 'добрый вечер', 'салом', 'ассалому', 'hello', 'hi'],
            'smalltalk' => ['как дела', 'как ты', 'как жизнь', 'как настроен', 'что нового', 'қандайсиз', 'яхшимисиз'],
            'capabilities' => ['что умеешь', 'что можешь', 'чем помо', 'возможности', 'нима қила олас', 'помощь', 'помоги', 'ёрдам', 'yordam', 'help'],

            'kpi_improve' => ['как увеличить', 'как улучшить', 'как повысить', 'как поднять', 'план улучшен', 'что делать с kpi', 'яхшилаш', 'кўтариш'],
            'kpi_explain' => ['почему kpi', 'почему низк', 'почему ниже', 'объясни kpi', 'разъясни', 'нега kpi паст', 'из-за чего kpi'],
            'kpi_score' => ['kpi', 'кпи', 'показател', 'эффективност', 'результат', 'самарадорлик'],

            'leave_balance' => ['остаток отпуск', 'сколько дней', 'дней отпуска', 'отпуск сколько остал', 'сколько остал', 'таътил қолди', 'неча кун таътил'],
            'leave_request' => ['отпуск', 'отгул', 'выходн', 'больничн', 'отсутств', 'таътил'],

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
            'greeting',
            'smalltalk',
            'capabilities',
            'kpi_improve',
            'kpi_explain',
            'kpi_score',
            'leave_balance',
            'leave_request',
            'discipline_question',
            'recognition_question',
            'bonus_inquiry',
            'salary_question',
            'training_question',
            'schedule_question',
            'benefits_question',
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

        if (in_array($intent, ['kpi_score', 'kpi_explain', 'kpi_improve', 'bonus_inquiry', 'salary_question'], true)) {
            $kpiSnapshot = $this->kpiClient->getOrFetchSnapshot($employee, 'month');

            if ($kpiSnapshot) {
                $context['facts']['current_kpi'] = [
                    'period' => (string) $kpiSnapshot->period_label,
                    'total_score' => (float) $kpiSnapshot->total_score,
                    'metrics' => $this->normalizeKpiMetricsForFacts((array) $kpiSnapshot->metrics),
                    'bonus_eligible' => (bool) $kpiSnapshot->isBonusEligible(),
                ];
            }

            $trend = $this->kpiClient->getKpiTrend($employee, 3);

            if (!empty($trend)) {
                $context['facts']['kpi_trend'] = collect($trend)
                    ->map(function ($item) {
                        return [
                            'period' => $item['period'] ?? null,
                            'score' => isset($item['score']) ? (float) $item['score'] : 0.0,
                            'label' => $item['label'] ?? null,
                        ];
                    })
                    ->values()
                    ->toArray();
            }
        }

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

        if ($intent === 'recognition_question') {
            $context['facts']['recognition'] = [
                'total_points' => $employee->recognition_points ?? 0,
            ];
        }

        $policyIntents = [
            'leave_request',
            'leave_balance',
            'policy_search',
            'general',
            'discipline_question',
            'training_question',
            'benefits_question',
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
            'total_score' => isset($kpiData['total_score']) ? (float) $kpiData['total_score'] : 0.0,
            'metrics' => $metrics,
            'low_metrics' => $lowMetrics,
        ];
    }

    private function normalizeKpiMetricsForFacts(array $metrics): array
    {
        return collect($metrics)
            ->mapWithKeys(function ($value, $key) {
                return [$key => $this->normalizeMetric((string) $key, $value)];
            })
            ->toArray();
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

            $result = [
                ...$metricValue,
                'name' => $metricValue['name'] ?? $this->makeMetricName($metricKey),
                'completion' => (float) $completion,
            ];

            // Ensure all numeric fields are floats (prevents str vs int errors in Python)
            foreach (['value', 'target', 'weight'] as $numField) {
                if (isset($result[$numField])) {
                    $result[$numField] = is_numeric($result[$numField]) ? (float) $result[$numField] : 0.0;
                }
            }

            return $result;
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

    private function resolveAiErrorMessage(int $status): string
    {
        return match ($status) {
            400, 422 => 'Не удалось обработать запрос. Попробуйте переформулировать вопрос.',
            429 => 'Слишком много запросов. Подождите немного и попробуйте снова.',
            500, 502, 503, 504 => 'AI сервис временно недоступен. Попробуйте через несколько минут.',
            default => 'Произошла ошибка при обращении к AI. Попробуйте позже.',
        };
    }

    private function decodeResponseBody(string $body): mixed
    {
        $decoded = json_decode($body, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $body;
    }

    private function normalizeHistoryForAi(array $history): array
    {
        return collect($history)
            ->map(function ($item) {
                $role = $item['role'] ?? 'user';

                if (is_object($role) && property_exists($role, 'value')) {
                    $role = $role->value;
                } elseif ($role instanceof \BackedEnum) {
                    $role = $role->value;
                } elseif ($role instanceof \UnitEnum) {
                    $role = $role->name;
                } elseif (is_array($role)) {
                    $role = reset($role) ?: 'user';
                }

                return [
                    'role' => (string) $role,
                    'content' => (string) ($item['content'] ?? ''),
                ];
            })
            ->filter(function ($item) {
                // Remove empty messages
                if (empty(trim($item['content']))) {
                    return false;
                }
                // Remove error assistant messages to avoid polluting AI context
                if ($item['role'] === 'assistant' && (
                    str_contains($item['content'], 'произошла ошибка') ||
                    str_contains($item['content'], 'Произошла ошибка') ||
                    str_contains($item['content'], 'временно недоступен') ||
                    str_contains($item['content'], 'Превышено время') ||
                    str_contains($item['content'], 'Не удалось обработать')
                )) {
                    return false;
                }
                return true;
            })
            ->values()
            ->toArray();
    }

    private function handleError(EmployeeAiConversation $conversation, string $errorMessage): array
    {
        $message = $conversation->addMessage(
            'assistant',
            $errorMessage,
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