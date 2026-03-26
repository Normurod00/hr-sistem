<?php

namespace App\Console\Commands;

use App\Jobs\AnalyzeApplication;
use App\Models\Application;
use App\Services\AiClient;
use Illuminate\Console\Command;

class ReanalyzeAllApplications extends Command
{
    protected $signature = 'applications:reanalyze {--force : Переанализировать даже уже обработанные}';

    protected $description = 'Перезапустить AI анализ всех заявок';

    public function handle(): int
    {
        $aiClient = new AiClient();

        // Проверяем доступность AI сервера
        $health = $aiClient->healthCheck();
        if ($health['status'] !== 'online') {
            $this->error('AI сервер недоступен: ' . ($health['message'] ?? 'Unknown error'));
            return 1;
        }

        $this->info('AI сервер доступен.');

        // Получаем заявки с профилями
        $query = Application::query()
            ->whereHas('candidate.candidateProfile', function ($q) {
                $q->whereNotNull('profile');
            })
            ->with(['candidate.candidateProfile', 'vacancy']);

        if (!$this->option('force')) {
            $query->whereNull('match_score');
        }

        $applications = $query->get();

        if ($applications->isEmpty()) {
            $this->info('Нет заявок для анализа.');
            return 0;
        }

        $this->info("Найдено {$applications->count()} заявок для анализа.");

        if (!$this->confirm('Запустить анализ?')) {
            return 0;
        }

        $bar = $this->output->createProgressBar($applications->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($applications as $application) {
            try {
                // Синхронно запускаем анализ
                AnalyzeApplication::dispatchSync($application);
                $success++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Ошибка заявки #{$application->id}: " . $e->getMessage());
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Анализ завершён:");
        $this->info("  - Успешно: {$success}");
        $this->info("  - Ошибки: {$failed}");

        return 0;
    }
}
