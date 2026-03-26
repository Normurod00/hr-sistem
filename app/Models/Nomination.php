<?php

namespace App\Models;

use App\Enums\NominationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nomination extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomination_type_id',
        'nominee_id',
        'nominator_id',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_comment',
        'period_type',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'status' => NominationStatus::class,
        'period_start' => 'date',
        'period_end' => 'date',
        'reviewed_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    public function nominationType(): BelongsTo
    {
        return $this->belongsTo(NominationType::class);
    }

    public function nominee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nominee_id');
    }

    public function nominator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nominator_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ===== SCOPES =====

    public function scopePending($query)
    {
        return $query->where('status', NominationStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', NominationStatus::Approved);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', NominationStatus::Rejected);
    }

    public function scopeForPeriod($query, string $periodStart, ?string $periodEnd = null)
    {
        $query->where('period_start', $periodStart);

        if ($periodEnd) {
            $query->where('period_end', $periodEnd);
        }

        return $query;
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

    public function approve(int $reviewerId, ?string $comment = null): bool
    {
        return $this->update([
            'status' => NominationStatus::Approved,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_comment' => $comment,
        ]);
    }

    public function reject(int $reviewerId, ?string $comment = null): bool
    {
        return $this->update([
            'status' => NominationStatus::Rejected,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_comment' => $comment,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === NominationStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === NominationStatus::Approved;
    }

    // ===== STATIC HELPERS =====

    public static function getCurrentPeriod(string $periodType = 'month'): array
    {
        $now = now();

        return match ($periodType) {
            'quarter' => [
                'start' => $now->copy()->startOfQuarter()->toDateString(),
                'end' => $now->copy()->endOfQuarter()->toDateString(),
            ],
            'year' => [
                'start' => $now->copy()->startOfYear()->toDateString(),
                'end' => $now->copy()->endOfYear()->toDateString(),
            ],
            default => [
                'start' => $now->copy()->startOfMonth()->toDateString(),
                'end' => $now->copy()->endOfMonth()->toDateString(),
            ],
        };
    }
}
