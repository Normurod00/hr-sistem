<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile',
        'last_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'profile' => 'array',
            'last_generated_at' => 'datetime',
        ];
    }

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ========== Accessors for Profile Fields ==========

    public function getPositionTitleAttribute(): ?string
    {
        return $this->profile['position_title'] ?? null;
    }

    public function getYearsOfExperienceAttribute(): ?float
    {
        return $this->profile['years_of_experience'] ?? null;
    }

    public function getSkillsAttribute(): array
    {
        return $this->profile['skills'] ?? [];
    }

    public function getLanguagesAttribute(): array
    {
        return $this->profile['languages'] ?? [];
    }

    public function getDomainsAttribute(): array
    {
        return $this->profile['domains'] ?? [];
    }

    public function getEducationAttribute(): array
    {
        return $this->profile['education'] ?? [];
    }

    public function getHasManagementExperienceAttribute(): bool
    {
        return $this->profile['management_experience'] ?? false;
    }

    public function getHasRemoteExperienceAttribute(): bool
    {
        return $this->profile['remote_experience'] ?? false;
    }

    public function getContactInfoAttribute(): array
    {
        return $this->profile['contact_info'] ?? [];
    }

    // ========== Helpers ==========

    public function getSkillNames(): array
    {
        return array_column($this->skills, 'name');
    }

    public function getStrongSkills(): array
    {
        return array_filter($this->skills, fn($s) => ($s['level'] ?? '') === 'strong');
    }

    public function getLanguageNames(): array
    {
        return array_column($this->languages, 'name');
    }

    public function hasSkill(string $skillName): bool
    {
        $skillName = mb_strtolower($skillName);
        foreach ($this->skills as $skill) {
            if (mb_strtolower($skill['name'] ?? '') === $skillName) {
                return true;
            }
        }
        return false;
    }

    public function hasLanguage(string $languageName): bool
    {
        $languageName = mb_strtolower($languageName);
        foreach ($this->languages as $language) {
            if (mb_strtolower($language['name'] ?? '') === $languageName) {
                return true;
            }
        }
        return false;
    }

    public function toAiFormat(): array
    {
        return $this->profile ?? [];
    }

    public function updateProfile(array $newProfile): bool
    {
        return $this->update([
            'profile' => $newProfile,
            'last_generated_at' => now(),
        ]);
    }

    public function isEmpty(): bool
    {
        return empty($this->profile);
    }
}
