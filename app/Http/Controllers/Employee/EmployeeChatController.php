<?php

namespace App\Http\Controllers\Employee;

use App\Enums\AiContextType;
use App\Http\Controllers\Controller;
use App\Models\EmployeeAiConversation;
use App\Services\AiGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeChatController extends Controller
{
    public function __construct(
        private readonly AiGatewayService $aiGateway
    ) {}

    /**
     * Список чатов
     */
    public function index(Request $request): View
    {
        $employee = auth()->user()->employeeProfile;

        $conversations = $employee->aiConversations()
            ->recent()
            ->paginate(20);

        return view('employee.chat.index', [
            'employee' => $employee,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Показать чат
     */
    public function show(Request $request, EmployeeAiConversation $conversation): View
    {
        // Проверяем доступ
        $this->authorizeConversation($conversation);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get();

        return view('employee.chat.show', [
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    /**
     * Создать новый чат
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'context_type' => 'required|string|in:' . implode(',', AiContextType::values()),
            'message' => 'required|string|max:2000',
        ]);

        $employee = auth()->user()->employeeProfile;

        // Создаём новый разговор
        $conversation = $employee->aiConversations()->create([
            'context_type' => $validated['context_type'],
            'status' => 'active',
        ]);

        // Отправляем первое сообщение
        $result = $this->aiGateway->chat($employee, $conversation, $validated['message']);

        // Генерируем название на основе первого сообщения
        $conversation->update([
            'title' => \Str::limit($validated['message'], 100),
        ]);

        return response()->json([
            'success' => $result['success'],
            'conversation_id' => $conversation->id,
            'redirect_url' => route('employee.chat.show', $conversation),
            'response' => $result['response'] ?? null,
        ]);
    }

    /**
     * Отправить сообщение
     */
    public function sendMessage(Request $request, EmployeeAiConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $employee = auth()->user()->employeeProfile;

        $result = $this->aiGateway->chat($employee, $conversation, $validated['message']);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'] ?? null,
            'response' => $result['response'] ?? null,
            'intent' => $result['intent'] ?? null,
            'sources' => $result['sources'] ?? [],
        ]);
    }

    /**
     * Получить сообщения (polling)
     */
    public function getMessages(Request $request, EmployeeAiConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        $afterId = $request->query('after_id', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'role' => $m->role->value,
                'content' => $m->content,
                'intent' => $m->intent_label,
                'created_at' => $m->created_at->format('H:i'),
            ]),
            'last_id' => $messages->last()?->id ?? $afterId,
        ]);
    }

    /**
     * Закрыть чат
     */
    public function close(Request $request, EmployeeAiConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        $conversation->close();

        return response()->json(['success' => true]);
    }

    /**
     * Проверить доступ к разговору
     */
    private function authorizeConversation(EmployeeAiConversation $conversation): void
    {
        $employee = auth()->user()->employeeProfile;

        if (!$employee) {
            abort(403, 'Доступ запрещён');
        }

        if ($conversation->employee_profile_id !== $employee->id) {
            // HR и SysAdmin могут видеть все разговоры
            if (!$employee->role?->canViewAllEmployees()) {
                abort(403, 'Доступ запрещён');
            }
        }
    }
}
