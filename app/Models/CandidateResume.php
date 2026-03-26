<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateResume extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_id',
        'full_name',
        'birth_date',
        'phone',
        'email',
        'city',
        'citizenship',
        'desired_position',
        'desired_salary',
        'education',
        'experience',
        'skills',
        'languages',
        'about',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'education' => 'array',
        'experience' => 'array',
        'languages' => 'array',
    ];

    /**
     * Пользователь-владелец резюме
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Заявка, к которой привязано резюме
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Возраст кандидата
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        return $this->birth_date->age;
    }

    /**
     * Навыки как массив
     */
    public function getSkillsArrayAttribute(): array
    {
        if (!$this->skills) {
            return [];
        }

        return array_map('trim', explode(',', $this->skills));
    }

    /**
     * Общий опыт работы в годах
     */
    public function getTotalExperienceYearsAttribute(): float
    {
        if (empty($this->experience)) {
            return 0;
        }

        $totalMonths = 0;

        foreach ($this->experience as $exp) {
            if (empty($exp['start_date'])) continue;

            try {
                $startDate = \Carbon\Carbon::createFromFormat('Y-m', $exp['start_date']);

                if (!empty($exp['current'])) {
                    $endDate = now();
                } elseif (!empty($exp['end_date'])) {
                    $endDate = \Carbon\Carbon::createFromFormat('Y-m', $exp['end_date']);
                } else {
                    continue;
                }

                $totalMonths += $startDate->diffInMonths($endDate);
            } catch (\Exception $e) {
                continue;
            }
        }

        return round($totalMonths / 12, 1);
    }
}
