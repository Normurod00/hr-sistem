<?php

namespace App\Services;

use App\Enums\AwardType;
use App\Enums\NominationStatus;
use App\Enums\PointSourceType;
use App\Models\EmployeePoint;
use App\Models\EmployeePointBalance;
use App\Models\Nomination;
use App\Models\NominationType;
use App\Models\RecognitionAward;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecognitionService
{
    /**
     * Создать номинацию
     */
    public function createNomination(
        int $nominatorId,
        int $nomineeId,
        int $nominationTypeId,
        string $reason,
        string $periodType = 'month'
    ): Nomination {
        $period = Nomination::getCurrentPeriod($periodType);

        $nomination = Nomination::create([
            'nomination_type_id' => $nominationTypeId,
            'nominee_id' => $nomineeId,
            'nominator_id' => $nominatorId,
            'reason' => $reason,
            'status' => NominationStatus::Pending,
            'period_type' => $periodType,
            'period_start' => $period['start'],
            'period_end' => $period['end'],
        ]);

        // Начисляем баллы за номинирование
        EmployeePoint::award(
            $nominatorId,
            PointSourceType::NominationGiven->defaultPoints(),
            PointSourceType::NominationGiven,
            'Номинация берилди',
            $nomination->id
        );

        Log::info('Nomination created', [
            'nomination_id' => $nomination->id,
            'nominator' => $nominatorId,
            'nominee' => $nomineeId,
        ]);

        return $nomination;
    }

    /**
     * Одобрить номинацию
     */
    public function approveNomination(Nomination $nomination, int $reviewerId, ?string $comment = null): bool
    {
        $result = $nomination->approve($reviewerId, $comment);

        if ($result) {
            // Начисляем баллы номинированному
            $nominationType = $nomination->nominationType;

            EmployeePoint::award(
                $nomination->nominee_id,
                $nominationType->points_reward,
                PointSourceType::NominationWin,
                "Номинация тасдиқланди: {$nominationType->name}",
                $nomination->id
            );
        }

        return $result;
    }

    /**
     * Получить лидерборд
     */
    public function getLeaderboard(string $period = 'total', int $limit = 10): Collection
    {
        $query = EmployeePointBalance::with('user');

        $query = match ($period) {
            'month' => $query->topByMonthly($limit),
            'quarter' => $query->topByQuarterly($limit),
            'year' => $query->topByYearly($limit),
            default => $query->topByTotal($limit),
        };

        return $query->get()->map(function ($balance, $index) use ($period) {
            $points = match ($period) {
                'month' => $balance->monthly_points,
                'quarter' => $balance->quarterly_points,
                'year' => $balance->yearly_points,
                default => $balance->total_points,
            };

            return [
                'rank' => $index + 1,
                'user' => $balance->user,
                'points' => $points,
                'nominations_received' => $balance->nominations_received,
                'awards_won' => $balance->awards_won,
            ];
        });
    }

    /**
     * Получить статистику для dashboard
     */
    public function getDashboardStats(): array
    {
        $currentMonth = now()->startOfMonth()->toDateString();
        $currentQuarter = now()->startOfQuarter()->toDateString();
        $currentYear = now()->startOfYear()->toDateString();

        return [
            'total_nominations_this_month' => Nomination::where('period_start', $currentMonth)->count(),
            'approved_nominations_this_month' => Nomination::where('period_start', $currentMonth)
                ->where('status', NominationStatus::Approved)->count(),
            'pending_nominations' => Nomination::where('status', NominationStatus::Pending)->count(),
            'active_employees' => EmployeePointBalance::where('total_points', '>', 0)->count(),
            'employee_of_month' => RecognitionAward::getEmployeeOfMonth(),
            'employee_of_quarter' => RecognitionAward::getEmployeeOfQuarter(),
            'employee_of_year' => RecognitionAward::getEmployeeOfYear(),
            'top_performers_month' => $this->getLeaderboard('month', 5),
            'nomination_types' => NominationType::active()->ordered()->get(),
        ];
    }

    /**
     * Получить кандидатов на награду
     */
    public function getAwardCandidates(AwardType $awardType, ?int $nominationTypeId = null): Collection
    {
        $periodType = $awardType->periodType();
        $period = Nomination::getCurrentPeriod($periodType);

        $query = Nomination::where('status', NominationStatus::Approved)
            ->where('period_start', $period['start'])
            ->select('nominee_id')
            ->selectRaw('COUNT(*) as nominations_count')
            ->groupBy('nominee_id')
            ->orderByDesc('nominations_count');

        if ($nominationTypeId) {
            $query->where('nomination_type_id', $nominationTypeId);
        }

        return $query->with('nominee')->get()->map(function ($item) {
            $balance = EmployeePointBalance::where('user_id', $item->nominee_id)->first();

            return [
                'user' => User::find($item->nominee_id),
                'nominations_count' => $item->nominations_count,
                'total_points' => $balance?->total_points ?? 0,
                'monthly_points' => $balance?->monthly_points ?? 0,
            ];
        });
    }

    /**
     * Присудить награду
     */
    public function grantAward(
        int $userId,
        AwardType $awardType,
        int $awardedById,
        ?int $nominationTypeId = null,
        ?string $description = null
    ): RecognitionAward {
        $periodType = $awardType->periodType();
        $period = Nomination::getCurrentPeriod($periodType);

        $user = User::findOrFail($userId);

        // Считаем номинации за период
        $nominationsCount = Nomination::where('nominee_id', $userId)
            ->where('status', NominationStatus::Approved)
            ->where('period_start', $period['start'])
            ->when($nominationTypeId, fn($q) => $q->where('nomination_type_id', $nominationTypeId))
            ->count();

        // Получаем KPI если есть
        $kpiScore = null;
        if ($user->employeeProfile) {
            $latestKpi = $user->employeeProfile->getLatestKpiSnapshot();
            $kpiScore = $latestKpi?->total_score;
        }

        // Формируем заголовок
        $title = $awardType->label() . ' - ' . match ($awardType) {
            AwardType::EmployeeOfMonth => now()->translatedFormat('F Y'),
            AwardType::EmployeeOfQuarter => 'Q' . now()->quarter . ' ' . now()->year,
            AwardType::EmployeeOfYear => (string) now()->year,
        };

        $award = RecognitionAward::create([
            'user_id' => $userId,
            'nomination_type_id' => $nominationTypeId,
            'award_type' => $awardType,
            'title' => $title,
            'description' => $description,
            'points_awarded' => $awardType->pointsReward(),
            'nominations_count' => $nominationsCount,
            'kpi_score' => $kpiScore,
            'period_start' => $period['start'],
            'period_end' => $period['end'],
            'awarded_by' => $awardedById,
            'is_published' => false,
        ]);

        // Начисляем баллы
        EmployeePoint::award(
            $userId,
            $awardType->pointsReward(),
            PointSourceType::AwardWin,
            "Мукофот: {$title}",
            $award->id,
            $awardedById
        );

        Log::info('Award granted', [
            'award_id' => $award->id,
            'user_id' => $userId,
            'type' => $awardType->value,
        ]);

        return $award;
    }

    /**
     * Автоматически определить победителя
     */
    public function autoSelectWinner(AwardType $awardType, ?int $nominationTypeId = null): ?array
    {
        $candidates = $this->getAwardCandidates($awardType, $nominationTypeId);

        if ($candidates->isEmpty()) {
            return null;
        }

        // Сортируем по количеству номинаций, затем по баллам
        $winner = $candidates->sortByDesc(function ($candidate) {
            return ($candidate['nominations_count'] * 1000) + $candidate['total_points'];
        })->first();

        return $winner;
    }

    /**
     * Получить историю наград сотрудника
     */
    public function getUserAwards(int $userId): Collection
    {
        return RecognitionAward::where('user_id', $userId)
            ->with('nominationType')
            ->orderByDesc('period_start')
            ->get();
    }

    /**
     * Получить номинации сотрудника
     */
    public function getUserNominations(int $userId, string $type = 'received'): Collection
    {
        $query = Nomination::with(['nominationType', 'nominator', 'nominee']);

        if ($type === 'received') {
            $query->where('nominee_id', $userId);
        } else {
            $query->where('nominator_id', $userId);
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Проверить, может ли пользователь номинировать
     */
    public function canNominate(int $nominatorId, int $nomineeId, int $nominationTypeId): array
    {
        // Нельзя номинировать себя
        if ($nominatorId === $nomineeId) {
            return ['can' => false, 'reason' => 'Ўзингизни номинация қила олмайсиз'];
        }

        // Проверяем, не номинировал ли уже в этом периоде
        $period = Nomination::getCurrentPeriod('month');
        $exists = Nomination::where('nomination_type_id', $nominationTypeId)
            ->where('nominee_id', $nomineeId)
            ->where('nominator_id', $nominatorId)
            ->where('period_start', $period['start'])
            ->exists();

        if ($exists) {
            return ['can' => false, 'reason' => 'Сиз бу ходимни ушбу номинацияда шу ой номинация қилгансиз'];
        }

        return ['can' => true, 'reason' => null];
    }
}
