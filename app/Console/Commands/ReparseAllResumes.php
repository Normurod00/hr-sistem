<?php

namespace App\Console\Commands;

use App\Models\ApplicationFile;
use App\Models\CandidateProfile;
use App\Services\AiClient;
use Illuminate\Console\Command;

class ReparseAllResumes extends Command
{
    protected $signature = 'resumes:reparse {--force : Перепарсить даже уже обработанные файлы}';

    protected $description = 'Перепарсить все файлы резюме и обновить профили кандидатов';

    public function handle(): int
    {
        $aiClient = new AiClient();

        // Проверяем доступность AI сервера
        $health = $aiClient->healthCheck();
        if ($health['status'] !== 'online') {
            $this->error('AI сервер недоступен: ' . ($health['message'] ?? 'Unknown error'));
            $this->info('Запустите AI сервер: cd ai_server && python run.py');
            return 1;
        }

        $this->info('AI сервер доступен: ' . json_encode($health['data'] ?? []));

        // Получаем файлы
        $query = ApplicationFile::with(['application.candidate']);

        if (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('parsed_text')
                  ->orWhere('parsed_text', '')
                  ->orWhere('is_parsed', false);
            });
        }

        $files = $query->get();

        if ($files->isEmpty()) {
            $this->info('Нет файлов для обработки.');
            return 0;
        }

        $this->info("Найдено {$files->count()} файлов для обработки.");

        $bar = $this->output->createProgressBar($files->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($files as $file) {
            try {
                $fullPath = $file->full_path;

                if (!file_exists($fullPath)) {
                    $this->newLine();
                    $this->warn("Файл не найден: {$fullPath}");
                    $failed++;
                    $bar->advance();
                    continue;
                }

                // Читаем и кодируем файл
                $content = file_get_contents($fullPath);
                $base64Content = base64_encode($content);

                // Парсим через AI сервер
                $result = $aiClient->parseFile($base64Content, $file->original_name, $file->application_id);

                if (!empty($result['text'])) {
                    // Сохраняем распознанный текст
                    $file->update([
                        'parsed_text' => $result['text'],
                        'is_parsed' => true,
                    ]);
                }

                // Если есть профиль - обновляем его
                if (!empty($result['profile'])) {
                    $this->updateCandidateProfile($file, $result['profile']);
                }

                $success++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Ошибка файла #{$file->id}: " . $e->getMessage());
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Обработка завершена:");
        $this->info("  - Успешно: {$success}");
        $this->info("  - Ошибки: {$failed}");

        return 0;
    }

    protected function updateCandidateProfile(ApplicationFile $file, array $profile): void
    {
        $candidate = $file->application->candidate ?? null;

        if (!$candidate) {
            return;
        }

        // Ищем или создаём профиль
        $candidateProfile = CandidateProfile::firstOrNew(['user_id' => $candidate->id]);

        // Обновляем данные профиля (всё хранится в поле profile как JSON)
        $candidateProfile->profile = $profile;
        $candidateProfile->last_generated_at = now();
        $candidateProfile->save();

        $this->line(" → Профиль обновлён для: {$candidate->name}");
    }
}
