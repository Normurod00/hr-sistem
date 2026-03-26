<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeePointBalance;
use App\Models\Nomination;
use App\Models\NominationType;
use App\Models\RecognitionAward;
use App\Models\User;
use App\Services\RecognitionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecognitionController extends Controller
{
    public function __construct(
        protected RecognitionService $recognitionService
    ) {}

    /**
     * Главная страница эътироф (Live Dashboard)
     */
    public function index(): View
    {
        $stats = $this->recognitionService->getDashboardStats();

        return view('employee.recognition.index', [
            'stats' => $stats,
            'leaderboardMonth' => $this->recognitionService->getLeaderboard('month', 10),
            'leaderboardTotal' => $this->recognitionService->getLeaderboard('total', 10),
            'recentAwards' => RecognitionAward::published()->recent(5)->with('user')->get(),
        ]);
    }

    /**
     * Страница лидерборда
     */
    public function leaderboard(Request $request): View
    {
        $period = $request->get('period', 'month');
        $limit = min((int) $request->get('limit', 50), 100);

        return view('employee.recognition.leaderboard', [
            'leaderboard' => $this->recognitionService->getLeaderboard($period, $limit),
            'currentPeriod' => $period,
            'userBalance' => EmployeePointBalance::where('user_id', auth()->id())->first(),
        ]);
    }

    /**
     * Форма номинирования
     */
    public function nominate(): View
    {
        $employees = User::whereHas('employeeProfile')
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get();

        return view('employee.recognition.nominate', [
            'nominationTypes' => NominationType::active()->ordered()->get(),
            'employees' => $employees,
        ]);
    }

    /**
     * Сохранить номинацию
     */
    public function storeNomination(Request $request)
    {
        $validated = $request->validate([
            'nominee_id' => 'required|exists:users,id',
            'nomination_type_id' => 'required|exists:nomination_types,id',
            'reason' => 'required|string|min:10|max:1000',
        ]);

        // Проверяем возможность номинирования
        $check = $this->recognitionService->canNominate(
            auth()->id(),
            $validated['nominee_id'],
            $validated['nomination_type_id']
        );

        if (!$check['can']) {
            return back()->withErrors(['nominee_id' => $check['reason']])->withInput();
        }

        $nomination = $this->recognitionService->createNomination(
            auth()->id(),
            $validated['nominee_id'],
            $validated['nomination_type_id'],
            $validated['reason']
        );

        return redirect()->route('employee.recognition.my-nominations')
            ->with('success', 'Номинация муваффақиятли юборилди!');
    }

    /**
     * Мои номинации
     */
    public function myNominations(): View
    {
        $userId = auth()->id();

        return view('employee.recognition.my-nominations', [
            'nominationsReceived' => $this->recognitionService->getUserNominations($userId, 'received'),
            'nominationsGiven' => $this->recognitionService->getUserNominations($userId, 'given'),
            'myAwards' => $this->recognitionService->getUserAwards($userId),
            'myBalance' => EmployeePointBalance::getOrCreate($userId),
        ]);
    }

    /**
     * Мои баллы (история)
     */
    public function myPoints(): View
    {
        $userId = auth()->id();
        $balance = EmployeePointBalance::getOrCreate($userId);

        return view('employee.recognition.my-points', [
            'balance' => $balance,
            'history' => \App\Models\EmployeePoint::where('user_id', $userId)
                ->orderByDesc('created_at')
                ->paginate(20),
        ]);
    }

    /**
     * Профиль сотрудника в системе признания
     */
    public function profile(User $user): View
    {
        $balance = EmployeePointBalance::where('user_id', $user->id)->first();

        return view('employee.recognition.profile', [
            'user' => $user,
            'balance' => $balance,
            'awards' => $this->recognitionService->getUserAwards($user->id),
            'nominationsReceived' => Nomination::where('nominee_id', $user->id)
                ->where('status', 'approved')
                ->with('nominationType')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
        ]);
    }

    /**
     * Зал славы (все награды)
     */
    public function hallOfFame(): View
    {
        return view('employee.recognition.hall-of-fame', [
            'employeesOfMonth' => RecognitionAward::byType(\App\Enums\AwardType::EmployeeOfMonth)
                ->published()
                ->recent(12)
                ->with('user')
                ->get(),
            'employeesOfQuarter' => RecognitionAward::byType(\App\Enums\AwardType::EmployeeOfQuarter)
                ->published()
                ->recent(4)
                ->with('user')
                ->get(),
            'employeesOfYear' => RecognitionAward::byType(\App\Enums\AwardType::EmployeeOfYear)
                ->published()
                ->recent(5)
                ->with('user')
                ->get(),
        ]);
    }
}
