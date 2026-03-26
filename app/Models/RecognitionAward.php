<?php

namespace App\Models;

use App\Enums\AwardType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecognitionAward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nomination_type_id',
        'award_type',
        'title',
        'description',
        'points_awarded',
        'nominations_count',
        'kpi_score',
        'period_start',
        'period_end',
        'awarded_by',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'award_type' => AwardType::class,
        'period_start' => 'date',
        'period_end' => 'date',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'points_awarded' => 'integer',
        'nominations_count' => 'integer',
        'kpi_score' => 'decimal:2',
    ];

    // ===== RELATIONSHIPS =====

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nominationType(): BelongsTo
    {
        return $this->belongsTo(NominationType::class);
    }

    public function awardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    // ===== SCOPES =====

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByType($query, AwardType $type)
    {
        return $query->where('award_type', $type);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('period_start')->limit($limit);
    }

    public function scopeCurrentMonth($query)
    {
        return $query->where('period_start', now()->startOfMonth()->toDateString());
    }

    public function scopeCurrentQuarter($query)
    {
        return $query->where('period_start', now()->startOfQuarter()->toDateString());
    }

    public function scopeCurrentYear($query)
    {
        return $query->where('period_start', now()->startOfYear()->toDateString());
    }

    // ===== HELPERS =====

    public function publish(): bool
    {
        return $this->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublish(): bool
    {
        return $this->update([
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    // ===== ACCESSORS =====

    public function getPeriodLabelAttribute(): string
    {
        $start = $this->period_start;

        return match ($this->award_type) {
            AwardType::EmployeeOfMonth => $start->translatedFormat('F Y'),
            AwardType::EmployeeOfQuarter => 'Q' . $start->quarter . ' ' . $start->year,
            AwardType::EmployeeOfYear => (string) $start->year,
        };
    }

    // ===== STATIC HELPERS =====

    public static function getLatestByType(AwardType $type): ?self
    {
        return self::where('award_type', $type)
            ->published()
            ->orderByDesc('period_start')
            ->first();
    }

    public static function getEmployeeOfMonth(): ?self
    {
        return self::getLatestByType(AwardType::EmployeeOfMonth);
    }

    public static function getEmployeeOfQuarter(): ?self
    {
        return self::getLatestByType(AwardType::EmployeeOfQuarter);
    }

    public static function getEmployeeOfYear(): ?self
    {
        return self::getLatestByType(AwardType::EmployeeOfYear);
    }
}
