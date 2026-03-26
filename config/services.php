<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ERI (Электрон рақамли имзо) - Электронная цифровая подпись
    |--------------------------------------------------------------------------
    */
    'eri' => [
        'base_url' => env('ERI_BASE_URL', 'https://dls.yt.uz'),
        'api_key' => env('ERI_API_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | my.gov.uz API - Получение данных о сотрудниках
    |--------------------------------------------------------------------------
    */
    'mygov' => [
        'base_url' => env('MYGOV_BASE_URL', 'https://my.gov.uz'),
        'api_key' => env('MYGOV_API_KEY', ''),
        'timeout' => env('MYGOV_TIMEOUT', 30),
        'brb_inn' => env('MYGOV_BRB_INN', ''), // ИНН организации BRB Bank
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Supported providers: 'log' (для тестирования), 'eskiz', 'playmobile'
    |
    */
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'log'),

        // Eskiz.uz (для Узбекистана)
        'eskiz' => [
            'token' => env('SMS_ESKIZ_TOKEN', ''),
            'from' => env('SMS_ESKIZ_FROM', '4546'),
        ],

        // PlayMobile (для Узбекистана)
        'playmobile' => [
            'login' => env('SMS_PLAYMOBILE_LOGIN', ''),
            'password' => env('SMS_PLAYMOBILE_PASSWORD', ''),
            'originator' => env('SMS_PLAYMOBILE_ORIGINATOR', 'BRB'),
        ],
    ],

];
