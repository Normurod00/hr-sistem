<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'notification_preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'api_token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_employee' => 'boolean',
            'api_token_expires_at' => 'datetime',
            'notification_preferences' => 'array',
        ];
    }

    // ========== Relationships ==========

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function candidateProfile(): HasOne
    {
        return $this->hasOne(CandidateProfile::class);
    }

    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function createdVacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class, 'created_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // ========== Role Helpers ==========

    public function isCandidate(): bool
    {
        return $this->role === UserRole::Candidate;
    }

    public function isHr(): bool
    {
        return $this->role === UserRole::Hr;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function canAccessAdmin(): bool
    {
        return $this->isHr() || $this->isAdmin();
    }

    public function isEmployee(): bool
    {
        // Проверяем по роли ИЛИ по флагу is_employee
        return $this->role->isEmployee() || ($this->is_employee && $this->employeeProfile !== null);
    }

    public function canAccessEmployeePortal(): bool
    {
        return $this->isEmployee();
    }

    public function getEmployeeRole(): ?string
    {
        return $this->employeeProfile?->role?->value;
    }

    // ========== Accessors ==========

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Генерируем аватар по инициалам
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=E52716&color=fff&size=128";
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';

        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_substr($word, 0, 1);
        }

        return mb_strtoupper($initials);
    }
}
