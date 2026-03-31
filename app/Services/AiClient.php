<?php

namespace App\Services;

use App\Models\AiLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\ConnectionException;
use Exception;

class AiClient
{
    protected string $baseUrl;
    protected int $timeout;
    protected bool $cacheEnabled;
    protected int $cacheTtl;

    public function __construct()
    {
        $url = config('ai.server.url');

        if (empty($url)) {
            Log::error('AiClient: HR_AI_URL не настроен в .env');
            $url = 'http://127.0.0.1:8095';
        }

        $this->baseUrl = rtrim($url, '/');
        $this->timeout = config('ai.server.timeout', 120);
        $this->cacheEnabled = config('ai.cache.enabled', true);
        $this->cacheTtl = config('ai.cache.ttl', 3600);
    }

    /**
     * Проверка доступности AI-сервера
     */
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/health");

            if ($response->successful()) {
                return [
                    'status' => 'online',
                    'data' => $response->json(),
                ];
            }

            return [
                'status' => 'error',
                'message' => 'AI-сервер вернул ошибку: ' . $response->status(),
            ];
        } catch (ConnectionException $e) {
            return [
                'status' => 'offline',
                'message' => 'AI-сервер недоступен. Убедитесь, что он запущен.',
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Генерация ключа кэша
     */
    protected function getCacheKey(string $operation, string $content): string
    {
        return 'ai:' . $operation . ':' . md5($content);
    }

    /**
     * Парсинг текста резюме → структурированный профиль (С КЭШИРОВАНИЕМ)
     */
    public function parseResume(string $resumeText, ?int $applicationId = null): array
    {
        // Проверяем кэш
        if ($this->cacheEnabled) {
            $cacheKey = $this->getCacheKey('parse_resume', $resumeText);
            $cached = Cache::get($cacheKey);

            if ($cached) {
                Log::info('AiClient::parseResume используется кэш', [
                    'cache_key' => $cacheKey,
                    'application_id' => $applicationId,
                ]);

                return $cached;
            }
        }

        $startTime = microtime(true);

        $log = AiLog::logStart(
            AiLog::OP_PARSE_RESUME,
            $applicationId,
            ['text_length' => mb_strlen($resumeText)]
        );

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/parse-resume", [
                    'text' => $resumeText,
                ]);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $log->markSuccess($data, $durationMs);

                $result = [
                    'success' => true,
                    'profile' => $data['profile'] ?? $data,
                ];

                // Сохраняем в кэш
                if ($this->cacheEnabled) {
                    Cache::put($cacheKey, $result, $this->cacheTtl);
                }

                return $result;
            }

            $error = $response->json()['detail'] ?? 'Неизвестная ошибка';
            $log->markError($error, $durationMs);

            return [
                'success' => false,
                'error' => $error,
            ];

        } catch (ConnectionException $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError('AI-сервер недоступен', $durationMs);

            return [
                'success' => false,
                'error' => 'AI-сервер недоступен. Проверьте подключение.',
            ];

        } catch (Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            Log::error('AiClient::parseResume error', [
                'message' => $e->getMessage(),
                'application_id' => $applicationId,
            ]);

            return [
                'success' => false,
                'error' => 'Ошибка при обработке резюме: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Парсинг файла резюме (base64)
     */
    public function parseFile(string $base64Content, string $filename, ?int $applicationId = null): array
    {
        $startTime = microtime(true);

        $log = AiLog::logStart(
            AiLog::OP_PARSE_FILE,
            $applicationId,
            ['filename' => $filename]
        );

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/parse-file", [
                    'file_content' => $base64Content,
                    'filename' => $filename,
                ]);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $log->markSuccess($data, $durationMs);

                return [
                    'success' => true,
                    'text' => $data['text'] ?? '',
                    'profile' => $data['profile'] ?? null,
                ];
            }

            $error = $response->json()['detail'] ?? 'Ошибка парсинга файла';
            $log->markError($error, $durationMs);

            return [
                'success' => false,
                'error' => $error,
            ];

        } catch (Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Анализ кандидата под вакансию
     */
    public function analyzeCandidate(array $profile, array $vacancy, ?int $applicationId = null): array
    {
        $startTime = microtime(true);

        $log = AiLog::logStart(
            AiLog::OP_ANALYZE,
            $applicationId,
            [
                'profile_position' => $profile['position_title'] ?? 'unknown',
                'vacancy_title' => $vacancy['title'] ?? 'unknown',
            ]
        );

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/analyze", [
                    'profile' => $profile,
                    'vacancy' => $vacancy,
                ]);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $log->markSuccess($data, $durationMs);

                // AI-сервер возвращает {"success": true, "analysis": {...}}
                $analysisData = $data['analysis'] ?? $data;

                return [
                    'success' => true,
                    'analysis' => [
                        'strengths' => $analysisData['strengths'] ?? [],
                        'weaknesses' => $analysisData['weaknesses'] ?? [],
                        'risks' => $analysisData['risks'] ?? [],
                        'suggested_questions' => $analysisData['suggested_questions'] ?? [],
                        'recommendation' => $analysisData['recommendation'] ?? '',
                        'match_score' => $analysisData['match_score'] ?? null,
                    ],
                ];
            }

            $error = $response->json()['detail'] ?? 'Ошибка анализа';
            $log->markError($error, $durationMs);

            return [
                'success' => false,
                'error' => $error,
            ];

        } catch (Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Расчёт match score через AI
     */
    public function calculateMatchScore(array $profile, array $vacancy, ?int $applicationId = null): array
    {
        $startTime = microtime(true);

        $log = AiLog::logStart(
            AiLog::OP_MATCH_SCORE,
            $applicationId
        );

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/match-score", [
                    'profile' => $profile,
                    'vacancy' => $vacancy,
                ]);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $log->markSuccess($data, $durationMs);

                return [
                    'success' => true,
                    'score' => (int) ($data['score'] ?? $data['match_score'] ?? 0),
                    'breakdown' => $data['breakdown'] ?? null,
                ];
            }

            $error = $response->json()['detail'] ?? 'Ошибка расчёта';
            $log->markError($error, $durationMs);

            return [
                'success' => false,
                'error' => $error,
            ];

        } catch (Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Генерация вопросов для интервью
     */
    public function generateQuestions(array $profile, array $vacancy, int $count = 5, ?int $applicationId = null): array
    {
        $startTime = microtime(true);

        $log = AiLog::logStart(
            AiLog::OP_GENERATE_QUESTIONS,
            $applicationId,
            ['count' => $count]
        );

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/questions", [
                    'profile' => $profile,
                    'vacancy' => $vacancy,
                    'count' => $count,
                ]);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $log->markSuccess($data, $durationMs);

                return [
                    'success' => true,
                    'questions' => $data['questions'] ?? [],
                ];
            }

            $error = $response->json()['detail'] ?? 'Ошибка генерации вопросов';
            $log->markError($error, $durationMs);

            return [
                'success' => false,
                'error' => $error,
            ];

        } catch (Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $log->markError($e->getMessage(), $durationMs);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Генерация письма об отказе
     */
    public function generateRejectionEmail(array $profile, array $vacancy, ?string $reason = null): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/rejection-email", [
                    'profile' => $profile,
                    'vacancy' => $vacancy,
                    'reason' => $reason,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    // FastAPI returns email_text; keep backward compat with email
                    'email' => $data['email_text'] ?? $data['email'] ?? '',
                    'subject' => $data['subject'] ?? 'Ответ на вашу заявку',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['detail'] ?? 'Ошибка генерации письма',
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Получить список поддерживаемых форматов файлов
     */
    public function getSupportedFormats(): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/supported-formats");

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Параллельный парсинг нескольких файлов
     *
     * @param array $files Массив файлов [{file_content, filename, file_id?}]
     * @return array
     */
    public function parseFilesBatch(array $files): array
    {
        $startTime = microtime(true);

        try {
            $response = Http::timeout($this->timeout * 2) // Увеличенный таймаут для batch
                ->post("{$this->baseUrl}/parse-files-batch", $files);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('AiClient::parseFilesBatch успешно', [
                    'total' => $data['total'] ?? 0,
                    'processed' => $data['processed'] ?? 0,
                    'failed' => $data['failed'] ?? 0,
                    'duration_ms' => $durationMs,
                ]);

                return [
                    'success' => true,
                    'total' => $data['total'] ?? 0,
                    'processed' => $data['processed'] ?? 0,
                    'failed' => $data['failed'] ?? 0,
                    'results' => $data['results'] ?? [],
                    'processing_time_ms' => $data['processing_time_ms'] ?? $durationMs,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['detail'] ?? 'Ошибка batch парсинга',
            ];

        } catch (ConnectionException $e) {
            return [
                'success' => false,
                'error' => 'AI-сервер недоступен',
            ];
        } catch (Exception $e) {
            Log::error('AiClient::parseFilesBatch error', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Параллельный анализ нескольких кандидатов
     *
     * @param array $items Массив [{profile, vacancy, application_id?}]
     * @return array
     */
    public function analyzeCandidatesBatch(array $items): array
    {
        $startTime = microtime(true);

        try {
            $response = Http::timeout($this->timeout * 2)
                ->post("{$this->baseUrl}/analyze-batch", $items);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('AiClient::analyzeCandidatesBatch успешно', [
                    'total' => $data['total'] ?? 0,
                    'processed' => $data['processed'] ?? 0,
                    'duration_ms' => $durationMs,
                ]);

                return [
                    'success' => true,
                    'total' => $data['total'] ?? 0,
                    'processed' => $data['processed'] ?? 0,
                    'results' => $data['results'] ?? [],
                    'processing_time_ms' => $data['processing_time_ms'] ?? $durationMs,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['detail'] ?? 'Ошибка batch анализа',
            ];

        } catch (Exception $e) {
            Log::error('AiClient::analyzeCandidatesBatch error', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
