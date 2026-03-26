<?php

namespace App\Services;

use App\Models\Vacancy;
use App\Models\CandidateProfile;

class MatchingService
{
    protected array $weights;

    public function __construct()
    {
        $this->weights = config('ai.match_weights', [
            'must_have_skills' => 0.5,
            'nice_to_have_skills' => 0.3,
            'experience' => 0.2,
        ]);
    }

    /**
     * Рассчитать match score для кандидата и вакансии
     *
     * @param array $profile Профиль кандидата
     * @param Vacancy|array $vacancy Вакансия
     * @return int Score от 0 до 100
     */
    public function calculateMatchScore(array $profile, Vacancy|array $vacancy): int
    {
        $vacancyData = $vacancy instanceof Vacancy ? $vacancy->toAiFormat() : $vacancy;

        $mustHaveScore = $this->calculateSkillsCoverage(
            $profile['skills'] ?? [],
            $vacancyData['must_have_skills'] ?? []
        );

        $niceToHaveScore = $this->calculateSkillsCoverage(
            $profile['skills'] ?? [],
            $vacancyData['nice_to_have_skills'] ?? []
        );

        $experienceScore = $this->calculateExperienceScore(
            $profile['years_of_experience'] ?? 0,
            $vacancyData['min_experience_years'] ?? 0
        );

        $totalScore = 100 * (
            $this->weights['must_have_skills'] * $mustHaveScore +
            $this->weights['nice_to_have_skills'] * $niceToHaveScore +
            $this->weights['experience'] * $experienceScore
        );

        return (int) round(min(100, max(0, $totalScore)));
    }

    /**
     * Рассчитать детальный breakdown
     */
    public function calculateBreakdown(array $profile, Vacancy|array $vacancy): array
    {
        $vacancyData = $vacancy instanceof Vacancy ? $vacancy->toAiFormat() : $vacancy;

        $mustHaveSkills = $vacancyData['must_have_skills'] ?? [];
        $niceToHaveSkills = $vacancyData['nice_to_have_skills'] ?? [];
        $candidateSkills = $profile['skills'] ?? [];

        $mustHaveCoverage = $this->calculateSkillsCoverage($candidateSkills, $mustHaveSkills);
        $niceToHaveCoverage = $this->calculateSkillsCoverage($candidateSkills, $niceToHaveSkills);
        $experienceScore = $this->calculateExperienceScore(
            $profile['years_of_experience'] ?? 0,
            $vacancyData['min_experience_years'] ?? 0
        );

        $matchedMustHave = $this->getMatchedSkills($candidateSkills, $mustHaveSkills);
        $missingMustHave = $this->getMissingSkills($candidateSkills, $mustHaveSkills);
        $matchedNiceToHave = $this->getMatchedSkills($candidateSkills, $niceToHaveSkills);

        return [
            'total_score' => $this->calculateMatchScore($profile, $vacancy),

            'must_have' => [
                'score' => (int) round($mustHaveCoverage * 100),
                'weight' => $this->weights['must_have_skills'],
                'total' => count($mustHaveSkills),
                'matched' => count($matchedMustHave),
                'matched_skills' => $matchedMustHave,
                'missing_skills' => $missingMustHave,
            ],

            'nice_to_have' => [
                'score' => (int) round($niceToHaveCoverage * 100),
                'weight' => $this->weights['nice_to_have_skills'],
                'total' => count($niceToHaveSkills),
                'matched' => count($matchedNiceToHave),
                'matched_skills' => $matchedNiceToHave,
            ],

            'experience' => [
                'score' => (int) round($experienceScore * 100),
                'weight' => $this->weights['experience'],
                'candidate_years' => $profile['years_of_experience'] ?? 0,
                'required_years' => $vacancyData['min_experience_years'] ?? 0,
            ],

            'languages' => $this->calculateLanguageMatch(
                $profile['languages'] ?? [],
                $vacancyData['language_requirements'] ?? []
            ),
        ];
    }

