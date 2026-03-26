<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiLog;
use App\Services\AiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AiSettingsController extends Controller
{
    /**
     * Страница настроек AI
     */
    public function index(AiClient $aiClient): View
    {
        // Получаем текущие настройки из БД
        $settings = DB::table('ai_settings')->pluck('value', 'key')->toArray();

        // Проверяем статус AI-сервера
        $aiStatus = $aiClient->healthCheck();

        // Статистика за последние 7 дней
        $stats = [
            'total_operations' => AiLog::recent(7)->count(),
            'successful' => AiLog::recent(7)->successful()->count(),
            'failed' => AiLog::recent(7)->failed()->count(),
            'avg_duration' => AiLog::recent(7)->successful()->avg('duration_ms'),
        ];

        // По типам операций
        $operationStats = AiLog::recent(7)
            ->select('operation', DB::raw('count(*) as count'))
            ->groupBy('operation')
            ->get()
            ->pluck('count', 'operation')
            ->toArray();

        return view('admin.ai.settings', compact(
            'settings',
            'aiStatus',
            'stats',
            'operationStats'
        ));
    }

    /**
     * Обновление настроек
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'auto_analyze_on_new_application' => ['boolean'],
            'generate_strengths' => ['boolean'],
            'generate_weaknesses' => ['boolean'],
            'generate_risks' => ['boolean'],
            'generate_questions' => ['boolean'],
            'min_match_score_for_shortlist' => ['integer', 'min:0', 'max:100'],
        ]);

        foreach ($validated as $key => $value) {
            DB::table('ai_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
            );
        }

        return back()->with('success', 'Настройки AI сохранены.');
    }

    /**
     * Логи AI-операций
     */
    public function logs(Request $request): View
    {
        $query = AiLog::query()->with('application.vacancy')->latest();

        // Фильтр по статусу
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Фильтр по операции
        if ($operation = $request->input('operation')) {
            $query->where('operation', $operation);
        }

        // Фильтр по дате
        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        $logs = $query->paginate(50)->withQueryString();

        $operations = [
            AiLog::OP_PARSE_RESUME => 'Парсинг резюме',
            AiLog::OP_PARSE_FILE => 'Парсинг файла',
            AiLog::OP_ANALYZE => 'Анализ кандидата',
            AiLog::OP_MATCH_SCORE => 'Расчёт совместимости',
            AiLog::OP_GENERATE_QUESTIONS => 'Генерация вопросов',
            AiLog::OP_BUILD_PROFILE => 'Построение профиля',
        ];

        return view('admin.ai.logs', compact('logs', 'operations'));
    }

    /**
     * Проверка состояния AI-сервера
     */
    public function checkHealth(AiClient $aiClient): RedirectResponse
    {
        $status = $aiClient->healthCheck();

        if ($status['status'] === 'online') {
            return back()->with('success', 'AI-сервер работает нормально.');
        }

        return back()->with('error', 'AI-сервер недоступен: ' . ($status['message'] ?? 'Неизвестная ошибка'));
    }
}
