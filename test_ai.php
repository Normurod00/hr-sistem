<?php
// Тест AI-сервера

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Client\Factory;

echo "=== Тестирование AI-сервера ===\n\n";

$client = new Factory();

// 1. Health Check
echo "1. Health Check:\n";
try {
    $response = $client->timeout(5)->get('http://127.0.0.1:8888/health');
    echo "   Статус: " . $response->status() . "\n";
    echo "   Ответ: " . json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
} catch (Exception $e) {
    echo "   ОШИБКА: " . $e->getMessage() . "\n\n";
}

// 2. Root endpoint
echo "2. Root Endpoint:\n";
try {
    $response = $client->timeout(5)->get('http://127.0.0.1:8888/');
    echo "   Статус: " . $response->status() . "\n";
    echo "   Ответ: " . json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
} catch (Exception $e) {
    echo "   ОШИБКА: " . $e->getMessage() . "\n\n";
}

// 3. Supported formats
echo "3. Supported Formats:\n";
try {
    $response = $client->timeout(5)->get('http://127.0.0.1:8888/supported-formats');
    echo "   Статус: " . $response->status() . "\n";
    echo "   Ответ: " . json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
} catch (Exception $e) {
    echo "   ОШИБКА: " . $e->getMessage() . "\n\n";
}

// 4. Парсинг резюме
echo "4. Парсинг тестового резюме:\n";
$testResume = "Иванов Иван Иванович\nРазработчик PHP\n\nОпыт работы: 5 лет\n\nНавыки:\n- PHP, Laravel, MySQL\n- JavaScript, Vue.js\n- Docker, Git\n\nОбразование:\nТашкентский университет информационных технологий, 2018\nБакалавр, Информационные системы\n\nТелефон: +998901234567\nEmail: ivanov@example.com";

try {
    $response = $client->timeout(10)->post('http://127.0.0.1:8888/parse-resume', [
        'text' => $testResume
    ]);
    echo "   Статус: " . $response->status() . "\n";
    $result = $response->json();
    if ($result['success'] ?? false) {
        echo "   ✓ Успешно распарсено\n";
        echo "   Профиль:\n";
        echo "   " . json_encode($result['profile'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    } else {
        echo "   ✗ Ошибка: " . ($result['error'] ?? 'Unknown') . "\n\n";
    }
} catch (Exception $e) {
    echo "   ОШИБКА: " . $e->getMessage() . "\n\n";
}

// 5. Расчет Match Score
echo "5. Расчет Match Score:\n";
try {
    $profile = [
        'position_title' => 'PHP Developer',
        'years_of_experience' => 5,
        'skills' => ['PHP', 'Laravel', 'MySQL', 'JavaScript', 'Vue.js', 'Docker'],
        'education' => [
            ['degree' => 'Bachelor', 'field' => 'Information Systems', 'institution' => 'TUIT', 'year' => 2018]
        ]
    ];

    $vacancy = [
        'title' => 'Senior PHP Developer',
        'must_have_skills' => ['PHP', 'Laravel', 'MySQL'],
        'nice_to_have_skills' => ['Vue.js', 'Docker', 'Redis'],
        'min_experience_years' => 3
    ];

    $response = $client->timeout(10)->post('http://127.0.0.1:8888/match-score', [
        'profile' => $profile,
        'vacancy' => $vacancy
    ]);

    echo "   Статус: " . $response->status() . "\n";
    $result = $response->json();
    if ($result['success'] ?? false) {
        echo "   ✓ Match Score: " . ($result['match_score'] ?? 0) . "%\n";
        if (isset($result['breakdown'])) {
            echo "   Breakdown:\n";
            echo "   " . json_encode($result['breakdown'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        }
    } else {
        echo "   ✗ Ошибка: " . ($result['error'] ?? 'Unknown') . "\n\n";
    }
} catch (Exception $e) {
    echo "   ОШИБКА: " . $e->getMessage() . "\n\n";
}

echo "=== Тестирование завершено ===\n";
