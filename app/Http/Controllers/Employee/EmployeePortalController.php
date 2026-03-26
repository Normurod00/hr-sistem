<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Employee\EmployeeKpiService;
use App\Services\Integrations\Kpi\KpiClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeePortalController extends Controller
{
    public function __construct(
        private readonly EmployeeKpiService $kpiService,
        private readonly KpiClient $kpiClient
    ) {}

    /**
     * Dashboard сотрудника
     */
    public function index(Request $request): View
    {
        $employee = auth()->user()->employeeProfile;

        // Если профиля нет - показываем заглушку
        if (!$employee) {
            return view('employee.dashboard', [
                'employee' => null,
                'dashboard' => null,
                'recentConversations' => collect(),
                'activeRecommendations' => collect(),
                'teamStats' => null,
            ]);
        }

        // Получаем дашборд KPI
        $dashboard = $this->kpiService->getDashboard($employee);

        // Последние разговоры с AI
        $recentConversations = $employee->aiConversations()
            ->active()
            ->recent()
            ->limit(5)
            ->get();

        // Активные рекомендации
        $activeRecommendations = $employee->aiRecommendations()
            ->whereIn('status', ['pending', 'in_progress'])
            ->byPriority()
            ->limit(5)
            ->get();

        // Статистика для менеджеров
        $teamStats = null;
        if ($employee->isManager()) {
            $teamStats = $this->kpiService->getTeamStats($employee);
        }

        return view('employee.dashboard', [
            'employee' => $employee,
            'dashboard' => $dashboard,
            'recentConversations' => $recentConversations,
            'activeRecommendations' => $activeRecommendations,
            'teamStats' => $teamStats,
        ]);
    }

    /**
     * Настройки профиля сотрудника
     */
    public function settings(Request $request): View
    {
        $employee = auth()->user()->employeeProfile;

        return view('employee.settings', [
            'employee' => $employee,
            'user' => auth()->user(),
        ]);
    }

    /**
     * Обновить настройки уведомлений
     */
    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'kpi_alerts' => 'boolean',
            'recommendation_reminders' => 'boolean',
        ]);

        auth()->user()->update([
            'notification_preferences' => $validated,
        ]);

        return back()->with('success', 'Настройки уведомлений обновлены');
    }

    /**
     * Команда менеджера
     */
    public function team(Request $request): View
    {
        $employee = auth()->user()->employeeProfile;

        if (!$employee) {
            abort(403, 'Профиль не найден');
        }

        $teamMembers = $employee->subordinates()
            ->with(['user', 'department'])
            ->paginate(20);

        return view('employee.team.index', [
            'employee' => $employee,
            'teamMembers' => $teamMembers,
        ]);
    }

    /**
     * Детали члена команды
     */
    public function teamMember(Request $request, $employeeId): View
    {
        $manager = auth()->user()->employeeProfile;

        if (!$manager) {
            abort(403, 'Профиль не найден');
        }

        $teamMember = $manager->subordinates()
            ->where('id', $employeeId)
            ->with(['user', 'department'])
            ->firstOrFail();

        // KPI данные подчиненного (только для менеджера)
        $kpiData = $this->kpiService->getDashboard($teamMember);

        return view('employee.team.show', [
            'manager' => $manager,
            'teamMember' => $teamMember,
            'kpiData' => $kpiData,
        ]);
    }
}
