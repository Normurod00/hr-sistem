<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoMeetingParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'user_id',
        'role',
        'status',
        'invited_at',
        'joined_at',
        'left_at',
        'is_muted',
        'is_video_off',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'is_muted' => 'boolean',
        'is_video_off' => 'boolean',
    ];

    // ========== Relationships ==========

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(VideoMeeting::class, 'meeting_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========== Scopes ==========

    public function scopeInvited($query)
    {
        return $query->where('status', 'invited');
    }

    public function scopeJoined($query)
    {
        return $query->where('status', 'joined');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['invited', 'accepted', 'joined']);
    }

    // ========== Helpers ==========

    public function isHost(): bool
    {
        return $this->role === 'host';
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['host', 'moderator']);
    }

    public function hasJoined(): bool
    {
        return $this->status === 'joined';
    }

    public function join(): void
    {
        $this->update([
            'status' => 'joined',
            'joined_at' => now(),
        ]);
    }

    public function leave(): void
    {
        $this->update([
            'status' => 'left',
            'left_at' => now(),
        ]);
    }

    public function accept(): void
    {
        $this->update(['status' => 'accepted']);
    }

    public function decline(): void
    {
        $this->update(['status' => 'declined']);
    }

    public function toggleMute(): void
    {
        $this->update(['is_muted' => !$this->is_muted]);
    }

    public function toggleVideo(): void
    {
        $this->update(['is_video_off' => !$this->is_video_off]);
    }
}
