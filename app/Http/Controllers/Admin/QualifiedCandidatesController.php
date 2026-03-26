<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\Vacancy;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QualifiedCandidatesController extends Controller
{
    /**
     * Страница подходящих кандидатов
     */
    public function index(Request $request): View
    {
        $vacancies = Vacancy::where('is_active', true)
            ->orderBy('title')
            ->get();

        $query = Application::query()
            ->with(['candidate', 'vacancy', 'candidateTest', 'chatRoom'])
            ->whereNotNull('match_score')
            ->where('match_score', '>=', 50) // Минимум 50% соответствия
            ->whereIn('status', [
                ApplicationStatus::New,
                ApplicationStatus::InReview,
            ]);

        // Фильтр по вакансии
        if ($vacancyId = $request->input('vacancy_id')) {
            $query->where('vacancy_id', $vacancyId);
        }

        // Фильтр по минимальному score
        if ($minScore = $request->input('min_score')) {
            $query->where('match_score', '>=', (int) $minScore);
        }

        // Сортировка по match_score (лучшие сверху)
        $query->orderByDesc('match_score');

        $candidates = $query->paginate(20)->withQueryString();

        $selectedVacancy = $vacancyId ? Vacancy::find($vacancyId) : null;

        return view('admin.qualified-candidates.index', compact(
            'candidates',
            'vacancies',
            'selectedVacancy'
        ));
    }

    /**
     * Пригласить кандидата в чат
     */
    public function inviteToChat(Request $request, Application $application): JsonResponse
    {
        // Проверяем, что можно пригласить (не уже приглашён)
        if (in_array($application->status, [ApplicationStatus::Invited, ApplicationStatus::Hired])) {
            return response()->json([
                'success' => false,
                'message' => 'Кандидат уже приглашён',
            ], 400);
        }

        // Меняем статус на "Приглашён"
        $application->update([
            'status' => ApplicationStatus::Invited,
        ]);

        // Создаём чат-комнату
        $chatRoom = ChatRoom::getOrCreateForApplication($application);

        // Назначаем текущего HR
        if (!$chatRoom->hr_id) {
            $chatRoom->update(['hr_id' => auth()->id()]);
        }

        // Отправляем приветственное сообщение от HR
        $welcomeMessage = $request->input('message', 'Здравствуйте! Мы рассмотрели вашу заявку и хотели бы пригласить вас на собеседование. Пожалуйста, напишите нам удобное для вас время.');

        ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'sender_id' => auth()->id(),
            'sender_type' => 'hr',
            'message' => $welcomeMessage,
        ]);

        $chatRoom->update(['last_message_at' => now()]);

        // SMS уведомление отправится автоматически через Observer

        return response()->json([
            'success' => true,
            'message' => 'Кандидат приглашён в чат',
            'chat_url' => route('admin.chat.show', $application),
        ]);
    }

    /**
     * Массовое приглашение кандидатов
     */
    public function bulkInvite(Request $request): JsonResponse
    {
        $request->validate([
            'application_ids' => ['required', 'array'],
            'application_ids.*' => ['integer', 'exists:applications,id'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $welcomeMessage = $request->input('message', 'Здравствуйте! Мы рассмотрели вашу заявку и хотели бы пригласить вас на собеседование.');

        $invitedCount = 0;

        foreach ($request->input('application_ids') as $id) {
            $application = Application::find($id);

            if (!$application) continue;

            // Пропускаем уже приглашённых
            if (in_array($application->status, [ApplicationStatus::Invited, ApplicationStatus::Hired])) {
                continue;
            }

            // Меняем статус
            $application->update(['status' => ApplicationStatus::Invited]);

            // Создаём чат
            $chatRoom = ChatRoom::getOrCreateForApplication($application);

            if (!$chatRoom->hr_id) {
                $chatRoom->update(['hr_id' => auth()->id()]);
            }

            // Приветственное сообщение
            ChatMessage::create([
                'chat_room_id' => $chatRoom->id,
                'sender_id' => auth()->id(),
                'sender_type' => 'hr',
                'message' => $welcomeMessage,
            ]);

            $chatRoom->update(['last_message_at' => now()]);

            $invitedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Приглашено кандидатов: {$invitedCount}",
            'invited_count' => $invitedCount,
        ]);
    }
}
