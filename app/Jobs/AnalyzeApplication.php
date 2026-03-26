<?php

namespace App\Jobs;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\AiLog;
use App\Models\CandidateProfile;
use App\Services\AiClient;
use App\Services\MatchingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeApplication implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 120;

    public function __construct(
        public Application $application
    ) {}

    public function handle(AiClient $aiClient, MatchingService $matchingService): void
    {
        Log::info('AnalyzeApplication: начало анализа', [
            'application_id' => $this->application->id,
        ]);

        // Получаем профиль кандидата
        $candidateProfile = $this->application->candidate?->candidateProfile;

        if (!$candidateProfile || $candidateProfile->isEmpty()) {
            // Попробуем построить профиль на лету из уже распарсенного резюме
            $resumeFile = $this->application->resume_file;
            if ($resumeFile && $resumeFile->has_parsed_text) {
                $parsed = $aiClient->parseResume($resumeFile->parsed_text, $this->application->id);
                if ($parsed['success'] ?? false) {
                    $candidateProfile = CandidateProfile::updateOrCreate(
                        ['user_id' => $this->application->user_id],
                        ['profile' => $parsed['profile'], 'last_generated_at' => now()]
                    );
                }
            }

            if (!$candidateProfile || $candidateProfile->isEmpty()) {
                Log::warning('AnalyzeApplication: профиль кандидата пуст', [
                    'application_id' => $this->application->id,
                ]);
                return;
            }
        }

        $profile = $candidateProfile->toAiFormat();
        $vacancy = $this->application->vacancy;

        if (!$vacancy) {
            Log::error('AnalyzeApplication: вакансия не найдена', [
                'application_id' => $this->application->id,
            ]);
            return;
        }

        $vacancyData = $vacancy->toAiFormat();

        // 1. Рассчитываем match score локально
        $matchScore = $matchingService->calculateMatchScore($profile, $vacancy);
        $this->application->updateMatchScore($matchScore);

        Log::info('AnalyzeApplication: match score рассчитан', [
            'application_id' => $this->application->id,
            'match_score' => $matchScore,
        ]);

        // 2. Получаем AI-анализ
        $result = $aiClient->analyzeCandidate(
            $profile,
            $vacancyData,
            $this->application->id
        );

        if (!$result['success']) {
            Log::error('AnalyzeApplication: ошибка AI-анализа', [
                'application_id' => $this->application->id,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
            return;
        }

        $analysis = $result['analysis'];

        // ВАЛИДАЦИЯ AI-ОТВЕТА
        if (!$this->validateAnalysisResponse($analysis)) {
            Log::warning('AnalyzeApplication: невалидный ответ от AI', [
                'application_id' => $this->application->id,
                'analysis' => $analysis,
            ]);

            // Используем значения по умолчанию
            $analysis = array_merge([
                'strengths' => [],
                'weaknesses' => [],
                'risks' => [],
                'suggested_questions' => [],
                'recommendation' => 'Требуется ручная проверка',
            ], $analysis);
        }

        // 3. Сохраняем анализ
        ApplicationAnalysis::updateOrCreate(
            ['application_id' => $this->application->id],
            [
                'strengths' => $analysis['strengths'] ?? [],
                'weaknesses' => $analysis['weaknesses'] ?? [],
                'risks' => $analysis['risks'] ?? [],
                'suggested_questions' => $analysis['suggested_questions'] ?? [],
                'recommendation' => $analysis['recommendation'] ?? '',
                'raw_ai_payload' => $result,
            ]
        );

        // 4. Автоматическое отклонение при низком score
        if (config('ai.auto_reject.enabled', false)) {
            $autoRejectThreshold = config('ai.auto_reject.threshold', 25);

            if ($matchScore < $autoRejectThreshold) {
                Log::info('AnalyzeApplication: автоматическое отклонение', [
                    'application_id' => $this->application->id,
                    'match_score' => $matchScore,
                    'threshold' => $autoRejectThreshold,
                ]);

                $this->application->update([
                    'status' => ApplicationStatus::Rejected,
                    'notes' => "Автоматически отклонено: match score ({$matchScore}%) ниже порога ({$autoRejectThreshold}%)",
                ]);

                return;
            }
        }

        // 5. Обновляем статус заявки
        if ($this->application->status === ApplicationStatus::New) {
            $this->application->markAsInReview();
        }

        Log::info('AnalyzeApplication: анализ завершён', [
            'application_id' => $this->application->id,
            'strengths_count' => count($analysis['strengths'] ?? []),
            'weaknesses_count' => count($analysis['weaknesses'] ?? []),
        ]);
    }

    /**
     * Валидация структуры ответа от AI (НОВОЕ)
     */
    protected function validateAnalysisResponse(array $analysis): bool
    {
        // Проверяем наличие всех ключевых полей
        $requiredFields = ['strengths', 'weaknesses', 'risks', 'suggested_questions', 'recommendation'];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $analysis)) {
                return false;
            }
        }

        // Проверяем типы данных
        if (!is_array($analysis['strengths']) ||
            !is_array($analysis['weaknesses']) ||
            !is_array($analysis['risks']) ||
            !is_array($analysis['suggested_questions']) ||
            !is_string($analysis['recommendation'])) {
            return false;
        }

        return true;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('AnalyzeApplication: job failed', [
            'application_id' => $this->application->id,
            'error' => $exception->getMessage(),
        ]);

        AiLog::logError(
            AiLog::OP_ANALYZE,
            $exception->getMessage(),
            $this->application->id
        );
    }
}
