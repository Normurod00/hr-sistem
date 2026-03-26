<?php

namespace App\Models;

use App\Enums\AiContextType;
use App\Enums\ConversationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeAiConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'title',
        'context_type',
        'status',
        'metadata',
        'message_count',
        'last_message_at',
    ];

    protected $casts = [
        'context_type' => AiContextType::class,
        'status' => ConversationStatus::class,
        'metadata' => 'array',
        'last_message_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(EmployeeAiMessage::class, 'conversation_id');
    }

    // ===== SCOPES =====

    public function scopeActive($query)
    {
        return $query->where('status', ConversationStatus::Active);
    }

    public function scopeByContextType($query, AiContextType $type)
    {
        return $query->where('context_type', $type);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('last_message_at');
    }

    // ===== ACCESSORS =====

    public function getDisplayTitleAttribute(): string
    {
        if ($this->title) {
            return $this->title;
        }

        $firstMessage = $this->messages()->where('role', 'user')->first();
        if ($firstMessage) {
            return \Str::limit($firstMessage->content, 50);
        }

        return 'Новый разговор';
    }

    public function getContextLabelAttribute(): string
    {
        return match ($this->context_type) {
            AiContextType::General => 'Общий вопрос',
            AiContextType::Kpi => 'KPI',
            AiContextType::Leave => 'Отпуск',
            AiContextType::Bonus => 'Бонус',
            AiContextType::Policy => 'Политики',
            AiContextType::Complaint => 'Обращение',
            default => 'Другое',
        };
    }

    // ===== HELPERS =====

    public function addMessage(string $role, string $content, ?string $intent = null, ?array $metadata = null): EmployeeAiMessage
    {
        $message = $this->messages()->create([
            'role' => $role,
            'content' => $content,
            'intent' => $intent,
            'metadata' => $metadata,
        ]);

        $this->increment('message_count');
        $this->update(['last_message_at' => now()]);

        return $message;
    }

    public function getMessagesForAi(int $limit = 20): array
    {
        return $this->messages()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(fn($m) => [
                'role' => $m->role,
                'content' => $m->content,
            ])
            ->values()
            ->toArray();
    }

    public function close(): void
    {
        $this->update(['status' => ConversationStatus::Closed]);
    }

    public function archive(): void
    {
        $this->update(['status' => ConversationStatus::Archived]);
    }
}
