<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\CandidateTest;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class TestController extends Controller
{
    /**
     * Страница теста для кандидата
     */
    public function show(Application $application): View
    {
        // Проверяем, что заявка принадлежит текущему пользователю
        if ($application->user_id !== auth()->id()) {
            abort(403, 'Это не ваша заявка');
        }

        // Проверяем, есть ли уже тест
        $test = $application->candidateTest;

        if (!$test) {
            // Создаём новый тест
            $test = $this->createTest($application);
        }

        // Проверяем статус теста
        if ($test->status === CandidateTest::STATUS_COMPLETED) {
            return view('tests.completed', compact('test', 'application'));
        }

        if ($test->status === CandidateTest::STATUS_EXPIRED) {
            return view('tests.expired', compact('test', 'application'));
        }

        // Проверяем, не истекло ли время
        if ($test->is_expired) {
            $test->expire();
            return view('tests.expired', compact('test', 'application'));
        }

        return view('tests.show', compact('test', 'application'));
    }

    /**
     * Запуск теста
     */
    public function start(Application $application): JsonResponse
    {
        if ($application->user_id !== auth()->id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $test = $application->candidateTest;

        if (!$test) {
            return response()->json(['error' => 'Тест не найден'], 404);
        }

        if ($test->status !== CandidateTest::STATUS_PENDING) {
            return response()->json(['error' => 'Тест уже начат или завершён'], 400);
        }

        $test->start();

        return response()->json([
            'success' => true,
            'remaining_time' => $test->remaining_time,
            'questions' => $this->prepareQuestionsForClient($test->questions),
        ]);
    }

    /**
     * Получить текущее состояние теста
     */
    public function status(Application $application): JsonResponse
    {
        if ($application->user_id !== auth()->id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $test = $application->candidateTest;

        if (!$test) {
            return response()->json(['error' => 'Тест не найден'], 404);
        }

        // Проверяем, не истекло ли время
        if ($test->status === CandidateTest::STATUS_IN_PROGRESS && $test->is_expired) {
            $test->expire();
        }

        return response()->json([
            'status' => $test->status,
            'remaining_time' => $test->remaining_time,
            'total_questions' => $test->total_questions,
            'started_at' => $test->started_at?->toIso8601String(),
        ]);
    }

    /**
     * Отправка ответов
     */
    public function submit(Request $request, Application $application): JsonResponse
    {
        if ($application->user_id !== auth()->id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $test = $application->candidateTest;

        if (!$test) {
            return response()->json(['error' => 'Тест не найден'], 404);
        }

        if ($test->status === CandidateTest::STATUS_COMPLETED) {
            return response()->json(['error' => 'Тест уже завершён'], 400);
        }

        if ($test->status === CandidateTest::STATUS_EXPIRED) {
            return response()->json(['error' => 'Время теста истекло'], 400);
        }

        $answers = $request->input('answers', []);

        // Завершаем тест
        $test->complete($answers);

        return response()->json([
            'success' => true,
            'score' => $test->score,
            'correct_answers' => $test->correct_answers,
            'total_questions' => $test->total_questions,
            'time_spent' => $test->time_spent,
        ]);
    }

    /**
     * Страница результатов
     */
    public function results(Application $application): View
    {
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        $test = $application->candidateTest;

        if (!$test || $test->status !== CandidateTest::STATUS_COMPLETED) {
            return redirect()->route('tests.show', $application);
        }

        return view('tests.results', compact('test', 'application'));
    }

    /**
     * Создаёт тест для заявки
     */
    private function createTest(Application $application): CandidateTest
    {
        $vacancy = $application->vacancy;

        // Генерируем вопросы через AI
        $questions = $this->generateQuestions($vacancy);

        return CandidateTest::create([
            'application_id' => $application->id,
            'user_id' => $application->user_id,
            'vacancy_id' => $vacancy->id,
            'questions' => $questions,
            'total_questions' => count($questions),
            'time_limit' => 900, // 15 минут
            'status' => CandidateTest::STATUS_PENDING,
        ]);
    }

    /**
     * Генерирует вопросы через AI сервер
     */
    private function generateQuestions(Vacancy $vacancy): array
    {
        try {
            $response = Http::timeout(30)->post(config('ai.url', 'http://127.0.0.1:8095') . '/generate-test', [
                'vacancy_title' => $vacancy->title,
                'vacancy_description' => $vacancy->description ?? '',
                'required_skills' => $vacancy->required_skills ?? [],
                'department' => $vacancy->department ?? '',
                'difficulty_distribution' => [
                    'easy' => 5,
                    'medium' => 5,
                    'hard' => 5,
                ],
            ]);

            if ($response->successful() && $response->json('success')) {
                return $response->json('questions', []);
            }
        } catch (\Exception $e) {
            \Log::error('Test generation failed, using fallback questions', [
                'vacancy_id' => $vacancy->id,
                'ai_url' => config('ai.url', 'http://127.0.0.1:8095'),
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback - базовые вопросы
        return $this->getFallbackQuestions();
    }

    /**
     * Базовые вопросы, если AI недоступен
     */
    private function getFallbackQuestions(): array
    {
        return [
            ['id' => 1, 'question' => 'Что такое deadline?', 'options' => ['Крайний срок', 'Начало проекта', 'Перерыв', 'Встреча'], 'correct_answer' => 0, 'difficulty' => 'easy'],
            ['id' => 2, 'question' => 'Что означает KPI?', 'options' => ['Ключевые показатели эффективности', 'Код проекта', 'Тип документа', 'Отдел'], 'correct_answer' => 0, 'difficulty' => 'easy'],
            ['id' => 3, 'question' => 'Сколько будет 15% от 200?', 'options' => ['30', '20', '25', '35'], 'correct_answer' => 0, 'difficulty' => 'easy'],
            ['id' => 4, 'question' => 'Если A > B и B > C, то:', 'options' => ['A > C', 'C > A', 'A = C', 'Нельзя определить'], 'correct_answer' => 0, 'difficulty' => 'easy'],
            ['id' => 5, 'question' => 'Какое число следующее: 2, 4, 8, 16, ?', 'options' => ['32', '24', '20', '28'], 'correct_answer' => 0, 'difficulty' => 'easy'],
            ['id' => 6, 'question' => 'Что такое приоритизация задач?', 'options' => ['Определение порядка важности', 'Удаление задач', 'Делегирование', 'Откладывание'], 'correct_answer' => 0, 'difficulty' => 'medium'],
            ['id' => 7, 'question' => 'Что важнее: скорость или качество?', 'options' => ['Баланс зависит от контекста', 'Всегда скорость', 'Всегда качество', 'Ни то, ни другое'], 'correct_answer' => 0, 'difficulty' => 'medium'],
            ['id' => 8, 'question' => 'Как справиться с конфликтом в команде?', 'options' => ['Обсуждение и компромисс', 'Игнорирование', 'Увольнение', 'Эскалация'], 'correct_answer' => 0, 'difficulty' => 'medium'],
            ['id' => 9, 'question' => 'Цена выросла на 20%, затем упала на 20%. Итог:', 'options' => ['96% от начальной', '100%', '80%', '104%'], 'correct_answer' => 0, 'difficulty' => 'medium'],
            ['id' => 10, 'question' => 'Что такое делегирование?', 'options' => ['Передача задач подчинённым', 'Выполнение самому', 'Отмена задачи', 'Откладывание'], 'correct_answer' => 0, 'difficulty' => 'medium'],
            ['id' => 11, 'question' => 'Что такое agile методология?', 'options' => ['Гибкий итеративный подход', 'Жёсткое планирование', 'Водопадная модель', 'Отсутствие планирования'], 'correct_answer' => 0, 'difficulty' => 'hard'],
            ['id' => 12, 'question' => 'Как измерить эффективность процесса?', 'options' => ['Метрики, KPI, анализ', 'Субъективная оценка', 'Не измерять', 'Спросить начальника'], 'correct_answer' => 0, 'difficulty' => 'hard'],
            ['id' => 13, 'question' => 'Два поезда едут навстречу (60 и 80 км/ч). За сколько сблизятся на 280 км?', 'options' => ['2 часа', '3 часа', '4 часа', '1.5 часа'], 'correct_answer' => 0, 'difficulty' => 'hard'],
            ['id' => 14, 'question' => 'Что такое SWOT-анализ?', 'options' => ['Анализ сильных/слабых сторон', 'Финансовый отчёт', 'Тип презентации', 'Метод продаж'], 'correct_answer' => 0, 'difficulty' => 'hard'],
            ['id' => 15, 'question' => 'Что такое ROI?', 'options' => ['Рентабельность инвестиций', 'Тип отчёта', 'Должность', 'Отдел'], 'correct_answer' => 0, 'difficulty' => 'hard'],
        ];
    }

    /**
     * Подготавливает вопросы для клиента (без correct_answer)
     */
    private function prepareQuestionsForClient(array $questions): array
    {
        return array_map(function ($q) {
            return [
                'id' => $q['id'],
                'question' => $q['question'],
                'options' => $q['options'],
                'difficulty' => $q['difficulty'],
            ];
        }, $questions);
    }
}
