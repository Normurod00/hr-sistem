<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_room_id',
        'sender_id',
        'sender_type',
        'message',
        'attachments',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'read_at' => 'datetime',
        ];
    }

    const TYPE_CANDIDATE = 'candidate';
    const TYPE_HR = 'hr';
    const TYPE_SYSTEM = 'system';

    // ========== Relationships ==========

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // ========== Accessors ==========

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    public function getFormattedTimeAttribute(): string
    {
        if ($this->created_at->isToday()) {
            return $this->created_at->format('H:i');
        }

        if ($this->created_at->isYesterday()) {
            return 'Вчера ' . $this->created_at->format('H:i');
        }

        return $this->created_at->format('d.m.Y H:i');
    }

    public function getSenderTypeLabel(): string
    {
        return match ($this->sender_type) {
            self::TYPE_CANDIDATE => 'Кандидат',
            self::TYPE_HR => 'HR',
            self::TYPE_SYSTEM => 'Система',
            default => 'Неизвестно',
        };
    }

    // ========== Methods ==========

    /**
     * Создать системное сообщение
     */
    public static function createSystemMessage(ChatRoom $chatRoom, string $message): self
    {
        return self::create([
            'chat_room_id' => $chatRoom->id,
            'sender_id' => $chatRoom->hr_id ?? $chatRoom->candidate_id,
            'sender_type' => self::TYPE_SYSTEM,
            'message' => $message,
        ]);
    }
}
