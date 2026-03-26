<?php

use App\Services\AiClient;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Команда для тестирования AI
Artisan::command('test:ai', function () {
    $this->info('=== Тестирование AI-сервера ===');
    $this->newLine();

    $aiClient = new AiClient();

    // 1. Health Check
    $this->info('1. Health Check:');
    $result = $aiClient->healthCheck();
    $this->line('   Статус: ' . ($result['status'] ?? 'unknown'));
    if (isset($result['data'])) {
        $this->line('   Данные: ' . json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    if (isset($result['message'])) {
        $this->line('   Сообщение: ' . $result['message']);
    }
    $this->newLine();

    if ($result['status'] !== 'online') {
        $this->error('AI-сервер недоступен. Тестирование прервано.');
        return 1;
    }

    // 2. Парсинг тестового резюме
    $this->info('2. Парсинг тестового резюме:');
    $testResume = "Иванов Иван Иванович
Разработчик PHP

Опыт работы: 5 лет

Навыки:
- PHP 8.x, Laravel 10+
- MySQL, PostgreSQL
- JavaScript, Vue.js, React
- Docker, Git, CI/CD
- REST API, GraphQL

Опыт работы:
2019-2024 - Senior PHP Developer, IT Company LLC
- Разработка корпоративных веб-приложений
- Оптимизация баз данных

2017-2019 - PHP Developer, StartUp Inc
- Разработка MVP продуктов

Образование:
ТУИТ, 2017, Бакалавр

Языки:
- Русский (родной)
- Английский (B2)

Контакты:
+998901234567
ivanov@example.com";

    $result = $aiClient->parseResume($testResume);
    $this->line('   Успех: ' . ($result['success'] ? 'ДА' : 'НЕТ'));
    if ($result['success']) {
        $profile = $result['profile'];
        $this->line('   - Позиция: ' . ($profile['position_title'] ?? 'N/A'));
        $this->line('   - Опыт: ' . ($profile['years_of_experience'] ?? 'N/A') . ' лет');
        $this->line('   - Навыки: ' . implode(', ', array_slice($profile['skills'] ?? [], 0, 10)));
        if (!empty($profile['languages'])) {
            $this->line('   - Языки: ' . count($profile['languages']) . ' языков');
        }
    } else {
        $this->error('   Ошибка: ' . ($result['error'] ?? 'Unknown'));
        return 1;
    }
    $this->newLine();

    // 3. Расчет Match Score
    $this->info('3. Расчет Match Score:');
    $profile = $result['profile'];

    $vacancy = [
        'title' => 'Senior PHP Developer',
        'must_have_skills' => ['PHP', 'Laravel', 'MySQL'],
        'nice_to_have_skills' => ['Vue.js', 'Docker', 'PostgreSQL'],
        'min_experience_years' => 3,
    ];

    $scoreResult = $aiClient->calculateMatchScore($profile, $vacancy);
    $this->line('   Успех: ' . ($scoreResult['success'] ? 'ДА' : 'НЕТ'));
    if ($scoreResult['success']) {
        $this->line('   Match Score: ' . $scoreResult['score'] . '%');
    } else {
        $this->error('   Ошибка: ' . ($scoreResult['error'] ?? 'Unknown'));
    }
    $this->newLine();

    // 4. Анализ кандидата
    $this->info('4. Анализ кандидата под вакансию:');
    $analysisResult = $aiClient->analyzeCandidate($profile, $vacancy);
    $this->line('   Успех: ' . ($analysisResult['success'] ? 'ДА' : 'НЕТ'));
    if ($analysisResult['success']) {
        $analysis = $analysisResult['analysis'];

        if (!empty($analysis['strengths'])) {
            $this->newLine();
            $this->line('   Сильные стороны:');
            foreach ($analysis['strengths'] as $i => $strength) {
                $this->line('   ' . ($i + 1) . '. ' . $strength);
            }
        }

        if (!empty($analysis['weaknesses'])) {
            $this->newLine();
            $this->line('   Слабые стороны:');
            foreach ($analysis['weaknesses'] as $i => $weakness) {
                $this->line('   ' . ($i + 1) . '. ' . $weakness);
            }
        }

        if (!empty($analysis['suggested_questions'])) {
            $this->newLine();
            $this->line('   Рекомендуемые вопросы (топ-5):');
            foreach (array_slice($analysis['suggested_questions'], 0, 5) as $i => $question) {
                $this->line('   ' . ($i + 1) . '. ' . $question);
            }
        }

        if (!empty($analysis['recommendation'])) {
            $this->newLine();
            $this->line('   Рекомендация: ' . $analysis['recommendation']);
        }
    } else {
        $this->error('   Ошибка: ' . ($analysisResult['error'] ?? 'Unknown'));
    }
    $this->newLine();

    $this->info('=== Тестирование завершено ===');
    return 0;
})->purpose('Тестирование AI-сервера');
