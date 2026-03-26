<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Авторизация по email/паролю
     *
     * POST /api/auth/login
     * Body: { "email": "...", "password": "..." }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный email или пароль.',
            ], 401);
        }

        $user = Auth::user();

        // Генерируем API токен
        $apiToken = $this->generateApiToken($user);

        Log::info('API login success', [
            'user_id' => $user->id,
            'name' => $user->name,
        ]);

        return response()->json([
            'success' => true,
            'token' => $apiToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role->value,
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }

    /**
     * Получить данные текущего пользователя
     *
     * GET /api/auth/me
     * Headers: Authorization: Bearer {token}
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Не авторизован.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role->value,
                'avatar_url' => $user->avatar_url,
                'created_at' => $user->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * Выход (инвалидация токена)
     *
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $user->update(['api_token' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Выход выполнен.',
        ]);
    }

    /**
     * Генерация API токена
     */
    protected function generateApiToken(User $user): string
    {
        $token = Str::random(64);

        $user->update([
            'api_token' => hash('sha256', $token),
        ]);

        return $token;
    }
}
