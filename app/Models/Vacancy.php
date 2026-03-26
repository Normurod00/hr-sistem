<?php

namespace App\Models;

use App\Enums\EmploymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Vacancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'must_have_skills',
        'nice_to_have_skills',
        'min_experience_years',
        'language_requirements',
        'salary_min',
        'salary_max',
        'location',
        'employment_type',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'must_have_skills' => 'array',
            'nice_to_have_skills' => 'array',
            'language_requirements' => 'array',
            'min_experience_years' => 'decimal:1',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'is_active' => 'boolean',
            'employment_type' => EmploymentType::class,
        ];
    }

    // ========== Relationships ==========

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    // ========== Scopes ==========

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, EmploymentType $type): Builder
    {
        return $query->where('employment_type', $type);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%");
        });
    }

    // ========== Accessors ==========

    public function getSalaryFormattedAttribute(): ?string
    {
        if (!$this->salary_min && !$this->salary_max) {
            return null;
        }

        if ($this->salary_min && $this->salary_max) {
            return number_format($this->salary_min, 0, '', ' ') . ' – ' .
                   number_format($this->salary_max, 0, '', ' ') . ' сум';
        }

        if ($this->salary_min) {
            return 'от ' . number_format($this->salary_min, 0, '', ' ') . ' сум';
        }

        return 'до ' . number_format($this->salary_max, 0, '', ' ') . ' сум';
    }

    public function getEmploymentTypeLabelAttribute(): string
    {
        return $this->employment_type?->label() ?? 'Не указано';
    }

    public function getApplicationsCountAttribute(): int
    {
        return $this->applications()->count();
    }

    public function getNewApplicationsCountAttribute(): int
    {
        return $this->applications()->where('status', 'new')->count();
    }

    // ========== Helpers ==========

    public function toAiFormat(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'must_have_skills' => $this->must_have_skills ?? [],
            'nice_to_have_skills' => $this->nice_to_have_skills ?? [],
            'min_experience_years' => $this->min_experience_years,
            'language_requirements' => $this->language_requirements,
            'employment_type' => $this->employment_type?->value,
            'location' => $this->location,
        ];
    }
}
