<?php

namespace App\Models;

use App\Enums\RecommendationStatus;
use App\Enums\RecommendationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAiRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'kpi_snapshot_id',
        'type',
        'priority',
        'action',
        'expected_effect',
        'expected_impact',
        'status',
        'completed_at',
        'completion_notes',
        'metadata',
    ];

    protected $casts = [
        'type' => RecommendationType::class,
        'status' => RecommendationStatus::class,
        'expected_impact' => 'decimal:2',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    // ===== RELATIONSHIPS =====

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function kpiSnapshot(): BelongsTo
    {
        return $this->belongsTo(EmployeeKpiSnapshot::class, 'kpi_snapshot_id');
    }

    // ===== SCOPES =====

    public function scopePending($query)
    {
        return $query->where('status', RecommendationStatus::Pending);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', RecommendationStatus::InProgress);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', RecommendationStatus::Completed);
    }

    public function scopeByType($query, RecommendationType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority');
    }

    // ===== ACCESSORS =====

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            RecommendationType::Quick => 'Быстрое (1-2 недели)',
            RecommendationType::Medium => 'Среднее (1-3 месяца)',
            RecommendationType::Long => 'Долгосрочное (3-12 месяцев)',
            default => 'Другое',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            RecommendationType::Quick => 'success',
            RecommendationType::Medium => 'warning',
            RecommendationType::Long => 'info',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            RecommendationStatus::Pending => 'Ожидает',
            RecommendationStatus::InProgress => 'В работе',
            RecommendationStatus::Completed => 'Выполнено',
            RecommendationStatus::Dismissed => 'Отклонено',
            default => 'Неизвестно',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            1 => 'Высокий',
            2 => 'Средний',
            3 => 'Низкий',
            default => 'Обычный',
        };
    }

    // ===== HELPERS =====

    public function markInProgress(): void
    {
        $this->update(['status' => RecommendationStatus::InProgress]);
    }

    public function markCompleted(?string $notes = null): void
    {
        $this->update([
            'status' => RecommendationStatus::Completed,
            'completed_at' => now(),
            'completion_notes' => $notes,
        ]);
    }

    public function dismiss(?string $reason = null): void
    {
        $this->update([
            'status' => RecommendationStatus::Dismissed,
            'completion_notes' => $reason,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === RecommendationStatus::Pending;
    }

    public function isCompleted(): bool
    {
        return $this->status === RecommendationStatus::Completed;
    }
}
