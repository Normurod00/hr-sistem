<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\ApplicationStatus;
use App\Jobs\AnalyzeApplication;
use App\Models\Application;
use App\Services\MatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Список всех заявок
     */
    public function index(Request $request): View
    {
        $query = Application::query()
            ->with(['candidate', 'vacancy', 'analysis', 'candidateTest'])
            ->latest();

        // Фильтр по статусу
        if ($status = $request->input('status')) {
            $appStatus = ApplicationStatus::tryFrom($status);
            if ($appStatus) {
                $query->where('status', $appStatus);
            }
        }

        // Фильтр по вакансии
        if ($vacancyId = $request->input('vacancy_id')) {
            $query->where('vacancy_id', $vacancyId);
        }

        // Фильтр по match score
        if ($request->has('min_score')) {
            $query->where('match_score', '>=', (int) $request->input('min_score'));
        }

        $applications = $query->paginate(20)->withQueryString();

        $statuses = ApplicationStatus::cases();

        return view('admin.applications.index', compact('applications', 'statuses'));
    }

    /**
     * Детальный просмотр заявки с AI-анализом
     */
    public function show(Application $application, MatchingService $matchingService): View
    {
        $application->load([
            'candidate.candidateProfile',
            'vacancy',
            'files',
            'analysis',
            'candidateTest',
            'aiLogs' => fn($q) => $q->latest()->take(10),
        ]);

        // Получаем детальный breakdown, если есть профиль
        $matchBreakdown = null;
        if ($application->candidate?->candidateProfile) {
            $profile = $application->candidate->candidateProfile->toAiFormat();
            $matchBreakdown = $matchingService->calculateBreakdown($profile, $application->vacancy);
        }

        $statuses = ApplicationStatus::cases();

        return view('admin.applications.show', compact(
            'application',
            'matchBreakdown',
            'statuses'
        ));
    }

    /**
     * Изменение статуса заявки
     */
    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $status = ApplicationStatus::tryFrom($validated['status']);

        if (!$status) {
            return back()->with('error', 'Неверный статус.');
        }

        $application->update([
            'status' => $status,
            'notes' => $validated['notes'] ?? $application->notes,
        ]);

        return back()->with('success', 'Статус заявки обновлён.');
    }

    /**
     * Запустить AI-анализ повторно
     */
    public function reanalyze(Application $application): RedirectResponse
    {
        // Проверяем, есть ли профиль кандидата
        if (!$application->candidate?->candidateProfile || $application->candidate->candidateProfile->isEmpty()) {
            // Проверяем есть ли файлы для обработки
            $hasFiles = $application->files()->exists();

            if (!$hasFiles) {
                return back()->with('error', 'Нет файлов резюме для обработки.');
            }

            // Запускаем обработку файлов → профиль → анализ (цепочка)
            \App\Jobs\ProcessApplicationFilesBatch::dispatch($application);

            return back()->with('success', 'Запущена обработка файлов резюме. Анализ начнётся автоматически после создания профиля.');
        }

        AnalyzeApplication::dispatch($application);

        return back()->with('success', 'AI-анализ запущен. Результаты появятся в течение нескольких минут.');
    }

    /**
     * Массовое изменение статуса
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_ids' => ['required', 'array'],
            'application_ids.*' => ['integer', 'exists:applications,id'],
            'status' => ['required', 'string'],
        ]);

        $status = ApplicationStatus::tryFrom($validated['status']);

        if (!$status) {
            return back()->with('error', 'Неверный статус.');
        }

        $applications = Application::whereIn('id', $validated['application_ids'])->get();

        foreach ($applications as $application) {
            $application->update(['status' => $status]);
        }

        return back()->with('success', "Статус обновлён для {$applications->count()} заявок.");
    }

    /**
     * Удаление заявки кандидата
     */
    public function destroy(Application $application): RedirectResponse
    {
        $candidateName = $application->candidate?->name ?? 'Неизвестный';
        $vacancyTitle = $application->vacancy?->title ?? 'Удалённая вакансия';

        $application->delete();

        return redirect()
            ->route('admin.applications.index')
            ->with('success', "Заявка кандидата {$candidateName} на вакансию \"{$vacancyTitle}\" успешно удалена.");
    }
}
