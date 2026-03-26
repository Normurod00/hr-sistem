<?php

namespace App\Models;

use App\Enums\MessageRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAiMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'intent',
        'metadata',
        'tokens_used',
        'response_time_ms',
    ];

    protected $casts = [
        'role' => MessageRole::class,
        'metadata' => 'array',
    ];

    // ===== RELATIONSHIPS =====

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(EmployeeAiConversation::class, 'conversation_id');
    }

    // ===== SCOPES =====

    public function scopeByRole($query, MessageRole $role)
    {
        return $query->where('role', $role);
    }

    public function scopeUserMessages($query)
    {
        return $query->where('role', MessageRole::User);
    }

    public function scopeAssistantMessages($query)
    {
        return $query->where('role', MessageRole::Assistant);
    }

    // ===== ACCESSORS =====

    public function getIntentLabelAttribute(): ?string
    {
        return match ($this->intent) {
            'leave_request' => 'Запрос отпуска',
            'leave_balance' => 'Остаток отпуска',
            'kpi_question' => 'Вопрос по KPI',
            'kpi_explain' => 'Объяснение KPI',
            'bonus_inquiry' => 'Вопрос по бонусу',
            'policy_search' => 'Поиск политики',
            'general' => 'Общий вопрос',
            default => $this->intent,
        };
    }

    public function getConfidenceAttribute(): ?float
    {
        return $this->metadata['confidence'] ?? null;
    }

    public function getSourcesAttribute(): array
    {
        return $this->metadata['sources'] ?? [];
    }

    // ===== HELPERS =====

    public function isFromUser(): bool
    {
        return $this->role === MessageRole::User;
    }

    public function isFromAssistant(): bool
    {
        return $this->role === MessageRole::Assistant;
    }

    public function isSystemMessage(): bool
    {
        return $this->role === MessageRole::System;
    }
}
