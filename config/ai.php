<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HR AI Server Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки подключения к локальному AI серверу.
    | Сервер запускается из папки ai_server: python run.py
    |
    */

    // Employee AI Gateway URL (используется AiGatewayService)
    'url' => env('HR_AI_URL', 'http://127.0.0.1:8095'),
    'timeout' => (int) env('HR_AI_TIMEOUT', 120),

    'server' => [
        // Default points to local FastAPI runner; keep in sync with ai_server/config.yaml and start-services.bat
        'url' => env('HR_AI_URL', 'http://127.0.0.1:8095'),
        'timeout' => (int) env('HR_AI_TIMEOUT', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Settings
    |--------------------------------------------------------------------------
    |
    | Настройки поведения AI-робота
    |
    */
    'settings' => [
        // Автоматически анализировать при новой заявке
        'auto_analyze_on_new_application' => true,

        // Генерировать блоки отчёта
        'generate_strengths' => true,
        'generate_weaknesses' => true,
        'generate_risks' => true,
        'generate_questions' => true,

        // Минимальный match score для shortlist
        'min_match_score_for_shortlist' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Match Score Weights
    |--------------------------------------------------------------------------
    |
    | Веса для расчёта match score
    |
    */
    'match_weights' => [
        'must_have_skills' => 0.5,
        'nice_to_have_skills' => 0.3,
        'experience' => 0.2,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Response Caching (НОВОЕ)
    |--------------------------------------------------------------------------
    |
    | Кэширование ответов AI для ускорения повторных запросов
    | Одинаковые резюме не будут анализироваться повторно
    |
    */
    'cache' => [
        // Включить кэширование
        'enabled' => env('HR_AI_CACHE_ENABLED', true),

        // Время жизни кэша в секундах (по умолчанию 1 час)
        'ttl' => (int) env('HR_AI_CACHE_TTL', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Rejection Settings (НОВОЕ)
    |--------------------------------------------------------------------------
    |
    | Автоматическое отклонение заявок с низким match score
    |
    */
    'auto_reject' => [
        // Включить автоотклонение
        'enabled' => env('HR_AI_AUTO_REJECT_ENABLED', false),

        // Порог score для автоматического отклонения
        'threshold' => (int) env('HR_AI_AUTO_REJECT_THRESHOLD', 25),
    ],
];
