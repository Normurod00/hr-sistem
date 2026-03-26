<?php

namespace App\Models;

use App\Enums\KpiPeriodType;
use App\Enums\KpiStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeKpiSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'period_type',
        'period_start',
        'period_end',
        'metrics',
        'total_score',
        'status',
        'bonus_info',
        'synced_at',
        'raw_response',
        'notes',
    ];

    protected $casts = [
        'period_type' => KpiPeriodType::class,
        'period_start' => 'date',
        'period_end' => 'date',
        'metrics' => 'array',
        'total_score' => 'decimal:2',
        'status' => KpiStatus::class,
        'bonus_info' => 'array',
        'synced_at' => 'datetime',
        'raw_response' => 'array',
    ];

    // ===== RELATIONSHIPS =====

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(EmployeeAiRecommendation::class, 'kpi_snapshot_id');
    }

    // ===== SCOPES =====

    public function scopeByPeriodType($query, KpiPeriodType $type)
    {
        return $query->where('period_type', $type);
    }

    public function scopeRecent($query, int $months = 12)
    {
        return $query->where('period_start', '>=', now()->subMonths($months));
    }

    public function scopeApproved($query)
    {
        return $query->where('status', KpiStatus::Approved);
    }

    // ===== ACCESSORS =====

    public function getPeriodLabelAttribute(): string
    {
        return match ($this->period_type) {
            KpiPeriodType::Month => $this->period_start->format('F Y'),
            KpiPeriodType::Quarter => 'Q' . ceil($this->period_start->month / 3) . ' ' . $this->period_start->year,
            KpiPeriodType::HalfYear => ($this->period_start->month <= 6 ? 'H1' : 'H2') . ' ' . $this->period_start->year,
            KpiPeriodType::Year => $this->period_start->format('Y'),
            default => $this->period_start->format('Y-m-d'),
        };
    }

    public function getScoreColorAttribute(): string
    {
        return match (true) {
            $this->total_score >= 90 => 'success',
            $this->total_score >= 70 => 'info',
            $this->total_score >= 50 => 'warning',
            default => 'danger',
        };
    }

    public function getScoreLabelAttribute(): string
    {
        return match (true) {
            $this->total_score >= 90 => 'Отлично',
            $this->total_score >= 70 => 'Хорошо',
            $this->total_score >= 50 => 'Удовлетворительно',
            default => 'Требует улучшения',
        };
    }

    // ===== HELPERS =====

    public function getMetric(string $key): ?array
    {
        return $this->metrics[$key] ?? null;
    }

    public function getMetricValue(string $key): float
    {
        return $this->metrics[$key]['value'] ?? 0;
    }

    public function getMetricTarget(string $key): float
    {
        return $this->metrics[$key]['target'] ?? 0;
    }

    public function getMetricCompletion(string $key): float
    {
        $target = $this->getMetricTarget($key);
        if ($target <= 0) {
            return 0;
        }
        return min(100, ($this->getMetricValue($key) / $target) * 100);
    }

    public function isBonusEligible(): bool
    {
        return $this->bonus_info['eligible'] ?? false;
    }

    public function getBonusAmount(): float
    {
        return $this->bonus_info['amount'] ?? 0;
    }

    public function isBonusPaid(): bool
    {
        return $this->bonus_info['paid'] ?? false;
    }

    public function getLowPerformingMetrics(): array
    {
        $low = [];
        foreach ($this->metrics as $key => $metric) {
            $completion = $this->getMetricCompletion($key);
            if ($completion < 70) {
                $low[$key] = [
                    ...$metric,
                    'completion' => $completion,
                ];
            }
        }
        return $low;
    }

    public function getHighPerformingMetrics(): array
    {
        $high = [];
        foreach ($this->metrics as $key => $metric) {
            $completion = $this->getMetricCompletion($key);
            if ($completion >= 100) {
                $high[$key] = [
                    ...$metric,
                    'completion' => $completion,
                ];
            }
        }
        return $high;
    }
}
