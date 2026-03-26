<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EriService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.eri.base_url', 'https://dls.yt.uz');
        $this->apiKey = config('services.eri.api_key', '');
    }

    /**
     * Проверить и извлечь данные из ERI ключа
     */
    public function verifyAndExtract(UploadedFile $file, string $password): ?array
    {
        try {
            // Отправляем файл на сервер верификации ERI
            $response = Http::attach(
                'key_file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )
            ->post($this->baseUrl . '/api/v1/verify', [
                'password' => $password,
                'api_key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] ?? false) {
                    return $this->parseEriData($data['certificate'] ?? []);
                }

                Log::warning('ERI verification failed', [
                    'error' => $data['error'] ?? 'Unknown error',
                ]);
                return null;
            }

            Log::error('ERI API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('ERI verification exception', [
                'message' => $e->getMessage(),
            ]);

            // Для демо: если API недоступен, возвращаем mock данные
            if (config('app.debug')) {
                return $this->getMockEriData($file->getClientOriginalName());
            }

            return null;
        }
    }

    /**
     * Парсинг данных из сертификата ERI
     */
    protected function parseEriData(array $certificate): array
    {
        return [
            'eri_serial' => $certificate['serial_number'] ?? null,
            'pin' => $certificate['pin'] ?? $certificate['inn'] ?? null,
            'first_name' => $certificate['first_name'] ?? null,
            'last_name' => $certificate['last_name'] ?? null,
            'middle_name' => $certificate['middle_name'] ?? null,
            'full_name' => $certificate['common_name'] ?? $certificate['cn'] ?? null,
            'organization' => $certificate['organization'] ?? $certificate['o'] ?? null,
            'position' => $certificate['title'] ?? null,
            'email' => $certificate['email'] ?? null,
            'valid_from' => $certificate['not_before'] ?? null,
            'valid_to' => $certificate['not_after'] ?? null,
            'issuer' => $certificate['issuer'] ?? null,
            'raw' => $certificate,
        ];
    }

    /**
     * Mock данные для демонстрации (только в debug режиме)
     */
    protected function getMockEriData(string $filename): array
    {
        return [
            'eri_serial' => 'ERI-' . strtoupper(substr(md5($filename), 0, 8)),
            'pin' => '3' . rand(1000000000000, 9999999999999),
            'first_name' => 'Тест',
            'last_name' => 'Пользователь',
            'middle_name' => 'ERI',
            'full_name' => 'Пользователь Тест ERI',
            'organization' => 'BRB Bank',
            'position' => 'Специалист',
            'email' => 'test.eri@brb.uz',
            'valid_from' => now()->subYear()->toDateString(),
            'valid_to' => now()->addYear()->toDateString(),
            'issuer' => 'E-IMZO Demo CA',
            'raw' => [],
        ];
    }

    /**
     * Проверить валидность сертификата
     */
    public function isValid(array $eriData): bool
    {
        if (empty($eriData['valid_to'])) {
            return true; // Если нет даты, считаем валидным
        }

        return now()->lt($eriData['valid_to']);
    }
}
