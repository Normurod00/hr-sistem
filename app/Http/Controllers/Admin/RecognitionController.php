<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AwardType;
use App\Enums\NominationStatus;
use App\Http\Controllers\Controller;
use App\Models\EmployeePointBalance;
use App\Models\Nomination;
use App\Models\NominationType;
use App\Models\RecognitionAward;
use App\Services\RecognitionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecognitionController extends Controller
{
    public function __construct(
        protected RecognitionService $recognitionService
    ) {}

    /**
     * Dashboard признания (для HR)
     */
    public function index(): View
    {
        $stats = $this->recognitionService->getDashboardStats();

        return view('admin.recognition.index', [
            'stats' => $stats,
            'pendingNominations' => Nomination::pending()
                ->with(['nominee', 'nominator', 'nominationType'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
            'recentAwards' => RecognitionAward::recent(10)->with('user')->get(),
        ]);
    }

    /**
     * Управление номинациями
     */
    public function nominations(Request $request): View
    {
        $query = Nomination::with(['nominee', 'nominator', 'nominationType', 'reviewer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('nomination_type_id', $request->type);
        }

        if ($request->filled('period')) {
            $query->where('period_start', $request->period);
        }

        $nominations = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.recognition.nominations', [
            'nominations' => $nominations,
            'nominationTypes' => NominationType::all(),
            'filters' => $request->only(['status', 'type', 'period']),
        ]);
    }

    /**
     * Одобрить номинацию
     */
    public function approveNomination(Nomination $nomination, Request $request)
    {
        $comment = $request->input('comment');

        $this->recognitionService->approveNomination($nomination, auth()->id(), $comment);

        return back()->with('success', 'Номинация тасдиқланди');
    }

    /**
     * Отклонить номинацию
     */
    public function rejectNomination(Nomination $nomination, Request $request)
    {
        $request->validate([
            'comment' => 'required|string|min:5',
        ]);

        $nomination->reject(auth()->id(), $request->comment);

        return back()->with('success', 'Номинация рад этилди');
    }

    /**
     * Массовое одобрение
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'nomination_ids' => 'required|array',
            'nomination_ids.*' => 'exists:nominations,id',
        ]);

        $count = 0;
        foreach ($request->nomination_ids as $id) {
            $nomination = Nomination::find($id);
            if ($nomination && $nomination->isPending()) {
                $this->recognitionService->approveNomination($nomination, auth()->id());
                $count++;
            }
        }

        return back()->with('success', "{$count} та номинация тасдиқланди");
    }

    /**
     * Управление типами номинаций
     */
    public function nominationTypes(): View
    {
        return view('admin.recognition.nomination-types', [
            'types' => NominationType::ordered()->get(),
        ]);
    }

    /**
     * Создать тип номинации
     */
    public function storeNominationType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'name_uz' => 'nullable|string|max:100',
            'name_ru' => 'nullable|string|max:100',
            'slug' => 'required|string|max:50|unique:nomination_types',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'points_reward' => 'required|integer|min:0',
        ]);

        NominationType::create($validated);

        return back()->with('success', 'Номинация тури яратилди');
    }

    /**
     * Обновить тип номинации
     */
    public function updateNominationType(NominationType $nominationType, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'name_uz' => 'nullable|string|max:100',
            'name_ru' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'points_reward' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $nominationType->update($validated);

        return back()->with('success', 'Номинация тури янгиланди');
    }

    /**
     * Управление наградами
     */
    public function awards(Request $request): View
    {
        $query = RecognitionAward::with(['user', 'nominationType', 'awardedBy']);

        if ($request->filled('type')) {
            $query->where('award_type', $request->type);
        }

        $awards = $query->orderByDesc('period_start')->paginate(20);

        return view('admin.recognition.awards', [
            'awards' => $awards,
            'awardTypes' => AwardType::cases(),
        ]);
    }

    /**
     * Форма создания награды
     */
    public function createAward(Request $request): View
    {
        $awardType = $request->get('type', 'employee_of_month');
        $awardTypeEnum = AwardType::from($awardType);

        $candidates = $this->recognitionService->getAwardCandidates($awardTypeEnum);
        $autoWinner = $this->recognitionService->autoSelectWinner($awardTypeEnum);

        return view('admin.recognition.create-award', [
            'awardType' => $awardTypeEnum,
            'candidates' => $candidates,
            'autoWinner' => $autoWinner,
            'nominationTypes' => NominationType::active()->get(),
        ]);
    }

    /**
     * Сохранить награду
     */
    public function storeAward(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'award_type' => 'required|in:employee_of_month,employee_of_quarter,employee_of_year',
            'nomination_type_id' => 'nullable|exists:nomination_types,id',
            'description' => 'nullable|string',
            'publish_now' => 'boolean',
        ]);

        $award = $this->recognitionService->grantAward(
            $validated['user_id'],
            AwardType::from($validated['award_type']),
            auth()->id(),
            $validated['nomination_type_id'] ?? null,
            $validated['description'] ?? null
        );

        if ($request->boolean('publish_now')) {
            $award->publish();
        }

        return redirect()->route('admin.recognition.awards')
            ->with('success', 'Мукофот берилди: ' . $award->title);
    }

    /**
     * Опубликовать награду
     */
    public function publishAward(RecognitionAward $award)
    {
        $award->publish();

        return back()->with('success', 'Мукофот эълон қилинди');
    }

    /**
     * Снять публикацию награды
     */
    public function unpublishAward(RecognitionAward $award)
    {
        $award->unpublish();

        return back()->with('success', 'Мукофот яширилди');
    }

    /**
     * Лидерборд (для HR)
     */
    public function leaderboard(Request $request): View
    {
        $period = $request->get('period', 'month');

        return view('admin.recognition.leaderboard', [
            'leaderboard' => $this->recognitionService->getLeaderboard($period, 100),
            'currentPeriod' => $period,
            'totalEmployees' => EmployeePointBalance::count(),
        ]);
    }

    /**
     * Ручное начисление баллов
     */
    public function manualPoints(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:-1000|max:1000',
            'description' => 'required|string|max:255',
        ]);

        if ($validated['points'] >= 0) {
            \App\Models\EmployeePoint::award(
                $validated['user_id'],
                $validated['points'],
                \App\Enums\PointSourceType::Manual,
                $validated['description'],
                null,
                auth()->id()
            );
        } else {
            \App\Models\EmployeePoint::deduct(
                $validated['user_id'],
                abs($validated['points']),
                $validated['description'],
                auth()->id()
            );
        }

        return back()->with('success', 'Баллар киритилди');
    }

    /**
     * Пересчитать все балансы
     */
    public function recalculateBalances()
    {
        $count = EmployeePointBalance::recalculateAll();

        return back()->with('success', "{$count} та ходим баланси қайта ҳисобланди");
    }
}
