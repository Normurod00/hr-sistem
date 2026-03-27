<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CandidateController extends Controller
{
    /**
     * Список кандидатов (Kanban Board)
     */
    public function index(Request $request): View
    {

        // Базовый запрос кандидатов
        $candidatesQuery = User::query()
            ->where('role', UserRole::Candidate)
            ->with(['applications.vacancy', 'applications.analysis', 'candidateProfile']);

        // Поиск
        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $candidatesQuery->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                  ->orWhere('email', 'like', "%{$escaped}%")
                  ->orWhere('phone', 'like', "%{$escaped}%");
            });
        }

        // Фильтр по вакансии
        if ($vacancyId = $request->input('vacancy_id')) {
            $candidatesQuery->whereHas('applications', function ($q) use ($vacancyId) {
                $q->where('vacancy_id', $vacancyId);
            });
        }

        // Фильтр по наличию заявок
        if ($hasApplications = $request->input('has_applications')) {
            if ($hasApplications === 'yes') {
                $candidatesQuery->has('applications');
            } elseif ($hasApplications === 'no') {
                $candidatesQuery->doesntHave('applications');
            }
        }

        $candidates = $candidatesQuery->latest()->get();

        // Группировка кандидатов по статусу их последней заявки для Kanban
        $kanbanColumns = [
            'new' => ['title' => 'Новые', 'status' => ApplicationStatus::New, 'candidates' => collect()],
            'in_review' => ['title' => 'На рассмотрении', 'status' => ApplicationStatus::InReview, 'candidates' => collect()],
            'invited' => ['title' => 'Приглашены', 'status' => ApplicationStatus::Invited, 'candidates' => collect()],
            'hired' => ['title' => 'Приняты', 'status' => ApplicationStatus::Hired, 'candidates' => collect()],
            'rejected' => ['title' => 'Отклонены', 'status' => ApplicationStatus::Rejected, 'candidates' => collect()],
            'no_applications' => ['title' => 'Без заявок', 'status' => null, 'candidates' => collect()],
        ];

        foreach ($candidates as $candidate) {
            if ($candidate->applications->isEmpty()) {
                $kanbanColumns['no_applications']['candidates']->push($candidate);
            } else {
                // Берем последнюю заявку кандидата
                $latestApplication = $candidate->applications->sortByDesc('created_at')->first();
                $status = $latestApplication->status->value;

                if (isset($kanbanColumns[$status])) {
                    $kanbanColumns[$status]['candidates']->push($candidate);
                }
            }
        }

        // Список вакансий для фильтра (только id и title)
        $vacancies = \App\Models\Vacancy::select('id', 'title')->orderBy('title')->get();

        return view('admin.candidates.index', compact('kanbanColumns', 'vacancies'));
    }

    /**
     * Просмотр кандидата
     */
    public function show(User $candidate): View
    {
        // Проверка что это кандидат
        if (!$candidate->isCandidate()) {
            abort(404);
        }

        $candidate->load(['applications.vacancy', 'candidateProfile']);

        return view('admin.candidates.show', compact('candidate'));
    }

    /**
     * Форма редактирования кандидата
     */
    public function edit(User $candidate): View
    {
        // Проверка что это кандидат
        if (!$candidate->isCandidate()) {
            abort(404);
        }

        return view('admin.candidates.edit', compact('candidate'));
    }

    /**
     * Обновление кандидата
     */
    public function update(Request $request, User $candidate): RedirectResponse
    {
        // Проверка что это кандидат
        if (!$candidate->isCandidate()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $candidate->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $candidate->update($validated);

        return redirect()->route('admin.candidates.show', $candidate)
            ->with('success', 'Данные кандидата обновлены.');
    }

    /**
     * Сброс пароля кандидата
     */
    public function resetPassword(Request $request, User $candidate): RedirectResponse
    {
        // Проверка что это кандидат
        if (!$candidate->isCandidate()) {
            abort(404);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $candidate->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Пароль кандидата сброшен.');
    }

    /**
     * Удаление кандидата
     */
    public function destroy(User $candidate): RedirectResponse
    {
        // Проверка что это кандидат
        if (!$candidate->isCandidate()) {
            abort(404);
        }

        $candidateName = $candidate->name;

        // Удаляем все заявки и связанные данные
        $candidate->delete();

        return redirect()->route('admin.candidates.index')
            ->with('success', "Кандидат {$candidateName} и все его заявки удалены.");
    }
}
