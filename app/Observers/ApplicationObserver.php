<?php

namespace App\Observers;

use App\Enums\ApplicationStatus;
use App\Jobs\ProcessApplicationFilesBatch;
use App\Jobs\SendStatusNotification;
use App\Models\Application;
use Illuminate\Support\Facades\Log;

class ApplicationObserver
{
    /**
     * Когда создаётся новая заявка — автоматически запускаем обработку файлов
     */
    public function created(Application $application): void
    {
        // Даём время на загрузку файлов (они могут добавляться после создания заявки)
        // Используем delay чтобы файлы успели сохраниться
        ProcessApplicationFilesBatch::dispatch($application)
            ->delay(now()->addSeconds(5));

        Log::info('ApplicationObserver: запланирована обработка файлов', [
            'application_id' => $application->id,
        ]);
    }

    /**
     * При обновлении заявки проверяем изменение статуса
     */
    public function updated(Application $application): void
    {
        // Проверяем, изменился ли статус
        if ($application->wasChanged('status')) {
            $newStatus = $application->status;

            // Статусы, при которых отправляем SMS
            $notifyStatuses = [
                ApplicationStatus::InReview,
                ApplicationStatus::Invited,
                ApplicationStatus::Rejected,
                ApplicationStatus::Hired,
            ];

            if (in_array($newStatus, $notifyStatuses)) {
                // Отправляем SMS через Job (асинхронно)
                SendStatusNotification::dispatch($application, $newStatus);

                Log::info('ApplicationObserver: запланирована отправка SMS', [
                    'application_id' => $application->id,
                    'new_status' => $newStatus->value,
                ]);
            }
        }
    }
}
