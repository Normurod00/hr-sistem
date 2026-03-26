<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\ApplicationFile;
use App\Models\CandidateProfile;
use App\Services\AiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProcessApplicationFilesBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 60;

    public function __construct(
        public Application $application
    ) {}

    public function handle(AiClient $aiClient): void
    {
        Log::info('ProcessApplicationFilesBatch: начало batch обработки', [
            'application_id' => $this->application->id,
        ]);

        // Получаем все необработанные файлы
        $files = $this->application->files()
            ->unparsed()
            ->get();

        if ($files->isEmpty()) {
            Log::info('ProcessApplicationFilesBatch: нет файлов для обработки');
            return;
        }

        // Подготавливаем данные для batch запроса
        $batchFiles = $files->map(function (ApplicationFile $file) {
            return [
                'file_content' => $file->getBase64Contents(),
                'filename' => $file->original_name,
                'file_id' => (string) $file->id,
            ];
        })->filter(fn($f) => !empty($f['file_content']))->values()->all();

        if (empty($batchFiles)) {
            Log::warning('ProcessApplicationFilesBatch: не удалось получить содержимое файлов');
            return;
        }

        // Отправляем batch запрос
        $result = $aiClient->parseFilesBatch($batchFiles);

        if (!$result['success'] && empty($result['results'])) {
            Log::error('ProcessApplicationFilesBatch: batch запрос не удался', [
                'error' => $result['error'] ?? 'Unknown error',
            ]);
            return;
        }

        Log::info('ProcessApplicationFilesBatch: batch результат', [
            'total' => $result['total'] ?? 0,
            'processed' => $result['processed'] ?? 0,
            'failed' => $result['failed'] ?? 0,
            'processing_time_ms' => $result['processing_time_ms'] ?? 0,
        ]);

        // Обрабатываем результаты
        $resumeProfiles = [];

        foreach ($result['results'] ?? [] as $fileResult) {
            $fileId = $fileResult['file_id'] ?? null;

            if (!$fileId) {
                continue;
            }

            $file = $files->firstWhere('id', (int) $fileId);

            if (!$file) {
                continue;
            }

            if ($fileResult['success']) {
                // Извлекаем текст из профиля для сохранения
                $profile = $fileResult['profile'] ?? [];
                $text = $this->extractTextFromProfile($profile);

                $file->markAsParsed($text);

                // Собираем профили резюме
                if ($file->is_resume && !empty($profile)) {
                    $resumeProfiles[] = $profile;
                }

                Log::info('ProcessApplicationFilesBatch: файл обработан', [
                    'file_id' => $file->id,
                    'filename' => $file->original_name,
                ]);
            } else {
                Log::warning('ProcessApplicationFilesBatch: ошибка обработки файла', [
                    'file_id' => $file->id,
                    'filename' => $file->original_name,
                    'error' => $fileResult['error'] ?? 'Unknown',
                ]);
            }
        }

        // Объединяем профили если было несколько резюме
        if (!empty($resumeProfiles)) {
            $mergedProfile = $this->mergeProfiles($resumeProfiles);

            CandidateProfile::updateOrCreate(
                ['user_id' => $this->application->user_id],
                [
                    'profile' => $mergedProfile,
                    'last_generated_at' => now(),
                ]
            );

            Log::info('ProcessApplicationFilesBatch: профиль кандидата создан/обновлён', [
                'user_id' => $this->application->user_id,
                'profiles_merged' => count($resumeProfiles),
            ]);

            // Запускаем анализ
            AnalyzeApplication::dispatch($this->application);
        }
    }

    /**
     * Извлекает текстовое представление из профиля
     */
    protected function extractTextFromProfile(array $profile): string
    {
        $parts = [];

        if (!empty($profile['position_title'])) {
            $parts[] = 'Позиция: ' . $profile['position_title'];
        }

        if (!empty($profile['years_of_experience'])) {
            $parts[] = 'Опыт: ' . $profile['years_of_experience'] . ' лет';
        }

        if (!empty($profile['skills'])) {
            $skillNames = array_map(function ($s) {
                return is_array($s) ? ($s['name'] ?? '') : $s;
            }, $profile['skills']);
            $parts[] = 'Навыки: ' . implode(', ', array_filter($skillNames));
        }

        if (!empty($profile['languages'])) {
            $langNames = array_map(function ($l) {
                $name = is_array($l) ? ($l['name'] ?? '') : $l;
                $level = is_array($l) ? ($l['level'] ?? '') : '';
                return $level ? "{$name} ({$level})" : $name;
            }, $profile['languages']);
            $parts[] = 'Языки: ' . implode(', ', array_filter($langNames));
        }

        return implode("\n", $parts);
    }

    /**
     * Объединяет несколько профилей в один
     */
    protected function mergeProfiles(array $profiles): array
    {
        if (count($profiles) === 1) {
            return $profiles[0];
        }

        $merged = [
            'position_title' => null,
            'years_of_experience' => 0,
            'skills' => [],
            'languages' => [],
            'education' => [],
            'domains' => [],
            'contact_info' => [],
            'soft_skills' => [],
            'has_management_experience' => false,
            'has_remote_experience' => false,
        ];

        $seenSkills = [];
        $seenLanguages = [];

        foreach ($profiles as $profile) {
            // Берём первую непустую позицию
            if (empty($merged['position_title']) && !empty($profile['position_title'])) {
                $merged['position_title'] = $profile['position_title'];
            }

            // Максимальный опыт
            $exp = $profile['years_of_experience'] ?? 0;
            if ($exp > $merged['years_of_experience']) {
                $merged['years_of_experience'] = $exp;
            }

            // Объединяем навыки (уникальные)
            foreach ($profile['skills'] ?? [] as $skill) {
                $name = is_array($skill) ? ($skill['name'] ?? '') : $skill;
                $nameLower = mb_strtolower($name);

                if (!isset($seenSkills[$nameLower])) {
                    $seenSkills[$nameLower] = true;
                    $merged['skills'][] = $skill;
                }
            }

            // Объединяем языки (уникальные)
            foreach ($profile['languages'] ?? [] as $lang) {
                $name = is_array($lang) ? ($lang['name'] ?? '') : $lang;
                $nameLower = mb_strtolower($name);

                if (!isset($seenLanguages[$nameLower])) {
                    $seenLanguages[$nameLower] = true;
                    $merged['languages'][] = $lang;
                }
            }

            // Объединяем образование
            $merged['education'] = array_merge($merged['education'], $profile['education'] ?? []);

            // Объединяем домены
            $merged['domains'] = array_unique(array_merge($merged['domains'], $profile['domains'] ?? []));

            // Объединяем контакты
            $merged['contact_info'] = array_merge($merged['contact_info'], $profile['contact_info'] ?? []);

            // Soft skills
            $merged['soft_skills'] = array_unique(array_merge($merged['soft_skills'], $profile['soft_skills'] ?? []));

            // Флаги
            $merged['has_management_experience'] = $merged['has_management_experience'] || ($profile['has_management_experience'] ?? false);
            $merged['has_remote_experience'] = $merged['has_remote_experience'] || ($profile['has_remote_experience'] ?? false);
        }

        return $merged;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessApplicationFilesBatch: job failed', [
            'application_id' => $this->application->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
