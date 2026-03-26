<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vacancy_id',
        'status',
        'match_score',
        'source',
        'notes',
        'cover_letter',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'match_score' => 'integer',
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

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ApplicationFile::class);
    }

    public function analysis(): HasOne
    {
        return $this->hasOne(ApplicationAnalysis::class);
    }

    public function latestAnalysis(): HasOne
    {
        return $this->hasOne(ApplicationAnalysis::class)->latestOfMany();
    }

    public function aiLogs(): HasMany
    {
        return $this->hasMany(AiLog::class);
    }

    public function candidateResume(): HasOne
    {
        return $this->hasOne(CandidateResume::class);
    }

    public function candidateTest(): HasOne
    {
        return $this->hasOne(CandidateTest::class);
    }

    public function chatRoom(): HasOne
    {
        return $this->hasOne(ChatRoom::class);
    }

    public function videoMeetings(): HasMany
    {
        return $this->hasMany(VideoMeeting::class);
    }

    // ========== Scopes ==========

    public function scopeByStatus(Builder $query, ApplicationStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', ApplicationStatus::New);
    }

    public function scopeInReview(Builder $query): Builder
    {
        return $query->where('status', ApplicationStatus::InReview);
    }

    public function scopeShortlisted(Builder $query, int $minScore = 60): Builder
    {
        return $query->where('match_score', '>=', $minScore);
    }

    public function scopeForVacancy(Builder $query, int $vacancyId): Builder
    {
        return $query->where('vacancy_id', $vacancyId);
    }

    // ========== Accessors ==========

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? 'Неизвестно';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status?->color() ?? 'gray';
    }

    public function getStatusBgClassAttribute(): string
    {
        return $this->status?->bgClass() ?? 'bg-gray-100 text-gray-700';
    }

    public function getMatchScoreColorAttribute(): string
    {
        if ($this->match_score === null) {
            return 'gray';
        }

        return match (true) {
            $this->match_score >= 80 => 'green',
            $this->match_score >= 60 => 'yellow',
            $this->match_score >= 40 => 'orange',
            default => 'red',
        };
    }

    public function getMatchScoreBgClassAttribute(): string
    {
        return match ($this->match_score_color) {
            'green' => 'bg-green-100 text-green-700',
            'yellow' => 'bg-yellow-100 text-yellow-700',
            'orange' => 'bg-orange-100 text-orange-700',
            'red' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function getResumeFileAttribute(): ?ApplicationFile
    {
        return $this->files()->where('file_type', 'resume')->first();
    }

    public function getHasAnalysisAttribute(): bool
    {
        return $this->analysis !== null;
    }

    public function getIsAnalyzedAttribute(): bool
    {
        return $this->match_score !== null && $this->analysis !== null;
    }

    // ========== Helpers ==========

    public function markAsInReview(): bool
    {
        return $this->update(['status' => ApplicationStatus::InReview]);
    }

    public function markAsInvited(): bool
    {
        return $this->update(['status' => ApplicationStatus::Invited]);
    }

    public function markAsRejected(): bool
    {
        return $this->update(['status' => ApplicationStatus::Rejected]);
    }

    public function markAsHired(): bool
    {
        return $this->update(['status' => ApplicationStatus::Hired]);
    }

    public function updateMatchScore(int $score): bool
    {
        return $this->update(['match_score' => min(100, max(0, $score))]);
    }

    public function getCandidateProfile(): ?array
    {
        return $this->candidate?->candidateProfile?->profile;
    }
}
