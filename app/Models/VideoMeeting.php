<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'chat_room_id',
        'created_by',
        'title',
        'description',
        'scheduled_at',
        'started_at',
        'ended_at',
        'duration_minutes',
        'meeting_link',
        'room_id',
        'status',
        'notes',
        'max_participants',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_minutes' => 'integer',
            'max_participants' => 'integer',
            'settings' => 'array',
        ];
    }

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_STARTED = 'started';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // ========== Relationships ==========

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(VideoMeetingParticipant::class, 'meeting_id');
    }

    public function activeParticipants(): HasMany
    {
        return $this->participants()->whereIn('status', ['invited', 'accepted', 'joined']);
    }

    public function joinedParticipants(): HasMany
    {
        return $this->participants()->where('status', 'joined');
    }

    // ========== Scopes ==========

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())
                     ->where('status', self::STATUS_SCHEDULED)
                     ->orderBy('scheduled_at');
    }

    public function scopePast($query)
    {
        return $query->where(function ($q) {
            $q->where('scheduled_at', '<', now())
              ->orWhereIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
        })->orderByDesc('scheduled_at');
    }

    // ========== Accessors ==========

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'Запланировано',
            self::STATUS_STARTED => 'Идёт',
            self::STATUS_COMPLETED => 'Завершено',
            self::STATUS_CANCELLED => 'Отменено',
            default => 'Неизвестно',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'primary',
            self::STATUS_STARTED => 'success',
            self::STATUS_COMPLETED => 'secondary',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    public function getEndTimeAttribute(): \Carbon\Carbon
    {
        return $this->scheduled_at->addMinutes($this->duration_minutes);
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->scheduled_at > now() && $this->status === self::STATUS_SCHEDULED;
    }

    public function getIsActiveAttribute(): bool
    {
        $now = now();
        return $this->scheduled_at <= $now &&
               $this->end_time >= $now &&
               $this->status === self::STATUS_STARTED;
    }

    // ========== Methods ==========

    public function start(): bool
    {
        return $this->update(['status' => self::STATUS_STARTED]);
    }

    public function complete(): bool
    {
        return $this->update(['status' => self::STATUS_COMPLETED]);
    }

    public function cancel(): bool
    {
        return $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Генерация простой ссылки на встречу (можно заменить на интеграцию)
     */
    public function generateMeetingLink(): string
    {
        // Можно интегрировать с Jitsi, Zoom, Google Meet и т.д.
        // Пока генерируем простую ссылку на Jitsi (бесплатно, без регистрации)
        $roomName = 'brb-interview-' . $this->application_id . '-' . $this->id;

        return 'https://meet.jit.si/' . $roomName;
    }

    /**
     * Генерация уникального room_id для WebRTC
     */
    public function generateRoomId(): string
    {
        return 'hr_meeting_' . $this->id . '_' . bin2hex(random_bytes(4));
    }

    /**
     * Добавить участника
     */
    public function addParticipant(User $user, string $role = 'participant'): VideoMeetingParticipant
    {
        return $this->participants()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'role' => $role,
                'status' => 'invited',
                'invited_at' => now(),
            ]
        );
    }

    /**
     * Добавить нескольких участников
     */
    public function addParticipants(array $userIds, string $role = 'participant'): void
    {
        foreach ($userIds as $userId) {
            $this->participants()->updateOrCreate(
                ['user_id' => $userId],
                [
                    'role' => $role,
                    'status' => 'invited',
                    'invited_at' => now(),
                ]
            );
        }
    }

    /**
     * Удалить участника
     */
    public function removeParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->delete() > 0;
    }

    /**
     * Проверка, может ли пользователь присоединиться
     */
    public function canJoin(User $user): bool
    {
        return $this->created_by === $user->id ||
               $this->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * Проверка, является ли пользователь организатором
     */
    public function isHost(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    /**
     * Получить количество присоединившихся участников
     */
    public function getJoinedCountAttribute(): int
    {
        return $this->joinedParticipants()->count();
    }

    /**
     * Scope для поиска встреч пользователя
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('created_by', $userId)
            ->orWhereHas('participants', fn($q) => $q->where('user_id', $userId));
    }
}
