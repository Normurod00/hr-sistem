<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebRtcSignal extends Model
{
    protected $table = 'webrtc_signals';

    protected $fillable = [
        'meeting_id',
        'sender_id',
        'recipient_id',
        'type',
        'data',
        'processed',
    ];

    protected $casts = [
        'data' => 'array',
        'processed' => 'boolean',
    ];

    // ========== Relationships ==========

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(VideoMeeting::class, 'meeting_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    // ========== Scopes ==========

    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    public function scopeForRecipient($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('recipient_id', $userId)
              ->orWhereNull('recipient_id'); // broadcast signals
        });
    }

    public function scopeForMeeting($query, int $meetingId)
    {
        return $query->where('meeting_id', $meetingId);
    }

    // ========== Helpers ==========

    public function markProcessed(): void
    {
        $this->update(['processed' => true]);
    }
}
