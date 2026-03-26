<?php

namespace App\Http\Controllers;

use App\Models\Vacancy;
use App\Enums\EmploymentType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacancyController extends Controller
{
    /**
     * Список активных вакансий (публичная страница)
     */
    public function index(Request $request): View
    {
        $query = Vacancy::query()
            ->active()
            ->with('creator')
            ->latest();

        // Поиск
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Фильтр по типу занятости
        if ($type = $request->input('type')) {
            $employmentType = EmploymentType::tryFrom($type);
            if ($employmentType) {
                $query->byType($employmentType);
            }
        }

        // Фильтр по локации
        if ($location = $request->input('location')) {
            $query->where('location', 'like', "%{$location}%");
        }

        $vacancies = $query->paginate(12)->withQueryString();

        $employmentTypes = EmploymentType::cases();

        return view('vacancies.index', compact('vacancies', 'employmentTypes'));
    }

    /**
     * Детальная страница вакансии
     */
    public function show(Vacancy $vacancy): View
    {
        // Проверяем, что вакансия активна
        if (!$vacancy->is_active) {
            abort(404);
        }

        $vacancy->load('creator');

        // Проверяем, подавал ли текущий пользователь заявку
        $hasApplied = false;
        $application = null;

        if (auth()->check() && auth()->user()->isCandidate()) {
            $application = $vacancy->applications()
                ->where('user_id', auth()->id())
                ->first();
            $hasApplied = $application !== null;
        }

        return view('vacancies.show', compact('vacancy', 'hasApplied', 'application'));
    }
}
