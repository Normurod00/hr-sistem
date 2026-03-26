<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\VideoMeeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Страница чата для кандидата
     */
    public function show(Application $application): View
    {
        // Проверяем, что заявка принадлежит текущему пользователю
        if ($application->user_id !== auth()->id()) {
            abort(403, 'Это не ваша заявка');
        }

        // Проверяем, что кандидат приглашён (имеет доступ к чату)
        if (!in_array($application->status->value, ['invited', 'hired'])) {
            abort(403, 'Чат доступен только после приглашения на собеседование');
        }

        // Получаем или создаём чат
        $chatRoom = ChatRoom::getOrCreateForApplication($application);

        // Загружаем сообщения
        $messages = $chatRoom->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        // Отмечаем сообщения как прочитанные
        $chatRoom->markAsReadFor(auth()->id());

        // Предстоящие встречи
        $upcomingMeetings = $application->videoMeetings()
            ->upcoming()
            ->get();

        return view('chat.show', compact('application', 'chatRoom', 'messages', 'upcomingMeetings'));
    }

    /**
     * Отправка сообщения
     */
    public function sendMessage(Request $request, Application $application): JsonResponse
    {
        if ($application->user_id !== auth()->id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $chatRoom = ChatRoom::getOrCreateForApplication($application);

        $message = ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'sender_id' => auth()->id(),
            'sender_type' => 'candidate',
            'message' => $request->input('message'),
        ]);

        // Обновляем время последнего сообщения
        $chatRoom->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'message' => $message->message,
                'sender_type' => $message->sender_type,
                'sender_name' => $message->sender->name,
                'formatted_time' => $message->formatted_time,
                'is_mine' => true,
            ],
        ]);
    }

    /**
     * Получить новые сообщения (polling)
     */
    public function getMessages(Request $request, Application $application): JsonResponse
    {
        if ($application->user_id !== auth()->id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $lastId = $request->input('last_id', 0);

        $chatRoom = $application->chatRoom;

        if (!$chatRoom) {
            return response()->json(['messages' => []]);
        }

        $messages = $chatRoom->messages()
            ->with('sender')
            ->where('id', '>', $lastId)
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'sender_name' => $message->sender->name,
                    'formatted_time' => $message->formatted_time,
                    'is_mine' => $message->sender_id === auth()->id(),
                ];
            });

        // Отмечаем как прочитанные
        if ($messages->count() > 0) {
            $chatRoom->markAsReadFor(auth()->id());
        }

        return response()->json([
            'messages' => $messages,
            'unread_count' => 0,
        ]);
    }
}