    /**
     * Расчёт покрытия навыков
     */
    protected function calculateSkillsCoverage(array $candidateSkills, array $requiredSkills): float
    {
        if (empty($requiredSkills)) {
            return 1.0;
        }

        $candidateSkillNames = $this->normalizeSkillNames($candidateSkills);
        $matched = 0;

        foreach ($requiredSkills as $required) {
            $requiredName = is_array($required) ? ($required['name'] ?? $required) : $required;
            $requiredName = $this->normalizeSkillName($requiredName);

            if ($this->skillMatches($requiredName, $candidateSkillNames)) {
                $matched++;
            }
        }

        return $matched / count($requiredSkills);
    }

    /**
     * Расчёт оценки опыта
     */
    protected function calculateExperienceScore(float $candidateYears, float $requiredYears): float
    {
        if ($requiredYears <= 0) {
            return 1.0;
        }

        if ($candidateYears >= $requiredYears) {
            return 1.0;
        }

        return min(1.0, $candidateYears / $requiredYears);
    }

    /**
     * Расчёт соответствия языков
     */
    protected function calculateLanguageMatch(array $candidateLanguages, array $requiredLanguages): array
    {
        if (empty($requiredLanguages)) {
            return ['matched' => true, 'details' => []];
        }

        $details = [];
        $allMatched = true;

        foreach ($requiredLanguages as $required) {
            $requiredName = is_array($required) ? ($required['name'] ?? '') : $required;
            $requiredLevel = is_array($required) ? ($required['level'] ?? null) : null;

            $candidateLevel = $this->findLanguageLevel($candidateLanguages, $requiredName);

            $matched = $candidateLevel !== null;
            if ($matched && $requiredLevel) {
                $matched = $this->languageLevelSufficient($candidateLevel, $requiredLevel);
            }

            $details[] = [
                'language' => $requiredName,
                'required_level' => $requiredLevel,
                'candidate_level' => $candidateLevel,
                'matched' => $matched,
            ];

            if (!$matched) {
                $allMatched = false;
            }
        }

        return [
            'matched' => $allMatched,
            'details' => $details,
        ];
    }

    /**
     * Получить совпавшие навыки
     */
    protected function getMatchedSkills(array $candidateSkills, array $requiredSkills): array
    {
        $matched = [];
        $candidateSkillNames = $this->normalizeSkillNames($candidateSkills);

        foreach ($requiredSkills as $required) {
            $requiredName = is_array($required) ? ($required['name'] ?? $required) : $required;
            $normalizedRequired = $this->normalizeSkillName($requiredName);

            if ($this->skillMatches($normalizedRequired, $candidateSkillNames)) {
                $matched[] = $requiredName;
            }
        }

        return $matched;
    }

    /**
     * Получить недостающие навыки
     */
    protected function getMissingSkills(array $candidateSkills, array $requiredSkills): array
    {
        $missing = [];
        $candidateSkillNames = $this->normalizeSkillNames($candidateSkills);

        foreach ($requiredSkills as $required) {
            $requiredName = is_array($required) ? ($required['name'] ?? $required) : $required;
            $normalizedRequired = $this->normalizeSkillName($requiredName);

            if (!$this->skillMatches($normalizedRequired, $candidateSkillNames)) {
                $missing[] = $requiredName;
            }
        }

        return $missing;
    }

    /**
     * Нормализация имён навыков
     */
    protected function normalizeSkillNames(array $skills): array
    {
        $names = [];
        foreach ($skills as $skill) {
            $name = is_array($skill) ? ($skill['name'] ?? '') : $skill;
            $names[] = $this->normalizeSkillName($name);
        }
        return $names;
    }

    /**
     * Нормализация имени навыка
     */
    protected function normalizeSkillName(string $name): string
    {
        return mb_strtolower(trim($name));
    }

