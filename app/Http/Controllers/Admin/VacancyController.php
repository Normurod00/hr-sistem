<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\EmploymentType;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacancyController extends Controller
{
    /**
     * Список всех вакансий
     */
    public function index(Request $request): View
    {
        $query = Vacancy::query()
            ->with('creator')
            ->withCount('applications')
            ->latest();

        // Поиск
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Фильтр по статусу
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $vacancies = $query->paginate(15)->withQueryString();

        return view('admin.vacancies.index', compact('vacancies'));
    }

    /**
     * Форма создания вакансии
     */
    public function create(): View
    {
        $employmentTypes = EmploymentType::cases();

        return view('admin.vacancies.create', compact('employmentTypes'));
    }

    /**
     * Сохранение новой вакансии
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateVacancy($request);
        $validated['created_by'] = auth()->id();

        $vacancy = Vacancy::create($validated);

        return redirect()->route('admin.vacancies.show', $vacancy)
            ->with('success', 'Вакансия успешно создана.');
    }

    /**
     * Просмотр вакансии с заявками
     */
    public function show(Vacancy $vacancy): View
    {
        $vacancy->load('creator');

        $applications = $vacancy->applications()
            ->with(['candidate', 'analysis'])
            ->latest()
            ->paginate(20);

        return view('admin.vacancies.show', compact('vacancy', 'applications'));
    }

    /**
     * Форма редактирования вакансии
     */
    public function edit(Vacancy $vacancy): View
    {
        $employmentTypes = EmploymentType::cases();

        return view('admin.vacancies.edit', compact('vacancy', 'employmentTypes'));
    }

    /**
     * Обновление вакансии
     */
    public function update(Request $request, Vacancy $vacancy): RedirectResponse
    {
        $validated = $this->validateVacancy($request);

        $vacancy->update($validated);

        return redirect()->route('admin.vacancies.show', $vacancy)
            ->with('success', 'Вакансия успешно обновлена.');
    }

    /**
     * Удаление вакансии
     */
    public function destroy(Vacancy $vacancy): RedirectResponse
    {
        // Проверяем, нет ли заявок
        if ($vacancy->applications()->exists()) {
            return back()->with('error', 'Невозможно удалить вакансию с заявками.');
        }

        $vacancy->delete();

        return redirect()->route('admin.vacancies.index')
            ->with('success', 'Вакансия удалена.');
    }

    /**
     * Переключение активности вакансии
     */
    public function toggleActive(Vacancy $vacancy): RedirectResponse
    {
        $vacancy->update(['is_active' => !$vacancy->is_active]);

        $status = $vacancy->is_active ? 'активирована' : 'деактивирована';

        return back()->with('success', "Вакансия {$status}.");
    }

    /**
     * Валидация вакансии
     */
    protected function validateVacancy(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'must_have_skills' => ['nullable', 'array'],
            'must_have_skills.*' => ['string'],
            'nice_to_have_skills' => ['nullable', 'array'],
            'nice_to_have_skills.*' => ['string'],
            'min_experience_years' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'language_requirements' => ['nullable', 'array'],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0', 'gte:salary_min'],
            'location' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'string'],
            'is_active' => ['boolean'],
        ], [
            'title.required' => 'Введите название вакансии',
            'description.required' => 'Введите описание вакансии',
            'employment_type.required' => 'Выберите тип занятости',
            'salary_max.gte' => 'Максимальная зарплата должна быть больше минимальной',
        ]);
    }
}
