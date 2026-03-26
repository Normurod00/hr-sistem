<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для получения данных о сотрудниках через my.gov.uz API
 *
 * Используется для:
 * - Получения данных о месте работы по ПИНФЛ
 * - Проверки принадлежности к организации BRB
 * - Кэширования данных сотрудников
 */
class MyGovService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    // ИНН организации BRB Bank (замените на реальный)
    protected string $brbOrganizationCode;

    public function __construct()
    {
        $this->baseUrl = config('services.mygov.base_url', 'https://my.gov.uz');
        $this->apiKey = config('services.mygov.api_key', '');
        $this->timeout = (int) config('services.mygov.timeout', 30);
        $this->brbOrganizationCode = config('services.mygov.brb_inn', '');
    }

    /**
     * Проверить, является ли человек сотрудником BRB Bank
     *
     * @param string $pinfl ПИНФЛ сотрудника
     * @return array ['is_employee' => bool, 'data' => array|null, 'error' => string|null]
     */
    public function verifyBrbEmployee(string $pinfl): array
    {
        // Проверяем кэш (чтобы не делать запросы каждый раз)
        $cacheKey = "brb_employee_{$pinfl}";
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            Log::info('BRB employee verification from cache', ['pinfl' => $pinfl]);
            return $cached;
        }

        // Получаем данные из API
        $employeeData = $this->getEmployeeByPinfl($pinfl);

        if ($employeeData === null) {
            $result = [
                'is_employee' => false,
                'data' => null,
                'error' => 'Маълумотларни олишда хатолик юз берди. Кейинроқ уриниб кўринг.',
            ];

            // Кэшируем ошибку на 1 минуту
            Cache::put($cacheKey, $result, now()->addMinute());
            return $result;
        }

        // Проверяем, работает ли в BRB
        $isBrbEmployee = $this->checkIfBrbEmployee($employeeData);

        $result = [
            'is_employee' => $isBrbEmployee,
            'data' => $isBrbEmployee ? $employeeData : null,
            'error' => $isBrbEmployee ? null : 'Сиз BRB Bank ходими сифатида рўйхатдан ўтмагансиз.',
        ];

        // Кэшируем результат на 1 час
        Cache::put($cacheKey, $result, now()->addHour());

        Log::info('BRB employee verification result', [
            'pinfl' => $pinfl,
            'is_employee' => $isBrbEmployee,
        ]);

        return $result;
    }

    /**
     * Проверить, является ли сотрудник работником BRB
     * Проверяем по ИНН организации из ответа API
     */
    protected function checkIfBrbEmployee(array $employeeData): bool
    {
        // Получаем ИНН организации из ответа API
        $orgInn = $employeeData['organization_inn']
            ?? $employeeData['org_inn']
            ?? $employeeData['inn']
            ?? $employeeData['organization_code']
            ?? null;

        // Если ИНН BRB настроен - проверяем по нему
        if (!empty($this->brbOrganizationCode) && !empty($orgInn)) {
            return $orgInn === $this->brbOrganizationCode;
        }

        // Альтернатива: проверка по названию организации
        $orgName = mb_strtolower($employeeData['organization'] ?? $employeeData['org_name'] ?? '');

        if (!empty($orgName)) {
            // Ищем BRB в названии организации
            return str_contains($orgName, 'brb')
                || str_contains($orgName, 'брб')
                || str_contains($orgName, 'business retail bank');
        }

        // Если нет данных для проверки - отклоняем
        Log::warning('Cannot verify BRB employee - no organization data', [
            'employee_data' => $employeeData,
        ]);

        return false;
    }

    /**
     * Получить данные о сотруднике по ПИНФЛ
     *
     * @param string $pinfl ПИНФЛ сотрудника (14 цифр)
     * @return array|null Данные о сотруднике или null при ошибке
     */
    public function getEmployeeByPinfl(string $pinfl): ?array
    {
        try {
            // API запрос к my.gov.uz
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->get($this->baseUrl . '/api/v1/employee/work-info', [
                'pinfl' => $pinfl,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('MyGov API response received', [
                    'pinfl' => $pinfl,
                    'has_data' => !empty($data),
                ]);

                // Если данные получены - парсим их
                if (!empty($data)) {
                    return $this->parseEmployeeData($data);
                }

                return null;
            }

            Log::error('MyGov API request failed', [
                'pinfl' => $pinfl,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('MyGov API exception', [
                'pinfl' => $pinfl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Проверить, работает ли человек в указанной организации
     *
     * @param string $pinfl ПИНФЛ сотрудника
     * @param string $organizationCode Код организации (ИНН организации)
     * @return bool
     */
    public function isEmployeeOf(string $pinfl, string $organizationCode): bool
    {
        $employee = $this->getEmployeeByPinfl($pinfl);

        if (!$employee) {
            return false;
        }

        // TODO: Проверьте код организации в ответе API
        // return ($employee['organization_code'] ?? '') === $organizationCode;

        return false;
    }

    /**
     * Парсинг данных сотрудника из ответа API
     *
     * @param array $data Ответ API
     * @return array Нормализованные данные
     */
    protected function parseEmployeeData(array $data): array
    {
        // Адаптируйте поля под структуру ответа вашего API
        return [
            // ФИО
            'full_name' => $data['full_name'] ?? $data['fio'] ?? $data['name'] ?? null,

            // Дата рождения
            'birth_date' => $data['birth_date'] ?? $data['dob'] ?? $data['birthDate'] ?? null,

            // Организация (название)
            'organization' => $data['organization'] ?? $data['org_name'] ?? $data['company'] ?? $data['work_place'] ?? null,

            // ИНН организации (для проверки BRB)
            'organization_inn' => $data['organization_inn'] ?? $data['org_inn'] ?? $data['inn'] ?? $data['org_stir'] ?? null,

            // Отдел
            'department' => $data['department'] ?? $data['division'] ?? $data['struct_name'] ?? null,

            // Должность
            'position' => $data['position'] ?? $data['job_title'] ?? $data['post_name'] ?? null,

            // Дата приёма на работу
            'hire_date' => $data['hire_date'] ?? $data['employment_date'] ?? $data['start_date'] ?? null,

            // Табельный номер
            'employee_number' => $data['employee_number'] ?? $data['tab_number'] ?? $data['personnel_number'] ?? null,

            // Внутренний телефон
            'phone_internal' => $data['phone_internal'] ?? $data['ext'] ?? $data['internal_phone'] ?? null,

            // Офис/локация
            'office_location' => $data['office'] ?? $data['location'] ?? $data['branch'] ?? null,

            // Статус занятости
            'status' => $data['status'] ?? $data['work_status'] ?? 'active',

            // Оригинальные данные
            'raw' => $data,
        ];
    }

    /**
     * Получить API ключ
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Установить API ключ (для тестирования)
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * Проверить подключение к API
     */
    public function healthCheck(): array
    {
        try {
            // TODO: Реализуйте проверку подключения к API
            // $response = Http::withHeaders([
            //     'Authorization' => 'Bearer ' . $this->apiKey,
            // ])
            // ->timeout(10)
            // ->get($this->baseUrl . '/api/health');

            return [
                'status' => 'not_configured',
                'message' => 'API ключ не настроен или API не реализовано',
                'base_url' => $this->baseUrl,
                'has_api_key' => !empty($this->apiKey),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
