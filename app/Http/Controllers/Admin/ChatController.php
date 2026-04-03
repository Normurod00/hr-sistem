<?php

namespace App\Http\Controllers\Admin;

use App\Events\Chat\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\VideoMeeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Список всех чатов
     */
    public function index(Request $request): View
    {
        $query = ChatRoom::with(['candidate', 'application.vacancy', 'messages' => fn($q) => $q->latest()->take(1)])
            ->active();

        // Поиск по имени кандидата
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('candidate', fn($q) => $q->where('name', 'ilike', "%{$search}%")
                ->orWhere('email', 'ilike', "%{$search}%"));
        }

        // Фильтр по вакансии
        if ($request->filled('vacancy_id')) {
            $query->whereHas('application', fn($q) => $q->where('vacancy_id', $request->input('vacancy_id')));
        }

        // Фильтр: только непрочитанные
        if ($request->input('unread_only')) {
            $query->whereHas('messages', fn($q) => $q->where('sender_type', '!=', 'hr')->whereNull('read_at'));
        }

        // Сортировка
        $sort = $request->input('sort', 'recent');
        if ($sort === 'name') {
            $query->orderBy(
                \App\Models\User::select('name')
                    ->whereColumn('users.id', 'chat_rooms.candidate_id')
                    ->limit(1)
            );
        } else {
            $query->orderByDesc('last_message_at');
        }

        $chatRooms = $query->paginate(30)->withQueryString();

        // Статистика
        $totalChats = ChatRoom::active()->count();
        $totalUnread = ChatMessage::whereHas('chatRoom', fn($q) => $q->active())
            ->where('sender_type', '!=', 'hr')
            ->whereNull('read_at')
            ->count();

        // Вакансии для фильтра
        $vacancies = \App\Models\Vacancy::whereHas('applications.chatRoom')
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('admin.chat.index', compact('chatRooms', 'totalChats', 'totalUnread', 'vacancies'));
    }

    /**
     * Страница чата с кандидатом
     */
    public function show(Application $application): View
    {
        // Получаем или создаём чат
        $chatRoom = ChatRoom::getOrCreateForApplication($application);

        // Назначаем себя HR, если ещё не назначен
        if (!$chatRoom->hr_id) {
            $chatRoom->update(['hr_id' => auth()->id()]);
        }

        // Загружаем сообщения
        $messages = $chatRoom->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        // Отмечаем сообщения как прочитанные
        $chatRoom->markAsReadFor(auth()->id());

        // Загружаем заявку с данными
        $application->load(['candidate', 'vacancy', 'candidateTest']);

        // Встречи
        $meetings = $application->videoMeetings()
            ->orderByDesc('scheduled_at')
            ->get();

        return view('admin.chat.show', compact('application', 'chatRoom', 'messages', 'meetings'));
    }

    /**
     * Отправка сообщения от HR
     */
    public function sendMessage(Request $request, Application $application): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $chatRoom = ChatRoom::getOrCreateForApplication($application);

        // Убедимся что HR назначен
        if (!$chatRoom->hr_id) {
            $chatRoom->update(['hr_id' => auth()->id()]);
        }

        $message = ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'sender_id' => auth()->id(),
            'sender_type' => 'hr',
            'message' => $request->input('message'),
        ]);

        // Обновляем время последнего сообщения
        $chatRoom->update(['last_message_at' => now()]);

        $message->load('sender');

        // Broadcast to WebSocket
        broadcast(MessageSent::fromMessage($message))->toOthers();

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
     * Получить новые сообщения
     */
    public function getMessages(Request $request, Application $application): JsonResponse
    {
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
        ]);
    }

    /**
     * Создание видео-встречи
     */
    public function createMeeting(Request $request, Application $application): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $chatRoom = $application->chatRoom;

        $meeting = VideoMeeting::create([
            'application_id' => $application->id,
            'chat_room_id' => $chatRoom?->id,
            'created_by' => auth()->id(),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'scheduled_at' => $request->input('scheduled_at'),
            'duration_minutes' => $request->input('duration_minutes'),
            'status' => VideoMeeting::STATUS_SCHEDULED,
        ]);

        // Генерируем ссылку на встречу
        $meeting->update(['meeting_link' => $meeting->generateMeetingLink()]);

        // Отправляем системное сообщение в чат
        if ($chatRoom) {
            ChatMessage::createSystemMessage(
                $chatRoom,
                "Запланирована видео-встреча: {$meeting->title}\n" .
                "Дата: " . $meeting->scheduled_at->format('d.m.Y H:i') . "\n" .
                "Ссылка: {$meeting->meeting_link}"
            );
        }

        return response()->json([
            'success' => true,
            'meeting' => [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'scheduled_at' => $meeting->scheduled_at->format('d.m.Y H:i'),
                'duration_minutes' => $meeting->duration_minutes,
                'meeting_link' => $meeting->meeting_link,
                'status' => $meeting->status,
                'status_label' => $meeting->status_label,
            ],
        ]);
    }

    /**
     * Отмена встречи
     */
    public function cancelMeeting(VideoMeeting $meeting): JsonResponse
    {
        $meeting->cancel();

        // Системное сообщение
        if ($meeting->chatRoom) {
            ChatMessage::createSystemMessage(
                $meeting->chatRoom,
                "Видео-встреча \"{$meeting->title}\" отменена."
            );
        }

        return response()->json(['success' => true]);
    }
}
