<?php

namespace App\Services\WebRTC;

use App\Models\User;
use App\Models\VideoMeeting;
use App\Models\WebRtcSignal;
use App\Models\VideoMeetingParticipant;
use Illuminate\Support\Collection;

class WebRTCService
{
    /**
     * Получить STUN/TURN серверы
     */
    public function getIceServers(): array
    {
        return [
            [
                'urls' => [
                    'stun:stun.l.google.com:19302',
                    'stun:stun1.l.google.com:19302',
                ],
            ],
        ];
    }

    /**
     * Отправить сигнал (offer, answer, ice-candidate)
     */
    public function sendSignal(
        VideoMeeting $meeting,
        User $sender,
        string $type,
        array $data,
        ?int $recipientId = null
    ): WebRtcSignal {
        return WebRtcSignal::create([
            'meeting_id' => $meeting->id,
            'sender_id' => $sender->id,
            'recipient_id' => $recipientId,
            'type' => $type,
            'data' => $data,
            'processed' => false,
        ]);
    }

    /**
     * Получить непрочитанные сигналы для пользователя
     */
    public function getPendingSignals(VideoMeeting $meeting, User $user): Collection
    {
        $signals = WebRtcSignal::forMeeting($meeting->id)
            ->forRecipient($user->id)
            ->where('sender_id', '!=', $user->id)
            ->unprocessed()
            ->orderBy('created_at')
            ->get();

        // Помечаем как обработанные
        WebRtcSignal::whereIn('id', $signals->pluck('id'))->update(['processed' => true]);

        return $signals;
    }

    /**
     * Присоединить пользователя к встрече
     */
    public function joinMeeting(VideoMeeting $meeting, User $user): VideoMeetingParticipant
    {
        $participant = $meeting->participants()->where('user_id', $user->id)->first();

        if ($participant) {
            $participant->join();
            return $participant;
        }

        // Если пользователь — хост, создаем запись
        if ($meeting->isHost($user)) {
            return $meeting->participants()->create([
                'user_id' => $user->id,
                'role' => 'host',
                'status' => 'joined',
                'joined_at' => now(),
            ]);
        }

        throw new \Exception('Пользователь не приглашен на встречу');
    }

    /**
     * Покинуть встречу
     */
    public function leaveMeeting(VideoMeeting $meeting, User $user): void
    {
        $participant = $meeting->participants()->where('user_id', $user->id)->first();

        if ($participant) {
            $participant->leave();
        }

        // Если все покинули — завершаем встречу
        $activeCount = $meeting->participants()->whereIn('status', ['joined'])->count();
        if ($activeCount === 0 && $meeting->status === VideoMeeting::STATUS_STARTED) {
            $meeting->complete();
        }
    }

    /**
     * Получить список активных участников
     */
    public function getActiveParticipants(VideoMeeting $meeting): Collection
    {
        return $meeting->participants()
            ->with('user:id,name,email,avatar')
            ->whereIn('status', ['joined', 'accepted', 'invited'])
            ->get();
    }

    /**
     * Очистить старые сигналы (старше 1 часа)
     */
    public function cleanupOldSignals(): int
    {
        return WebRtcSignal::where('created_at', '<', now()->subHour())->delete();
    }

    /**
     * Начать встречу
     */
    public function startMeeting(VideoMeeting $meeting): void
    {
        if (!$meeting->room_id) {
            $meeting->update([
                'room_id' => $meeting->generateRoomId(),
            ]);
        }

        $meeting->start();
    }

    /**
     * Завершить встречу
     */
    public function endMeeting(VideoMeeting $meeting): void
    {
        // Помечаем всех участников как покинувших
        $meeting->participants()
            ->where('status', 'joined')
            ->update([
                'status' => 'left',
                'left_at' => now(),
            ]);

        // Очищаем сигналы
        WebRtcSignal::forMeeting($meeting->id)->delete();

        $meeting->complete();
    }
}
