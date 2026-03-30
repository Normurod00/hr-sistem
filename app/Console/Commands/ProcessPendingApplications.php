<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStatus;
use App\Jobs\AnalyzeApplication;
use App\Jobs\ProcessApplicationFile;
use App\Jobs\ProcessApplicationFilesBatch;
use App\Models\Application;
use App\Models\ApplicationFile;
use App\Services\AiClient;
use Illuminate\Console\Command;

class ProcessPendingApplications extends Command
{
    protected $signature = 'ai:process-pending
                            {--batch : Использовать batch обработку (быстрее)}
                            {--force : Переобработать все заявки, даже уже обработанные}
                            {--limit=100 : Максимум заявок для обработки}
                            ';

    protected $description = 'Обработать все ожидающие заявки кандидатов через AI';

    public function handle(AiClient $aiClient): int
    {
        $quiet = $this->output->isQuiet();

        // Проверяем доступность AI-сервера
        $health = $aiClient->healthCheck();

        if ($health['status'] !== 'online') {
            if (!$quiet) {
                $this->error('AI-сервер недоступен: ' . ($health['message'] ?? 'Unknown error'));
                $this->line('Запустите AI-сервер: cd ai_server && python run.py');
            }
            return Command::FAILURE;
        }

        if (!$quiet) {
            $this->info('[' . now()->format('H:i:s') . '] AI-сервер онлайн');
        }

        $useBatch = $this->option('batch');
        $force = $this->option('force');
        $limit = (int) $this->option('limit');

        // 1. Сначала обрабатываем непарсенные файлы
        $this->processUnparsedFiles($useBatch, $quiet);

        // 2. Затем анализируем заявки без анализа
        $this->analyzeApplications($force, $limit, $quiet);

        if (!$quiet) {
            $this->info('[' . now()->format('H:i:s') . '] Обработка завершена');
        }

        return Command::SUCCESS;
    }

    protected function processUnparsedFiles(bool $useBatch, bool $quiet = false): void
    {
        $unparsedFiles = ApplicationFile::with('application')
            ->unparsed()
            ->get();

        if ($unparsedFiles->isEmpty()) {
            return;
        }

        if (!$quiet) {
            $this->info("Найдено {$unparsedFiles->count()} непарсенных файлов");
        }

        if ($useBatch) {
            // Группируем по заявкам для batch обработки
            $applicationIds = $unparsedFiles->pluck('application_id')->unique();

            foreach ($applicationIds as $applicationId) {
                $application = Application::find($applicationId);
                if ($application) {
                    ProcessApplicationFilesBatch::dispatch($application);
                }
            }

            if (!$quiet) {
                $this->info("Добавлено {$applicationIds->count()} batch задач в очередь");
            }
        } else {
            foreach ($unparsedFiles as $file) {
                ProcessApplicationFile::dispatch($file);
            }

            if (!$quiet) {
                $this->info("Добавлено {$unparsedFiles->count()} задач в очередь");
            }
        }
    }

    protected function analyzeApplications(bool $force, int $limit, bool $quiet = false): void
    {
        $query = Application::with(['candidate.candidateProfile', 'analysis'])
            ->latest();

        if (!$force) {
            // Только заявки без анализа или с пустым match_score
            $query->where(function ($q) {
                $q->whereDoesntHave('analysis')
                  ->orWhereNull('match_score');
            });
        }

        $applications = $query->limit($limit)->get();

        if ($applications->isEmpty()) {
            return;
        }

        if (!$quiet) {
            $this->info("Найдено {$applications->count()} заявок для анализа");
        }

        $dispatched = 0;
        $skipped = 0;

        foreach ($applications as $application) {
            // Проверяем есть ли профиль кандидата
            if (!$application->candidate?->candidateProfile ||
                $application->candidate->candidateProfile->isEmpty()) {
                $skipped++;
                continue;
            }

            AnalyzeApplication::dispatch($application);
            $dispatched++;
        }

        if (!$quiet && $dispatched > 0) {
            $this->info("Добавлено {$dispatched} задач анализа в очередь");
        }

        if (!$quiet && $skipped > 0) {
            $this->warn("Пропущено {$skipped} заявок (нет профиля кандидата)");
        }
    }
}
