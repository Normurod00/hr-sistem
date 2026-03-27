<?php

/**
 * Конфигурация внешних интеграций
 *
 * ВАЖНО: Это ПЛЕЙСХОЛДЕР для будущих интеграций.
 * Когда будет готов реальный API:
 * 1. Заполните переменные в .env
 * 2. Реализуйте методы в HttpKpiProvider
 */

return [
    /*
    |--------------------------------------------------------------------------
    | KPI System Integration
    |--------------------------------------------------------------------------
    |
    | Интеграция с внешней системой KPI.
    | Все запросы к KPI API проходят через KpiClient.
    |
    */
    'kpi' => [
        'enabled' => env('KPI_API_ENABLED', false),
        'base_url' => env('KPI_API_BASE_URL', 'https://kpi.example.com/api/v1'),
        'token' => env('KPI_API_TOKEN', ''),
        'timeout' => env('KPI_API_TIMEOUT', 30),
        'retry' => [
            'times' => env('KPI_API_RETRY_TIMES', 3),
            'sleep' => env('KPI_API_RETRY_SLEEP', 1000), // ms
        ],
        'cache' => [
            'enabled' => env('KPI_CACHE_ENABLED', true),
            'ttl' => env('KPI_CACHE_TTL', 3600), // seconds
        ],
        'endpoints' => [
            'employee_kpi' => '/employees/{employee_id}/kpi',
            'department_kpi' => '/departments/{department_id}/kpi',
            'periods' => '/periods',
            'metrics' => '/metrics',
        ],
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            // 'X-API-Key' => env('KPI_API_KEY', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pulse Integration (Placeholder)
    |--------------------------------------------------------------------------
    */
    'pulse' => [
        'enabled' => env('PULSE_API_ENABLED', false),
        'base_url' => env('PULSE_API_BASE_URL', 'https://pulse.example.com/api/v1'),
        'token' => env('PULSE_API_TOKEN', ''),
        'timeout' => env('PULSE_API_TIMEOUT', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Smart Office Integration (Placeholder)
    |--------------------------------------------------------------------------
    */
    'smart_office' => [
        'enabled' => env('SMART_OFFICE_API_ENABLED', false),
        'base_url' => env('SMART_OFFICE_API_BASE_URL', 'https://smartoffice.example.com/api'),
        'token' => env('SMART_OFFICE_API_TOKEN', ''),
        'timeout' => env('SMART_OFFICE_API_TIMEOUT', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | iABS Integration (Placeholder)
    |--------------------------------------------------------------------------
    */
    'iabs' => [
        'enabled' => env('IABS_API_ENABLED', false),
        'base_url' => env('IABS_API_BASE_URL', 'https://iabs.example.com/api'),
        'token' => env('IABS_API_TOKEN', ''),
        'timeout' => env('IABS_API_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mock Data Settings (for development)
    |--------------------------------------------------------------------------
    */
    'mock' => [
        'enabled' => env('INTEGRATIONS_MOCK_ENABLED', false),
        'delay_ms' => env('INTEGRATIONS_MOCK_DELAY', 100), // имитация задержки
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('INTEGRATIONS_LOG_ENABLED', true),
        'channel' => env('INTEGRATIONS_LOG_CHANNEL', 'daily'),
        'retention_days' => env('INTEGRATIONS_LOG_RETENTION', 30),
    ],
];
