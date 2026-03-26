<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePointBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_points',
        'monthly_points',
        'quarterly_points',
        'yearly_points',
        'nominations_received',
        'nominations_given',
        'awards_won',
        'last_updated_at',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'monthly_points' => 'integer',
        'quarterly_points' => 'integer',
        'yearly_points' => 'integer',
        'nominations_received' => 'integer',
        'nominations_given' => 'integer',
        'awards_won' => 'integer',
        'last_updated_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ===== SCOPES =====

    public function scopeTopByTotal($query, int $limit = 10)
    {
        return $query->orderByDesc('total_points')->limit($limit);
    }

    public function scopeTopByMonthly($query, int $limit = 10)
    {
        return $query->orderByDesc('monthly_points')->limit($limit);
    }

    public function scopeTopByQuarterly($query, int $limit = 10)
    {
        return $query->orderByDesc('quarterly_points')->limit($limit);
    }

    public function scopeTopByYearly($query, int $limit = 10)
    {
        return $query->orderByDesc('yearly_points')->limit($limit);
    }

    // ===== STATIC HELPERS =====

    public static function getOrCreate(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'total_points' => 0,
                'monthly_points' => 0,
                'quarterly_points' => 0,
                'yearly_points' => 0,
                'nominations_received' => 0,
                'nominations_given' => 0,
                'awards_won' => 0,
            ]
        );
    }

    public static function updateBalance(int $userId): self
    {
        $balance = self::getOrCreate($userId);

        // Считаем баллы за разные периоды
        $balance->total_points = EmployeePoint::where('user_id', $userId)->sum('points');
        $balance->monthly_points = EmployeePoint::where('user_id', $userId)->thisMonth()->sum('points');
        $balance->quarterly_points = EmployeePoint::where('user_id', $userId)->thisQuarter()->sum('points');
        $balance->yearly_points = EmployeePoint::where('user_id', $userId)->thisYear()->sum('points');

        // Считаем номинации
        $balance->nominations_received = Nomination::where('nominee_id', $userId)
            ->where('status', 'approved')
            ->count();
        $balance->nominations_given = Nomination::where('nominator_id', $userId)->count();

        // Считаем награды
        $balance->awards_won = RecognitionAward::where('user_id', $userId)->count();

        $balance->last_updated_at = now();
        $balance->save();

        return $balance;
    }

    public static function recalculateAll(): int
    {
        $userIds = User::pluck('id');
        $count = 0;

        foreach ($userIds as $userId) {
            self::updateBalance($userId);
            $count++;
        }

        return $count;
    }

    // ===== ACCESSORS =====

    public function getRankAttribute(): int
    {
        return self::where('total_points', '>', $this->total_points)->count() + 1;
    }

    public function getMonthlyRankAttribute(): int
    {
        return self::where('monthly_points', '>', $this->monthly_points)->count() + 1;
    }
}
