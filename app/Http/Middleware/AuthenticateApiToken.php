<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Проверка API токена
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Токен не предоставлен.',
            ], 401);
        }

        // Ищем пользователя по хэшу токена
        $user = User::where('api_token', hash('sha256', $token))->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный токен.',
            ], 401);
        }

        // Проверяем срок действия токена
        if ($user->api_token_expires_at && $user->api_token_expires_at->isPast()) {
            $user->update(['api_token' => null, 'api_token_expires_at' => null]);

            return response()->json([
                'success' => false,
                'message' => 'Токен истёк. Выполните повторную авторизацию.',
            ], 401);
        }

        // Устанавливаем пользователя в request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
