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

        $unauthorizedResponse = response()->json([
            'success' => false,
            'message' => 'Не авторизован.',
        ], 401);

        if (!$token) {
            return $unauthorizedResponse;
        }

        // Ищем пользователя по хэшу токена
        $user = User::where('api_token', hash('sha256', $token))->first();

        if (!$user) {
            return $unauthorizedResponse;
        }

        // Проверяем срок действия токена
        if ($user->api_token_expires_at && $user->api_token_expires_at->isPast()) {
            $user->api_token = null;
            $user->api_token_expires_at = null;
            $user->save();

            return $unauthorizedResponse;
        }

        // Устанавливаем пользователя в request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
