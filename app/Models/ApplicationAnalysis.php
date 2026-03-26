<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationAnalysis extends Model
{
    use HasFactory;

    protected $table = 'application_analyses';

    protected $fillable = [
        'application_id',
        'strengths',
        'weaknesses',
        'risks',
        'suggested_questions',
        'recommendation',
        'raw_ai_payload',
    ];

    protected function casts(): array
    {
        return [
            'strengths' => 'array',
            'weaknesses' => 'array',
            'risks' => 'array',
            'suggested_questions' => 'array',
            'raw_ai_payload' => 'array',
        ];
    }

    // ========== Relationships ==========

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // ========== Accessors ==========

    public function getStrengthsCountAttribute(): int
    {
        return count($this->strengths ?? []);
    }

    public function getWeaknessesCountAttribute(): int
    {
        return count($this->weaknesses ?? []);
    }

    public function getRisksCountAttribute(): int
    {
        return count($this->risks ?? []);
    }

    public function getQuestionsCountAttribute(): int
    {
        return count($this->suggested_questions ?? []);
    }

    public function getHasStrengthsAttribute(): bool
    {
        return !empty($this->strengths);
    }

    public function getHasWeaknessesAttribute(): bool
    {
        return !empty($this->weaknesses);
    }

    public function getHasRisksAttribute(): bool
    {
        return !empty($this->risks);
    }

    public function getHasQuestionsAttribute(): bool
    {
        return !empty($this->suggested_questions);
    }

    public function getHasRecommendationAttribute(): bool
    {
        return !empty($this->recommendation);
    }

    public function getIsCompleteAttribute(): bool
    {
        return $this->has_strengths
            && $this->has_weaknesses
            && $this->has_recommendation;
    }

    // ========== Helpers ==========

    public function updateFromAiResponse(array $response): bool
    {
        return $this->update([
            'strengths' => $response['strengths'] ?? [],
            'weaknesses' => $response['weaknesses'] ?? [],
            'risks' => $response['risks'] ?? [],
            'suggested_questions' => $response['suggested_questions'] ?? [],
            'recommendation' => $response['recommendation'] ?? '',
            'raw_ai_payload' => $response,
        ]);
    }

    public static function createFromAiResponse(int $applicationId, array $response): self
    {
        return self::create([
            'application_id' => $applicationId,
            'strengths' => $response['strengths'] ?? [],
            'weaknesses' => $response['weaknesses'] ?? [],
            'risks' => $response['risks'] ?? [],
            'suggested_questions' => $response['suggested_questions'] ?? [],
            'recommendation' => $response['recommendation'] ?? '',
            'raw_ai_payload' => $response,
        ]);
    }

    public function getSummary(): array
    {
        return [
            'strengths_count' => $this->strengths_count,
            'weaknesses_count' => $this->weaknesses_count,
            'risks_count' => $this->risks_count,
            'questions_count' => $this->questions_count,
            'has_recommendation' => $this->has_recommendation,
        ];
    }
}
