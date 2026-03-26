<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Policy;
use App\Services\Employee\PolicySearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeePolicyController extends Controller
{
    public function __construct(
        private readonly PolicySearchService $policyService
    ) {}

    /**
     * Список политик
     */
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $query = $request->query('q', '');

        $policies = $this->policyService->search($query, $category, 15);
        $categories = $this->policyService->getCategoriesWithCounts();
        $popular = $this->policyService->getPopular(5);
        $recent = $this->policyService->getRecent(5);

        return view('employee.policies.index', [
            'policies' => $policies,
            'categories' => $categories,
            'popular' => $popular,
            'recent' => $recent,
            'currentCategory' => $category,
            'searchQuery' => $query,
        ]);
    }

    /**
     * Показать политику
     */
    public function show(Request $request, Policy $policy): View
    {
        if (!$policy->is_effective) {
            abort(404, 'Политика не действует');
        }

        // Увеличиваем счётчик просмотров
        $policy->incrementViewCount();

        // Логируем
        AuditLog::logView('policy', $policy->id);

        // Находим связанные политики
        $related = Policy::active()
            ->where('id', '!=', $policy->id)
            ->where('category', $policy->category)
            ->limit(5)
            ->get();

        return view('employee.policies.show', [
            'policy' => $policy,
            'related' => $related,
        ]);
    }

    /**
     * Поиск политик (AJAX)
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        $category = $request->query('category');

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $policies = $this->policyService->search($query, $category, 10);

        return response()->json([
            'results' => $policies->items(),
            'total' => $policies->total(),
        ]);
    }

    /**
     * Скачать файл политики
     */
    public function download(Request $request, Policy $policy)
    {
        if (!$policy->hasFile()) {
            abort(404, 'Файл не найден');
        }

        // Логируем
        AuditLog::log('download', 'policy', $policy->id);

        return \Storage::download($policy->file_path, $policy->code . '.pdf');
    }
}
