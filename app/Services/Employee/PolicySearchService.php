<?php

namespace App\Services\Employee;

use App\Models\Policy;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Сервис поиска по политикам/регламентам
 */
class PolicySearchService
{
    /**
     * Поиск политик по запросу
     */
    public function search(
        string $query,
        ?string $category = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $builder = Policy::active();

        if ($category) {
            $builder->byCategory($category);
        }

        if (!empty($query)) {
            $builder->search($query);
        }

        return $builder
            ->orderBy('view_count', 'desc')
            ->orderBy('effective_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Найти наиболее релевантные политики для запроса
     */
    public function findRelevant(string $query, int $limit = 5): Collection
    {
        // Разбиваем запрос на ключевые слова
        $keywords = $this->extractKeywords($query);

        if (empty($keywords)) {
            return collect();
        }

        return Policy::active()
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('title', 'like', "%{$keyword}%")
                        ->orWhere('content', 'like', "%{$keyword}%")
                        ->orWhere('summary', 'like', "%{$keyword}%")
                        ->orWhereJsonContains('tags', $keyword);
                }
            })
            ->orderByRaw($this->buildRelevanceOrder($keywords))
            ->limit($limit)
            ->get();
    }

    /**
     * Получить политики по категории
     */
    public function getByCategory(string $category, int $limit = 10): Collection
    {
        return Policy::active()
            ->byCategory($category)
            ->orderBy('effective_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить популярные политики
     */
    public function getPopular(int $limit = 10): Collection
    {
        return Policy::active()
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить последние политики
     */
    public function getRecent(int $limit = 10): Collection
    {
        return Policy::active()
            ->orderBy('effective_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Найти политики по тегам
     */
    public function findByTags(array $tags, int $limit = 10): Collection
    {
        $query = Policy::active();

        foreach ($tags as $tag) {
            $query->orWhereJsonContains('tags', $tag);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Получить все категории с количеством политик
     */
    public function getCategoriesWithCounts(): array
    {
        $categories = Policy::getCategories();
        $counts = Policy::active()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $result = [];
        foreach ($categories as $key => $label) {
            $result[] = [
                'key' => $key,
                'label' => $label,
                'count' => $counts[$key] ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Получить контекст для AI из политик
     */
    public function getPolicyContextForAi(string $query): array
    {
        $policies = $this->findRelevant($query, 3);

        if ($policies->isEmpty()) {
            return [];
        }

        return $policies->map(function (Policy $policy) {
            return [
                'id' => $policy->id,
                'code' => $policy->code,
                'title' => $policy->title,
                'category' => $policy->category_label,
                'summary' => $policy->excerpt,
                'effective_date' => $policy->effective_date->format('d.m.Y'),
            ];
        })->toArray();
    }

    /**
     * Извлечь ключевые слова из запроса
     */
    private function extractKeywords(string $query): array
    {
        // Убираем стоп-слова
        $stopWords = [
            'как', 'что', 'где', 'когда', 'почему', 'какой', 'какая', 'какие',
            'можно', 'нужно', 'есть', 'для', 'при', 'без', 'или', 'если',
            'это', 'этот', 'эта', 'эти', 'тот', 'та', 'те',
            'the', 'is', 'are', 'was', 'were', 'be', 'been', 'being',
            'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
            'could', 'should', 'may', 'might', 'must', 'shall',
        ];

        // Разбиваем на слова
        $words = preg_split('/[\s,.\-:;!?]+/', mb_strtolower($query));

        // Фильтруем
        $keywords = array_filter($words, function ($word) use ($stopWords) {
            return mb_strlen($word) >= 3 && !in_array($word, $stopWords);
        });

        return array_values(array_unique($keywords));
    }

    /**
     * Построить ORDER BY для релевантности
     */
    private function buildRelevanceOrder(array $keywords): string
    {
        // SQLite/MySQL совместимый вариант
        $cases = [];

        foreach ($keywords as $keyword) {
            $escaped = addslashes($keyword);
            $cases[] = "(CASE WHEN title LIKE '%{$escaped}%' THEN 10 ELSE 0 END)";
            $cases[] = "(CASE WHEN summary LIKE '%{$escaped}%' THEN 5 ELSE 0 END)";
            $cases[] = "(CASE WHEN content LIKE '%{$escaped}%' THEN 1 ELSE 0 END)";
        }

        return '(' . implode(' + ', $cases) . ') DESC';
    }
}
