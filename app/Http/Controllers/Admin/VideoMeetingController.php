<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VideoMeeting;
use App\Models\Application;
use App\Services\WebRTC\WebRTCService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VideoMeetingController extends Controller
{
    protected WebRTCService $webrtcService;

    public function __construct(WebRTCService $webrtcService)
    {
        $this->webrtcService = $webrtcService;
    }

    /**
     * Список всех видеовстреч
     */
    public function index(Request $request)
    {
        $query = VideoMeeting::with(['createdBy', 'application.user', 'participants.user'])
            ->orderByDesc('scheduled_at');

        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Фильтр предстоящие/прошедшие
        if ($request->filter === 'upcoming') {
            $query->upcoming();
        } elseif ($request->filter === 'past') {
            $query->past();
        }

        $meetings = $query->paginate(15);

        // Список HR/Admin для выбора участников
        $staffUsers = User::whereIn('role', ['hr', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('admin.meetings.index', compact('meetings', 'staffUsers'));
    }

    /**
     * Форма создания встречи
     */
    public function create(Request $request)
    {
        $application = null;
        if ($request->filled('application_id')) {
            $application = Application::with('user')->find($request->application_id);
        }

        // Список HR/Admin
        $staffUsers = User::whereIn('role', ['hr', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('admin.meetings.create', compact('application', 'staffUsers'));
    }

    /**
     * Сохранить новую встречу
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:180',
            'application_id' => 'nullable|exists:applications,id',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
        ]);

        $meeting = VideoMeeting::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'],
            'application_id' => $validated['application_id'] ?? null,
            'created_by' => auth()->id(),
            'status' => VideoMeeting::STATUS_SCHEDULED,
            'room_id' => 'hr_meeting_' . uniqid() . '_' . bin2hex(random_bytes(4)),
        ]);

        // Добавляем хоста как участника
        $meeting->addParticipant(auth()->user(), 'host');

        // Добавляем выбранных участников
        $meeting->addParticipants($validated['participants']);

        // Если есть заявка, добавляем кандидата
        if ($meeting->application_id) {
            $candidate = Application::find($meeting->application_id)->user;
            if ($candidate) {
                $meeting->addParticipant($candidate, 'participant');
            }
        }

        // Генерируем ссылку на встречу
        $meeting->update([
            'meeting_link' => route('admin.meetings.room', $meeting),
        ]);

        return redirect()
            ->route('admin.meetings.index')
            ->with('success', 'Видеовстреча успешно создана');
    }

    /**
     * Просмотр деталей встречи
     */
    public function show(VideoMeeting $meeting)
    {
        $meeting->load(['createdBy', 'application.user', 'participants.user']);

        return view('admin.meetings.show', compact('meeting'));
    }

    /**
     * Форма редактирования
     */
    public function edit(VideoMeeting $meeting)
    {
        if ($meeting->status !== VideoMeeting::STATUS_SCHEDULED) {
            return redirect()
                ->route('admin.meetings.show', $meeting)
                ->with('error', 'Нельзя редактировать начатую или завершенную встречу');
        }

        $meeting->load(['participants.user']);

        $staffUsers = User::whereIn('role', ['hr', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('admin.meetings.edit', compact('meeting', 'staffUsers'));
    }

    /**
     * Обновить встречу
     */
    public function update(Request $request, VideoMeeting $meeting)
    {
        if ($meeting->status !== VideoMeeting::STATUS_SCHEDULED) {
            return redirect()
                ->route('admin.meetings.show', $meeting)
                ->with('error', 'Нельзя редактировать начатую или завершенную встречу');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:180',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
        ]);

        $meeting->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'],
        ]);

        // Обновляем участников (кроме хоста и кандидата)
        $meeting->participants()
            ->where('role', 'participant')
            ->whereHas('user', fn($q) => $q->whereIn('role', ['hr', 'admin']))
            ->delete();

        $meeting->addParticipants($validated['participants']);

        return redirect()
            ->route('admin.meetings.show', $meeting)
            ->with('success', 'Видеовстреча обновлена');
    }

    /**
     * Отменить встречу
     */
    public function cancel(VideoMeeting $meeting)
    {
        if ($meeting->status !== VideoMeeting::STATUS_SCHEDULED) {
            return back()->with('error', 'Можно отменить только запланированную встречу');
        }

        $meeting->cancel();

        return redirect()
            ->route('admin.meetings.index')
            ->with('success', 'Встреча отменена');
    }

    /**
     * Комната видеовстречи
     */
    public function room(VideoMeeting $meeting)
    {
        $user = auth()->user();

        // Проверяем доступ
        if (!$meeting->canJoin($user)) {
            abort(403, 'У вас нет доступа к этой встрече');
        }

        // Если встреча не начата — начинаем
        if ($meeting->status === VideoMeeting::STATUS_SCHEDULED) {
            $this->webrtcService->startMeeting($meeting);
        }

        // Присоединяем пользователя
        $participant = $this->webrtcService->joinMeeting($meeting, $user);

        $meeting->load(['createdBy', 'participants.user']);

        $iceServers = $this->webrtcService->getIceServers();

        return view('admin.meetings.room', compact('meeting', 'participant', 'iceServers'));
    }

    /**
     * API: Отправить сигнал WebRTC
     */
    public function sendSignal(Request $request, VideoMeeting $meeting): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:offer,answer,ice-candidate,renegotiate',
            'data' => 'required|array',
            'recipient_id' => 'nullable|integer',
        ]);

        $user = auth()->user();

        if (!$meeting->canJoin($user)) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        $signal = $this->webrtcService->sendSignal(
            $meeting,
            $user,
            $validated['type'],
            $validated['data'],
            $validated['recipient_id'] ?? null
        );

        return response()->json(['success' => true, 'signal_id' => $signal->id]);
    }

    /**
     * API: Получить ожидающие сигналы
     */
    public function getSignals(VideoMeeting $meeting): JsonResponse
    {
        $user = auth()->user();

        if (!$meeting->canJoin($user)) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        $signals = $this->webrtcService->getPendingSignals($meeting, $user);

        return response()->json([
            'signals' => $signals->map(fn($s) => [
                'id' => $s->id,
                'type' => $s->type,
                'data' => $s->data,
                'sender_id' => $s->sender_id,
            ]),
        ]);
    }

    /**
     * API: Получить список участников
     */
    public function getParticipants(VideoMeeting $meeting): JsonResponse
    {
        $user = auth()->user();

        if (!$meeting->canJoin($user)) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        $participants = $this->webrtcService->getActiveParticipants($meeting);

        return response()->json([
            'participants' => $participants->map(fn($p) => [
                'id' => $p->user_id,
                'name' => $p->user->name,
                'role' => $p->role,
                'status' => $p->status,
                'is_muted' => $p->is_muted,
                'is_video_off' => $p->is_video_off,
            ]),
        ]);
    }

    /**
     * API: Покинуть встречу
     */
    public function leave(VideoMeeting $meeting): JsonResponse
    {
        $user = auth()->user();

        $this->webrtcService->leaveMeeting($meeting, $user);

        return response()->json(['success' => true]);
    }

    /**
     * API: Завершить встречу (только хост)
     */
    public function end(VideoMeeting $meeting): JsonResponse
    {
        $user = auth()->user();

        if (!$meeting->isHost($user)) {
            return response()->json(['error' => 'Только организатор может завершить встречу'], 403);
        }

        $this->webrtcService->endMeeting($meeting);

        return response()->json(['success' => true]);
    }

    /**
     * API: Переключить mute/video
     */
    public function toggleMedia(Request $request, VideoMeeting $meeting): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:mute,video',
        ]);

        $user = auth()->user();
        $participant = $meeting->participants()->where('user_id', $user->id)->first();

        if (!$participant) {
            return response()->json(['error' => 'Вы не участник встречи'], 403);
        }

        if ($validated['type'] === 'mute') {
            $participant->toggleMute();
        } else {
            $participant->toggleVideo();
        }

        return response()->json([
            'success' => true,
            'is_muted' => $participant->is_muted,
            'is_video_off' => $participant->is_video_off,
        ]);
    }
}
