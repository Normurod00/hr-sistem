<?php

namespace App\Observers;

use App\Jobs\ProcessEmployeeDocument;
use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Log;

class EmployeeDocumentObserver
{
    public function created(EmployeeDocument $document): void
    {
        try {
            ProcessEmployeeDocument::dispatch($document)->delay(now()->addSeconds(3));

            Log::info('EmployeeDocumentObserver: запланирована обработка документа', [
                'document_id' => $document->id,
                'type' => $document->document_type,
            ]);
        } catch (\Throwable $e) {
            Log::error('EmployeeDocumentObserver: ошибка при dispatch', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
