<?php

namespace App\Jobs;

use App\Models\ApplicationFile;
use App\Services\AiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessApplicationFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public ApplicationFile $file
    ) {}

    public function handle(AiClient $aiClient): void
    {
        Log::info('ProcessApplicationFile: начало обработки', [
            'file_id' => $this->file->id,
            'original_name' => $this->file->original_name,
        ]);

        // Получаем содержимое файла в base64
        $base64Content = $this->file->getBase64Contents();

        if (!$base64Content) {
            Log::error('ProcessApplicationFile: файл не найден', [
                'file_id' => $this->file->id,
                'path' => $this->file->path,
            ]);
            return;
        }

        // Отправляем на парсинг в AI-сервер
        $result = $aiClient->parseFile(
            $base64Content,
            $this->file->original_name,
            $this->file->application_id
        );

        if (!$result['success']) {
            Log::error('ProcessApplicationFile: ошибка парсинга', [
                'file_id' => $this->file->id,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
            return;
        }

        // Сохраняем извлечённый текст
        $this->file->markAsParsed($result['text'] ?? '');

        Log::info('ProcessApplicationFile: файл обработан', [
            'file_id' => $this->file->id,
            'text_length' => mb_strlen($result['text'] ?? ''),
        ]);

        // Если это резюме — запускаем построение профиля
        if ($this->file->is_resume) {
            BuildCandidateProfile::dispatch($this->file->application);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessApplicationFile: job failed', [
            'file_id' => $this->file->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
