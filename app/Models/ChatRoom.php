<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'candidate_id',
        'hr_id',
        'is_active',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_message_at' => 'datetime',
        ];
    }

    // ========== Relationships ==========

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function hr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function videoMeetings(): HasMany
    {
        return $this->hasMany(VideoMeeting::class);
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('candidate_id', $userId)
              ->orWhere('hr_id', $userId);
        });
    }

    // ========== Methods ==========

    /**
     * Получить или создать чат для заявки
     */
    public static function getOrCreateForApplication(Application $application): self
    {
        return self::firstOrCreate(
            ['application_id' => $application->id],
            [
                'candidate_id' => $application->user_id,
                'hr_id' => null,
                'is_active' => true,
            ]
        );
    }

    /**
     * Количество непрочитанных сообщений для пользователя
     */
    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Отметить все сообщения прочитанными для пользователя
     */
    public function markAsReadFor(int $userId): void
    {
        $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Последнее сообщение
     */
    public function getLastMessageAttribute(): ?ChatMessage
    {
        return $this->messages()->latest()->first();
    }
}
