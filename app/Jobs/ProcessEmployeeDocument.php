<?php

namespace App\Jobs;

use App\Models\EmployeeDocument;
use App\Services\AiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEmployeeDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public EmployeeDocument $document
    ) {}

    public function handle(AiClient $aiClient): void
    {
        Log::info('ProcessEmployeeDocument: начало обработки', [
            'document_id' => $this->document->id,
            'type' => $this->document->document_type,
            'filename' => $this->document->original_name,
        ]);

        $this->document->markAsProcessing();

        $base64 = $this->document->getBase64Contents();

        if (!$base64) {
            $this->document->markAsFailed('Не удалось прочитать файл');
            Log::error('ProcessEmployeeDocument: файл не найден', [
                'document_id' => $this->document->id,
                'path' => $this->document->path,
            ]);
            return;
        }

        $result = $aiClient->parseFile(
            $base64,
            $this->document->original_name,
            null // no application_id for employee docs
        );

        if (!($result['success'] ?? false)) {
            $error = $result['error'] ?? 'Неизвестная ошибка AI';
            $this->document->markAsFailed($error);
            Log::error('ProcessEmployeeDocument: ошибка парсинга', [
                'document_id' => $this->document->id,
                'error' => $error,
            ]);
            return;
        }

        $text = $result['text'] ?? '';
        $profile = $result['profile'] ?? [];

        // Build analysis result adapted for employee documents
        $analysisResult = $this->buildAnalysisResult($profile, $text);

        $this->document->markAsParsed($text, $analysisResult);

        Log::info('ProcessEmployeeDocument: документ обработан', [
            'document_id' => $this->document->id,
            'text_length' => mb_strlen($text),
            'has_profile' => !empty($profile),
        ]);
    }

    /**
     * Builds structured analysis result from parsed profile data.
     * Adapts candidate-oriented parsing output to employee document context.
     */
    protected function buildAnalysisResult(array $profile, string $text): array
    {
        $result = [
            'document_type' => $this->document->document_type,
            'extracted_text_length' => mb_strlen($text),
            'parsing_successful' => !empty($text),
        ];

        // Extract relevant fields depending on document type
        switch ($this->document->document_type) {
            case 'diploma':
            case 'certificate':
                $result['education'] = $profile['education'] ?? [];
                $result['skills'] = array_map(
                    fn($s) => is_array($s) ? ($s['name'] ?? $s) : $s,
                    $profile['skills'] ?? []
                );
                $result['domains'] = $profile['domains'] ?? [];
                break;

            case 'contract':
                $result['position_title'] = $profile['position_title'] ?? null;
                $result['contact_info'] = $profile['contact_info'] ?? [];
                break;

            case 'id_document':
                $result['contact_info'] = $profile['contact_info'] ?? [];
                break;

            default:
                // For general documents, extract everything available
                if (!empty($profile['skills'])) {
                    $result['skills'] = array_map(
                        fn($s) => is_array($s) ? ($s['name'] ?? $s) : $s,
                        $profile['skills']
                    );
                }
                if (!empty($profile['education'])) {
                    $result['education'] = $profile['education'];
                }
                if (!empty($profile['position_title'])) {
                    $result['position_title'] = $profile['position_title'];
                }
                break;
        }

        $result['summary'] = $profile['summary'] ?? null;

        return $result;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessEmployeeDocument: job failed', [
            'document_id' => $this->document->id,
            'error' => $exception->getMessage(),
        ]);

        $this->document->markAsFailed('Job failed: ' . $exception->getMessage());
    }
}
