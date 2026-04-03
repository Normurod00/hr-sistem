<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Получить непрочитанные уведомления
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $notifications = $request->user()
                ->unreadNotifications()
                ->take(20)
                ->get()
                ->map(fn($n) => [
                    'id' => $n->id,
                    'type' => $n->data['type'] ?? 'general',
                    'message' => $n->data['message'] ?? '',
                    'url' => $n->data['url'] ?? null,
                    'data' => $n->data,
                    'created_at' => $n->created_at->diffForHumans(),
                ]);

            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Таблица notifications ещё не создана
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }
    }

    /**
     * Отметить уведомление как прочитанное
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Отметить все как прочитанные
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
