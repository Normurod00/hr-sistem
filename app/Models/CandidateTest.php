<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'user_id',
        'vacancy_id',
        'questions',
        'total_questions',
        'correct_answers',
        'score',
        'time_limit',
        'time_spent',
        'started_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'questions' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Статусы
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';

    // Relationships
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    // Accessors
    public function getIsExpiredAttribute(): bool
    {
        if ($this->status === self::STATUS_COMPLETED) {
            return false;
        }

        if (!$this->started_at) {
            return false;
        }

        return $this->started_at->addSeconds($this->time_limit)->isPast();
    }

    public function getRemainingTimeAttribute(): int
    {
        if (!$this->started_at) {
            return $this->time_limit;
        }

        $remaining = $this->time_limit - $this->started_at->diffInSeconds(now());
        return max(0, $remaining);
    }

    public function getScoreLabelAttribute(): string
    {
        if ($this->score === null) {
            return 'Не пройден';
        }

        return match (true) {
            $this->score >= 80 => 'Отлично',
            $this->score >= 60 => 'Хорошо',
            $this->score >= 40 => 'Удовлетворительно',
            default => 'Неудовлетворительно',
        };
    }

    public function getScoreColorAttribute(): string
    {
        if ($this->score === null) {
            return 'gray';
        }

        return match (true) {
            $this->score >= 80 => 'green',
            $this->score >= 60 => 'yellow',
            $this->score >= 40 => 'orange',
            default => 'red',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Ожидает',
            self::STATUS_IN_PROGRESS => 'В процессе',
            self::STATUS_COMPLETED => 'Завершён',
            self::STATUS_EXPIRED => 'Время истекло',
            default => 'Неизвестно',
        };
    }

    // Methods
    public function start(): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);
    }

    public function complete(array $answers): void
    {
        $questions = $this->questions;
        $correctCount = 0;

        foreach ($answers as $index => $answer) {
            $index = (int) $index;
            if (isset($questions[$index])) {
                $questions[$index]['user_answer'] = $answer;

                if ($answer == $questions[$index]['correct_answer']) {
                    $correctCount++;
                }
            }
        }

        $total = $this->total_questions ?: count($questions) ?: 1;
        $score = round(($correctCount / $total) * 100);

        $this->update([
            'questions' => $questions,
            'correct_answers' => $correctCount,
            'score' => $score,
            'time_spent' => $this->started_at ? $this->started_at->diffInSeconds(now()) : 0,
            'completed_at' => now(),
            'status' => self::STATUS_COMPLETED,
        ]);
    }

    public function expire(): void
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
            'completed_at' => now(),
            'time_spent' => $this->time_limit,
        ]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }
}
