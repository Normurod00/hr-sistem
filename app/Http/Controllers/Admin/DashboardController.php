<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Vacancy;
use App\Models\User;
use App\Models\AiLog;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Основная статистика
        $stats = [
            'vacancies_active' => Vacancy::active()->count(),
            'vacancies_total' => Vacancy::count(),
            'applications_total' => Application::count(),
            'applications_new' => Application::where('status', ApplicationStatus::New)->count(),
            'applications_in_review' => Application::where('status', ApplicationStatus::InReview)->count(),
            'applications_invited' => Application::where('status', ApplicationStatus::Invited)->count(),
            'applications_hired' => Application::where('status', ApplicationStatus::Hired)->count(),
            'applications_rejected' => Application::where('status', ApplicationStatus::Rejected)->count(),
            'candidates_total' => User::where('role', 'candidate')->count(),
        ];

        // Статистика за прошлый период (для сравнения)
        $lastWeekApplications = Application::where('created_at', '>=', now()->subWeeks(2))
            ->where('created_at', '<', now()->subWeek())
            ->count();
        $thisWeekApplications = Application::where('created_at', '>=', now()->subWeek())->count();

        $lastWeekHired = Application::where('status', ApplicationStatus::Hired)
            ->where('updated_at', '>=', now()->subWeeks(2))
            ->where('updated_at', '<', now()->subWeek())
            ->count();
        $thisWeekHired = Application::where('status', ApplicationStatus::Hired)
            ->where('updated_at', '>=', now()->subWeek())
            ->count();

        // Расчёт процентных изменений
        $changes = [
            'applications' => $this->calculateChange($lastWeekApplications, $thisWeekApplications),
            'hired' => $this->calculateChange($lastWeekHired, $thisWeekHired),
        ];

        // Последние заявки
        $recentApplications = Application::query()
            ->with(['candidate', 'vacancy'])
            ->latest()
            ->take(8)
            ->get();

        // Популярные вакансии
        $popularVacancies = Vacancy::query()
            ->active()
            ->withCount('applications')
            ->orderByDesc('applications_count')
            ->take(5)
            ->get();

        // Логи AI за последние 24 часа
        $aiStats = [
            'total' => AiLog::where('created_at', '>=', now()->subDay())->count(),
            'success' => AiLog::where('created_at', '>=', now()->subDay())
                ->where('status', 'success')->count(),
            'errors' => AiLog::where('created_at', '>=', now()->subDay())
                ->where('status', 'error')->count(),
        ];

        // Данные для графика заявок по дням (последние 14 дней)
        $applicationsChart = $this->getApplicationsChartData(14);

        // Данные для круговой диаграммы статусов
        $statusChart = [
            ['status' => 'Новые', 'count' => $stats['applications_new'], 'color' => '#3b82f6'],
            ['status' => 'На рассмотрении', 'count' => $stats['applications_in_review'], 'color' => '#f59e0b'],
            ['status' => 'Приглашены', 'count' => $stats['applications_invited'], 'color' => '#8b5cf6'],
            ['status' => 'Приняты', 'count' => $stats['applications_hired'], 'color' => '#22c55e'],
            ['status' => 'Отклонены', 'count' => $stats['applications_rejected'], 'color' => '#ef4444'],
        ];

        // Последняя активность
        $recentActivity = $this->getRecentActivity();

        // Канбан по откликам с последним AI-анализом
        $kanbanColumns = [
            ['key' => ApplicationStatus::New->value, 'title' => 'Новые', 'color' => '#3b82f6'],
            ['key' => ApplicationStatus::InReview->value, 'title' => 'На рассмотрении', 'color' => '#f59e0b'],
            ['key' => ApplicationStatus::Invited->value, 'title' => 'Приглашены', 'color' => '#8b5cf6'],
            ['key' => ApplicationStatus::Rejected->value, 'title' => 'Отклонены', 'color' => '#ef4444'],
            ['key' => ApplicationStatus::Hired->value, 'title' => 'Приняты', 'color' => '#22c55e'],
        ];
        $kanbanApplications = $this->getKanbanApplications();

        return view('admin.dashboard', compact(
            'stats',
            'changes',
            'recentApplications',
            'popularVacancies',
            'aiStats',
            'applicationsChart',
            'statusChart',
            'recentActivity',
            'kanbanColumns',
            'kanbanApplications'
        ));
    }

    /**
     * Расчёт процентного изменения
     */
    private function calculateChange(int $old, int $new): array
    {
        if ($old === 0) {
            return ['value' => $new > 0 ? 100 : 0, 'direction' => 'up'];
        }

        $change = round((($new - $old) / $old) * 100, 1);

        return [
            'value' => abs($change),
            'direction' => $change >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Данные для графика заявок по дням
     */
    private function getApplicationsChartData(int $days): array
    {
        $data = [];
        $labels = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d.m');

            $count = Application::whereDate('created_at', $date->toDateString())->count();
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Последняя активность в системе
     */
    private function getRecentActivity(): array
    {
        $activities = [];

        // Последние заявки
        $recentApps = Application::with(['candidate', 'vacancy'])
            ->latest()
            ->take(5)
            ->get();

        foreach ($recentApps as $app) {
            $activities[] = [
                'type' => 'application',
                'icon' => 'fa-file-lines',
                'color' => 'info',
                'title' => $app->candidate?->name ?? 'Кандидат',
                'description' => 'подал заявку на "' . ($app->vacancy?->title ?? 'вакансию') . '"',
                'time' => $app->created_at,
            ];
        }

        // Последние изменения статусов
        $statusChanges = Application::with(['candidate', 'vacancy'])
            ->where('status', '!=', ApplicationStatus::New)
            ->where('updated_at', '>=', now()->subDay())
            ->latest('updated_at')
            ->take(5)
            ->get();

        foreach ($statusChanges as $app) {
            $statusLabel = $app->status->label();
            $icon = match ($app->status) {
                ApplicationStatus::Hired => 'fa-user-check',
                ApplicationStatus::Rejected => 'fa-user-xmark',
                ApplicationStatus::Invited => 'fa-envelope',
                ApplicationStatus::InReview => 'fa-magnifying-glass',
                default => 'fa-circle',
            };
            $color = match ($app->status) {
                ApplicationStatus::Hired => 'success',
                ApplicationStatus::Rejected => 'danger',
                ApplicationStatus::Invited => 'purple',
                ApplicationStatus::InReview => 'warning',
                default => 'secondary',
            };

            $activities[] = [
                'type' => 'status',
                'icon' => $icon,
                'color' => $color,
                'title' => $app->candidate?->name ?? 'Кандидат',
                'description' => "статус изменён на \"{$statusLabel}\"",
                'time' => $app->updated_at,
            ];
        }

        // Сортируем по времени и берём последние 10
        usort($activities, fn($a, $b) => $b['time']->timestamp - $a['time']->timestamp);

        return array_slice($activities, 0, 10);
    }

    /**
     * Собирает карточки для AI-канбана: последний анализ, профиль и контакты кандидата
     */
    private function getKanbanApplications(): Collection
    {
        $applications = Application::with([
            'candidate',
            'candidate.candidateProfile',
            'vacancy',
            'latestAnalysis',
        ])
            ->latest()
            ->take(40)
            ->get();

        return $applications->map(function (Application $app) {
            $profile = $app->candidate?->candidateProfile;
            $analysis = $app->latestAnalysis;
            $contactInfo = $profile?->contact_info ?? [];

            return [
                'id' => $app->id,
                'status' => $app->status->value,
                'status_label' => $app->status_label,
                'name' => $app->candidate?->name ?? 'Кандидат',
                'vacancy' => $app->vacancy?->title ?? '—',
                'match_score' => $app->match_score,
                'position_title' => $profile?->position_title ?? null,
                'strong_skills' => $profile?->getStrongSkills() ?? [],
                'domains' => $profile?->domains ?? [],
                'contact' => [
                    'email' => $app->candidate?->email ?? ($contactInfo['email'] ?? null),
                    'phone' => $app->candidate?->phone ?? ($contactInfo['phone'] ?? null),
                    'pin' => $app->candidate?->pin ?? null,
                ],
                'analysis' => [
                    'strengths' => $analysis?->strengths ?? [],
                    'weaknesses' => $analysis?->weaknesses ?? [],
                    'risks' => $analysis?->risks ?? [],
                    'questions' => $analysis?->suggested_questions ?? [],
                    'recommendation' => $analysis?->recommendation ?? '',
                ],
            ];
        });
    }
}
