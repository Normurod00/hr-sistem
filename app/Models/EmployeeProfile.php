<?php

namespace App\Models;

use App\Enums\EmployeeRole;
use App\Enums\EmployeeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_number',
        'department',
        'position',
        'manager_id',
        'role',
        'hire_date',
        'status',
        'phone_internal',
        'office_location',
        'metadata',
    ];

    protected $casts = [
        'role' => EmployeeRole::class,
        'status' => EmployeeStatus::class,
        'hire_date' => 'date',
        'metadata' => 'array',
    ];

    // ===== RELATIONSHIPS =====

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(EmployeeProfile::class, 'manager_id');
    }

    public function kpiSnapshots(): HasMany
    {
        return $this->hasMany(EmployeeKpiSnapshot::class);
    }

    public function aiConversations(): HasMany
    {
        return $this->hasMany(EmployeeAiConversation::class);
    }

    public function aiRecommendations(): HasMany
    {
        return $this->hasMany(EmployeeAiRecommendation::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function disciplinaryActions(): HasMany
    {
        return $this->hasMany(DisciplinaryAction::class);
    }

    // ===== SCOPES =====

    public function scopeActive($query)
    {
        return $query->where('status', EmployeeStatus::Active);
    }

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByRole($query, EmployeeRole $role)
    {
        return $query->where('role', $role);
    }

    // ===== ACCESSORS =====

    public function getFullNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown';
    }

    public function getEmailAttribute(): string
    {
        return $this->user?->email ?? '';
    }

    // ===== HELPERS =====

    public function isManager(): bool
    {
        return $this->role === EmployeeRole::Manager || $this->subordinates()->exists();
    }

    public function isHr(): bool
    {
        return $this->role === EmployeeRole::Hr;
    }

    public function isSysAdmin(): bool
    {
        return $this->role === EmployeeRole::SysAdmin;
    }

    public function canViewEmployee(EmployeeProfile $employee): bool
    {
        // SysAdmin и HR видят всех
        if ($this->isSysAdmin() || $this->isHr()) {
            return true;
        }

        // Свои данные
        if ($this->id === $employee->id) {
            return true;
        }

        // Manager видит подчинённых (рекурсивно)
        if ($this->isManager()) {
            return $this->isManagerOf($employee);
        }

        return false;
    }

    public function isManagerOf(EmployeeProfile $employee): bool
    {
        $current = $employee;
        $depth = 0;
        $maxDepth = 10; // защита от циклов

        while ($current->manager_id && $depth < $maxDepth) {
            if ($current->manager_id === $this->id) {
                return true;
            }
            $current = $current->manager;
            $depth++;
        }

        return false;
    }

    public function getTeamIds(): array
    {
        if (!$this->isManager()) {
            return [$this->id];
        }

        return $this->subordinates()
            ->pluck('id')
            ->push($this->id)
            ->toArray();
    }

    public function getLatestKpiSnapshot()
    {
        return $this->kpiSnapshots()
            ->orderByDesc('period_end')
            ->first();
    }
}
