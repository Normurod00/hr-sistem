<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\ApplicationFile;
use App\Models\AiLog;
use App\Models\CandidateProfile;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Аналитика по кандидатам
     */
    public function candidates(Request $request): View
    {
        $period = $request->get('period', '30'); // days
        $since = now()->subDays((int) $period);

        // === KPI Cards ===
        $totalApplications = Application::count();
        $periodApplications = Application::where('created_at', '>=', $since)->count();
        $analyzedApplications = Application::whereNotNull('match_score')->count();
        $withAnalysis = ApplicationAnalysis::count();
        $avgMatchScore = Application::whereNotNull('match_score')->avg('match_score');
        $totalDocuments = ApplicationFile::count();
        $parsedDocuments = ApplicationFile::where('is_parsed', true)->count();
        $failedDocuments = ApplicationFile::where('is_parsed', false)
            ->whereRaw("created_at < NOW() - INTERVAL '1 hour'")
            ->count();

        $kpi = [
            'total_applications' => $totalApplications,
            'period_applications' => $periodApplications,
            'analyzed_applications' => $analyzedApplications,
            'with_analysis' => $withAnalysis,
            'avg_match_score' => round($avgMatchScore ?? 0, 1),
            'total_documents' => $totalDocuments,
            'parsed_documents' => $parsedDocuments,
            'failed_documents' => $failedDocuments,
            'analysis_coverage' => $totalApplications > 0
                ? round(($analyzedApplications / $totalApplications) * 100, 1)
                : 0,
            'parse_success_rate' => $totalDocuments > 0
                ? round(($parsedDocuments / $totalDocuments) * 100, 1)
                : 0,
        ];

        // === Status Distribution ===
        $statusDistribution = [];
        foreach (ApplicationStatus::cases() as $status) {
            $statusDistribution[] = [
                'status' => $status->label(),
                'count' => Application::where('status', $status)->count(),
                'color' => match ($status) {
                    ApplicationStatus::New => '#3b82f6',
                    ApplicationStatus::InReview => '#f59e0b',
                    ApplicationStatus::Invited => '#8b5cf6',
                    ApplicationStatus::Hired => '#22c55e',
                    ApplicationStatus::Rejected => '#ef4444',
                },
            ];
        }

        // === Match Score Distribution (histogram buckets) ===
        $scoreDistribution = DB::table('applications')
            ->whereNotNull('match_score')
            ->selectRaw("
                CASE
                    WHEN match_score >= 90 THEN '90-100'
                    WHEN match_score >= 80 THEN '80-89'
                    WHEN match_score >= 70 THEN '70-79'
                    WHEN match_score >= 60 THEN '60-69'
                    WHEN match_score >= 50 THEN '50-59'
                    WHEN match_score >= 40 THEN '40-49'
                    WHEN match_score >= 30 THEN '30-39'
                    ELSE '0-29'
                END as bucket,
                COUNT(*) as count
            ")
            ->groupBy('bucket')
            ->orderByRaw("MIN(match_score) DESC")
            ->get();

        // === Recommendation Distribution ===
        $recommendationDistribution = DB::table('application_analyses')
            ->select('recommendation', DB::raw('COUNT(*) as count'))
            ->groupBy('recommendation')
            ->orderByDesc('count')
            ->get();

        // === AI Operations Stats (last 7 days) ===
        $aiOpsStats = DB::table('ai_logs')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(
                'operation',
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success"),
                DB::raw("SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors"),
                DB::raw("AVG(duration_ms) as avg_duration")
            )
            ->groupBy('operation')
            ->get();

        // === Applications per day (trend) ===
        $trendDays = min((int) $period, 30);
        $applicationsTrend = [];
        for ($i = $trendDays - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $applicationsTrend[] = [
                'date' => $date->format('d.m'),
                'count' => Application::whereDate('created_at', $date->toDateString())->count(),
                'analyzed' => Application::whereDate('created_at', $date->toDateString())
                    ->whereNotNull('match_score')->count(),
            ];
        }

        // === Top vacancies by applications ===
        $topVacancies = Vacancy::withCount(['applications', 'applications as analyzed_count' => function ($q) {
                $q->whereNotNull('match_score');
            }])
            ->having('applications_count', '>', 0)
            ->orderByDesc('applications_count')
            ->limit(10)
            ->get()
            ->map(fn($v) => [
                'title' => $v->title,
                'applications' => $v->applications_count,
                'analyzed' => $v->analyzed_count,
                'avg_score' => round($v->applications()->whereNotNull('match_score')->avg('match_score') ?? 0, 1),
            ]);

        // === Conversion Funnel ===
        $funnel = [
            ['stage' => 'Всего заявок', 'count' => $totalApplications],
            ['stage' => 'AI проанализированы', 'count' => $analyzedApplications],
            ['stage' => 'На рассмотрении', 'count' => Application::where('status', ApplicationStatus::InReview)->count()],
            ['stage' => 'Приглашены', 'count' => Application::where('status', ApplicationStatus::Invited)->count()],
            ['stage' => 'Приняты', 'count' => Application::where('status', ApplicationStatus::Hired)->count()],
        ];

        return view('admin.analytics.candidates', compact(
            'kpi',
            'statusDistribution',
            'scoreDistribution',
            'recommendationDistribution',
            'aiOpsStats',
            'applicationsTrend',
            'topVacancies',
            'funnel',
            'period'
        ));
    }

    /**
     * Аналитика по сотрудникам
     */
    public function employees(Request $request): View
    {
        $totalEmployees = \App\Models\EmployeeProfile::count();
        $activeEmployees = \App\Models\EmployeeProfile::active()->count();
        $totalConversations = \App\Models\EmployeeAiConversation::count();
        $activeConversations = \App\Models\EmployeeAiConversation::active()->count();
        $totalRecommendations = \App\Models\EmployeeAiRecommendation::count();
        $completedRecommendations = \App\Models\EmployeeAiRecommendation::completed()->count();
        $pendingRecommendations = \App\Models\EmployeeAiRecommendation::pending()->count();

        // Department distribution
        $departmentDistribution = DB::table('employee_profiles')
            ->whereNull('deleted_at')
            ->select('department', DB::raw('COUNT(*) as count'))
            ->groupBy('department')
            ->orderByDesc('count')
            ->get();

        // AI conversation context types
        $contextTypes = DB::table('employee_ai_conversations')
            ->select('context_type', DB::raw('COUNT(*) as count'))
            ->groupBy('context_type')
            ->orderByDesc('count')
            ->get();

        // Recommendation types
        $recommendationTypes = DB::table('employee_ai_recommendations')
            ->select('type', 'status', DB::raw('COUNT(*) as count'))
            ->groupBy('type', 'status')
            ->get();

        // AI usage trend (last 30 days)
        $aiUsageTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $aiUsageTrend[] = [
                'date' => $date->format('d.m'),
                'conversations' => \App\Models\EmployeeAiConversation::whereDate('created_at', $date->toDateString())->count(),
                'messages' => \App\Models\EmployeeAiMessage::whereDate('created_at', $date->toDateString())->count(),
            ];
        }

        // Document analysis stats
        $totalDocs = \App\Models\EmployeeDocument::count();
        $parsedDocs = \App\Models\EmployeeDocument::where('status', 'parsed')->count();
        $failedDocs = \App\Models\EmployeeDocument::where('status', 'failed')->count();

        $docTypeDistribution = DB::table('employee_documents')
            ->select('document_type', DB::raw('COUNT(*) as count'))
            ->groupBy('document_type')
            ->orderByDesc('count')
            ->get();

        $kpi = [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'total_conversations' => $totalConversations,
            'active_conversations' => $activeConversations,
            'total_recommendations' => $totalRecommendations,
            'completed_recommendations' => $completedRecommendations,
            'pending_recommendations' => $pendingRecommendations,
            'recommendation_completion_rate' => $totalRecommendations > 0
                ? round(($completedRecommendations / $totalRecommendations) * 100, 1)
                : 0,
            'total_documents' => $totalDocs,
            'parsed_documents' => $parsedDocs,
            'failed_documents' => $failedDocs,
            'doc_parse_rate' => $totalDocs > 0 ? round(($parsedDocs / $totalDocs) * 100, 1) : 0,
        ];

        return view('admin.analytics.employees', compact(
            'kpi',
            'departmentDistribution',
            'contextTypes',
            'recommendationTypes',
            'aiUsageTrend',
            'docTypeDistribution'
        ));
    }
}