    /**
     * Проверка соответствия навыка
     */
    protected function skillMatches(string $required, array $candidateSkills): bool
    {
        foreach ($candidateSkills as $candidate) {
            // Точное совпадение
            if ($candidate === $required) {
                return true;
            }

            // Частичное совпадение (например, "javascript" содержит "js")
            if (str_contains($candidate, $required) || str_contains($required, $candidate)) {
                return true;
            }

            // Синонимы
            if ($this->areSkillsSynonyms($required, $candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверка синонимов навыков (расширенный список)
     */
    protected function areSkillsSynonyms(string $skill1, string $skill2): bool
    {
        $synonyms = [
            // Языки программирования
            ['javascript', 'js', 'ecmascript'],
            ['typescript', 'ts'],
            ['python', 'py', 'python3'],
            ['c#', 'csharp', 'c sharp'],
            ['c++', 'cpp', 'cplusplus'],
            ['golang', 'go'],

            // Базы данных
            ['postgresql', 'postgres', 'pgsql', 'psql'],
            ['mysql', 'mariadb'],
            ['mongodb', 'mongo'],
            ['elasticsearch', 'elastic'],
            ['mssql', 'sql server', 'microsoft sql'],

            // Фреймворки
            ['nodejs', 'node.js', 'node'],
            ['react', 'reactjs', 'react.js'],
            ['vue', 'vuejs', 'vue.js'],
            ['angular', 'angularjs', 'angular.js'],
            ['next.js', 'nextjs', 'next'],
            ['nuxt', 'nuxtjs', 'nuxt.js'],
            ['express', 'expressjs', 'express.js'],
            ['nestjs', 'nest.js', 'nest'],
            ['fastapi', 'fast api'],
            ['django', 'джанго'],
            ['laravel', 'ларавел'],
            ['spring', 'spring boot', 'springboot'],
            ['asp.net', 'aspnet', '.net core', 'dotnet'],

            // DevOps
            ['kubernetes', 'k8s'],
            ['docker', 'контейнер', 'контейнеризация'],
            ['gitlab ci', 'gitlab-ci', 'gitlab ci/cd'],
            ['github actions', 'github action'],

            // Cloud
            ['aws', 'amazon web services'],
            ['gcp', 'google cloud', 'google cloud platform'],
            ['azure', 'microsoft azure'],

            // Мобильная разработка
            ['react native', 'rn'],
            ['android', 'андроид'],
            ['ios', 'айос'],

            // Тестирование
            ['unit test', 'unit testing', 'юнит тест'],
            ['pytest', 'py.test'],
            ['jest', 'джест'],

            // ML/AI
            ['machine learning', 'ml', 'машинное обучение'],
            ['artificial intelligence', 'ai', 'искусственный интеллект'],
            ['tensorflow', 'tf'],
            ['pytorch', 'torch'],
        ];

        foreach ($synonyms as $group) {
            if (in_array($skill1, $group) && in_array($skill2, $group)) {
                return true;
            }
        }

        // Fuzzy matching для длинных строк (защита от опечаток)
        if (strlen($skill1) > 4 && strlen($skill2) > 4) {
            similar_text($skill1, $skill2, $percent);
            if ($percent >= 80) {
                return true;
            }
        }

        return false;
    }

    /**
     * Найти уровень языка кандидата
     */
    protected function findLanguageLevel(array $candidateLanguages, string $languageName): ?string
    {
        $languageName = mb_strtolower($languageName);

        foreach ($candidateLanguages as $lang) {
            $name = is_array($lang) ? mb_strtolower($lang['name'] ?? '') : mb_strtolower($lang);
            if ($name === $languageName || str_contains($name, $languageName)) {
                return is_array($lang) ? ($lang['level'] ?? null) : null;
            }
        }

        return null;
    }

    /**
     * Проверка достаточности уровня языка
     */
    protected function languageLevelSufficient(string $candidateLevel, string $requiredLevel): bool
    {
        $levels = ['a1' => 1, 'a2' => 2, 'b1' => 3, 'b2' => 4, 'c1' => 5, 'c2' => 6, 'native' => 7];

        $candidateScore = $levels[mb_strtolower($candidateLevel)] ?? 0;
        $requiredScore = $levels[mb_strtolower($requiredLevel)] ?? 0;

        return $candidateScore >= $requiredScore;
    }
}
