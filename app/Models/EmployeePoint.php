<?php

namespace App\Models;

use App\Enums\PointSourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'points',
        'source_type',
        'source_id',
        'description',
        'awarded_by',
    ];

    protected $casts = [
        'points' => 'integer',
        'source_type' => PointSourceType::class,
    ];

    // ===== RELATIONSHIPS =====

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function awardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    // ===== SCOPES =====

    public function scopePositive($query)
    {
        return $query->where('points', '>', 0);
    }

    public function scopeNegative($query)
    {
        return $query->where('points', '<', 0);
    }

    public function scopeBySource($query, PointSourceType $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeThisQuarter($query)
    {
        $start = now()->startOfQuarter();
        $end = now()->endOfQuarter();

        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('created_at', now()->year);
    }

    // ===== STATIC HELPERS =====

    public static function award(
        int $userId,
        int $points,
        PointSourceType $sourceType,
        string $description,
        ?int $sourceId = null,
        ?int $awardedBy = null
    ): self {
        $point = self::create([
            'user_id' => $userId,
            'points' => $points,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'description' => $description,
            'awarded_by' => $awardedBy,
        ]);

        // Обновляем баланс
        EmployeePointBalance::updateBalance($userId);

        return $point;
    }

    public static function deduct(
        int $userId,
        int $points,
        string $description,
        ?int $awardedBy = null
    ): self {
        return self::award(
            $userId,
            -abs($points),
            PointSourceType::Adjustment,
            $description,
            null,
            $awardedBy
        );
    }
}
