<?php

namespace App\Jobs;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendStatusNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Application $application,
        public ApplicationStatus $newStatus
    ) {}

    public function handle(SmsService $smsService): void
    {
        Log::info('SendStatusNotification: отправка SMS уведомления', [
            'application_id' => $this->application->id,
            'status' => $this->newStatus->value,
        ]);

        // Загружаем связанные данные
        $this->application->load(['candidate', 'vacancy']);

        // Отправляем SMS
        $notification = $smsService->sendStatusNotification($this->application, $this->newStatus);

        if ($notification) {
            Log::info('SendStatusNotification: SMS отправлено', [
                'application_id' => $this->application->id,
                'notification_id' => $notification->id,
                'status' => $notification->status,
            ]);
        } else {
            Log::warning('SendStatusNotification: SMS не отправлено (нет номера телефона)', [
                'application_id' => $this->application->id,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendStatusNotification: ошибка отправки', [
            'application_id' => $this->application->id,
            'status' => $this->newStatus->value,
            'error' => $exception->getMessage(),
        ]);
    }
}
