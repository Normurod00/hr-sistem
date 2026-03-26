<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NominationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_uz',
        'name_ru',
        'slug',
        'description',
        'icon',
        'color',
        'points_reward',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points_reward' => 'integer',
        'sort_order' => 'integer',
    ];

    // ===== RELATIONSHIPS =====

    public function nominations(): HasMany
    {
        return $this->hasMany(Nomination::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(RecognitionAward::class);
    }

    // ===== SCOPES =====

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ===== ACCESSORS =====

    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'uz' => $this->name_uz ?? $this->name,
            'ru' => $this->name_ru ?? $this->name,
            default => $this->name,
        };
    }

    // ===== HELPERS =====

    public function getNominationsCount(string $periodType = 'month', ?string $periodStart = null): int
    {
        $query = $this->nominations()->where('status', 'approved');

        if ($periodStart) {
            $query->where('period_start', $periodStart);
        }

        return $query->count();
    }
}
