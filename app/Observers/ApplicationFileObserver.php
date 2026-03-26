<?php

namespace App\Observers;

use App\Models\ApplicationFile;
use Illuminate\Support\Facades\Log;

class ApplicationFileObserver
{
    /**
     * Когда добавляется новый файл к заявке - сбрасываем анализ
     */
    public function created(ApplicationFile $file): void
    {
        $application = $file->application;

        if (!$application) {
            return;
        }

        // Если у заявки уже был анализ - сбрасываем его
        if ($application->match_score !== null || $application->analysis()->exists()) {
            Log::info("Новый файл загружен для заявки #{$application->id}, сбрасываем анализ");

            // Сбрасываем match_score
            $application->update(['match_score' => null]);

            // Удаляем старый анализ (будет создан новый)
            $application->analysis()->delete();
        }
    }
}
