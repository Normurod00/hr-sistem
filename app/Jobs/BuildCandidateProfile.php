<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\AiLog;
use App\Services\AiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BuildCandidateProfile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Application $application
    ) {}

    public function handle(AiClient $aiClient): void
    {
        Log::info('BuildCandidateProfile: начало построения профиля', [
            'application_id' => $this->application->id,
            'user_id' => $this->application->user_id,
        ]);

        // Получаем резюме
        $resumeFile = $this->application->resume_file;

        if (!$resumeFile || !$resumeFile->has_parsed_text) {
            Log::warning('BuildCandidateProfile: нет распарсенного резюме', [
                'application_id' => $this->application->id,
            ]);
            return;
        }

        // Парсим резюме через AI
        $result = $aiClient->parseResume(
            $resumeFile->parsed_text,
            $this->application->id
        );

        if (!$result['success']) {
            Log::error('BuildCandidateProfile: ошибка парсинга резюме', [
                'application_id' => $this->application->id,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
            return;
        }

        $profile = $result['profile'];

        // Создаём или обновляем профиль кандидата
        CandidateProfile::updateOrCreate(
            ['user_id' => $this->application->user_id],
            [
                'profile' => $profile,
                'last_generated_at' => now(),
            ]
        );

        Log::info('BuildCandidateProfile: профиль создан/обновлён', [
            'application_id' => $this->application->id,
            'user_id' => $this->application->user_id,
            'position' => $profile['position_title'] ?? 'unknown',
        ]);

        // Если автоанализ включён — запускаем анализ
        if (config('ai.settings.auto_analyze_on_new_application', true)) {
            AnalyzeApplication::dispatch($this->application);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('BuildCandidateProfile: job failed', [
            'application_id' => $this->application->id,
            'error' => $exception->getMessage(),
        ]);

        AiLog::logError(
            AiLog::OP_BUILD_PROFILE,
            $exception->getMessage(),
            $this->application->id
        );
    }
}
