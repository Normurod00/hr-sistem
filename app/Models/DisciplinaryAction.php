<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisciplinaryAction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_profile_id',
        'type',
        'severity',
        'category',
        'title',
        'description',
        'reason',
        'incident_date',
        'action_date',
        'effective_from',
        'effective_until',
        'fine_amount',
        'fine_currency',
        'status',
        'can_appeal',
        'appeal_deadline',
        'appeal_text',
        'appealed_at',
        'appeal_status',
        'appeal_resolution',
        'created_by',
        'approved_by',
        'approved_at',
        'employee_notified',
        'notified_at',
        'employee_acknowledged',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'action_date' => 'date',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'fine_amount' => 'decimal:2',
            'can_appeal' => 'boolean',
            'appeal_deadline' => 'date',
            'appealed_at' => 'datetime',
            'approved_at' => 'datetime',
            'employee_notified' => 'boolean',
            'notified_at' => 'datetime',
            'employee_acknowledged' => 'boolean',
            'acknowledged_at' => 'datetime',
        ];
    }

    // ========== Relationships ==========

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ========== Accessors ==========

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'warning' => 'Огоҳлантириш',
            'reprimand' => 'Ҳайфсан',
            'fine' => 'Жарима',
            'suspension' => 'Ишдан четлатиш',
            'termination' => 'Ишдан бўшатиш',
            default => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'warning' => 'yellow',
            'reprimand' => 'orange',
            'fine' => 'red',
            'suspension' => 'purple',
            'termination' => 'black',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Қоралама',
            'active' => 'Фаол',
            'appealed' => 'Шикоят қилинган',
            'revoked' => 'Бекор қилинган',
            'expired' => 'Муддати ўтган',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'active' => 'danger',
            'appealed' => 'warning',
            'revoked' => 'success',
            'expired' => 'secondary',
            default => 'info',
        };
    }

    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'minor' => 'Енгил',
            'moderate' => 'Ўртача',
            'major' => 'Оғир',
            'critical' => 'Жуда оғир',
            default => $this->severity,
        };
    }

    public function getCanStillAppealAttribute(): bool
    {
        if (!$this->can_appeal) {
            return false;
        }

        if ($this->appealed_at) {
            return false;
        }

        if ($this->appeal_deadline && $this->appeal_deadline->isPast()) {
            return false;
        }

        return $this->status === 'active';
    }

    public function getIsActiveAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->effective_until && $this->effective_until->isPast()) {
            return false;
        }

        return true;
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            });
    }

    public function scopeForEmployee($query, int $employeeProfileId)
    {
        return $query->where('employee_profile_id', $employeeProfileId);
    }

    // ========== Methods ==========

    public function approve(User $approver): void
    {
        $this->update([
            'status' => 'active',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    public function revoke(): void
    {
        $this->update(['status' => 'revoked']);
    }

    public function submitAppeal(string $appealText): void
    {
        if (!$this->can_still_appeal) {
            throw new \Exception('Шикоят қилиш муддати ўтган');
        }

        $this->update([
            'status' => 'appealed',
            'appeal_text' => $appealText,
            'appealed_at' => now(),
            'appeal_status' => 'pending',
        ]);
    }

    public function acknowledge(): void
    {
        $this->update([
            'employee_acknowledged' => true,
            'acknowledged_at' => now(),
        ]);
    }
}
